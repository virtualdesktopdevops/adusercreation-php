<?php
require_once 'vendor/autoload.php';
include 'config.php';

use \adldap2\adldap2;
use RestService\Server; #https://github.com/marcj/php-rest-service

$useraccount = $_GET['useraccount'];
$userpassword = $_GET['password'];
$userfirstname = $_GET['userfirstname'];
$userlastname = $_GET['userlastname'];
$useremail = $_GET['useremail'];

// Construct new Adldap instance.
$ad = new \Adldap\Adldap();

// Create a configuration array.
$config = [
  // An array of your LDAP hosts. You can use either
  // the host name or the IP address of your host.
  'hosts'    => $hosts,
  'port'     => 636,
  'use_ssl'  => true,

  // The LDAP type and LDAP base distinguished name of your domain to perform searches upon.
  'schema'         => \Adldap\Schemas\ActiveDirectory::class,
  'base_dn'        => $base_dn,
  'account_suffix' => $account_suffix,

  // The account to use for querying / modifying LDAP records. This
  // does not need to be an admin account. This can also
  // be a full distinguished name of the user account.
  'username' => $serviceaccount,
  'password' => $serviceaccountpassword,
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
    $user = $provider->search()->find($useraccount);

    if ($user->exists) {
      http_response_code(403);
      exit("User already exists");
    }
    else {
      // Creating a new LDAP entry. You can pass in attributes into the make methods.
      $user =  $provider->make()->user();

      // Setting a model's attribute.
      $user->setAccountName($useraccount);
      $user->setCommonName($useraccount);
      $user->setFirstName($userfirstname);
      $user->setLastName($userlastname);
      #$user->setCompany('ACME');
      $user->setEmail($useremail);

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
          $user->setPassword($userpassword);

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
?>
