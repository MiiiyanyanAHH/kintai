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

    <a href="admin.php" class="back-link">← 戻る</a>

    <main class="container">

        <div class="employee-info">
            <p>社員ID：000007</p>
            <p>社員名：仲程明日香</p>
        </div>
        <h1>QRコード発行</h1>
        <div class="qr-code">
            QRコードの画像がくる場所
        </div>
        <p class="success-message">QRコードを発行しました。</p>
        <button type="button" class="print-btn">印刷</button>

    </main>

    <?php include('footer.php'); ?>

</body>
</html>