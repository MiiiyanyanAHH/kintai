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

//受け取ったデータを確認
if(
  empty($data['employee_id']) ||
  empty($data['work_date']) ||
  empty($admin_id)
){
  echo json_encode([
    'success' => false,
    'message' => '必要なデータが不足しています。'
  ]);
  exit;
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