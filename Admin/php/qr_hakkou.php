
<?php
session_start();

// セッションから社員情報を受け取る
$employee = $_SESSION['employee'] ?? null;

// 受け取ったデータを変数に入れる
if ($employee !== null) {
    $employee_id = $employee['id'];
    $employee_name = $employee['name'];
    $qr_date = $employee['date'];
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
    //phpの値をJSON形式へ
    const employeeId = <?= json_encode($employee_id) ?>;
    const qrDate = <?= json_encode($qr_date) ?>;
    const qrData = employeeId + ',' + qrDate;
</script>

    <a href="admin.php" class="back-link">← 戻る</a>

    <main class="container">

        <div class="employee-info">
            <p>社員ID：<?= htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8') ?></p>
            <p>社員名：<?= htmlspecialchars($employee_name, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <h1>QRコード発行</h1>
        <div class="qr-code" id="qrcode">
            
        </div>
        <p class="success-message">QRコードを発行しました。</p>
        <button type="button" class="print-btn">印刷</button>

    </main>

    <?php include('footer.php'); ?>

    

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>  
<script src="../js/qr_hakkou.js"></script>
</body>
</html>