
//jsから情報受取り
<?php
$data = json_decode(file_get_contents("php://input"),true);

$employeeId = $data["employeeId"];
$qrIssueDate = $data["qrIssueDate"];

$pdo = new PDO('mysql:host=localhost;dbname=kinta_db;charset=utf8','root','');


?>