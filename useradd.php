<?php
require_once 'vendor/autoload.php';

use \adldap2\adldap2;
#https://github.com/marcj/php-rest-service
use RestService\Server;

// Construct new Adldap instance.
$ad = new \Adldap\Adldap();

// Create a configuration array.
$config = [
  // An array of your LDAP hosts. You can use either
  // the host name or the IP address of your host.
  'hosts'    => ['WIN-P6AESJ2R0PA.domain-test.com'],
  'port'             => 636,
  'use_ssl'          => true,

  // The LDAP type and LDAP base distinguished name of your domain to perform searches upon.
  'schema'           => \Adldap\Schemas\ActiveDirectory::class,
  'base_dn'  => 'dc=domain-test,dc=com',
  'account_suffix'   => '@domain-test.com',

  // The account to use for querying / modifying LDAP records. This
  // does not need to be an admin account. This can also
  // be a full distinguished name of the user account.
  'username' => 'administrator@domain-test.com',
  'password' => 'P@ssw0rd',
];

// Add a connection provider to Adldap.
$ad->addProvider($config);

try {
    // If a successful connection is made to your server, the provider will be returned.
    $provider = $ad->connect();

    // Performing a query.
    #$results = $provider->search()->where('cn', '=', 'matthieu')->get();
    #echo '<pre>' . var_export($results, true) . '</pre>';

    // Finding a record.
    $user = $provider->search()->find('John Doe');

    if ($user->exists) {
      http_response_code(403);
      exit("User already exists");
    }
    else {
      // Creating a new LDAP entry. You can pass in attributes into the make methods.
      $user =  $provider->make()->user();

      // Setting a model's attribute.
      $user->setAccountName('jdoe');
      $user->setCommonName('John Doe');
      $user->setFirstName('John');
      $user->setLastName('Doe');
      $user->setCompany('ACME');
      $user->setEmail('jdoe@acme.com');

      /*$dn = $user->getDnBuilder();
      $dn->addCn($user->getCommonName());
      $dn->addOu('User Accounts');
      $dn->addDc('domain-test');
      $dn->addDc('com');
      $user->setDn($dn);*/

      // Saving the changes to your LDAP server.
      if ($user->save()) {
          // User was saved!
          echo "<p>User was saved!</p>";

          // Enable the new user (using user account control).
          $user->setUserAccountControl('512');

          // Set new user password
          $user->setPassword('P@ssw0rd');

          // Save the user.
          if($user->save()) {
              // The password was saved successfully.
              echo "<p>The password was saved successfully</p>";
          }
      }
    }


} catch (\Adldap\Auth\BindException $e) {

    // There was an issue binding / connecting to the server.
    echo '<pre>' . var_export($e, true) . '</pre>';

}

/*$user = $this->provider->make()->user();

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
$user->save();*/
 ?>
