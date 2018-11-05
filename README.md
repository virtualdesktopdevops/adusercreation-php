# adusercreation-php
PHP webservice for Active Directory user creation

## Integration
### Web server configuration
Adusercreation uses an LDAPS Active Directory connection to create the user. The connection has to be secured with SSL to allow password change and account activation. Active Directory doesn't allow password data to transit over a non secure LDAP connection.

Active Directory domain controller has to be configured with a valid SSL certificate. The domain controller certificate can be provided by a Microsoft or a Third Party Certification Authority. Tutorial provided on https://gist.github.com/magnetikonline/0ccdabfec58eb1929c997d22e7341e45

The Certification Authority root certificate has to be included in the certificate trust store of the web server hosting the Adusercreation webservice. On Debian 9, the crt file has to be deployed in **/usr/local/share/ca-certificates/**, included with the **update-ca-certificates** command. Apache2 has to be then reloaded to allow it to validate the Active Directory LDAPS certificate during LDAPS connection establishment. For PHP configuration on IIS, use the following guide : http://www.web-site-scripts.com/knowledge-base/getAttach/263/AA-00754/Enable+LDAPS+on+Windows+IIS.pdf

### Adusercreation webservice configuration
Configure the LDAPS variables in **config.php**. Example in the sample config.php file provided on github.

In this first release, user is created in the default Active Directory **Users CN**.

## How does Adusercreation work ?
### How to create an Active Directory account with PHP using LDAPS
Adusercreation uses Adldap2 PHP library to interact with Active Directory LDAP interface.

To create Active Directory users using LDAP you first need to create the user, which is created disabled, then set the password and then enable the account.

The userAccountControl determines if an account is enabled or disabled. According to Microsoft's documentation the following values can be used and combined:
```
Tag 	Name 	Notes
SCRIPT 	1 	
ACCOUNTDISABLE 	2 	
HOMEDIR_REQUIRED 	8 	
LOCKOUT 	16 	
PASSWD_NOTREQD 	32 	You can not assign this permission
PASSWD_CANT_CHANGE 	64 	
ENCRYPTED_TEXT_PWD_ALLOWED 	128 	
TEMP_DUPLICATE_ACCOUNT 	256 	
NORMAL_ACCOUNT 	512 	
INTERDOMAIN_TRUST_ACCOUNT 	2048 	
WORKSTATION_TRUST_ACCOUNT 	4096 	
SERVER_TRUST_ACCOUNT 	8192 	
DONT_EXPIRE_PASSWORD 	65536 	
MNS_LOGON_ACCOUNT 	131072 	
SMARTCARD_REQUIRED 	262144 	
TRUSTED_FOR_DELEGATION 	524288 	
NOT_DELEGATED 	1048576 	
USE_DES_KEY_ONLY 	2097152 	
DONT_REQ_PREAUTH 	4194304 	
PASSWORD_EXPIRED 	8388608 	
TRUSTED_TO_AUTH_FOR_DELEGATION 	16777216
```
So 512 is a normal user account adding 2 results in a normal user account, but disabled.

To make sure that the account never expires we set the accountExpires value to 0, which seems to work. Although according to http://arnoutvandervorst.blogspot.com/2008/03/ldap-accountexpires-attribute-values.html, the initial value should be: 9223372036854775807.

Of course the user needs a password. To create the password do:

echo -n "\"password\"" | iconv -f UTF8 -t UTF16LE | base64 -w 0

Microsoft stores a quoted password in little endian UTF16 base64 encoded. The trivial command above takes care of it all. Note the -n option to echo, otherwise the carriage-return will also be part of the password.

### Example LDIF
The first part of the following LDIF creates the disabled user account, the second part sets the password and the last part enables the account:

```
dn: CN=Piet Prutser,CN=Users,DC=forest,DC=example,DC=com
changetype: add
objectClass: top
objectClass: person
objectClass: organizationalPerson
objectClass: user
objectCategory: CN=Person,CN=Schema,CN=Configuration,DC=example,DC=com
codePage: 0
countryCode: 0
distinguishedName: CN=Piet Prutser,CN=Users,DC=forest,DC=example,DC=com
cn: Piet Prutser
sn: Prutser
givenName: Piet
displayName: Piet Prutser
name: Piet Prutser
telephoneNumber: 123456
instanceType: 4
userAccountControl: 514
accountExpires: 0
uidNumber: 600
gidNumber: 600
sAMAccountName: pprutser
userPrincipalName: P.Prutser@example.com
altSecurityIdentities: Kerberos:pprutser@EXAMLE.COM
mail: P.Prutser@example.com
homeDirectory: \\ads\home\pprutser
homeDrive: Z:
unixHomeDirectory: /home/pprutser
loginShell: /bin/bash

dn: CN=Piet Prutser,OU=Users,DC=forest,DC=example,DC=com
changetype: modify
replace: unicodePwd
unicodePwd::IlwwdFwwZVwwc1wwdFwwIgo=

dn: CN=Piet Prutser,OU=Users,DC=forest,DC=example,DC=com
changetype: modify
replace: userAccountControl
userAccountControl: 512
```

When you get an error report like this:

ldap_modify: Server is unwilling to perform (53)
additional info: 0000001F: SvcErr: DSID-031A0FC0, problem 5003 (WILL_NOT_PERFORM), data 0

It means that the password (like the one in the example) is not a correct UTF16LE password

### Reference
- http://pig.made-it.com/pig-adusers.html
- https://community.hortonworks.com/articles/82544/how-to-create-ad-principal-accounts-using-openldap.html
