<?php
$user = $this->provider->make()->user();

$user->setCommonName('Daisy Duck');
$user->setDisplayName('Daisy Duck');
$user->setFirstName('Daisy');
$user->setLastName('Duck');
$user->setTitle('Girlfriend of Donald Duck');
$user->setDepartment('Andeby');
$user->setInfo('Daisy was introduced in the short film Mr. Duck Steps Out (1940)');
$user->setInitials('DD');
$user->setPhysicalDeliveryOfficeName('Clubhouse office');
$user->setTelephoneNumber('12345678');
$user->setCompany('Mickey Mouse Clubhouse');
$user->setPassword('Monday123');
$user->setStreetAddress('Duckburg 123');
$user->setPostalCode('1234');

$dn = $user->getDnBuilder();

$dn->addCn($user->getCommonName());
$dn->addOu('Testusers');
$dn->addDc('login');
$dn->addDc('domain');
$dn->addDc('local');

// Returns 'cn=John Doe,ou=Accounting,dc=corp,dc=acme,dc=org'
echo $dn->get();

// The DistinguishedName object also contains the __toString() magic method
// so you can also just echo the object itself
echo $dn;
$user->setDn($dn);
$user->save();
 ?>
