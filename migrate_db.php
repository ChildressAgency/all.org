<?php
//require_once(explode("wp-content", __FILE__)[0] . "wp-load.php");
require_once("wp-load.php");

$serverName = '192.168.1.49\SQLEXPRESS, 51115';

/* pdo test
try{
  $conn = new PDO("sqlsrv:Server=$serverName; Database=all", "childress_admin", "Childress1$");
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e){
  die(print_r($e->getMessage()));
}

$serverName = "localhost";
$connectionOptions = array(
    "Database" => "SampleDB",
    "Uid" => "sa",
    "PWD" => "your_password"
);
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

try{
  $sql = "SELECT * FROM NewsArticle WHERE ArticleId = 1329" ;
  $getArticle = $conn->prepare($sql);
  $getArticle->excecute();
  $articles = $getArticle->fetchAll(PDO::FETCH_ASSOC);

  foreach($articles as $article){
    echo $article['Headline'];
  }
}
catch(Exception $e){
  die(print_r($e->getMessage()));
}
*/
//phpinfo();

$connOptions = array(
  'Database' => 'all',
  //'Authentication' => 'SqlPassword',
  'TrustServerCertificate' => true,
  'Uid' => 'db_admin',
  'PWD' => 'Childress1$'
);
$conn = sqlsrv_connect($serverName, $connOptions);
//$conn = new PDO("sqlsrv:Server=$serverName; Database=all", "db_admin", "Childress1$");

if($conn==false){
  die( FormatErrors( sqlsrv_errors()));
}

//1329
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

function FormatErrors($errors){
  echo 'Error information:';
  foreach($errors as $error){
    echo 'SQLSTATE: ' . $error['SQLSTATE'] . '';
    echo 'Code: ' . $error['code'] . '';
    echo 'Message: ' . $error['message'] . '';
  }
}