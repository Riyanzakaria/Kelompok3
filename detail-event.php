<?php
session_start();
require_once 'db.php'; // File koneksi database Anda

$event_id = null;
$eventData = null;
$ticketTypesData = [];
$organizerName = "Penyelenggara Event"; // Default
$organizerLogo = "foto/default_logo_penyelenggara.png"; // Default logo

if (isset($_GET['event_id']) && filter_var($_GET['event_id'], FILTER_VALIDATE_INT)) {
  $event_id = $_GET['event_id'];

  // Ambil data event utama
  $stmtEvent = $conn->prepare("SELECT nama, deskripsi, event_date, event_time, lokasi, image_url FROM events WHERE event_id = ?");
  $stmtEvent->bind_param("i", $event_id);
  $stmtEvent->execute();
  $resultEvent = $stmtEvent->get_result();
  if ($resultEvent->num_rows > 0) {
    $eventData = $resultEvent->fetch_assoc();
    // Untuk sementara, kita bisa gunakan nama event sebagai nama penyelenggara jika tidak ada data spesifik
    $organizerName = htmlspecialchars($eventData['nama']);
  } else {
    // Event tidak ditemukan, bisa redirect atau tampilkan pesan error
    // die("Event tidak ditemukan.");
  }
  $stmtEvent->close();

  // Ambil data jenis tiket untuk event ini
  if ($eventData) {
    $stmtTickets = $conn->prepare("SELECT ticket_type_id, name, price, stock, status FROM ticket_types WHERE event_id = ? ORDER BY price ASC");
    $stmtTickets->bind_param("i", $event_id);
    $stmtTickets->execute();
    $resultTickets = $stmtTickets->get_result();
    while ($row = $resultTickets->fetch_assoc()) {
      $ticketTypesData[] = $row;
    }
    $stmtTickets->close();
  }
} else {
  // event_id tidak valid atau tidak ada, bisa redirect atau tampilkan pesan error
  // die("ID Event tidak valid.");
}

// Fungsi untuk format tanggal (Anda bisa pindahkan ke file helper jika sering digunakan)
function formatEventDate($dateStr, $timeStr = null)
{
  if (!$dateStr)
    return "Tanggal belum ditentukan";
  $date = new DateTime($dateStr);
  $formatted = $date->format('d M Y'); // Contoh: 27 May 2025

  // Tambahkan waktu jika ada
  if ($timeStr) {
    try {
      $time = new DateTime($timeStr);
      // $formatted .= ' - ' . $time->format('H:i \W\I\B'); // Contoh: 19:30 WIB
    } catch (Exception $e) {

    }
  }
  return $formatted;
}

function formatEventTime($timeStr)
{
  if (!$timeStr)
    return "Waktu belum ditentukan";
  try {
    $time = new DateTime($timeStr);
    return $time->format('H:i \W\I\B');
  } catch (Exception $e) {
    return "Format waktu salah";
  }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="foto/logoputih.png" rel="icon">
  <title><?php echo $eventData ? htmlspecialchars($eventData['nama']) : 'Detail Event'; ?> - Harmonix</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <style>
    /* ... CSS Anda yang sudah ada ... */
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
      background-color: #f9fafb;
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

    /* Sebelumnya .detail-box { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); } */
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

    /* .navbar-actions .fa-search { cursor: pointer; transition: transform 0.3s ease; color: white; margin-right: 15px; } */
    /* .navbar-actions .fa-search:hover { transform: scale(1.1); } */
    /* .profile-icon-link { display: inline-block; } */
    .profile-icon {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
      cursor: pointer;
      /* border: 1px solid rgba(255, 255, 255, 0.5); */
    }

    .btn-daftar-sekarang {
      color: white;
      font-weight: 500;
      text-decoration: none;
      padding: 0.375rem 0.75rem;
    }

    .btn-daftar-sekarang:hover {
      text-decoration: underline;
      color: #f0f0f0;
    }

    .btn-beli-tiket {
      background-color: #1e3a8a;
      border: none;
    }
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
          <a href="tambah_event.php">Event Creator</a>
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
    <?php if ($eventData): ?>
      <div class="row align-items-start">
        <div class="col-lg-8">
          <small class="text-muted">Event</small>
          <h4 class="mb-3" id="detailEventName"><?php echo htmlspecialchars($eventData['nama']); ?></h4>
          <div class="mb-3">
            <img
              src="foto/<?php echo !empty($eventData['image_url']) ? htmlspecialchars($eventData['image_url']) : 'default_event_image.png'; ?>"
              class="image-landscape" id="detailEventImage" alt="<?php echo htmlspecialchars($eventData['nama']); ?>" />
          </div>
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
              <img src="<?php echo $organizerLogo; ?>" alt="Logo Penyelenggara" width="40" height="40"
                class="me-2 rounded-circle" />
              <span class="fw-semibold">Penyelenggara<br><strong><?php echo $organizerName; ?></strong></span>
            </div>
            <div class="text-end">
              <small class="d-block">Instagram</small>
              <a href="#" class="text-decoration-none text-dark fw-semibold">
                <i class="bi bi-instagram me-1"></i> balibarberexpo
              </a>
            </div>
          </div>

          <div class="mb-3">
            <button class="nav-button active" id="btnDeskripsi"> <i class="bi bi-info-circle me-1"></i> Deskripsi
            </button>
            <button class="nav-button inactive" id="btnTiket"> <i class="bi bi-ticket-perforated me-1"></i> Tiket
            </button>
          </div>
          <div id="contentArea">
            <div id="kontenDeskripsi">
              <h6>Deskripsi Event</h6>
              <p><?php echo nl2br(htmlspecialchars($eventData['deskripsi'] ?? 'Deskripsi event belum tersedia.')); ?></p>
            </div>
            <div id="kontenTiket" style="display: none;">
              <h6>Pilih Tiket</h6>
              <?php if (!empty($ticketTypesData)): ?>
                <?php foreach ($ticketTypesData as $ticket): ?>
                  <div class="card p-3 mb-3 border border-light shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <strong><?php echo htmlspecialchars($ticket['name']); ?></strong>
                      <span
                        class="badge <?php echo ($ticket['status'] == 'available' && $ticket['stock'] > 0 ? 'bg-light text-primary border border-primary' : 'bg-light text-secondary border border-secondary'); ?>">
                        <?php echo ($ticket['status'] == 'available' && $ticket['stock'] > 0 ? 'On Sale' : ($ticket['stock'] == 0 ? 'Habis Terjual' : 'Tidak Tersedia')); ?>
                      </span>
                    </div>
                    <hr class="my-2" />
                    <div class="mb-3">
                      <div class="text-muted small">Harga</div>
                      <div class="text-danger fw-bold">Rp <?php echo number_format($ticket['price'], 0, ',', '.'); ?></div>
                    </div>
                    <?php if ($ticket['status'] == 'available' && $ticket['stock'] > 0): ?>
                      <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                          <button class="btn btn-light border rounded-3 me-2 btn-kurang"
                            data-target="jumlahTiket_<?php echo $ticket['ticket_type_id']; ?>">−</button>
                          <input type="text" id="jumlahTiket_<?php echo $ticket['ticket_type_id']; ?>"
                            class="form-control text-center" value="1" style="width: 50px;" readonly />
                          <button class="btn btn-light border rounded-3 ms-2 btn-tambah"
                            data-target="jumlahTiket_<?php echo $ticket['ticket_type_id']; ?>">+</button>
                        </div>
                        <div class="d-flex align-items-center">
                          <button class="btn btn-primary rounded-3 px-4 btn-beli-tiket"
                            data-ticket-id="<?php echo $ticket['ticket_type_id']; ?>"
                            data-ticket-name="<?php echo htmlspecialchars($ticket['name']); ?>"
                            data-ticket-price="<?php echo $ticket['price']; ?>"
                            data-max-stock="<?php echo $ticket['stock']; ?>">Beli</button>
                        </div>
                      </div>
                    <?php else: ?>
                      <p class="text-muted small">Tiket tidak tersedia atau stok habis.</p>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>Tidak ada jenis tiket yang tersedia untuk event ini.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mt-lg-5 mt-5 pt-lg-4y pt-3">
          <div class="card shadow-sm rounded-4 p-3 position-sticky" style="top: 20px;">
            <h6 class="mb-3">Detail Event</h6>
            <div class="event-detail mb-3">
              <i class="bi bi-calendar-event"></i>
              <strong>Tanggal</strong>
              <span id="detailEventDate"><?php echo formatEventDate($eventData['event_date']); ?></span>
            </div>
            <div class="event-detail mb-3">
              <i class="bi bi-clock"></i>
              <strong>Waktu</strong>
              <span id="detailEventTime"><?php echo formatEventTime($eventData['event_time'] ?? 'N/A'); ?></span>
            </div>
            <div class="event-detail mb-4">
              <i class="bi bi-geo-alt"></i>
              <strong>Lokasi</strong>
              <span id="detailEventLocation"><?php echo htmlspecialchars($eventData['lokasi'] ?? 'N/A'); ?></span>
            </div>
            <button class="btn btn-primary w-100 btn-beli-tiket" id="beliTiketSidebar">Pesan Tiket Sekarang</button>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center" role="alert">
        Event yang Anda cari tidak ditemukan atau ID event tidak valid. <a href="dashboard.php" class="alert-link">Kembali
          ke beranda</a>.
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/init.js?v=<?php echo time(); ?>"></script>
  <script>
    // Skrip spesifik detail-event.php
    const btnDeskripsi = document.getElementById('btnDeskripsi');
    const btnTiket = document.getElementById('btnTiket');
    const kontenDeskripsi = document.getElementById('kontenDeskripsi');
    const kontenTiket = document.getElementById('kontenTiket');
    const beliTiketSidebarButton = document.getElementById('beliTiketSidebar');

    // Ambil data event dari PHP untuk JS jika diperlukan untuk handleBeliTiketAction
    const jsEventData = <?php echo $eventData ? json_encode($eventData) : 'null'; ?>;

    if (btnDeskripsi && btnTiket && kontenDeskripsi && kontenTiket) {
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

    function handleBeliTiketAction(ticketId, ticketName, ticketPrice, jumlahTiket) {
      if (!jsEventData) {
        alert("Data event tidak ditemukan.");
        return;
      }
      const dataPembelian = {
        eventId: <?php echo $event_id ?? 'null'; ?>,
        eventName: jsEventData.nama,
        eventDate: "<?php echo $eventData ? formatEventDate($eventData['event_date'], $eventData['event_time']) : ''; ?>",
        eventLocation: jsEventData.lokasi,
        eventImage: "foto/" + (jsEventData.image_url || 'default_event_image.png'),
        ticketTypeId: ticketId,
        namaTiket: ticketName,
        hargaSatuan: parseFloat(ticketPrice),
        jumlah: parseInt(jumlahTiket),
        totalHarga: parseFloat(ticketPrice) * parseInt(jumlahTiket)
      };
      console.log("Data Pembelian:", dataPembelian); // Untuk debugging
      localStorage.setItem('dataPembelianTiket', JSON.stringify(dataPembelian));
      window.location.href = 'form-pembelian.php';
    }

    document.querySelectorAll('.btn-beli-tiket[data-ticket-id]').forEach(button => {
      button.addEventListener('click', function () {
        const ticketId = this.dataset.ticketId;
        const ticketName = this.dataset.ticketName;
        const ticketPrice = this.dataset.ticketPrice;
        const jumlahInputId = `jumlahTiket_${ticketId}`;
        const jumlahTiket = document.getElementById(jumlahInputId).value;

        // Pastikan requireLogin didefinisikan (misalnya di init.js)
        if (typeof requireLogin === 'function') {
          requireLogin(() => handleBeliTiketAction(ticketId, ticketName, ticketPrice, jumlahTiket));
        } else {
          // Fallback jika requireLogin tidak ada (misal, jika user belum login, logika di bawah akan jalan)
          const isLoggedInForButton = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
          if (!isLoggedInForButton) {
            alert("Anda harus login terlebih dahulu untuk melanjutkan.");
            // window.location.href = 'login.php'; // Opsional: arahkan ke login
          } else {
            handleBeliTiketAction(ticketId, ticketName, ticketPrice, jumlahTiket);
          }
        }
      });
    });

    if (beliTiketSidebarButton) {
      beliTiketSidebarButton.addEventListener('click', function () {
        // Pastikan requireLogin didefinisikan
        if (typeof requireLogin === 'function') {
          requireLogin(() => {
            if (btnTiket) btnTiket.click(); // Arahkan ke tab tiket
          });
        } else {
          const isLoggedInForButton = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
          if (!isLoggedInForButton) {
            alert("Anda harus login terlebih dahulu untuk melanjutkan.");
          } else {
            if (btnTiket) btnTiket.click(); // Arahkan ke tab tiket
          }
        }
      });
    }

    document.querySelectorAll('.btn-tambah').forEach(button => {
      button.addEventListener('click', function () {
        const targetInputId = this.dataset.target;
        const inputElement = document.getElementById(targetInputId);
        const maxStock = parseInt(this.closest('.card').querySelector('.btn-beli-tiket').dataset.maxStock); // Ambil max stock
        let currentValue = parseInt(inputElement.value);
        if (currentValue < maxStock) { // Batasi agar tidak melebihi stok
          inputElement.value = currentValue + 1;
        } else {
          alert("Jumlah tiket tidak dapat melebihi stok yang tersedia (" + maxStock + ").");
        }
      });
    });
    document.querySelectorAll('.btn-kurang').forEach(button => {
      button.addEventListener('click', function () {
        const targetInputId = this.dataset.target;
        const inputElement = document.getElementById(targetInputId);
        let currentValue = parseInt(inputElement.value);
        if (currentValue > 1) { inputElement.value = currentValue - 1; }
      });
    });

    // Search toggle functionality
    let searchToggle = document.getElementById('searchToggle');
    if (searchToggle) {
      let isToggling = false;
      searchToggle.addEventListener('click', function () {
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
