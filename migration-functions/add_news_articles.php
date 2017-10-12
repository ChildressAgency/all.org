<?php

function allorg_add_news_articles($conn){
  $sql = "SELECT Admin.AdminId as post_author, NewsArticle.PostDate as post_date, NewsArticle.Body as post_content, NewsArticle.Headline as post_title, NewsArticle.Summary as post_excerpt
          FROM NewsArticle
            INNER JOIN Admin ON NewsArticle.ModifyAdminID = Admin.AdminId
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
      $task = 'Adding article id ' . $article['ArticleId'];
      if(is_wp_error($post_id)){
        $success = false;
        $message = $post_id->get_error_message();

        allorgLogIt($success, $task, $message);
        echo 'Could not add article id ' . $article['ArticleId'];
      }
      else{
        $success = true;
        $message = 'post id = ' . $post_id;

        allorgLogIt($success, $task, $message);
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