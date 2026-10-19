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
  <div class="form-area">
    <p>管理者ID <br><input type="text" name="id"></p>
    <p>パスワード<br><input type="password" name="password"></p>
    <button type = "submit">ログイン</button>
  </div>
  </form>
  <a href="home.php" class="back-link">&larr; 戻る</a>
  <?php include('footer.php');?>
  </div>
</body>
</html>