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
            <input type="date" id="hire_date" name="hire_date" required>

            <label for="authority">権限</label>
            <select id="authority" name="authority" required>
                <option value="">選択してください</option>
                <option value="管理者">管理者</option>
                <option value="正社員">正社員</option>
                <option value="非正規雇用">非正規雇用</option>
            </select>

            <button type="submit">登録する</button>
        </form>
    </main>

</body>
</html>