<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CSV出力</title>
  <link rel="stylesheet" href="../css/common.css">
  <link rel="stylesheet" href="../css/csv_export.css">
</head>
<body>

<?php include('header.php'); ?>
    <div class="container">
      <h1>CSV出力</h1> 
      <div class="grup">
      <p>出力する年月</p>
    <input type="month" name="example">
      <div class="menu-buttons">
        <a href="#"><button type = "button" class="preview-btn">プレビュー</button></a>
        <a href="#"><button type = "button" class="csv-btn">CSVを出力</button></a>
      </div>
    </div>
    </div>
<?php include('footer.php'); ?>
</body>
</html>