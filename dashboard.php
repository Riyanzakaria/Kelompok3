<?php
session_start(); // Memulai session untuk penggunaan variabel session (misal, untuk status login)
require_once 'db.php'; // Menyertakan file koneksi database Anda

// Mengambil data untuk Rekomendasi Event
$eventsData = [];
$limitRecommendations = 8; // Jumlah event yang ingin ditampilkan di slider

// Query untuk mengambil data event rekomendasi (misalnya, upcoming event dengan harga terendah)
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
                        e.status = 'upcoming' -- Hanya tampilkan event yang akan datang
                    GROUP BY
                        e.event_id
                    ORDER BY
                        e.event_date ASC -- Urutkan berdasarkan tanggal terdekat
                    LIMIT ?"; // Batasi jumlah event

$stmt = $conn->prepare($sqlRecommendations);
$stmt->bind_param("i", $limitRecommendations);
$stmt->execute();
$resultRecommendations = $stmt->get_result();

if ($resultRecommendations && $resultRecommendations->num_rows > 0) {
    while ($row = $resultRecommendations->fetch_assoc()) {
        $eventsData[] = $row;
    }
}
$stmt->close();
// Jangan tutup koneksi $conn di sini jika masih ada bagian lain dari halaman yang mungkin membutuhkannya.
// Koneksi akan otomatis tertutup saat skrip PHP selesai.
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <link href="foto/logoputih.png" rel="icon">
  <title>Harmonix - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://unpkg.com/swiper/swiper-bundle.min.css" rel="stylesheet" />
  <style>
    body {
      /* font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; */
      /* Menggunakan font family dari dashboard_tiket.php untuk konsistensi jika diinginkan */
      font-family: 'Inter', sans-serif; /* Contoh jika Anda punya font Inter */
      background-color: #f9fafb; /* Sebelumnya #f9fafb di file ini, bg-white dari body class */
    }

    .navbar {
      background-color: #1E3A8A;
    }

    .navbar a,
    .navbar .btn-link {
      color: white;
      text-decoration: none;
      margin: 0 10px;
    }

    .navbar a:hover,
    .navbar .btn-link:hover {
      text-decoration: underline;
    }

    .navbar .btn-link {
      padding: 0.375rem 0.75rem;
      border: none;
      background: none;
      font-weight: normal;
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

    .swiper-slide .btn-buy-event, 
    .btn-lihat-lainnya { 
      background-color: #1E3A8A;
      color: white;
      border-radius: 30px; 
      border: none;
      padding: 0.375rem 1rem; 
      font-size: 0.875rem; 
      text-decoration: none; 
      display: inline-block; 
    }
    .btn-lihat-lainnya:hover {
        background-color: #162b65; 
        color: white; 
    }

    .swiper-container {
      max-width: 1000px; 
      margin: 0 auto;
      overflow: hidden; 
      position: relative;
    }

    .swiper-wrapper {
      /* padding: 0 20px; Dihapus jika navigasi button ada di luar dan butuh space */
    }

    .swiper-slide {
      margin-right: 25px; /* Jarak antar slide di Swiper */
      /* Style berikut dipindahkan dari CSS inline di HTML ke sini untuk konsistensi */
      background-color: white; 
      border-radius: .5rem; /* 8px */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
      overflow: hidden; 
      /* max-height: 400px; Dihapus agar tinggi menyesuaikan konten atau diatur oleh Swiper */
      width: auto; /* Agar lebar slide bisa diatur oleh slidesPerView Swiper */
    }
     .swiper-slide img { 
      width: 100%;
      height: 180px; 
      object-fit: cover;
    }


    .swiper-button-next,
    .swiper-button-prev {
      color: #1E3A8A; /* Warna ikon panah */
      width: 38px;  /* Ukuran tombol */
      height: 38px; /* Ukuran tombol */
      background-color: #fff;
      border-radius: 50%;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      top: calc(180px / 2); /* Posisi vertikal di tengah gambar (tinggi gambar 180px) */
      transform: translateY(-50%); 
      position: absolute;
      z-index: 10;
    }
    .swiper-button-prev::after, .swiper-button-next::after {
        font-size: 1rem; /* Ukuran ikon panah di dalam tombol */
    }

    .swiper-button-prev {
      left: -15px; /* Posisi tombol prev, sedikit keluar dari container */
    }

    .swiper-button-next {
      right: -15px; /* Posisi tombol next, sedikit keluar dari container */
    }

    .navbar-brand {
      color: white !important;
      font-weight: bold;
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
    
    /* CSS untuk search icon dan profile icon di navbar-actions */
    .navbar-actions .fa-search {
      cursor: pointer;
      transition: transform 0.3s ease;
      color: white;
      /* margin-right: 15px; Dihapus jika init.js mengatur margin/spacing */
    }
    .navbar-actions .fa-search:hover {
      transform: scale(1.1);
    }
    .profile-icon { /* Ditambahkan dari init.js untuk konsistensi */
      width: 32px; /* Sesuai dengan init.js */
      height: 32px; /* Sesuai dengan init.js */
      border-radius: 50%;
      object-fit: cover;
      cursor: pointer;
      /* border: 1px solid rgba(255, 255, 255, 0.5); Opsional */
    }
    .btn-daftar-sekarang { /* Untuk tombol masuk di init.js */
      color: white;
      font-weight: 500;
      text-decoration: none;
      padding: 0.375rem 0.75rem;
    }
    .btn-daftar-sekarang:hover {
      text-decoration: underline;
      color: #f0f0f0;
    }
    /* --- END CSS Tambahan --- */

    .section-header { 
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem; 
    }
  </style>
</head>

<body class="bg-white" style="overflow-x: hidden;">

  <nav class="navbar navbar-expand-lg">
    <div class="container d-flex align-items-center py-3">
      <div class="d-flex align-items-center">
        <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">Harmonix</a>
      </div>
      <div class="navbar-center">
        <div class="nav-links d-none d-lg-flex" id="navLinks">
          <a href="dashboard_tiket.php">Jelajah</a>
          <a href="tambah_event.php">Event Creator</a>
          <a href="#">Hubungi Kami</a>
        </div>
        <div class="search-container" id="searchContainer">
          <input type="text" placeholder="Cari..." aria-label="Search events" />
        </div>
      </div>
      <div class="d-flex align-items-center navbar-actions" id="navbarActions">
          </div>
    </div>
  </nav>

  <section class="hero-banner">
    <div class="container">
      <img src="foto/banner.png" alt="Banner Harmonix" class="img-fluid rounded-4 w-100">
    </div>
  </section>

  <section class="py-5 bg-white position-relative"> 
    <div class="container">
      <div class="section-header">
        <h2 class="fw-bold mb-0">Rekomendasi Event</h2> 
        <a href="dashboard_tiket.php" class="btn-lihat-lainnya">Lihat Lainnya</a>
      </div>
    </div>

    <div class="position-relative" style="max-width: 1200px; margin: 0 auto;"> 
      <div class="swiper-container"> 
        <div class="swiper-wrapper mb-4">
          
          <?php if (!empty($eventsData)): ?>
            <?php foreach ($eventsData as $event): ?>
              <?php
                // Format tanggal
                $date = new DateTime($event['event_date']);
                // Menggunakan format 'd M Y' (contoh: 28 Mei 2025)
                // Anda bisa menyesuaikan formatnya jika event berlangsung beberapa hari
                // Untuk saat ini, kita anggap event_date adalah tanggal mulai dan event satu hari.
                $formattedDate = $date->format('d M Y');
                // Jika Anda ingin format seperti "05 – 06 Jul 2025" dan memiliki tanggal akhir di DB:
                // $endDate = new DateTime($event['event_end_date']);
                // if ($date->format('m Y') == $endDate->format('m Y')) {
                //   $formattedDate = $date->format('d') . ' – ' . $endDate->format('d M Y');
                // } else {
                //   $formattedDate = $date->format('d M Y') . ' – ' . $endDate->format('d M Y');
                // }


                // Format harga
                $priceDisplay = "Segera Hadir"; // Default jika tidak ada harga
                if ($event['starting_price'] !== null && $event['starting_price'] > 0) {
                    $priceDisplay = "Rp " . number_format($event['starting_price'], 0, ',', '.');
                } elseif ($event['starting_price'] == 0) {
                    $priceDisplay = "Gratis";
                }
                
                // Path gambar
                $imagePath = "foto/" . (!empty($event['image_url']) ? htmlspecialchars($event['image_url']) : "default_event_slider.png");
                // Pastikan Anda memiliki gambar default_event_slider.png di folder foto_event/
              ?>
              <div class="swiper-slide">
                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($event['nama']); ?>">
                <div class="p-3"> 
                  <h6 class="fw-bold mb-1 text-truncate" title="<?php echo htmlspecialchars($event['nama']); ?>"><?php echo htmlspecialchars($event['nama']); ?></h6>
                  <small class="text-muted d-block"><?php echo $formattedDate; ?></small>
                  <small class="text-muted d-block mb-2 text-truncate" title="<?php echo htmlspecialchars($event['lokasi']); ?>"><?php echo htmlspecialchars($event['lokasi']); ?></small>
                  <div class="d-flex align-items-center justify-content-between">
                    <p class="text-danger fw-bold mb-0" style="font-size: 1rem;"><?php echo $priceDisplay; ?></p>
                    <a class="btn btn-sm btn-buy-event" href="detail-event.php?event_id=<?php echo $event['event_id']; ?>">Beli Tiket</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="swiper-slide">
                <div class="p-3 text-center">
                    <p class="text-muted">Belum ada event rekomendasi saat ini.</p>
                </div>
            </div>
          <?php endif; ?>
          
        </div>
      </div>

      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
  </section>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  <script src="js/init.js"></script> 
  <script>
    // Inisialisasi Swiper
    var swiper = new Swiper('.swiper-container', {
      loop: true, // Nonaktifkan loop jika tidak diperlukan
      slidesPerView: 1, 
      spaceBetween: 25, // Sesuaikan spaceBetween dengan margin-right pada .swiper-slide
      // loop: true, // Loop bisa diaktifkan jika ada cukup banyak slide
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

    // Fungsi requireLogin (jika digunakan oleh tombol "Beli Tiket")
    // Anda perlu mendefinisikan fungsi ini atau memastikan init.js menanganinya.
    // Contoh sederhana:
    /*
    function requireLogin() {
        // Ganti dengan logika cek login Anda yang sebenarnya (misalnya dari session PHP via JS variable)
        const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
        if (!isLoggedIn) {
            alert("Anda harus login terlebih dahulu untuk membeli tiket.");
            // window.location.href = 'login.php'; // Arahkan ke halaman login
            return false;
        }
        return true;
    }
    */

    // Event listener untuk tombol "Beli Tiket" di Swiper
    // (Sudah ada di dashboard.php yang Anda berikan, pastikan requireLogin terdefinisi jika digunakan)
    /*
    document.querySelectorAll('.btn-buy-event').forEach(button => {
      button.addEventListener('click', function (event) {
        // Pastikan requireLogin() terdefinisi global atau di scope ini
        // if (typeof requireLogin === 'function' && !requireLogin()) {
        //   event.preventDefault();
        // }
        // Atau, jika Anda menggunakan variabel session langsung dari PHP di atas:
        const isLoggedInForButton = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
        if (!isLoggedInForButton) {
            event.preventDefault();
            alert("Anda harus login terlebih dahulu untuk membeli tiket.");
        }
      });
    });
    */

    // JavaScript untuk search toggle (sudah ada di file dashboard.php Anda)
    // Pastikan ID elemennya benar dan tidak ada duplikasi jika init.js juga menanganinya
    let searchToggle = document.getElementById('searchToggle');
    if (searchToggle) {
        let isToggling = false;
        searchToggle.addEventListener('click', function() {
          if (isToggling) return;
          isToggling = true;

          const navLinks = document.getElementById('navLinks');
          const searchContainer = document.getElementById('searchContainer');
          
          if (navLinks && searchContainer) {
            navLinks.classList.toggle('hidden');
            searchContainer.classList.toggle('active');

            const isSearchVisible = searchContainer.classList.contains('active');
            this.setAttribute('aria-expanded', isSearchVisible);
            searchContainer.setAttribute('aria-hidden', !isSearchVisible);

            if (isSearchVisible) {
              searchContainer.querySelector('input').focus();
            }
          }
          setTimeout(() => { isToggling = false; }, 300);
        });
    }
  </script>
</body>
</html>
