
/*
DUMMY DATA FINAL - PINJEM MOBIL (SQL Server / SSMS)
nama customer: Alicia, Felice, Sheryl, Rakha.

Cara pakai:
1. Copy folder uploads dari ZIP ini ke C:\xampp\htdocs\pinjemobil\
2. Jalankan script ini di database pinjemobil lewat SSMS.
*/

DELETE FROM detail_denda;
DELETE FROM pengembalian;
DELETE FROM kondisi_mobil;
DELETE FROM peminjaman;
DELETE FROM users;
DELETE FROM mobil;
DELETE FROM member;
DELETE FROM pegawai;
DELETE FROM cabang;

DBCC CHECKIDENT ('detail_denda', RESEED, 0);
DBCC CHECKIDENT ('pengembalian', RESEED, 0);
DBCC CHECKIDENT ('kondisi_mobil', RESEED, 0);
DBCC CHECKIDENT ('peminjaman', RESEED, 0);
DBCC CHECKIDENT ('users', RESEED, 0);
DBCC CHECKIDENT ('mobil', RESEED, 0);
DBCC CHECKIDENT ('member', RESEED, 0);
DBCC CHECKIDENT ('pegawai', RESEED, 0);
DBCC CHECKIDENT ('cabang', RESEED, 0);

INSERT INTO cabang (nama_cabang, alamat) VALUES
('Pinjem Mobil Cabang Utama', 'Jl. Anggrek No. 10, Bandung'),
('Pinjem Mobil Cabang Barat', 'Jl. Mawar No. 21, Bandung'),
('Pinjem Mobil Cabang Timur', 'Jl. Tulip No. 8, Bandung');

INSERT INTO pegawai (id_cabang, nama_pegawai, nomor_telepon, email, alamat, jabatan) VALUES
(1, 'Admin Utama', '081234567801', 'adminutama@gmail.com', 'Jl. Bukit Jarian No. 12', 'Pemilik'),
(1, 'Admin Cabang Utama', '081234567802', 'admincabang@gmail.com', 'Jl. Ciumbuleuit No. 45', 'Admin Cabang'),
(2, 'Pegawai Barat', '081234567803', 'pegawaibarat@gmail.com', 'Jl. Cibaduyut No. 17', 'Petugas Cek Kondisi'),
(3, 'Pegawai Timur', '081234567804', 'pegawaitimur@gmail.com', 'Jl. Antapani No. 3', 'Petugas Cek Kondisi');

INSERT INTO member (nama_member, ktp, sim, alamat, nomor_telepon, email, tanggal_registrasi, id_cabang) VALUES
('Vania', 'uploads/identitas/ktp_vania.png', 'uploads/identitas/sim_vania.png', 'Jl. Sukajadi No. 15, Bandung', '082129284461', 'vania@gmail.com', '2026-05-20', 2),
('Maria', 'uploads/identitas/ktp_maria.png', 'uploads/identitas/sim_maria.png', 'Jl. Dago No. 22, Bandung', '082145678902', 'maria@gmail.com', '2026-05-21', 1),
('Alicia', 'uploads/identitas/ktp_alicia.png', 'uploads/identitas/sim_alicia.png', 'Jl. Riau No. 8, Bandung', '082156789013', 'alicia@gmail.com', '2026-05-22', 2),
('Felice', 'uploads/identitas/ktp_felice.png', 'uploads/identitas/sim_felice.png', 'Jl. Braga No. 11, Bandung', '082167890124', 'felice@gmail.com', '2026-05-23', 1),
('Sheryl', 'uploads/identitas/ktp_sheryl.png', 'uploads/identitas/sim_sheryl.png', 'Jl. Pasteur No. 5, Bandung', '082178901235', 'sheryl@gmail.com', '2026-05-24', 3),
('Rakha', 'uploads/identitas/ktp_rakha.png', 'uploads/identitas/sim_rakha.png', 'Jl. Buah Batu No. 19, Bandung', '082189012346', 'rakha@gmail.com', '2026-05-25', 3);

INSERT INTO users (nama, username, password, role, id_member, id_pegawai) VALUES
('Admin Utama', 'admin', 'Admin123!', 'admin', NULL, 1),
('Admin Cabang Utama', 'admin01', 'Admin123!', 'admin', NULL, 2),
('Pegawai Barat', 'pegawai1', 'Pegawai1!', 'pegawai', NULL, 3),
('Pegawai Timur', 'pegawai2', 'Pegawai2!', 'pegawai', NULL, 4),
('Vania', 'vania01', 'Vania1!', 'customer', 1, NULL),
('Maria', 'maria01', 'Maria1!', 'customer', 2, NULL),
('Alicia', 'alicia01', 'Alicia1!', 'customer', 3, NULL),
('Felice', 'felice01', 'Felice1!', 'customer', 4, NULL),
('Sheryl', 'sheryl01', 'Sheryl1!', 'customer', 5, NULL),
('Rakha', 'rakha01', 'Rakha1!', 'customer', 6, NULL);

INSERT INTO mobil (id_cabang, nomor_polisi, nama_mobil, kapasitas, status_mobil, harga_sewa, foto_mobil) VALUES
(1, 'D 1201 AB', 'Toyota Avanza', 7, 'Tersedia', 350000, 'uploads/mobil/toyota_avanza.png'),
(1, 'D 1402 IN', 'Toyota Innova', 7, 'Tersedia', 500000, 'uploads/mobil/toyota_innova.png'),
(1, 'D 1703 XP', 'Mitsubishi Xpander', 7, 'Maintenance', 420000, 'uploads/mobil/mitsubishi_xpander.png'),
(2, 'D 2204 CR', 'Hyundai Creta', 5, 'Tersedia', 450000, 'uploads/mobil/hyundai_creta.png'),
(2, 'DK 555 A', 'Suzuki Jimny', 4, 'Dipinjam', 550000, 'uploads/mobil/suzuki_jimny.png'),
(2, 'D 2605 CV', 'Honda Civic', 5, 'Tersedia', 600000, 'uploads/mobil/honda_civic.png'),
(3, 'D 3306 FT', 'Toyota Fortuner', 7, 'Dipinjam', 750000, 'uploads/mobil/toyota_fortuner.png'),
(3, 'D 3707 PL', 'Hyundai Palisade', 7, 'Tersedia', 900000, 'uploads/mobil/hyundai_palisade.png'),
(3, 'D 3908 TR', 'Daihatsu Terios', 7, 'Tersedia', 400000, 'uploads/mobil/daihatsu_terios.png');

INSERT INTO peminjaman
(id_member, id_mobil, tanggal_pinjam, rencana_kembali, lama_sewa, total_sewa, status_transaksi, pembayaran_dp)
VALUES
-- Vania: selesai normal, cabang barat
(1, 5, '2026-05-20', '2026-05-21', 1, 550000, 'Selesai', 275000),
-- Alicia: selesai dengan telat + light damage, cabang barat
(3, 5, '2026-05-24', '2026-05-25', 1, 550000, 'Selesai', 275000),
-- Sheryl: sedang dipinjam, cabang timur
(5, 7, '2026-05-27', '2026-05-29', 2, 1500000, 'Sedang Dipinjam', 750000),
-- Maria: menunggu cek awal, cabang utama
(2, 1, '2026-05-28', '2026-05-30', 2, 700000, 'Menunggu Cek Awal', 350000),
-- Vania: menunggu pelunasan, cabang barat
(1, 4, '2026-05-24', '2026-05-26', 2, 900000, 'Menunggu Pelunasan', 450000),
-- Rakha: menunggu cek akhir, cabang timur
(6, 8, '2026-05-26', '2026-05-28', 2, 1800000, 'Menunggu Cek Akhir', 900000);

INSERT INTO kondisi_mobil
(id_peminjaman, id_pegawai, jenis_kondisi, foto_kondisi, keterangan_kondisi, tanggal_upload)
VALUES
(1, 3, 'Awal', 'uploads/mobil/suzuki_jimny.png', 'Mobil bersih, ban normal, tidak ada kerusakan baru.', '2026-05-20'),
(1, 3, 'Akhir', 'uploads/mobil/suzuki_jimny.png', 'Mobil kembali dalam kondisi baik.', '2026-05-21'),
(2, 3, 'Awal', 'uploads/mobil/suzuki_jimny.png', 'Kondisi awal baik dan siap digunakan.', '2026-05-24'),
(2, 3, 'Akhir', 'uploads/mobil/suzuki_jimny.png', 'Ada baret kecil pada pintu kiri dan customer terlambat mengembalikan mobil.', '2026-05-27'),
(3, 4, 'Awal', 'uploads/mobil/toyota_fortuner.png', 'Kondisi awal baik, interior bersih, bahan bakar cukup.', '2026-05-27'),
(5, 3, 'Awal', 'uploads/mobil/hyundai_creta.png', 'Kondisi awal baik dan dokumen kendaraan lengkap.', '2026-05-24'),
(5, 3, 'Akhir', 'uploads/mobil/hyundai_creta.png', 'Terdapat penyok kecil pada bumper belakang.', '2026-05-27'),
(6, 4, 'Awal', 'uploads/mobil/hyundai_palisade.png', 'Kondisi awal sangat baik, interior bersih.', '2026-05-26');

INSERT INTO pengembalian
(id_peminjaman, id_pegawai, tanggal_kembali, keterlambatan, total_denda, total_bayar_sewa, sisa_bayar, status_pembayaran, tanggal_pelunasan)
VALUES
(1, 3, '2026-05-21', 0, 0, 550000, 275000, 'Lunas', '2026-05-21'),
(2, 3, '2026-05-27', 2, 150000, 700000, 425000, 'Lunas', '2026-05-27'),
(5, 3, '2026-05-27', 1, 200000, 1100000, 650000, 'Belum Lunas', NULL);

INSERT INTO detail_denda (id_pengembalian, nama_denda, nominal_denda) VALUES
(2, 'Light Damage', 50000),
(3, 'Medium Damage', 150000);

SELECT * FROM cabang;
SELECT * FROM member;
SELECT * FROM pegawai;
SELECT * FROM users;
SELECT * FROM mobil;
SELECT * FROM peminjaman;
SELECT * FROM pengembalian;
SELECT * FROM detail_denda;