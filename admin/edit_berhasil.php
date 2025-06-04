<?php
// Aktifkan error reporting untuk debugging penuh (HAPUS ATAU KOMENTARI DI PRODUKSI)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Mulai output buffering SEGERA

session_start();
// Pengecekan Sesi Admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
   header('Location: admin_login.php'); // Arahkan ke halaman login admin
   exit;
}
require_once '../db.php'; // Path ke db.php

// Autentikasi Admin (contoh, sesuaikan dengan sistem Anda)
// if (!isset($_SESSION['admin_id'])) { 
//     if (ob_get_length()) ob_end_clean();
//     if (!headers_sent()) header('Content-Type: application/json');
//     echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai admin.']);
//     exit;
// }

$response = ['success' => false, 'message' => 'Permintaan tidak valid atau data tidak lengkap.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = filter_var($_POST['eventId'] ?? null, FILTER_VALIDATE_INT);

    // Validasi Event ID terlebih dahulu
    if (!$eventId) {
        $response['message'] = 'ID Event tidak valid untuk diedit.';
        if (ob_get_length())
            ob_end_clean();
        if (!headers_sent())
            header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Ambil dan bersihkan data event utama
    $eventName = trim($_POST['eventName'] ?? '');
    $eventDate = trim($_POST['eventDate'] ?? '');
    $eventLocation = trim($_POST['eventLocation'] ?? '');

    $eventTimePost = $_POST['eventTime'] ?? '';
    $eventTimeToSave = trim($eventTimePost) !== '' ? trim($eventTimePost) : null;

    $eventDescriptionPost = $_POST['eventDescription'] ?? '';
    $eventDescriptionToSave = trim($eventDescriptionPost) !== '' ? trim($eventDescriptionPost) : null;

    // Validasi dan pembersihan Status Event Utama
    $eventStatus = trim($_POST['eventStatus'] ?? ''); // Trim dulu
    $allowed_event_statuses = ['upcoming', 'ongoing', 'finished'];
    if (empty($eventStatus) || !in_array($eventStatus, $allowed_event_statuses)) {
        $response['message'] = 'Nilai status event utama tidak valid. Diterima: \'' . htmlspecialchars($eventStatus) . '\', diharapkan salah satu dari: upcoming, ongoing, finished.';
        if (ob_get_length())
            ob_end_clean();
        if (!headers_sent())
            header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Validasi field wajib lainnya untuk event utama
    if (empty($eventName) || empty($eventDate) || empty($eventLocation)) {
        $response['message'] = 'Data event utama (Nama, Tanggal, Lokasi) wajib diisi.';
        if (ob_get_length())
            ob_end_clean();
        if (!headers_sent())
            header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Ambil data tiket
    $ticketTypeIds_from_form = $_POST['ticket_type_ids'] ?? [];
    $ticketNames_from_form = $_POST['ticket_names'] ?? [];
    $ticketPrices_from_form = $_POST['ticket_prices'] ?? [];
    $ticketStocks_from_form = $_POST['ticket_stocks'] ?? [];
    $ticketStatuses_from_form = $_POST['ticket_statuses'] ?? [];

    // Validasi minimal satu jenis tiket dikirim
    if (empty($ticketNames_from_form)) {
        $response['message'] = 'Minimal harus ada satu jenis tiket.';
        if (ob_get_length())
            ob_end_clean();
        if (!headers_sent())
            header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    // Validasi bahwa semua array tiket memiliki jumlah elemen yang sama
    $numTicketForms = count($ticketNames_from_form);
    if (
        $numTicketForms !== count($ticketPrices_from_form) ||
        $numTicketForms !== count($ticketStocks_from_form) ||
        $numTicketForms !== count($ticketStatuses_from_form) ||
        $numTicketForms !== count($ticketTypeIds_from_form)
    ) {
        $response['message'] = 'Data jenis tiket tidak konsisten jumlahnya.';
        error_log("Edit event error: Inconsistent ticket array counts for event ID $eventId");
        if (ob_get_length())
            ob_end_clean();
        if (!headers_sent())
            header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Ambil nama gambar lama dari database
    $currentImageInDb = null;
    $stmtGetOldImage = $conn->prepare("SELECT image_url FROM events WHERE event_id = ?");
    if ($stmtGetOldImage) {
        $stmtGetOldImage->bind_param("i", $eventId);
        $stmtGetOldImage->execute();
        $resultOldImage = $stmtGetOldImage->get_result();
        if ($rowOldImage = $resultOldImage->fetch_assoc()) {
            $currentImageInDb = $rowOldImage['image_url'];
        }
        $stmtGetOldImage->close();
    } else {
        error_log("Gagal mengambil data gambar lama untuk event ID: $eventId - " . $conn->error);
    }

    $imageFileNameToSave = $currentImageInDb;
    $uploadMessage = '';
    $targetDir = dirname(__DIR__) . "/foto_event/";

    if (isset($_FILES['eventImageFile']) && $_FILES['eventImageFile']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['eventImageFile'];
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                $uploadMessage = " Peringatan: Gagal membuat direktori upload ($targetDir).";
                error_log($uploadMessage);
                // Jangan exit, biarkan proses update event berjalan dengan gambar lama jika ada
            }
        }
        if (is_dir($targetDir) && !is_writable($targetDir)) { // Cek writable hanya jika direktori ada
            $uploadMessage = " Peringatan: Direktori upload tidak bisa ditulis ($targetDir).";
            error_log('Upload directory not writable: ' . realpath($targetDir));
        }

        // Lanjutkan hanya jika direktori bisa ditulis (atau baru berhasil dibuat)
        if (is_dir($targetDir) && is_writable($targetDir)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
            finfo_close($finfo);
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($mimeType, $allowedMimeTypes)) {
                $fileExt = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExt, $allowedExtensions)) {
                    $newImageFileName = uniqid('event_', true) . '.' . $fileExt;
                    $destinationPath = $targetDir . $newImageFileName;
                    if (move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
                        if (!empty($currentImageInDb) && $currentImageInDb !== $newImageFileName && file_exists($targetDir . $currentImageInDb)) {
                            @unlink($targetDir . $currentImageInDb);
                        }
                        $imageFileNameToSave = $newImageFileName;
                        $uploadMessage .= " Gambar baru berhasil diupload.";
                    } else {
                        $uploadMessage .= " Peringatan: Gagal memindahkan gambar baru ke tujuan.";
                    }
                } else {
                    $uploadMessage .= " Peringatan: Ekstensi file gambar baru tidak diizinkan.";
                }
            } else {
                $uploadMessage .= " Peringatan: Format file gambar baru tidak diizinkan (MIME terdeteksi: $mimeType).";
            }
        }
    } elseif (isset($_FILES['eventImageFile']) && $_FILES['eventImageFile']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadMessage = " Peringatan: Error upload gambar baru (Code: " . $_FILES['eventImageFile']['error'] . ").";
    }

    $conn->begin_transaction();
    try {
        $stmtEvent = $conn->prepare("UPDATE events SET nama = ?, deskripsi = ?, event_date = ?, event_time = ?, lokasi = ?, image_url = ?, status = ? WHERE event_id = ?");
        if (!$stmtEvent)
            throw new Exception("DB Error (event update prepare): " . $conn->error);

        $stmtEvent->bind_param("sssssssi", $eventName, $eventDescriptionToSave, $eventDate, $eventTimeToSave, $eventLocation, $imageFileNameToSave, $eventStatus, $eventId);
        if (!$stmtEvent->execute()) {
            throw new Exception("DB Error (event update execute): " . $stmtEvent->error . " | Status dikirim: '" . $eventStatus . "'");
        }
        $stmtEvent->close();

        // --- BLOK PENGELOLAAN JENIS TIKET ---
        $existingTicketIdsInDb = [];
        $stmtGetExisting = $conn->prepare("SELECT ticket_type_id FROM ticket_types WHERE event_id = ?");
        if ($stmtGetExisting) {
            $stmtGetExisting->bind_param("i", $eventId);
            $stmtGetExisting->execute();
            $resultExisting = $stmtGetExisting->get_result();
            while ($row = $resultExisting->fetch_assoc()) {
                $existingTicketIdsInDb[] = (int) $row['ticket_type_id'];
            }
            $stmtGetExisting->close();
        } else {
            throw new Exception("DB Error (get existing tickets prepare): " . $conn->error);
        }

        $submittedAndValidTicketIds = [];

        $stmtUpdateTicket = $conn->prepare("UPDATE ticket_types SET name = ?, price = ?, stock = ?, status = ? WHERE ticket_type_id = ? AND event_id = ?");
        $stmtInsertTicket = $conn->prepare("INSERT INTO ticket_types (event_id, name, price, stock, status) VALUES (?, ?, ?, ?, ?)");
        if (!$stmtUpdateTicket || !$stmtInsertTicket)
            throw new Exception("DB Error (ticket prepare U/I): " . $conn->error);

        $allowed_ticket_statuses = ['available', 'sold_out'];

        for ($i = 0; $i < $numTicketForms; $i++) {
            $id_tiket_dari_form_str = trim($ticketTypeIds_from_form[$i] ?? '');
            $id = (!empty($id_tiket_dari_form_str) && filter_var($id_tiket_dari_form_str, FILTER_VALIDATE_INT)) ? (int) $id_tiket_dari_form_str : null;

            $name = trim($ticketNames_from_form[$i] ?? '');
            $price_str = $ticketPrices_from_form[$i] ?? null;
            $stock_str = $ticketStocks_from_form[$i] ?? null;
            $status_tiket_input = trim($ticketStatuses_from_form[$i] ?? '');

            $status_tiket_to_save = ''; // Inisialisasi
            if (!empty($status_tiket_input) && in_array($status_tiket_input, $allowed_ticket_statuses)) {
                $status_tiket_to_save = $status_tiket_input;
            } else {
                // Jika status kosong atau tidak valid, default ke 'available' atau throw error
                error_log("Status tiket tidak valid ('" . htmlspecialchars($status_tiket_input) . "') untuk tiket '" . htmlspecialchars($name) . "'. Default ke 'available'.");
                $status_tiket_to_save = 'available';
                // throw new Exception("Status ('".htmlspecialchars($status_tiket_input)."') untuk jenis tiket '".htmlspecialchars($name)."' tidak valid. Harus 'available' atau 'sold_out'.");
            }

            if (empty($name)) {
                // Jika nama kosong, lewati baris tiket ini, tapi jika tiket lama, pertahankan IDnya agar tidak dihapus jika hanya namanya yang dikosongkan
                if ($id)
                    $submittedAndValidTicketIds[] = $id;
                error_log("Nama jenis tiket ke-" . ($i + 1) . " kosong, baris dilewati.");
                continue;
            }
            $price = filter_var($price_str, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            if ($price === null || $price < 0) {
                throw new Exception("Harga untuk jenis tiket '" . htmlspecialchars($name) . "' tidak valid atau negatif.");
            }
            $stock = filter_var($stock_str, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            if ($stock === null || $stock < 0) {
                throw new Exception("Stok untuk jenis tiket '" . htmlspecialchars($name) . "' tidak valid atau negatif.");
            }

            if ($id && in_array($id, $existingTicketIdsInDb, true)) {
                $stmtUpdateTicket->bind_param("sdisii", $name, $price, $stock, $status_tiket_to_save, $id, $eventId);
                if (!$stmtUpdateTicket->execute())
                    throw new Exception("DB Error (update tiket ID " . $id . "): " . $stmtUpdateTicket->error);
                $submittedAndValidTicketIds[] = $id;
            } else {
                $stmtInsertTicket->bind_param("isdis", $eventId, $name, $price, $stock, $status_tiket_to_save);
                if (!$stmtInsertTicket->execute())
                    throw new Exception("DB Error (insert tiket baru): " . $stmtInsertTicket->error);
                $submittedAndValidTicketIds[] = $conn->insert_id;
            }
        }
        if (isset($stmtUpdateTicket))
            $stmtUpdateTicket->close();
        if (isset($stmtInsertTicket))
            $stmtInsertTicket->close();

        $idsToDelete = array_diff($existingTicketIdsInDb, $submittedAndValidTicketIds);
        if (!empty($idsToDelete)) {
            $stmtDeleteTicket = $conn->prepare("DELETE FROM ticket_types WHERE ticket_type_id = ? AND event_id = ?");
            if (!$stmtDeleteTicket)
                throw new Exception("DB Error (ticket delete prepare): " . $conn->error);
            foreach ($idsToDelete as $deleteId) {
                $checkSalesStmt = $conn->prepare("SELECT COUNT(*) as sales_count FROM order_items WHERE ticket_type_id = ?");
                if (!$checkSalesStmt)
                    throw new Exception("DB Error (check sales prepare): " . $conn->error);
                $checkSalesStmt->bind_param("i", $deleteId);
                $checkSalesStmt->execute();
                $salesResult = $checkSalesStmt->get_result()->fetch_assoc();
                $checkSalesStmt->close();

                if ($salesResult['sales_count'] == 0) {
                    $stmtDeleteTicket->bind_param("ii", $deleteId, $eventId);
                    if (!$stmtDeleteTicket->execute())
                        error_log("Gagal menghapus jenis tiket ID: $deleteId. " . $stmtDeleteTicket->error);
                } else {
                    error_log("Mencoba menghapus ticket_type_id: $deleteId (EventID: $eventId) yang memiliki riwayat penjualan. Penghapusan dibatalkan.");
                }
            }
            if (isset($stmtDeleteTicket))
                $stmtDeleteTicket->close();
        }
        // --- AKHIR BLOK PENGELOLAAN JENIS TIKET ---

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Event berhasil diupdate!' . $uploadMessage;

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Gagal: " . $e->getMessage() . $uploadMessage;
        error_log("Edit event error (event ID: $eventId): " . $e->getMessage() . " - POST Data: " . json_encode($_POST) . " - Files: " . json_encode($_FILES));
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

if (isset($conn))
    $conn->close();

// Pembersihan output buffer dan pengiriman JSON
if (ob_get_length())
    ob_end_clean();
if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode($response);
exit;
?>