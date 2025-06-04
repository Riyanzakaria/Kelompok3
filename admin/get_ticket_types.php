<?php
session_start();
// Pengecekan Sesi Admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
   header('Location: admin_login.php'); // Arahkan ke halaman login admin
   exit;
}
require_once '../db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => 'Event ID tidak valid atau tidak ditemukan.'];

if (isset($_GET['event_id']) && filter_var($_GET['event_id'], FILTER_VALIDATE_INT)) {
    $event_id = (int)$_GET['event_id'];

    $stmt = $conn->prepare("SELECT ticket_type_id, name, price, stock, status FROM ticket_types WHERE event_id = ? ORDER BY ticket_type_id ASC");
    if ($stmt) {
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tickets = [];
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row; // Semua data tiket yang relevan diambil
        }
        $stmt->close();
        $response['success'] = true;
        $response['data'] = $tickets;
        $response['message'] = count($tickets) > 0 ? 'Data tiket berhasil diambil.' : 'Tidak ada jenis tiket untuk event ini.';
    } else {
        $response['message'] = 'Gagal mempersiapkan query pengambilan tiket: ' . $conn->error;
        error_log('Error prepare get_ticket_types: ' . $conn->error);
    }
}

$conn->close();
echo json_encode($response);
?>