
<?php
session_start();

$admin_id = null;
$password = null;


// セッションから社員情報を受け取る
$employee = $_SESSION['employee'] ?? null;

// 受け取ったデータを変数に入れる
if ($employee !== null) {
    $employee_id = $employee['id'];
    $employee_name = $employee['name'];
    $qr_date = $employee['date'];
    $admin_id = $employee['admin_id'];
    $password = $employee['password'];
}

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRコード発行</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/qr_hakkou.css">
</head>
<body>
    <script>
    //phpの値をJSON形式へ（QRコードの文字列作成）
    const employeeId = <?= json_encode($employee_id) ?>;
    const qrDate = <?= json_encode($qr_date) ?>;
    const qrData = employeeId + ',' + qrDate;
</script>

    <a href="employee_kanri.php" class="back-link">← 戻る</a>

    <main class="container">

        <div class="employee-info">
            <p>社員ID：<?= htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') ?></p>
            <p>社員名：<?= htmlspecialchars($employee_name, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <h1>QRコード発行</h1>
        <div class="qr-code" id="qrcode">
            
        </div>
        <p class="success-message">QRコードを発行しました。</p>
        <button type="button" class="print-btn" onclick="window.print();">印刷</button>

    </main>

    <?php include('footer.php'); ?>

<!--QRコード作成の外部ライブラリ読み込み-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>  
<script src="../js/qr_hakkou.js"></script>


<?php if(!empty($admin_id)):?>
<!--ここからモーダル-->
<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close-button">×</span>

    <div class="modal-title">
        <p class="title">アカウント発行完了</p>
        <p class = "modal-subtitle">管理者アカウントを発行しました</p>
    </div>

        <p class="employee-name">
            対象社員：<?= htmlspecialchars($employee_name, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <!--管理者ID-->
        <div class="account-item">
            <p class="label">管理者ID</p>
            <p class="value">
                <?= htmlspecialchars($admin_id, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <!--パスワード-->
        <div class="account-item">
            <p class="label">パスワード</p>
            <p class="value">
                <?= htmlspecialchars($password, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <div class="message">
            <p class="k">ご確認ください</p>
            <p>発行した管理者IDとパスワードを、対象の社員に伝えてください。</p>
        </div>
  </div>
</div>

<!--JS.モーダル設定-->
<script>
var modal = document.getElementById("myModal");
var closeButton = document.querySelector(".close-button");

window.onload = function() {
  setTimeout(function() {
    modal.style.display = "flex";
  }, 0);
}

closeButton.onclick = function() {
  modal.style.display = "none";
}

window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
<?php endif; ?>

</body>
</html>