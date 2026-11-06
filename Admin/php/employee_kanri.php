
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

//初期値はASKで
  $sort = $_GET['sort']?? 'ASC';

//不正な値が入っていないか確認
if($sort !== 'ASC' && $sort !== 'DESC'){
    $sort = 'ASC';
}


$sql = "SELECT e.employee_id,e.employee_name,e.hire_date,e.retire_date,a.active,a.authority
        FROM employees AS e
        LEFT JOIN administrators AS a
        ON e.employee_id = a.employee_id
        ORDER BY employee_id $sort";

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
     <div class = "container2"> 
      <div class="kanri-content">
        <h1>社員一覧</h1>
      </div>

      <!--新規社員登録と退職者一覧ボタン-->
      <div class = "btn">
        <a href="employee_register.php" class = "btn1">新規社員登録</a>
        <a href="#" class = "btn2">退職者一覧</a>
      </div>
     </div> 

      <!--並び替え-->
      <form action="" method="GET" class = "narabikae">
        <select name="sort" id="narabi" onchange="this.form.submit()">
          <option value="ASC" <?php if ($sort === 'ASC') echo 'selected'; ?>>昇順</option>
          <option value="DESC" <?php if ($sort === 'DESC') echo 'selected'; ?>>降順</option>
        </select>
      </form>

      

      <!--項目名-->
      <div class="kintai" id="table_scroll">
          <table class="preview-table">
            <thead>
            <tr>
              <th >社員ID</th>
              <th >社員名</th>
              <th >入社年月日</th>
              <th >退職年月日</th>
              <th >権限</th>
              <th >管理者ID</th>
              <th >操作</th>
            </tr>
            </thead>
           <!--DBからデータ取り出し-->
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
              }
              }else{
                $authorityNames = "非正規雇用";
              }
            ?>

            <?php
            $activeNames = "";
            if($employee['active'] !== null){
              switch((int)$employee['active']){
                case 0:
                $activeNames = "無効";
                break;
                case 1:
                $activeNames = "有効";
              }
            }else{
              $activeNames = "-";
            }
            ?>
            
            
            <tr>
                    <td><?php echo htmlspecialchars($employee['employee_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($employee['employee_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($employee['hire_date'], ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo htmlspecialchars($employee['retire_date']?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($authorityNames,  ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($activeNames, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><a href="#" class="syousai">詳細</a></td>
            </tr>
            <?php endforeach; ?>

          </table>

      </div>
    </main>
    <?php include('footer.php'); ?>
  </body>

</html>