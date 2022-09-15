<?php

/** @noinspection MissedFieldInspection */

use humhub\modules\ui\menu\widgets\Menu;
use humhub\components\ActiveRecord;

return [
    'id' => 'advanced-ldap',
    'class' => 'humhub\modules\advancedLdap\Module',
    'namespace' => 'humhub\modules\advancedLdap',
    'events' => [
        ['humhub\modules\space\modules\manage\widgets\MemberMenu', Menu::EVENT_INIT, ['humhub\modules\advancedLdap\Events', 'onSpaceMemberMenuInit']],
        ['humhub\modules\admin\widgets\GroupManagerMenu', Menu::EVENT_INIT, ['humhub\modules\advancedLdap\Events', 'onAdminGroupMenuInit']],
        ['humhub\modules\user\models\Group', ActiveRecord::EVENT_BEFORE_DELETE, ['humhub\modules\advancedLdap\Events', 'onGroupDelete']],
        ['humhub\modules\space\models\Space', ActiveRecord::EVENT_BEFORE_DELETE, ['humhub\modules\advancedLdap\Events', 'onSpaceDelete']],
        ['humhub\modules\user\authclient\Collection', 'client_set', ['humhub\modules\advancedLdap\Events', 'onAuthClientCollectionInit']],
        ['humhub\modules\user\controllers\ImageController', 'init', ['humhub\modules\advancedLdap\Events', 'onUserImageControllerInit']],
    ]
];
?>