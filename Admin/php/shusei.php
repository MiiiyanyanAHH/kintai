<?php session_start(); 
  $pdo = new PDO(
    'mysql:host=localhost;dbname=kinta_db;charset=utf8mb4',
    'root',
    ''
);


//表示ボタンを押した後、社員名（社員IDと対象年月を変数に保存する）
//2つ指定されているか
$rows = [];
if (isset ($_GET['employee_id']) && isset ($_GET['example'])){
    $employee_id = $_GET['employee_id'];
    $example = $_GET['example'];//例2026-10

  //選択した年月の初日を作る
    $start_date = $example.'-01';//例2026-10-01

  //その日付から一か月後の日付を計算する
    $next_month = date('Y-m-d', strtotime($start_date.'+1 month'));//例2026-11-01

  //２つのテーブルを社員IDと日付で左結合
    
$sql = $pdo->prepare(
    "WITH

    -- ① 勤務予定に連番を付ける 勤務予定を取得 ROW_NUMBER()はデータに連番を付ける機能 あとからデータを対応させるために連番を付けた
    plan_rows AS (
        SELECT
            employee_id,
            work_date,
            start_plan,
            end_plan,
            work_type,
            ROW_NUMBER() OVER (
                PARTITION BY employee_id, work_date
                ORDER BY start_plan
            ) AS rn
        FROM attendance
        WHERE employee_id = ?
          AND work_date >= ?
          AND work_date < ?
    ),

    -- ② 出勤ログに連番を付ける 出勤記録を取得
    shukin AS (
        SELECT
            employee_id,
            DATE(time_stamp) AS work_date,
            time_stamp AS shukin_time,
            ROW_NUMBER() OVER (
                PARTITION BY employee_id, DATE(time_stamp)
                ORDER BY time_stamp
            ) AS rn
        FROM attendance_logs
        WHERE employee_id = ?
          AND DATE(time_stamp) >= ?
          AND DATE(time_stamp) < ?
          AND time_type = '出勤'
    ),

    -- ③ 退勤ログに連番を付ける 退勤記録を取得
    taikin AS (
        SELECT
            employee_id,
            DATE(time_stamp) AS work_date,
            time_stamp AS taikin_time,
            ROW_NUMBER() OVER (
                PARTITION BY employee_id, DATE(time_stamp)
                ORDER BY time_stamp
            ) AS rn
        FROM attendance_logs
        WHERE employee_id = ?
          AND DATE(time_stamp) >= ?
          AND DATE(time_stamp) < ?
          AND time_type = '退勤'
    ),

    -- ④ 予定と打刻の連番をまとめる
    slots AS (
        SELECT employee_id, work_date, rn FROM plan_rows
        UNION
        SELECT employee_id, work_date, rn FROM shukin
        UNION
        SELECT employee_id, work_date, rn FROM taikin
    )

    -- ⑤ 連番を使って結合する
    SELECT
        slots.work_date AS display_date,
        plan_rows.start_plan,
        shukin.shukin_time,
        plan_rows.end_plan,
        taikin.taikin_time,
        plan_rows.work_type

    FROM slots

    LEFT JOIN plan_rows
        ON slots.employee_id = plan_rows.employee_id
        AND slots.work_date = plan_rows.work_date
        AND slots.rn = plan_rows.rn

    LEFT JOIN shukin
        ON slots.employee_id = shukin.employee_id
        AND slots.work_date = shukin.work_date
        AND slots.rn = shukin.rn

    LEFT JOIN taikin
        ON slots.employee_id = taikin.employee_id
        AND slots.work_date = taikin.work_date
        AND slots.rn = taikin.rn

    ORDER BY slots.work_date ASC, slots.rn ASC"
);

// SQLを実行する
$sql->execute([
    $employee_id,
    $start_date,
    $next_month,

    $employee_id,
    $start_date,
    $next_month,

    $employee_id,
    $start_date,
    $next_month
]);

// 検索結果を保存する
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
                  <option value="" selected disabled>社員名を選択してください</option>
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
                    foreach($rows as $data){
                      echo '<tr>';

                      echo '<td>'.$data['display_date'].'</td>';

                      // 出勤予定
                      echo '<td>'.(!empty($data['start_plan'])
                          ? substr($data['start_plan'],0,5) : '').'</td>';

                      // 実際の出勤
                      echo '<td>'.(!empty($data['shukin_time'])
                          ? substr($data['shukin_time'],11,5) : '').'</td>';

                      // 退勤予定
                      echo '<td>'.(!empty($data['end_plan'])
                          ? substr($data['end_plan'],0,5) : '').'</td>';

                      // 実際の退勤
                      echo '<td>'.(!empty($data['taikin_time'])
                          ? substr($data['taikin_time'],11,5) : '').'</td>';

                      // 勤務区分
                      echo '<td>'.($data['work_type'] ?? '').'</td>';

                      echo '</tr>';
}
              ?> 



          </table>

      </div>
    </main>   

    <?php include('footer.php'); ?>
  </body>

</html>