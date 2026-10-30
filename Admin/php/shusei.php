<?php session_start(); 
  $pdo = new PDO(
    'mysql:host=localhost;dbname=kinta_db;charset=utf8mb4',
    'root',
    ''
);


//表示ボタンを押した後、社員名（社員IDと対象年月を変数に保存する）
//2つ指定されているか
if (isset ($_GET['employee_id']) && isset ($_GET['example'])){
    $employee_id = $_GET['employee_id'];
    $example = $_GET['example'];//例2026-10

  //選択した年月の初日を作る
    $start_date = $example.'-01';//例2026-10-01

  //その日付から一か月後の日付を計算する
    $next_month = date('Y-m-d', strtotime($start_date.'+1 month'));//例2026-11-01

  //２つのテーブルを社員IDと日付で左結合
     $sql = $pdo->prepare(
             'select max(case when time_type = "出勤" then time_stamp end ) as shukin_time 
                     ,max(case when time_type = "退勤" then time_stamp end) as taikin_time
                     ,attendance.work_date
                     ,max(attendance.start_plan) as start_plan
                     ,max(attendance.end_plan) as end_plan
                     ,max(attendance.work_type) as work_type
             from attendance left join attendance_logs 
                    on  attendance.employee_id = attendance_logs.employee_id and attendance.work_date = date(attendance_logs.time_stamp)
                    where attendance.employee_id = ? and attendance.work_date is not null and attendance.work_date >= ? and attendance.work_date < ?
                    group by attendance.employee_id, attendance.work_date
                    order by attendance.work_date asc'
                );
                  $sql->execute([$employee_id, $start_date, $next_month]);
                  //検索結果をまとめて変数に保存する
                  $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
    
}else {
  echo 'エラー';
}


?>


<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>月別勤怠修正</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/shusei.css">
  </head>

  <body>
    <?php include('header.php'); ?>

    <main class="container">
      <div class="shusei-content">
        <h1>月別勤怠修正</h1>
            <?php
              $sql = "select * from employees";
              $stmt = $pdo->query($sql);
              $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
          <div class="grup">
    <form method="GET">
            <label>
              社員名
                <select name="employee_id">
                  <?php foreach($employees as $employee) { ?>
                      <option value="<?= htmlspecialchars($employee['employee_id'],ENT_QUOTES, 'UTF-8')?>"
                      <?= (isset($employee_id) && $employee_id == $employee['employee_id'])? 'selected':'' ?>
                      >
                      
                          <?=htmlspecialchars($employee['employee_name'],ENT_QUOTES,'UTF-8') ?>
                      </option>
                  <?php } ?>
               </select>
            </label>  

            <label>
              対象年月
              <input type="month" name="example"  value="<?= htmlspecialchars($example ?? '', ENT_QUOTES, 'UTF-8')?>">
            </label>

          <button type = "submit" class="preview-btn">表示</button>
  </form>
        </div>
      </div>
      <div class="kintai">
          <table>
            <tr>
              <th>日付</th>
              <th>出勤予定</th>
              <th>出勤</th>
              <th>退勤予定</th>
              <th>退勤</th>
              <th>勤務区分</th>
            </tr>
            
            
          <?php 
                    foreach($rows as $data){//一行ずつ取り出す
                          echo '<tr>'.'<td>'.$data['work_date'].'</td>';
                          echo '<td>'.substr($data['start_plan'],0,5).'</td>';
                          echo '<td>'.substr($data['shukin_time'], 11,5).'</td>';
                          echo '<td>'.substr($data['end_plan'],0,5).'</td>';
                          echo '<td>'.substr($data['taikin_time'],11,5).'</td>';
                          echo '<td>'.$data['work_type'].'</td>'
                          .'</tr>';
                    }
              ?> 



          </table>

      </div>
    </main>   

    <?php include('footer.php'); ?>
  </body>

</html>