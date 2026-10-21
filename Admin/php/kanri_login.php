<?php session_start(); 
// ログイン状態などのセッションを使えるようにする。
//データベースに接続 
$pdo = new PDO('mysql:host=localhost; dbname=kinta_db; charset=utf8mb4', 'root', '',
      [    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]
);

// エラーメッセージを初期化
$error = '';

// ログインボタンが押された時
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 入力された管理者IDとパスワードを取得。
  $id = $_POST['id'] ?? '';
  $password = $_POST['password'] ?? '';

  // 管理者IDを使ってデータベースを検索
  //$sql = "SELECT admin_id, password
  //        FROM administrators
  //        WHERE admin_id = :id";
  // ↑activとauthrityも一緒に取得するようsqlを変更する

  $sql = "SELECT admin_id, password, active, authority, employee_id
          FROM administrators
          WHERE admin_id = :id";

  $stmt = $pdo -> prepare($sql);
  $stmt -> execute(['id' => $id]);
  
  $admin = $stmt -> fetch();

  // 管理者IDが存在し、パスワードが一致するか判定
  if($admin && $password === $admin['password']) {

    // アカウントが無効ならログインさせない
    if($admin['active'] == 0) {
      $error = 'このアカウントは無効化されています。';
    } else {
      // ログイン成功
      session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['admin_id'];

    // セッションに権限(authority)と社員ID(employee_id)を保存する。
      $_SESSION['authority'] = $admin['authority'];
      $_SESSION['employee_id'] = $admin['employee_id'];
    }

    // 管理者画面へ移動
    header('Location: admin_dashboard.php');
    exit;

  } else {
    // ログイン失敗
    $error = '管理者IDまたはパスワードが違います。';
  }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <link rel="stylesheet" href="../css/common.css">
  <link rel="stylesheet" href="../css/login.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理者ログイン画面</title>
</head>
<body>
  <div class="container">
  <h1>管理者ログイン</h1>

<form action="kanri_login.php" method="post">
  <div class="form-area">


<?php if($error !== ''): ?>
    <p class= "error">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </p>
  <?php endif; ?>

    <p>管理者ID <br><input type="text" name="id" required></p>
    <p>パスワード<br><input type="password" name="password" required></p>
    <button type = "submit">ログイン</button>
  </div>
  </form>
  <a href="home.php" class="back-link">&larr; 戻る</a>
  <?php include('footer.php');?>
  </div>
</body>
</html>