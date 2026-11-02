
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

// // 社員情報を取得
// $sql = "SELECT * FROM employees";
// $stmt = $pdo->query($sql);
// $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);//全部まとめて取り出してPDO::で列名をキーにした配列形式で取得

// //管理者情報も取得
// $sql = "SELECT * FROM administrators";
// $stmt = $pdo->query($sql);
// $administrators = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sql = "SELECT e.employee_id,e.employee_name,e.hire_date,e.retire_date,a.active,a.authority
        FROM employees AS e
        LEFT JOIN administrators AS a
        ON e.employee_id = a.employee_id" ;

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
    <title>社員一覧</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/employee_kanri.css">
  </head>

  <body>
    <?php include('header.php'); ?>

    <main class="container">
      <div class="shusei-content">
        <h1>社員一覧</h1>
      </div>
      <div class="kintai">
          <table>
            <tr>
              <th>社員ID</th>
              <th>社員名</th>
              <th>入社年月日</th>
              <th>退職年月日</th>
              <th>権限</th>
              <th>管理者ID</th>
              <th>操作</th>
            </tr>
            
          <?php foreach($employees as $employee):?>


            <?php
              $authorityNames = "";
              
            if($employee['authority'] !== null){

              switch((int)$employee['authority']){
                case 0:
                  $authorityNames = "管理者";
                  break;
                case 1:
                  $authorityNames = "正社員";
                  break;
                case 2:
                  $authorityNames = "非正規雇用";
                  break;
              }
              }
            ?>
            <tr>
                    <td><?php echo htmlspecialchars($employee['employee_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['employee_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($employee['hire_date'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo htmlspecialchars($employee['retire_date']?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($administrator['active']?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($authorityNames, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endforeach; ?>


          </table>

      </div>
    </main>   

    <?php include('footer.php'); ?>
  </body>

</html>