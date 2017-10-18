<?php
//require_once(explode("wp-content", __FILE__)[0] . "wp-load.php");
require_once("wp-load.php");
require_once('wp-admin/includes/taxonomy.php');
require_once('migration-functions/add_users.php');
require_once('migration-functions/add_cats.php');
require_once('migration-functions/add_news_articles.php');
require_once('migration-functions/add_faqs.php');

$serverName = '192.168.1.49\SQLEXPRESS, 51115';
$sql_server_uid = file_get_contents('sql_server_uid.txt');
$sql_server_pwd = file_get_contents('sql_server_pwd.txt');

$connOptions = array(
  'Database' => 'all',
  //'Authentication' => 'SqlPassword',
  'ReturnDatesAsStrings' => true,
  'TrustServerCertificate' => true,
  'Uid' => $sql_server_uid,
  'PWD' => $sql_server_pwd
);
$conn = sqlsrv_connect($serverName, $connOptions);

if($conn==false){
  $success = false;
  $task = 'Connecting to sql server';
  $message = FormatErrors(sqlsrv_errors());

  allorgLogIt($success, $task, $message);
  die($message);
}

// task list

//allorg_add_users($conn);
//allorg_add_categories($conn);
//allorg_add_news_articles($conn);
allorg_add_faqs($conn);

// end task list

sqlsrv_close($conn);

function FormatErrors($errors){
  echo 'Error information:';
  foreach($errors as $error){
    echo 'SQLSTATE: ' . $error['SQLSTATE'] . '';
    echo 'Code: ' . $error['code'] . '';
    echo 'Message: ' . $error['message'] . '';
  }
}

function allorgLogIt($success, $task = '', $message = ''){
  global $wpdb;
  $wpdb->insert(
    'migration_log',
    array(
      'success' => $success,
      'task' => $task,
      'message' => $message
    )
  );
}