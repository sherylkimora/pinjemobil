/*
DUMMY DATA - PINJEM MOBIL
Database: SQL Server/SSMS

Keterangan:
Script ini digunakan untuk menghapus data lama,
mereset nomor identity/auto-increment, lalu mengisi ulang
data dummy untuk kebutuhan testing aplikasi Pinjem Mobil

Catatan:
- Ada sedikit bagian DDL, yaitu ALTER TABLE untuk menambah constraint UNIQUE
- DBCC CHECKIDENT digunakan agar identity kembali mulai dari 1 setelah data
  dihapus menggunakan delete from
*/

-- 1. Menghapus Data Lama
/*

Data dihapus dari tabel yang memiliki foreign key terlebih dahulu,
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

DELETE FROM detail_denda;
DELETE FROM pengembalian;
DELETE FROM kondisi_mobil;
DELETE FROM peminjaman;
DELETE FROM users;
DELETE FROM mobil;
DELETE FROM member;
DELETE FROM pegawai;
DELETE FROM cabang;

-- 2. Reset Identity/Auto-Increment
/*

DBCC CHECKIDENT digunaakn untuk mereset nilai identity.

Contoh:
Kalau sebelumnya id_mobil sudah sampai 9,
lalu data dihapus dengan delete from,
maka insert berikutnya akan lanjut ke id_mobil = 10.

Dengan DBCC CHECKIDENT RESEED, 0,
insert berikutnya akan mulai lagi dari 1.

Ini penting karena dummy data di bawah masih menggunakan
foreign key berdasarkan ID yang sudah diprediksi,
misalnya id_member = 1, id_mobil = 5, id_pegawai = 3, dan seterusnya.

*/

DBCC CHECKIDENT ('detail_denda', RESEED, 0);
DBCC CHECKIDENT ('pengembalian', RESEED, 0);
DBCC CHECKIDENT ('kondisi_mobil', RESEED, 0);
DBCC CHECKIDENT ('peminjaman', RESEED, 0);
DBCC CHECKIDENT ('users', RESEED, 0);
DBCC CHECKIDENT ('mobil', RESEED, 0);
DBCC CHECKIDENT ('member', RESEED, 0);
DBCC CHECKIDENT ('pegawai', RESEED, 0);
DBCC CHECKIDENT ('cabang', RESEED, 0);

-- 3. Insert Data Cabang
/*

Dieksekusi saat setup awal database / menambahkan cabang baru

Tabel cabang berisi daftar cabang rental mobil.
Data cabang harus dimasukkan terlebih dahulu karena
tabel pegawai, member, dan mobil membutuhkan id_cabang.

*/

INSERT INTO cabang (nama_cabang, alamat) VALUES
('Pinjem Mobil Cabang Utama', 'Jl. Anggrek No. 10, Bandung'),
('Pinjem Mobil Cabang Barat', 'Jl. Mawar No. 21, Bandung'),
('Pinjem Mobil Cabang Timur', 'Jl. Tulip No. 8, Bandung');

-- 4. Insert Data Pegawai
/*

Dieksekusi saat menambahkan pegawai baru ke cabang

Tabel pegawai berisi data pegawai yang bekerja di tiap cabang.
Kolom id_cabang menunjukkan pegawai tersebut bekerja di cabang mana.

*/

INSERT INTO pegawai (id_cabang, nama_pegawai, nomor_telepon, email, alamat, jabatan) VALUES
(1, 'Admin Utama', '081234567801', 'adminutama@gmail.com', 'Jl. Bukit Jarian No. 12', 'Pemilik'),
(1, 'Admin Cabang Utama', '081234567802', 'admincabang@gmail.com', 'Jl. Ciumbuleuit No. 45', 'Admin Cabang'),
(2, 'Pegawai Barat', '081234567803', 'pegawaibarat@gmail.com', 'Jl. Cibaduyut No. 17', 'Petugas Cek Kondisi'),
(3, 'Pegawai Timur', '081234567804', 'pegawaitimur@gmail.com', 'Jl. Antapani No. 3', 'Petugas Cek Kondisi');

-- 5. Insert Data Member
/*

Dieksekusi saat member mendaftar di cabang tertentu

Tabel member berisi data customer yang sudah terdaftar.
Kolom ktp dan sim menyimpan path file upload identitas customer.
Kolom id_cabang menunjukkan cabang tempat member terdaftar.

*/

INSERT INTO member (nama_member, ktp, sim, alamat, nomor_telepon, email, tanggal_registrasi, id_cabang) VALUES
('Dodo', 'uploads/identitas/ktp_dodo.png', 'uploads/identitas/sim_dodo.png', 'Jl. Sukajadi No. 15, Bandung', '082129284461', 'dodo@gmail.com', '2026-05-20', 2),
('Kapi', 'uploads/identitas/ktp_kapi.png', 'uploads/identitas/sim_kapi.png', 'Jl. Dago No. 22, Bandung', '082145678902', 'kapi@gmail.com', '2026-05-21', 1),
('Alicia', 'uploads/identitas/ktp_alicia.png', 'uploads/identitas/sim_alicia.png', 'Jl. Riau No. 8, Bandung', '082156789013', 'alicia@gmail.com', '2026-05-22', 2),
('Felice', 'uploads/identitas/ktp_felice.png', 'uploads/identitas/sim_felice.png', 'Jl. Braga No. 11, Bandung', '082167890124', 'felice@gmail.com', '2026-05-23', 1),
('Sheryl', 'uploads/identitas/ktp_sheryl.png', 'uploads/identitas/sim_sheryl.png', 'Jl. Pasteur No. 5, Bandung', '082178901235', 'sheryl@gmail.com', '2026-05-24', 3),
('Rakha', 'uploads/identitas/ktp_rakha.png', 'uploads/identitas/sim_rakha.png', 'Jl. Buah Batu No. 19, Bandung', '082189012346', 'rakha@gmail.com', '2026-05-25', 3);

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

INSERT INTO users (nama, username, password, role, id_member, id_pegawai) VALUES
('Admin Utama', 'admin', 'admin', 'admin', NULL, 1),
('Admin Cabang Utama', 'admincabang', 'admincabang', 'admin', NULL, 2),
('Pegawai Barat', 'pegawaibarat', 'pegawaibarat', 'pegawai', NULL, 3),
('Pegawai Timur', 'pegawaitimur', 'pegawaitimur', 'pegawai', NULL, 4),
('Dodo', 'dodo', 'dodo', 'customer', 1, NULL),
('kapi', 'kapi', 'kapi', 'customer', 2, NULL),
('Alicia', 'alicia', 'alicia', 'customer', 3, NULL),
('Felice', 'felice', 'felice', 'customer', 4, NULL),
('Sheryl', 'sheryl', 'sheryl', 'customer', 5, NULL),
('Rakha', 'rakha', 'rakha', 'customer', 6, NULL);

-- 7. Insert Data Mobil
/*

Dieksekusi saat menambahkan mobil baru ke cabang

Tabel mobil berisi data kendaraan yang tersedia di setiap cabang.
Kolom status_mobil digunakan untuk menunjukkan status mobil,
misalnya Tersedia atau Sedang Dipinjam.
Kolom foto_mobil menyimpan path gambar mobil.

*/

INSERT INTO mobil (id_cabang, nomor_polisi, nama_mobil, kapasitas, status_mobil, harga_sewa, foto_mobil) VALUES
(1, 'D 1201 AB', 'Toyota Avanza', 7, 'Tersedia', 350000, 'uploads/mobil/toyota_avanza.png'),
(1, 'D 1402 IN', 'Toyota Innova', 7, 'Tersedia', 500000, 'uploads/mobil/toyota_innova.png'),
(1, 'D 1703 XP', 'Mitsubishi Xpander', 7, 'Tersedia', 420000, 'uploads/mobil/mitsubishi_xpander.png'),
(2, 'D 2204 CR', 'Hyundai Creta', 5, 'Tersedia', 450000, 'uploads/mobil/hyundai_creta.png'),
(2, 'DK 555 A', 'Suzuki Jimny', 4, 'Tersedia', 550000, 'uploads/mobil/suzuki_jimny.png'),
(2, 'D 2605 CV', 'Honda Civic', 5, 'Tersedia', 600000, 'uploads/mobil/honda_civic.png'),
(3, 'D 3306 FT', 'Toyota Fortuner', 7, 'Tersedia', 750000, 'uploads/mobil/toyota_fortuner.png'),
(3, 'D 3707 PL', 'Hyundai Palisade', 7, 'Tersedia', 900000, 'uploads/mobil/hyundai_palisade.png'),
(3, 'D 3908 TR', 'Daihatsu Terios', 7, 'Tersedia', 400000, 'uploads/mobil/daihatsu_terios.png');

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

INSERT INTO peminjaman
(id_member, id_mobil, tanggal_pinjam, rencana_kembali, lama_sewa, total_sewa, status_transaksi, pembayaran_dp)
VALUES
-- Dodo: selesai normal, cabang barat
(1, 5, '2026-05-20', '2026-05-21', 1, 550000, 'Selesai', 275000),
-- Alicia: selesai dengan telat + light damage, cabang barat
(3, 5, '2026-05-24', '2026-05-25', 1, 550000, 'Selesai', 275000),
-- Sheryl: sedang dipinjam, cabang timur
(5, 7, '2026-05-27', '2026-05-29', 2, 1500000, 'Sedang Dipinjam', 750000),
-- Kapi: menunggu cek awal, cabang utama
(2, 1, '2026-05-28', '2026-05-30', 2, 700000, 'Menunggu Cek Awal', 350000),
-- Dodo: menunggu pelunasan, cabang barat
(1, 4, '2026-05-24', '2026-05-26', 2, 900000, 'Menunggu Pelunasan', 450000),
-- Rakha: menunggu cek akhir, cabang timur
(6, 8, '2026-05-26', '2026-05-28', 2, 1800000, 'Menunggu Cek Akhir', 900000);

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

INSERT INTO pengembalian
(id_peminjaman, id_pegawai, tanggal_kembali, keterlambatan, total_denda, total_bayar_sewa, sisa_bayar, status_pembayaran, tanggal_pelunasan)
VALUES
(1, 3, '2026-05-21', 0, 0, 550000, 275000, 'Lunas', '2026-05-21'),
(2, 3, '2026-05-27', 2, 150000, 700000, 425000, 'Lunas', '2026-05-27'),
(5, 3, '2026-05-27', 1, 200000, 1100000, 650000, 'Belum Lunas', NULL);

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
(3, 'Medium Damage', 150000);