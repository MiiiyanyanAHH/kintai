
//jsから情報受取り
<?php
$data = json_decode(file_get_contents("php://input"),true);

$employeeId = $data["employeeId"];
$qrIssueDate = $data["qrIssueDate"];

//データベースから社員IDと識別日を検索
$pdo = new PDO('mysql:host=localhost;dbname=kinta_db;charset=utf8','root','');
$sql=$pdo->prepare('select * from employees where employee_id=? and qr_date=?');
$sql->execute([$employeeId,$qrIssueDate]);
  //検索結果をresultへ
  $result = $sql->fetch();
  //データが取れた場合、退職日を検索しNULLだったらログテーブルを検索
  if($result){
    if($result["retire_date"] == NULL){
      //タイムゾーン設定
      date_default_timezone_set('Asia/Tokyo');
      //ログテーブルを検索。社員IDと今日の日付と一致するログがあるか
      $sql=$pdo->prepare('select * from attendance_logs where employee_id=? and DATE(Time_stamp)=?');
      $sql->execute([$employeeId,date('Y-m-d')]);
      $todayLog = $sql->fetch();
      if($todayLog == false){
        echo "出勤しました";
      }else{
        echo "退勤しました。";
      }
    } else {
    echo "無効なIDです";
  }
  }


?>

