<?php session_start(); 

$admin_id = $_SESSION['admin_id']?? '';

  $pdo = new PDO(
    'mysql:host=localhost;dbname=kinta_db;charset=utf8mb4',
    'root',
    ''
);

//社員一覧を取得
$stmt = $pdo->query("select * from employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rows = [];
$employee_name = '';

//表示ボタンが押され、社員IDと対象年月が指定されているか
if(isset($_GET['search']) && !empty($_GET['employee_id']) && !empty($_GET['example'])){
  $employee_id = $_GET['employee_id'];
    $example = $_GET['example'];
    // 社員IDから社員名を取得
    foreach ($employees as $employee) {
        if ($employee['employee_id'] == $employee_id) {
            $employee_name = $employee['employee_name'];
            break;
        }
    }
}


//表示ボタンを押した後、社員名（社員IDと対象年月を変数に保存する）
//2つ指定されているか
$rows = [];
if (isset ($_GET['search']) &&
    !empty($_GET['employee_id']) &&
    !empty($_GET['example'])
  ){
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
            DATE(time_stamp- INTERVAL 4 HOUR - INTERVAL 1 SECOND) AS work_date,
            time_stamp AS shukin_time,
            ROW_NUMBER() OVER (
                PARTITION BY employee_id, DATE(time_stamp- INTERVAL 4 HOUR - INTERVAL 1 SECOND)
                ORDER BY time_stamp
            ) AS rn
        FROM attendance_logs
        WHERE employee_id = ?
          AND DATE(time_stamp -INTERVAL 4 HOUR - INTERVAL 1 SECOND) >= ?
          AND DATE(time_stamp - INTERVAL 4 HOUR - INTERVAL 1 SECOND) < ?
          AND time_type = '出勤'
    ),

    -- ③ 退勤ログに連番を付ける 退勤記録を取得
    taikin AS (
        SELECT
            employee_id,
            DATE(time_stamp- INTERVAL 4 HOUR - INTERVAL 1 SECOND) AS work_date,
            time_stamp AS taikin_time,
            ROW_NUMBER() OVER (
                PARTITION BY employee_id, DATE(time_stamp - INTERVAL 4 HOUR - INTERVAL 1 SECOND)
                ORDER BY time_stamp
            ) AS rn
        FROM attendance_logs
        WHERE employee_id = ?
          AND DATE(time_stamp - INTERVAL 4 HOUR - INTERVAL 1 SECOND) >= ?
          AND DATE(time_stamp - INTERVAL 4 HOUR - INTERVAL 1 SECOND) < ?
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
        plan_rows.work_type,
        shukin.shukin_time AS original_start_timestamp,
        taikin.taikin_time AS original_end_timestamp

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
//修正済みの勤怠データを取得
$detail_stmt = $pdo->prepare(
  "select * from attendance_details
  where employee_id = ?
  and work_date >= ?
  and work_date < ?
  and is_deleted = 0"
);

$detail_stmt->execute([
  $employee_id,
  $start_date,
  $next_month
]);

$details = $detail_stmt->fetchAll(PDO::FETCH_ASSOC);
//元の勤怠データと修正データを照合する
foreach($rows as &$row){
  foreach ($details as $detail){
    if(
        $row['original_start_timestamp'] == $detail['original_start_timestamp'] &&
        $row['original_end_timestamp'] == $detail['original_end_timestamp'] &&
        $row['display_date'] == $detail['work_date']
    ){
      //修正データが見つかった場合
      $row['detail'] = $detail;
      break;
    }
  }
}
unset($row);
  //追加された勤怠データを一覧に追加
  foreach($details as $detail){

  //追加データだけを対象にする
  if($detail['operation_type'] == '追加'){
    $rows[] = [
      'display_date' => $detail['work_date'],
      'start_plan' => null,
      'shukin_time' => null,
      'end_plan' => null,
      'taikin_time' => null,
      'work_type' => $detail['work_type'],
      'original_start_timestamp' => null,
      'original_end_timestamp' => null,
      'detail' => $detail
    ];

  }
  }
  //日付順に並び変える（古い日付から新しい日付）
  usort($rows,function($a, $b){
    //日付を比較
    $dateCompare = strcmp($a['display_date'], $b['display_date']);

    //日付が違う場合は日付順を優先
    if($dateCompare !== 0){
      return $dateCompare;
    }
    //同じ日付なら出勤時刻を比較
    $timeA = $a['detail']['start_time'] ?? $a['shukin_time'] ?? '';
    $timeB = $b['detail']['start_time'] ?? $b['shukin_time'] ?? '';

    return strcmp($timeA,$timeB);
  });
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
    <link rel="stylesheet" href="../css/shusei_modal.css">
  </head>

  <body>
    <?php include('header.php'); ?>

    <main class="container">
      <div class="shusei-content">
        <h1>月別勤怠修正</h1>
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

          <button type = "submit" name="search" value="1" class="preview-btn">表示</button>
  </form>
   </div>
<!-- 表示後の処理 -->
 <?php if(isset($_GET['search']) && !empty($example)): ?>
  <!-- 社員ID・社員名・対象年月 -->
    <div class="employee-info">
      <span class="employee-name">
      <?= htmlspecialchars($employee_id) ?>
      <?= htmlspecialchars($employee_name) ?>
      </span> 
    <span class="employee-month">
      <?= htmlspecialchars($example) ?>
    </span>
    </div>
    <!-- 操作ボタン -->
      <div class="attendance-buttons">
        <!-- 勤怠追加ボタン -->
        <button type="button" class="add-btn">＋ 勤怠追加</button>
        <!-- 削除モード開始 -->
         <button type="button" id="deleteModeBtn">－ 勤怠削除</button>

         <!-- 削除モード中だけ表示 -->
          <button type="button" id="cancelDeleteBtn" hidden>キャンセル</button>
          <button type="button" id="confirmDeleteBtn" hidden disabled>選択した勤怠を削除</button>
      </div>
    <!-- 勤怠一覧（チェックボックス・修正ボタン付き） -->
    
    <div class="kintai">
          <table>
            <tr>
              <th class="delete-column" hidden>選択</th>
              <th>日付</th>
              <th>出勤予定</th>
              <th>出勤</th>
              <th>退勤予定</th>
              <th>退勤</th>
              <th>勤務区分</th>
              <th>操作</th>
            </tr>
            
            
          <?php 
                    foreach($rows as $data){
                      echo '<tr
                        data-employee-id="'.htmlspecialchars($employee_id, ENT_QUOTES, 'UTF-8').'"
                        data-work-date="'.htmlspecialchars($data['display_date'], ENT_QUOTES,'UTF-8').'"
                        data-original-start="'.htmlspecialchars($data['original_start_timestamp'] ?? '', ENT_QUOTES, 'UTF-8').'"
                        data-original-end="'.htmlspecialchars($data['original_end_timestamp'] ?? '', ENT_QUOTES, 'UTF-8').'"
                      >';//各行にID、勤務日、修正前の出勤、退勤を保存する

                      //チェックボックス
                      $detail_id = $data['detail']['detail_id'] ?? '';

                      echo '<td class="delete-column" hidden><input type="checkbox"
                           class="delete-checkbox" name="delete_ids[]"
                           value="'.htmlspecialchars((string)$detail_id, ENT_QUOTES,'UTF-8').'"
                           '.($detail_id === '' ? 'disabled' : '').'
                      ></td>';

                      echo '<td>'.htmlspecialchars($data['display_date'], ENT_QUOTES, 'UTF-8').'</td>';

                      // 出勤予定
                      echo '<td>'.(!empty($data['start_plan'])
                          ? substr($data['start_plan'],0,5) : '').'</td>';

                      // 実際の出勤
                      echo '<td>'.(
                          isset($data['detail'])
                              ? (!empty($data['detail']['start_time'])
                                  ? substr($data['detail']['start_time'], 0, 5)
                                  : '')
                              : (!empty($data['shukin_time'])
                                  ? substr($data['shukin_time'], 11, 5)
                                  : '')
                      ).'</td>';

                      // 退勤予定
                      echo '<td>'.(!empty($data['end_plan'])
                          ? substr($data['end_plan'],0,5) : '').'</td>';

                      // 実際の退勤
                      echo '<td>'.(
                          isset($data['detail'])
                              ? (!empty($data['detail']['end_time'])
                                  ? substr($data['detail']['end_time'], 0, 5)
                                  : '')
                              : (!empty($data['taikin_time'])
                                  ? substr($data['taikin_time'], 11, 5)
                                  : '')
                      ).'</td>';

                     //勤務区分
                     $work_type = '';
                     if(isset($data['detail'])){
                      //管理医者が修正したデータがある場合
                      $work_type = $data['detail']['work_type'] ?? '';
                     } elseif(!empty($data['work_type'])){
                      //勤務予定に勤務区分がある場合
                      $work_type = $data['work_type'];
                     } elseif(!empty($data['shukin_time'])|| !empty($data['taikin_time'])){
                      //QR打刻がある場合
                      $work_type = '通常勤務';
                     }

                     //画面に表示
                     echo '<td>'.htmlspecialchars(
                      $work_type,
                      ENT_QUOTES,
                      'UTF-8'
                     ).'</td>';
                      
                      //修正ボタン
                      echo '<td><button type="button" class="edit-btn">修正</button></td>';
                      echo '</tr>';
               }
              ?> 
          </table>
      </div>

      <!-- ポップアップ -->
      <div id="addModal" class="modal" hidden>
        <div class="modal-content">
            <h2>勤怠追加</h2>

            <form id="addForm">
                  <input type="hidden" id="addEmployeeId"
                  value="<?= htmlspecialchars($employee_id,ENT_QUOTES,'UTF-8')?>">     

                  <label>
                    勤務日
                    <input type="date" id="addDate" required>
                  </label>

                  <label>
                    出勤時刻
                    <input type="time" id="addStartTime">
                  </label>

                  <label>
                    退勤時刻
                    <input type="time" id="addEndTime">
                  </label>

                  <label>
                    勤務区分
                    <select id="addWorkType" required>
                      <option value="通常勤務">通常勤務</option>
                      <option value="有給">有給</option>
                      <option value="欠勤">欠勤</option>
                    </select>
                  </label>

                    <div class="modal-buttons">
                      <button type="button" id="closeModal">キャンセル</button>
                      <button type="submit">追加する</button>
                    </div>
             </form>
        </div>
      </div> 
<?php endif; ?>

        </div>
     
      
    </main>   

    <?php include('footer.php'); ?>
    <script>
      const table = document.querySelector('.kintai table');

      if(table){
      //テーブル全体にクリックイベントを設定
      table.addEventListener('click',function(event){
        //押されたボタンを取得
        const button = event.target.closest('button');

        if(!button) return;

        //ボタンがある行を取得
        const row = button.closest('tr');
        const cells = row.querySelectorAll('td');
        const actionCell = cells[7];

        //①修正ボタンが押されたとき
        if(button.classList.contains('edit-btn')){
          const startTime = cells[3].textContent.trim();
          const endTime = cells[5].textContent.trim();
          const workType = cells[6].textContent.trim();

          //修正前の値を保存しておく
          row.dataset.startTime = startTime;
          row.dataset.endTime = endTime;
          row.dataset.workType = workType;

          //出勤時間
          cells[3].innerHTML = `<input type="time" value="${startTime}">`;

          //退勤時間
          cells[5].innerHTML = `<input type="time" value="${endTime}">`;

          //勤務区分
          cells[6].innerHTML = `<select>
                               <option value="通常勤務" ${workType === '通常勤務' ? 'selected':''}>通常勤務</option>
                               <option value="有給" ${workType === '有給' ? 'selected' : ''}>有給</option>
                               <option value="欠勤" ${workType ===  '欠勤' ? 'selected' : ''}>欠勤</option>
                               </select>
                            `;

          //操作ボタンを切り替える
          actionCell.innerHTML = `
              <button type="button" class="save-btn">保存</button>
              <button type="button" class="cancel-btn">キャンセル</button>
          `;
        }

        //②保存ボタンが押されたとき
        else if(button.classList.contains('save-btn')){
            const employeeId = row.dataset.employeeId;
            const workDate = row.dataset.workDate;
            const originalStart = row.dataset.originalStart;
            const originalEnd = row.dataset.originalEnd;

          const newStartTime = cells[3].querySelector('input').value;
          const newEndTime = cells[5].querySelector('input').value;
          const newWorkType = cells[6].querySelector('select').value;

        console.log({
          employeeId,
          workDate,
          originalStart,
          originalEnd,
          newStartTime,
          newEndTime,
          newWorkType
      });


          fetch('shusei_save.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
        employee_id: employeeId,
        work_date: workDate,
        original_start_timestamp: originalStart,
        original_end_timestamp: originalEnd,
        start_time: newStartTime,
        end_time: newEndTime,
        work_type: newWorkType
    })
})
.then(response => response.json())
.then(data => {
    console.log(data);


    if(data.success){
      alert('勤怠を保存しました。');
      location.reload();
 }else { 
  alert(data.message);
  
 }
})
 .catch(error => {
    console.error('保存エラー：',error);
    alert('保存中にエラーが発生しました。');
 });

 }

        //③キャンセルボタンが押されたとき
        else if(button.classList.contains('cancel-btn')){
          //修正前の値に戻す
            cells[3].textContent = row.dataset.startTime;
            cells[5].textContent = row.dataset.endTime;
            cells[6].textContent = row.dataset.workType;

            //操作欄を修正ボタンに戻す
            actionCell.innerHTML = `
              <button type="button" class = "edit-btn">修正</button>
            `;
        }

      });
 }
      //ポップアップを取得する
      const addModal = document.getElementById('addModal');
      const addBtn = document.querySelector('.add-btn');
      const closeModal = document.getElementById('closeModal');

      //クリックしたときにポップアップを開閉する処理
      //勤怠追加ボタンを押したとき
      addBtn.addEventListener('click', function(){
        addModal.hidden = false;
      });

      //キャンセルボタンを押したとき
      closeModal.addEventListener('click', function(){
        addModal.hidden = true;
      });

      //勤怠追加フォーム
      const addForm = document.getElementById('addForm');

      addForm.addEventListener('submit', function(event){
        //フォーム送信によるページ更新を防ぐ
        event.preventDefault();

        //入力内容を取得
        const employeeId = document.getElementById('addEmployeeId').value;
        const workDate = document.getElementById('addDate').value;
        const startTime = document.getElementById('addStartTime').value;
        const endTime = document.getElementById('addEndTime').value;
        const workType = document.getElementById('addWorkType').value;

        //入力内容をPHPに送信
        fetch('shusei_save.php',{
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            employee_id: employeeId,
            work_date: workDate,
            start_time: startTime,
            end_time: endTime,
            work_type:workType,
            operation_type: '追加'

          })
        })
       .then(response => response.json()).then(data=> {
        console.log(data);

        if(data.success){
          alert('勤怠を追加しました。');
          location.reload();
        } else {
          alert(data.message);
        }
       })
       .catch(error => {
        console.error('追加エラー:', error);
        alert('勤怠の追加に失敗しました。');
      });
      //submitイベントの終了
 });


 //削除モードのボタンを取得
 const deleteModeBtn = document.getElementById('deleteModeBtn');
 const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
 const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

 //チェックボックスがある列を取得
 const deleteColumns = document.querySelectorAll('.delete-column');

//チェックボックスを取得
const deleteCheckboxes = document.querySelectorAll('.delete-checkbox');

//チェックされた数に応じて削除ボタンを切り替える
function updateDeleteButton(){
  //チェックされているチェックボックスの数を数える
  const checkedCount = document.querySelectorAll(
    '.delete-checkbox:checked'
  ).length;

  //1件以上選択されていれば削除ボタンを有効にする
  confirmDeleteBtn.disabled = checkedCount == 0;
}

//チェックボックスに変更があったとき
deleteCheckboxes.forEach(checkbox => {
  checkbox.addEventListener('change',updateDeleteButton);
});

//削除モードを開始
deleteModeBtn.addEventListener('click', function(){
  //チェックボックスを表示
  deleteColumns.forEach(column =>{
    column.hidden = false;
  });
  //ボタンを切り替える
  deleteModeBtn.hidden = true;
  cancelDeleteBtn.hidden = false;
  confirmDeleteBtn.hidden = false;
});

//削除モードをキャンセル
cancelDeleteBtn.addEventListener('click', function(){
  //チェックを全て外す
  deleteCheckboxes.forEach(checkbox => {
    checkbox.checked = false;
  });

  //チェックボックスを非表示
  deleteColumns.forEach(column => {
    column.hidden = true;
  });

  //ボタンをもとに戻す
  deleteModeBtn.hidden = false;
  cancelDeleteBtn.hidden = true;
  confirmDeleteBtn.hidden = true;
  confirmDeleteBtn.disabled = true;
});

//選択した勤怠を削除する
confirmDeleteBtn.addEventListener('click', function(){
  //チェックされたチェックボックスを取得
  const checkedBoxes = document.querySelectorAll(
    '.delete-checkbox:checked'
  );

  //選択されたdetail_idを配列にする
  const deleteIds = Array.from(checkedBoxes).map(
    checkbox => checkbox.value
  );

  //選択された勤怠がなければ修了
  if(deleteIds.length === 0){
    return;
  }
  //削除対象を確認
  //削除前に確認
  if(!confirm('選択した勤怠を削除しますか？')){
    return;
  }
  //PHPに削除対象のIDを送信
  fetch('shusei_save.php',{
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      operation_type: '削除',
      detail_ids: deleteIds
    })
  })
  .then(response => response.json())
  .then(result => {
    alert(result.message);

    if(result.success){
      location.reload();
    }
  })
  .catch(error => {
    console.error('削除エラー：', error);
    alert('通信に失敗しました。');
  });
  
});
    </script>
  </body>
</html>