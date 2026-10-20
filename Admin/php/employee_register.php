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

    <main class="container">
        <h1>新規社員登録</h1>

        <form action="" method="post">
            <label for="employee_name">社員名</label>
            <input type="text" id="employee_name" name="employee_name" required>

            <label for="hire_date">入社年月日</label>
            <input type="date" id="hire_date" name="hire_date" required><!--日付選択入力欄-->

            	<p>
                    <span class="role-title">権限</span><br>
		            <input type="radio" name="role" value="管理者">管理者<br>
		            <input type="radio" name="role" value="正社員">正社員<br>
		            <input type="radio" name="role" value="非正規雇用">非正規雇用<br>
	            </p>

            <button type="submit">登録する</button>
        </form>
    </main>
<?php include('footer.php'); ?>
</body>
</html>