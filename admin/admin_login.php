<?php
session_start(); // Mulai sesi di paling atas

// Jika admin sudah login (berdasarkan sesi yang dibuat oleh proses_admin_login.php),
// langsung arahkan ke halaman admin utama (misalnya, admin.php)
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin') {
    header('Location: admin.php');
    exit;
}
require_once '../db.php';


// Tidak ada interaksi database langsung di halaman ini untuk menampilkan form.
// Koneksi database hanya akan digunakan oleh proses_admin_login.php.
$pageTitle = "Admin Login - Harmonix";
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $pageTitle; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
  <link rel="icon" href="../foto/logoputih.png" type="image/png"> 
  <style>
    body, html {
        height: 100%;
        margin: 0;
        font-family: 'Poppins', sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    .bg-login {
        /* Path ke background image dari dalam folder admin */
        background-image: url('../foto/bg.jpg'); 
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .bg-login::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(17, 24, 39, 0.7); /* Overlay gelap */
        z-index: 1;
    }

    .login-container {
        position: relative;
        z-index: 2;
        background-color: #1E3A8A; 
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 420px;
        color: white;
    }

    .login-logo {
        display: block;
        margin: 0 auto 20px auto;
        width: 70px; /* Ukuran logo disesuaikan */
        height: auto;
    }

    .login-title {
        font-weight: 600;
        margin-bottom: 1.5rem;
        text-align: center;
        font-size: 1.75rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-control {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3); /* Border lebih terlihat */
        color: white;
        border-radius: 8px;
        padding: 10px 15px; /* Padding disesuaikan */
        transition: background-color 0.3s, border-color 0.3s;
        font-size: 0.9rem;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .form-control:focus {
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: #FFC107; 
        box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
    }

    .input-group-text {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-right: none; /* Hilangkan border kanan pada ikon */
        color: rgba(255, 255, 255, 0.7);
        border-radius: 8px 0 0 8px;
    }
    .form-control-icon { /* Untuk input yang ada ikonnya */
        border-radius: 0 8px 8px 0 !important;
        border-left: none !important;
    }


    .btn-login {
        background-color: #FFC107; 
        color: #142F86; /* Warna teks biru tua agar kontras dengan kuning */
        border: none;
        padding: 10px 15px;
        font-size: 1rem;
        font-weight: bold;
        border-radius: 8px;
        transition: background-color 0.3s, transform 0.1s;
        width: 100%;
    }

    .btn-login:hover {
        background-color: #ffca2c; 
        color: #142F86;
    }
    .btn-login:active {
        transform: scale(0.98);
    }
    
    .alert-custom {
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
    }
    /* Tambahkan font Poppins jika belum ada secara global */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
  </style>
</head>
<body>
  <div class="bg-login">
    <div class="login-container text-center">
      <img src="../foto/logoputih.png" alt="Harmonix Logo" class="login-logo"> 
      <h2 class="login-title">Harmonix Admin</h2>
      
      <div id="loginErrorMessage" class="alert alert-danger alert-custom d-none" role="alert"></div>

      <form id="adminLoginForm">
        <div class="mb-3 text-start">
          <label for="adminEmail" class="form-label">Alamat Email</label>
          <div class="input-group">
              <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
              <input type="email" class="form-control form-control-icon" id="adminEmail" name="email" placeholder="admin@harmonix.com" required>
          </div>
        </div>
        <div class="mb-4 text-start"> 
          <label for="adminPassword" class="form-label">Password</label>
          <div class="input-group">
               <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
              <input type="password" class="form-control form-control-icon" id="adminPassword" name="password" placeholder="Masukkan password" required>
          </div>
        </div>
        <button class="btn btn-login" type="submit" id="adminLoginButton">MASUK</button>
      </form>
      <p class="text-center mt-4 mb-0" style="font-size: 0.8rem; color: rgba(255,255,255,0.5);">
          &copy; <?php echo date("Y"); ?> Harmonix Panel.
      </p>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const adminLoginForm = document.getElementById('adminLoginForm');
    const adminLoginButton = document.getElementById('adminLoginButton');
    const loginErrorMessageDiv = document.getElementById('loginErrorMessage');
    const adminEmailInput = document.getElementById('adminEmail');
    const adminPasswordInput = document.getElementById('adminPassword');

    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            loginErrorMessageDiv.classList.add('d-none'); // Sembunyikan pesan error lama
            loginErrorMessageDiv.textContent = '';

            const email = adminEmailInput.value.trim();
            const password = adminPasswordInput.value; // Password tidak di-trim untuk pengecekan

            // Validasi sisi klien sederhana
            if (!email || !password) {
                loginErrorMessageDiv.textContent = "Email dan password tidak boleh kosong.";
                loginErrorMessageDiv.classList.remove('d-none');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { // Validasi format email dasar
                loginErrorMessageDiv.textContent = "Format email tidak valid.";
                loginErrorMessageDiv.classList.remove('d-none');
                return;
            }
            
            const originalButtonText = adminLoginButton.innerHTML;
            adminLoginButton.disabled = true;
            adminLoginButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sedang memproses...';

            const formData = new FormData(adminLoginForm); 

            try {
                // Pastikan proses_admin_login.php ada di folder yang sama (admin/)
                const response = await fetch('proses_admin_login.php', { 
                    method: 'POST',
                    body: formData
                });
                const resultText = await response.text();
                try {
                    const result = JSON.parse(resultText);
                    if (result.success) {
                        if (result.redirect_url) {
                            window.location.href = result.redirect_url;
                        } else {
                            window.location.href = 'admin.php'; // Default redirect
                        }
                    } else {
                        loginErrorMessageDiv.textContent = result.message || "Login gagal. Periksa kembali kredensial Anda.";
                        loginErrorMessageDiv.classList.remove('d-none');
                    }
                } catch (jsonError) {
                    console.error("Gagal parsing JSON dari server:", jsonError, "Respons Mentah:", resultText);
                    loginErrorMessageDiv.textContent = "Terjadi kesalahan pada respons server. Silakan coba lagi. [" + resultText.substring(0,150) + "]";
                    loginErrorMessageDiv.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Error saat login admin:', error);
                loginErrorMessageDiv.textContent = 'Terjadi kesalahan koneksi. Periksa jaringan Anda dan coba lagi.';
                loginErrorMessageDiv.classList.remove('d-none');
            } finally {
                adminLoginButton.disabled = false;
                adminLoginButton.innerHTML = originalButtonText;
            }
        });
    }
});
</script>
</body>
</html>