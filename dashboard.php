<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <link href="foto/logoputih.png" rel="icon">
  <title>Harmonix - Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet"/>

  <style>
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
    .hero-banner {
      padding: 32px 0;
      background-color: #ffffff;
    }
    
    .hero-banner img {
      aspect-ratio: 30 / 9;
      object-fit: cover;
      border-radius: 16px;
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
      overflow: hidden; /* agar card tidak keluar area wrapper */
      position: relative;
    }
    
    .swiper-wrapper {
      padding: 0 20px; /* Tambahkan padding agar slide tidak mepet kiri kanan */
    }
    
    .swiper-slide {
      margin: 0 10px; /* Jarak antar card */
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
<body class="bg-white" style="overflow-x: hidden;">

  <!-- Navbar -->
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
      <div class="d-flex align-items-center">
        <div class="flag-container">
          <div class="flag"></div>
          <span class="flag-text">ID</span>
        </div>
        <i class="fas fa-search text-white" id="searchToggle" aria-label="Toggle search bar"></i>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
 <section class="hero-banner">
  <div class="container">
    <img src="foto/banner.png" alt="Banner Artatix" class="img-fluid rounded-4 w-100">
  </div>
</section>

  <!-- Event Recommendations -->
<section class=" bg-white position-relative">
  <div class="container">
    <h2 class="fw-bold mb-4">Rekomendasi Event</h2>
  </div>

  <div class="position-relative" style="max-width: 1200px; margin: 0 auto;">
    <div class="swiper-container posisition-relative">
      <div class="swiper-wrapper mb-4">
        <!-- Mulai Card -->
        <div class="swiper-slide bg-white shadow rounded overflow-hidden" style="max-height: 400px;">
          <img src="foto/konser2.png" alt="Event 1" class="w-100" style="height: 180px; object-fit: cover;">
          <div class="p-2">
            <h6 class="fw-bold mb-1 text-truncate">Bali Barber Expo</h6>
            <small class="text-muted d-block">10 May 2025</small>
            <small class="text-muted d-block mb-1">JNM Bloc</small>
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <p class="text-danger fw-bold mb-2 text-center" style="font-size: 0.9rem;">Rp 500.000</p>
              <a class="btn btn-sm btn-primary" href="detail-event.php" style="background-color: #1e3a8a; border:none;">Beli Tiket</a>
              </div>
          </div>
        </div> <div class="swiper-slide bg-white shadow rounded overflow-hidden" style="max-height: 400px;">
          <img src="foto/konser2.png" alt="Event 1" class="w-100" style="height: 180px; object-fit: cover;">
          <div class="p-2">
            <h6 class="fw-bold mb-1 text-truncate">Bali Barber Expo</h6>
            <small class="text-muted d-block">10 May 2025</small>
            <small class="text-muted d-block mb-1">JNM Bloc</small>
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <p class="text-danger fw-bold mb-2 text-center" style="font-size: 0.9rem;">Rp 500.000</p>
              <a class="btn btn-sm btn-primary" href="detail-event.php" style="background-color: #1e3a8a; border:none;">Beli Tiket</a>
              </div>
          </div>
        </div>
        <div class="swiper-slide bg-white shadow rounded overflow-hidden" style="max-height: 400px;">
          <img src="foto/konser2.png" alt="Event 1" class="w-100" style="height: 180px; object-fit: cover;">
          <div class="p-2">
            <h6 class="fw-bold mb-1 text-truncate">Bali Barber Expo</h6>
            <small class="text-muted d-block">10 May 2025</small>
            <small class="text-muted d-block mb-1">JNM Bloc</small>
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <p class="text-danger fw-bold mb-2 text-center" style="font-size: 0.9rem;">Rp 500.000</p>
              <a class="btn btn-sm btn-primary" href="detail-event.php" style="background-color: #1e3a8a; border:none;">Beli Tiket</a>
              </div>
          </div>
        </div>
        <div class="swiper-slide bg-white shadow rounded overflow-hidden" style="max-height: 400px;">
          <img src="foto/konser2.png" alt="Event 1" class="w-100" style="height: 180px; object-fit: cover;">
          <div class="p-2">
            <h6 class="fw-bold mb-1 text-truncate">Bali Barber Expo</h6>
            <small class="text-muted d-block">10 May 2025</small>
            <small class="text-muted d-block mb-1">JNM Bloc</small>
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <p class="text-danger fw-bold mb-2 text-center" style="font-size: 0.9rem;">Rp 500.000</p>
              <a class="btn btn-sm btn-primary" href="detail-event.php" style="background-color: #1e3a8a; border:none;">Beli Tiket</a>
              </div>
          </div>
        </div>
        <div class="swiper-slide bg-white shadow rounded overflow-hidden" style="max-height: 400px;">
          <img src="foto/konser2.png" alt="Event 1" class="w-100" style="height: 180px; object-fit: cover;">
          <div class="p-2">
            <h6 class="fw-bold mb-1 text-truncate">Bali Barber Expo</h6>
            <small class="text-muted d-block">10 May 2025</small>
            <small class="text-muted d-block mb-1">JNM Bloc</small>
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <p class="text-danger fw-bold mb-2 text-center" style="font-size: 0.9rem;">Rp 500.000</p>
              <a class="btn btn-sm btn-primary" href="detail-event.php" style="background-color: #1e3a8a; border:none;">Beli Tiket</a>
              </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation buttons (letakkan di luar swiper-wrapper) -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-pagination"></div>
  </div>
</section>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  <script>
    var swiper = new Swiper('.swiper-container', {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        576: {
          slidesPerView: 1.5,
        },
        768: {
          slidesPerView: 2.2,
        },
        992: {
          slidesPerView: 3,
        },
        1200: {
          slidesPerView: 4,
        }
      }
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
