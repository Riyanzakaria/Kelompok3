<?php
session_start();
require_once 'db.php'; // File koneksi database Anda

header('Content-Type: application/json'); // Mengatur tipe konten output sebagai JSON

$BIAYA_LAYANAN = 7000; // Definisikan biaya layanan di sisi server

// Ambil data JSON yang dikirim dari JavaScript
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); // Ubah JSON menjadi array PHP

// Validasi input dasar
if (!$input || !isset($input['eventDetails'], $input['pemesan'], $input['pemilikTikets'])) {
    echo json_encode(['success' => false, 'message' => 'Data yang diterima tidak lengkap.']);
    exit;
}

// Periksa status login pengguna
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Anda harus login untuk melakukan pemesanan.']);
    exit;
}
$user_id = (int)$_SESSION['user_id'];

$eventDetails = $input['eventDetails'];
$dataPemesan = $input['pemesan']; // Data pemesan utama
$dataPemilikTikets = $input['pemilikTikets']; // Array data untuk setiap pemilik tiket

// Validasi lebih lanjut di sisi server
if (empty($eventDetails['eventId']) || !filter_var($eventDetails['eventId'], FILTER_VALIDATE_INT) ||
    empty($eventDetails['ticketTypeId']) || !filter_var($eventDetails['ticketTypeId'], FILTER_VALIDATE_INT) ||
    empty($eventDetails['quantity']) || !filter_var($eventDetails['quantity'], FILTER_VALIDATE_INT) || $eventDetails['quantity'] < 1) {
    echo json_encode(['success' => false, 'message' => 'Detail event atau tiket tidak valid.']);
    exit;
}

// Validasi data pemesan
if (empty(trim($dataPemesan['nama'])) || empty(trim($dataPemesan['email'])) || !filter_var(trim($dataPemesan['email']), FILTER_VALIDATE_EMAIL) || 
    empty(trim($dataPemesan['nomorIdentitas'])) || empty(trim($dataPemesan['noWhatsapp'])) || !preg_match('/^\+?[0-9\s-]{10,15}$/', $dataPemesan['noWhatsapp'])) {
    echo json_encode(['success' => false, 'message' => 'Data pemesan tidak lengkap atau format salah.']);
    exit;
}

if (count($dataPemilikTikets) != $eventDetails['quantity']) {
    echo json_encode(['success' => false, 'message' => 'Jumlah data pemilik tiket tidak sesuai dengan jumlah tiket yang dipesan.']);
    exit;
}

// Validasi setiap pemilik tiket
foreach ($dataPemilikTikets as $idx => $pemilik) {
    if (empty(trim($pemilik['namaLengkap'])) || empty(trim($pemilik['email'])) || !filter_var(trim($pemilik['email']), FILTER_VALIDATE_EMAIL) ||
        empty(trim($pemilik['nomorIdentitas'])) || empty(trim($pemilik['noWhatsapp'])) || !preg_match('/^\+?[0-9\s-]{10,15}$/', $pemilik['noWhatsapp'])) {
        echo json_encode(['success' => false, 'message' => 'Data untuk Pemilik Tiket ke-' . ($idx + 1) . ' tidak lengkap atau format salah.']);
        exit;
    }
}


// Mulai transaksi database
$conn->begin_transaction();

try {
    // 1. Cek stok tiket dan ambil harga asli dari database (untuk keamanan)
    $stmtCheckTicket = $conn->prepare("SELECT price, stock FROM ticket_types WHERE ticket_type_id = ? AND event_id = ? FOR UPDATE");
    if (!$stmtCheckTicket) {
        throw new Exception("Gagal mempersiapkan statement pengecekan tiket: " . $conn->error);
    }
    $stmtCheckTicket->bind_param("ii", $eventDetails['ticketTypeId'], $eventDetails['eventId']);
    $stmtCheckTicket->execute();
    $resultTicketInfo = $stmtCheckTicket->get_result();

    if ($resultTicketInfo->num_rows === 0) {
        $stmtCheckTicket->close();
        throw new Exception("Jenis tiket tidak valid atau tidak ditemukan untuk event ini.");
    }
    $ticketInfo = $resultTicketInfo->fetch_assoc();
    $stmtCheckTicket->close();

    if ($ticketInfo['stock'] < $eventDetails['quantity']) {
        throw new Exception("Stok tiket tidak mencukupi. Sisa stok saat ini: " . $ticketInfo['stock']);
    }
    
    // Gunakan harga dari DB untuk kalkulasi subtotal tiket
    $hargaSatuanDariDB = (float)$ticketInfo['price'];
    $ticketSubtotal = $hargaSatuanDariDB * (int)$eventDetails['quantity'];
    
    // Hitung jumlah total untuk order (subtotal tiket + biaya layanan)
    $jumlahTotalUntukOrder = $ticketSubtotal + $BIAYA_LAYANAN;


    // 2. Insert ke tabel 'orders'
    $orderStatus = 'pending'; // Status awal order
    $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, order_date, jumlah_total, status) VALUES (?, NOW(), ?, ?)");
    if (!$stmtOrder) {
        throw new Exception("Gagal mempersiapkan statement order: " . $conn->error);
    }
    $stmtOrder->bind_param("ids", $user_id, $jumlahTotalUntukOrder, $orderStatus);
    if (!$stmtOrder->execute()) {
        throw new Exception("Gagal menyimpan data order: " . $stmtOrder->error);
    }
    $order_id = $conn->insert_id; 
    $stmtOrder->close();

    // 3. Insert ke tabel 'order_items'
    $stmtOrderItem = $conn->prepare("INSERT INTO order_items (order_id, ticket_type_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    if (!$stmtOrderItem) {
        throw new Exception("Gagal mempersiapkan statement item order: " . $conn->error);
    }
    $stmtOrderItem->bind_param("iiid", $order_id, $eventDetails['ticketTypeId'], $eventDetails['quantity'], $ticketSubtotal);
    if (!$stmtOrderItem->execute()) {
        throw new Exception("Gagal menyimpan item order: " . $stmtOrderItem->error);
    }
    $stmtOrderItem->close();

    // 4. Insert data untuk setiap pemilik tiket ke tabel 'order_customers'
    // Skema terbaru tidak memiliki kolom alamat, provinsi, kota di order_customers
    $stmtCustomer = $conn->prepare("INSERT INTO order_customers (order_id, nama_lengkap, identity_type, identity_number, email, whatsapp) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmtCustomer) {
        throw new Exception("Gagal mempersiapkan statement customer order: " . $conn->error);
    }
    foreach ($dataPemilikTikets as $pemilik) {
        $stmtCustomer->bind_param("isssss", 
            $order_id, 
            $pemilik['namaLengkap'], 
            $pemilik['jenisIdentitas'], 
            $pemilik['nomorIdentitas'], 
            $pemilik['email'], 
            $pemilik['noWhatsapp']
        );
        if (!$stmtCustomer->execute()) {
            throw new Exception("Gagal menyimpan data pemilik tiket: " . $stmtCustomer->error);
        }
    }
    $stmtCustomer->close();

    // 5. Update stok tiket di tabel 'ticket_types'
    $newStock = $ticketInfo['stock'] - $eventDetails['quantity'];
    $newTicketStatus = ($newStock <= 0) ? 'sold_out' : 'available';
    $stmtUpdateStock = $conn->prepare("UPDATE ticket_types SET stock = ?, status = ? WHERE ticket_type_id = ?");
    if (!$stmtUpdateStock) {
        throw new Exception("Gagal mempersiapkan statement update stok: " . $conn->error);
    }
    $stmtUpdateStock->bind_param("isi", $newStock, $newTicketStatus, $eventDetails['ticketTypeId']);
    if (!$stmtUpdateStock->execute()) {
        throw new Exception("Gagal mengupdate stok tiket: " . $stmtUpdateStock->error);
    }
    $stmtUpdateStock->close();

    // Jika semua berhasil, commit transaksi
    $conn->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Pemesanan berhasil diproses.', 
        'order_id' => $order_id,
        'subtotal_tiket' => $ticketSubtotal,
        'biaya_layanan' => $BIAYA_LAYANAN,
        'total_pembayaran' => $jumlahTotalUntukOrder
    ]);

} catch (Exception $e) {
    // Jika ada error, rollback transaksi
    $conn->rollback();
    // Kirim pesan error yang lebih spesifik jika dalam mode debug, atau pesan generik untuk produksi
    error_log("Order processing error: " . $e->getMessage() . " - Input: " . $inputJSON); // Log error untuk admin
    echo json_encode(['success' => false, 'message' => "Terjadi kesalahan internal saat memproses pesanan Anda. Silakan coba beberapa saat lagi atau hubungi dukungan. (Error: " . $e->getMessage() . ")"]);
}

$conn->close();
?>