USE kinta_db;

--社員テーブル
CREATE TABLE employees(
  employee_id CHAR(6) PRIMARY KEY,
  employee_name VARCHAR(100) NOT NULL,
  hire_date DATE NOT NULL,
  retire_date DATE,
  qr_date DATE NOT NULL  --追加
);

--管理者テーブル
--パスワード8桁固定。ハッシュ化して格納するため（255）にしてます。
CREATE TABLE administrators(
  employee_id CHAR(6) NOT NULL,
  admin_id CHAR(7) PRIMARY KEY,
  password VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  authority VARCHAR(30) NOT NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);


--勤怠テーブル
CREATE TABLE attendance (
  employee_id CHAR(6) NOT NULL,
  work_date DATE NOT NULL,
  start_plan TIME,
  end_plan TIME,
  work_type VARCHAR(30),
  PRIMARY KEY (employee_id,work_date),
  FOREIGN KEY(employee_id)
  REFERENCES employees(employee_id)
);

--出退勤ログテーブル
--外部キー...employee_id
CREATE TABLE attendance_logs (
    employee_id CHAR(6) NOT NULL,
    time_stamp DATETIME NOT NULL,
    time_type VARCHAR(30) NOT NULL,
    FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
);

--修正ログテーブル
--外部キー...employee_id+admin_id
CREATE TABLE correction_logs (
    employee_id CHAR(6) NOT NULL,
    admin_id CHAR(7) NOT NULL,
    changed_at DATETIME NOT NULL,
    changed_item VARCHAR(100) NOT NULL,
    new_value VARCHAR(255),
    operation_type VARCHAR(30) NOT NULL,
    FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id),
    FOREIGN KEY (admin_id)
        REFERENCES administrators(admin_id)
);