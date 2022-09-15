<?php

namespace humhub\modules\advancedLdap\authclient;

use humhub\modules\advancedLdap\LdapHelper;
use humhub\modules\advancedLdap\models\Group as GroupLdapModel;
use humhub\modules\advancedLdap\models\Space as SpaceLdapModel;
use humhub\modules\ldap\authclient\LdapAuth as BaseLdapAuth;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\User;
use humhub\modules\advancedLdap\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;

/**
 * {@inheritDoc}
 */
class LdapAuth extends BaseLdapAuth
{

    /**
     * @var string|null ldap attribute name which holds the user profile image
     */
    public $profileImageAttribute = null;

    /**
     * @var boolean if enabled only LDAP profile images will be used - otherwise import when no image exists
     */
    public $forceProfileImage = true;

    /**
     * @var SpaceLdapModel[] Map
     */
    private static $_spaceMappings = null;

    /**
     * @var GroupLdapModel[] Map
     */
    private static $_groupMappings = null;

    /**
     * @var string the temp directory
     */
    protected $tempDirectory;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_UPDATE_USER, [$this, 'onUpdateUser']);
        $this->on(self::EVENT_CREATE_USER, [$this, 'onUpdateUser']);

        // Create temporary directory for image imports
        $this->tempDirectory = Yii::getAlias('@runtime/temp');
        if (!is_dir($this->tempDirectory)) {
            mkdir($this->tempDirectory);
        }

        parent::init();
    }

    /**
     * Ensure group and space mapping
     *
     * @param \yii\web\UserEvent $event
     */
    public function onUpdateUser($event)
    {
        /** @var User $user */
        $user = $event->identity;

        /** @var Module $module */
        $module = Yii::$app->getModule('advanced-ldap');

        // Load space mappings configured by humhub
        if (self::$_spaceMappings === null) {
            self::$_spaceMappings = SpaceLdapModel::find()->with(['space'])->all();
        }

        // Load group mappings configured by humhub
        if (self::$_groupMappings === null) {
            self::$_groupMappings = GroupLdapModel::find()->all();
        }

        $attributes = $this->getUserAttributes();

        // Oracle Directory Server Enterprise Edition Administration Guide 11g Release 1 (11.1.1.5.0)
        if (isset($attributes['ismemberof'])) {
            $attributes['memberof'] = $attributes['ismemberof'];
        }

        unset($attributes['memberof']['count']);

        if (isset($attributes['memberof']) && $module->enableLdapParentMembershipLookup) {
            foreach ($attributes['memberof'] as $groupDn) {
                $attributes['memberof'] = array_merge(LdapHelper::getLdapParentGroups($groupDn, $this->getLdap()), $attributes['memberof']);
            }
            $attributes['memberof'] = array_unique($attributes['memberof']);
        }

        #if (Yii::$app->request->isConsoleRequest) {
        $this->updateSpaceMemberships($user, $attributes);
        $this->updateGroupMemberships($user, $attributes);
        #}

        $this->updateProfileImage($user, $attributes);
    }

    /**
     * Update space memberships
     *
     * @param User $user
     * @param $attributes array of LDAP attributes
     */
    protected function updateProfileImage(User $user, $attributes)
    {
        // Check if image import is enabled
        if ($this->profileImageAttribute === null) {
            Yii::warning('No profile image attribute specified!', 'advanced-ldap');
            return;
        }

        // Check if LDAP image is set
        if (!isset($attributes[$this->profileImageAttribute])) {
            Yii::warning('LDAP profile image not set for user "'.$user->username.'". ', 'advanced-ldap');
            return;
        }

        // Check if user has already an image && overwrite is enabled
        if ($user->profileImage->hasImage() && !$this->forceProfileImage) {
            Yii::warning('LDAP profile image not overwritten for user "'.$user->username.'".  User Image already set!', 'advanced-ldap');
            return;
        }

        $ldapImageFile = $user->profileImage->getPath('_ldap');

        // Check if image has changed
        if (file_exists($ldapImageFile)) {
            if (file_exists($user->profileImage->getPath('_org')) && md5_file($ldapImageFile) == md5($attributes[$this->profileImageAttribute])) {
                return;
            }
        }

        file_put_contents($ldapImageFile, $attributes[$this->profileImageAttribute]);
        $user->profileImage->setNew($ldapImageFile);
    }

    /**
     * Update space memberships
     *
     * @param User $user
     * @param $attributes array of LDAP attributes
     */
    protected function updateSpaceMemberships(User $user, $attributes)
    {
        // Update user's space memberships
        $userLdapSpaceIds = [];
        foreach (self::$_spaceMappings as $spaceMapping) {
            if (empty($spaceMapping->space)) {
                continue;
            }

            if (LdapHelper::matchMapping($spaceMapping->dn, $user, $attributes, $this->getLdap())) {

                // add user as member of space.
                try {
                    $spaceMapping->space->addMember($user->id);
                } catch (InvalidConfigException $e) {
                    Yii::error($e->getMessage(), 'advanced-ldap');
                    continue;
                } catch (\Throwable $e) {
                    Yii::error($e->getMessage(), 'advanced-ldap');
                    continue;
                }

                $membership = $spaceMapping->space->getMembership($user->id);
                $membership->authclient_id = $this->getId();
                $membership->can_cancel_membership = 0;
                $membership->save();

                Yii::info('Added user ' . $user->displayName . ' to space ' . $spaceMapping->space->name . '. (LDAP Space Mapping)', 'advanced-ldap');

                // Store mappings to revoke
                $userLdapSpaceIds[] = $spaceMapping->space_id;
            }
        }

        // Get all current user group memberships handled by LDAP
        $spaceMembershipsLdap = Membership::find()->with('space')->where(['authclient_id' => $this->getId(), 'user_id' => $user->id])->all();
        // Remove memberships where the ldap mapping no longer exists for the user 
        foreach ($spaceMembershipsLdap as $spaceMembershipLdap) {
            if (!in_array($spaceMembershipLdap->space_id, $userLdapSpaceIds)) {
                try {
                    $spaceMembershipLdap->delete();
                } catch (StaleObjectException $e) {
                    Yii::error($e->getMessage(), 'advanced-ldap');
                    continue;
                } catch (\Throwable $e) {
                    Yii::error($e->getMessage(), 'advanced-ldap');
                    continue;
                }
                Yii::info('Removing user ' . $user->displayName . ' from space ' . $spaceMembershipLdap->space->name . '. (No LDAP Space Mapping anymore)', 'advanced-ldap');
            }
        }
    }

    /**
     * Update group members
     *
     * @param User $user
     * @param $attributes array of LDAP attributes
     */
    protected function updateGroupMemberships(User $user, $attributes)
    {
        // Make sure user is added to all groups
        $userLdapGroupIds = [];
        foreach (self::$_groupMappings as $groupMapping) {

            if (LdapHelper::matchMapping($groupMapping->dn, $user, $attributes, $this->getLdap())) {

                if ($groupMapping->group === null) {
                    Yii::error('Group mapping invalid. Group mapping with id ' . $groupMapping->id . ' is invalid!', 'advanced-ldap');
                    continue;
                }

                if (!$groupMapping->group->isMember($user)) {
                    $newGroupUser = new GroupUser();
                    $newGroupUser->user_id = $user->id;
                    $newGroupUser->group_id = $groupMapping->group_id;
                    $newGroupUser->is_group_manager = false;
                    $newGroupUser->authclient_id = $this->getId();

                    if ($newGroupUser->save()) {
                        Yii::info('Added user ' . $user->displayName . ' to group ' . $groupMapping->group->name . '. (LDAP Group Mapping)', 'advanced-ldap');
                    } else {
                        Yii::error('Could not add user ' . $user->displayName . ' to group ' . $groupMapping->group->name . '. (LDAP Group Mapping)', 'advanced-ldap');
                    }
                }
                $userLdapGroupIds[] = $groupMapping->group_id;
            }
        }

        // Get current user group memberships handled by LDAP
        $groupUsersLdap = GroupUser::find()->with('group')->where(['authclient_id' => $this->getId(), 'user_id' => $user->id])->all();
        foreach ($groupUsersLdap as $groupUserLdap) {
            if (!in_array($groupUserLdap->group_id, $userLdapGroupIds)) {
                $groupUserLdap->group->removeUser($user);
                Yii::info('Removing user ' . $user->displayName . ' from group ' . $groupUserLdap->group->name . '. (No LDAP Group Mapping anymore)', 'advanced-ldap');
            }
        }
    }

    protected function normalizeUserAttributes($attributes)
    {
        // Oracle Directory Server Enterprise Edition Administration Guide 11g Release 1 (11.1.1.5.0)
        if (isset($attributes['ismemberof'])) {
            $attributes['memberof'] = $attributes['ismemberof'];
        }

        return parent::normalizeUserAttributes($attributes);
    }

}