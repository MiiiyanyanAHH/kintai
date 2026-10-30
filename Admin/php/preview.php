<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/common.css">
  <link rel="stylesheet" href="../css/preview.css">
  <title>プレビュー</title>
</head>

<?php
      session_start();

?>

<?php 
      // データベースに接続
      $pdo = new PDO('mysql:host=localhost;dbname=kinta_db;charset=utf8','root','');

      // 選択された年月を取得する
      // まだ年月が送られていない場合は、空欄にする
      $month = $_GET['month'] ?? "";

      // 年月の形式を確認する
      if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        exit('年月の指定が正しくありません。');
      }

      /* 月の初日と翌月の初日を取得する [月末の日付を計算する代わりに、翌月の1日を取得してその日の前のデータを選ぶ。] */
      $startDate = $month. '-01';
      $endDate = date('Y-m-d', strtotime($startDate. '+1month'));

      /*  出勤予定と退勤予定の時刻と、出退勤ログテーブルから実際の出退勤時刻を取得するSQL */
      $sql = "
        SELECT e.employee_id,
               e.employee_name,
               a.work_date,
               a.start_plan,
               a.end_plan,
               a.work_type,

        Max(
          /* CASE WHENは、出勤の打刻だった場合だけ、その時刻を取り出す */
          CASE
              WHEN l.time_type = '出勤' /* 打刻区分を確認  */
              THEN TIME(l.time_stamp)  /* 打刻日時から時刻を取り出す  */
          END
        ) AS start_time ,/* 取得した時刻に start_time という名前を付ける */

        Max(
          CASE
              WHEN l.time_type = '退勤'
              THEN TIME(l.time_stamp)
          END
        ) AS end_time

        FROM attendance AS a 
        
        INNER JOIN employees AS e
              ON a.employee_id = e.employee_id 

        LEFT JOIN attendance_logs AS l 
             ON a.employee_id = l.employee_id 
        
        AND DATE(l.time_stamp) = a.work_date
        WHERE a.work_date >= :start_date
        AND a.work_date < :end_date

        GROUP BY
                e.employee_id,
                e.employee_name,
                a.work_date,
                a.start_plan,
                a.end_plan,
                a.work_type

        ORDER BY
                e.employee_id,
                a.work_date ";

      // SQLを実行する準備(データベースから勤怠データを取得する処理を書く)
      $stmt = $pdo->prepare($sql);

      // 年月の範囲を指定して実行
      $stmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate
      ]);

      // 取得したデータを配列にする
      $attendanceList = $stmt->fetchAll(PDO::FETCH_ASSOC);            
?>

<body>
  <main class="container">
    <div>
  <a href="home.php" class="back-link">&larr; 戻る</a>
<!-- 年月が送られていないときは空欄にしたいからここを変更する。
      <p>2026年9月 プレビュー</p>                          !-->
  
  <table>
    <thead>
      <tr>
        <th>社員ID</th>
        <th>社員名</th>
        <th>日付</th>
        <th>出勤予定</th>
        <th>退勤予定</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>勤務区分</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach($attendanceList as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['employee_id']) ?> </td>
          <td><?= htmlspecialchars($row['employee_name']) ?> </td>
          <td><?= htmlspecialchars($row['work_date']) ?> </td>
          <td><?= htmlspecialchars($row['start_plan']) ?> </td>
          <td><?= htmlspecialchars($row['end_plan']) ?> </td>
          <!--  ?? '' は出勤の打刻がない場合に、エラーにならず空欄で表示するため　 !-->
          <td><?= htmlspecialchars($row['start_time'] ?? '') ?> </td>
          <td><?= htmlspecialchars($row['end_time']) ?? '' ?> </td>
          <td><?= htmlspecialchars($row['work_type']) ?> </td>
        </tr>
    
        <?php endforeach; ?>
    </tbody>


</table>
</div>

</main>
   <?php include('footer.php'); ?> 
</body>
</html>