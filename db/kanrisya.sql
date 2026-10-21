--仮で管理者テーブルを登録。
INSERT INTO administrators (employee_id, admin_id, password, active, authority)
VALUES
      ('E00001', 'ADM7K2P', 'K7#mP2@x', 1, 0),
      ('E00002', 'ADM3X8Q', 'v8$Rk4!N', 1, 1),
      ('E00003', NULL, NULL, 0, 2);