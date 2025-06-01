<?php
session_start();
require_once 'db.php'; // File koneksi database Anda

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Arahkan ke halaman login jika belum
    exit;
}

$order_id = null;
$orderDetails = null;
$orderItems = [];
$pageTitle = "Pembayaran Pesanan";
$BIAYA_LAYANAN = 7000; // Pastikan ini konsisten dengan di proses_pemesanan.php

if (isset($_GET['order_id']) && filter_var($_GET['order_id'], FILTER_VALIDATE_INT)) {
    $order_id = (int)$_GET['order_id'];
    $user_id_session = (int)$_SESSION['user_id'];

    // Ambil detail order utama
    $stmtOrder = $conn->prepare(
        "SELECT o.order_id, o.user_id, o.order_date, o.jumlah_total, o.status,
                oi.quantity, oi.subtotal AS ticket_subtotal, 
                tt.name AS ticket_type_name, tt.price AS ticket_unit_price,
                e.nama AS event_name, e.event_date, e.lokasi AS event_location, e.image_url AS event_image
         FROM orders o
         JOIN order_items oi ON o.order_id = oi.order_id
         JOIN ticket_types tt ON oi.ticket_type_id = tt.ticket_type_id
         JOIN events e ON tt.event_id = e.event_id
         WHERE o.order_id = ? AND o.user_id = ?"
    );

    if($stmtOrder) {
        $stmtOrder->bind_param("ii", $order_id, $user_id_session);
        $stmtOrder->execute();
        $resultOrder = $stmtOrder->get_result();
        if ($resultOrder->num_rows > 0) {
            $orderDetails = $resultOrder->fetch_assoc();
            $pageTitle = "Pembayaran Pesanan #" . htmlspecialchars($orderDetails['order_id']);
            // Jika ada beberapa item tiket per order (meskipun alur saat ini 1 jenis tiket per order)
            // Anda perlu loop di sini atau menyesuaikan query. Untuk sekarang, kita asumsikan 1 baris hasil.
        }
        $stmtOrder->close();
    } else {
        // Error saat prepare statement
        error_log("Failed to prepare statement for order details: " . $conn->error);
        // Handle error, misalnya tampilkan pesan umum
    }
}

// Fungsi format tanggal (bisa dari helper)
function formatPaymentPageDate($dateStr) {
    if (!$dateStr) return "-";
    try {
        $date = new DateTime($dateStr);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return $days[$date->format('w')] . ', ' . $date->format('d') . ' ' . $months[$date->format('n')-1] . ' ' . $date->format('Y');
    } catch (Exception $e) { return "-"; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="foto/logoputih.png" rel="icon">
  <title><?php echo $pageTitle; ?> - Harmonix</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <style>
    body { background-color: #f9fafb; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; }
    .navbar { background-color: #1E3A8A; }
    .navbar a { color: white; text-decoration: none; margin: 0 10px; }
    .navbar a:hover { text-decoration: underline; }
    .steps { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
    .step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; flex:1; text-align:center; }
    .step-number { width: 36px; height: 36px; border-radius: 50%; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px; font-size:0.9rem; }
    .step.active .step-number { background-color: #1E3A8A; color: white; }
    .step-title { font-size: 0.8rem; color: #6b7280; }
    .step.active .step-title { font-weight: 600; color: #1E3A8A; }
    .steps::before { content: ""; position: absolute; top: 18px; left: 15%; right: 15%; height: 2px; background-color: #e5e7eb; z-index: 0; }
    .payment-section { background-color: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 24px; margin-bottom: 24px; border:1px solid #e5e7eb; }
    .payment-section h5 { font-weight: 600; font-size: 1.1rem; color: #111827; margin-bottom: 1.25rem; }
    .order-summary-card { background-color: #f3f4f6; border-radius: 8px; padding: 20px; }
    .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size:0.9rem; }
    .summary-item span:first-child { color: #4b5563; }
    .summary-item span:last-child { color: #111827; font-weight: 500; }
    .summary-item.total-payment span { font-weight: bold; font-size: 1.15rem; color: #1E3A8A; }
    .payment-method-card { background-color: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom:16px; cursor:pointer; transition: all 0.2s ease-in-out;}
    .payment-method-card:hover, .payment-method-card.selected { border-color: #1E3A8A; box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.5); }
    .payment-method-card img { max-height: 30px; margin-right: 15px; }
    .btn-confirm-payment { background-color: #1E3A8A; border-color: #1E3A8A; padding: 0.75rem 1.5rem; font-weight: 500; font-size:1rem;}
    .btn-confirm-payment:hover { background-color: #162b65; border-color: #162b65;}
    .breadcrumb { font-size: 0.875rem; }
    .breadcrumb-item a { color: #1E3A8A; text-decoration: none; }
    .breadcrumb-item a:hover { text-decoration: underline; }
    .breadcrumb-item.active { color: #495057; }
    .breadcrumb-item + .breadcrumb-item::before { color: #6c757d; }
    .alert-payment-info { font-size: 0.9rem; }
    .navbar-brand { color: white !important; font-weight: bold; }
    .profile-icon { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; cursor: pointer; }
    .btn-daftar-sekarang { color: white; font-weight: 500; text-decoration: none; padding: 0.375rem 0.75rem;}
    .btn-daftar-sekarang:hover { text-decoration: underline; color: #f0f0f0; }
    .flag-container { display: flex; align-items: center; }
    .flag { width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(to bottom, red 50%, white 50%); margin-right: 8px; }
    .flag-text { color: white; font-size: 0.875rem; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container d-flex align-items-center py-2">
      <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">
        <img src="foto/logoputih.png" alt="Harmonix Logo" width="32" height="32" class="d-inline-block align-text-top me-2">
        Harmonix
      </a>
      <div class="d-none d-lg-flex mx-auto">
        <a href="dashboard_tiket.php" class="nav-link px-3">Jelajah</a>
        <a href="tambah_event.php" class="nav-link px-3">Event Creator</a>
        <a href="#" class="nav-link px-3">Hubungi Kami</a>
      </div>
      <div class="d-flex align-items-center">
        <div class="flag-container me-3 d-none d-md-flex">
            <div class="flag"></div>
            <span class="flag-text">ID</span>
        </div>
        <div class="d-flex align-items-center navbar-actions" id="navbarActionsPayment">
            </div>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Beranda</a></li>
        <li class="breadcrumb-item"><a href="dashboard_tiket.php">Jelajah Event</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
      </ol>
    </nav>

    <div class="steps">
      <div class="step" id="step1">
        <div class="step-number">1</div>
        <div class="step-title">Data Pemesanan</div>
      </div>
      <div class="step active" id="step2">
        <div class="step-number">2</div>
        <div class="step-title">Pembayaran</div>
      </div>
      <div class="step" id="step3">
        <div class="step-number">3</div>
        <div class="step-title">Selesai</div>
      </div>
    </div>

    <?php if ($orderDetails): ?>
        <div class="row">
            <div class="col-lg-7">
                <div class="payment-section">
                    <h5>Pilih Metode Pembayaran</h5>
                    <p class="text-muted small">Silakan pilih salah satu metode pembayaran di bawah ini dan ikuti instruksi yang diberikan.</p>
                    
                    <div id="paymentError" class="alert alert-danger d-none" role="alert"></div>

                    <div class="payment-method-card mb-3" data-method="bank_transfer_bca">
                        <div class="d-flex align-items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia_logo.svg/2560px-Bank_Central_Asia_logo.svg.png" alt="BCA Logo" style="height: 25px;">
                            <strong class="ms-2">Transfer Bank BCA</strong>
                        </div>
                        <div id="bca_details" class="mt-2" style="display:none; font-size:0.9rem;">
                            Silakan transfer ke rekening berikut:<br>
                            No. Rekening: <strong>123-456-7890</strong><br>
                            Atas Nama: <strong>PT Harmonix Indonesia</strong><br>
                            Jumlah: <strong class="text-danger">Rp <?php echo number_format($orderDetails['jumlah_total'], 0, ',', '.'); ?></strong><br>
                            <small>Mohon transfer dengan jumlah yang tepat. Simpan bukti transfer Anda.</small>
                        </div>
                    </div>

                    <div class="payment-method-card mb-3" data-method="virtual_account_mandiri">
                         <div class="d-flex align-items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/f/fa/Bank_Mandiri_logo.svg/1200px-Bank_Mandiri_logo.svg.png" alt="Mandiri Logo" style="height: 20px;">
                            <strong class="ms-2">Virtual Account Mandiri</strong>
                        </div>
                        <div id="mandiri_va_details" class="mt-2" style="display:none; font-size:0.9rem;">
                            Nomor Virtual Account: <strong>8808 XXXX XXXX XXXX</strong> (akan digenerate sistem nyata)<br>
                            Jumlah: <strong class="text-danger">Rp <?php echo number_format($orderDetails['jumlah_total'], 0, ',', '.'); ?></strong><br>
                            <small>Pembayaran akan terverifikasi otomatis dalam beberapa menit.</small>
                        </div>
                    </div>
                     <div class="payment-method-card" data-method="ewallet_gopay">
                         <div class="d-flex align-items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Gopay_logo.svg/2560px-Gopay_logo.svg.png" alt="Gopay Logo" style="height: 22px;">
                            <strong class="ms-2">GoPay</strong>
                        </div>
                        <div id="gopay_details" class="mt-2" style="display:none; font-size:0.9rem;">
                            <p>Pindai kode QR berikut menggunakan aplikasi Gojek Anda atau bayar ke nomor <strong>0812XXXXXXXX</strong>.</p>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Bayar_Gopay_Harmonix_<?php echo $order_id; ?>_Rp<?php echo $orderDetails['jumlah_total']; ?>" alt="QR Code Gopay" class="my-2"> <br>
                            Jumlah: <strong class="text-danger">Rp <?php echo number_format($orderDetails['jumlah_total'], 0, ',', '.'); ?></strong>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-muted small">Setelah melakukan pembayaran, klik tombol di bawah ini untuk konfirmasi. Untuk metode transfer manual, mungkin diperlukan waktu untuk verifikasi.</p>
                        <button class="btn btn-confirm-payment w-100" id="btnConfirmPayment" disabled>Pilih Metode Pembayaran Terlebih Dahulu</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="payment-section position-sticky" style="top: 20px;">
                    <h5>Ringkasan Pesanan</h5>
                    <div class="order-summary-card">
                        <p class="mb-1"><strong>ID Pesanan:</strong> #<?php echo htmlspecialchars($orderDetails['order_id']); ?></p>
                        <p class="mb-1"><strong>Event:</strong> <?php echo htmlspecialchars($orderDetails['event_name']); ?></p>
                        <p class="text-muted small mb-1"><?php echo formatPaymentPageDate($orderDetails['event_date']); ?> | <?php echo htmlspecialchars($orderDetails['event_location']); ?></p>
                        <hr class="my-2">
                        <div class="summary-item">
                            <span><?php echo htmlspecialchars($orderDetails['ticket_type_name']); ?> (x<?php echo htmlspecialchars($orderDetails['quantity']); ?>)</span>
                            <span>Rp <?php echo number_format($orderDetails['ticket_subtotal'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Biaya Layanan</span>
                            <span>Rp <?php echo number_format($BIAYA_LAYANAN, 0, ',', '.'); ?></span>
                        </div>
                        <hr class="my-2">
                        <div class="summary-item total-payment">
                            <span>Total Pembayaran</span>
                            <span>Rp <?php echo number_format($orderDetails['jumlah_total'], 0, ',', '.'); ?></span>
                        </div>
                         <?php if ($orderDetails['status'] == 'pending'): ?>
                            <div class="alert alert-warning p-2 small mt-3" role="alert">
                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Pembayaran
                            </div>
                        <?php elseif ($orderDetails['status'] == 'dibayar'): ?>
                            <div class="alert alert-success p-2 small mt-3" role="alert">
                                <i class="bi bi-check-circle-fill me-1"></i> Pembayaran Berhasil
                            </div>
                        <?php else: ?>
                             <div class="alert alert-secondary p-2 small mt-3" role="alert">
                                Status: <?php echo ucfirst(htmlspecialchars($orderDetails['status'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($order_id): ?>
        <div class="alert alert-danger text-center" role="alert">
            Detail pesanan dengan ID #<?php echo htmlspecialchars($order_id); ?> tidak ditemukan atau Anda tidak memiliki akses.
            Pastikan Anda login dengan akun yang benar. <a href="dashboard.php" class="alert-link">Kembali ke Beranda</a>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            ID Pesanan tidak valid atau tidak disediakan. Silakan akses halaman ini melalui link yang benar.
            <a href="dashboard.php" class="alert-link">Kembali ke Beranda</a>
        </div>
    <?php endif; ?>
  </div>

  <footer class="mt-5 py-4 border-top bg-light">
    <div class="container text-center">
        <p class="mb-0 text-muted small">&copy; <?php echo date("Y"); ?> Harmonix. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/init.js?v=<?php echo time(); ?>"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderId = <?php echo $order_id ? $order_id : 'null'; ?>;
        const orderStatus = "<?php echo $orderDetails ? $orderDetails['status'] : ''; ?>";
        const paymentMethodCards = document.querySelectorAll('.payment-method-card');
        const btnConfirmPayment = document.getElementById('btnConfirmPayment');
        const paymentErrorDiv = document.getElementById('paymentError');
        let selectedMethod = null;

        paymentMethodCards.forEach(card => {
            card.addEventListener('click', function() {
                if (orderStatus !== 'pending') return; // Jangan biarkan pilih jika sudah bukan pending

                // Sembunyikan semua detail, reset style
                paymentMethodCards.forEach(c => {
                    c.classList.remove('selected');
                    const detailDiv = document.getElementById(c.dataset.method + '_details');
                    if (detailDiv) detailDiv.style.display = 'none';
                });

                // Tampilkan detail yang dipilih, set style
                this.classList.add('selected');
                selectedMethod = this.dataset.method;
                const selectedDetailDiv = document.getElementById(selectedMethod + '_details');
                if (selectedDetailDiv) selectedDetailDiv.style.display = 'block';
                
                if(btnConfirmPayment) {
                    btnConfirmPayment.disabled = false;
                    btnConfirmPayment.textContent = 'Saya Sudah Bayar & Konfirmasi';
                }
            });
        });

        if (btnConfirmPayment) {
            if (orderStatus !== 'pending') {
                btnConfirmPayment.textContent = 'Pesanan Sudah Diproses';
                btnConfirmPayment.disabled = true;
                paymentMethodCards.forEach(card => card.style.pointerEvents = 'none'); // Disable klik card
            }


            btnConfirmPayment.addEventListener('click', async function() {
                if (!selectedMethod) {
                    paymentErrorDiv.textContent = 'Silakan pilih metode pembayaran terlebih dahulu.';
                    paymentErrorDiv.classList.remove('d-none');
                    return;
                }
                paymentErrorDiv.classList.add('d-none');
                
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengonfirmasi...';

                try {
                    const response = await fetch('update_status_pembayaran.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ order_id: orderId, payment_method: selectedMethod })
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert(result.message + "\nAnda akan diarahkan ke halaman tiket saya.");
                        // Arahkan ke halaman konfirmasi atau tiket saya
                        window.location.href = 'dashboard_tiket_saya.php'; 
                    } else {
                        paymentErrorDiv.textContent = `Error: ${result.message || 'Gagal mengonfirmasi pembayaran.'}`;
                        paymentErrorDiv.classList.remove('d-none');
                        this.disabled = false;
                        this.innerHTML = 'Saya Sudah Bayar & Konfirmasi';
                    }
                } catch (error) {
                    console.error('Error confirming payment:', error);
                    paymentErrorDiv.textContent = 'Terjadi kesalahan koneksi atau server. Silakan coba lagi.';
                    paymentErrorDiv.classList.remove('d-none');
                    this.disabled = false;
                    this.innerHTML = 'Saya Sudah Bayar & Konfirmasi';
                }
            });
        }
    });
  </script>
</body>
</html>