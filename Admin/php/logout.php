
<?php
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $_SESSION = [];

    session_destroy();

       // ログイン画面へ移動
    header('Location: kanri_login.php');
    exit;

}

?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログアウト確認</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/logout.css">
</head>
<body>

    <main class="container">
        <div class="message">
            <h1>ログアウトしますか？</h1>
            <p>再度ログインが必要になります</p>
        </div>

        <div class="buttons">
            <a href="admin_dashboard.php" class="cancel-btn">キャンセル</a>
            <form action="logout.php" method = "post">
            <button type = "submit" class = "logout-btn">ログアウト</button>
            </form>
        </div>
        
    </main>
<?php include('footer.php');?>
</body>
</html>