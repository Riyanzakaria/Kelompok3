<?php
session_start();
require_once 'db.php'; // File koneksi database Anda

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id_session = (int) $_SESSION['user_id'];
$pageTitle = "Tiket Saya";
$userTickets = [];

// Query untuk mengambil semua tiket yang dimiliki oleh pengguna,
// di mana setiap baris di order_customers dianggap sebagai satu tiket individu.
$stmt = $conn->prepare(
    "SELECT
        oc.order_customer_id, 
        oc.nama_lengkap AS ticket_holder_name,
        o.order_id, 
        o.order_date, 
        o.status AS order_status,
        e.event_id, 
        e.nama AS event_name, 
        e.event_date, 
        e.event_time, 
        e.lokasi AS event_location, 
        e.image_url AS event_image,
        tt.name AS ticket_type_name,
        tt.price AS ticket_price,
        (SELECT COUNT(*) FROM order_customers WHERE order_id = o.order_id) AS total_tickets_in_order,
        (SELECT COUNT(*) FROM order_customers WHERE order_id = o.order_id AND order_customer_id <= oc.order_customer_id) AS ticket_sequence_in_order
     FROM order_customers oc
     JOIN orders o ON oc.order_id = o.order_id
     JOIN order_items oi ON o.order_id = oi.order_id /* Asumsi 1 order_item per order dari alur saat ini */
     JOIN ticket_types tt ON oi.ticket_type_id = tt.ticket_type_id
     JOIN events e ON tt.event_id = e.event_id
     WHERE o.user_id = ? AND o.status = 'dibayar' /* Hanya tampilkan tiket dari order yang sudah dibayar */
     ORDER BY o.order_date DESC, e.event_date DESC, oc.order_customer_id ASC"
);

if ($stmt) {
    $stmt->bind_param("i", $user_id_session);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $userTickets[] = $row;
    }
    $stmt->close();
} else {
    error_log("Failed to prepare statement for user tickets: " . $conn->error);
}
$conn->close();

function formatDateIndo($dateStr)
{
    if (!$dateStr)
        return "-";
    try {
        $date = new DateTime($dateStr);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return $days[$date->format('w')] . ', ' . $date->format('d') . ' ' . $months[$date->format('n') - 1] . ' ' . $date->format('Y');
    } catch (Exception $e) {
        return "-";
    }
}
function formatTimeIndo($timeStr)
{
    if (!$timeStr)
        return "";
    try {
        $time = new DateTime($timeStr);
        return $time->format('H:i \W\I\B');
    } catch (Exception $e) {
        return "";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="foto/logoputih.png" rel="icon">
    <title><?php echo $pageTitle; ?> - Harmonix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        body {
            /* font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; */
            /* Menggunakan font family dari dashboard_tiket.php untuk konsistensi jika diinginkan */
            font-family: 'Inter', sans-serif;
            /* Contoh jika Anda punya font Inter */
            background-color: #f9fafb;
            /* Sebelumnya #f9fafb di file ini, bg-white dari body class */
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

        .ticket-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .ticket-card-header {
            background-color: #1E3A8A;
            color: white;
            padding: 15px 20px;
        }

        .ticket-card-header h5 {
            margin-bottom: 5px;
            font-weight: 600;
        }

        .ticket-card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .ticket-card-body .event-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #e9ecef;
        }

        .ticket-info p {
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .ticket-info p strong {
            color: #111827;
            font-weight: 500;
        }

        .btn-download-tiket {
            background-color: #10b981;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .btn-download-tiket:hover {
            background-color: #059669;
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }

        .profile-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
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

        .flag-container {
            display: flex;
            align-items: center;
        }

        .flag {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(to bottom, red 50%, white 50%);
            margin-right: 8px;
        }

        .flag-text {
            color: white;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container d-flex align-items-center py-3">
            <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">
                <img src="foto/logoputih.png" alt="Harmonix Logo" width="32" height="32"
                    class="d-inline-block align-text-top me-2">
                Harmonix
            </a>
            <div class="d-none d-lg-flex mx-auto">
                <a href="dashboard_tiket.php" class="nav-link">Jelajah</a>
                <a href="tambah_event.php" class="nav-link">Event Creator</a>
                <a href="#" class="nav-link">Hubungi Kami</a>
            </div>
            
            <div class="d-flex align-items-center">
          <i class="fas fa-search text-white me-3" id="searchToggleDashboard" aria-label="Toggle search bar"></i>
                <div class="flag-container me-3 d-none d-md-flex">
                    <div class="flag"></div>
                    <span class="flag-text">ID</span>
                </div>
                <div class="d-flex align-items-center navbar-actions" id="navbarActions">
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4 fw-bold">Tiket Saya</h2>
        <?php if (empty($userTickets)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-ticket-detailed fs-3 mb-2 d-block"></i>
                Anda belum memiliki tiket untuk event apapun. <br>
                <a href="dashboard_tiket.php" class="alert-link">Jelajahi event menarik sekarang!</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($userTickets as $ticket): ?>
                    <div class="col-lg-4 col-md-6 mb-4 d-flex align-items-stretch">
                        <div class="ticket-card">
                            <div class="ticket-card-header">
                                <h5><?php echo htmlspecialchars($ticket['event_name']); ?></h5>
                                <small><?php echo htmlspecialchars($ticket['ticket_type_name']); ?> - Untuk:
                                    <?php echo htmlspecialchars($ticket['ticket_holder_name']); ?></small>
                            </div>
                            <div class="ticket-card-body">
                                <div>
                                    <img src="foto/<?php echo !empty($ticket['event_image']) ? htmlspecialchars($ticket['event_image']) : 'default_event_square.png'; ?>"
                                        alt="Gambar Event" class="event-image"
                                        onerror="this.onerror=null; this.src='foto/default_event_square.png';">
                                    <div class="ticket-info">
                                        <p><i class="bi bi-calendar-event me-2 text-primary"></i><strong>Tanggal:</strong>
                                            <?php echo formatDateIndo($ticket['event_date']); ?>
                                            <?php echo formatTimeIndo($ticket['event_time']); ?>
                                        </p>
                                        <p><i class="bi bi-geo-alt-fill me-2 text-primary"></i><strong>Lokasi:</strong>
                                            <?php echo htmlspecialchars($ticket['event_location']); ?></p>
                                        <p><i class="bi bi-bookmark-star-fill me-2 text-primary"></i><strong>ID
                                                Pesanan:</strong> #<?php echo htmlspecialchars($ticket['order_id']); ?></p>
                                        <p><i class="bi bi-person-badge-fill me-2 text-primary"></i><strong>Pemilik
                                                Tiket:</strong> <?php echo htmlspecialchars($ticket['ticket_holder_name']); ?>
                                        </p>
                                        <p class="text-muted small">Tiket <?php echo $ticket['ticket_sequence_in_order']; ?>
                                            dari <?php echo $ticket['total_tickets_in_order']; ?> (untuk pesanan ini)</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="generate_etiket_pdf.php?oc_id=<?php echo $ticket['order_customer_id']; ?>"
                                        class="btn btn-download-tiket" target="_blank"> <i class="bi bi-download me-2"></i>Unduh
                                        E-Tiket
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="mt-auto py-4 border-top bg-light">
        <div class="container text-center">
            <p class="mb-0 text-muted small">&copy; <?php echo date("Y"); ?> Harmonix. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
        const userName = <?php echo json_encode($_SESSION['user_name'] ?? 'Pengguna'); ?>;
        const userProfilePic = <?php echo json_encode($_SESSION['user_profile_pic'] ?? 'foto/default_avatar.png'); ?>;
    </script>
    <script src="js/init.js?v=<?php echo time(); ?>"></script>
</body>

</html>