<?php
//require_once(explode("wp-content", __FILE__)[0] . "wp-load.php");
require_once("wp-load.php");
require_once('migration-functions/add_users.php');

$serverName = '192.168.1.49\SQLEXPRESS, 51115';

$connOptions = array(
  'Database' => 'all',
  //'Authentication' => 'SqlPassword',
  'TrustServerCertificate' => true,
  'Uid' => 'db_admin',
  'PWD' => 'Childress1$'
);
$conn = sqlsrv_connect($serverName, $connOptions);

if($conn==false){
  die( FormatErrors( sqlsrv_errors()));
}

allorg_add_users($conn);

//1329
/*
$sql = "SELECT TOP (5) * FROM NewsArticle";
$getResults = sqlsrv_query($conn, $sql);
if($getResults == false){
  die(FormatErrors(sqlsrv_errors()));
}
else{
  while($article = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
    echo $article['Headline'] . '<br />';
  }
}
*/

function FormatErrors($errors){
  echo 'Error information:';
  foreach($errors as $error){
    echo 'SQLSTATE: ' . $error['SQLSTATE'] . '';
    echo 'Code: ' . $error['code'] . '';
    echo 'Message: ' . $error['message'] . '';
  }
}
