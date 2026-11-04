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
                          "select *, dates.work_date as display_date from
                            (select employee_id, work_date from attendance where employee_id = ? and work_date >= ? and work_date < ?
                            union
                            select employee_id, DATE(time_stamp) from attendance_logs where employee_id = ? and DATE(time_stamp) >= ? and DATE(time_stamp) < ?)
                            as dates

                            left join attendance on dates.work_date = attendance.work_date
                            and attendance.employee_id = ?
                          

                          left join 
                            (select  employee_id
                                  ,DATE(time_stamp) as work_date
                                  ,time_stamp as shukin_time
                                  ,row_number() over (
                                     partition by employee_id,DATE(time_stamp)
                                     order by time_stamp
                                   ) as rn
                          from attendance_logs
                          where time_type ='出勤') as shukin

                          on dates.work_date = shukin.work_date
                          and dates.employee_id = shukin.employee_id

                          left join(select  employee_id
                                  ,DATE(time_stamp) as work_date
                                  ,time_stamp as taikin_time
                                  ,row_number() over (
                                     partition by employee_id,DATE(time_stamp)
                                     order by time_stamp
                                   ) as rn
                          from attendance_logs
                          where time_type ='退勤') as taikin

                          on dates.work_date = taikin.work_date
                          and shukin.rn = taikin.rn
                          and dates.employee_id = taikin.employee_id
                          
                          order by dates.work_date asc, shukin.rn asc");

                  $sql->execute([
                    $employee_id
                    ,$start_date
                    ,$next_month
                    ,$employee_id
                    ,$start_date
                    ,$next_month
                    ,$employee_id
                    ]);
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
                          echo '<tr>'.'<td>'.$data['display_date'].'</td>';
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