<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="foto/logoputih.png" rel="icon">
  <title>Bali Barber Expo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"/>
  <style>
    /* ... CSS Anda yang sudah ada (Sama seperti dashboard.php untuk navbar dan profile) ... */
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; background-color: #f9fafb; }
    .navbar { background-color: #1E3A8A; }
    .navbar a, .navbar .btn-link { color: white; text-decoration: none; margin: 0 10px; }
    .navbar a:hover, .navbar .btn-link:hover { text-decoration: underline; }
    .navbar .btn-link { padding: 0.375rem 0.75rem; border: none; background: none; font-weight: normal; }
    .image-landscape { aspect-ratio: 19 / 9; object-fit: cover; width: 100%; border-radius: 0.75rem; }
    .event-detail i { color: #1e3a8a; margin-right: 8px; }
    .event-detail strong { display: block; color: #6b7280; font-size: small; }
    .event-detail span { font-weight: 600; color: #111827; }
    .detail-box { background: transparent; padding: 0; box-shadow: none; }
    .nav-button { border: none; padding: 10px 20px; border-radius: 5px; margin-right: 10px; }
    .nav-button.active { background-color: #1e3a8a; color: white; }
    .nav-button.inactive { background-color: white; color: black; border: 1px solid #ddd; }
    .navbar-brand { color: white !important; font-weight: bold; }
    .navbar-center { flex-grow: 1; display: flex; justify-content: center; align-items: center; position: relative; }
    .search-container { width: 100%; max-width: 500px; opacity: 0; visibility: hidden; max-height: 0; overflow: hidden; transition: opacity 0.3s ease, visibility 0.3s ease, max-height 0.3s ease; position: absolute; left: 50%; transform: translateX(-50%); }
    .search-container.active { opacity: 1; visibility: visible; max-height: 40px; }
    .search-container input { width: 100%; padding: 5px 10px; border-radius: 20px; border: 1px solid #ccc; background-color: #f8f9fa; color: #333; }
    .search-container input::placeholder { color: #6c757d; }
    .nav-links { display: flex; opacity: 1; visibility: visible; max-height: 40px; transition: opacity 0.3s ease, visibility 0.3s ease, max-height 0.3s ease; }
    .nav-links.hidden { opacity: 0; visibility: hidden; max-height: 0; }
    .navbar-actions .fa-search { cursor: pointer; transition: transform 0.3s ease; color: white; margin-right: 15px; }
    .navbar-actions .fa-search:hover { transform: scale(1.1); }
    .profile-icon-link { display: inline-block; }
    .profile-icon { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; cursor: pointer; border: 1px solid rgba(255, 255, 255, 0.5); }
    .btn-daftar-sekarang { color: white; font-weight: 500; text-decoration: none; padding: 0.375rem 0.75rem;}
    .btn-daftar-sekarang:hover { text-decoration: underline; color: #f0f0f0; }
    .btn-beli-tiket { background-color: #1e3a8a; border: none; }
  </style>
</head>
<body>
   <nav class="navbar navbar-expand-lg">
    <div class="container d-flex align-items-center py-3">
      <div class="d-flex align-items-center">
        <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">Harmonix</a>
      </div>
      <div class="navbar-center">
        <div class="nav-links d-none d-lg-flex" id="navLinks">
          <a href="dashboard_tiket.php">Jelajah</a>
          <a href="#">Event Creator</a>
          <a href="#">Hubungi Kami</a>
        </div>
        <div class="search-container" id="searchContainer">
          <input type="text" placeholder="Cari..." aria-label="Search events" />
        </div>
      </div>
      <div class="d-flex align-items-center navbar-actions" id="navbarActionsDetail">
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <div class="row align-items-start">
      <div class="col-lg-8">
        <small class="text-muted">Seminar</small>
        <h4 class="mb-3" id="detailEventName">BALI BARBER EXPO</h4>
        <div class="mb-3"> <img src="foto/konser2.png" class="image-landscape" id="detailEventImage" alt="Bali Barber Expo" /> </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center"> <img src="foto/logo-bali-barber-expo.jpg" alt="Logo Penyelenggara" width="40" height="40" class="me-2 rounded-circle" /> <span class="fw-semibold">Penyelenggara<br><strong>Bali Barber Expo</strong></span> </div>
          <div class="text-end"> <small class="d-block">Instagram</small> <a href="#" class="text-decoration-none text-dark fw-semibold"> <i class="bi bi-instagram me-1"></i> balibarberexpo </a> </div>
        </div>
        <div class="mb-3"> <button class="nav-button active" id="btnDeskripsi"> <i class="bi bi-info-circle me-1"></i> Deskripsi </button> <button class="nav-button inactive" id="btnTiket"> <i class="bi bi-ticket-perforated me-1"></i> Tiket </button> </div>
        <div id="contentArea">
          <div id="kontenDeskripsi"> <h6>Deskripsi Event</h6> <p><em>Bali Barber Expo</em> adalah event barber tahunan...</p> </div>
          <div id="kontenTiket" style="display: none;">
            <div class="card p-3 mb-3 border border-light shadow-sm rounded-4">
              <div class="d-flex justify-content-between align-items-center mb-2"> <strong>Presale 3 - Festival</strong> <span class="badge bg-light text-primary border border-primary">On Sale</span> </div> <hr />
              <div class="mb-3"> <div class="text-muted small">Harga</div> <div class="text-danger fw-bold">Rp 150.000</div> </div>
              <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center"> <button class="btn btn-light border rounded-3 me-2 btn-kurang" data-target="jumlahTiketFest">−</button> <input type="text" id="jumlahTiketFest" class="form-control text-center" value="1" style="width: 50px;" readonly /> <button class="btn btn-light border rounded-3 ms-2 btn-tambah" data-target="jumlahTiketFest">+</button> </div>
                <div class="d-flex align-items-center"> <button class="btn btn-primary rounded-3 px-4 btn-beli-tiket" id="beliTiketFest">Beli</button> </div>
              </div>
            </div>
            <div class="card p-3 mb-3 border border-light shadow-sm rounded-4">
              <div class="d-flex justify-content-between align-items-center mb-2"> <strong>Presale 3 - VIP Access</strong> <span class="badge bg-light text-primary border border-primary">On Sale</span> </div> <hr />
              <div class="mb-3"> <div class="text-muted small">Harga</div> <div class="text-danger fw-bold">Rp 300.000</div> </div>
              <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center"> <button class="btn btn-light border rounded-3 me-2 btn-kurang" data-target="jumlahTiketVip">−</button> <input type="text" id="jumlahTiketVip" class="form-control text-center" value="1" style="width: 50px;" readonly /> <button class="btn btn-light border rounded-3 ms-2 btn-tambah" data-target="jumlahTiketVip">+</button> </div>
                <div class="d-flex align-items-center"> <button class="btn btn-primary rounded-3 px-4 btn-beli-tiket" id="beliTiketVip">Beli</button> </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mt-5 pt-4">
        <div class="detail-box mb-4">
          <h6 class="mb-3">Detail Event</h6>
          <div class="event-detail mb-3"> <i class="bi bi-calendar-event"></i> <strong>Tanggal</strong> <span id="detailEventDate">05 – 06 Jul 2025</span> </div>
          <div class="event-detail mb-3"> <i class="bi bi-clock"></i> <strong>Waktu</strong> <span id="detailEventTime">10:00 – 21:00</span> </div>
          <div class="event-detail mb-4"> <i class="bi bi-geo-alt"></i> <strong>Lokasi</strong> <span id="detailEventLocation">Dharma Negara Alaya</span> </div>
          <button class="btn btn-primary w-100 btn-beli-tiket" id="beliTiketSidebar">Beli Tiket</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https.cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/init.js"></script> {/* Pastikan path ini benar */}
  <script>
    // Skrip spesifik detail-event.php
    const btnDeskripsi = document.getElementById('btnDeskripsi');
    const btnTiket = document.getElementById('btnTiket');
    const kontenDeskripsi = document.getElementById('kontenDeskripsi');
    const kontenTiket = document.getElementById('kontenTiket');
    const beliTiketFestButton = document.getElementById('beliTiketFest');
    const beliTiketVipButton = document.getElementById('beliTiketVip');
    const beliTiketSidebarButton = document.getElementById('beliTiketSidebar');

    if(btnDeskripsi && btnTiket && kontenDeskripsi && kontenTiket) {
        btnDeskripsi.addEventListener('click', () => { 
            btnDeskripsi.classList.add('active'); btnDeskripsi.classList.remove('inactive');
            btnTiket.classList.remove('active'); btnTiket.classList.add('inactive');
            kontenDeskripsi.style.display = 'block'; kontenTiket.style.display = 'none';
        });
        btnTiket.addEventListener('click', () => {
            btnTiket.classList.add('active'); btnTiket.classList.remove('inactive');
            btnDeskripsi.classList.remove('active'); btnDeskripsi.classList.add('inactive');
            kontenDeskripsi.style.display = 'none'; kontenTiket.style.display = 'block';
        });
    }
    
    function handleBeliTiketAction(jenisTiketId, namaTiketDisplay, hargaTiketDisplay) {
        const jumlahInputId = jenisTiketId === 'festival' ? 'jumlahTiketFest' : 'jumlahTiketVip';
        const jumlahTiket = parseInt(document.getElementById(jumlahInputId).value);
        const hargaNumerik = parseInt(hargaTiketDisplay.replace(/[^0-9]/g, ''));
        const dataPembelian = {
            eventName: document.getElementById('detailEventName').textContent.trim(),
            eventDate: document.getElementById('detailEventDate').textContent.trim(),
            eventTime: document.getElementById('detailEventTime').textContent.trim(),
            eventLocation: document.getElementById('detailEventLocation').textContent.trim(),
            eventImage: document.getElementById('detailEventImage').src,
            jenis: jenisTiketId, namaTiket: namaTiketDisplay, hargaTiket: hargaNumerik,
            jumlah: jumlahTiket, totalHarga: hargaNumerik * jumlahTiket
        };
        localStorage.setItem('dataPembelianTiket', JSON.stringify(dataPembelian));
        window.location.href = 'form-pembelian.php';
    }

    if (beliTiketFestButton) {
        beliTiketFestButton.addEventListener('click', function() {
            const cardElement = this.closest('.card');
            const namaTiket = cardElement.querySelector('strong').textContent.trim();
            const hargaTiketText = cardElement.querySelector('.text-danger.fw-bold').textContent.trim();
            requireLogin(() => handleBeliTiketAction('festival', namaTiket, hargaTiketText));
        });
    }
    if (beliTiketVipButton) {
        beliTiketVipButton.addEventListener('click', function() {
            const cardElement = this.closest('.card');
            const namaTiket = cardElement.querySelector('strong').textContent.trim();
            const hargaTiketText = cardElement.querySelector('.text-danger.fw-bold').textContent.trim();
            requireLogin(() => handleBeliTiketAction('vip', namaTiket, hargaTiketText));
        });
    }
    if (beliTiketSidebarButton) {
        beliTiketSidebarButton.addEventListener('click', function() {
             requireLogin(() => { 
                if(btnTiket) btnTiket.click();
            });
        });
    }

    document.querySelectorAll('.btn-tambah').forEach(button => { 
        button.addEventListener('click', function() {
            const targetInputId = this.dataset.target;
            const inputElement = document.getElementById(targetInputId);
            let currentValue = parseInt(inputElement.value);
            inputElement.value = currentValue + 1;
        });
    });
    document.querySelectorAll('.btn-kurang').forEach(button => { 
        button.addEventListener('click', function() {
            const targetInputId = this.dataset.target;
            const inputElement = document.getElementById(targetInputId);
            let currentValue = parseInt(inputElement.value);
            if (currentValue > 1) { inputElement.value = currentValue - 1; }
        });
    });
  </script>
</body>
</html>
