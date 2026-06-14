// skrip.js - Logika Transaksi & Animasi UI
let products = [];
let cart = [];
let grandTotal = 0;
let signaturePad;

$(document).ready(function() {
    // 1. Load Produk dengan Animasi Fade-In
    $.get('ajax.php?action=get_produk', function(res) {
        products = JSON.parse(res).data;
        renderProducts(products);
    });

    // 2. Pencarian Produk Real-time
    $('#searchProduct').on('input', function() {
        let keyword = $(this).val().toLowerCase();
        let filtered = products.filter(p => p.nama_produk.toLowerCase().includes(keyword) || p.kode_produk.toLowerCase().includes(keyword));
        renderProducts(filtered);
    });

    // 3. Inisialisasi Signature Pad (Kanvas TTD)
    const canvas = document.getElementById('signaturePad');
    if (canvas) {
        signaturePad = new SignaturePad(canvas, { 
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: '#0d6efd' // Warna tinta biru agar modern
        });
        
        // Fix ukuran Canvas responsive
        function resizeCanvas() {
            const ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        
        // Resize saat modal pembayaran dibuka + Animasi Kanvas
        $('#modalPayment').on('shown.bs.modal', function () { 
            resizeCanvas(); 
            $('#canvasContainer').hide().fadeIn(600); // Animasi munculnya kanvas
        });

        // Hapus TTD dengan efek fade
        $('#btnClearSignature').click(() => {
            $('#canvasContainer').fadeOut(150, function() {
                signaturePad.clear();
                $(this).fadeIn(150);
            });
        });
    }

    // 4. Hitung Kembalian Otomatis
    $('#uangBayar').on('input', function() {
        let bayar = parseInt($(this).val()) || 0;
        let kembali = bayar - grandTotal;
        $('#uangKembali').val(kembali >= 0 ? formatRupiah(kembali) : 0);
        $('#btnSubmitTrx').prop('disabled', bayar < grandTotal);
    });

    // 5. Tombol Proses ke Modal
    $('#btnCheckout').click(function() {
        if(cart.length === 0) return;
        $('#modalTotal').text(formatRupiah(grandTotal));
        $('#uangBayar').val('');
        $('#uangKembali').val('');
        if(signaturePad) signaturePad.clear();
        $('#btnSubmitTrx').prop('disabled', true);
        new bootstrap.Modal(document.getElementById('modalPayment')).show();
    });

    // 6. Submit Transaksi Final
    $('#btnSubmitTrx').click(function() {
        if (signaturePad.isEmpty()) {
            alert("Tanda tangan pelanggan/kasir tidak boleh kosong sebagai bukti!");
            // Beri efek border merah berkedip pada kanvas jika kosong
            $('#canvasContainer').css('border-color', 'red').delay(500).queue(function(next){
                $(this).css('border-color', '#ced4da');
                next();
            });
            return;
        }

        let payload = {
            action: 'save_transaksi',
            cart: cart,
            total: grandTotal,
            bayar: parseInt($('#uangBayar').val()),
            signature: signaturePad.toDataURL() // Konversi canvas ke Base64
        };

        let btn = $(this);
        btn.html('<i class="bi bi-hourglass-split"></i> Menyimpan...').prop('disabled', true);

        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(res) {
                let response = JSON.parse(res);
                if (response.status === 'success') {
                    alert("Transaksi Sukses! Invoice: " + response.invoice);
                    cart = [];
                    updateCartUI();
                    $('#modalPayment').modal('hide');
                } else {
                    alert("Gagal: " + response.message);
                }
            },
            complete: function() {
                btn.html('Simpan Transaksi');
            }
        });
    });
});

// FUNGSI BANTUAN (HELPER)

function renderProducts(data) {
    let html = '';
    data.forEach((p, index) => {
        // Tambahkan inline style display none untuk persiapan efek fade-in
        html += `
        <div class="col-sm-6 col-md-4 col-xl-3 product-item" style="display:none;">
            <div class="product-card p-3 shadow-sm h-100 d-flex flex-column justify-content-between animate-pop" onclick="addToCart(${p.id}, '${p.nama_produk}', ${p.harga})">
                <div>
                    <div class="text-muted small fw-bold mb-1">${p.kode_produk}</div>
                    <div class="fw-bold text-dark mb-2" style="font-size: 0.95rem; line-height: 1.2;">${p.nama_produk}</div>
                </div>
                <div class="fw-bold text-primary mt-2 fs-5">${formatRupiah(p.harga)}</div>
            </div>
        </div>`;
    });
    $('#productList').html(html);
    
    // Animasi Stagger (Muncul berurutan satu per satu)
    $('.product-item').each(function(i) {
        $(this).delay(i * 30).fadeIn(300);
    });
}

function addToCart(id, nama, harga) {
    let existing = cart.find(c => c.id === id);
    if(existing) {
        existing.qty += 1;
    } else {
        cart.push({ id: id, nama: nama, harga: harga, qty: 1, isNew: true });
    }
    updateCartUI();
    
    // Beri efek animasi pada ikon keranjang
    let cartIcon = $('.bi-cart3').parent();
    cartIcon.removeClass('cart-icon-bounce');
    void cartIcon[0].offsetWidth; // trigger reflow
    cartIcon.addClass('cart-icon-bounce');
}

function updateQty(id, value) {
    let item = cart.find(c => c.id === id);
    if(item) {
        item.qty = parseInt(value) || 1;
        item.isNew = false;
        updateCartUI();
    }
}

function removeCart(id) {
    // Animasi slide out sebelum menghapus dari array
    $(`#cart-row-${id}`).fadeOut(200, function() {
        cart = cart.filter(c => c.id !== id);
        updateCartUI();
    });
}

function updateCartUI() {
    let tbody = '';
    grandTotal = 0;

    cart.forEach(c => {
        let subtotal = c.qty * c.harga;
        grandTotal += subtotal;
        
        // Jika item baru ditambahkan, beri kelas animasi masuk (animate-slide)
        let animationClass = c.isNew ? 'animate-slide' : '';
        
        tbody += `
        <tr id="cart-row-${c.id}" class="${animationClass}">
            <td class="small fw-bold text-dark px-4">${c.nama}</td>
            <td class="text-center">
                <input type="number" class="form-control form-control-sm form-control-modern text-center mx-auto" style="width: 70px;" value="${c.qty}" min="1" onchange="updateQty(${c.id}, this.value)">
            </td>
            <td class="fw-bold text-end text-primary">${formatRupiah(subtotal)}</td>
            <td class="text-center px-3">
                <button class="btn btn-sm btn-light text-danger py-1 px-2 rounded-3 border" onclick="removeCart(${c.id})">
                    <i class="bi bi-trash3-fill"></i>
                </button>
            </td>
        </tr>`;
        
        c.isNew = false; // Reset status baru setelah dirender
    });

    $('#cartTable tbody').html(tbody);
    
    // Update total harga dengan animasi fade
    $('#grandTotalText').hide().text(formatRupiah(grandTotal)).fadeIn(200);
    $('#btnCheckout').prop('disabled', cart.length === 0);
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}