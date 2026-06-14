-- Buat database
CREATE DATABASE IF NOT EXISTS pos_fotokopi;
USE pos_fotokopi;

-- Tabel Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL
);

-- Insert user default. Username: admin, Password: admin123
INSERT INTO users (username, password, nama_lengkap) 
VALUES ('admin', '$2y$10$wE.Q6fFhQoG3T7I2jJ1Bxe9XkXy1.W4wTzN0E8p4T8s1v5hL7Z8.G', 'Administrator');

-- Tabel Produk
CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(20) NOT NULL UNIQUE,
    nama_produk VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    jenis VARCHAR(50)
);

-- Tabel Gambar Produk (Relasi One to Many untuk Multiple Upload)
CREATE TABLE gambar_produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produk_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
);

-- Tabel Transaksi
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(12,2) NOT NULL,
    bayar DECIMAL(12,2) NOT NULL,
    kembali DECIMAL(12,2) NOT NULL,
    signature LONGTEXT, -- Menyimpan Base64 Signature
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Detail Transaksi
CREATE TABLE detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    qty INT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id)
);