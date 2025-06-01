<?php
session_start(); // Pindahkan session_start() ke paling atas
require_once 'db.php'; // File koneksi database Anda

header('Content-Type: application/json'); // Mengatur output sebagai JSON

$response = ['success' => false, 'message' => 'Terjadi kesalahan yang tidak diketahui.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $phoneNumber = trim($_POST['phoneNumber'] ?? '');
    // $birthdate = trim($_POST['birthdate'] ?? ''); // Tetap dikomentari karena tidak ada di DB
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['passwordConfirm'] ?? '';

    // Validasi sisi server (sudah cukup baik, bisa ditambahkan validasi lain jika perlu)
    if (empty($username) || empty($phoneNumber) || empty($email) || empty($password) || empty($passwordConfirm)) {
        $response['message'] = 'Semua field wajib diisi.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Format email tidak valid.';
        echo json_encode($response);
        exit;
    }

    if (!preg_match('/^08[0-9]{8,11}$/', $phoneNumber)) {
        $response['message'] = 'Format nomor telepon tidak valid (contoh: 081234567890, 10-13 digit).';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($password) < 8) {
        $response['message'] = 'Password minimal harus 8 karakter.';
        echo json_encode($response);
        exit;
    }

    if ($password !== $passwordConfirm) {
        $response['message'] = 'Konfirmasi password tidak cocok.';
        echo json_encode($response);
        exit;
    }

    // Cek apakah email atau username sudah ada
    $stmtCheck = $conn->prepare("SELECT user_id, email, name FROM users WHERE email = ? OR name = ?"); // Ambil juga email dan name untuk pesan error lebih spesifik
    if (!$stmtCheck) {
        $response['message'] = "Database error (prepare check): " . $conn->error;
        error_log($response['message']); // Log error
        echo json_encode($response);
        exit;
    }
    $stmtCheck->bind_param("ss", $email, $username);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $existingUser = $resultCheck->fetch_assoc();
        if ($existingUser['email'] === $email) { // Perbandingan case-sensitive, DB mungkin case-insensitive tergantung collation
             $response['message'] = 'Email sudah terdaftar. Silakan gunakan email lain.';
        } else if ($existingUser['name'] === $username) { // Perbandingan case-sensitive
             $response['message'] = 'Username sudah digunakan. Silakan pilih username lain.';
        } else {
            // Jika query menemukan baris tapi fieldnya tidak sama persis (misal karena case-insensitive di DB)
            $response['message'] = 'Email atau username mungkin sudah digunakan.';
        }
        $stmtCheck->close();
        echo json_encode($response);
        exit;
    }
    $stmtCheck->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert pengguna baru
    $stmtInsert = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
     if (!$stmtInsert) {
        $response['message'] = "Database error (prepare insert): " . $conn->error;
        error_log($response['message']); // Log error
        echo json_encode($response);
        exit;
    }
    $stmtInsert->bind_param("ssss", $username, $email, $hashedPassword, $phoneNumber);

    if ($stmtInsert->execute()) {
        $response['success'] = true;
        // Pilihan apakah mau auto-login atau hanya pesan sukses
        // Jika auto-login:
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_phone'] = $phoneNumber; // Simpan juga phone jika perlu
        $response['message'] = 'Registrasi berhasil! Anda akan diarahkan ke dashboard.';
        $response['redirect_url'] = 'dashboard.php'; 
        // Jika hanya pesan sukses dan redirect ke login:
        // $response['message'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
        // $response['redirect_url'] = 'login.php';
    } else {
        $response['message'] = 'Registrasi gagal. Silakan coba lagi. Error: ' . $stmtInsert->error;
        error_log("Registration failed for $email: " . $stmtInsert->error); // Log error
    }
    $stmtInsert->close();

} else {
    $response['message'] = 'Metode permintaan tidak valid.';
}

$conn->close();
echo json_encode($response);
?>