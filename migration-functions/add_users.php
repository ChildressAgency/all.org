<?php

function allorg_add_users($conn){
  $sql = "SELECT * FROM Admin WHERE IsInternal != 1";
  $user_info = [];

  $getResults = sqlsrv_query($conn, $sql);
  if($getResults == false){
    die(FormatErrors(sqlsrv_errors()));
  }
  else{
    while($user = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
      //$user_info['ID'] = $user['AdminId'];
      $user_info['user_pass'] = 'allorguserpassword';
      $user_info['user_login'] = $user['Username'];
      $user_info['user_nicename'] = $user['Username'];
      $user_info['user_email'] = $user['Email'];
      $user_info['first_name'] = $user['FirstName'];
      $user_info['last_name'] = $user['LastName'];
      $user_info['user_registered'] = $user['CreateDate'];

      $user_added = wp_insert_user($user_info);
      if(is_wp_error($user_added)){
        echo $user_added->get_error_message();
      }
      else{
        echo 'User added: ' . $user['Username'];
      }
    }
  }
  sqlsrv_free_stmt($getResults);
}