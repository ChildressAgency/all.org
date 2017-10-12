<?php

function allorg_add_categories($conn){
  $sql = "SELECT CategoryName, CategoryId FROM NewsCategory WHERE SiteId = 1";

  $getResults = sqlsrv_query($conn, $sql);
  if($getResults == false){
    $success = false;
    $task = 'Getting categories from sql server.';
    $message = FormatErrors(sqlsrv_errors());

    allorgLogIt($success, $tasks, $message);
    die($message);
  }
  else{
    while($cat = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
      $cat_id = wp_create_category($cat['CategoryName']);

      $task = 'Creating ' . $cat['CategoryName'] . ' category.';
      if($cat_id == 0){
        $success = false;
        $message = 'Could not create category, no error returned.';

        allorgLogIt($success, $task, $message);
        echo 'There was a problem adding the ' . $cat['Categoryname'] . ' category.';
      }
      else{
        if($cat_id != $cat['CategoryId']){
          global $wpdb;
          $wpdb->update(
            'wp_terms',
            array(
              'term_id' => $cat['CategoryId']
            ),
            array(
              'term_id' => $cat_id
            )
          );
        }
        $success = true;

        allorgLogIt($success, $task);
        echo $cat['CategoryName'] . ' category created.<br />';
      }
    }
  }
}