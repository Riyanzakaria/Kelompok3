<?php
session_start(); 
require_once 'db.php'; 
$searchQuery = "";

if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
    header("Location: dashboard_tiket.php?search_query=" . urlencode(trim($_GET['search_query'])));
    exit; 
}

$eventsData = [];
$limitRecommendations = 8; 

$sqlRecommendations = "SELECT
                        e.event_id,
                        e.nama,
                        e.event_date,
                        e.lokasi,
                        e.image_url,
                        MIN(tt.price) AS starting_price
                    FROM
                        events e
                    LEFT JOIN
                        ticket_types tt ON e.event_id = tt.event_id
                    WHERE
                        e.status = 'upcoming'
                    GROUP BY
                        e.event_id
                    ORDER BY
                        e.event_date ASC 
                    LIMIT ?"; 

$stmt = $conn->prepare($sqlRecommendations);
if ($stmt) {
    $stmt->bind_param("i", $limitRecommendations);
    $stmt->execute();
    $resultRecommendations = $stmt->get_result();

    if ($resultRecommendations && $resultRecommendations->num_rows > 0) {
        while ($row = $resultRecommendations->fetch_assoc()) {
            $eventsData[] = $row;
        }
    }
    $stmt->close();
} else {
    error_log("Failed to prepare statement for recommendations: " . $conn->error);
}
// $conn->close(); // Sebaiknya ditutup di akhir skrip jika tidak ada operasi DB lagi
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <link href="foto/logoputih.png" rel="icon">
  <title>Harmonix - Beranda</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet" />
  <style>
    html, body {
      height: 100%; 
      margin: 0;
    }
    body {
      font-family: 'Inter', sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
      background-color: #f9fafb; /* Anda menggunakan bg-white di body tag, bisa disesuaikan */
      display: flex; 
      flex-direction: column; 
      overflow-x: hidden; /* Pindahkan dari tag body HTML ke sini */
    }
    .main-content-wrapper { /* Ini wrapper utama di dashboard.php */
        flex: 1 0 auto; 
    }
    footer {
        flex-shrink: 0; 
        /* Pastikan footer tidak memiliki margin atas yang besar yang mendorongnya ke bawah secara tidak perlu */
        /* Hapus mt-5 jika ada dan biarkan flexbox yang mengatur */
    }
    /* ... sisa CSS Anda untuk dashboard.php ... */
    .navbar { background-color: #1E3A8A; }
    .navbar a, .navbar .btn-link { color: white; text-decoration: none; margin: 0 10px; }
    .navbar a:hover, .navbar .btn-link:hover { text-decoration: underline; }
    .navbar .btn-link { padding: 0.375rem 0.75rem; border: none; background: none; font-weight: normal; }
    .hero-banner { padding: 32px 0; background-color: #ffffff; }
    .hero-banner img { aspect-ratio: 30 / 9; object-fit: cover; border-radius: 16px; }
    .swiper-slide .btn-buy-event, 
    .btn-lihat-lainnya { 
      background-color: #1E3A8A; color: white; border-radius: 9999px; 
      border: none; padding: 0.375rem 1rem; font-size: 0.875rem; 
      text-decoration: none; display: inline-block; 
    }
    .btn-lihat-lainnya:hover { background-color: #162b65; color: white; }
    .swiper-container { max-width: 1000px; margin: 0 auto; overflow: hidden; position: relative; }
    .swiper-slide {
      margin-right: 25px; background-color: white; 
      border-radius: .5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
      overflow: hidden; width: auto; 
    }
    .swiper-slide img { width: 100%; height: 180px; object-fit: cover; }
    .swiper-button-next, .swiper-button-prev {
      color: #1E3A8A; width: 38px; height: 38px; background-color: #fff;
      border-radius: 50%; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      display: flex; align-items: center; justify-content: center;
      top: calc(180px / 2); 
      transform: translateY(-50%); position: absolute; z-index: 10;
    }
    .swiper-button-prev::after, .swiper-button-next::after { font-size: 1rem; }
    .swiper-button-prev { left: -15px; }
    .swiper-button-next { right: -15px; }
    .navbar-brand { color: white !important; font-weight: bold; }
    .navbar-center { flex-grow: 1; display: flex; justify-content: center; align-items: center; position: relative; }
    
    .search-form-wrapper { 
      width: 100%; max-width: 500px; opacity: 0; visibility: hidden; max-height: 0; 
      overflow: hidden; transition: opacity 0.3s ease, visibility 0.3s ease, max-height 0.3s ease; 
      position: absolute; left: 50%; transform: translateX(-50%);
    }
    .search-form-wrapper.active { opacity: 1; visibility: visible; max-height: 40px; }
    .search-form-wrapper form { display: flex; width: 100%; }
    .search-form-wrapper input[type="text"] { 
        flex-grow: 1; padding: 5px 10px; border-radius: 20px 0 0 20px; 
        border: 1px solid #ccc; background-color: #f8f9fa; color: #333; 
        border-right: none; 
    }
    .search-form-wrapper input[type="text"]::placeholder { color: #6c757d; }
    .search-form-wrapper button[type="submit"] {
        background-color: #f8f9fa; border: 1px solid #ccc; color: #1E3A8A; 
        padding: 5px 12px; cursor: pointer; border-radius: 0 20px 20px 0; 
        border-left: none; 
    }
    .search-form-wrapper button[type="submit"]:hover { background-color: #e9ecef; }


    .nav-links { display: flex; opacity: 1; visibility: visible; max-height: 40px; transition: opacity 0.3s ease, visibility 0.3s ease, max-height 0.3s ease; }
    .nav-links.hidden { opacity: 0; visibility: hidden; max-height: 0; }
    .profile-icon { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; cursor: pointer; }
    .btn-daftar-sekarang { color: white; font-weight: 500; text-decoration: none; padding: 0.375rem 0.75rem; }
    .btn-daftar-sekarang:hover { text-decoration: underline; color: #f0f0f0; }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .flag-container { display: flex; align-items: center; }
    .flag { width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(to bottom, red 50%, white 50%); margin-right: 8px; }
    .flag-text { color: white; font-size: 0.875rem; }
    #searchToggleDashboard { cursor: pointer; }
  </style>
</head>
<body class="bg-white">  <!-- Anda menggunakan bg-white di sini, pastikan konsisten dengan style body di CSS -->

  <nav class="navbar navbar-expand-lg">
    <!-- Konten Navbar Anda -->
    <div class="container d-flex align-items-center py-3">
      <div class="d-flex align-items-center">
        <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">
          <img src="foto/logoputih.png" alt="Harmonix Logo" width="32" height="32" class="d-inline-block align-text-top me-2">
          Harmonix
        </a>
      </div>
      <div class="navbar-center">
        <div class="nav-links d-none d-lg-flex" id="navLinksDashboard">
          <a href="dashboard_tiket.php">Jelajah</a>
          <a href="tambah_event.php">Event Creator</a>
          <a href="#">Hubungi Kami</a>
        </div>
        <div class="search-form-wrapper" id="searchFormWrapperDashboard">
            <form method="GET" action="dashboard_tiket.php">
                <input type="text" name="search_query" placeholder="Cari event, konser, atau artis..." 
                       aria-label="Search events" value="" />
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
      </div>
      <div class="d-flex align-items-center me-3">
          <i class="fas fa-search text-white me-3" id="searchToggleDashboard" aria-label="Toggle search bar"></i>
          <div class="flag-container">
              <div class="flag"></div>
              <span class="flag-text">ID</span>
          </div>
      </div>
      <div class="d-flex align-items-center navbar-actions" id="navbarActions">
          </div>
    </div>
  </nav>

  <div class="main-content-wrapper">  <!-- Wrapper ini penting -->
      <section class="hero-banner">
        <div class="container">
          <img src="foto/banner.png" alt="Banner Harmonix" class="img-fluid rounded-4 w-100" onerror="this.style.display='none'">
        </div>
      </section>

      <section class="py-5 bg-white position-relative"> 
        <div class="container">
          <div class="section-header">
            <h2 class="fw-bold mb-0">Rekomendasi Event</h2> 
            <a href="dashboard_tiket.php" class="btn-lihat-lainnya">Lihat Lainnya <i class="fas fa-arrow-right ms-1"></i></a>
          </div>
        </div>

        <div class="position-relative" style="max-width: 1200px; margin: 0 auto;"> 
          <div class="swiper-container"> 
            <div class="swiper-wrapper mb-4">
              <?php if (!empty($eventsData)): ?>
                <?php foreach ($eventsData as $event): ?>
                  <?php
                    $date = new DateTime($event['event_date']);
                    $formattedDate = $date->format('d M Y');
                    $priceDisplay = "Segera Hadir";
                    if ($event['starting_price'] !== null && $event['starting_price'] > 0) {
                        $priceDisplay = "Rp " . number_format($event['starting_price'], 0, ',', '.');
                    } elseif ($event['starting_price'] !== null && $event['starting_price'] == 0) { 
                        $priceDisplay = "Gratis";
                    }
                    $imagePath = "foto/" . (!empty($event['image_url']) ? htmlspecialchars($event['image_url']) : "default_event_slider.png");
                  ?>
                  <div class="swiper-slide">
                    <a href="detail-event.php?event_id=<?php echo $event['event_id']; ?>" class="text-decoration-none text-dark">
                      <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($event['nama']); ?>" onerror="this.onerror=null; this.src='foto/default_event_slider.png';">
                      <div class="p-3"> 
                        <h6 class="fw-bold mb-1 text-truncate" title="<?php echo htmlspecialchars($event['nama']); ?>"><?php echo htmlspecialchars($event['nama']); ?></h6>
                        <small class="text-muted d-block"><?php echo $formattedDate; ?></small>
                        <small class="text-muted d-block mb-2 text-truncate" title="<?php echo htmlspecialchars($event['lokasi']); ?>"><?php echo htmlspecialchars($event['lokasi']); ?></small>
                        <div class="d-flex align-items-center justify-content-between">
                          <p class="text-danger fw-bold mb-0" style="font-size: 1rem;"><?php echo $priceDisplay; ?></p>
                          <span class="btn btn-sm btn-buy-event">Beli Tiket</span>
                        </div>
                      </div>
                    </a>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="swiper-slide">
                    <div class="p-3 text-center text-muted" style="height: 280px; display:flex; align-items:center; justify-content:center;">
                        Belum ada event rekomendasi saat ini.
                    </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php if (count($eventsData) > 3): ?>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
          <?php endif; ?>
        </div>
      </section>
  </div>
  
  <footer class="py-4 border-top bg-light"> <!-- Hapus mt-5 jika ada, biarkan flexbox yang atur -->
    <div class="container text-center">
        <p class="mb-0 text-muted small">© <?php echo date("Y"); ?> Harmonix. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  
  <script>
    const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
    const userName = <?php echo json_encode($_SESSION['user_name'] ?? 'Pengguna'); ?>;
    const userProfilePic = <?php echo json_encode($_SESSION['user_profile_pic'] ?? 'foto/default_avatar.png'); ?>;
  </script>
  <script src="js/init.js?v=<?php echo time(); ?>"></script> 
  
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (document.querySelector('.swiper-container')) {
        var swiper = new Swiper('.swiper-container', {
          slidesPerView: 1, 
          spaceBetween: 25, 
          loop: <?php echo count($eventsData) > 4 ? 'true' : 'false'; ?>,
          navigation: { 
            nextEl: '.swiper-button-next', 
            prevEl: '.swiper-button-prev', 
          },
          breakpoints: { 
            576: { slidesPerView: 1.5, spaceBetween: 20 }, 
            768: { slidesPerView: 2.5, spaceBetween: 25 }, 
            992: { slidesPerView: 3.5, spaceBetween: 30 }, 
            1200: { slidesPerView: 4, spaceBetween: 30 } 
          }
        });
      }

      document.querySelectorAll('.swiper-slide a[href^="detail-event.php"]').forEach(link => {
        link.addEventListener('click', function (event) {
          if (typeof window.requireLoginGlobal === 'function') { 
            window.requireLoginGlobal(() => {
              // Navigasi lanjut
            });
            if (!(typeof isLoggedIn !== 'undefined' && isLoggedIn)) {
                 event.preventDefault(); 
            }
          }
        });
      });

      let searchToggleDashboard = document.getElementById('searchToggleDashboard');
      const navLinksDashboard = document.getElementById('navLinksDashboard');
      const searchFormWrapperDashboard = document.getElementById('searchFormWrapperDashboard'); 
      const searchInputDashboard = searchFormWrapperDashboard.querySelector('input[name="search_query"]');

      if (searchToggleDashboard && navLinksDashboard && searchFormWrapperDashboard) {
          let isToggling = false;
          searchToggleDashboard.addEventListener('click', function() {
            if (isToggling) return;
            isToggling = true;
            
            navLinksDashboard.classList.toggle('hidden');
            searchFormWrapperDashboard.classList.toggle('active'); 

            const isSearchVisible = searchFormWrapperDashboard.classList.contains('active');
            this.setAttribute('aria-expanded', isSearchVisible);

            if (isSearchVisible) {
              searchInputDashboard.focus();
            }
            setTimeout(() => { isToggling = false; }, 300);
          });

          searchInputDashboard.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
              event.preventDefault();
              searchFormWrapperDashboard.querySelector('form').submit(); 
            }
          });
      }
    });
  </script>
</body>
</html>
<?php
if(isset($conn)) { $conn->close(); } 
?>
