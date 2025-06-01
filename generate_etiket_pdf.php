<?php
// Aktifkan error reporting untuk debugging penuh (HAPUS ATAU KOMENTARI DI PRODUKSI)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php'; 

// 0. Cek ekstensi PHP yang dibutuhkan
if (!extension_loaded('gd')) { // FPDF mungkin memerlukan GD untuk beberapa operasi gambar dasar atau font
    error_log("Ekstensi PHP 'gd' tidak aktif atau tidak terinstal.");
    // die("KRITIS: Ekstensi PHP 'gd' tidak aktif. Beberapa fitur PDF mungkin tidak berfungsi.");
}



// 1. PASTIKAN PATH KE AUTOLOAD.PHP BENAR
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("KRITIS: vendor/autoload.php tidak ditemukan. Jalankan 'composer install'. Path: " . __DIR__ . "/vendor/autoload.php");
}

// 2. PASTIKAN PATH KE FPDF.PHP BENAR
if (file_exists(__DIR__ . '/fpdf/fpdf.php')) {
    require_once(__DIR__ . '/fpdf/fpdf.php');
} else {
    die("KRITIS: fpdf/fpdf.php tidak ditemukan. Pastikan pustaka FPDF ada di folder 'fpdf'. Path: " . __DIR__ . "/fpdf/fpdf.php");
}

// 3. GUNAKAN NAMESPACE UNTUK BACONQRCODE DENGAN IMAGICK
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\PNGImageBackEnd; // DIUBAH ke PNGImageBackEnd
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
// use BaconQrCode\Common\ErrorCorrectionLevel; // Aktifkan jika Anda ingin mengatur level ini secara manual

// Fungsi helper
function formatDateIndo($dateStr) {
    if (!$dateStr) return "-";
    try {
        $date = new DateTime($dateStr);
        return $date->format('d F Y'); 
    } catch (Exception $e) { return "Format tanggal salah"; }
}

function formatTimeIndoPdf($timeStr) {
    if (!$timeStr) return "";
    try {
        $time = new DateTime($timeStr);
        return $time->format('H:i');
    } catch (Exception $e) { return ""; }
}

// Periksa login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Anda harus login.");
}

// Validasi parameter oc_id
if (!isset($_GET['oc_id']) || !filter_var($_GET['oc_id'], FILTER_VALIDATE_INT) || (int)$_GET['oc_id'] <= 0) {
    die("Parameter ID tiket pelanggan tidak valid atau hilang.");
}
$order_customer_id = (int)$_GET['oc_id'];
$user_id_session = (int)$_SESSION['user_id'];
$ticketData = null;

// Ambil data tiket
$stmt = $conn->prepare(
    "SELECT 
        oc.nama_lengkap AS ticket_holder_name, oc.order_customer_id,
        o.order_id, o.user_id AS order_user_id,
        e.nama AS event_name, e.event_date, e.event_time, e.lokasi AS event_location, e.image_url AS event_banner_image,
        tt.name AS ticket_type_name,
        oi.quantity AS total_tickets_for_item
     FROM order_customers oc
     JOIN orders o ON oc.order_id = o.order_id
     JOIN order_items oi ON o.order_id = oi.order_id
     JOIN ticket_types tt ON oi.ticket_type_id = tt.ticket_type_id
     JOIN events e ON tt.event_id = e.event_id
     WHERE oc.order_customer_id = ? AND o.user_id = ?"
);

if ($stmt) {
    $stmt->bind_param("ii", $order_customer_id, $user_id_session);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $ticketData = $result->fetch_assoc();
    }
    $stmt->close();
} else {
    error_log("PDF Generation Error - DB Prepare Statement Failed for main ticket data: " . $conn->error);
    die("Gagal mempersiapkan pengambilan data tiket (1). Silakan coba lagi.");
}

if (!$ticketData) {
    $checkOrderExistsStmt = $conn->prepare("SELECT o.order_id, o.user_id FROM order_customers oc JOIN orders o ON oc.order_id = o.order_id WHERE oc.order_customer_id = ?");
    if ($checkOrderExistsStmt) {
        $checkOrderExistsStmt->bind_param("i", $order_customer_id);
        $checkOrderExistsStmt->execute();
        $checkResult = $checkOrderExistsStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $orderCheckData = $checkResult->fetch_assoc();
            if ($orderCheckData['user_id'] != $user_id_session) {
                die("Akses ditolak. Tiket ini bukan milik Anda.");
            } else {
                die("Data tiket lengkap tidak ditemukan meskipun pesanan ada. Hubungi administrator.");
            }
        } else {
            die("E-Tiket dengan ID pelanggan " . htmlspecialchars($order_customer_id) . " tidak ditemukan.");
        }
        $checkOrderExistsStmt->close();
    } else {
         die("E-Tiket tidak ditemukan atau Anda tidak memiliki akses untuk tiket ini (Error Cek DB).");
    }
     $conn->close(); 
     exit; 
}

// Dapatkan urutan tiket
$stmtSeq = $conn->prepare("SELECT order_customer_id FROM order_customers WHERE order_id = ? ORDER BY order_customer_id ASC");
$ticket_sequence_in_order = 0;
$total_tickets_in_order = 0; 
if ($stmtSeq) {
    $stmtSeq->bind_param("i", $ticketData['order_id']);
    $stmtSeq->execute();
    $resultSeq = $stmtSeq->get_result();
    $customer_ids_array = [];
    while($rowSeq = $resultSeq->fetch_assoc()) {
        $customer_ids_array[] = $rowSeq['order_customer_id'];
    }
    $total_tickets_in_order = count($customer_ids_array);
    $search_result = array_search($order_customer_id, $customer_ids_array);
    if ($search_result !== false) {
        $ticket_sequence_in_order = $search_result + 1;
    }
    $stmtSeq->close();
} else {
     error_log("PDF Generation Error - DB Prepare Statement Failed for ticket sequence: " . $conn->error);
}

if ($total_tickets_in_order == 0 && isset($ticketData['total_tickets_for_item'])) {
    $total_tickets_in_order = $ticketData['total_tickets_for_item'];
}
$conn->close(); 

ob_start(); 

class PDF extends FPDF {
    function TicketDetailCell($label, $value, $label_width = 30, $is_multiline = false) {
        $current_y_before_cell = $this->GetY();
        $this->SetFont('Arial','B',8);
        $this->Cell($label_width, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', htmlspecialchars_decode($label)), 0, 0, 'L');
        $this->SetFont('Arial','',8);
        $x_after_label = $this->GetX();
        if ($is_multiline) {
            $this->SetX($x_after_label);
            $this->MultiCell($this->GetPageWidth() - $this->lMargin - $this->rMargin - $label_width - 2, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', htmlspecialchars_decode($value)), 0, 'L');
        } else {
            $this->SetX($x_after_label);
            $this->Cell($this->GetPageWidth() - $this->lMargin - $this->rMargin - $label_width -2 , 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', htmlspecialchars_decode($value)), 0, 1, 'L');
        }
        if (!$is_multiline && strlen(trim(htmlspecialchars_decode($value))) == 0) {
             $this->SetY($current_y_before_cell + 5);
        }
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
// JANGAN panggil AddPage() di sini dulu jika banner akan mengisi halaman pertama secara penuh

// MARGIN YANG DISET (digunakan nanti setelah banner atau di halaman baru)
$margin_left = 10;
$margin_right = 10;
$margin_top = 10; 
$margin_bottom = 10;

// --- HALAMAN PERTAMA (KHUSUS BANNER JIKA PERLU) ---
$pdf->AddPage(); // Tambahkan halaman pertama
$pdf->SetMargins($margin_left, $margin_top, $margin_right); // Set margin untuk halaman ini jika banner tidak full
$pdf->SetAutoPageBreak(false); // Nonaktifkan auto page break sementara untuk banner full page

// 1. Event Banner
$banner_image_name = $ticketData['event_banner_image'] ?? 'default_banner.jpg';
$bannerPath = 'foto/' . $banner_image_name;
$pageWidth = $pdf->GetPageWidth(); 

if (file_exists($bannerPath)) {
    list($imgWidth, $imgHeight) = @getimagesize($bannerPath);
    if ($imgWidth && $imgHeight && $imgWidth > 0) {
        // Skalakan gambar agar sesuai dengan lebar halaman, pertahankan rasio aspek
        $bannerDisplayHeight = ($imgHeight / $imgWidth) * $pageWidth;
        // Jika ingin banner mengisi seluruh tinggi halaman pertama (misalnya 297mm untuk A4), 
        // Anda mungkin perlu cropping atau gambar dengan rasio yang sangat spesifik.
        // Untuk contoh ini, kita biarkan tingginya proporsional dengan lebar penuh.
        $pdf->Image($bannerPath, 0, 0, $pageWidth, $bannerDisplayHeight); 
    } else { 
        // Fallback jika gambar banner tidak valid
        $pdf->SetFillColor(220,220,220); 
        $pdf->Rect(0, 0, $pageWidth, $pdf->GetPageHeight(),'F'); // Placeholder abu-abu full page
        $pdf->SetXY($margin_left, $pdf->GetPageHeight()/2 - 10); 
        $pdf->SetFont('Arial','B',16);
        $eventNameForBanner = $ticketData['event_name'] ?? 'Nama Event Tidak Ada';
        $pdf->Cell($pageWidth - $margin_left - $margin_right,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',htmlspecialchars_decode($eventNameForBanner)),0,1,'C');
        error_log("Dimensi gambar banner tidak valid atau nol: " . $bannerPath);
    }
} else {
    // Placeholder jika file banner tidak ada
    $pdf->SetFillColor(220,220,220); 
    $pdf->Rect(0, 0, $pageWidth, $pdf->GetPageHeight(),'F'); // Placeholder abu-abu full page
    $pdf->SetXY($margin_left, $pdf->GetPageHeight()/2 - 10); 
    $pdf->SetFont('Arial','B',16);
    $eventNameForBanner = $ticketData['event_name'] ?? 'Nama Event Tidak Ada';
    $pdf->Cell($pageWidth - $margin_left - $margin_right,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',htmlspecialchars_decode($eventNameForBanner)),0,1,'C');
}

// --- HALAMAN KEDUA (UNTUK DETAIL EVENT DAN SELANJUTNYA) ---
$pdf->AddPage(); // Tambahkan halaman baru untuk semua konten setelah banner
$pdf->SetMargins($margin_left, $margin_top, $margin_right); // Set margin normal untuk halaman ini
$pdf->SetAutoPageBreak(true, $margin_bottom); // Aktifkan kembali auto page break
$pdf->SetY($margin_top); // Set posisi Y ke margin atas

// 2. Informasi Event Utama (sekarang di halaman kedua)
$pdf->SetFont('Arial','B',18); $pdf->SetTextColor(0,0,0);
$pdf->MultiCell(0,8,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',strtoupper(htmlspecialchars_decode($ticketData['event_name']))),0,'C');
$pdf->Ln(2);
$pdf->SetFont('Arial','',10);
$eventDateStr = formatDateIndo($ticketData['event_date']);
$eventTimeStr = formatTimeIndoPdf($ticketData['event_time']);
$pdf->Cell(0,6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$eventDateStr . ($eventTimeStr ? ' | Pukul ' . $eventTimeStr : '')),0,1,'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',htmlspecialchars_decode($ticketData['event_location'])),0,1,'C');
$pdf->Ln(8);

// 3. Detail Tiket dan QR Code
$currentY = $pdf->GetY();
$printableWidth = $pdf->GetPageWidth() - $margin_left - $margin_right;
$spacingBetweenColumns = 5; 
$columnWidth = ($printableWidth - $spacingBetweenColumns) / 2;
$rightColumnX = $margin_left + $columnWidth + $spacingBetweenColumns; 

// Kolom Kiri: Detail Tiket
$pdf->SetXY($margin_left, $currentY);
$pdf->TicketDetailCell('Lokasi:', htmlspecialchars_decode($ticketData['event_location']), 30, true);
$pdf->TicketDetailCell('Order ID:', ($ticketData['order_id'] ? htmlspecialchars_decode($ticketData['order_id']) : '-'), 30);
$pdf->TicketDetailCell('Tanggal Event:', $eventDateStr, 30);
$pdf->TicketDetailCell('Nama:', htmlspecialchars_decode($ticketData['ticket_holder_name']), 30);

$order_id_for_code = isset($ticketData['order_id']) && is_numeric($ticketData['order_id']) ? (int)$ticketData['order_id'] : 0;
$oc_id_for_code = isset($ticketData['order_customer_id']) && is_numeric($ticketData['order_customer_id']) ? (int)$ticketData['order_customer_id'] : 0;
$uniqueTicketCode = 'HARMONIX-' . strtoupper(dechex($order_id_for_code)) . '-' . strtoupper(dechex($oc_id_for_code)) . '-' . substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6);
$pdf->TicketDetailCell('Kode Tiket:', $uniqueTicketCode, 30);
if ($eventTimeStr) { $pdf->TicketDetailCell('Waktu:', $eventTimeStr, 30); }
$pdf->TicketDetailCell('Kategori Tiket:', htmlspecialchars_decode($ticketData['ticket_type_name']), 30);
$yAfterLeftColumn = $pdf->GetY();

// Kolom Kanan: QR Code
$pdf->SetXY($rightColumnX, $currentY); 
$qrData = json_encode([ 
    'app' => 'HarmonixTicket', 'version' => '1.0', 'ticket_code' => $uniqueTicketCode,
    'order_id' => $ticketData['order_id'], 'customer_id' => $ticketData['order_customer_id'],
    'event_name' => $ticketData['event_name'], 'holder_name' => $ticketData['ticket_holder_name']
]);
$safeUniqueTicketCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $uniqueTicketCode);
$qrCodeFileName = 'qr_' . $safeUniqueTicketCode . '.png';
$qrCodeFilePath = 'temp_qrcodes/' . $qrCodeFileName; 

if (!is_dir('temp_qrcodes')) { 
    if (!mkdir('temp_qrcodes', 0777, true) && !is_dir('temp_qrcodes')) {
        error_log("Gagal membuat direktori temp_qrcodes.");
    }
}

$qrImageWidthInMm = 0; 
try {
    if (is_writable('temp_qrcodes')) {
        $renderer = new ImageRenderer(
            new RendererStyle(280, 5), // Ukuran pixel QR, margin QR
            new PNGImageBackEnd('png') // DIUBAH KE IMAGICK
        );
        $writer = new Writer($renderer);
        $writer->writeFile($qrData, $qrCodeFilePath);
        
        $qrImageWidthInMm = 40; 
        $qrImageXPos = $rightColumnX + ($columnWidth - $qrImageWidthInMm) / 2;
        if(file_exists($qrCodeFilePath)) {
            $pdf->Image($qrCodeFilePath, $qrImageXPos, $pdf->GetY(), $qrImageWidthInMm, $qrImageWidthInMm); 
            if (file_exists($qrCodeFilePath)) { // Hapus setelah digunakan jika berhasil
                 unlink($qrCodeFilePath); // Aktifkan jika ingin menghapus file QR setelah PDF dibuat
            }
        } else { 
            throw new Exception("File QR code tidak berhasil dibuat atau tidak ditemukan di: " . $qrCodeFilePath); 
        }
    } else { 
        throw new Exception("Direktori temp_qrcodes tidak bisa ditulis oleh server PHP. Path: " . realpath('temp_qrcodes')); 
    }
} catch (Throwable $e) { 
    error_log("QR Code generation failed (BaconQrCode with Imagick) for ticket " . $uniqueTicketCode . ": " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
    $pdf->SetFont('Arial','B',9); $pdf->SetX($rightColumnX);
    $pdf->Cell($columnWidth, 10, 'Gagal membuat QR Code', 0, 1, 'C');
    $qrImageWidthInMm = 0; 
}
$yPosQrBlock = $pdf->GetY(); 
$yAfterRightColumn = ($qrImageWidthInMm > 0) ? ($currentY + $qrImageWidthInMm + 5) : $yPosQrBlock;

$nextSectionY = max($yAfterLeftColumn, $yAfterRightColumn) + 5;
$pdf->SetXY($margin_left, $nextSectionY);

// 4. Informasi Tiket
$pdf->SetFont('Arial','B',9); $pdf->Cell(0,6,'Informasi Tiket',0,1,'L');
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(0,5,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
    "- Tunjukkan e-Tiket/QR Code yang telah diterima kepada panitia di lokasi Event.\n".
    "- Pemilik tiket WAJIB menunjukkan Kartu Identitas (KTP/Paspor/SIM) yang telah terdaftar untuk verifikasi data diri.\n".
    "- Setelah sudah terverifikasi, Pemilik tiket dapat memasuki Event.\n".
    "- Pengunjung WAJIB untuk mematuhi aturan yang berlaku selama acara berlangsung."
),0,'L');
$pdf->Ln(2);
if ($total_tickets_in_order > 0 && $ticket_sequence_in_order > 0) {
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Tiket " . $ticket_sequence_in_order . " dari " . $total_tickets_in_order), 0, 1, 'R');
}

// 5. Footer
$yBeforeFooter = $pdf->GetY();
if ($pdf->GetPageHeight() - $margin_bottom - $yBeforeFooter < 15) {
    $pdf->AddPage(); $pdf->SetY($margin_top);
} else { $pdf->Ln(5); }
$pdf->SetFont('Arial','I',8); $pdf->Cell(0, 10, 'Ticket Available at Harmonix', 0, 0, 'R');

// 6. Syarat dan Ketentuan
$pdf->AddPage(); $pdf->SetY($margin_top); 
$pdf->SetFont('Arial','B',11); $pdf->SetFillColor(230,230,230);
$pdf->Cell(0,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT','SYARAT DAN KETENTUAN (TERMS AND CONDITIONS)'),0,1,'C', true);
$pdf->Ln(5); $pdf->SetFont('Arial','',8);
$syaratDanKetentuan = "1. Entry Pass yang valid adalah yang dibeli melalui kanal resmi penjualan tiket acara ini.\n2. Satu Entry Pass berlaku untuk satu orang.\n3. Panitia dan Promotor tidak bertanggungjawab/tidak ada penggantian kerugian atas pembelian tiket acara melalui calo/tempat/kanal/platform/yang bukan mitra resmi penjualan tiket.\n4. Tiket yang hilang/dicuri tidak akan diganti atau diterbitkan ulang, meskipun anda memiliki bukti pembelian. Tiket adalah tanggung jawab pembeli.\n5. Panitia acara, Promotor, dan Pengisi Acara tidak bertanggung jawab atas biaya transportasi atau akomodasi yang telah dikeluarkan penonton untuk mengunjungi acara jika seandainya acara harus dibatalkan atau dipindahkan ke hari dan/atau waktu lain.\n6. Dalam keadaan kahar seperti bencana alam, kerusuhan, perang, wabah, dan semua keadaan darurat yang diumumkan secara resmi oleh Pemerintah, Panitia/penyelenggara/promotor berhak untuk membatalkan dan/atau merubah waktu acara dan tata letak tempat tanpa pemberitahuan sebelumnya.\n7. Dilarang membawa makanan dan minuman dari luar, minuman beralkohol, obat-obatan terlarang, senjata tajam/api, bahan peledak, dan benda-benda berbahaya lainnya.\n8. Pihak promotor/penyelenggara acara berhak mengambil, menyita dan tidak mengembalikan kepada penonton jika ditemukannya barang terlarang.\n9. Dilarang membuat kerusuhan dalam situasi apapun di dalam area venue.\n10. Saya telah membaca dan memahami syarat dan ketentuan pembelian dan penggunaan entry pass di atas. Dan jika ada perubahan aturan promotor, akan segera diinformasikan di akun media sosial promotor dan saya memberikan persetujuan saya untuk dikontrakkan secara hukum dengan syarat dan ketentuan.";
$pdf->MultiCell(0,4,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$syaratDanKetentuan),0,'L');

$event_name_clean = $ticketData['event_name'] ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $ticketData['event_name']) : 'Event';
$holder_name_clean = $ticketData['ticket_holder_name'] ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $ticketData['ticket_holder_name']) : 'TicketHolder';
$pdfFileName = 'E-Tiket_' . $event_name_clean . '_' . $holder_name_clean . '.pdf';

if (ob_get_length()) ob_end_clean(); 
$pdf->Output('I', $pdfFileName); 
exit;
?>