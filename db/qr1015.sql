--QRコードのテーブル削除と、社員テーブルにQR識別日追加

ALTER TABLE employees
ADD COLUMN qr_date DATE;
DROP TABLE qr_codes;