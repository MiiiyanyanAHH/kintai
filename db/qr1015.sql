--QRコードのテーブル削除と、社員テーブルにQR識別日追加

ALTER TABLE employees
ADD COLUMN qr_date DATE;
DROP TABLE qr_codes;

--社員テーブルに仮データを3件登録

INSERT INTO employees(employee_id, employee_name, hire_date, retire_date, qr_date)
  VALUES('E00001', '仲程 明日香', '2019-04-15', NULL, '2019-04-15'),
        ('E00002', '仲村 雅', '2021-07-01', NULL, '2021-12-12'),
        ('E00003', '佐藤 明', '2022-11-21', '2023-02-05', '2022-11-21');


