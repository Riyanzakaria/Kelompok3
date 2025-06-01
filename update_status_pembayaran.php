<?php
session_start();
require_once 'db.php'; // File koneksi database Anda

header('Content-Type: application/json');

// Periksa status login pengguna
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Anda harus login.']);
    exit;
}

// Ambil data JSON yang dikirim dari JavaScript
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (!$input || !isset($input['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap (order_id tidak ada).']);
    exit;
}

$order_id = filter_var($input['order_id'], FILTER_VALIDATE_INT);
$user_id_session = (int)$_SESSION['user_id'];

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak valid.']);
    exit;
}

$conn->begin_transaction();

try {
    // Pastikan order tersebut milik user yang sedang login dan statusnya 'pending'
    $stmtCheckOrder = $conn->prepare("SELECT status, user_id FROM orders WHERE order_id = ?");
    if(!$stmtCheckOrder) throw new Exception("Prepare statement gagal: " . $conn->error);
    
    $stmtCheckOrder->bind_param("i", $order_id);
    $stmtCheckOrder->execute();
    $resultOrder = $stmtCheckOrder->get_result();
    
    if ($resultOrder->num_rows === 0) {
        $stmtCheckOrder->close();
        throw new Exception("Pesanan tidak ditemukan.");
    }
    
    $currentOrder = $resultOrder->fetch_assoc();
    $stmtCheckOrder->close();

    if ($currentOrder['user_id'] !== $user_id_session) {
        throw new Exception("Anda tidak memiliki hak untuk mengubah status pesanan ini.");
    }

    if ($currentOrder['status'] !== 'pending') {
        throw new Exception("Status pesanan ini sudah bukan 'pending' (saat ini: " . $currentOrder['status'] . "). Tidak dapat diubah.");
    }

    // Update status order menjadi 'dibayar'
    // Untuk sistem nyata, status mungkin 'menunggu_konfirmasi_admin' jika perlu verifikasi manual
    $newStatus = 'dibayar';
    $stmtUpdate = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ? AND user_id = ? AND status = 'pending'");
    if(!$stmtUpdate) throw new Exception("Prepare statement update gagal: " . $conn->error);

    $stmtUpdate->bind_param("sii", $newStatus, $order_id, $user_id_session);
    
    if ($stmtUpdate->execute()) {
        if ($stmtUpdate->affected_rows > 0) {
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Status pembayaran berhasil diperbarui menjadi "Dibayar".']);
        } else {
            // Bisa jadi status sudah berubah oleh proses lain, atau order tidak ditemukan dengan kriteria tsb.
            $conn->rollback(); // Meskipun mungkin tidak ada yang diubah, untuk konsistensi
            echo json_encode(['success' => false, 'message' => 'Tidak ada pesanan yang diperbarui. Status mungkin sudah berubah atau order tidak memenuhi kriteria.']);
        }
    } else {
        throw new Exception("Gagal mengupdate status pesanan: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Payment status update error for order_id $order_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>