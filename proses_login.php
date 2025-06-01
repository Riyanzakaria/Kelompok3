<?php
session_start(); // WAJIB di paling atas sebelum output apapun
require_once 'db.php'; // File koneksi database Anda

header('Content-Type: application/json'); // Output akan berupa JSON

$response = ['success' => false, 'message' => 'Permintaan tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Password tidak di-trim untuk menjaga integritas

    // Validasi dasar di sisi server
    if (empty($email) || empty($password)) {
        $response['message'] = 'Email dan password wajib diisi.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Format email tidak valid.';
        echo json_encode($response);
        exit;
    }

    // Ambil data pengguna dari database berdasarkan email
    $stmt = $conn->prepare("SELECT user_id, name, email, password AS hashedPassword, phone FROM users WHERE email = ?");
    if (!$stmt) {
        // Jika prepare statement gagal, ini masalah server/query, bukan input pengguna
        error_log("MySQL Prepare Statement Error: " . $conn->error);
        $response['message'] = "Terjadi kesalahan pada server (1).";
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("MySQL Execute Statement Error: " . $stmt->error);
        $response['message'] = "Terjadi kesalahan pada server (2).";
        $stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $user['hashedPassword'])) {
            // Password cocok, login berhasil
            // Regenerasi ID session untuk keamanan (mencegah session fixation)
            session_regenerate_id(true); 

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            // Anda bisa menambahkan data lain ke session jika perlu, misalnya path foto profil
            // $_SESSION['user_profile_pic'] = $user['profile_pic_url'] ?? 'foto/default_profile.png';

            $response['success'] = true;
            $response['message'] = 'Login berhasil! Mengarahkan ke dashboard...';
            $response['redirect_url'] = 'dashboard.php'; // URL tujuan setelah login
        } else {
            // Password tidak cocok
            $response['message'] = 'Email atau password yang Anda masukkan salah.';
        }
    } else {
        // Pengguna dengan email tersebut tidak ditemukan
        $response['message'] = 'Email atau password yang Anda masukkan salah.';
    }
    $stmt->close();

} else {
    $response['message'] = 'Metode permintaan tidak diizinkan.';
}

$conn->close();
echo json_encode($response);
?>