/*
DDL - PINJEM MOBIL
Database: SQL Server/SSMS
*/

-- 1. Menghapus Data Lama
/*

Tabel dihapus dari yang memiliki foreign key terlebih dahulu,
lalu dilanjutkan ke tabel yang menjadi referensi.

Contoh:
- detail_denda memiliki id_pengembalian, jadi detail_denda
  harus dihapus sebelum pengembalian.
- peminjaman memiliki id_member dan id_mobil, jadi peminjaman
  harus dihapus sebelum member dan mobil.
- pegawai, member, dan mobil memiliki id_cabang, jadi ketinganya
  harus dihapus sebelum cabang.

Tujuannya agar tidak terjadi error foreign key saat menghapus data.

*/

DROP TABLE IF EXISTS detail_denda;
DROP TABLE IF EXISTS pengembalian;
DROP TABLE IF EXISTS kondisi_mobil;
DROP TABLE IF EXISTS peminjaman;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS mobil;
DROP TABLE IF EXISTS member;
DROP TABLE IF EXISTS pegawai;
DROP TABLE IF EXISTS cabang;

-- 2. Membuat tabel baru
-- Catatan : IDENTITIY(1,1) = membuat nilai kolom bertambah otomatis (auto increment) 

-- CABANG
CREATE TABLE cabang (
    id_cabang INT IDENTITY(1,1) PRIMARY KEY,
    nama_cabang VARCHAR(100) NOT NULL,
    alamat VARCHAR(255) NOT NULL
);

-- PEGAWAI
CREATE TABLE pegawai (
    id_pegawai INT IDENTITY(1,1) PRIMARY KEY,
    id_cabang INT NOT NULL,
    nama_pegawai VARCHAR(100) NOT NULL,
    nomor_telepon VARCHAR(20),
    email VARCHAR(100),
    alamat VARCHAR(255),
    jabatan VARCHAR(50),

    CONSTRAINT FK_Pegawai_Cabang
        FOREIGN KEY (id_cabang)
        REFERENCES cabang(id_cabang)
);

-- MEMBER
CREATE TABLE member (
    id_member INT IDENTITY(1,1) PRIMARY KEY,
    nama_member VARCHAR(100) NOT NULL,
    ktp VARCHAR(255),
    sim VARCHAR(255),
    alamat VARCHAR(255),
    nomor_telepon VARCHAR(20),
    email VARCHAR(100),
    tanggal_registrasi DATE,
    id_cabang INT NOT NULL,

    CONSTRAINT FK_Member_Cabang
        FOREIGN KEY (id_cabang)
        REFERENCES cabang(id_cabang)
);

-- USERS
CREATE TABLE users (
    id_user INT IDENTITY(1,1) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    id_member INT NULL,
    id_pegawai INT NULL,

    CONSTRAINT FK_Users_Member
        FOREIGN KEY (id_member)
        REFERENCES member(id_member),

    CONSTRAINT FK_Users_Pegawai
        FOREIGN KEY (id_pegawai)
        REFERENCES pegawai(id_pegawai)
);

-- MOBIL
CREATE TABLE mobil (
    id_mobil INT IDENTITY(1,1) PRIMARY KEY,
    id_cabang INT NOT NULL,
    nomor_polisi VARCHAR(20) NOT NULL,
    nama_mobil VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    status_mobil VARCHAR(50) NOT NULL,
    harga_sewa DECIMAL(12,2) NOT NULL,
    foto_mobil VARCHAR(255),

    CONSTRAINT FK_Mobil_Cabang
        FOREIGN KEY (id_cabang)
        REFERENCES cabang(id_cabang)
);

-- PEMINJAMAN
CREATE TABLE peminjaman (
    id_peminjaman INT IDENTITY(1,1) PRIMARY KEY,
    id_member INT NOT NULL,
    id_mobil INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    rencana_kembali DATE NOT NULL,
    lama_sewa INT NOT NULL,
    total_sewa DECIMAL(12,2) NOT NULL,
    status_transaksi VARCHAR(50) NOT NULL,
    pembayaran_dp DECIMAL(12,2) NOT NULL,

    CONSTRAINT FK_Peminjaman_Member
        FOREIGN KEY (id_member)
        REFERENCES member(id_member),

    CONSTRAINT FK_Peminjaman_Mobil
        FOREIGN KEY (id_mobil)
        REFERENCES mobil(id_mobil)
);

-- KONDISI MOBIL
CREATE TABLE kondisi_mobil (
    id_kondisi INT IDENTITY(1,1) PRIMARY KEY,
    id_peminjaman INT NOT NULL,
    id_pegawai INT NOT NULL,
    jenis_kondisi VARCHAR(20) NOT NULL,

    foto_depan VARCHAR(255),
    foto_belakang VARCHAR(255),
    foto_kiri VARCHAR(255),
    foto_kanan VARCHAR(255),
    foto_interior VARCHAR(255),

    keterangan_kondisi VARCHAR(MAX),
    tanggal_upload DATE,

    CONSTRAINT FK_Kondisi_Peminjaman
        FOREIGN KEY (id_peminjaman)
        REFERENCES peminjaman(id_peminjaman),

    CONSTRAINT FK_Kondisi_Pegawai
        FOREIGN KEY (id_pegawai)
        REFERENCES pegawai(id_pegawai)
);


-- PENGEMBALIAN
CREATE TABLE pengembalian (
    id_pengembalian INT IDENTITY(1,1) PRIMARY KEY,
    id_peminjaman INT NOT NULL,
    id_pegawai INT NOT NULL,

    tanggal_kembali DATE NOT NULL,
    keterlambatan INT DEFAULT 0,

    total_denda DECIMAL(12,2) DEFAULT 0,
    total_bayar_sewa DECIMAL(12,2) NOT NULL,
    sisa_bayar DECIMAL(12,2) DEFAULT 0,

    status_pembayaran VARCHAR(30),
    tanggal_pelunasan DATE NULL,

    CONSTRAINT FK_Pengembalian_Peminjaman
        FOREIGN KEY (id_peminjaman)
        REFERENCES peminjaman(id_peminjaman),

    CONSTRAINT FK_Pengembalian_Pegawai
        FOREIGN KEY (id_pegawai)
        REFERENCES pegawai(id_pegawai)
);


-- DETAIL DENDA
CREATE TABLE detail_denda (
    id_detail_denda INT IDENTITY(1,1) PRIMARY KEY,
    id_pengembalian INT NOT NULL,
    nama_denda VARCHAR(100) NOT NULL,
    nominal_denda DECIMAL(12,2) NOT NULL,

    CONSTRAINT FK_DetailDenda_Pengembalian
        FOREIGN KEY (id_pengembalian)
        REFERENCES pengembalian(id_pengembalian)
);

--Catatan:
/*Jika constraint ini sudah pernah dibuat sebelumnya,
perintah ini bisa menghasilkan error karena nama constraint
sudah ada. Jadi bagian ini biasanya cukup dijalankan sekali saja
saat setup database.
*/
ALTER TABLE mobil
ADD CONSTRAINT UQ_mobil_nomor_polisi UNIQUE(nomor_polisi);


/*
DML - PINJEM MOBIL
Database: SQL Server/SSMS
DATA DUMMY
*/


-- 3. Insert Data Cabang
/*

Dieksekusi saat setup awal database / menambahkan cabang baru

Tabel cabang berisi daftar cabang rental mobil.
Data cabang harus dimasukkan terlebih dahulu karena
tabel pegawai, member, dan mobil membutuhkan id_cabang.

*/
-- Data Master
INSERT INTO cabang (nama_cabang, alamat) VALUES
('Pinjem Mobil Cabang Utama', 'Jl. Anggrek No. 10, Bandung'),
('Pinjem Mobil Cabang Barat', 'Jl. Mawar No. 21, Bandung'),
('Pinjem Mobil Cabang Timur', 'Jl. Tulip No. 8, Bandung'),
('Pinjem Mobil Cabang Selatan', 'Jl. Matahari No. 9, Bandung'),
('Pinjem Mobil Cabang Utara', 'Jl. Melati No. 18, Bandung');

-- 4. Insert Data Pegawai
/*

Dieksekusi saat menambahkan pegawai baru ke cabang

Tabel pegawai berisi data pegawai yang bekerja di tiap cabang.
Kolom id_cabang menunjukkan pegawai tersebut bekerja di cabang mana.

*/

--Data Master
INSERT INTO pegawai (id_cabang, nama_pegawai, nomor_telepon, email, alamat, jabatan) VALUES
(1, 'Admin Utama', '081234567801', 'adminutama@gmail.com', 'Jl. Bukit Jarian No. 12', 'Pemilik'),
(1, 'Admin Cabang Utama', '081234567802', 'admincabang@gmail.com', 'Jl. Ciumbuleuit No. 45', 'Admin Cabang'),
(2, 'Pegawai Barat', '081234567803', 'pegawaibarat@gmail.com', 'Jl. Cibaduyut No. 17', 'Petugas Cek Kondisi'),
(3, 'Pegawai Timur', '081234567804', 'pegawaitimur@gmail.com', 'Jl. Antapani No. 3', 'Petugas Cek Kondisi'),
(4, 'Pegawai Selatan', '089876543211', 'pegawaiselatan@gmail.com', 'Jl. Cicendo No. 7', 'Petugas Cek Kondisi'),
(5, 'Pegawai Utara', '081122334455', 'pegawaiutara@gmail.com', 'Jl. Pasirkaliki No. 9', 'Petugas Cek Kondisi');

-- 5. Insert Data Member
/*

Dieksekusi saat member mendaftar di cabang tertentu

Tabel member berisi data customer yang sudah terdaftar.
Kolom ktp dan sim menyimpan path file upload identitas customer.
Kolom id_cabang menunjukkan cabang tempat member terdaftar.

*/

--Data Master 
INSERT INTO member (nama_member, ktp, sim, alamat, nomor_telepon, email, tanggal_registrasi, id_cabang) VALUES
('Dodo', 'uploads/identitas/ktp_dodo.png', 'uploads/identitas/sim_dodo.png', 'Jl. Sukajadi No. 15, Bandung', '082129284461', 'dodo@gmail.com', '2026-05-20', 2),
('Kapi', 'uploads/identitas/ktp_kapi.png', 'uploads/identitas/sim_kapi.png', 'Jl. Dago No. 22, Bandung', '082145678902', 'kapi@gmail.com', '2026-05-21', 2),
('Alicia', 'uploads/identitas/ktp_alicia.png', 'uploads/identitas/sim_alicia.png', 'Jl. Riau No. 8, Bandung', '082156789013', 'alicia@gmail.com', '2026-05-22', 2),
('Felice', 'uploads/identitas/ktp_felice.png', 'uploads/identitas/sim_felice.png', 'Jl. Braga No. 11, Bandung', '082167890124', 'felice@gmail.com', '2026-05-23', 1),
('Sheryl', 'uploads/identitas/ktp_sheryl.png', 'uploads/identitas/sim_sheryl.png', 'Jl. Pasteur No. 5, Bandung', '082178901235', 'sheryl@gmail.com', '2026-05-24', 3),
('Rakha', 'uploads/identitas/ktp_rakha.png', 'uploads/identitas/sim_rakha.png', 'Jl. Buah Batu No. 19, Bandung', '082189012346', 'rakha@gmail.com', '2026-05-25', 3),
('Wombat', 'uploads/identitas/ktp_wombat.jpg', 'uploads/identitas/sim_wombat.jpg', 'Jl. Setiabudi No. 55, Bandung', '081122334472', 'wombat@gmail.com', '2026-05-26', 4),
('Wovey', 'uploads/identitas/ktp_wovey.jpg', 'uploads/identitas/sim_wovey.jpg', 'Jl. Ciumbuleuit No. 37, Bandung', '089988776655', 'wovey@gmail.com', '2026-05-27', 5);

-- 6. Insert Data Users
/*

Dieksekusi saat membuat akun login (admin, pegawai, atau customer)

Tabel users digunakan untuk login aplikasi.

Keterangan role:
- admin    : pengguna dengan akses admin
- pegawai  : pegawai cabang/petugas cek kondisi
- customer : member/customer rental mobil

Kolom id_member diisi jika user adalah customer.
Kolom id_pegawai diisi jika user adalah admin atau pegawai.

*/

-- Data Master
INSERT INTO users (nama, username, password, role, id_member, id_pegawai) VALUES
('Admin Utama', 'admin', 'admin', 'admin', NULL, 1),
('Admin Cabang Utama', 'admincabang', 'admincabang', 'admin', NULL, 2),
('Pegawai Barat', 'pegawaibarat', 'pegawaibarat', 'pegawai', NULL, 3),
('Pegawai Timur', 'pegawaitimur', 'pegawaitimur', 'pegawai', NULL, 4),
('Pegawai Selatan', 'pegawaiselatan', 'pegawaiselatan', 'pegawai', NULL, 5),
('Pegawai Utara', 'pegawaiutara', 'pegawaiutara', 'pegawai', NULL, 6),
('Dodo', 'dodo', 'dodo', 'customer', 1, NULL),
('kapi', 'kapi', 'kapi', 'customer', 2, NULL),
('Alicia', 'alicia', 'alicia', 'customer', 3, NULL),
('Felice', 'felice', 'felice', 'customer', 4, NULL),
('Sheryl', 'sheryl', 'sheryl', 'customer', 5, NULL),
('Rakha', 'rakha', 'rakha', 'customer', 6, NULL),
('Wombat', 'wombat', 'wombat', 'customer', 7, NULL),
('Wovey', 'wovey', 'wovey', 'customer', 8, NULL);


-- 7. Insert Data Mobil
/*

Dieksekusi saat menambahkan mobil baru ke cabang

Tabel mobil berisi data kendaraan yang tersedia di setiap cabang.
Kolom status_mobil digunakan untuk menunjukkan status mobil,
misalnya Tersedia atau Sedang Dipinjam.
Kolom foto_mobil menyimpan path gambar mobil.

*/

-- Data Master
INSERT INTO mobil (id_cabang, nomor_polisi, nama_mobil, kapasitas, status_mobil, harga_sewa, foto_mobil) VALUES
(1, 'D 1201 AB', 'Toyota Avanza', 7, 'Tersedia', 350000, 'uploads/mobil/toyota_avanza.png'),
(1, 'D 1402 IN', 'Toyota Innova', 7, 'Tersedia', 500000, 'uploads/mobil/toyota_innova.png'),
(1, 'D 1703 XP', 'Mitsubishi Xpander', 7, 'Tersedia', 420000, 'uploads/mobil/mitsubishi_xpander.png'),
(2, 'D 2204 CR', 'Hyundai Creta', 5, 'Dipinjam', 450000, 'uploads/mobil/hyundai_creta.png'),
(2, 'DK 555 A', 'Suzuki Jimny', 4, 'Dipinjam', 550000, 'uploads/mobil/suzuki_jimny.png'),
(2, 'D 2605 CV', 'Honda Civic', 5, 'Dipinjam', 600000, 'uploads/mobil/honda_civic.png'),
(3, 'D 3306 FT', 'Toyota Fortuner', 7, 'Dipinjam', 750000, 'uploads/mobil/toyota_fortuner.png'),
(3, 'D 3707 PL', 'Hyundai Palisade', 7, 'Dipinjam', 900000, 'uploads/mobil/hyundai_palisade.png'),
(3, 'D 3908 TR', 'Daihatsu Terios', 7, 'Tersedia', 500000, 'uploads/mobil/daihatsu_terios.png'),
(4, 'D 1234 MIB', 'Honda Brio', 4, 'Tersedia', 350000, 'uploads/mobil/honda_brio.jpg'),
(5, 'D 1111 A', 'Mercedes-Benz C200', 4, 'Tersedia', 1000000, 'uploads/mobil/mercedes_benz_c200.jpg');

-- 8. Insert Data Peminjaman
/*

Dieksekusi saat member melakukan peminjaman mobil

Tabel peminjaman berisi transaksi sewa mobil.

Keterangan beberapa status_transaksi:
- Selesai			 : transaksi sudah selesai
- Sedang Dipinjam	 : mobil masih digunakan customer
- Menunggu Cek Awal	 : menunggu pengecekan kondisi awal mobil
- Menunggu Pelunasan : customer belum melunasi pembayaran
- Menunggu Cek Akhir : mobil sudah kembali, menunggu cek akhir

Kolom total_sewa dihitung berdasarkan:
harga_sewa mobil * lama sewa.

Kolom pembayaran_dp berisi uang muka pembayaran.

*/

-- Data transaksi
INSERT INTO peminjaman
(id_member, id_mobil, tanggal_pinjam, rencana_kembali, lama_sewa, total_sewa, status_transaksi, pembayaran_dp)
VALUES

(1, 1, '2026-04-01', '2026-04-03', 2, 700000, 'Selesai', 350000), -- 1
(2, 2, '2026-04-02', '2026-04-04', 2, 1000000, 'Selesai', 500000), -- 2
(3, 3, '2026-04-03', '2026-04-06', 3, 1260000, 'Selesai', 630000), -- 3
(4, 4, '2026-04-05', '2026-04-06', 1, 450000, 'Selesai', 225000), -- 4
(5, 5, '2026-04-06', '2026-04-08', 2, 1100000, 'Selesai', 550000), -- 5
(6, 6, '2026-04-07', '2026-04-08', 1, 600000, 'Selesai', 300000), -- 6
(1, 7, '2026-04-09', '2026-04-11', 2, 1500000, 'Selesai', 750000), -- 7
(2, 8, '2026-04-10', '2026-04-12', 2, 1800000, 'Selesai', 900000), -- 8
(3, 9, '2026-04-11', '2026-04-12', 1, 400000, 'Selesai', 200000), -- 9
(4, 1, '2026-04-12', '2026-04-15', 3, 1050000, 'Selesai', 525000), -- 10
(5, 2, '2026-04-14', '2026-04-16', 2, 1000000, 'Selesai', 500000), -- 11
(6, 3, '2026-04-15', '2026-04-16', 1, 420000, 'Selesai', 210000), -- 12
(1, 4, '2026-04-17', '2026-04-19', 2, 900000, 'Selesai', 450000), -- 13
(2, 5, '2026-04-18', '2026-04-20', 2, 1100000, 'Selesai', 550000), -- 14
(3, 6, '2026-04-20', '2026-04-22', 2, 1200000, 'Selesai', 600000), -- 15
(4, 7, '2026-04-21', '2026-04-22', 1, 750000, 'Selesai', 375000), -- 16
(5, 8, '2026-04-22', '2026-04-25', 3, 2700000, 'Selesai', 1350000), -- 17
(6, 9, '2026-04-23', '2026-04-25', 2, 800000, 'Selesai', 400000), -- 18
(1, 1, '2026-04-26', '2026-04-27', 1, 350000, 'Selesai', 175000), -- 19
(2, 2, '2026-04-27', '2026-04-29', 2, 1000000, 'Selesai', 500000), -- 20
(3, 3, '2026-04-28', '2026-04-30', 2, 840000, 'Selesai', 420000), -- 21
(4, 4, '2026-05-01', '2026-05-02', 1, 450000, 'Selesai', 225000), -- 22
(5, 5, '2026-05-02', '2026-05-05', 3, 1650000, 'Selesai', 825000), -- 23
(6, 6, '2026-05-03', '2026-05-05', 2, 1200000, 'Selesai', 600000), -- 24
(1, 7, '2026-05-06', '2026-05-07', 1, 750000, 'Selesai', 375000), -- 25
(2, 8, '2026-05-07', '2026-05-09', 2, 1800000, 'Selesai', 900000), -- 26
(3, 9, '2026-05-09', '2026-05-11', 2, 800000, 'Menunggu Pelunasan', 400000), -- 27
(4, 1, '2026-05-10', '2026-05-12', 2, 700000, 'Menunggu Pelunasan', 350000), -- 28
(5, 2, '2026-05-11', '2026-05-14', 3, 1500000, 'Menunggu Pelunasan', 750000), -- 29
(6, 3, '2026-05-12', '2026-05-13', 1, 420000, 'Menunggu Pelunasan', 210000), -- 30
(1, 5, '2026-05-13', '2026-05-15', 2, 1100000, 'Menunggu Pelunasan', 550000), -- 31
(1, 4, '2026-06-01', '2026-06-03', 2, 900000, 'Sedang Dipinjam', 450000), -- 32
(2, 5, '2026-06-02', '2026-06-05', 3, 1650000, 'Sedang Dipinjam', 825000), -- 33
(3, 6, '2026-06-03', '2026-06-04', 1, 600000, 'Menunggu Cek Akhir', 300000), -- 34
(4, 7, '2026-06-04', '2026-06-06', 2, 1500000, 'Menunggu Cek Awal', 750000), -- 35
(5, 8, '2026-06-05', '2026-06-07', 2, 1800000, 'Menunggu Cek Awal', 900000); -- 36

-- 9. Insert Data Kondisi Mobil
/*

Dieksekusi saat pegawai melakukan pengecekan kondisi mobil sebelum atau sesudah peminjaman

Tabel kondisi_mobil digunakan untuk mencatat kondisi mobil
sebelum dan sesudah peminjaman.

jenis_kondisi:
- Awal	: kondisi mobil sebelum diserahlan ke customer
- Akhir : kondisi mobil setelah dikembalikan customer

Data ini digunakan sebagai bukti apabila ada kerusakan
atau perbedaan kondisi mobil saat kembali.

*/

-- Data transaksi
INSERT INTO kondisi_mobil
(id_peminjaman,id_pegawai,jenis_kondisi,foto_depan,foto_belakang,foto_kiri,foto_kanan,foto_interior,keterangan_kondisi,tanggal_upload)
VALUES

(1,3,'Awal','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Mobil siap digunakan.','2026-04-01'),
(2,4,'Awal','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Kondisi awal baik.','2026-04-02'),
(3,3,'Awal','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Mobil bersih dan siap jalan.','2026-04-03'),
(4,4,'Awal','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','Kondisi normal.','2026-04-05'),
(5,3,'Awal','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Mobil siap digunakan.','2026-04-06'),
(6,4,'Awal','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','Interior bersih.','2026-04-07'),

(7,3,'Awal','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','Kondisi baik.','2026-04-09'),
(8,4,'Awal','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','Kondisi sangat baik.','2026-04-10'),
(9,3,'Awal','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','Siap digunakan.','2026-04-11'),

(10,4,'Awal','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Kondisi baik.','2026-04-12'),
(11,3,'Awal','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Siap digunakan.','2026-04-14'),
(12,4,'Awal','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Tidak ada kerusakan.','2026-04-15'),

(13,3,'Awal','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','Mobil siap dipakai.','2026-04-17'),
(14,4,'Awal','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Ban normal.','2026-04-18'),
(15,3,'Awal','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','Kondisi bagus.','2026-04-20'),

(16,4,'Awal','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','Siap jalan.','2026-04-21'),
(17,3,'Awal','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','Interior bersih.','2026-04-22'),
(18,4,'Awal','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','Kondisi baik.','2026-04-23'),

(19,3,'Awal','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Siap digunakan.','2026-04-26'),
(20,4,'Awal','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Kondisi baik.','2026-04-27'),
(21,3,'Awal','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Tidak ada masalah.','2026-04-28'),

(22,4,'Awal','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','Normal.','2026-05-01'),
(23,3,'Awal','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Kondisi sangat baik.','2026-05-02'),
(24,4,'Awal','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','Bersih.','2026-05-03'),

(25,3,'Awal','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','Normal.','2026-05-06'),
(26,4,'Awal','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','Kondisi baik.','2026-05-07'),

(27,4,'Awal','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','Mobil siap digunakan.','2026-05-09'),
(28,3,'Awal','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Kondisi baik.','2026-05-10'),
(29,4,'Awal','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Normal.','2026-05-11'),
(30,3,'Awal','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Siap dipakai.','2026-05-12'),
(31,3,'Awal','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Mobil bagus.','2026-05-13'),

(1,3,'Akhir','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Kembali dalam kondisi baik.','2026-04-03'),
(2,4,'Akhir','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Tidak ada kerusakan.','2026-04-04'),

(3,3,'Akhir','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Ada goresan ringan sisi kiri.','2026-04-07'),

(4,4,'Akhir','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','Mobil kembali normal tanpa kerusakan.','2026-04-06'),

(5,3,'Akhir','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Ada penyok ringan bumper belakang.','2026-04-08'),

(6,4,'Akhir','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','Kondisi tetap baik.','2026-04-08'),

(7,3,'Akhir','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','Mobil kembali dalam kondisi baik.','2026-04-11'),

(8,4,'Akhir','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','Terlambat 1 hari, kondisi baik.','2026-04-13'),

(9,3,'Akhir','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','Tidak ada kerusakan tambahan.','2026-04-12'),

(10,4,'Akhir','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Kembali normal.','2026-04-15'),

(11,3,'Akhir','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Kondisi baik saat pengembalian.','2026-04-16'),

(12,4,'Akhir','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Tidak ditemukan masalah.','2026-04-16'),

(13,3,'Akhir','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','Mobil kembali bersih.','2026-04-19'),

(14,4,'Akhir','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Ada goresan kecil bagian kiri.','2026-04-20'),

(15,3,'Akhir','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','Kondisi aman.','2026-04-22'),

(16,4,'Akhir','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','Tidak ada perubahan kondisi.','2026-04-22'),

(17,3,'Akhir','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','Ada baret kecil pintu kanan.','2026-04-26'),

(18,4,'Akhir','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','Mobil kembali baik.','2026-04-25'),

(19,3,'Akhir','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Tidak ada kerusakan.','2026-04-27'),

(20,4,'Akhir','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Kembali normal.','2026-04-29'),

(21,3,'Akhir','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Kondisi masih baik.','2026-04-30'),

(22,4,'Akhir','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','Pengembalian normal.','2026-05-02'),

(23,3,'Akhir','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Mobil kembali baik.','2026-05-05'),

(24,4,'Akhir','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','Ada keterlambatan 1 hari.','2026-05-06'),

(25,3,'Akhir','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','uploads/kondisi/kondisi_toyota_fortuner.jpg','Mobil baik saat kembali.','2026-05-07'),

(26,4,'Akhir','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','uploads/kondisi/kondisi_hyundai_palisade.jpg','Tidak ditemukan kerusakan.','2026-05-09'),

(28,3,'Akhir','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','uploads/kondisi/kondisi_toyota_avanza.jpg','Belum melakukan pelunasan akhir.','2026-05-12'),

(29,4,'Akhir','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','uploads/kondisi/kondisi_toyota_innova.jpg','Mobil kembali normal.','2026-05-14'),

(30,3,'Akhir','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','uploads/kondisi/kondisi_mitsubishi_xpander.jpg','Kondisi aman saat dicek.','2026-05-13'),

(31,3,'Akhir','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Pembayaran belum lunas.','2026-05-15'),


(27,4,'Akhir','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','uploads/kondisi/kondisi_daihatsu_terios.jpg','Belum melakukan pelunasan akhir.','2026-05-10'),

(32,3,'Awal','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','uploads/kondisi/kondisi_hyundai_creta.jpg','Sedang digunakan customer.','2026-06-01'),

(33,3,'Awal','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','uploads/kondisi/kondisi_suzuki_jimny.jpg','Masih dipinjam.','2026-06-02'),

(34,4,'Awal','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','uploads/kondisi/kondisi_honda_civic.jpg','Menunggu cek akhir.','2026-06-03');

-- 10. Insert Data Pengembalian
/*

Dieksekusi saat customer mengembalikan mobil dan pembayaran dicatat

Tabel pengembalian menyimpan data saat mobil dikembalikan.

Kolom keterlambatan berisi jumlah hari keterlambatan.
Kolom total_denda berisi total denda yang dikenakan.
Kolom total_bayar_sewa berisi total sewa akhir, bisa termasuk
tambahan akibat keterlambatan.
Kolom sisa_bayar menunjukkan sisa pembayaran setelah DP.
Kolom status_pembayaran menunjukkan apakah pembayaran sudah lunas.

*/

-- Data Transaksi
INSERT INTO pengembalian
(id_peminjaman, id_pegawai, tanggal_kembali, keterlambatan, total_denda, total_bayar_sewa, sisa_bayar, status_pembayaran, tanggal_pelunasan)
VALUES
(1, 3, '2026-04-03', 0, 0, 700000, 350000, 'Lunas', '2026-04-03'),
(2, 4, '2026-04-04', 0, 0, 1000000, 500000, 'Lunas', '2026-04-04'),
(3, 3, '2026-04-07', 1, 50000, 1310000, 680000, 'Lunas', '2026-04-07'),
(4, 4, '2026-04-06', 0, 0, 450000, 225000, 'Lunas', '2026-04-06'),
(5, 3, '2026-04-08', 0, 0, 1100000, 550000, 'Lunas', '2026-04-08'),
(6, 4, '2026-04-08', 0, 0, 600000, 300000, 'Lunas', '2026-04-08'),
(7, 3, '2026-04-11', 0, 0, 1500000, 750000, 'Lunas', '2026-04-11'),
(8, 4, '2026-04-13', 1, 50000, 1850000, 950000, 'Lunas', '2026-04-13'),
(9, 3, '2026-04-12', 0, 0, 400000, 200000, 'Lunas', '2026-04-12'),
(10, 4, '2026-04-15', 0, 0, 1050000, 525000, 'Lunas', '2026-04-15'),
(11, 3, '2026-04-16', 0, 0, 1000000, 500000, 'Lunas', '2026-04-16'),
(12, 4, '2026-04-16', 0, 0, 420000, 210000, 'Lunas', '2026-04-16'),
(13, 3, '2026-04-19', 0, 0, 900000, 450000, 'Lunas', '2026-04-19'),
(14, 4, '2026-04-20', 0, 0, 1100000, 550000, 'Lunas', '2026-04-20'),
(15, 3, '2026-04-22', 0, 0, 1200000, 600000, 'Lunas', '2026-04-22'),
(16, 4, '2026-04-22', 0, 0, 750000, 375000, 'Lunas', '2026-04-22'),
(17, 3, '2026-04-26', 1, 50000, 2750000, 1400000, 'Lunas', '2026-04-26'),
(18, 4, '2026-04-25', 0, 0, 800000, 400000, 'Lunas', '2026-04-25'),
(19, 3, '2026-04-27', 0, 0, 350000, 175000, 'Lunas', '2026-04-27'),
(20, 4, '2026-04-29', 0, 0, 1000000, 500000, 'Lunas', '2026-04-29'),
(21, 3, '2026-04-30', 0, 0, 840000, 420000, 'Lunas', '2026-04-30'),
(22, 4, '2026-05-02', 0, 0, 450000, 225000, 'Lunas', '2026-05-02'),
(23, 3, '2026-05-05', 0, 0, 1650000, 825000, 'Lunas', '2026-05-05'),
(24, 4, '2026-05-06', 1, 50000, 1250000, 650000, 'Lunas', '2026-05-06'),
(25, 3, '2026-05-07', 0, 0, 750000, 375000, 'Lunas', '2026-05-07'),
(26, 4, '2026-05-09', 0, 0, 1800000, 900000, 'Lunas', '2026-05-09'),
(27, 4, '2026-05-10', 0, 0, 1800000, 900000, 'Belum Lunas', NULL),
(28, 3, '2026-05-12', 1, 50000, 850000, 450000, 'Belum Lunas', NULL),
(29, 4, '2026-05-12', 0, 0, 700000, 350000, 'Belum Lunas', NULL),
(30, 3, '2026-05-14', 0, 0, 1500000, 750000, 'Belum Lunas', NULL),
(31, 4, '2026-05-13', 0, 0, 420000, 210000, 'Belum Lunas', NULL);

-- 11. Insert Detail Denda
/*

Dieksekusi saat customer mengembalikan mobil dan pembayaran dicatat

Tabel detail_denda menyimpan rincian jenis denda.
Satu data pengembalian bisa memiliki satu atau lebih detail denda.

Contoh:
- Light Damage  : kerusakan ringan
- Medium Damage : kerusakan sedang

*/

INSERT INTO detail_denda (id_pengembalian, nama_denda, nominal_denda) VALUES
(2, 'Light Damage', 50000),
(3, 'Medium Damage', 150000),
(5, 'Medium Damage',150000),
(18, 'Light Damage', 50000),
(28, 'Medium Damage', 150000);

SELECT * FROM cabang
SELECT * FROM pegawai
SELECT * FROM member
SELECT * FROM users
SELECT * FROM mobil
SELECT * FROM peminjaman
SELECT * FROM kondisi_mobil
SELECT * FROM pengembalian
SELECT * FROM detail_denda
