<?php
// ajax.php - Handler untuk semua Request AJAX (No-Reload)
require_once 'config.php';
require_login();

// Mendeteksi Action dari GET, POST reguler, atau POST JSON
$action = $_REQUEST['action'] ?? '';

// Cek apakah request berupa JSON (dari transaksi)
if (empty($action)) {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);
    if(isset($input['action'])) {
        $action = $input['action'];
        $_POST = $input; // Override POST untuk kemudahan
    }
}

switch ($action) {
    case 'get_produk':
        $stmt = $pdo->query("SELECT * FROM produk ORDER BY id DESC");
        echo json_encode(['data' => $stmt->fetchAll()]);
        break;

    case 'save_produk':
        try {
            $id = $_POST['id'] ?? '';
            $kode = $_POST['kode_produk'];
            $nama = $_POST['nama_produk'];
            $harga = $_POST['harga'];
            $jenis = $_POST['jenis'];

            if (empty($id)) {
                // Insert Baru
                $stmt = $pdo->prepare("INSERT INTO produk (kode_produk, nama_produk, harga, jenis) VALUES (?, ?, ?, ?)");
                $stmt->execute([$kode, $nama, $harga, $jenis]);
                $produk_id = $pdo->lastInsertId();
            } else {
                // Update
                $stmt = $pdo->prepare("UPDATE produk SET kode_produk=?, nama_produk=?, harga=?, jenis=? WHERE id=?");
                $stmt->execute([$kode, $nama, $harga, $jenis, $id]);
                $produk_id = $id;
            }

            // Handle Multiple File Uploads
            if (!empty($_FILES['gambar']['name'][0])) {
                $uploadDir = 'assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($_FILES['gambar']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['gambar']['error'][$key] == 0) {
                        $name = $_FILES['gambar']['name'][$key];
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $newName = uniqid() . '_' . time() . '.' . $ext; // Nama Unik
                        
                        if (move_uploaded_file($tmp_name, $uploadDir . $newName)) {
                            // Simpan record gambar ke relasi database
                            $stmtImg = $pdo->prepare("INSERT INTO gambar_produk (produk_id, nama_file) VALUES (?, ?)");
                            $stmtImg->execute([$produk_id, $newName]);
                        }
                    }
                }
            }

            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_produk':
        try {
            $id = $_POST['id'];
            // File gambar akan otomatis terhapus referensinya karena ON DELETE CASCADE di database.
            // Secara optimal bisa ditambahkan logic `unlink()` file fisik di folder assets.
            $stmt = $pdo->prepare("DELETE FROM produk WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'save_transaksi':
        try {
            $pdo->beginTransaction();

            $total = $_POST['total'];
            $bayar = $_POST['bayar'];
            $kembali = $bayar - $total;
            $signature = $_POST['signature']; // Base64 Text
            $cart = $_POST['cart'];
            $user_id = $_SESSION['user_id'];
            
            // Buat Invoice format: INV-YYYYMMDD-RANDOM
            $no_invoice = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

            // Insert ke tabel Transaksi
            $stmt = $pdo->prepare("INSERT INTO transaksi (no_invoice, total, bayar, kembali, signature, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$no_invoice, $total, $bayar, $kembali, $signature, $user_id]);
            $transaksi_id = $pdo->lastInsertId();

            // Insert ke Detail Transaksi
            $stmt_detail = $pdo->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($cart as $item) {
                $subtotal = $item['qty'] * $item['harga'];
                $stmt_detail->execute([$transaksi_id, $item['id'], $item['qty'], $item['harga'], $subtotal]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'invoice' => $no_invoice]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>