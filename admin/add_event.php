<?php
session_start();
require_once '../db.php'; // Path ke db.php dari folder admin
header('Content-Type: application/json');

// Autentikasi Admin (contoh, sesuaikan dengan sistem Anda)
// if (!isset($_SESSION['admin_id'])) { 
//     echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai admin.']);
//     exit;
// }

$response = ['success' => false, 'message' => 'Permintaan tidak valid atau data tidak lengkap.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = trim($_POST['eventName'] ?? '');
    $eventDate = trim($_POST['eventDate'] ?? '');
    $eventTimePost = trim($_POST['eventTime'] ?? '');
    $eventLocation = trim($_POST['eventLocation'] ?? '');
    $eventDescriptionPost = trim($_POST['eventDescription'] ?? '');
    $eventStatus = trim($_POST['eventStatus'] ?? 'upcoming');

    $ticketNames = $_POST['ticket_names'] ?? [];
    $ticketPrices = $_POST['ticket_prices'] ?? [];
    $ticketStocks = $_POST['ticket_stocks'] ?? [];
    $ticketStatuses = $_POST['ticket_statuses'] ?? [];

    // Validasi data event utama
    if (empty($eventName) || empty($eventDate) || empty($eventLocation) || empty($eventStatus)) {
        $response['message'] = 'Data event utama (Nama, Tanggal, Lokasi, Status) wajib diisi.';
        echo json_encode($response);
        exit;
    }
    if (empty($ticketNames) || empty(trim($ticketNames[0]))) {
        $response['message'] = 'Minimal harus ada satu jenis tiket dengan nama yang valid.';
        echo json_encode($response);
        exit;
    }

    $eventTimeToSave = !empty($eventTimePost) ? $eventTimePost : null;
    $eventDescriptionToSave = !empty($eventDescriptionPost) ? $eventDescriptionPost : null;
    
    $imageFileName = null;
    $uploadMessage = '';
    $targetDir = dirname(__DIR__) . "/foto/"; // Path ke folder foto_event dari root proyek

    if (isset($_FILES['eventImageFile']) && $_FILES['eventImageFile']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['eventImageFile'];
        
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                $response['message'] = 'Gagal membuat direktori upload gambar: ' . $targetDir;
                echo json_encode($response);
                exit;
            }
        }
        if (!is_writable($targetDir)){
            $response['message'] = 'Direktori upload gambar tidak bisa ditulis: ' . $targetDir;
            error_log('Upload directory not writable: ' . $targetDir);
            echo json_encode($response);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($mimeType, $allowedMimeTypes)) {
            $fileExt = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExt, $allowedExtensions)) {
                $imageFileName = uniqid('event_', true) . '.' . $fileExt;
                $destinationPath = $targetDir . $imageFileName;
                if (!move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
                    $uploadMessage = " Peringatan: Gagal memindahkan gambar ke server.";
                    $imageFileName = null; 
                }
            } else {
                 $uploadMessage = " Peringatan: Ekstensi file gambar tidak diizinkan.";
                 $imageFileName = null; 
            }
        } else {
            $uploadMessage = " Peringatan: Format file gambar tidak diizinkan (MIME terdeteksi: $mimeType).";
            $imageFileName = null;
        }
    } elseif (isset($_FILES['eventImageFile']) && $_FILES['eventImageFile']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadMessage = " Peringatan: Terjadi kesalahan saat mengupload gambar (Error code: " . $_FILES['eventImageFile']['error'] . ").";
    }

    $conn->begin_transaction();
    try {
        $stmtEvent = $conn->prepare("INSERT INTO events (nama, deskripsi, event_date, event_time, lokasi, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmtEvent) throw new Exception("DB Error (event prepare): " . $conn->error);
        
        $stmtEvent->bind_param("sssssss", $eventName, $eventDescriptionToSave, $eventDate, $eventTimeToSave, $eventLocation, $imageFileName, $eventStatus);
        if (!$stmtEvent->execute()) {
            throw new Exception("DB Error (event execute): " . $stmtEvent->error . " | Status Value: " . $eventStatus);
        }
        
        $new_event_id = $conn->insert_id;
        $stmtEvent->close();

        if (!empty($ticketNames)) {
            $stmtTicket = $conn->prepare("INSERT INTO ticket_types (event_id, name, price, stock, status) VALUES (?, ?, ?, ?, ?)");
            if (!$stmtTicket) throw new Exception("DB Error (ticket prepare): " . $conn->error);

            for ($i = 0; $i < count($ticketNames); $i++) {
                $name = trim($ticketNames[$i] ?? '');
                $price = isset($ticketPrices[$i]) ? filter_var($ticketPrices[$i], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;
                $stock = isset($ticketStocks[$i]) ? filter_var($ticketStocks[$i], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) : null;
                $status = trim($ticketStatuses[$i] ?? 'available');

                if (empty($name) || $price === null || $stock === null) {
                    error_log("Data jenis tiket ke-" . ($i+1) . " tidak valid dan dilewati (Nama: $name, Harga: {$ticketPrices[$i]}, Stok: {$ticketStocks[$i]}).");
                    continue; 
                }
                $stmtTicket->bind_param("isdis", $new_event_id, $name, $price, $stock, $status);
                if (!$stmtTicket->execute()) throw new Exception("DB Error (ticket execute " . ($i+1) . "): " . $stmtTicket->error);
            }
            $stmtTicket->close();
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Event berhasil ditambahkan!' . $uploadMessage;

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Gagal: " . $e->getMessage() . $uploadMessage;
        error_log("Add event error: " . $e->getMessage());
    }
}

if (isset($conn)) $conn->close();
echo json_encode($response);
?><?php
session_start();
require_once '../db.php'; // Path ke db.php dari folder admin
header('Content-Type: application/json');

// Autentikasi Admin (contoh, sesuaikan dengan sistem Anda)
// if (!isset($_SESSION['admin_id'])) { 
//     echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai admin.']);
//     exit;
// }

$response = ['success' => false, 'message' => 'Permintaan tidak valid atau data tidak lengkap.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = trim($_POST['eventName'] ?? '');
    $eventDate = trim($_POST['eventDate'] ?? '');
    $eventTimePost = trim($_POST['eventTime'] ?? '');
    $eventLocation = trim($_POST['eventLocation'] ?? '');
    $eventDescriptionPost = trim($_POST['eventDescription'] ?? '');
    $eventStatus = trim($_POST['eventStatus'] ?? 'upcoming');

    $ticketNames = $_POST['ticket_names'] ?? [];
    $ticketPrices = $_POST['ticket_prices'] ?? [];
    $ticketStocks = $_POST['ticket_stocks'] ?? [];
    $ticketStatuses = $_POST['ticket_statuses'] ?? [];

    // Validasi data event utama
    if (empty($eventName) || empty($eventDate) || empty($eventLocation) || empty($eventStatus)) {
        $response['message'] = 'Data event utama (Nama, Tanggal, Lokasi, Status) wajib diisi.';
        echo json_encode($response);
        exit;
    }
    if (empty($ticketNames) || empty(trim($ticketNames[0]))) {
        $response['message'] = 'Minimal harus ada satu jenis tiket dengan nama yang valid.';
        echo json_encode($response);
        exit;
    }

    $eventTimeToSave = !empty($eventTimePost) ? $eventTimePost : null;
    $eventDescriptionToSave = !empty($eventDescriptionPost) ? $eventDescriptionPost : null;
    
    $imageFileName = null;
    $uploadMessage = '';
    $targetDir = dirname(__DIR__) . "/foto/"; // Path ke folder foto_event dari root proyek

    if (isset($_FILES['eventImageFile']) && $_FILES['eventImageFile']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['eventImageFile'];
        
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                $response['message'] = 'Gagal membuat direktori upload gambar: ' . $targetDir;
                echo json_encode($response);
                exit;
            }
        }
        if (!is_writable($targetDir)){
            $response['message'] = 'Direktori upload gambar tidak bisa ditulis: ' . $targetDir;
            error_log('Upload directory not writable: ' . $targetDir);
            echo json_encode($response);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($mimeType, $allowedMimeTypes)) {
            $fileExt = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExt, $allowedExtensions)) {
                $imageFileName = uniqid('event_', true) . '.' . $fileExt;
                $destinationPath = $targetDir . $imageFileName;
                if (!move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
                    $uploadMessage = " Peringatan: Gagal memindahkan gambar ke server.";
                    $imageFileName = null; 
                }
            } else {
                 $uploadMessage = " Peringatan: Ekstensi file gambar tidak diizinkan.";
                 $imageFileName = null; 
            }
        } else {
            $uploadMessage = " Peringatan: Format file gambar tidak diizinkan (MIME terdeteksi: $mimeType).";
            $imageFileName = null;
        }
    } elseif (isset($_FILES['eventImageFile']) && $_FILES['eventImageFile']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadMessage = " Peringatan: Terjadi kesalahan saat mengupload gambar (Error code: " . $_FILES['eventImageFile']['error'] . ").";
    }

    $conn->begin_transaction();
    try {
        $stmtEvent = $conn->prepare("INSERT INTO events (nama, deskripsi, event_date, event_time, lokasi, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmtEvent) throw new Exception("DB Error (event prepare): " . $conn->error);
        
        $stmtEvent->bind_param("sssssss", $eventName, $eventDescriptionToSave, $eventDate, $eventTimeToSave, $eventLocation, $imageFileName, $eventStatus);
        if (!$stmtEvent->execute()) {
            throw new Exception("DB Error (event execute): " . $stmtEvent->error . " | Status Value: " . $eventStatus);
        }
        
        $new_event_id = $conn->insert_id;
        $stmtEvent->close();

        if (!empty($ticketNames)) {
            $stmtTicket = $conn->prepare("INSERT INTO ticket_types (event_id, name, price, stock, status) VALUES (?, ?, ?, ?, ?)");
            if (!$stmtTicket) throw new Exception("DB Error (ticket prepare): " . $conn->error);

            for ($i = 0; $i < count($ticketNames); $i++) {
                $name = trim($ticketNames[$i] ?? '');
                $price = isset($ticketPrices[$i]) ? filter_var($ticketPrices[$i], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;
                $stock = isset($ticketStocks[$i]) ? filter_var($ticketStocks[$i], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) : null;
                $status = trim($ticketStatuses[$i] ?? 'available');

                if (empty($name) || $price === null || $stock === null) {
                    error_log("Data jenis tiket ke-" . ($i+1) . " tidak valid dan dilewati (Nama: $name, Harga: {$ticketPrices[$i]}, Stok: {$ticketStocks[$i]}).");
                    continue; 
                }
                $stmtTicket->bind_param("isdis", $new_event_id, $name, $price, $stock, $status);
                if (!$stmtTicket->execute()) throw new Exception("DB Error (ticket execute " . ($i+1) . "): " . $stmtTicket->error);
            }
            $stmtTicket->close();
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Event berhasil ditambahkan!' . $uploadMessage;

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Gagal: " . $e->getMessage() . $uploadMessage;
        error_log("Add event error: " . $e->getMessage());
    }
}

if (isset($conn)) $conn->close();
echo json_encode($response);
?>