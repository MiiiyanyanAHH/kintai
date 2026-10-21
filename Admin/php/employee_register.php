<?php
//データベースに接続する.
$pdo = new PDO(
    'mysql:host=localhost;dbname=kinta_db;charset=utf8mb4',
    'root','');

    //フォームから送信されたデータを受け取る
    $employee_name = $_POST['employee_name']??'';
    $hire_date = $_POST['hire_date']??'';
    $role = $_POST['role'] ?? '';

    //フォームからPOST送信されたら
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

         // 社員IDの最大番号を取得する。
         //ID二文字目以降を文字列→数値→一番大きい値取得
        $sql = "SELECT MAX(CAST(SUBSTRING(employee_id, 2) AS UNSIGNED))
            FROM employees";
            //このsqlの指示を使ってDB実行命令。
        $stmt = $pdo->query($sql);

         // 取得した番号を変数に入れる。取得した単位をintにして代入。
        $last_number = (int)$stmt->fetchColumn();

        /* 次の社員IDを作る。最大番号に１をたして、頭にE追加。ゼロ埋めID作成。
        2引数➤全体を5桁に 3➤0埋め 4➤左埋め*/
        $employee_id = 'E' . str_pad((string)($last_number + 1), 5, '0', STR_PAD_LEFT);

         // QRコードの発行日を入社年月日に設定する
        $qr_date = $hire_date;

        // 社員情報を登録するSQL
         $sql = "INSERT INTO employees
            (employee_id, employee_name, hire_date, qr_date)
            VALUES
            (:employee_id, :employee_name, :hire_date, :qr_date)";

          // SQLを準備する
         $stmt = $pdo->prepare($sql);

          // データを登録する
         $stmt->execute([
         ':employee_id' => $employee_id,
         ':employee_name' => $employee_name,
          ':hire_date' => $hire_date,
         ':qr_date' => $qr_date
    ]);

    }
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規社員登録</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/employee_register.css">
</head>
<body>
    <a href="admin.php" class="back-link">← 戻る</a>
    <main class="container">
        <h1>新規社員登録</h1>

        <form action="" method="post">
            <label for="employee_name">社員名</label><!--名前入力欄-->
            <!--英字、ひらがな、カタカナ、漢字のみ受け付け-->
            <input type="text" id="employee_name" name="employee_name" placeholder="例）山田 太郎" 
             pattern="[a-zA-Z\u3040-\u309F\u30A0-\u30FF\u4E00-\u9FFF]+　[a-zA-Z\u3040-\u309F\u30A0-\u30FF\u4E00-\u9FFF]+$"
            title="苗字と名前の間に全角スペースを入れてください。数字や記号は使用できません。" required>

            <label for="hire_date">入社年月日</label>
            <input type="date" id="hire_date" name="hire_date" required><!--日付選択入力欄-->

            	<p>
                    <span class="role-title">権限</span><br>
		            <input type="radio" name="role" value="管理者" required>管理者<br>
		            <input type="radio" name="role" value="正社員">正社員<br>
		            <input type="radio" name="role" value="非正規雇用">非正規雇用<br>
	            </p>

            <button type="submit">登録する</button>
        </form>
    </main>
<?php include('footer.php'); ?>
</body>
</html>