<p>
    <?= Yii::t('AdvancedLdapModule.base', 'Mapping options:'); ?>
<ul>
    <li><?= Yii::t('AdvancedLdapModule.base', 'Part of the users base DN (e.g. <code>OU=People,DC=example,DC=com</code>)'); ?></li>
    <li><?= Yii::t('AdvancedLdapModule.base', 'User Memberships (MemberOf, e.g. <code>CN=xyz_space_access,OU=Groups,DC=example,DC=com</code>)'); ?></li>
    <li><?= Yii::t('AdvancedLdapModule.base', 'Attribute value (e.g. <code>street==Some Street</code> <i>or</i> <code>street=~Street</code>)'); ?></li>
    <li><?= Yii::t('AdvancedLdapModule.base', 'LDAP Query (e.g. <code>(&(objectCategory=person)(objectClass=user))</code>)'); ?></li>
</ul>
</p>