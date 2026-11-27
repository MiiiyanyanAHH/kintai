<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

//データベース接続
$pdo = new PDO('mysql:host=localhost;dbname=kinta_db;charset=utf8mb4','root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

//JavaScriptから送られたJSONデータを受け取る
// $data = json_decode(file_get_contents('php://input'),true);
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);


//管理者IDを取得
$admin_id = $_SESSION['admin_id'] ?? '';

//操作の種類を取得
$operation_type = $data['operation_type'] ?? '修正';

//管理者ログイン確認
if(empty($admin_id)){
  echo json_encode([
    'success' => false,
    'message' => 'ログインしてください。'
  ]);
  exit;
}
//削除以外の場合は社員IDと勤務日を確認
if($operation_type !== '削除'){
  if(
    empty($data['employee_id']) ||
    empty($data['work_date'])
  ){
    echo json_encode([
      'success' => false,
      'message' => '必要なデータが不足しています。'
    ]);
    exit;
  }
}
//削除の場合はdetail_idsを確認
if($operation_type === '削除'){
  if(
    empty($data['detail_ids']) ||
    !is_array($data['detail_ids'])
  ){
    echo json_encode([
      'success' => false,
      'message' => '削除する勤怠を選択してください。'
    ]);
    exit;
  }
}
//ここに削除処理を追加する
if($operation_type === '削除'){
  //削除対象のIDを取得
  $detail_ids = $data['detail_ids'];

  //IDが正しいか確認
  foreach($detail_ids as $id){
    if(filter_var($id, FILTER_VALIDATE_INT) === false || (int)$id <= 0){
      echo json_encode([
        'success' => false,
        'message' => '不正な勤怠IDが含まれています。'
      ]);
      exit;
    }
  }
  try{
    //処理開始
    $pdo->beginTransaction();

    //選択された勤怠を1件ずつ処理
    foreach($detail_ids as $id){
      //対象の勤怠を取得
      $stmt = $pdo->prepare(
        "select employee_id, operation_type
        from attendance_details
        where detail_id = ?
        and is_deleted = 0
        for update"
      );
      $stmt->execute([(int)$id]);
      $detail = $stmt->fetch(PDO::FETCH_ASSOC);

      //対象の勤怠が存在しない場合
      if(!$detail){
        throw new Exception('対象の勤怠が見つかりません。');
      }
      //追加した勤怠の場合
      if($detail['operation_type'] === '追加'){
        $changed_item = '追加した勤怠';
        $new_value = '削除済み';
        $log_operation = '削除';

        //修正した勤怠の場合
      } elseif($detail['operation_type'] === '修正'){
        $changed_item = '修正した勤怠';
        $new_value = '元のQR打刻に復元';
        $log_operation = '修正取消';
      }else{
        throw new Exception('処理できない勤怠です。');
      }
      //論理削除（元のデータは残す）
      $stmt = $pdo->prepare(
        "update attendance_details
        set is_deleted = 1,
        admin_id = ?,
        changed_at = now()
        where detail_id = ?
        and is_deleted = 0"
      );

      $stmt->execute([
        $admin_id,
        (int)$id
      ]);

      //操作履歴を保存
      $stmt = $pdo->prepare(
        "insert into correction_logs(
        employee_id,
        admin_id,
        changed_at,
        changed_item,
        new_value,
        operation_type
        ) values(?,?,now(),?,?,?)"
      );

      $stmt->execute([
        $detail['employee_id'],
        $admin_id,
        $changed_item,
        $new_value,
        $log_operation
      ]);
    }

    //すべて成功したら確定
    $pdo->commit();

    echo json_encode([
      'success' => true,
      'message' => '選択した勤怠の削除・修正取り消しが完了しました。'
    ]);
    exit;
  }catch(Throwable $e){
    //失敗したらすべて取り消す
    if($pdo->inTransaction()){
      $pdo->rollBack();
    }
    echo json_encode([
      'success' => false,
      'message' => '削除処理に失敗しました。'
    ]);
    exit;
  }

}



//受け取ったデータを変数に格納
$employee_id = $data['employee_id'];
$work_date = $data['work_date'];

$original_start = !empty($data['original_start_timestamp'])
? $data['original_start_timestamp']
: null;

$original_end = !empty($data['original_end_timestamp'])
 ? $data['original_end_timestamp']
 : null;


$start_time = !empty($data['start_time']) ? $data['start_time']:null;
$end_time = !empty($data['end_time']) ? $data['end_time']:null;

$work_type =  $data['work_type'] ?? null;


//勤怠追加の場合
if($operation_type === '追加'){

$stmt = $pdo->prepare(
  "insert into attendance_details(
  employee_id,
  work_date,
  start_time,
  end_time,
  work_type,
  original_start_timestamp,
  original_end_timestamp,
  operation_type,
  admin_id,
  start_time_changed,
  end_time_changed,
  work_type_changed
  )  values(?,?,?,?,?,NULL,NULL,'追加',?,0,0,0)"
);
  $stmt->execute([
    $employee_id,
    $work_date,
    $start_time,
    $end_time,
    $work_type,
    $admin_id
  ]);

  echo json_encode([
    'success' => true,
    'message' => '勤怠を追加しました。'
  ]);
  exit;
}

//既存の修正データを検索
$stmt = $pdo->prepare(
  "select * from attendance_details
  where employee_id = ?
  and work_date = ?
  and original_start_timestamp <=> ?
  and original_end_timestamp <=> ?
  and is_deleted = 0
  order by detail_id desc
  limit 1"
);

$stmt->execute([
$employee_id,
$work_date,
$original_start,
$original_end
]);

$existing = $stmt->fetch(PDO::FETCH_ASSOC);

//既存データがある場合
if($existing){

    //どの項目が変更されたか判定
    $start_changed = ($existing['start_time'] != $start_time) ? 1 : 0;
    $end_changed = ($existing['end_time'] != $end_time) ? 1 : 0;
    $work_type_changed = ($existing['work_type'] != $work_type) ? 1 : 0;

  //既存データを更新
  $stmt = $pdo->prepare(
    "update attendance_details
    set start_time = ?,
    end_time = ?,
    work_type = ?,
    operation_type = '修正',
    admin_id = ?,
    changed_at = now(),
    start_time_changed = ?,
    end_time_changed = ?,
    work_type_changed = ?
    where detail_id = ?"
  );

  //SQLを実行
  $stmt->execute([
    $start_time,
    $end_time,
    $work_type,
    $admin_id,
    $start_changed,
    $end_changed,
    $work_type_changed,
    $existing['detail_id']
  ]);


}else{
  //新しい勤怠データを登録
  $stmt = $pdo->prepare(
    "insert into attendance_details(
      employee_id,
      work_date,
      start_time,
      end_time,
      work_type,
      original_start_timestamp,
      original_end_timestamp,
      operation_type,
      admin_id,
      start_time_changed,
      end_time_changed,
      work_type_changed
    ) values(?,?,?,?,?,?,?,'修正',?,1,1,1)"
  );

  $stmt->execute([
    $employee_id,
    $work_date,
    $start_time,
    $end_time,
    $work_type,
    $original_start,
    $original_end,
    $admin_id
  ]);
}

//保存成功をjavascriptに返す
echo json_encode([
  'success' => true,
  'message' => '保存しました。'
]);

?>