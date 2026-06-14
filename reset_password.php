<?php
// reset_password.php
require_once 'config.php';
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password_baru = $_POST['password_baru'];
    
    if (!empty($password_baru)) {
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->execute([$hash_baru, $username]);
            
            if ($stmt->rowCount() > 0) {
                $pesan = "<div class='alert alert-success'>Sukses! Password untuk user <b>" . htmlspecialchars($username) . "</b> berhasil diperbarui. <br><a href='login.php' class='btn btn-success btn-sm mt-2'>Lanjut ke Halaman Login</a></div>";
            } else {
                $pesan = "<div class='alert alert-warning'>Username tidak ditemukan di database.</div>";
            }
        } catch (Exception $e) {
            $pesan = "<div class='alert alert-danger' style='border-radius: 12px; font-size: 0.9rem;'>Terjadi kesalahan: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - POS Fotokopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
        }
        .minimalist-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.03);
            border: none;
            width: 100%;
            max-width: 420px;
        }
        .form-control {
            background-color: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }
        .btn-primary {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2);
        }
        .form-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh; margin: 0;">
    <div class="minimalist-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h4 class="fw-bold" style="color: #2b3440;">Reset Password</h4>
            <p class="text-muted small">Amankan kembali akses POS Fotokopi Anda</p>
        </div>
        
        <?= $pesan ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" class="form-control" value="admin" required readonly>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Password Baru</label>
                <input type="password" name="password_baru" class="form-control" placeholder="Ketik password baru..." required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>