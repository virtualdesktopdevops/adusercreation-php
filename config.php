<?php

// An array of your LDAP hosts. You can use either
// the host name or the IP address of your host.
$hosts = ['WIN-P6AESJ2R0PA.domain-test.com'];
$basedn = 'dc=domain-test,dc=com';
$account_suffix = '@domain-test.com';

// The account to use for querying / modifying LDAP records. This
// does not need to be an admin account. This can also
// be a full distinguished name of the user account.
$serviceaccount = 'administrator@domain-test.com';
$serviceaccountpassword = 'P@ssw0rd';

 ?>
