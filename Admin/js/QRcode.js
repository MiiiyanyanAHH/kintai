<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.js"></script>
//ここでうけとって、変数にいれといたりしてＱＲコード生成する
document.getElementById('QR_new').addEventListener('click', () => {
	var QR_new = '' + ;
	var qr3 = new QRious({
			value: gradeup2Input,
		});
	document.getElementById("gradeup2QR").src = qr3.toDataURL('image/png');
});
//できたらまたＰＨＰに送り返す