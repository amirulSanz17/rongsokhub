-- Create database
CREATE DATABASE IF NOT EXISTS rongsokhub;
USE rongsokhub;

-- Users table
CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    phone VARCHAR(20),
    role ENUM('warga','pengepul','admin'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: password)
INSERT INTO users (nama, email, password, phone, role) VALUES 
('Admin RongsokHub', 'admin@rongsokhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08123456789', 'admin');

-- Collector profiles
CREATE TABLE collector_profiles(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nama_usaha VARCHAR(150),
    alamat TEXT,
    area_operasional TEXT,
    foto VARCHAR(255),
    deskripsi TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories
CREATE TABLE categories(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100)
);

INSERT INTO categories (nama_kategori) VALUES 
('Plastik'), ('Kardus'), ('Besi'), ('Aluminium'), ('Elektronik'), ('Botol Kaca'), ('Botol Plastik'), ('Koran/Buku'), ('Kaleng'), ('Campuran');

-- Items
CREATE TABLE items(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_id INT,
    nama_barang VARCHAR(255),
    berat DECIMAL(10,2),
    alamat TEXT,
    deskripsi TEXT,
    status ENUM('tersedia','menunggu_konfirmasi','diproses','sudah_diambil','selesai') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Item photos
CREATE TABLE item_photos(
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    foto VARCHAR(255),
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

-- Pickup requests
CREATE TABLE pickup_requests(
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    warga_id INT,
    collector_id INT,
    request_by ENUM('warga','pengepul'),
    status ENUM('pending','accepted','rejected','pickup','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (warga_id) REFERENCES users(id),
    FOREIGN KEY (collector_id) REFERENCES users(id)
);

-- Create indexes for better performance
CREATE INDEX idx_items_status ON items(status);
CREATE INDEX idx_items_user ON items(user_id);
CREATE INDEX idx_requests_status ON pickup_requests(status);
CREATE INDEX idx_requests_collector ON pickup_requests(collector_id);
CREATE INDEX idx_requests_warga ON pickup_requests(warga_id);