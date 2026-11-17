


// QRコード作成
var qrcode = new QRCode('qrcode', {
  text:  qrData,
  width: 170,
  height: 170,
  correctLevel : QRCode.CorrectLevel.H
});
