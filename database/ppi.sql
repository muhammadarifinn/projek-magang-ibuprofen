-- ============================================================
-- Database PPI RS (MySQL / phpMyAdmin)
-- Impor file ini lewat phpMyAdmin: tab Import > pilih ppi.sql
-- ============================================================
CREATE DATABASE IF NOT EXISTS ppi_rs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ppi_rs;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_jawaban, audit, pertanyaan, tahap, bundle, users, ruangan, simrs_pasien;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Referensi
-- ------------------------------------------------------------
CREATE TABLE ruangan (
  id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_ruangan_nama (nama)
) ENGINE=InnoDB;

CREATE TABLE users (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username   VARCHAR(50)  NOT NULL,
  password   VARCHAR(255) NOT NULL,
  nama       VARCHAR(100) NOT NULL,
  role       ENUM('admin','ipcn','ketua','perawat') NOT NULL,
  ruangan_id INT UNSIGNED NULL,
  aktif      TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_username (username),
  CONSTRAINT fk_users_ruangan FOREIGN KEY (ruangan_id) REFERENCES ruangan (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Master bundle -> tahap -> pertanyaan (pengganti hardcode)
-- ------------------------------------------------------------
CREATE TABLE bundle (
  id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode  VARCHAR(20)  NOT NULL,
  nama  VARCHAR(120) NOT NULL,
  aktif TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uk_bundle_kode (kode)
) ENGINE=InnoDB;

-- kode: IGD atau RI. harian=1 berarti diisi setiap hari rawat, harian=0 diisi sekali saat masuk.
CREATE TABLE tahap (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  bundle_id  INT UNSIGNED NOT NULL,
  kode       VARCHAR(10)  NOT NULL,
  nama       VARCHAR(60)  NOT NULL,
  keterangan VARCHAR(120) NULL,
  urutan     SMALLINT     NOT NULL DEFAULT 1,
  harian     TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uk_tahap (bundle_id, kode),
  CONSTRAINT fk_tahap_bundle FOREIGN KEY (bundle_id) REFERENCES bundle (id)
) ENGINE=InnoDB;

-- Pertanyaan tidak pernah dihapus, hanya dinonaktifkan (aktif=0), agar audit lama utuh.
CREATE TABLE pertanyaan (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tahap_id   INT UNSIGNED NOT NULL,
  teks       TEXT         NOT NULL,
  urutan     SMALLINT     NOT NULL DEFAULT 1,
  wajib      TINYINT(1)   NOT NULL DEFAULT 1,
  aktif      TINYINT(1)   NOT NULL DEFAULT 1,
  created_at DATETIME     NOT NULL,
  updated_at DATETIME     NOT NULL,
  PRIMARY KEY (id),
  KEY idx_pertanyaan_tahap (tahap_id, aktif, urutan),
  CONSTRAINT fk_pertanyaan_tahap FOREIGN KEY (tahap_id) REFERENCES tahap (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Data pasien dari SIMRS. SEMENTARA berupa satu tabel di database ini.
-- Nanti diganti koneksi ke SQL Server (lihat application/config/ppi.php).
-- Satu baris = satu registrasi (satu kali masuk rumah sakit).
-- ------------------------------------------------------------
CREATE TABLE simrs_pasien (
  no_registrasi VARCHAR(30)  NOT NULL,
  no_rm         VARCHAR(20)  NOT NULL,
  nama          VARCHAR(120) NOT NULL,
  ruangan       VARCHAR(80)  NOT NULL,
  tgl_masuk     DATE         NOT NULL COMMENT 'tanggal masuk IGD',
  tgl_masuk_ri  DATE         NULL     COMMENT 'tanggal masuk rawat inap, NULL jika belum dirawat',
  tgl_keluar    DATE         NULL     COMMENT 'NULL = masih dirawat',
  PRIMARY KEY (no_registrasi),
  KEY idx_simrs_rm (no_rm),
  KEY idx_simrs_keluar (tgl_keluar)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Audit. Satu audit = satu registrasi pasien x satu bundle.
-- Data pasien disalin (snapshot) agar dashboard dan laporan tidak bergantung pada SIMRS.
-- ------------------------------------------------------------
CREATE TABLE audit (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  no_registrasi VARCHAR(30)  NOT NULL,
  bundle_id     INT UNSIGNED NOT NULL,
  no_rm         VARCHAR(20)  NOT NULL,
  nama_pasien   VARCHAR(120) NOT NULL,
  ruangan       VARCHAR(80)  NOT NULL,
  tgl_masuk     DATE         NOT NULL,
  tgl_masuk_ri  DATE         NULL,
  tgl_keluar    DATE         NULL,
  status        ENUM('aktif','selesai') NOT NULL DEFAULT 'aktif',
  created_by    INT UNSIGNED NOT NULL,
  created_at    DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_audit (no_registrasi, bundle_id),
  KEY idx_audit_status (status, bundle_id),
  KEY idx_audit_ruangan (ruangan),
  CONSTRAINT fk_audit_bundle FOREIGN KEY (bundle_id) REFERENCES bundle (id),
  CONSTRAINT fk_audit_user   FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB;

-- jawaban: Y = Ya, T = Tidak, N = Tidak berlaku. Sel kosong = tidak ada baris.
-- teks_pertanyaan = salinan teks saat pertama dijawab, supaya audit lama tidak berubah.
CREATE TABLE audit_jawaban (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  audit_id        INT UNSIGNED NOT NULL,
  pertanyaan_id   INT UNSIGNED NOT NULL,
  tahap_id        INT UNSIGNED NOT NULL,
  tanggal         DATE         NOT NULL,
  jawaban         ENUM('Y','T','N') NOT NULL,
  teks_pertanyaan TEXT         NOT NULL,
  updated_by      INT UNSIGNED NOT NULL,
  updated_at      DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_jawaban (audit_id, pertanyaan_id, tanggal),
  KEY idx_jawaban_tanggal (tanggal),
  CONSTRAINT fk_jawaban_audit      FOREIGN KEY (audit_id)      REFERENCES audit (id) ON DELETE CASCADE,
  CONSTRAINT fk_jawaban_pertanyaan FOREIGN KEY (pertanyaan_id) REFERENCES pertanyaan (id),
  CONSTRAINT fk_jawaban_tahap      FOREIGN KEY (tahap_id)      REFERENCES tahap (id)
) ENGINE=InnoDB;

-- ============================================================
-- DATA AWAL
-- ============================================================
INSERT INTO ruangan (nama) VALUES ('IGD'), ('ICU'), ('Ruang 1'), ('Ruang 2');

-- Kata sandi awal semua akun: admin123 (WAJIB diganti setelah login pertama)
INSERT INTO users (username, password, nama, role, ruangan_id) VALUES
('admin',   '$2y$10$ce7fipprIWY2Qwt/lgeKLeJKJ49X/WYp5J0hLA0HjwDnSyRtwABeq', 'Administrator', 'admin',   NULL),
('ipcn',    '$2y$10$ce7fipprIWY2Qwt/lgeKLeJKJ49X/WYp5J0hLA0HjwDnSyRtwABeq', 'IPCN contoh',   'ipcn',    NULL),
('ketua',   '$2y$10$ce7fipprIWY2Qwt/lgeKLeJKJ49X/WYp5J0hLA0HjwDnSyRtwABeq', 'Ketua PPI',     'ketua',   NULL),
('perawat', '$2y$10$ce7fipprIWY2Qwt/lgeKLeJKJ49X/WYp5J0hLA0HjwDnSyRtwABeq', 'Perawat contoh', 'perawat', 3);

INSERT INTO bundle (kode, nama) VALUES
('VAP',      'Ventilator Associated Pneumonia'),
('IADP',     'Infeksi Aliran Darah Primer'),
('ISK',      'Infeksi Saluran Kemih'),
('IDO',      'Infeksi Daerah Operasi'),
('Plebitis', 'Plebitis');

-- Dua tahap per bundle: IGD (sekali saat masuk) dan Rawat Inap (harian)
INSERT INTO tahap (bundle_id, kode, nama, keterangan, urutan, harian)
SELECT id, 'IGD', 'IGD',        'pasien pertama masuk', 1, 0 FROM bundle;
INSERT INTO tahap (bundle_id, kode, nama, keterangan, urutan, harian)
SELECT id, 'RI',  'Rawat Inap', 'jika pasien dirawat',  2, 1 FROM bundle;

-- Pertanyaan PLACEHOLDER: ganti dengan pertanyaan asli lewat menu Master Pertanyaan
-- atau impor dari database lama (lihat README bagian "Impor pertanyaan lama").
INSERT INTO pertanyaan (tahap_id, teks, urutan, wajib, aktif, created_at, updated_at)
SELECT t.id, CONCAT('[Pertanyaan ', b.kode, ' ', t.kode, ' ', n.n, ']'), n.n, 1, 1, NOW(), NOW()
FROM tahap t
JOIN bundle b ON b.id = t.bundle_id
JOIN (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3) n;

-- Pasien contoh (hapus setelah koneksi SIMRS sungguhan dipakai)
INSERT INTO simrs_pasien (no_registrasi, no_rm, nama, ruangan, tgl_masuk, tgl_masuk_ri, tgl_keluar) VALUES
('REG-0001', '00-12-01', 'Pasien contoh A', 'ICU',     DATE_SUB(CURDATE(), INTERVAL 4 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY), NULL),
('REG-0002', '00-12-02', 'Pasien contoh B', 'ICU',     DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL),
('REG-0003', '00-12-03', 'Pasien contoh C', 'Ruang 1', DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL),
('REG-0004', '00-12-04', 'Pasien contoh D', 'IGD',     CURDATE(),                           NULL,                                NULL),
('REG-0005', '00-12-05', 'Pasien contoh E', 'Ruang 1', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), NULL),
('REG-0006', '00-12-06', 'Pasien contoh F', 'IGD',     CURDATE(),                           NULL,                                NULL),
('REG-0007', '00-12-07', 'Pasien contoh G', 'Ruang 2', DATE_SUB(CURDATE(), INTERVAL 9 DAY), DATE_SUB(CURDATE(), INTERVAL 9 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY));
