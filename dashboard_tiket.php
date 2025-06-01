<?php
session_start();
require_once 'db.php';

// --- Inisialisasi Variabel Filter dan Pencarian ---
$searchQuery = "";
$filterLokasi = "";
$filterWaktu = "";

$base_url_params = []; // Untuk pagination dan menjaga state filter/search

// --- START: Logika Pencarian ---
if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
    $searchQuery = trim($_GET['search_query']);
    $base_url_params['search_query'] = $searchQuery;
}
// --- END: Logika Pencarian ---

// --- START: Logika Filter ---
if (isset($_GET['filter_lokasi']) && !empty(trim($_GET['filter_lokasi']))) {
    $filterLokasi = trim($_GET['filter_lokasi']);
    $base_url_params['filter_lokasi'] = $filterLokasi;
}

if (isset($_GET['filter_waktu']) && !empty($_GET['filter_waktu'])) {
    $filterWaktu = $_GET['filter_waktu'];
    $base_url_params['filter_waktu'] = $filterWaktu;
}
// --- END: Logika Filter ---


// --- Membangun Kondisi SQL ---
$conditions = [];
$params = [];
$types = "";

// Kondisi untuk Search Query
if (!empty($searchQuery)) {
    $searchTerm = "%" . $searchQuery . "%";
    $conditions[] = "(e.nama LIKE ? OR e.deskripsi LIKE ? OR e.lokasi LIKE ?)";
    $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
    $types .= "sss";
}

// Kondisi untuk Filter Lokasi
if (!empty($filterLokasi)) {
    $lokasiTerm = "%" . $filterLokasi . "%";
    $conditions[] = "e.lokasi LIKE ?";
    $params[] = $lokasiTerm;
    $types .= "s";
}

// Kondisi untuk Filter Waktu
if (!empty($filterWaktu)) {
    $today = date('Y-m-d');
    switch ($filterWaktu) {
        case 'minggu_ini':
            $startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($today)));
            $endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($today)));
            $conditions[] = "(e.event_date BETWEEN ? AND ?)";
            $params[] = $startOfWeek; $params[] = $endOfWeek;
            $types .= "ss";
            break;
        case 'minggu_depan':
            $startOfNextWeek = date('Y-m-d', strtotime('monday next week', strtotime($today)));
            $endOfNextWeek = date('Y-m-d', strtotime('sunday next week', strtotime($today)));
            $conditions[] = "(e.event_date BETWEEN ? AND ?)";
            $params[] = $startOfNextWeek; $params[] = $endOfNextWeek;
            $types .= "ss";
            break;
        case 'bulan_ini':
            $startOfMonth = date('Y-m-01', strtotime($today));
            $endOfMonth = date('Y-m-t', strtotime($today));
            $conditions[] = "(e.event_date BETWEEN ? AND ?)";
            $params[] = $startOfMonth; $params[] = $endOfMonth;
            $types .= "ss";
            break;
        case 'bulan_depan':
            $startOfNextMonth = date('Y-m-01', strtotime('+1 month', strtotime($today)));
            $endOfNextMonth = date('Y-m-t', strtotime($startOfNextMonth));
            $conditions[] = "(e.event_date BETWEEN ? AND ?)";
            $params[] = $startOfNextMonth; $params[] = $endOfNextMonth;
            $types .= "ss";
            break;
    }
}

$sqlConditions = "";
if (!empty($conditions)) {
    $sqlConditions = " WHERE " . implode(" AND ", $conditions);
}


// --- Pagination ---
$eventsPerPage = 16;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $eventsPerPage;

// --- Query Total Event (dengan filter) ---
$totalEventsQuery = "SELECT COUNT(DISTINCT e.event_id) AS total 
                     FROM events e 
                     LEFT JOIN ticket_types tt ON e.event_id = tt.event_id 
                     {$sqlConditions}";

$stmtTotal = $conn->prepare($totalEventsQuery);
if ($stmtTotal) {
    if (!empty($params)) {
        $stmtTotal->bind_param($types, ...$params);
    }
    $stmtTotal->execute();
    $totalEventsResult = $stmtTotal->get_result();
    $totalEvents = $totalEventsResult->fetch_assoc()['total'];
    $stmtTotal->close();
} else {
    $totalEvents = 0;
    error_log("Failed to prepare statement for total events: " . $conn->error);
}
$totalPages = ceil($totalEvents / $eventsPerPage);

// --- Query Data Event (dengan filter dan pagination) ---
$sql = "SELECT 
            e.event_id, e.nama, e.deskripsi, e.event_date, e.lokasi, e.image_url, 
            MIN(tt.price) AS starting_price 
        FROM events e 
        LEFT JOIN ticket_types tt ON e.event_id = tt.event_id 
        {$sqlConditions}
        GROUP BY e.event_id 
        ORDER BY e.event_date ASC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$eventsData = [];
if ($stmt) {
    $currentParamsForData = $params; // Mulai dengan parameter filter/search
    $currentTypesForData = $types;

    $currentParamsForData[] = $eventsPerPage;
    $currentTypesForData .= 'i';
    $currentParamsForData[] = $offset;
    $currentTypesForData .= 'i';

    if (!empty($currentTypesForData)){ // Hanya bind jika ada parameter
        $stmt->bind_param($currentTypesForData, ...$currentParamsForData);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $eventsData[] = $row;
        }
    }
    $stmt->close();
} else {
    error_log("Failed to prepare statement for events data: " . $conn->error);
}
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
    /* === START: STICKY FOOTER CSS === */
    html, body {
      height: 100%;
      margin: 0;
    }
    body { 
      font-family: 'Inter', sans-serif; 
      background-color: #f9fafb; /* Anda pakai bg-white di tag body, sesuaikan jika perlu */
      display: flex;
      flex-direction: column;
      overflow-x: hidden; /* Dipindahkan dari tag body */
    }
    .main-content-area-tiket { /* Wrapper baru untuk konten utama */
      flex: 1 0 auto;
    }
    footer {
      flex-shrink: 0;
      /* Hapus mt-5 agar tidak ada margin paksa dari atas */
    }
    /* === END: STICKY FOOTER CSS === */

    .navbar { background-color: #1E3A8A; }
    .navbar a { color: white; text-decoration: none; margin: 0 10px; }
    .navbar a:hover { text-decoration: underline; }
    .navbar-brand { color: white !important; font-weight: bold; }
    
    .filter-section-form { display: flex; align-items: center; padding: 10px 0; flex-wrap: wrap; }
    .filter-section-form h2 { margin: 0 20px 10px 0; font-size: 24px; }
    .filter-section-form .form-control-filter, 
    .filter-section-form .form-select-filter, 
    .filter-section-form .btn {
      margin-right: 10px; margin-bottom: 10px; padding: 0.375rem 0.75rem; /* Bootstrap's default padding */
      border-radius: 5px; border: 1px solid #ccc; font-size: 14px;
      height: 38px; /* Samakan tinggi */
    }
    .filter-section-form .form-control-filter { width: auto; min-width: 150px; }
    .filter-section-form .btn-filter, .filter-section-form .btn-reset { background-color: #f8f9fa; }
    
    .list:hover { transform: translateY(-5px); }
    .list { transition: transform 0.3s ease; }
    .event-card { border-radius: 8px; height:100%; display:flex; flex-direction:column; }
    .event-card img { width: 100%; height: 180px; object-fit: cover; }
    .event-card .card-body { padding: 10px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .event-card .card-title { 
        font-size: 0.9rem; font-weight: bold; line-height: 1.3; 
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; text-overflow: ellipsis; min-height: calc(1.3em * 2);
    }
    .event-card .card-text { 
        font-size: 0.75rem; color: #6c757d; margin-bottom: 2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;
    }
    .event-card .card-text.location { white-space: normal; }
    .event-card .price { color: #DC2626; font-weight: bold; font-size: 0.9rem; }
    .event-card .btn-buy { background-color: #1E3A8A; color: white; border-radius: 30px; padding: 6px 20px; font-size: 0.875rem; border: none; }
    .event-card .card-footer { margin-top: auto; padding-top: 8px; }
    
    .flag-container { display: flex; align-items: center; margin-right: 10px; }
    .flag { width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(to bottom, red 50%, white 50%); margin-right: 5px; }
    .flag-text { color: white; font-size: 14px; }
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
    #searchToggleJelajah { cursor: pointer; } 

    .pagination-container { display: flex; justify-content: center; align-items: center; margin: 30px 0; }
    .pagination { display: flex; list-style: none; padding: 0; margin: 0; }
    .pagination li { margin: 0 5px; }
    .pagination li a { text-decoration: none; color: #1E3A8A; padding: 8px 12px; border: 1px solid #1E3A8A; border-radius: 5px; font-size: 14px; transition: all 0.3s ease; }
    .pagination li a:hover, .pagination li.active a { background-color: #1E3A8A; color: white; border-color: #1E3A8A; }
    .pagination li.disabled a { color: #6c757d; border-color: #ccc; cursor: not-allowed; }
    .pagination li.disabled a:hover { background-color: transparent; color: #6c757d; }
    .no-events { text-align: center; padding: 40px 20px; font-size: 1.2rem; color: #6c757d; }
  </style>
</head>
<body class="bg-white"> <!-- style="overflow-x: hidden;" sudah dipindah ke CSS -->

  <nav class="navbar navbar-expand-lg">
    <div class="container d-flex align-items-center py-3">
      <div class="d-flex align-items-center">
        <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">Harmonix</a>
      </div>
      <div class="navbar-center">
        <div class="nav-links d-none d-lg-flex" id="navLinksJelajah">
          <a href="dashboard_tiket.php">Jelajah</a>
          <a href="tambah_event.php">Event Creator</a>
          <a href="#">Hubungi Kami</a>
        </div>
        <div class="search-form-wrapper" id="searchFormWrapperJelajah">
            <form method="GET" action="dashboard_tiket.php">
                <input type="text" name="search_query" placeholder="Cari event, konser, atau artis..." 
                       aria-label="Search events" value="<?php echo htmlspecialchars($searchQuery); ?>" />
                <button type="submit"><i class="fas fa-search"></i></button>
                 <?php // Menyertakan filter yang sudah ada dalam form pencarian agar tidak hilang saat search
                    if (!empty($filterLokasi)) echo "<input type='hidden' name='filter_lokasi' value='".htmlspecialchars($filterLokasi)."' />";
                    if (!empty($filterWaktu)) echo "<input type='hidden' name='filter_waktu' value='".htmlspecialchars($filterWaktu)."' />";
                 ?>
            </form>
        </div>
      </div>
      <div class="d-flex align-items-center me-3">
          <i class="fas fa-search text-white me-3" id="searchToggleJelajah" aria-label="Toggle search bar"></i>
          <div class="flag-container">
              <div class="flag"></div>
              <span class="flag-text">ID</span>
          </div>
      </div>
      <div class="d-flex align-items-center navbar-actions" id="navbarActions">
          </div>
    </div>
  </nav>

  <div class="main-content-area-tiket"> <!-- WRAPPER BARU DIMULAI DI SINI -->
    <div class="container mt-4">
      <form method="GET" action="dashboard_tiket.php" class="filter-section-form">
          <h2><?php echo !empty($searchQuery) ? 'Hasil Pencarian untuk "' . htmlspecialchars($searchQuery) . '"' : 'Jelajah Event'; ?></h2>
          
          <input type="text" name="filter_lokasi" class="form-control-filter" placeholder="Masukkan Lokasi" 
                value="<?php echo htmlspecialchars($filterLokasi); ?>" aria-label="Filter by location">
          
          <select name="filter_waktu" class="form-select-filter" aria-label="Filter by time">
              <option value="">Semua Waktu</option>
              <option value="minggu_ini" <?php if ($filterWaktu == 'minggu_ini') echo 'selected'; ?>>Minggu Ini</option>
              <option value="minggu_depan" <?php if ($filterWaktu == 'minggu_depan') echo 'selected'; ?>>Minggu Depan</option>
              <option value="bulan_ini" <?php if ($filterWaktu == 'bulan_ini') echo 'selected'; ?>>Bulan Ini</option>
              <option value="bulan_depan" <?php if ($filterWaktu == 'bulan_depan') echo 'selected'; ?>>Bulan Depan</option>
          </select>
          
          <?php // Menyertakan search query dalam form filter agar tidak hilang saat filter
              if (!empty($searchQuery)) echo "<input type='hidden' name='search_query' value='".htmlspecialchars($searchQuery)."' />";
          ?>

          <button type="submit" class="btn btn-filter">
              <i class="fas fa-filter me-1"></i> Filter
          </button>
          <a href="dashboard_tiket.php" class="btn btn-reset"> <!-- Reset hanya menghapus filter, bukan search query -->
              <i class="fas fa-sync-alt me-1"></i> Reset Filter
          </a>
      </form>
    </div>


    <div class="container mt-2">
      <?php if (!empty($eventsData)): ?>
        <div class="row g-3">
          <?php foreach ($eventsData as $event): ?>
            <?php
            $date = new DateTime($event['event_date']); 
            $formattedDate = $date->format('d M Y');
            $priceDisplay = "Info harga hubungi penyelenggara";
            if ($event['starting_price'] !== null) {
              if ($event['starting_price'] == 0) {
                  $priceDisplay = "Gratis";
              } else {
                  $priceDisplay = "Harga mulai Rp " . number_format($event['starting_price'], 0, ',', '.'); 
              }
            }
            $imagePath = "foto/" . (!empty($event['image_url']) ? htmlspecialchars($event['image_url']) : "default_event_image.png"); 
            ?>
            <div class="list col-xl-3 col-lg-3 col-md-4 col-sm-6 mb-4 ">
              <div class="event-card shadow rounded">
                <img src="<?php echo $imagePath; ?>" class=" p-3" alt="<?php echo htmlspecialchars($event['nama']); ?>" onerror="this.onerror=null; this.src='foto/default_event_image.png';">
                <div class="card-body">
                  <div>
                    <h5 class="card-title" title="<?php echo htmlspecialchars($event['nama']); ?>">
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
            <p class="no-events">
              <?php 
                  if (!empty($searchQuery) || !empty($filterLokasi) || !empty($filterWaktu)) {
                      echo 'Tidak ada event yang cocok dengan kriteria pencarian/filter Anda.';
                  } else {
                      echo 'Belum ada event yang tersedia saat ini. Silakan cek kembali nanti.';
                  }
              ?>
            </p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="pagination-container">
        <ul class="pagination">
          <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($base_url_params, ['page' => $currentPage - 1])); ?>">Previous</a>
          </li>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
              <a class="page-link" href="?<?php echo http_build_query(array_merge($base_url_params, ['page' => $i])); ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($base_url_params, ['page' => $currentPage + 1])); ?>">Next</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  </div> <!-- WRAPPER BARU BERAKHIR DI SINI -->
  
  <footer class="py-4 border-top bg-light"> <!-- Hapus class 'mt-5' -->
    <div class="container text-center">
        <p class="mb-0 text-muted small">© <?php echo date("Y"); ?> Harmonix. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
    const userName = <?php echo json_encode($_SESSION['user_name'] ?? 'Pengguna'); ?>;
    const userProfilePic = <?php echo json_encode($_SESSION['user_profile_pic'] ?? 'foto/default_avatar.png'); ?>;
  </script>
  <script src="js/init.js?v=<?php echo time(); ?>"></script> 
  <script>
    document.addEventListener('DOMContentLoaded', function () {
        let searchToggleJelajah = document.getElementById('searchToggleJelajah');
        const navLinksJelajah = document.getElementById('navLinksJelajah');
        const searchFormWrapperJelajah = document.getElementById('searchFormWrapperJelajah'); 
        const searchInputJelajah = searchFormWrapperJelajah.querySelector('input[name="search_query"]');

        if (searchToggleJelajah && navLinksJelajah && searchFormWrapperJelajah) {
            let isToggling = false;
            searchToggleJelajah.addEventListener('click', function() {
                if (isToggling) return;
                isToggling = true;
                
                navLinksJelajah.classList.toggle('hidden');
                searchFormWrapperJelajah.classList.toggle('active'); 

                const isSearchVisible = searchFormWrapperJelajah.classList.contains('active');
                this.setAttribute('aria-expanded', isSearchVisible);

                if (isSearchVisible) {
                    searchInputJelajah.focus();
                }
                setTimeout(() => { isToggling = false; }, 300);
            });

            searchInputJelajah.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchFormWrapperJelajah.querySelector('form').submit(); 
                }
            });
        }
        // Jika ada search query saat halaman dimuat, tampilkan search bar
        if (searchFormWrapperJelajah && "<?php echo !empty($searchQuery) ? 'true' : 'false'; ?>" === 'true') {
            if(navLinksJelajah) navLinksJelajah.classList.add('hidden');
            searchFormWrapperJelajah.classList.add('active');
            if(searchInputJelajah) searchInputJelajah.focus(); // Fokus ke input jika ada query
        }


        // Optional: Submit filter form on Enter key in lokasi input
        const filterLokasiInput = document.querySelector('input[name="filter_lokasi"]');
        if (filterLokasiInput) {
            filterLokasiInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.closest('form').submit();
                }
            });
        }
    });
  </script>
</body>
</html>
<?php
if (isset($conn)) { $conn->close(); }
?>
