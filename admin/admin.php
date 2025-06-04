<?php
session_start();
// Pengecekan Sesi Admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
  header('Location: admin_login.php'); // Arahkan ke halaman login admin
  exit;
}
require_once '../db.php'; // Path ke db.php dari folder admin

// Data untuk Dashboard
$totalEvents = 0;
$eventsThisMonth = 0;
$totalUsers = 0;

try {
  $totalEventsResult = $conn->query("SELECT COUNT(*) AS total_events FROM events WHERE status = 'upcoming' OR status = 'ongoing'");
  if ($totalEventsResult)
    $totalEvents = $totalEventsResult->fetch_assoc()['total_events'];

  $currentMonth = date('m');
  $currentYear = date('Y');
  $stmtEventsThisMonth = $conn->prepare("SELECT COUNT(*) AS events_this_month FROM events WHERE (status = 'upcoming' OR status = 'ongoing') AND MONTH(event_date) = ? AND YEAR(event_date) = ?");
  if ($stmtEventsThisMonth) {
    $stmtEventsThisMonth->bind_param("ss", $currentMonth, $currentYear);
    $stmtEventsThisMonth->execute();
    $resultEventsThisMonth = $stmtEventsThisMonth->get_result();
    if ($resultEventsThisMonth)
      $eventsThisMonth = $resultEventsThisMonth->fetch_assoc()['events_this_month'];
    $stmtEventsThisMonth->close();
  }

  $totalUsersResult = $conn->query("SELECT COUNT(*) AS total_users FROM users");
  if ($totalUsersResult)
    $totalUsers = $totalUsersResult->fetch_assoc()['total_users'];

  $eventListData = [];
  // Ambil semua kolom yang dibutuhkan untuk edit modal
  $resultEventList = $conn->query("SELECT event_id, nama, deskripsi, event_date, event_time, lokasi, image_url, status FROM events ORDER BY event_date DESC, event_id DESC");
  if ($resultEventList && $resultEventList->num_rows > 0) {
    while ($row = $resultEventList->fetch_assoc()) {
      $stmtPrice = $conn->prepare("SELECT MIN(price) as min_price FROM ticket_types WHERE event_id = ?");
      if ($stmtPrice) {
        $stmtPrice->bind_param("i", $row['event_id']);
        $stmtPrice->execute();
        $priceResult = $stmtPrice->get_result();
        $row['harga'] = ($priceResult && $priceResult->num_rows > 0) ? $priceResult->fetch_assoc()['min_price'] : null;
        $stmtPrice->close();
      } else {
        $row['harga'] = null;
        error_log("Failed to prepare statement for min_price on event_id: " . $row['event_id'] . " Error: " . $conn->error);
      }

      if ($row['status'] == 'upcoming') {
        $row['status_display_text'] = 'Akan Datang';
        $row['status_display_class'] = 'success';
      } elseif ($row['status'] == 'ongoing') {
        $row['status_display_text'] = 'Berlangsung';
        $row['status_display_class'] = 'primary';
      } elseif ($row['status'] == 'finished') {
        $row['status_display_text'] = 'Selesai';
        $row['status_display_class'] = 'secondary';
      } else {
        $row['status_display_text'] = 'Tidak Diketahui';
        $row['status_display_class'] = 'light';
      }
      $eventListData[] = $row;
    }
  }
} catch (Exception $e) {
  error_log("Error in admin.php data fetching: " . $e->getMessage());
  // Menampilkan pesan error dasar di halaman, bisa diganti dengan yang lebih baik
  echo "Terjadi kesalahan saat mengambil data: " . htmlspecialchars($e->getMessage());
}
// $conn->close(); // Ditutup oleh skrip backend
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="../foto/logoputih.png" rel="icon">
  <title>Admin Panel - Harmonix</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }

    .sidebar {
      min-height: 100vh;
      background-color: #ffffff;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
      padding-top: 20px;
      z-index: 1000;
    }

    .sidebar .nav-link {
      color: #343a40;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
      font-weight: 500;
      transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
      border-radius: 8px;
      margin: 2px 10px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: #1E3A8A;
      color: white;
    }

    .sidebar .nav-link i {
      width: 20px;
      text-align: center;
      margin-right: 8px;
    }

    .content {
      padding: 20px;
      margin-top: 0px;
    }

    .card-custom {
      border: none;
      border-radius: 12px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
      transition: transform 0.2s ease-in-out;
      height: 100%;
    }

    .card-custom:hover {
      transform: translateY(-5px);
    }

    .card-custom h5 {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 5px;
    }

    .card-custom h3 {
      color: #1E3A8A;
      font-weight: 700;
    }

    .btn-custom-action {
      padding: 0.3rem 0.6rem;
      font-size: 0.8rem;
    }

    .modal-content {
      border-radius: 0.75rem;
    }

    .table thead th {
      background-color: #1E3A8A !important;
      color: white !important;
      border-color: #1E3A8A !important;
      font-weight: 500;
      white-space: nowrap;
    }

    .table tbody td {
      vertical-align: middle;
      font-size: 0.875rem;
    }

    .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgba(30, 58, 138, 0.03);
    }

    .navbar .btn-logout {
      background-color: white;
      color: #1E3A8A;
      border-radius: 20px;
      padding: 0.375rem 1rem;
      font-weight: 500;
      transition: background-color 0.2s, color 0.2s;
    }

    .navbar .btn-logout:hover {
      background-color: #e9ecef;
      color: #1E3A8A;
    }

    .btn-tambah-event {
      background-color: #1E3A8A;
      color: white;
      border-radius: 8px;
      font-weight: 500;
      padding: 0.5rem 1rem;
    }

    .btn-tambah-event:hover {
      background-color: #162c6e;
      color: white;
    }

    .form-label {
      font-weight: 500;
      font-size: 0.875rem;
    }

    .modal-body {
      max-height: 70vh;
      overflow-y: auto;
    }

    .main-navbar {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .text-dark-emphasis {
      color: #343a40 !important;
    }

    .badge.bg-success {
      background-color: #198754 !important;
      color: white !important;
    }

    .badge.bg-primary {
      background-color: #0d6efd !important;
      color: white !important;
    }

    .badge.bg-secondary {
      background-color: #6c757d !important;
      color: white !important;
    }

    .badge.bg-light {
      background-color: #f8f9fa !important;
      color: #212529 !important;
      border: 1px solid #dee2e6;
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark sticky-top main-navbar" style="background-color: #1E3A8A;">
    <div class="container-fluid py-1">
      <a class="navbar-brand fw-bold" href="admin.php" style="font-size: 1.4rem;">
        <img src="../foto/logoputih.png" alt="Harmonix Admin Logo" width="30" height="30"
          class="d-inline-block align-text-top me-2">
        Harmonix Admin
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
        aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="d-flex ms-auto align-items-center">
        <span class="navbar-text text-white me-3 d-none d-sm-inline">
          Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!
        </span>
        <a href="logout_admin.php" class="btn btn-logout btn-sm" type="button">
          <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="position-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link active" href="#dashboard" onclick="showPage('dashboard', this)">
                <i class="fas fa-tachometer-alt"></i> Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#kelolaEvent" onclick="showPage('kelolaEvent', this)">
                <i class="fas fa-calendar-alt"></i> Kelola Event
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
        <div id="dashboard">
          <div
            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Dashboard</h1>
          </div>
          <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card card-custom text-center p-4">
                <h5>Total Event Aktif</h5>
                <h3 id="totalEventsCount"><?php echo $totalEvents; ?></h3>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card card-custom text-center p-4">
                <h5>Event Bulan Ini</h5>
                <h3 id="eventsThisMonthCount"><?php echo $eventsThisMonth; ?></h3>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card card-custom text-center p-4">
                <h5>Pengguna Terdaftar</h5>
                <h3 id="totalUsersCount"><?php echo $totalUsers; ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div id="kelolaEvent" style="display: none;">
          <div
            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Kelola Event</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
              <button class="btn btn-sm btn-tambah-event" data-bs-toggle="modal" data-bs-target="#eventModal"
                onclick="prepareAddModal()">
                <i class="fas fa-plus me-1"></i> Tambah Event Baru
              </button>
            </div>
          </div>

          <div class="table-responsive shadow-sm bg-white rounded-3 p-3">
            <table class="table table-hover table-bordered" id="eventTable">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Event</th>
                  <th>Tanggal</th>
                  <th>Lokasi</th>
                  <th>Harga Mulai</th>
                  <th>Status</th>
                  <th>Aksi</th>
                  <th>Analisis</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($eventListData)): ?>
                  <?php foreach ($eventListData as $index => $event): ?>
                    <tr id="event-row-<?php echo $event['event_id']; ?>">
                      <td><?php echo $index + 1; ?></td>
                      <td class="text-truncate" style="max-width: 200px;"
                        title="<?php echo htmlspecialchars($event['nama']); ?>">
                        <?php echo htmlspecialchars($event['nama']); ?></td>
                      <td><?php echo date('d M Y', strtotime($event['event_date'])); ?></td>
                      <td class="text-truncate" style="max-width: 150px;"
                        title="<?php echo htmlspecialchars($event['lokasi']); ?>">
                        <?php echo htmlspecialchars($event['lokasi']); ?></td>
                      <td>
                        <?php echo $event['harga'] !== null ? 'Rp ' . number_format($event['harga'], 0, ',', '.') : 'N/A'; ?>
                      </td>
                      <td>
                        <span
                          class="badge bg-<?php echo htmlspecialchars($event['status_display_class']); ?> <?php if ($event['status_display_class'] == 'light')
                                echo 'text-dark';
                              else
                                echo 'text-white'; ?> p-2">
                          <?php echo htmlspecialchars($event['status_display_text']); ?>
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-warning btn-custom-action" title="Edit Event"
                          onclick='prepareEditModal(<?php echo htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8'); ?>)'>
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-custom-action" title="Hapus Event"
                          onclick="deleteEvent(<?php echo $event['event_id']; ?>, '<?php echo htmlspecialchars(addslashes($event['nama']), ENT_QUOTES, 'UTF-8'); ?>')">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-info btn-custom-action" title="Lihat Analisis"
                          onclick="showEventAnalytics(<?php echo $event['event_id']; ?>)">
                          <i class="fas fa-chart-line"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-center">Belum ada event yang ditambahkan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title" id="eventModalLabel">Tambah Event Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="eventForm" enctype="multipart/form-data">
            <input type="hidden" id="eventId" name="eventId">

            <div class="mb-3">
              <label for="eventName" class="form-label">Nama Event <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="eventName" name="eventName" required>
            </div>
            <div class="row gx-3">
              <div class="col-md-6 mb-3">
                <label for="eventDate" class="form-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="eventDate" name="eventDate" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="eventTime" class="form-label">Waktu (Opsional)</label>
                <input type="time" class="form-control" id="eventTime" name="eventTime">
              </div>
            </div>
            <div class="mb-3">
              <label for="eventLocation" class="form-label">Lokasi <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="eventLocation" name="eventLocation" required>
            </div>
            <div class="mb-3">
              <label for="eventDescription" class="form-label">Deskripsi (Opsional)</label>
              <textarea class="form-control" id="eventDescription" name="eventDescription" rows="3"></textarea>
            </div>

            <div class="mb-3">
              <label for="eventImageFile" class="form-label">Gambar Event (Opsional)</label>
              <input type="file" class="form-control" id="eventImageFile" name="eventImageFile"
                accept="image/jpeg, image/png, image/gif">
              <small class="form-text text-muted">Pilih file baru untuk mengganti. Jika kosong & edit, gambar lama
                dipertahankan. Max: 2MB.</small>
              <div id="currentImagePreview" class="mt-2"></div>
            </div>

            <div class="mb-3">
              <label for="eventStatus" class="form-label">Status Event <span class="text-danger">*</span></label>
              <select class="form-select" id="eventStatus" name="eventStatus" required>
                <option value="upcoming" selected>Akan Datang (Upcoming)</option>
                <option value="ongoing">Sedang Berlangsung (Ongoing)</option>
                <option value="finished">Selesai (Finished)</option>
              </select>
            </div>
            <hr>
            <h6 class="mb-3">Jenis Tiket <span class="text-danger">*</span> <small class="text-muted">(Minimal 1 jenis
                tiket)</small></h6>
            <div id="ticketTypesContainer">
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addTicketTypeButton">
              <i class="fas fa-plus"></i> Tambah Jenis Tiket
            </button>

            <div class="mt-4">
              <button type="button" class="btn btn-tambah-event w-100" id="submitEventButton"
                onclick="submitEventForm()">Simpan Event</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="analyticsModal" tabindex="-1" aria-labelledby="analyticsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content rounded-4">
      <div class="modal-header">
        <h5 class="modal-title" id="analyticsModalLabel">Analisis Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="analyticsEventInfo" class="mb-3">
            <h4 id="analyticsEventName">Nama Event</h4>
            <p class="text-muted mb-0">
                <span id="analyticsEventDate">Tanggal</span> | Status: <span id="analyticsEventStatus" class="fw-bold">Status</span>
            </p>
        </div>
        <hr>
        <h5>Statistik Keseluruhan</h5>
        <div class="row mb-3">
            <div class="col-md-3 col-6 mb-2">
                <div class="card card-body p-2 text-center shadow-sm">
                    <small class="text-muted">Tiket Terjual</small>
                    <strong class="fs-5" id="analyticsTotalTicketsSold">0</strong>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                 <div class="card card-body p-2 text-center shadow-sm">
                    <small class="text-muted">Total Pesanan</small>
                    <strong class="fs-5" id="analyticsTotalOrders">0</strong>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                 <div class="card card-body p-2 text-center shadow-sm">
                    <small class="text-muted">Rata2 Tiket/Order</small>
                    <strong class="fs-5" id="analyticsAvgTickets">0</strong>
                </div>
            </div>
             <div class="col-md-3 col-6 mb-2">
                <div class="card card-body p-2 text-center shadow-sm">
                    <small class="text-muted">Pendapatan Total</small>
                    <strong class="fs-5" id="analyticsTotalRevenue">Rp 0</strong>
                </div>
            </div>
        </div>
        
        <hr>
        <h5>Rincian per Jenis Tiket</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Jenis Tiket</th>
                        <th>Harga Satuan</th>
                        <th>Terjual</th>
                        <th>Sisa Stok</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody id="analyticsTicketTypeDetails">
                    <tr><td colspan="5" class="text-center text-muted">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        <hr class="my-4"> <h5>Daftar Pembeli/Pemilik Tiket (Status Bayar)</h5>
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-sm table-hover table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Pemilik Tiket</th>
                        <th>Email</th>
                        <th>No. Whatsapp</th>
                        <th>Jenis Tiket</th>
                        <th>Tgl Pesan</th>
                        <th>ID Pesanan</th>
                    </tr>
                </thead>
                <tbody id="analyticsBuyersList">
                    <tr><td colspan="7" class="text-center text-muted">Memuat data pembeli...</td></tr>
                </tbody>
            </table>
        </div>
        </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navigasi Halaman
    function showPage(pageId, element) {
      document.querySelectorAll('main.content > div').forEach(div => div.style.display = 'none');
      document.getElementById(pageId).style.display = 'block';
      document.querySelectorAll('.sidebar .nav-link').forEach(link => link.classList.remove('active'));
      if (element) {
        element.classList.add('active');
      }
    }
    document.addEventListener('DOMContentLoaded', function () {
      const defaultPageId = 'dashboard';
      const defaultSidebarLink = document.querySelector(`.sidebar .nav-link[href="#${defaultPageId}"]`);
      showPage(defaultPageId, defaultSidebarLink);
    });

    // Logika Modal dan Form Event
    const eventModalElement = document.getElementById('eventModal');
    const eventModal = new bootstrap.Modal(eventModalElement);
    const eventForm = document.getElementById('eventForm');
    const eventModalLabel = document.getElementById('eventModalLabel');
    const ticketTypesContainer = document.getElementById('ticketTypesContainer');
    const currentImagePreviewDiv = document.getElementById('currentImagePreview');
    const eventImageFileInput = document.getElementById('eventImageFile');

    function addTicketTypeRow(ticket = null) {
      const div = document.createElement('div');
      div.className = 'row gx-2 mb-2 align-items-center ticket-type-row';

      const ticketTypeIdValue = ticket && ticket.ticket_type_id ? ticket.ticket_type_id : '';
      const ticketNameValue = ticket && ticket.name ? ticket.name : '';
      const ticketPriceValue = (ticket && ticket.price !== null) ? parseFloat(ticket.price) : '';
      const ticketStockValue = (ticket && ticket.stock !== null) ? parseInt(ticket.stock) : '';

      let ticketStatusValue = 'available'; // Default
      if (ticket && ticket.status && (ticket.status === 'available' || ticket.status === 'sold_out')) {
        ticketStatusValue = ticket.status;
      }
      const ticketStatusAvailableSelected = ticketStatusValue === 'available' ? 'selected' : '';
      const ticketStatusSoldOutSelected = ticketStatusValue === 'sold_out' ? 'selected' : '';

      div.innerHTML = `
        <input type="hidden" name="ticket_type_ids[]" value="${ticketTypeIdValue}">
        <div class="col-md-4 mb-2">
            <input type="text" class="form-control form-control-sm" name="ticket_names[]" placeholder="Nama Tiket (mis: VIP)" value="${ticketNameValue}" required>
        </div>
        <div class="col-md-3 mb-2">
            <input type="number" class="form-control form-control-sm" name="ticket_prices[]" placeholder="Harga (Rsp)" value="${ticketPriceValue}" required step="500" min="0">
        </div>
        <div class="col-md-2 mb-2">
            <input type="number" class="form-control form-control-sm" name="ticket_stocks[]" placeholder="Stok" value="${ticketStockValue}" required min="0">
        </div>
        <div class="col-md-2 mb-2">
             <select class="form-select form-select-sm" name="ticket_statuses[]" required>
                <option value="available" ${ticketStatusAvailableSelected}>Available</option>
                <option value="sold_out" ${ticketStatusSoldOutSelected}>Sold Out</option>
            </select>
        </div>
        <div class="col-md-1 mb-2 text-end">
            <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeTicketTypeRow(this);" title="Hapus Jenis Tiket"><i class="fas fa-times"></i></button>
        </div>
    `;
      ticketTypesContainer.appendChild(div);
    }

    function removeTicketTypeRow(buttonElement) {
      const row = buttonElement.closest('.ticket-type-row');
      const ticketIdInput = row.querySelector('input[name="ticket_type_ids[]"]');
      if (ticketIdInput && ticketIdInput.value !== '') {
        if (!confirm('Anda yakin ingin menghapus jenis tiket yang sudah ada ini dari form? Perubahan akan disimpan saat Anda klik "Simpan Event".')) {
          return;
        }
      }
      row.remove();
      if (ticketTypesContainer.children.length === 0) {
        addTicketTypeRow();
      }
    }

    document.getElementById('addTicketTypeButton').addEventListener('click', function () {
      addTicketTypeRow();
    });

    function prepareAddModal() {
      eventModalLabel.textContent = 'Tambah Event Baru';
      document.getElementById('eventId').value = '';
      eventForm.reset();
      document.getElementById('eventId').value = '';

      currentImagePreviewDiv.innerHTML = '';
      if (eventImageFileInput) eventImageFileInput.value = null;

      ticketTypesContainer.innerHTML = '';
      addTicketTypeRow();
      eventModal.show();
    }

    async function prepareEditModal(eventData) {
      const event = eventData; // eventData sudah objek karena json_encode di PHP

      eventModalLabel.textContent = 'Edit Event: ' + (event.nama || 'Event');
      document.getElementById('eventId').value = event.event_id;
      eventForm.reset();
      document.getElementById('eventId').value = event.event_id;

      document.getElementById('eventName').value = event.nama || '';
      document.getElementById('eventDate').value = event.event_date || '';
      document.getElementById('eventTime').value = event.event_time || '';
      document.getElementById('eventLocation').value = event.lokasi || '';
      document.getElementById('eventDescription').value = event.deskripsi || '';
      if (eventImageFileInput) eventImageFileInput.value = null;

      currentImagePreviewDiv.innerHTML = '';
      if (event.image_url) {
        currentImagePreviewDiv.innerHTML = `
            <p class="mb-1"><small>Gambar saat ini:</small></p>
            <img src="../foto/${event.image_url}" alt="Gambar Event Saat Ini" style="max-width: 150px; height: auto; border-radius: 4px; margin-bottom:10px;" onerror="this.style.display='none'; this.parentElement.insertAdjacentHTML('beforeend', '<small class=\\'text-danger\\'>Gambar tidak ditemukan atau path salah.</small>')">
        `;
      } else {
        currentImagePreviewDiv.innerHTML = '<p class="mb-1"><small class="text-muted">Tidak ada gambar saat ini.</small></p>';
      }

      document.getElementById('eventStatus').value = event.status || 'upcoming';

      ticketTypesContainer.innerHTML = '';

      try {
        const response = await fetch(`get_ticket_types.php?event_id=${event.event_id}`);
        const result = await response.json();
        if (result.success && result.data.length > 0) {
          result.data.forEach(ticket => addTicketTypeRow(ticket));
        } else if (result.success && result.data.length === 0) {
          addTicketTypeRow();
        } else {
          console.error("Gagal mengambil jenis tiket untuk edit:", result.message);
          addTicketTypeRow();
        }
      } catch (error) {
        console.error("Error saat fetch jenis tiket untuk edit:", error);
        addTicketTypeRow();
      }
      eventModal.show();
    }

    async function submitEventForm() {
      const submitButton = document.getElementById('submitEventButton');
      const originalButtonText = submitButton.textContent;

      if (!eventForm.checkValidity()) {
        eventForm.reportValidity();
        return;
      }
      const ticketNameInputs = eventForm.querySelectorAll('input[name="ticket_names[]"]');
      if (ticketNameInputs.length === 0) {
        alert("Minimal harus ada satu jenis tiket.");
        return;
      }
      let ticketDataValid = true;
      for (let i = 0; i < ticketNameInputs.length; i++) {
        const name = ticketNameInputs[i].value.trim();
        const price = eventForm.querySelectorAll('input[name="ticket_prices[]"]')[i].value.trim();
        const stock = eventForm.querySelectorAll('input[name="ticket_stocks[]"]')[i].value.trim();
        if (name === "" || price === "" || stock === "") {
          ticketDataValid = false;
          break;
        }
      }
      if (!ticketDataValid) {
        alert("Nama, Harga, dan Stok untuk setiap jenis tiket wajib diisi.");
        return;
      }

      submitButton.disabled = true;
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Menyimpan...';
      const formData = new FormData(eventForm);
      const eventIdValue = document.getElementById('eventId').value.trim();
      const action = eventIdValue ? 'edit_event.php' : 'add_event.php';

      try {
        const response = await fetch(action, { method: 'POST', body: formData });
        const resultText = await response.text();
        try {
          const result = JSON.parse(resultText);
          if (result.success) {
            alert(result.message);
            eventModal.hide();
            window.location.reload();
          } else {
            alert('Error: ' + (result.message || 'Gagal menyimpan data.'));
          }
        } catch (jsonError) {
          console.error("Gagal parsing JSON:", jsonError, "Response:", resultText);
          const errorPreview = resultText.length > 300 ? resultText.substring(0, 300) + "..." : resultText;
          alert("Terjadi kesalahan pada respons server. Detail awal: " + errorPreview);
        }
      } catch (error) {
        console.error('Submit error:', error);
        alert('Terjadi kesalahan saat mengirim data ke server.');
      } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
      }
    }

    async function deleteEvent(eventId, eventName) {
      if (confirm(`Apakah Anda yakin ingin menghapus event "${eventName}"?\nTindakan ini akan menghapus semua jenis tiket terkait dan juga item pesanan yang berhubungan dengan tiket tersebut (jika ada). Data pesanan utama akan tetap ada.`)) {
        const formData = new FormData();
        formData.append('eventId', eventId);
        try {
          const response = await fetch('delete_event.php', { method: 'POST', body: formData });
          const resultText = await response.text();
          try {
            const result = JSON.parse(resultText);
            if (result.success) {
              alert(result.message);
              const row = document.getElementById(`event-row-${eventId}`);
              if (row) row.remove();
            } else {
              alert('Error: ' + (result.message || 'Gagal menghapus event.'));
            }
          } catch (jsonError) {
            console.error("Gagal parsing JSON:", jsonError, "Response:", resultText);
            const errorPreview = resultText.length > 300 ? resultText.substring(0, 300) + "..." : resultText;
            alert("Terjadi kesalahan pada respons server saat menghapus. Detail awal: " + errorPreview);
          }
        } catch (error) {
          console.error('Delete error:', error);
          alert('Terjadi kesalahan saat mengirim permintaan hapus.');
        }
      }
    }

    // Di dalam <script> di admin.php, setelah fungsi CRUD event yang sudah ada

const analyticsModalElement = document.getElementById('analyticsModal');
const analyticsModal = new bootstrap.Modal(analyticsModalElement);

async function showEventAnalytics(eventId) {
    if (!eventId) return;

    // Tampilkan loading di modal (opsional)
    document.getElementById('analyticsEventName').textContent = 'Memuat data event...';
    document.getElementById('analyticsTicketTypeDetails').innerHTML = '<tr><td colspan="5" class="text-center text-muted">Memuat rincian tiket...</td></tr>';
    document.getElementById('analyticsTotalTicketsSold').textContent = '-';
    document.getElementById('analyticsTotalOrders').textContent = '-';
    document.getElementById('analyticsAvgTickets').textContent = '-';
    document.getElementById('analyticsTotalRevenue').textContent = 'Rp -';
    document.getElementById('analyticsEventName').textContent = 'Memuat data event...';
    document.getElementById('analyticsTicketTypeDetails').innerHTML = '<tr><td colspan="5" class="text-center text-muted">Memuat rincian tiket...</td></tr>';
    document.getElementById('analyticsBuyersList').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Memuat data pembeli...</td></tr>';
    analyticsModal.show();

    try {
        const response = await fetch(`get_event_analytics.php?event_id=${eventId}`);
        const result = await response.json();

        if (result.success) {
            document.getElementById('analyticsEventName').textContent = result.eventName || 'Tidak Diketahui';
            document.getElementById('analyticsEventDate').textContent = result.eventDate ? new Date(result.eventDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric'}) : '-';
            
            let statusText = result.eventStatus || 'Tidak Diketahui';
            let statusClass = 'text-muted';
            if (result.eventStatus === 'upcoming') { statusText = 'Akan Datang'; statusClass = 'text-success'; }
            else if (result.eventStatus === 'ongoing') { statusText = 'Berlangsung'; statusClass = 'text-primary'; }
            else if (result.eventStatus === 'finished') { statusText = 'Selesai'; statusClass = 'text-secondary'; }
            const statusSpan = document.getElementById('analyticsEventStatus');
            statusSpan.textContent = statusText;
            statusSpan.className = `fw-bold ${statusClass}`;


            const stats = result.overallStats;
            document.getElementById('analyticsTotalTicketsSold').textContent = stats.totalTicketsSold.toLocaleString('id-ID');
            document.getElementById('analyticsTotalOrders').textContent = stats.totalOrders.toLocaleString('id-ID');
            document.getElementById('analyticsAvgTickets').textContent = stats.avgTicketsPerOrder.toLocaleString('id-ID', {minimumFractionDigits: 1, maximumFractionDigits: 2 });
            // Gunakan accurateTotalRevenue yang sudah termasuk biaya layanan (jika ada dari backend)
            document.getElementById('analyticsTotalRevenue').textContent = `Rp ${stats.accurateTotalRevenue ? stats.accurateTotalRevenue.toLocaleString('id-ID') : (stats.totalRevenue ? stats.totalRevenue.toLocaleString('id-ID') : '0')}`;


            const ticketDetailsBody = document.getElementById('analyticsTicketTypeDetails');
            ticketDetailsBody.innerHTML = ''; // Kosongkan isi lama
            

            if (result.ticketTypeDetails && result.ticketTypeDetails.length > 0) {
                result.ticketTypeDetails.forEach(ticket => {
                    const row = ticketDetailsBody.insertRow();
                    row.insertCell().textContent = ticket.typeName;
                    row.insertCell().textContent = `Rp ${ticket.price.toLocaleString('id-ID')}`;
                    row.insertCell().textContent = ticket.sold.toLocaleString('id-ID');
                    row.insertCell().textContent = ticket.stockRemaining.toLocaleString('id-ID');
                    row.insertCell().textContent = `Rp ${ticket.revenue.toLocaleString('id-ID')}`;
                });
            } else {
                ticketDetailsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data jenis tiket untuk event ini.</td></tr>';
            }

            const buyersListBody = document.getElementById('analyticsBuyersList');
            buyersListBody.innerHTML = ''; // Kosongkan isi lama

              if (result.buyersList && result.buyersList.length > 0) {
                result.buyersList.forEach((buyer, index) => {
                    const row = buyersListBody.insertRow();
                    row.insertCell().textContent = index + 1;
                    row.insertCell().textContent = buyer.nama_lengkap || '-';
                    row.insertCell().innerHTML = buyer.email ? `<a href="mailto:${buyer.email}">${buyer.email}</a>` : '-';
                    row.insertCell().innerHTML = buyer.whatsapp ? `<a href="https://wa.me/${buyer.whatsapp.replace(/\D/g,'')}" target="_blank">${buyer.whatsapp}</a>` : '-';
                    row.insertCell().textContent = buyer.ticket_type_name || '-';
                    row.insertCell().textContent = buyer.order_date ? new Date(buyer.order_date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour:'2-digit', minute: '2-digit'}) : '-';
                    row.insertCell().textContent = buyer.order_id ? `#${buyer.order_id}` : '-';
                });
            } else {
                buyersListBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada data pembeli untuk event ini.</td></tr>';
            }


            // TODO (Opsional): Render chart jika ada data dan library Chart.js di-load
            // renderSalesChart(result.ticketTypeDetails);

        } else {
            document.getElementById('analyticsEventName').textContent = 'Gagal memuat data';
            ticketDetailsBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${result.message || 'Tidak dapat mengambil data analitik.'}</td></tr>`;
              document.getElementById('analyticsTicketTypeDetails').innerHTML = `<tr><td colspan="5" class="text-center text-danger">${result.message || 'Tidak dapat mengambil data analitik.'}</td></tr>`;
            document.getElementById('analyticsBuyersList').innerHTML = `<tr><td colspan="7" class="text-center text-danger">${result.message || 'Tidak dapat mengambil data pembeli.'}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching event analytics:', error);
        document.getElementById('analyticsEventName').textContent = 'Error Koneksi';
        document.getElementById('analyticsTicketTypeDetails').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Terjadi kesalahan saat mengambil data.</td></tr>';
        document.getElementById('analyticsBuyersList').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Terjadi kesalahan saat mengambil data pembeli.</td></tr>';
    }
}

// function renderSalesChart(ticketData) { // Contoh fungsi render chart (membutuhkan Chart.js)
//     const salesChartCanvas = document.getElementById('salesChart');
//     if (!salesChartCanvas || !ticketData || typeof Chart === 'undefined') return;

//     const labels = ticketData.map(t => t.typeName);
//     const data = ticketData.map(t => t.sold);
//     const backgroundColors = ['rgba(30, 58, 138, 0.7)', 'rgba(255, 193, 7, 0.7)', 'rgba(25, 135, 84, 0.7)', 'rgba(108, 117, 125, 0.7)'];

//     // Hancurkan chart lama jika ada
//     if (window.mySalesChart instanceof Chart) {
//         window.mySalesChart.destroy();
//     }
    
//     window.mySalesChart = new Chart(salesChartCanvas, {
//         type: 'bar', // atau 'pie'
//         data: {
//             labels: labels,
//             datasets: [{
//                 label: 'Tiket Terjual',
//                 data: data,
//                 backgroundColor: backgroundColors.slice(0, data.length),
//                 borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
//                 borderWidth: 1
//             }]
//         },
//         options: {
//             responsive: true,
//             maintainAspectRatio: false,
//             scales: {
//                 y: { beginAtZero: true }
//             }
//         }
//     });
// }
</script>
  </script>
</body>

</html>