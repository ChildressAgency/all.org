<?php

function allorg_add_news_articles($conn){
  $sql = "SELECT NewsArticle.ArticleId, 
                  Admin.AdminId as post_author, 
                  NewsArticle.PostDate as post_date, 
                  NewsArticle.Body as post_content, 
                  NewsArticle.Headline as post_title, 
                  NewsArticle.Summary as post_excerpt,
                  Asset.ToolSectionUrl as image_folder,
                  Asset.FileName,
                  Asset.AssetName as image_title,
                  Asset.Caption,
                  Asset.AlternateText as alt_text,
                  Asset.AssetDate
          FROM NewsArticle
            LEFT JOIN Admin ON NewsArticle.ModifyAdminID = Admin.AdminId
            LEFT JOIN Asset ON NewsArticle.MainImageAssetFirstId = Asset.AssetId
          WHERE NewsArticle.SiteId = 1
            AND NewsArticle.Status = 'Published'";

  $getResults = sqlsrv_query($conn, $sql);
  if($getResults == false){
    $success = false;
    $task = 'Getting articles from sql server.';
    $message = FormetErrors(sqlsrv_errors());

    allorgLogIt($success, $task, $message);
    die($message);
  }
  else{
    while($article = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
      $cats = get_article_cats($conn, $article['ArticleId']);

      $post_info = array(
        'post_author' => $article['post_author'],
        'post_date' => $article['post_date'],
        'post_content' => $article['post_content'],
        'post_title' => $article['post_title'],
        'post_excerpt' => $article['post_excerpt'],
        'post_status' => 'publish',
        'post_category' => $cats
      );
      $post_id = wp_insert_post($post_info);
      if(is_wp_error($post_id)){
        $task = 'Adding article id ' . $article['ArticleId'];
        $success = false;
        $message = $post_id->get_error_message();

        allorgLogIt($success, $task, $message);
        echo 'Could not add article id ' . $article['ArticleId'];
      }
      else{
        $task = 'Adding article id ' . $article['ArticleId'];
        $success = true;
        $message = 'post id = ' . $post_id;

        allorgLogIt($success, $task, $message);

        if($article['FileName'] != '' && $article['FileName'] != null){
          allorg_add_image($post_id, $article);
        }
      }
    }
  }
}

function get_article_cats($conn, $article_id){
  $sql = "SELECT CategoryId FROM NewsCategoryArticles WHERE ArticleId = $article_id";
  $getResults = sqlsrv_query($conn, $sql);

  $task = 'Getting categories for article id ' . $article_id;
  if($getResults == false){
    $success = false;
    $message = FormatErrors(sqlsrv_errors());

    allorgLogIt($success, $task, $message);
    echo 'Could not find any categories for article id ' . $article_id . '. Setting to default 1.';
    //couldn't find category, set to 1
    $cats[] = 1;
  }
  else{
    while($cat = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
      $cats[] = $cat['CategoryId'];
    }
  }
  return $cats;
}

function allorg_add_image($post_id, $article){
  $image_folder = str_replace('\\', '/', $article['image_folder']);
  $image_url = 'https://all.org/assets/1' . $image_folder . $article['FileName'];
  $image_name = basename($image_url);

  $asset_date = strtotime($article['AssetDate']);
  $date_folder = date('Y/m', $asset_date);

  $file_upload = wp_upload_bits($image_name, null, file_get_contents($image_url), $date_folder);
  if($file_upload['error']){
    $success = false;
    $task = 'Uploading article (' . $article['ArticleId'] . ') image ' . $image_url . '.';
    $message = $file_upload['error'];
    allorgLogIt($success, $task, $message);
  }
  else{
    $image_type = wp_check_filetype($image_name);
    $attachment_info = array(
      'post_mime_type' => $image_type['type'],
      'post_title' => sanitize_file_name($image_name),
      'post_content' => '',
      'post_status' => 'inherit'
    );

    $attachment_id = wp_insert_attachment($attachment_info, $file_upload['file'], $post_id);
    if(is_wp_error($attachment_id)){
      $success = false;
      $task = 'Attaching image ' . $image_name . ' to post ' . $post_id;
      $message = $attachment_id->get_error_message();
      allorgLogIt($success, $task, $message);
    }
    else{
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_upload['file']);
      wp_update_attachment_metadata($attachment_id, $attachment_data);

      $meta_id = set_post_thumbnail($post_id, $attachment_id);
      if($meta_id == false){
        $success = false;
        $task = 'Attaching image (' . $attachment_id . ') to post (' . $post_id . ').';
        $message = 'Could not attach image to post';
      }
      else{
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $article['alt_text']);
        wp_update_post(array('ID' => $attachment_id, 'post_excerpt' => $article['Caption']));
      }
    }
  }
}