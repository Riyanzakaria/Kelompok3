<?php
session_start();
require_once 'db.php'; // Koneksi database Anda

// Jika tidak ada email di session, redirect kembali
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgotPassword.php");
    exit();
}

$email_to_reset = $_SESSION['reset_email'];
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($password) || empty($confirm_password)) {
            $error_message = 'Both password fields are required.';
        } elseif (strlen($password) < 6) { // Contoh validasi panjang password
            $error_message = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match.';
        } else {
            // Hash password baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update password di database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email_to_reset);
            
            if ($stmt->execute()) {
                // Password berhasil diupdate
                unset($_SESSION['reset_email']); // Hapus email dari session
                $_SESSION['success_message'] = "Password has been reset successfully. Please login."; // Pesan untuk login.php
                header("Location: login.php");
                exit();
            } else {
                $error_message = 'Failed to update password. Please try again.';
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Step 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="foto/logoputih.png" type="image/png">
    <style>
      body {
        position: relative; background: url('foto/bg.jpg') no-repeat center center/cover;
        background-size: cover; color: white; height: 100vh; margin: 0; overflow: hidden;
      }
      body::before {
        content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); z-index: -1;
      }
      .container {
        height: 100vh; display: flex; align-items: center; justify-content: flex-start;
        padding: 0 !important; width: 100%; max-width: none;
      }
      .row { height: 100%; margin: 0; width: 100%; }
      .col-md-4 {
        background-color: #142F86; height: 100vh; display: flex; align-items: center;
        justify-content: center; padding-left: 0 !important; padding-right: 0 !important; z-index: 1;
      }
      .col-md-8.background-section { height: 100vh; background: transparent; padding: 0; }
      .navbar { background: transparent !important; padding: 10px 20px; z-index: 2; width: 100%; }
      .navbar-brand { font-size: 18px; font-weight: bold; }
      .sign-link { color: white; text-decoration: none; }
      .sign-link:hover { text-decoration: underline; }
      .form-section { background: transparent; padding: 40px 20px; width: 100%; max-width: none; margin: 0; }
      .form-control {
        background: rgba(255, 255, 255, 0.9); border: none; color: black; border-radius: 5px;
        padding: 10px; width: 100%; font-size: 16px;
      }
      .form-control::placeholder { color: rgba(0, 0, 0, 0.5); }
      .form-control:focus { background: rgba(255, 255, 255, 1); color: black; outline: none; box-shadow: none; }
      .form-label { color: white; }
      .btn-reset {
        background-color: #1f2937; color: white; border: none; padding: 10px; font-size: 16px;
        border-radius: 5px; cursor: pointer; transition: background-color 0.3s, transform 0.1s;
        display: block; width: 100%; text-align: center; margin-top: 15px;
      }
      .btn-reset:hover { background-color: #4B5563; }
      .btn-reset:active { background-color: #6B7280; transform: scale(0.95); }
      .form-error, .form-success {
        font-size: 0.875em; margin-top: 10px; display: block; 
        padding: 8px; border-radius: 4px;
      }
      .form-error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; }
      .form-success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb;}
    </style>
  </head>
  <body>
    <div class="container">
      <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
          <a class="navbar-brand text-white d-flex align-items-center" href="#">
            <img src="foto/logoputih.png" alt="Logo" width="30" height="30" class="me-2">
            HARMONIXX
          </a>
          <a href="register.php" class="sign-link">Sign Up!</a>
        </div>
      </nav>        
      <div class="row w-100">
        <div class="col-md-4 d-flex align-items-center">
          <div class="form-section">
            <form id="resetPasswordForm" method="POST" action="forgotPassword2.php">
              <div>
                <h3 style="text-align: center;">Reset Your Password</h3>
                <p class="text-center small mb-3">Enter your new password for <strong><?php echo htmlspecialchars($email_to_reset); ?></strong>.</p>
              </div>

              <?php if (!empty($error_message)): ?>
                <div class="form-error"><?php echo $error_message; ?></div>
              <?php endif; ?>

              <div class="mb-3">
                <label for="passwordInput" class="form-label">New Password</label>
                <input type="password" class="form-control" id="passwordInput" name="password" placeholder="Enter new password" required>
              </div>
              <div class="mb-3">
                <label for="confirmPasswordInput" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPasswordInput" name="confirm_password" placeholder="Confirm new password" required>
              </div>
              <button class="btn-reset" type="submit">Reset Password</button>
              <a href="login.php" class="register-link mt-3" style="font-weight:normal; font-size:0.9em;">Cancel and go back to Login</a>
            </form>
          </div>
        </div>
        <div class="col-md-8 background-section"></div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
