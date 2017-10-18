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
    $cat_ids = array();
    while($cat = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
      $cat_id = wp_create_category($cat['CategoryName']);

      $task = 'Creating ' . $cat['CategoryName'] . ' category.';
      if($cat_id == 0){
        $success = false;
        $message = 'Could not create category, no error returned.';

        allorgLogIt($success, $task, $message);
        echo 'There was a problem adding the ' . $cat['CategoryName'] . ' category.';
      }
      else{
        global $wpdb;

        $cat_id_twenty = $cat_id + 20;
        $wpdb->update(
          'wp_terms',
          array(
            'term_id' => $cat_id_twenty
          ),
          array(
            'term_id' => $cat_id
          )
          );
        $cat_ids[$cat_id_twenty] = $cat['CategoryId'];

        $success = true;
        allorgLogIt($success, $task);
        echo $cat['CategoryName'] . ' category created.<br />';
      }
    }
    allorg_update_cat_ids($cat_ids);
  }
}

function allorg_update_cat_ids($cat_ids){
  global $wpdb;
  var_dump($cat_ids);

  $wpdb->update(
    'wp_terms',
    array('term_id' => 99),
    array('name' => 'Uncategorized')
  );

  foreach($cat_ids as $old_id => $new_id){
    $result = $wpdb->update(
      'wp_terms',
      array(
        'term_id' => $new_id
      ),
      array(
        'term_id' => $old_id
      )
    );

    if($result == false){
      $success = false;
      $task = 'Updating Category ids.';
      $message = 'Could not update category ' . $old_id . ' to ' . $new_id;
      allorgLogIt($success, $task, $message);
    }
  } 
}