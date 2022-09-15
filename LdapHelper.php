<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\advancedLdap;

use humhub\modules\user\models\User;
use Laminas\Ldap\Exception\LdapException;
use Laminas\Ldap\Ldap;
use Yii;

/**
 * Class LdapHelper
 */
class LdapHelper
{
    /**
     * @var array cached list of parent groups
     */
    protected static $_ldapParentGroups = [];

    /**
     * @var array cached query results
     */
    protected static $_ldapQueryResults = [];

    const MAPPING_ATTRIBUTE = 1;
    const MAPPING_QUERY = 2;
    const MAPPING_MEMBER_OF = 3;
    const MAPPING_DN = 4;

    /**
     * @return bool
     */
    public static function isLdapEnabled()
    {
        foreach (Yii::$app->authClientCollection->getClients() as $authClient) {

            if (method_exists($authClient, 'getLdap')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns parent groups of an given group dn
     *
     * @param string $groupDn
     * @param Ldap $ldap
     * @return array list of dns
     */
    public static function getLdapParentGroups($groupDn, $ldap)
    {
        if (!isset(self::$_ldapParentGroups[$groupDn])) {
            self::$_ldapParentGroups[$groupDn] = [];

            try {
                foreach ($ldap->getNode($groupDn)->getAttribute('memberOf') as $parentGroupDn) {
                    self::$_ldapParentGroups[$groupDn][] = $parentGroupDn;
                    self::$_ldapParentGroups[$groupDn] = array_merge(self::getLdapParentGroups($parentGroupDn, $ldap), self::$_ldapParentGroups[$groupDn]);
                }
            } catch (\Exception $ex) {
                Yii::error('Could not get memberOf node: ' . $groupDn . " - Error: " . $ex->getMessage(), 'advanced-ldap');
            }
        }
        return self::$_ldapParentGroups[$groupDn];
    }


    /**
     * Matches a mapping against the user ldap attributes, this can be done by different possibilities.
     *
     * The mapping is found in:
     * - the attributes 'memberof' array
     * - is part of the 'dn' attribute
     *
     * Additionally there is also a special syntax to directly match attributes:
     * - attributeName==attributeValue (e.g. city==Munich)
     * - attributeName=~IT (e.g. some IT Department, department=IT Services)
     *
     * @param string $mapping
     * @param User $user
     * @param array $attributes
     * @param Ldap $ldap
     * @return boolean if the mapping matches
     */
    public static function matchMapping(string $mapping, User $user, array $attributes, Ldap $ldap): bool
    {
        if (!isset($attributes['dn']) && isset($attributes['distinguishedname'])) {
            $attributes['dn'] = $attributes['distinguishedname'];
        }

        $mappingType = static::getMappingType($mapping);

        if ($mappingType === static::MAPPING_QUERY) {
            if (!isset(static::$_ldapQueryResults[$mapping])) {
                static::$_ldapQueryResults[$mapping] = [];
                foreach ($ldap->search($mapping, null, Ldap::SEARCH_SCOPE_SUB) as $r) {
                    static::$_ldapQueryResults[$mapping][] = $r['dn'];
                }
            }
            if (in_array($attributes['dn'], static::$_ldapQueryResults[$mapping])) {
                return true;
            }
        } elseif ($mappingType === static::MAPPING_MEMBER_OF) {
            // Check if mapping is in membership array
            if (isset($attributes['memberof']) && is_array($attributes['memberof'])) {
                if (in_array(strtolower($mapping), array_map('strtolower', $attributes['memberof']))) {
                    return true;
                }
            }
        } elseif ($mappingType === static::MAPPING_DN) {
            // Check if mapping is part of the user ldap record DN
            if (isset($attributes['dn']) && strpos(strtolower($attributes['dn']), strtolower($mapping)) !== false) {
                return true;
            }
        } elseif ($mappingType === static::MAPPING_ATTRIBUTE) {
            // Check if mapping is found as user ldap attribute (==)
            if (strpos($mapping, '==') !== false) {
                list($name, $value) = explode('==', $mapping, 2);
                $name = strtolower($name);

                if (is_array($value) || !isset($attributes[$name]) || is_array($attributes[$name])) {
                    return false;
                }

                if (trim($attributes[$name]) == trim($value)) {
                    return true;
                }
            }

            // Check if mapping is found as user ldap attribute value part (=~)
            if (strpos($mapping, '=~') !== false) {
                list($name, $value) = explode('=~', $mapping, 2);
                $name = strtolower($name);
                if (isset($attributes[$name]) && strpos($attributes[$name], trim($value)) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getMappingType($mapping): int
    {
        if (substr($mapping, 0, 1) === '(') {
            return static::MAPPING_QUERY;
        } elseif (strpos($mapping, '==') !== false || strpos($mapping, '=~') !== false) {
            return static::MAPPING_ATTRIBUTE;
        } elseif (strtolower(substr($mapping, 0, 3)) === 'cn=') {
            return static::MAPPING_MEMBER_OF;
        } else {
            return static::MAPPING_DN;
        }

    }


    public static function getQueryStatus($query)
    {
        try {
            $ldapAuthClient = Yii::$app->authClientCollection->getClient('ldap');
            $ldap = $ldapAuthClient->getLdap();
            $count = $ldap->count($query, null, Ldap::SEARCH_SCOPE_SUB);
            return Yii::t('AdvancedLdapModule.base', '{count} result(s)', ['count' => $count]);
        } catch (LdapException $ex) {
            return Yii::t('AdvancedLdapModule.base', 'Error');
        }

    }

}