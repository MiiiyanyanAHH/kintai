<?php
// データベースに接続
$pdo = new PDO(
    'mysql:host=localhost;dbname=kinta_db;charset=utf8mb4',
    'root',
    ''
);

try{
//エラー発生したら例外発生させる。
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);



$sql = "SELECT employee_id,employee_name,hire_date,retire_date
        FROM employees
        WHERE retire_date IS NOT NULL
        ORDER BY retire_date DESC";

        $stmt = $pdo->query($sql);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);


}catch(PDOException $e){
  //エラー発生時の処理
  echo "接続失敗:".$e->getMessage();
  exit();
}
?>




<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>退職者一覧</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/retired_list.css">
  </head>

  <body>
    <main class="container">
      <div class="yoko_con">
        <a href="employee_kanri.php" class="back-link">← 戻る</a>

        <div class="h1_container">
          <h1>退職者一覧</h1>
        </div>
      </div>
          <div class="kazoe_container">
            <div class="kazoe">
              退職者数：
              <span class="count-number">
                <?php echo count($employees); ?> 
              </span>
              名
            </div>
          </div>


          <!--項目名-->
      <div class="kintai" id="table_scroll">
          <table class="preview-table">
            <thead>
            <tr>
              <th >社員ID</th>
              <th >社員名</th>
              <th >入社年月日</th>
              <th >退職年月日</th>
            </tr>
            </thead>

            

              <tbody>
                <!--DBからデータ取り出し-->
                <?php foreach($employees as $employee):?> 
            
                  <tr>
                    <td><?php echo htmlspecialchars($employee['employee_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['employee_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($employee['hire_date'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($employee['retire_date'], ENT_QUOTES, 'UTF-8')); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>

            </table>

        </div>

    </main>
    <?php include('footer.php'); ?>
  </body>


</html>