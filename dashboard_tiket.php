<?php
session_start(); // Memulai session untuk penggunaan variabel session
require_once 'db.php'; // Menyertakan file koneksi database Anda
$eventsPerPage = 16;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($currentPage - 1) * $eventsPerPage;
// --- END: Pengaturan Pagination ---

// --- START: Mengambil Data Event & Total Event untuk Pagination ---
// Query untuk mengambil total event (yang relevan, misal 'upcoming')
$totalEventsQuery = "SELECT COUNT(DISTINCT e.event_id) AS total 
                     FROM events e 
                     LEFT JOIN ticket_types tt ON e.event_id = tt.event_id";
// Tambahkan WHERE clause jika perlu, misal: WHERE e.status = 'upcoming'";
$totalEventsResult = $conn->query($totalEventsQuery);
$totalEvents = $totalEventsResult->fetch_assoc()['total'];
$totalPages = ceil($totalEvents / $eventsPerPage);

$sql = "SELECT 
            e.event_id, 
            e.nama, 
            e.deskripsi, 
            e.event_date, 
            e.lokasi, 
            e.image_url, 
            MIN(tt.price) AS starting_price 
        FROM 
            events e 
        LEFT JOIN 
            ticket_types tt ON e.event_id = tt.event_id 
        -- Tambahkan WHERE clause jika perlu, misal: WHERE e.status = 'upcoming'
        GROUP BY 
            e.event_id 
        ORDER BY 
            e.event_date ASC 
        LIMIT {$eventsPerPage} OFFSET {$offset}";

$result = $conn->query($sql);
$eventsData = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $eventsData[] = $row;
  }
}
// --- END: Mengambil Data Event ---
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <link href="foto/logoputih.png" rel="icon">
  <title>Harmonix - Jelajah Event</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
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


    .navbar a {
      color: white;
      text-decoration: none;
      margin: 0 10px;
    }

    .navbar a:hover {
      text-decoration: underline;
    }

    .navbar-brand {
      color: white !important;
      /* Ditambahkan !important agar selalu putih */
      font-weight: bold;
    }

    .filter-section {
      display: flex;
      align-items: center;
      padding: 10px 0;
      flex-wrap: wrap;
      /* Agar filter responsif */
    }

    .filter-section h2 {
      margin: 0 20px 10px 0;
      /* Margin bawah untuk wrap */
      font-size: 24px;
    }

    .filter-section select,
    .filter-section .btn-filter,
    .filter-section .btn-reset {
      margin-right: 10px;
      margin-bottom: 10px;
      /* Margin bawah untuk wrap */
      padding: 5px 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 14px;
      /* Menyamakan ukuran font */
    }

    .filter-section .btn-filter,
    .filter-section .btn-reset {
      background-color: #f8f9fa;
    }

    .filter-section select {
      padding: 6px 10px;
      /* Sedikit padding agar tinggi sama dengan button */
    }

    .list:hover {
      transform: translateY(-5px);
    }
    .list {
      transition: transform 0.3s ease;
      /* Transisi halus saat hover */
    }

    .event-card {
      border-radius: 8px;
      /* height: 100%;
      display: flex; 
      flex-direction: column; sudah ada dan biarkan */
    }

    .event-card img {
      width: 100%;
      height: 180px;
      /* DIUBAH: Naikkan tinggi gambar secara signifikan. Coba antara 200px - 230px. */
      object-fit: cover;
    }

    .event-card .card-body {
      padding: 10px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .event-card .card-title {
      font-size: 0.9rem;
      font-weight: bold;
      line-height: 1.3;
      /* Menaikkan sedikit line-height untuk keterbacaan jika font jadi 2 baris */
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      min-height: calc(1.3em * 2);
      /* Pastikan ada ruang untuk 2 baris judul */
    }

    .event-card .card-text {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 1.3;
      /* Tambahkan line-height untuk konsistensi */
    }

    .event-card .card-text.location {
      white-space: normal;
    }

    .event-card .price {
      color: #DC2626;
      font-weight: bold;
      font-size: 0.9rem;
    }

    .event-card .btn-buy {
      background-color: #1E3A8A;
      color: white;
            border-radius: 30px; 

      padding: 6px 20px;
          font-size: 0.875rem; 
      border: none;
    }

    .event-card .card-footer {
      margin-top: auto;
      padding-top: 8px;
      /* Sedikit menambah padding atas jika konten teks lebih tinggi */
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

    /* Pagination Styles */
    .pagination-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 30px 0;
    }

    .pagination {
      display: flex;
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .pagination li {
      margin: 0 5px;
    }

    .pagination li a {
      text-decoration: none;
      color: #1E3A8A;
      padding: 8px 12px;
      border: 1px solid #1E3A8A;
      border-radius: 5px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .pagination li a:hover,
    .pagination li.active a {
      background-color: #1E3A8A;
      color: white;
      border-color: #1E3A8A;
    }

    .pagination li.disabled a {
      color: #6c757d;
      border-color: #ccc;
      cursor: not-allowed;
    }

    .pagination li.disabled a:hover {
      background-color: transparent;
      color: #6c757d;
    }

    .no-events {
      text-align: center;
      padding: 40px 20px;
      font-size: 1.2rem;
      color: #6c757d;
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
        <div class="nav-links d-none d-lg-flex" id="navLinksDashboard">
          <a href="dashboard_tiket.php">Jelajah</a>
          <a href="tambah_event.php">Event Creator</a>
          <a href="#">Hubungi Kami</a>
        </div>
        <div class="search-container" id="searchContainerDashboard">
          <input type="text" placeholder="Cari event, konser, atau artis..." aria-label="Search events" />
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

  <div class="container filter-section mt-4">
    <h2>Jelajah</h2>
    <select name="lokasi" aria-label="Filter by location">
      <option value="">Semua Lokasi</option>
    </select>
    <select name="waktu" aria-label="Filter by time">
      <option value="">Semua Waktu</option>
    </select>
    <select name="harga" aria-label="Filter by price">
      <option value="">Semua Harga</option>
    </select>
    <select name="kategori" aria-label="Filter by category">
      <option value="">Semua Kategori</option>
    </select>
    <button class="btn-filter">
      <i class="fas fa-filter me-1"></i> Filter
    </button>
    <button class="btn-reset">
      <i class="fas fa-sync-alt me-1"></i> Reset
    </button>
  </div>

  <div class="container mt-2">
    <?php if (!empty($eventsData)): ?>
      <div class="row g-3">
        <?php foreach ($eventsData as $event): ?>
          <?php
          // Format tanggal
          $date = new DateTime($event['event_date']); // Kolom `event_date` dari tabel `events`
          $formattedDate = $date->format('d M Y');

          // Format harga
          $priceDisplay = "Info harga hubungi penyelenggara";
          if ($event['starting_price'] !== null) {
            $priceDisplay = "Harga mulai Rp " . number_format($event['starting_price'], 0, ',', '.'); // Kolom `price` dari tabel `ticket_types`
          }

          // Path gambar default jika image_url kosong atau null
          $imagePath = "foto/" . (!empty($event['image_url']) ? htmlspecialchars($event['image_url']) : "default_event_image.png"); // Kolom `image_url` dari tabel `events`
          // Pastikan Anda memiliki gambar default_event_image.png di folder foto_event/
          ?>
          <div class="list col-xl-3 col-lg-3 col-md-4 col-sm-6 mb-4 ">
            <div class="event-card shadow rounded">
              <img src="<?php echo $imagePath; ?>" class=" p-3" alt="<?php echo htmlspecialchars($event['nama']); ?>">
              <div class="card-body">
                <div>
                  <h5 class="card-title text-truncate" title="<?php echo htmlspecialchars($event['nama']); ?>">
                    <?php echo htmlspecialchars($event['nama']); ?>
                  </h5>
                  <p class="card-text"><i class="fas fa-calendar-alt me-1"></i> <?php echo $formattedDate; ?></p>
                  <p class="card-text location" title="<?php echo htmlspecialchars($event['lokasi']); ?>"><i
                      class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['lokasi']); ?></p>
                </div>
                <div class="card-footer bg-transparent border-0 p-0">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="price"><?php echo $priceDisplay; ?></span>
                    <a class="btn btn-buy" href="detail-event.php?event_id=<?php echo $event['event_id']; ?>">Beli</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="row">   
        <div class="col">
          <p class="no-events">Belum ada event yang tersedia saat ini. Silakan cek kembali nanti.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination-container">
      <ul class="pagination">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
          <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">Previous</a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
          <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Next</a>
        </li>
      </ul>
    </div>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
    const userName = <?php echo json_encode($_SESSION['user_name'] ?? 'Pengguna'); ?>;
    const userProfilePic = <?php echo json_encode($_SESSION['user_profile_pic'] ?? 'foto/default_avatar.png'); ?>;
  </script>

  <script src="js/init.js?v=<?php echo time(); ?>"></script> 
  <script>
    // Search bar toggle
    let isToggling = false;
    document.getElementById('searchToggle').addEventListener('click', function () {
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

    // Catatan untuk pagination JavaScript yang lama:
    // Fungsi changePage() yang ada sebelumnya di file HTML Anda tidak lagi diperlukan
    // karena pagination kini dikelola oleh PHP (server-side).
    // Jika Anda ingin tetap menggunakan pagination client-side dengan data yang sudah
    // ter-load semua, Anda perlu memodifikasi script tersebut agar sesuai dengan
    // struktur card event yang digenerate oleh PHP. Namun, untuk konsistensi
    // dengan pengambilan data per halaman dari database, pagination server-side lebih disarankan.
  </script>

</body>

</html>
<?php
// Tutup koneksi database
if (isset($conn)) {
  $conn->close();
}
?>
