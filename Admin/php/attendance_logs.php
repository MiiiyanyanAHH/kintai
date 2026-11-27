<?php session_start(); 

?>


<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ダッシュボード</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/attendance_logs.css">
  </head>

  <body>
    

    <main class="container">
      <h1>出退勤ログ</h1>
      <hr>

      <div id="table_scroll">
          <table class="table_1">
              <thead>
                  <tr>
                    <th>打刻日時</th>
                    <th>社員ID</th>
                    <th>社員名</th>
                    <th>打刻種別</th>
                </tr>
            </thead>

            <tbody>
              <?php 
                  $pdo = new PDO('mysql:host=localhost;dbname=kinta_db;charset=utf8','root','');
                  $sql = $pdo->prepare('select * from attendance_logs inner join employees on attendance_logs.employee_id = employees.employee_id order by time_stamp desc');
                  $sql->execute();
                      while($row = $sql->fetch()){
                        echo '<tr>';
                        echo '<td>'.$row['time_stamp'].'</td>';
                        echo '<td>'.$row['employee_id'].'</td>';
                        echo '<td>'.$row['employee_name'].'</td>';

                    // time_typeの値が「出勤」かどうかを判定する
                    if($row['time_type'] == '出勤'){
                         // 出勤の場合、出勤用のCSSクラスを付けて表示する
                        echo '<td><span class="type-attendance">出勤</span></td>';
                    }else{
                         // 出勤以外の場合、退勤用のCSSクラスを付けて表示する
                        echo '<td><span class="type-leave">退勤</span></td>';
                    }
                        echo '</tr>';
                  }
        ?>
    </tbody>
</table>
       </div>
    </main>   
    
    <?php include('footer.php'); ?>
    <a href="admin_dashboard_log.php" class="back-link">&larr; 戻る</a>
  </body>

</html>




  