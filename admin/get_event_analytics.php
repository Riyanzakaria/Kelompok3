<?php
session_start();
require_once '../db.php'; // Sesuaikan path ke db.php Anda
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$response = ['success' => false, 'message' => 'ID Event tidak valid atau tidak ditemukan.'];

if (isset($_GET['event_id']) && filter_var($_GET['event_id'], FILTER_VALIDATE_INT)) {
    $event_id = (int)$_GET['event_id'];

    $conn->begin_transaction();
    try {
        // Ambil Info Event Dasar (sama seperti sebelumnya)
        $stmtEventInfo = $conn->prepare("SELECT nama, event_date, status FROM events WHERE event_id = ?");
        if (!$stmtEventInfo) throw new Exception("Prepare event info failed: " . $conn->error);
        $stmtEventInfo->bind_param("i", $event_id);
        $stmtEventInfo->execute();
        $resultEventInfo = $stmtEventInfo->get_result();
        if ($resultEventInfo->num_rows === 0) {
            throw new Exception("Event tidak ditemukan.");
        }
        $eventInfo = $resultEventInfo->fetch_assoc();
        $stmtEventInfo->close();

        $response['eventName'] = $eventInfo['nama'];
        $response['eventDate'] = $eventInfo['event_date'];
        $response['eventStatus'] = $eventInfo['status'];

        // Statistik Keseluruhan
        $stmtOverall = $conn->prepare(
            "SELECT 
                COALESCE(SUM(oi.quantity), 0) AS totalTicketsSold,
                COALESCE(SUM(oi.subtotal), 0) AS totalRevenue, /* Ini adalah subtotal tiket, belum termasuk biaya layanan per order */
                COUNT(DISTINCT o.order_id) AS totalOrders
             FROM orders o
             JOIN order_items oi ON o.order_id = oi.order_id
             JOIN ticket_types tt ON oi.ticket_type_id = tt.ticket_type_id
             WHERE tt.event_id = ? AND o.status = 'dibayar'"
        );
        if (!$stmtOverall) throw new Exception("Prepare overall stats failed: " . $conn->error);
        $stmtOverall->bind_param("i", $event_id);
        $stmtOverall->execute();
        $resultOverall = $stmtOverall->get_result()->fetch_assoc();
        $stmtOverall->close();
        
        $response['overallStats'] = [
            'totalTicketsSold' => (int)$resultOverall['totalTicketsSold'],
            'totalRevenue' => (float)$resultOverall['totalRevenue'], // Ini subtotal tiket, biaya layanan ada di tabel orders
            'totalOrders' => (int)$resultOverall['totalOrders'],
            'avgTicketsPerOrder' => ($resultOverall['totalOrders'] > 0) ? round($resultOverall['totalTicketsSold'] / $resultOverall['totalOrders'], 2) : 0
        ];
        
        // Untuk total revenue yang lebih akurat (termasuk biaya layanan per order), kita bisa SUM dari tabel orders
        $stmtAccurateRevenue = $conn->prepare(
             "SELECT COALESCE(SUM(o.jumlah_total), 0) AS accurateTotalRevenue
              FROM orders o
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN ticket_types tt ON oi.ticket_type_id = tt.ticket_type_id
              WHERE tt.event_id = ? AND o.status = 'dibayar'"
        );
        if (!$stmtAccurateRevenue) throw new Exception("Prepare accurate revenue failed: " . $conn->error);
        $stmtAccurateRevenue->bind_param("i", $event_id);
        $stmtAccurateRevenue->execute();
        $accurateRevenueResult = $stmtAccurateRevenue->get_result()->fetch_assoc();
        $stmtAccurateRevenue->close();
        $response['overallStats']['accurateTotalRevenue'] = (float)$accurateRevenueResult['accurateTotalRevenue'];


        // Rincian per Jenis Tiket
        $stmtTicketDetails = $conn->prepare(
            "SELECT 
                tt.name AS typeName, tt.price, tt.stock AS stockRemaining,
                COALESCE(SUM(oi.quantity), 0) AS sold,
                COALESCE(SUM(oi.subtotal), 0) AS revenue
             FROM ticket_types tt
             LEFT JOIN order_items oi ON tt.ticket_type_id = oi.ticket_type_id
             LEFT JOIN orders o ON oi.order_id = o.order_id AND o.status = 'dibayar'
             WHERE tt.event_id = ?
             GROUP BY tt.ticket_type_id, tt.name, tt.price, tt.stock
             ORDER BY tt.price ASC"
        );
        if (!$stmtTicketDetails) throw new Exception("Prepare ticket details failed: " . $conn->error);
        $stmtTicketDetails->bind_param("i", $event_id);
        $stmtTicketDetails->execute();
        $resultTicketDetails = $stmtTicketDetails->get_result();
        $ticketTypeDetails = [];
        while ($row = $resultTicketDetails->fetch_assoc()) {
            $ticketTypeDetails[] = [
                'typeName' => $row['typeName'],
                'price' => (float)$row['price'],
                'sold' => (int)$row['sold'],
                'stockRemaining' => (int)$row['stockRemaining'], // Ini stok awal, bukan sisa setelah terjual. Sisa stok = stockRemaining (dari DB) - sold.
                                                                // Kolom stock di ticket_types sudah diupdate oleh proses_pemesanan.php, jadi stockRemaining adalah sisa sebenarnya.
                'revenue' => (float)$row['revenue']
            ];
        }
        $stmtTicketDetails->close();
        $response['ticketTypeDetails'] = $ticketTypeDetails;

         // ---- BARU: Ambil Daftar Pembeli/Pemilik Tiket ----
        $stmtBuyers = $conn->prepare(
            "SELECT 
                oc.nama_lengkap, 
                oc.email, 
                oc.whatsapp, 
                tt.name AS ticket_type_name,
                o.order_date,
                o.order_id
             FROM order_customers oc
             JOIN orders o ON oc.order_id = o.order_id
             JOIN order_items oi ON o.order_id = oi.order_id
             JOIN ticket_types tt ON oi.ticket_type_id = tt.ticket_type_id
             WHERE tt.event_id = ? AND o.status = 'dibayar'
             ORDER BY o.order_date DESC, oc.nama_lengkap ASC"
        );
        if (!$stmtBuyers) throw new Exception("Prepare buyer list failed: " . $conn->error);
        $stmtBuyers->bind_param("i", $event_id);
        $stmtBuyers->execute();
        $resultBuyers = $stmtBuyers->get_result();
        $buyersList = [];
        while($rowBuyer = $resultBuyers->fetch_assoc()){
            $buyersList[] = $rowBuyer;
        }
        $stmtBuyers->close();
        $response['buyersList'] = $buyersList;

        $conn->commit(); // Commit jika semua query SELECT berhasil (meskipun tidak ada modifikasi)
        $response['success'] = true;
        $response['message'] = 'Data analitik berhasil diambil.';

    } catch (Exception $e) {
        $conn->rollback(); // Rollback jika ada error dalam transaksi
        $response['message'] = $e->getMessage();
        error_log("Get Event Analytics Error for event_id $event_id: " . $e->getMessage());
    }
    
}

if (isset($conn)) $conn->close();
echo json_encode($response);
?>