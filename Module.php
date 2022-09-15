<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\advancedLdap;


class Module extends \humhub\components\Module
{
    /**
     * @inheritdoc
     */
    public $resourcesPath = 'resources';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\advancedLdap\controllers';

    /**
     * @var boolean recursively lookup ldap resolve group memberships
     */
    public $enableLdapParentMembershipLookup = false;
}