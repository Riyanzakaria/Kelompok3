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
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; /* Font default Bootstrap, mirip "Data Pemesan" */
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

    .form-section h5 { /* Menyamakan font judul section dengan body */
        font-weight: 600; /* Sedikit lebih tebal dari body, sesuaikan jika perlu */
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

    .pemilik-tiket-card {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 24px; 
    }

    .pemilik-tiket-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
    .form-label-required::after {
        content: " *";
        color: red;
    }
    .form-switch .form-check-label {
        padding-left: 0.5em; 
    }

    /* CSS untuk Breadcrumb */
    .breadcrumb {
        font-size: 0.9rem; /* Sedikit lebih kecil, sesuaikan jika perlu */
    }
    .breadcrumb-item a {
        color: #212529; /* Warna hitam atau abu-abu tua */
        text-decoration: none; /* Menghilangkan garis bawah */
    }
    .breadcrumb-item a:hover {
        color: #0d6efd; /* Warna link hover default Bootstrap, atau sesuaikan */
        text-decoration: none; /* Tetap tanpa garis bawah saat hover */
    }
    .breadcrumb-item.active {
        color: #495057; /* Warna untuk item aktif (Form Pembelian) */
    }
    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d; /* Warna separator "/" */
    }

  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container d-flex justify-content-between align-items-center py-3">
      <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">Harmonix</a>
      <div class="d-none d-lg-flex">
        <a href="dashboard_tiket.php">Jelajah</a>
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
        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
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
              <h6 id="eventName">NAMA EVENT</h6>
              <p class="text-muted mb-0" id="eventDateTime">TANGGAL, WAKTU</p>
              <p class="text-muted mb-0" id="eventLocation">LOKASI</p>
              <span class="ticket-badge" id="ticketType">JENIS TIKET</span>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h5 class="mb-3">Data Pemesan</h5>
          <div class="mb-3">
            <label for="namaPemesan" class="form-label form-label-required">Nama Lengkap</label>
            <input type="text" class="form-control" id="namaPemesan" placeholder="Masukkan nama lengkap" required>
          </div>
          <div class="mb-3">
            <label class="form-label form-label-required">Identitas</label>
            <div class="row">
              <div class="col-md-5 col-lg-4 mb-2 mb-md-0">
                <select class="form-select" id="jenisIdentitasPemesan" required>
                  <option value="KTP" selected>KTP</option>
                  <option value="SIM">SIM</option>
                  <option value="Pasport">Pasport</option>
                  <option value="KTM">KTM</option>
                  <option value="Kartu Pelajar">Kartu Pelajar</option>
                </select>
              </div>
              <div class="col-md-7 col-lg-8">
                <input type="text" class="form-control" id="nomorIdentitasPemesan" placeholder="Masukkan nomor identitas" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="emailPemesan" class="form-label form-label-required">Email</label>
            <input type="email" class="form-control" id="emailPemesan" placeholder="Masukkan email" required>
          </div>
          <div class="mb-3">
            <label for="whatsappPemesan" class="form-label form-label-required">No. Whatsapp</label>
            <input type="tel" class="form-control" id="whatsappPemesan" placeholder="Contoh: 08123456789" required>
          </div>
        </div>

        <div class="form-section">
          <div class="d-flex justify-content-between align-items-center mb-3">
             <h5 class="mb-0">Data Pemilik Tiket</h5>
             <i class="fas fa-ticket-alt" style="font-size: 1.2rem; color: #6b7280;"></i> 
          </div>
          <p class="text-muted small">Silakan isi data untuk setiap tiket yang dibeli.</p>
          
          <div id="pemilikTiketContainer">
            <!-- Form pemilik tiket akan digenerate oleh JavaScript di sini -->
          </div>
        </div>


        <div class="d-flex justify-content-between mt-4 mb-5">
          <a href="detail-event.php" class="btn btn-outline-primary px-4">Kembali</a>
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
              <span id="summaryTicketType">-</span>
            </div>
            <div class="summary-item">
              <span>Harga Tiket</span>
              <span id="summaryTicketPrice">Rp 0</span>
            </div>
            <div class="summary-item">
              <span>Jumlah Tiket</span>
              <span id="summaryTicketQuantity">0</span>
            </div>
            <hr>
            <div class="summary-item fw-bold">
              <span>Total</span>
              <span id="summaryTotal">Rp 0</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const dataTiket = JSON.parse(localStorage.getItem('dataPembelianTiket'));

      if (!dataTiket || !dataTiket.jumlah || dataTiket.jumlah < 1) {
        alert('Data pembelian tidak valid atau jumlah tiket tidak ditemukan. Anda akan diarahkan kembali.');
        window.location.href = 'detail-event.php';
        return;
      }

      // Isi data event
      document.getElementById('eventName').textContent = dataTiket.eventName || "Nama Event Default";
      document.getElementById('eventDateTime').textContent = `${dataTiket.eventDate || "Tanggal Default"}, ${dataTiket.eventTime || "Waktu Default"}`;
      document.getElementById('eventLocation').textContent = dataTiket.eventLocation || "Lokasi Default";
      document.getElementById('ticketType').textContent = dataTiket.namaTiket || "Jenis Tiket Default";
      if(document.getElementById('eventImage') && dataTiket.eventImage) {
          document.getElementById('eventImage').src = dataTiket.eventImage;
          document.getElementById('eventImage').alt = dataTiket.eventName || "Gambar Event";
      }
      const eventBreadcrumbLink = document.getElementById('eventBreadcrumb');
        if(eventBreadcrumbLink) {
            eventBreadcrumbLink.textContent = dataTiket.eventName || "Event";
            // Jika Anda ingin link breadcrumb event mengarah ke detail event tertentu (jika ada ID event)
            // eventBreadcrumbLink.href = `detail-event.php?id=${dataTiket.eventId || ''}`;
        }


      // Isi data ringkasan pembelian
      document.getElementById('summaryTicketType').textContent = dataTiket.namaTiket;
      document.getElementById('summaryTicketPrice').textContent = `Rp ${dataTiket.hargaTiket.toLocaleString('id-ID')}`;
      document.getElementById('summaryTicketQuantity').textContent = dataTiket.jumlah;
      document.getElementById('summaryTotal').textContent = `Rp ${dataTiket.totalHarga.toLocaleString('id-ID')}`;

      // --- Generate form untuk setiap pemilik tiket ---
      const container = document.getElementById('pemilikTiketContainer');
      container.innerHTML = ''; 

      for (let i = 1; i <= dataTiket.jumlah; i++) {
        const cardDiv = document.createElement('div');
        cardDiv.className = 'pemilik-tiket-card'; 
        
        let samakanDataToggleHTML = '';
        if (i === 1) { 
            samakanDataToggleHTML = `
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="samakanDataToggle_${i}">
                <label class="form-check-label" for="samakanDataToggle_${i}">Samakan dengan data pemesan</label>
            </div>
            `;
        }

        const isFirstTicket = i === 1;
        const chevronClass = isFirstTicket ? 'fa-chevron-down' : 'fa-chevron-up'; 
        const collapseStatus = isFirstTicket ? 'show' : ''; 

        cardDiv.innerHTML = `
          <div class="pemilik-tiket-header">
            <h6 class="mb-0 fw-bold">Pemilik Tiket ${i}</h6>
            <i class="fas ${chevronClass}" style="color: #6c757d; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapsePemilikTiket${i}"></i>
          </div>
          <div class="collapse ${collapseStatus}" id="collapsePemilikTiket${i}">
            ${samakanDataToggleHTML}
            <div class="mb-3">
                <label for="namaPemilik_${i}" class="form-label form-label-required">Nama Lengkap</label>
                <input type="text" class="form-control nama-pemilik-tiket" id="namaPemilik_${i}" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="mb-3">
                <label class="form-label form-label-required">Identitas</label>
                <div class="row">
                <div class="col-md-5 col-lg-4 mb-2 mb-md-0">
                    <select class="form-select jenis-identitas-pemilik" id="jenisIdentitasPemilik_${i}" required>
                    <option value="KTP" selected>KTP</option>
                    <option value="SIM">SIM</option>
                    <option value="Pasport">Pasport</option>
                    <option value="KTM">KTM</option>
                    <option value="Kartu Pelajar">Kartu Pelajar</option>
                    </select>
                </div>
                <div class="col-md-7 col-lg-8">
                    <input type="text" class="form-control nomor-identitas-pemilik" id="nomorIdentitasPemilik_${i}" placeholder="Masukkan nomor identitas" required>
                </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="emailPemilik_${i}" class="form-label form-label-required">Email</label>
                <input type="email" class="form-control email-pemilik-tiket" id="emailPemilik_${i}" placeholder="Masukkan email" required>
            </div>
            <div class="mb-3">
                <label for="whatsappPemilik_${i}" class="form-label form-label-required">No. Whatsapp</label>
                <input type="tel" class="form-control whatsapp-pemilik-tiket" id="whatsappPemilik_${i}" placeholder="Contoh: 08123456789" required>
            </div>
          </div>
        `;
        container.appendChild(cardDiv);

        const chevron = cardDiv.querySelector('.fas.fa-chevron-up, .fas.fa-chevron-down');
        if (chevron) {
            chevron.addEventListener('click', function() {
                this.classList.toggle('fa-chevron-down');
                this.classList.toggle('fa-chevron-up');
            });
        }
      }

      // --- Logika untuk toggle "Samakan data" (HANYA UNTUK TIKET PERTAMA) ---
      const namaPemesanInput = document.getElementById('namaPemesan');
      const jenisIdentitasPemesanSelect = document.getElementById('jenisIdentitasPemesan');
      const nomorIdentitasPemesanInput = document.getElementById('nomorIdentitasPemesan');
      const emailPemesanInput = document.getElementById('emailPemesan');
      const whatsappPemesanInput = document.getElementById('whatsappPemesan');

      const samakanDataToggle1 = document.getElementById('samakanDataToggle_1');

      if (samakanDataToggle1) { 
        function applyPemesanDataToTiket1() {
            const namaPemilik1 = document.getElementById('namaPemilik_1');
            const jenisIdentitasPemilik1 = document.getElementById('jenisIdentitasPemilik_1');
            const nomorIdentitasPemilik1 = document.getElementById('nomorIdentitasPemilik_1');
            const emailPemilik1 = document.getElementById('emailPemilik_1');
            const whatsappPemilik1 = document.getElementById('whatsappPemilik_1');

            if (samakanDataToggle1.checked) {
                if (namaPemilik1) namaPemilik1.value = namaPemesanInput.value;
                if (jenisIdentitasPemilik1) jenisIdentitasPemilik1.value = jenisIdentitasPemesanSelect.value;
                if (nomorIdentitasPemilik1) nomorIdentitasPemilik1.value = nomorIdentitasPemesanInput.value;
                if (emailPemilik1) emailPemilik1.value = emailPemesanInput.value;
                if (whatsappPemilik1) whatsappPemilik1.value = whatsappPemesanInput.value;
            }
        }
        
        samakanDataToggle1.addEventListener('change', applyPemesanDataToTiket1);

        [namaPemesanInput, jenisIdentitasPemesanSelect, nomorIdentitasPemesanInput, emailPemesanInput, whatsappPemesanInput].forEach(input => {
            const eventType = input.tagName === 'SELECT' ? 'change' : 'input';
            input.addEventListener(eventType, function() { 
                if (samakanDataToggle1.checked) {
                    applyPemesanDataToTiket1();
                }
            });
        });
      }


      // --- Event listener untuk tombol Lanjut Pembayaran ---
      const btnLanjut = document.getElementById('btnLanjutPembayaran');
      if (btnLanjut) {
        btnLanjut.addEventListener('click', function(event) {
          event.preventDefault(); 
          let isValid = true;
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          
          function displayError(inputId, message) {
            removeError(inputId); 
            const inputElement = document.getElementById(inputId);
            if (inputElement) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-danger small mt-1 error-message';
                errorDiv.textContent = message;
                inputElement.parentNode.appendChild(errorDiv);
                inputElement.classList.add('is-invalid');
                if(isValid && !document.querySelector('.is-invalid:focus')) { 
                    const firstErrorField = document.querySelector('.is-invalid');
                    if(firstErrorField) firstErrorField.focus();
                }
            }
          }
          function removeError(inputId) {
            const inputElement = document.getElementById(inputId);
             if (inputElement) {
                inputElement.classList.remove('is-invalid');
                const parent = inputElement.parentNode;
                const oldError = parent.querySelector('.error-message');
                if (oldError) parent.removeChild(oldError);
            }
          }
          function clearAllErrors() {
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.error-message').forEach(el => el.remove());
          }

          clearAllErrors(); 

          const dataPemesan = {
            nama: namaPemesanInput.value.trim(),
            jenisIdentitas: jenisIdentitasPemesanSelect.value,
            nomorIdentitas: nomorIdentitasPemesanInput.value.trim(),
            email: emailPemesanInput.value.trim(),
            noWhatsapp: whatsappPemesanInput.value.trim()
          };

          if (!dataPemesan.nama) { displayError('namaPemesan', 'Nama lengkap pemesan wajib diisi.'); isValid = false; }
          if (!dataPemesan.nomorIdentitas) { displayError('nomorIdentitasPemesan', 'Nomor identitas pemesan wajib diisi.'); isValid = false; }
          if (!dataPemesan.email) { displayError('emailPemesan', 'Email pemesan wajib diisi.'); isValid = false; }
          else if (!emailRegex.test(dataPemesan.email)) { displayError('emailPemesan', 'Format email pemesan tidak valid.'); isValid = false; }
          if (!dataPemesan.noWhatsapp) { displayError('whatsappPemesan', 'No. Whatsapp pemesan wajib diisi.'); isValid = false; }

          const dataPemilikTikets = [];
          if (isValid) { 
            for (let i = 1; i <= dataTiket.jumlah; i++) {
                const collapseElement = document.getElementById(`collapsePemilikTiket${i}`);
                const isTicketVisible = collapseElement.classList.contains('show');
                const samakanToggle = document.getElementById(`samakanDataToggle_${i}`);
                const shouldValidateThisTicket = isTicketVisible || (samakanToggle && !samakanToggle.checked) || i === 1;

                if(shouldValidateThisTicket){ 
                    const namaInput = document.getElementById(`namaPemilik_${i}`);
                    const jenisIdentitasSelect = document.getElementById(`jenisIdentitasPemilik_${i}`);
                    const nomorIdentitasInput = document.getElementById(`nomorIdentitasPemilik_${i}`);
                    const emailInput = document.getElementById(`emailPemilik_${i}`);
                    const whatsappInput = document.getElementById(`whatsappPemilik_${i}`);

                    const nama = namaInput.value.trim();
                    const jenisIdentitas = jenisIdentitasSelect.value;
                    const nomorIdentitas = nomorIdentitasInput.value.trim();
                    const email = emailInput.value.trim();
                    const noWhatsapp = whatsappInput.value.trim();

                    let currentTicketValid = true;
                    if (!nama) { displayError(`namaPemilik_${i}`, `Nama lengkap Pemilik Tiket ${i} wajib diisi.`); currentTicketValid = false; }
                    if (!nomorIdentitas) { displayError(`nomorIdentitasPemilik_${i}`, `Nomor identitas Pemilik Tiket ${i} wajib diisi.`); currentTicketValid = false; }
                    if (!email) { displayError(`emailPemilik_${i}`, `Email Pemilik Tiket ${i} wajib diisi.`); currentTicketValid = false; }
                    else if (!emailRegex.test(email)) { displayError(`emailPemilik_${i}`, `Format email Pemilik Tiket ${i} tidak valid.`); currentTicketValid = false; }
                    if (!noWhatsapp) { displayError(`whatsappPemilik_${i}`, `No. Whatsapp Pemilik Tiket ${i} wajib diisi.`); currentTicketValid = false; }
                    
                    if (!currentTicketValid) {
                        isValid = false; 
                        if(!isTicketVisible) {
                            new bootstrap.Collapse(collapseElement).show();
                             const chevron = collapseElement.previousElementSibling.querySelector('.fas');
                             if (chevron) {
                                chevron.classList.remove('fa-chevron-up');
                                chevron.classList.add('fa-chevron-down');
                             }
                        }
                    } else {
                         dataPemilikTikets.push({ 
                            idTiket: i, nama, jenisIdentitas, nomorIdentitas, email, noWhatsapp 
                        });
                    }
                } else if(samakanToggle && samakanToggle.checked) { 
                     dataPemilikTikets.push({ 
                        idTiket: i, 
                        nama: dataPemesan.nama, 
                        jenisIdentitas: dataPemesan.jenisIdentitas, 
                        nomorIdentitas: dataPemesan.nomorIdentitas, 
                        email: dataPemesan.email, 
                        noWhatsapp: dataPemesan.noWhatsapp
                    });
                }
                 if (!isValid && i < dataTiket.jumlah && !document.getElementById(`collapsePemilikTiket${i+1}`).classList.contains('show')) {
                     // Jika ada error dan tiket berikutnya collapsed, tidak perlu break, biarkan loop lanjut untuk membuka jika ada error lagi
                 } else if (!isValid) {
                    //  break; // Jika sudah tidak valid dan tiket berikutnya terbuka atau ini tiket terakhir, bisa break
                 }
            }
          }


          if (isValid) {
            const semuaDataPemesanan = {
              eventDetails: dataTiket,
              pemesan: dataPemesan,
              pemilikTiket: dataPemilikTikets
            };
            console.log('Semua Data Siap Dikirim:', semuaDataPemesanan);
            alert('Data valid! Siap untuk lanjut ke pembayaran. (Cek console untuk detail data)');
            // window.location.href = 'halaman-pembayaran.php'; 
          } else {
            alert("Terdapat kesalahan pada pengisian data. Mohon periksa kembali field yang ditandai.");
            console.log("Validasi gagal.");
          }
        });
      }
    });
  </script>

</body>
</html>
