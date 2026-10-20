//jsQRライブラリがQRコードを解析44行目
//gr-msgという場所に文字列を表示
//キャンバスとはWebページ上に画像を書くための領域

//codeが読み取り結果を保持している
//code.dataはQRに入っていた文字列

//テスト


//Webカメラの起動
const video = document.getElementById('video');
let contentWidth;
let contentHeight;
//映像を取得、音声はなし映像のサイズを指定
const media = navigator.mediaDevices.getUserMedia({ audio: false, video: { width: 640, height: 480 } }).then((stream) => {
  video.srcObject = stream;
  video.onloadeddata = () => {
    contentWidth = video.clientWidth;
    contentHeight = video.clientHeight;
    canvasUpdate();
    checkImage();
  }
}).catch((e) => {
  console.log(e);
});

//videoカメラ映像のキャンバス表示
const cvs = document.getElementById('camera-canvas');
const ctx = cvs.getContext('2d');
const canvasUpdate = () => {
  cvs.width = contentWidth;
  cvs.height = contentHeight;
  ctx.drawImage(video, 0, 0, contentWidth, contentHeight);
  requestAnimationFrame(canvasUpdate);
}

//QRコードの検出
const rectCvs = document.getElementById('rect-canvas');
const rectCtx = rectCvs.getContext('2d');
//表示のフラグ
let isProcessing = false;
const checkImage = () => {
  //映像の画像データの取り出し
  const imageData = ctx.getImageData(0, 0, contentWidth, contentHeight);
  //jsQRが画像解析し、解析した文字列をcodeに返す
  const code = jsQR(imageData.data, contentWidth, contentHeight);


  //検出結果に合わせて処理を実施
  if (code != null && isProcessing == false) {//QRが読み取れていて、まだ処理中ではない場合
    //処理中にする
    isProcessing = true;
    //ローディング画面を表示、場所を取得
    const frame = document.getElementById("result-frame");
    //表示画面の所在場所
    frame.src = "http://localhost/kintai/QR/php/loading.php";
    //QRコードを読み取ったときにフレーム表示
    frame.style.display = "block";
    
    //QRコードのデータをＩＤと識別日に分けて変数へ
    const qrData = code.data.split(",");
    const employeeId = qrData[0];
    const qrIssueDate = qrData[1];

    //phpにＩＤと識別日の情報を送信
    fetch("http://localhost/kintai/QR/php/qr_attendance.php", {
      method: "POST", headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        employeeId: employeeId,
        qrIssueDate: qrIssueDate
      })
    })
      //phpからかえされたJSONデータを読み取る.thenは前の処理が完了して結果を受け取った後に次の処理を行う仕組み
      .then(response => response.json()).then(data => {
        //phpから受け取った画面のURLをframeに設定
        setTimeout(() => {
            //JSONの中にあるpageの値を取り出し、表示するページを更新する
            frame.src = data.page;

            //打刻結果画面を3秒表示してQR読み取り画面に戻る
            setTimeout(() => {frame.style.display = "none";
        isProcessing = false;}, 3000);
        }, 2000);
      })
      .catch(error => {
        console.log("エラー", error);
        isProcessing = false;
      })
      

    //読み取り結果をコンソールに表示
    console.log("QRcodeが見つかりました", code);
    //読み取ったＱＲコードの周囲に四角形を描画する
    drawRect(code.location);
    //QRコード：見つかりましたを表示
    document.getElementById('qr-msg').textContent = `QRコード：${code.data}`;
  } else {
    //QRコードが見つからなかった場合
    console.log("QRcodeが見つかりません", code);
    rectCtx.clearRect(0, 0, contentWidth, contentHeight);
    document.getElementById('qr-msg').textContent = `QRコード：見つかりません`;
  }
    //QRコードを3秒ごとに読み取る
  setTimeout(() => { checkImage() }, 3000);//3秒
}


//四辺形の描画
const drawRect = (location) => {
  rectCvs.width = contentWidth;
  rectCvs.height = contentHeight;
  drawLine(location.topLeftCorner, location.topRightCorner);
  drawLine(location.topRightCorner, location.bottomRightCorner);
  drawLine(location.bottomRightCorner, location.bottomLeftCorner);
  drawLine(location.bottomLeftCorner, location.topLeftCorner);
}

//線の描画
const drawLine = (begin, end) => {
  rectCtx.lineWidth = 4;
  rectCtx.strokeStyle = "#F00";
  rectCtx.beginPath();
  rectCtx.moveTo(begin.x, begin.y);
  rectCtx.lineTo(end.x, end.y);
  rectCtx.stroke();
}



