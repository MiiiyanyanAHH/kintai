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
        <form action="preview.php"><!-- formのデータをpreview.phpに送るためのタグ -->
          <input type="month" name="month" required>
              <div class="menu-buttons">
                <button type = "submit" class="preview-btn">プレビュー</button>
                <button type = "submit" class="csv-btn" formaction="csv_download.php">CSVを出力</button>
              </div>
        </form>
      </div>
    </div>
<?php include('footer.php'); ?>
</body>
</html>