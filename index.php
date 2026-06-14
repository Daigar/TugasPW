<?php
// index.php
require_once 'config.php';
require_login();

// Mengambil ringkasan data
$stmt_prod = $pdo->query("SELECT COUNT(*) as total FROM produk");
$total_produk = $stmt_prod->fetch()['total'];

$stmt_trans = $pdo->query("SELECT COUNT(*) as total, SUM(total) as omzet FROM transaksi WHERE DATE(tanggal) = CURDATE()");
$data_hari_ini = $stmt_trans->fetch();
$total_transaksi = $data_hari_ini['total'];
$omzet_hari_ini = $data_hari_ini['omzet'] ?: 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - POS Fotokopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            color: #2b3440;
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 12px 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: #0d6efd !important;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
        }
        .nav-link {
            font-weight: 500;
            color: #6c757d;
            margin: 0 5px;
            border-radius: 10px;
            padding: 8px 16px !important;
            transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: #0d6efd;
            background-color: #f0f6ff;
        }
        .btn-logout {
            background-color: #fff0f0;
            color: #dc3545;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-logout:hover {
            background-color: #dc3545;
            color: #fff;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            padding: 24px;
            transition: all 0.3s ease;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }
        .icon-box {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
        }
        .bg-soft-success { background-color: #d1e7dd; color: #0f5132; }
        .bg-soft-primary { background-color: #cfe2ff; color: #084298; }
        .bg-soft-warning { background-color: #fff3cd; color: #664d03; }
        
        .stat-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #8792a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e2530;
            margin: 0;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg mb-5 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-printer-fill me-2"></i>POS Fotokopi</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="produk.php">Master Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Kasir POS</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-muted fw-medium me-4 d-none d-lg-block">
                        <i class="bi bi-person-circle me-1"></i> Halo, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
                    </span>
                    <a class="btn-logout" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="mb-4">
            <h3 class="fw-bold" style="color: #1e2530;">Dashboard</h3>
            <p class="text-muted">Ringkasan aktivitas dan performa hari ini.</p>
        </div>

        <div class="row g-4">
            <!-- Card Omzet -->
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-title">Omzet Hari Ini</div>
                    <h2 class="stat-value">Rp <?= number_format($omzet_hari_ini, 0, ',', '.') ?></h2>
                </div>
            </div>
            
            <!-- Card Transaksi -->
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-primary">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-title">Transaksi Hari Ini</div>
                    <h2 class="stat-value"><?= $total_transaksi ?> <span class="fs-5 text-muted fw-normal">Struk</span></h2>
                </div>
            </div>
            
            <!-- Card Total Produk -->
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-warning">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-title">Total Produk Master</div>
                    <h2 class="stat-value"><?= $total_produk ?> <span class="fs-5 text-muted fw-normal">Item</span></h2>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>