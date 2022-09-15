<?php

namespace humhub\modules\advancedLdap;

use humhub\components\Event;
use humhub\modules\user\controllers\ImageController;
use Yii;
use yii\helpers\Url;

class Events
{
    /**
     * @param $event Event
     */
    public static function onAdminGroupMenuInit($event)
    {
        if (!LdapHelper::isLdapEnabled()) {
            return;
        }

        $event->sender->addItem(array(
            'label' => Yii::t('AdvancedLdapModule.base', 'LDAP Mapping'),
            'sortOrder' => 1000,
            'isActive' => (Yii::$app->controller->id == 'group' && Yii::$app->controller->module->id == 'ldap'),
            'url' => Url::to(['/advanced-ldap/group/index', 'groupId' => $event->sender->group->id]),
        ));
    }

    /**
     * @param $event Event
     */
    public static function onGroupDelete($event)
    {
        models\Group::deleteAll(['group_id' => $event->sender->id]);
    }

    /**
     * @param $event Event
     */
    public static function onSpaceDelete($event)
    {
        models\Space::deleteAll(['space_id' => $event->sender->id]);
    }


    /**
     * Replace LDAP AuthClient with Enterprise
     *
     * @param Event $event
     * @since 1.1
     */
    public static function onAuthClientCollectionInit($event)
    {
        foreach ($event->sender->getClients(false) as $id => $config) {

            if (is_array($config) && isset($config['class'])) {
                if ($config['class'] == 'humhub\modules\user\authclient\ZendLdapClient' ||
                    $config['class'] == 'humhub\modules\ldap\authclient\LdapAuth' ||
                    $config['class'] == 'humhub\modules\enterprise\modules\ldap\authclient\ZendLdapClientEnterprise'
                ) {
                    $config['class'] = authclient\LdapAuth::class;
                    $event->sender->setClient($id, $config);
                }
            }
        }
    }

    /**
     * @param $event Event
     */
    public static function onSpaceMemberMenuInit($event)
    {
        if (!Yii::$app->user->isAdmin()) {
            return;
        }

        if (!LdapHelper::isLdapEnabled()) {
            return;
        }

        $event->sender->addItem(array(
            'label' => Yii::t('AdvancedLdapModule.base', 'LDAP'),
            'sortOrder' => 1000,
            'isActive' => (Yii::$app->controller->id == 'space' && Yii::$app->controller->module->id == 'ldap'),
            'url' => $event->sender->space->createUrl('/advanced-ldap/space/index'),
        ));
    }

    /**
     * Event callback when the user ImageController is initialized
     *
     * @param Event $event
     * @see \humhub\modules\user\controllers\ImageController
     */
    public static function onUserImageControllerInit($event)
    {
        if (!LdapHelper::isLdapEnabled()) {
            return;
        }

        /* @var $imageController ImageController */
        $imageController = $event->sender;

        /*
        $user = $imageController->user;

        foreach (AuthClientHelpers::getAuthClientsByUser($user) as $authClient) {
            if ($authClient instanceof authclient\LdapAuth) {

                // Profile Image import enabled
                if ($authClient->profileImageAttribute === null) {
                    return;
                }

                if ($authClient->forceProfileImage) {
                    $imageController->allowModifyProfileImage = false;
                    return;
                }
            }
        }
        */
    }
}