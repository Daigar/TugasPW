<?php
// transaksi.php
require_once 'config.php';
require_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir POS - POS Fotokopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #2b3440; }
        .navbar { background-color: #ffffff; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 12px 0; }
        .navbar-brand { font-weight: 700; color: #0d6efd !important; font-size: 1.4rem; letter-spacing: -0.5px; }
        .nav-link { font-weight: 500; color: #6c757d; margin: 0 5px; border-radius: 10px; padding: 8px 16px !important; transition: all 0.2s ease; }
        .nav-link:hover, .nav-link.active { color: #0d6efd; background-color: #f0f6ff; }
        .btn-logout { background-color: #fff0f0; color: #dc3545; border-radius: 10px; padding: 8px 20px; font-weight: 600; text-decoration: none; transition: all 0.2s; display: inline-block; }
        .btn-logout:hover { background-color: #dc3545; color: #fff; }

        .modern-card { background: #ffffff; border-radius: 24px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); }
        .form-control-modern { border-radius: 16px; padding: 12px 20px; border: 1px solid #e2e8f0; background-color: #f8fafc; font-weight: 500; transition: all 0.2s; }
        .form-control-modern:focus { background-color: #fff; box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); border-color: #86b7fe; }
        
        /* POS Specific */
        .product-card { 
            background: #ffffff; border-radius: 18px; border: 1px solid rgba(0,0,0,0.05); 
            transition: all 0.2s; cursor: pointer; user-select: none;
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(13, 110, 253, 0.1); border-color: #0d6efd; }
        
        .cart-container { max-height: calc(100vh - 350px); overflow-y: auto; }
        .cart-container::-webkit-scrollbar { width: 6px; }
        .cart-container::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
        
        .signature-pad-container { border: 2px dashed #ced4da; border-radius: 16px; background-color: #f8fafc; overflow: hidden; touch-action: none; }
        #signaturePad { width: 100%; height: 200px; }
        .modal-content { border-radius: 24px; border: none; }
        
        .btn-checkout { border-radius: 16px; font-weight: 700; transition: all 0.3s; }
        .btn-checkout:not(:disabled):hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(25, 135, 84, 0.2); }
        
        /* Animasi Kustom */
        @keyframes popIn {
            0% { transform: scale(0.9); opacity: 0; }
            50% { transform: scale(1.02); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes slideInRight {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-pop { animation: popIn 0.3s ease-out forwards; }
        .animate-slide { animation: slideInRight 0.3s ease-out forwards; }
        .cart-icon-bounce { animation: popIn 0.4s ease-in-out; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg mb-4 shadow-sm">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand" href="index.php"><i class="bi bi-printer-fill me-2"></i>POS Fotokopi</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="produk.php">Master Produk</a></li>
                    <li class="nav-item"><a class="nav-link active" href="transaksi.php">Kasir POS</a></li>
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

    <div class="container-fluid px-lg-5 pb-5">
        <div class="row g-4">
            <!-- Bagian Kiri: Daftar Produk -->
            <div class="col-xl-8 col-lg-7">
                <div class="mb-3">
                    <h3 class="fw-bold" style="color: #1e2530;">Kasir POS</h3>
                    <p class="text-muted">Pilih produk atau layanan untuk transaksi pelanggan.</p>
                </div>
                
                <div class="modern-card p-4">
                    <div class="position-relative mb-4">
                        <i class="bi bi-search position-absolute text-muted fs-5" style="top: 14px; left: 18px;"></i>
                        <input type="text" id="searchProduct" class="form-control form-control-modern ps-5 form-control-lg" placeholder="Ketik untuk mencari produk...">
                    </div>
                    <div class="row g-3" id="productList">
                        <!-- Produk akan dirender via AJAX disini -->
                    </div>
                </div>
            </div>

            <!-- Bagian Kanan: Keranjang Kasir -->
            <div class="col-xl-4 col-lg-5">
                <div class="modern-card d-flex flex-column" style="height: 100%; min-height: 70vh;">
                    <div class="p-4 border-bottom d-flex align-items-center">
                        <div class="bg-soft-primary p-2 rounded-3 me-3 text-primary" style="background:#cfe2ff;">
                            <i class="bi bi-cart3 fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Keranjang Belanja</h5>
                    </div>
                    
                    <div class="p-0 cart-container flex-grow-1">
                        <table class="table table-hover mb-0 align-middle" id="cartTable">
                            <thead class="table-light position-sticky top-0" style="z-index: 1;">
                                <tr>
                                    <th class="border-0 text-muted small fw-bold py-3 px-4">Produk</th>
                                    <th class="border-0 text-muted small fw-bold py-3 text-center" width="90">Qty</th>
                                    <th class="border-0 text-muted small fw-bold py-3 text-end">Subtotal</th>
                                    <th class="border-0 py-3 px-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Item Keranjang -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4 bg-light" style="border-radius: 0 0 24px 24px; border-top: 1px solid rgba(0,0,0,0.05);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-bold">Total Pembayaran</span>
                            <span id="grandTotalText" class="text-primary fw-bold fs-3">Rp 0</span>
                        </div>
                        <button class="btn btn-success btn-checkout w-100 py-3 fs-5" id="btnCheckout" disabled>
                            Proses Pembayaran <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran & Tanda Tangan -->
    <div class="modal fade" id="modalPayment" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Selesaikan Pembayaran</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end pe-md-4">
                            <p class="text-muted fw-bold mb-1">Total Tagihan:</p>
                            <h2 class="text-danger fw-bold mb-4" id="modalTotal"></h2>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Uang Diterima (Rp)</label>
                                <input type="number" id="uangBayar" class="form-control form-control-modern form-control-lg fw-bold text-primary" placeholder="Masukkan jumlah uang" min="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Kembalian (Rp)</label>
                                <input type="text" id="uangKembali" class="form-control form-control-modern form-control-lg fw-bold text-success" readonly style="background:#e9ecef; border-color: transparent;">
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <label class="form-label fw-bold text-muted small mb-2">Tanda Tangan Pelanggan / Kasir</label>
                            <div class="signature-pad-container shadow-sm mb-2" id="canvasContainer">
                                <canvas id="signaturePad"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3" id="btnClearSignature">
                                <i class="bi bi-eraser-fill me-1"></i> Bersihkan Tanda Tangan
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4 mt-2">
                    <button type="button" class="btn btn-light fw-bold px-4" style="border-radius:12px;" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success fw-bold px-4 py-2 fs-5 shadow-sm" style="border-radius:12px;" id="btnSubmitTrx" disabled>
                        Simpan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    
    <!-- Memanggil File Skrip Eksternal -->
    <script src="skrip.js"></script>
</body>
</html>