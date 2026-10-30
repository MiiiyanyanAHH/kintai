

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>社員一覧</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/shusei.css">
  </head>

  <body>
    <?php include('header.php'); ?>

    <main class="container">
      <div class="shusei-content">
        <h1>社員一覧</h1>
            <!-- <?php
              $sql = "select * from employees";
              $stmt = $pdo->query($sql);
              $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?> -->
          <div class="grup">
    <form method="GET">
            <label>
              社員名
                <select name="employee_id">
                  <!-- <?php foreach($employees as $employee) { ?>
                      <option value="<?= htmlspecialchars($employee['employee_id'],ENT_QUOTES, 'UTF-8')?>">
                          <?=htmlspecialchars($employee['employee_name'],ENT_QUOTES,'UTF-8') ?>
                      </option>
                  <?php } ?> -->
               </select>
            </label>  

            <label>
              対象年月
              <input type="month" name="example">
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
            
            
          <!-- <?php 
          //ここから続き
                    while($rows->fetch()){
                          echo '<tr>'.'<td>'.$row['time_stamp'].'</td>';
                          echo '<td>'.$row['employee_id'].'</td>';
                          echo '<td>'.$row['employee_name'].'</td>';
                          echo '<td>'.$row['time_type'].'</td>'.'</tr>';
                    }
              ?>  -->



          </table>

      </div>
    </main>   

    <?php include('footer.php'); ?>
  </body>

</html>