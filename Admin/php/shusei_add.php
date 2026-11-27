<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// ログイン中の管理者IDを取得
$admin_id = $_SESSION['admin_id'] ?? '';

// 管理者ログイン確認
if (empty($admin_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'ログイン情報を確認できません。'
    ]);
    exit;
}

try {
    // データベースに接続
    $pdo = new PDO(
        'mysql:host=localhost;dbname=kinta_db;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // JavaScriptから送られたデータを受け取る
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        throw new Exception('送信データが正しくありません。');
    }

    $employee_id = $data['employee_id'] ?? '';
    $work_date = $data['work_date'] ?? '';
    $start_time = $data['start_time'] ?? '';
    $end_time = $data['end_time'] ?? '';
    $work_type = $data['work_type'] ?? '';

    // 必須項目を確認
    if (
        $employee_id === '' ||
        $work_date === '' ||
        $work_type === ''
    ) {
        throw new Exception('必須項目を入力してください。');
    }

    // 空欄の時刻はNULLにする
    $start_time = $start_time !== '' ? $start_time : null;
    $end_time = $end_time !== '' ? $end_time : null;

    // 勤怠詳細テーブルに追加
    $stmt = $pdo->prepare(
        "INSERT INTO attendance_details (
            employee_id,
            work_date,
            start_time,
            end_time,
            work_type,
            original_start_timestamp,
            original_end_timestamp,
            operation_type,
            admin_id
        ) VALUES (?, ?, ?, ?, ?, NULL, NULL, '追加', ?)"
    );

    $stmt->execute([
        $employee_id,
        $work_date,
        $start_time,
        $end_time,
        $work_type,
        $admin_id
    ]);

    // 追加したデータのIDを取得
    $detail_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => '勤怠を追加しました。',
        'detail_id' => $detail_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
