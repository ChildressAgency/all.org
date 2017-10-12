<?php

function allorg_add_users($conn){

  $sql = "SELECT Username, Email, Firstname, Lastname, CreateDate FROM Admin WHERE IsInternal != 1";

  $getResults = sqlsrv_query($conn, $sql);
  if($getResults == false){
    $success = false;
    $task = 'Getting users from sql server.';
    $message = FormatErrors(sqlsrv_errors());

    allorgLogIt($success, $tasks, $message);
    die($message);
  }
  else{
    while($user = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
      //$user_info['ID'] = $user['AdminId'];
      $user_info = array(
        'user_pass' => 'allorguserpassword',
        'user_login' => $user['Username'],
        'user_nicename' => $user['Username'],
        'user_email' => $user['Email'],
        'first_name' => $user['Firstname'],
        'last_name' => $user['Lastname'],
        'user_registered' => $user['CreateDate']
      );
      //var_dump($user_info);

      $user_added = wp_insert_user($user_info);
      if(is_wp_error($user_added)){
        $success = false;
        $task = 'Adding user ' . $user['Username'] . ' to wpdb';
        $message = $user_added->get_error_message();

        allorgLogIt($success, $task, $message);
        echo 'There was a problem adding ' . $user['Username'] . '. Check the logs.';
      }
      else{
        $success = true;
        $task = 'Adding user ' . $user['Username'] . ' to wpdb';
        $message = '';

        allorgLogIt($success, $task, $message);
        echo 'User added: ' . $user['Username'] . '<br />';
      }
    }
  }
  sqlsrv_free_stmt($getResults);
}