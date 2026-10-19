
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
      $sql=$pdo->prepare('select * from attendance_logs where employee_id=? and DATE(time_stamp)=? order by time_stamp desc limit 1');
      $sql->execute([$employeeId,date('Y-m-d')]);
      $todayLog = $sql->fetch();
      if($todayLog == false){
        //対象IDのログデータがなかった場合
        // "出勤しました";
        $sql= $pdo->prepare('insert into attendance_logs (employee_id, time_stamp, time_type) values(?,?,?)');
        $sql->execute([
              $employeeId, date('Y-m-d H:i:s'), "出勤"
          ]);
          //出勤画面を返す
          echo json_encode([
            "page" => "http://localhost/kintai/QR/php/shukin_complete.php"
          ]);
          
      } else {
        //対象IDのログが見つかった場合
         if($todayLog["time_type"] == "出勤"){
          //直前が出勤だった場合
            // "退勤しました";
        $sql= $pdo->prepare('insert into attendance_logs (employee_id, time_stamp, time_type) values(?,?,?)');
        $sql->execute([
              $employeeId, date('Y-m-d H:i:s'), "退勤"
          ]);
          //退勤画面を返す
          echo json_encode([
            "page" => "http://localhost/kintai/QR/php/taikin_complete.php"
          ]);
          
         } else if($todayLog["time_type"] == "退勤"){
          //直前が退勤だった場合
            // "出勤しました";
        $sql= $pdo->prepare('insert into attendance_logs (employee_id, time_stamp, time_type) values(?,?,?)');
        $sql->execute([
              $employeeId, date('Y-m-d H:i:s'), "出勤"
          ]);
          //出勤画面を返す
          echo json_encode([
            "page" => "http://localhost/kintai/QR/php/shukin_complete.php"
          ]);
         
         }
      }
    } else {
        //無効なIDです画面を返す（退職済み）
          echo json_encode([
            "page" => "http://localhost/kintai/QR/php/mukouQR.php"
          ]);
     }
  } else {
        //無効なIDです画面を返す（登録されてない、関係ないQRデーター）
          echo json_encode([
            "page" => "http://localhost/kintai/QR/php/mukouQR.php"
          ]);
  }


?>

