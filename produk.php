<?php
// produk.php
require_once 'config.php';
require_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - POS Fotokopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            color: #2b3440;
        }
        .navbar { background-color: #ffffff; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 12px 0; }
        .navbar-brand { font-weight: 700; color: #0d6efd !important; font-size: 1.4rem; letter-spacing: -0.5px; }
        .nav-link { font-weight: 500; color: #6c757d; margin: 0 5px; border-radius: 10px; padding: 8px 16px !important; transition: all 0.2s ease; }
        .nav-link:hover, .nav-link.active { color: #0d6efd; background-color: #f0f6ff; }
        .btn-logout { background-color: #fff0f0; color: #dc3545; border-radius: 10px; padding: 8px 20px; font-weight: 600; text-decoration: none; transition: all 0.2s; display: inline-block; }
        .btn-logout:hover { background-color: #dc3545; color: #fff; }
        
        .modern-card { background: #ffffff; border-radius: 24px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); }
        .btn-modern { border-radius: 12px; font-weight: 600; padding: 10px 24px; transition: all 0.3s; }
        .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(13, 110, 253, 0.2); }
        
        .table-modern th { background-color: #f8f9fa; color: #8792a1; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; border-bottom: 2px solid #e9ecef !important; padding: 16px; }
        .table-modern td { vertical-align: middle; padding: 16px; border-bottom: 1px solid #f1f3f5; color: #495057; font-weight: 500; }
        
        .form-control-modern, .form-select-modern { border-radius: 12px; padding: 12px 16px; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.2s; font-weight: 500; }
        .form-control-modern:focus, .form-select-modern:focus { background-color: #fff; box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); border-color: #86b7fe; }
        .modal-content { border-radius: 24px; border: none; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg mb-5 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-printer-fill me-2"></i>POS Fotokopi</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="produk.php">Master Produk</a></li>
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

    <div class="container pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold" style="color: #1e2530;">Master Produk</h3>
                <p class="text-muted mb-0">Kelola daftar layanan dan produk jualan Anda.</p>
            </div>
            <button class="btn btn-primary btn-modern" onclick="openModal('add')">
                <i class="bi bi-plus-lg me-1"></i> Tambah Produk
            </button>
        </div>

        <div class="modern-card p-4">
            <div class="table-responsive">
                <table id="tableProduk" class="table table-hover table-modern w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Jenis</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form Produk -->
    <div class="modal fade" id="modalProduk" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <form id="formProduk" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" id="modalTitle">Tambah Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <input type="hidden" name="action" id="action" value="save_produk">
                        <input type="hidden" name="id" id="produk_id">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small mb-1">Kode Produk</label>
                            <input type="text" name="kode_produk" id="kode_produk" class="form-control form-control-modern" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small mb-1">Nama Produk / Layanan</label>
                            <input type="text" name="nama_produk" id="nama_produk" class="form-control form-control-modern" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small mb-1">Jenis Kategori</label>
                            <select name="jenis" id="jenis" class="form-select form-select-modern" required>
                                <option value="Fotokopi">Fotokopi</option>
                                <option value="Print">Print</option>
                                <option value="Jilid">Jilid / Finishing</option>
                                <option value="ATK">ATK Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small mb-1">Harga Satuan (Rp)</label>
                            <input type="number" name="harga" id="harga" class="form-control form-control-modern" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small mb-1">Upload Gambar / Dokumen</label>
                            <input type="file" name="gambar[]" id="gambar" class="form-control form-control-modern p-2" multiple accept="image/*,.pdf">
                            <small class="text-muted" style="font-size: 0.8rem;">Bisa upload lebih dari 1. Kosongkan saat edit jika tak ingin ubah.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-light fw-bold" style="border-radius:10px;" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius:10px;" id="btnSave">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts CDN -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        let table;
        $(document).ready(function() {
            // Inisiasi DataTables Client-Side dengan AJAX Fetch
            table = $('#tableProduk').DataTable({
                ajax: {
                    url: 'ajax.php?action=get_produk',
                    type: 'GET'
                },
                columns: [
                    { data: 'id' },
                    { data: 'kode_produk' },
                    { data: 'nama_produk' },
                    { data: 'jenis' },
                    { 
                        data: 'harga',
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `<button class="btn btn-sm btn-light text-warning shadow-sm border me-1 rounded-3" onclick='openModal("edit", ${JSON.stringify(data)})'><i class="bi bi-pencil-square"></i></button> 
                                    <button class="btn btn-sm btn-light text-danger shadow-sm border rounded-3" onclick="deleteProduk(${data.id})"><i class="bi bi-trash3"></i></button>`;
                        }
                    }
                ],
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
            });

            // Form Submit via AJAX (Mendukung Multiple Files)
            $('#formProduk').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                $('#btnSave').text('Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: 'ajax.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        let response = JSON.parse(res);
                        if(response.status === 'success') {
                            $('#modalProduk').modal('hide');
                            table.ajax.reload();
                            alert('Data berhasil disimpan!');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    complete: function() {
                        $('#btnSave').text('Simpan').prop('disabled', false);
                    }
                });
            });
        });

        function openModal(type, data = null) {
            $('#formProduk')[0].reset();
            if (type === 'add') {
                $('#modalTitle').text('Tambah Produk');
                $('#produk_id').val('');
            } else {
                $('#modalTitle').text('Edit Produk');
                $('#produk_id').val(data.id);
                $('#kode_produk').val(data.kode_produk);
                $('#nama_produk').val(data.nama_produk);
                $('#jenis').val(data.jenis);
                $('#harga').val(data.harga);
            }
            new bootstrap.Modal(document.getElementById('modalProduk')).show();
        }

        function deleteProduk(id) {
            if (confirm("Yakin ingin menghapus produk ini?")) {
                $.post('ajax.php', {action: 'delete_produk', id: id}, function(res) {
                    let response = JSON.parse(res);
                    if(response.status === 'success') table.ajax.reload();
                    else alert('Error menghapus data');
                });
            }
        }
    </script>
</body>
</html>