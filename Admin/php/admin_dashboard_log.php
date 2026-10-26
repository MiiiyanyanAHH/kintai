<?php session_start(); 

?>



<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ダッシュボード</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin_dashboard_log.css">
  </head>

  <body>
    <?php include('header.php'); ?>

    <main class="container">
      <h1>ログ</h1>

      <div class="menu-buttons">
        <a href="attendance_logs.php" class="menu-btn">出退勤ログ</a>
        <a href="#" class="menu-btn">管理者修正ログ</a>
       

    <?php
    // 権限が0のときだけ「社員管理」ボタンを表示する
    if (isset($_SESSION['authority']) && $_SESSION['authority'] == 0) {
    ?>
        <a href="#" class="menu-btn">社員管理</a>
    <?php
    }
    ?>
    
      </div>
    </main>   

    <?php include('footer.php'); ?>
  </body>

</html>