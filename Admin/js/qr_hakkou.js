
//ここでPHPから変数うけとって、ＱＲコード生成する
// const js_qr = JSON.parse('<?php echo $qrDate;?>');

// QRコード作成
var qrcode = new QRCode('qrcode', {
  text:  qrData,
  width: 170,
  height: 170,
  correctLevel : QRCode.CorrectLevel.H
});

//できたらまたＰＨＰに送り返す