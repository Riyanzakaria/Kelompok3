<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="foto/logoputih.png" rel="icon">
  <title>Form Pembelian - Bali Barber Expo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <style>
    body {
      background-color: #f9fafb;
    }

    .navbar {
      background-color: #1E3A8A;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      margin: 0 10px;
    }

    .navbar a:hover {
      text-decoration: underline;
    }

    .steps {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
      position: relative;
    }

    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      z-index: 1;
    }

    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #e5e7eb;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .step.active .step-number {
      background-color: #1E3A8A;
      color: white;
    }

    .step-title {
      font-size: 14px;
      color: #6b7280;
    }

    .step.active .step-title {
      font-weight: bold;
      color: #1E3A8A;
    }

    .steps::before {
      content: "";
      position: absolute;
      top: 20px;
      left: 60px;
      right: 60px;
      height: 2px;
      background-color: #e5e7eb;
      z-index: 0;
    }

    .form-section {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      padding: 24px;
      margin-bottom: 24px;
    }

    .summary-box {
      background-color: #f3f4f6;
      border-radius: 8px;
      padding: 16px;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
    }

    .btn-primary {
      background-color: #1E3A8A;
      border: none;
    }

    .btn-outline-primary {
      border-color: #1E3A8A;
      color: #1E3A8A;
    }

    .btn-outline-primary:hover {
      background-color: #1E3A8A;
      color: white;
    }

    .event-info {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
    }

    .event-info img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 16px;
    }

    .ticket-badge {
      display: inline-block;
      padding: 4px 12px;
      background-color: #e5e7eb;
      border-radius: 9999px;
      font-size: 14px;
      margin-top: 8px;
    }

    .nav-tabs .nav-link {
      color: #6b7280;
      border: none;
      padding: 10px 16px;
    }

    .nav-tabs .nav-link.active {
      color: #1E3A8A;
      border-bottom: 2px solid #1E3A8A;
      font-weight: bold;
    }

    .pemilik-tiket-card {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 16px;
    }

    .pemilik-tiket-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container d-flex justify-content-between align-items-center py-3">
      <a class="navbar-brand text-white fw-bold fs-4" href="dashboard3.html">Harmonix</a>
      <div class="d-none d-lg-flex">
        <a href="#">Jelajah</a>
        <a href="#">Event Creator</a>
        <a href="#">Hubungi Kami</a>
      </div>
      <div class="d-flex align-items-center">
        <img src="https://placehold.co/20x20" alt="Indonesian Flag" class="me-2" />
        <i class="fas fa-search text-white"></i>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="detail-event.php" id="eventBreadcrumb">Event</a></li>
        <li class="breadcrumb-item active" aria-current="page">Form Pembelian</li>
      </ol>
    </nav>

    <!-- Steps -->
    <div class="steps">
      <div class="step active">
        <div class="step-number">1</div>
        <div class="step-title">Data Pembeli</div>
      </div>
      <div class="step">
        <div class="step-number">2</div>
        <div class="step-title">Pembayaran</div>
      </div>
      <div class="step">
        <div class="step-number">3</div>
        <div class="step-title">Konfirmasi</div>
      </div>
    </div>

    <div class="row">
      <!-- Form -->
      <div class="col-lg-8">
        <div class="form-section">
          <h5 class="mb-3">Detail Event</h5>
          <div class="event-info">
            <img src="foto/konser2.png" alt="Event Image" id="eventImage">
            <div>
              <h6 id="eventName">BALI BARBER EXPO</h6>
              <p class="text-muted mb-0" id="eventDateTime">05 – 06 Jul 2025, 10:00 – 21:00</p>
              <p class="text-muted mb-0" id="eventLocation">Dharma Negara Alaya</p>
              <span class="ticket-badge" id="ticketType">Presale 3 - Festival</span>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h5 class="mb-3">Data Pemesan</h5>
          <div class="mb-3">
            <label for="namaPemesan" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="namaPemesan" placeholder="Masukkan nama lengkap" required>
          </div>
          <div class="mb-3">
            <label for="emailPemesan" class="form-label">Email</label>
            <input type="email" class="form-control" id="emailPemesan" placeholder="Masukkan email" required>
          </div>
          <div class="mb-3">
            <label for="teleponPemesan" class="form-label">Nomor Telepon</label>
            <input type="tel" class="form-control" id="teleponPemesan" placeholder="Masukkan nomor telepon" required>
          </div>
        </div>

        <div class="form-section">
          <h5 class="mb-3">Data Pemilik Tiket</h5>
          <p class="text-muted small">Silakan isi data untuk setiap tiket yang dibeli</p>
          
          <div id="pemilikTiketContainer">
            <!-- Pemilik tiket akan ditambahkan melalui JavaScript -->
          </div>
          
          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="samakanDataCheck">
            <label class="form-check-label" for="samakanDataCheck">
              Samakan data dengan pemesan untuk tiket pertama
            </label>
          </div>
        </div>

        <div class="d-flex justify-content-between mt-4 mb-5">
          <a href="detail-event.html" class="btn btn-outline-primary px-4">Kembali</a>
          <button id="btnLanjutPembayaran" class="btn btn-primary px-4">Lanjut ke Pembayaran</button>
        </div>
      </div>

      <!-- Summary -->
      <div class="col-lg-4">
        <div class="form-section">
          <h5 class="mb-3">Ringkasan Pesanan</h5>
          <div class="summary-box">
            <div class="summary-item">
              <span>Jenis Tiket</span>
              <span id="summaryTicketType">Presale 3 - Festival</span>
            </div>
            <div class="summary-item">
              <span>Harga Tiket</span>
              <span id="summaryTicketPrice">Rp 150.000</span>
            </div>
            <div class="summary-item">
              <span>Jumlah Tiket</span>
              <span id="summaryTicketQuantity">1</span>
            </div>
            <hr>
            <div class="summary-item fw-bold">
              <span>Total</span>
              <span id="summaryTotal">Rp 150.000</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Ambil data dari localStorage
      const dataTiket = JSON.parse(localStorage.getItem('dataPembelianTiket'));
      
      if (!dataTiket) {
        // Jika tidak ada data tiket, redirect ke halaman detail event
        alert('Tidak ada data pembelian tiket!');
        window.location.href = 'detail-event.php';
        return;
      }
      
      // Isi data event
      document.getElementById('eventName').textContent = dataTiket.eventName;
      document.getElementById('eventDateTime').textContent = `${dataTiket.eventDate}, ${dataTiket.eventTime}`;
      document.getElementById('eventLocation').textContent = dataTiket.eventLocation;
      document.getElementById('ticketType').textContent = dataTiket.namaTiket;
      
      // Isi data ringkasan pembelian
      document.getElementById('summaryTicketType').textContent = dataTiket.namaTiket;
      document.getElementById('summaryTicketPrice').textContent = `Rp ${dataTiket.hargaTiket.toLocaleString('id-ID')}`;
      document.getElementById('summaryTicketQuantity').textContent = dataTiket.jumlah;
      document.getElementById('summaryTotal').textContent = `Rp ${dataTiket.totalHarga.toLocaleString('id-ID')}`;
      
      // Generate form untuk setiap pemilik tiket
      const container = document.getElementById('pemilikTiketContainer');
      container.innerHTML = '';
      
      for (let i = 1; i <= dataTiket.jumlah; i++) {
        const pemilikTiketForm = document.createElement('div');
        pemilikTiketForm.className = 'pemilik-tiket-card';
        pemilikTiketForm.innerHTML = `
          <div class="pemilik-tiket-header">
            <h6 class="mb-0">Pemilik Tiket ${i}</h6>
            <span class="badge bg-light text-dark">${dataTiket.namaTiket}</span>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control pemilik-nama" data-index="${i}" required>
            </div>