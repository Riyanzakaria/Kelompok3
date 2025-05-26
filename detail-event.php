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
    .swiper-button-next,
    .swiper-button-prev {
      color: #1E3A8A;
    }
    .hero {
      background-color: #1E3A8A;
      color: white;
      padding: 80px 0;
      position: relative;
      overflow: hidden;
    }
    .hero .left-img, .hero .right-img {
      position: absolute;
      top: 0;
      width: 33%;
      height: 100%;
    }
    .hero .left-img {
      left: 0;
    }
    .hero .right-img {
      right: 0;
    }
    .hero img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .hero .content {
      z-index: 10;
      position: relative;
      text-align: center;
    }
    .hero .btn-yellow {
      background-color: #FACC15;
      color: #1E3A8A;
      font-weight: bold;
      border-radius: 9999px;
    }
    .event-card {
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: .5rem;
      overflow: hidden;
      background-color: white;
    }
    .event-card img {
      height: 160px;
      object-fit: cover;
    }
    .event-card .price {
      color: #DC2626;
      font-weight: bold;
    }
    .event-card .btn-buy {
      background-color: #1E3A8A;
      color: white;
      border-radius: 9999px;
    }
    .swiper-container {
      max-width: 1000px;
      margin: 0 auto;
      overflow: hidden; 
      position: relative;
    }
    
    .swiper-wrapper {
      padding: 0 20px; 
    }
    
    .swiper-slide {
      margin: 0 10px; 
      transition: transform 0.3s ease-in-out;
    }
    
    .swiper-button-next,
    .swiper-button-prev {
      color: #1E3A8A;
      width: 36px;
      height: 36px;
      background-color: #fff;
      border-radius: 50%;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      top: 40%;
      transform: translateY(-50%);
      position: absolute;
      z-index: 10;
    }
    
    .swiper-button-prev {
      left: -20px;
    }
    .swiper-button-next {
      right: -20px;
    }
    .nav-button {
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      margin-right: 10px;
    }
    .nav-button.active {
      background-color: #1e3a8a;
      color: white;
    }
    .nav-button.inactive {
      background-color: white;
      color: black;
      border: 1px solid #ddd;
    }
    .image-landscape {
      aspect-ratio: 19 / 9;
      object-fit: cover;
      width: 100%;
      border-radius: 0.75rem;
    }
    .event-detail i {
      color: #1e3a8a;
      margin-right: 8px;
    }
    .event-detail strong {
      display: block;
      color: #6b7280;
      font-size: small;
    }
    .event-detail span {
      font-weight: 600;
      color: #111827;
    }
    .detail-box {
      background: transparent;
      padding: 0;
      box-shadow: none;
    }

    /* --- START: CSS untuk Fitur Pencarian & Bendera --- */
    .navbar-brand {
      color: white !important; 
      font-weight: bold;
    }
    .flag-container {
      display: flex;
      align-items: center;
      margin-right: 10px;
    }
    .flag {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: linear-gradient(to bottom, red 50%, white 50%);
      margin-right: 5px;
    }
    .flag-text {
      color: white;
      font-size: 14px;
    }
    .navbar-center {
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }
    .search-container {
      width: 100%;
      max-width: 500px; 
      opacity: 0;
      visibility: hidden;
      max-height: 0;
      overflow: hidden;
      transition: opacity 0.3s ease, visibility 0.3s ease, max-height 0.3s ease;
      position: absolute; 
      left: 50%;
      transform: translateX(-50%);
    }
    .search-container.active {
      opacity: 1;
      visibility: visible;
      max-height: 40px; 
    }
    .search-container input {
      width: 100%;
      padding: 5px 10px;
      border-radius: 20px;
      border: 1px solid #ccc;
      background-color: #f8f9fa;
      color: #333;
    }
    .search-container input::placeholder {
      color: #6c757d;
    }
    .nav-links { 
      display: flex; 
      opacity: 1;
      visibility: visible;
      max-height: 40px; 
      transition: opacity 0.3s ease, visibility 0.3s ease, max-height 0.3s ease;
    }
    .nav-links.hidden {
      opacity: 0;
      visibility: hidden;
      max-height: 0;
    }
    .fa-search { 
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    .fa-search:hover {
      transform: scale(1.1);
    }
    /* --- END: CSS untuk Fitur Pencarian & Bendera --- */
  </style>
</head>
<body>
   <!-- Navbar -->
   <nav class="navbar navbar-expand-lg">
    <div class="container d-flex align-items-center py-3"> <!-- Dihapus: justify-content-between -->
      <div class="d-flex align-items-center"> <!-- Ditambahkan wrapper untuk brand agar tidak ikut flex-grow -->
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
      <div class="d-flex align-items-center">
        <div class="flag-container">
          <div class="flag"></div>
          <span class="flag-text">ID</span>
        </div>
        <i class="fas fa-search text-white" id="searchToggle" aria-label="Toggle search bar"></i>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <div class="row align-items-start">
      <!-- Kiri: Konten utama -->
      <div class="col-lg-8">
        <small class="text-muted">Seminar</small>
        <h4 class="mb-3">BALI BARBER EXPO</h4>
        <div class="mb-3">
          <img src="foto/konser2.png" class="image-landscape" alt="Bali Barber Expo" />
        </div>

        <!-- Info bawah gambar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center">
            <img src="foto/eo.jpg" alt="Logo Penyelenggara" width="40" height="40" class="me-2 rounded-circle" />
            <span class="fw-semibold">Penyelenggara<br><strong>Bali Barber Expo</strong></span>
          </div>
          <div class="text-end">
            <small class="d-block">Instagram</small>
            <a href="#" class="text-decoration-none text-dark fw-semibold">
              <i class="bi bi-instagram me-1"></i> balibarberexpo
            </a>
          </div>
        </div>

        <!-- Tombol navigasi -->
        <div class="mb-3">
          <button class="nav-button active" id="btnDeskripsi">
            <i class="bi bi-info-circle me-1"></i> Deskripsi
          </button>
          <button class="nav-button inactive" id="btnTiket">
            <i class="bi bi-ticket-perforated me-1"></i> Tiket
          </button>
        </div>

        <!-- Konten Deskripsi / Tiket -->
        <div id="contentArea">
          <div id="kontenDeskripsi">
            <h6>Deskripsi Event</h6>
            <p><em>Bali Barber Expo</em> adalah event barber tahunan yang diselenggarakan di pulau Dewata Bali yang mempertemukan para profesional industri barber dari seluruh Indonesia dan internasional. Dilengkapi dengan kompetisi, seminar, dan booth-brand ternama. Ini adalah ajang edukasi, inspirasi, dan networking terbaik bagi para barber dan pecinta dunia grooming pria.</p>
          </div>

          <div id="kontenTiket" style="display: none;">
            <div class="card p-3 mb-3 border border-light shadow-sm rounded-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Presale 3 - Festival</strong>
                <span class="badge bg-light text-primary border border-primary">On Sale</span>
              </div>
              <hr />
              <div class="mb-3">
                <div class="text-muted small">Harga</div>
                <div class="text-danger fw-bold">Rp 150.000</div>
              </div>

            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <div class="d-flex align-items-center">
                <button class="btn btn-light border rounded-3 me-2">−</button>
                <input type="text" id="jumlahTiketFest" class="form-control text-center" value="1" style="width: 50px;" readonly />
                <button class="btn btn-light border rounded-3 ms-2">+</button>
              </div>  
              <div class="d-flex align-items-center">
                <button class="btn btn-primary rounded-3 px-4" style="background-color: #1e3a8a; border: none;">Beli</button>
              </div>
            </div>

            </div>
            <div class="card p-3 mb-3 border border-light shadow-sm rounded-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Presale 3 - VIP Access</strong>
                <span class="badge bg-light text-primary border border-primary">On Sale</span>
              </div>
              <hr />
              <div class="mb-3">
                <div class="text-muted small">Harga</div>
                <div class="text-danger fw-bold">Rp 300.000</div>
              </div>

            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <div class="d-flex align-items-center">
                <button class="btn btn-light border rounded-3 me-2">−</button>
                <input type="text" id="jumlahTiketVip" class="form-control text-center" value="1" style="width: 50px;" readonly />
                <button class="btn btn-light border rounded-3 ms-2">+</button>
              </div>  
              <div class="d-flex align-items-center">
                <button class="btn btn-primary rounded-3 px-4" style="background-color: #1e3a8a; border: none;">Beli</button>
              </div>
            </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Kanan: Detail event sejajar -->
      <div class="col-lg-4 mt-5 pt-4">
        <div class="detail-box mb-4">
          <h6 class="mb-3">Detail Event</h6>
          <div class="event-detail mb-3">
            <i class="bi bi-calendar-event"></i>
            <strong>Tanggal</strong>
            <span>05 – 06 Jul 2025</span>
          </div>
          <div class="event-detail mb-3">
            <i class="bi bi-clock"></i>
            <strong>Waktu</strong>
            <span>10:00 – 21:00</span>
          </div>
          <div class="event-detail mb-4">
            <i class="bi bi-geo-alt"></i>
            <strong>Lokasi</strong>
            <span>Dharma Negara Alaya</span>
          </div>
          <button class="btn btn-primary w-100" style="background-color: #1e3a8a; border: none;">Beli Tiket</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const btnDeskripsi = document.getElementById('btnDeskripsi');
    const btnTiket = document.getElementById('btnTiket');
    const kontenDeskripsi = document.getElementById('kontenDeskripsi');
    const kontenTiket = document.getElementById('kontenTiket');

    btnDeskripsi.addEventListener('click', () => {
      btnDeskripsi.classList.add('active');
      btnDeskripsi.classList.remove('inactive');
      btnTiket.classList.remove('active');
      btnTiket.classList.add('inactive');
      kontenDeskripsi.style.display = 'block';
      kontenTiket.style.display = 'none';
    });

    btnTiket.addEventListener('click', () => {
      btnTiket.classList.add('active');
      btnTiket.classList.remove('inactive');
      btnDeskripsi.classList.remove('active');
      btnDeskripsi.classList.add('inactive');
      kontenDeskripsi.style.display = 'none';
      kontenTiket.style.display = 'block';
    });

    // --- START: JavaScript untuk Fitur Pencarian ---
    let isToggling = false;
    document.getElementById('searchToggle').addEventListener('click', function() {
      if (isToggling) return;
      isToggling = true;

      const navLinks = document.getElementById('navLinks');
      const searchContainer = document.getElementById('searchContainer');
      
      navLinks.classList.toggle('hidden');
      searchContainer.classList.toggle('active');

      const isSearchVisible = searchContainer.classList.contains('active');
      this.setAttribute('aria-expanded', isSearchVisible);
      searchContainer.setAttribute('aria-hidden', !isSearchVisible);

      setTimeout(() => {
        isToggling = false;
        if (isSearchVisible) {
          searchContainer.querySelector('input').focus();
        }
      }, 300);
    });
    // --- END: JavaScript untuk Fitur Pencarian ---
  </script>
</body>
</html>
