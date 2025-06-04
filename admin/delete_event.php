<?php
session_start();
// Pengecekan Sesi Admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
   header('Location: admin_login.php'); // Arahkan ke halaman login admin
   exit;
}

require_once '../db.php';
header('Content-Type: application/json');

// if (!isset($_SESSION['admin_id'])) {
//     echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
//     exit;
// }

$response = ['success' => false, 'message' => 'Permintaan tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = filter_var($_POST['eventId'] ?? null, FILTER_VALIDATE_INT);

    if (!$eventId) {
        $response['message'] = 'ID Event tidak valid.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Karena ada ON DELETE CASCADE dari events ke ticket_types,
        // dan dari ticket_types ke order_items,
        // menghapus event akan otomatis menghapus ticket_types dan order_items terkait.
        // Orders akan tetap ada, tetapi mungkin tidak memiliki item jika semua itemnya dari event ini.

        // Opsional: Cek dulu apakah event ada
        $stmtCheck = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
        $stmtCheck->bind_param("i", $eventId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if($resultCheck->num_rows === 0) {
            throw new Exception("Event dengan ID $eventId tidak ditemukan.");
        }
        $stmtCheck->close();

        // Hapus event
        $stmtDelete = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        if (!$stmtDelete) throw new Exception("Gagal mempersiapkan statement delete event: " . $conn->error);
        
        $stmtDelete->bind_param("i", $eventId);
        if (!$stmtDelete->execute()) throw new Exception("Gagal menghapus event: " . $stmtDelete->error);
        
        $stmtDelete->close();
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Event berhasil dihapus!';

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
        error_log("Delete event error: " . $e->getMessage());
    }
}

$conn->close();
echo json_encode($response);
?>