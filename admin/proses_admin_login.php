<?php
session_start(); 
require_once '../db.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Permintaan tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

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

    $stmt = $conn->prepare("SELECT user_id, name, email, password AS hashedPassword, role FROM users WHERE email = ?");
    if (!$stmt) {
        error_log("MySQL Prepare Error (proses_admin_login): " . $conn->error);
        $response['message'] = "Terjadi kesalahan internal server (1).";
        echo json_encode($response);
        exit;
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("MySQL Execute Error (proses_admin_login): " . $stmt->error);
        $response['message'] = "Terjadi kesalahan internal server (2).";
        $stmt->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifikasi password DAN role
        if (password_verify($password, $user['hashedPassword']) && $user['role'] === 'admin') {
            session_regenerate_id(true); 
            $_SESSION['admin_id'] = $user['user_id']; // Gunakan user_id sebagai admin_id
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role']; // Simpan role

            $response['success'] = true;
            $response['message'] = 'Login admin berhasil! Mengarahkan ke panel admin...';
            $response['redirect_url'] = 'admin.php'; 
        } else {
            $response['message'] = 'Email, password, atau hak akses admin salah.';
        }
    } else {
        $response['message'] = 'Email, password, atau hak akses admin salah.';
    }
    $stmt->close();

} else {
    $response['message'] = 'Metode permintaan tidak diizinkan.';
}

$conn->close();
echo json_encode($response);
?>