<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//Transfer timesheets from Kimai to Dolibarr on page load.
$config['transfer_on_load']=false;
//Dolibarr API Key
$config['DOLAPIKEY']='Wi2S4ZQGtKYcv9hur9wPV88sj5w90H1F';
$config['dolibarr_uri'] = 'http://192.168.2.159/dolibarr/htdocs/api/index.php/';
//Kimai 2 API Authentication
$config['kimai_user'] = 'admin';
$config['kimai_token'] = 'password';
$config['kimai_uri'] = "kimai.local/api/";
$config['default_user_password'] = 'password'; #default password for users that get transferred to kimai
$config['sync_users'] = false;
?>