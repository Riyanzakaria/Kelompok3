<?php
session_start();
// Jika pengguna sudah login, arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrasi - Harmonix</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
  <link rel="icon" href="foto/logoputih.png" type="image/png">
  <style>
    body {
      position: relative;
      background: url('foto/bg.jpg') no-repeat center center/cover;
      background-size: cover;
      color: white;
      height: 100vh;
      margin: 0;
      overflow: hidden;
    }

    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: -1;
    }

    .container-fluid.main-container {
      height: 100vh;
      display: flex;
      padding: 0 !important;
      width: 100%;
    }

    .form-column-wrapper {
      display: flex;
      justify-content: flex-start;
      width: 100%;
      height: 100%;
    }

    .col-md-4.form-column {
      background-color: #142F86;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding-left: 0 !important;
      padding-right: 0 !important;
      z-index: 1;
      overflow-y: auto;
      flex-shrink: 0;
    }

    .col-md-8.background-section {
      height: 100vh;
      background: transparent;
      padding: 0;
      flex-grow: 1;
    }

    .navbar {
      background: transparent !important;
      padding: 15px 30px;
      z-index: 2;
      width: 100%;
      position: fixed;
      top: 0;
    }

    .navbar-brand {
      font-size: 1.25rem;
      font-weight: bold;
    }

    .sign-link {
      color: white;
      text-decoration: none;
      font-weight: 500;
    }

    .sign-link:hover {
      text-decoration: underline;
    }

    .form-section-inner {
      background: transparent;
      padding: 30px 40px;
      width: 100%;
      max-width: 400px;
      margin: auto;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid transparent;
      color: black;
      border-radius: 8px;
      padding: 12px 15px;
      width: 100%;
      font-size: 0.95rem;
      margin-bottom: 1rem;
      display: block;
      transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .form-control::placeholder {
      color: #6c757d;
    }

    .form-control:focus {
      background: white;
      color: black;
      outline: none;
      box-shadow: 0 0 0 0.25rem rgba(31, 41, 55, 0.25);
      border-color: #1f2937;
    }

    .form-label {
      color: white;
      margin-bottom: 0.3rem;
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
    }

    .password-row {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    /* Dulu margin-bottom: 1rem; */
    .password-row>div {
      flex: 1;
    }

    .password-row>div .mb-3 {
      margin-bottom: 0 !important;
    }

    /* Menghapus margin bawah dari .mb-3 di dalam .password-row */

    .btn-register {
      background-color: #1f2937;
      color: white;
      border: none;
      padding: 12px;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.1s;
      display: block;
      width: 100%;
      text-align: center;
      margin-top: 1.5rem;
      font-weight: 500;
    }

    .btn-register:hover {
      background-color: #374151;
    }

    .btn-register:active {
      background-color: #4b5563;
      transform: scale(0.98);
    }

    .btn-google {
      background-color: white;
      color: #374151;
      border: 1px solid #d1d5db;
      padding: 12px;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      text-align: center;
      margin-top: 1rem;
      transition: background-color 0.3s;
      font-weight: 500;
    }

    .btn-google:hover {
      background-color: #f9fafb;
    }

    .btn-google i {
      margin-right: 0.5rem;
    }

    .form-error {
      color: #fca5a5;
      background-color: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 4px;
      padding: 0.3rem 0.6rem;
      font-size: 0.8rem;
      margin-top: 0.25rem;
      margin-bottom: 0.5rem;
      display: block;
    }

    .form-error:empty {
      display: none;
    }

    .text-center.mt-3 a {
      color: #9ca3af;
      text-decoration: none;
    }

    .text-center.mt-3 a:hover {
      color: white;
      text-decoration: underline;
    }

    h2.form-title {
      color: white;
      font-weight: 600;
      text-align: center;
      margin-bottom: 2rem;
    }

    @media (max-width: 767.98px) {
      .col-md-4.form-column {
        height: auto;
        min-height: 100vh;
        width: 100% !important;
        overflow-y: auto;
        padding-top: 80px !important;
      }

      .col-md-8.background-section {
        display: none;
      }

      .form-column-wrapper {
        justify-content: center;
      }

      .form-section-inner {
        padding: 20px;
      }

      .password-row {
        flex-direction: column;
        gap: 0;
      }

      .password-row>div {
        margin-bottom: 1rem;
      }

      .password-row>div:last-child {
        margin-bottom: 0;
      }

      .navbar {
        padding: 10px 15px;
      }
    }
  </style>

<body>
  <div class="container-fluid main-container">
    <nav class="navbar navbar-expand-lg fixed-top">
      <div class="container-fluid">
        <a class="navbar-brand text-white d-flex align-items-center" href="dashboard.php">
          <img src="foto/logoputih.png" alt="Logo" width="30" height="30" class="me-2"> HARMONIX
        </a>
        <span class="text-white me-2 d-none d-sm-inline">Sudah punya akun?</span>
        <a href="login.php" class="sign-link">Masuk!</a>
      </div>
    </nav>
    <div class="form-column-wrapper">
      <div class="col-md-4 form-column">
        <div class="form-section-inner">
          <h2 class="form-title">Buat Akun Baru</h2>
          <form id="registerForm" novalidate>
            <div class="mb-3">
              <label for="exampleUsername" class="form-label">Username</label>
              <input type="text" class="form-control" id="exampleUsername" name="username"
                placeholder="Masukkan username Anda" required>
              <div class="form-error" id="usernameError"></div>
            </div>
            <div class="mb-3">
              <label for="examplePhoneNumber" class="form-label">Nomor Telepon</label>
              <input type="tel" class="form-control" id="examplePhoneNumber" name="phoneNumber"
                placeholder="08xxxxxxxxxx" required pattern="08[0-9]{8,11}">
              <div class="form-error" id="phoneError"></div>
            </div>
            <div class="mb-3">
              <label for="exampleInputEmail1" class="form-label">Alamat Email</label>
              <input type="email" class="form-control" id="exampleInputEmail1" name="email"
                placeholder="nama@contoh.com" required>
              <div class="form-error" id="emailError"></div>
            </div>
            <div class="password-row">
              <div class="mb-3"> <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" name="password"
                  placeholder="Minimal 8 karakter" required minlength="8">
                <div class="form-error" id="passwordError"></div>
              </div>
              <div class="mb-3"> <label for="examplePasswordConfirm" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="examplePasswordConfirm" name="passwordConfirm"
                  placeholder="Ulangi password" required>
                <div class="form-error" id="passwordConfirmError"></div>
              </div>
            </div>
            <button class="btn-register" type="submit">DAFTAR</button>
            <p class="text-center my-3" style="color: #adb5bd;">atau</p>
            <button class="btn btn-google" type="button"> <i class="fab fa-google"></i> Lanjutkan dengan Google
            </button>
          </form>
          <p class="text-center mt-3"><small>Dengan mendaftar, Anda menyetujui<br><a href="#">Ketentuan Layanan</a> & <a
                href="#">Kebijakan Privasi</a> kami.</small></p>
        </div>
      </div>
      <div class="col-md-8 background-section d-none d-md-block">
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const registerForm = document.getElementById('registerForm');
    const usernameInput = document.getElementById('exampleUsername');
    const phoneInput = document.getElementById('examplePhoneNumber');
    // const birthdateInput = document.getElementById('exampleBirthdate'); // DIHAPUS
    const emailInput = document.getElementById('exampleInputEmail1');
    const passwordInput = document.getElementById('exampleInputPassword1');
    const passwordConfirmInput = document.getElementById('examplePasswordConfirm');

    function showError(elementId, message) {
      const errorElement = document.getElementById(elementId);
      const inputEl = document.getElementById(elementId.replace('Error', ''));
      if (errorElement) errorElement.innerText = message;
      if (inputEl) inputEl.classList.add('is-invalid');
    }
    function clearError(elementId) {
      const errorElement = document.getElementById(elementId);
      const inputEl = document.getElementById(elementId.replace('Error', ''));
      if (errorElement) errorElement.innerText = "";
      if (inputEl) inputEl.classList.remove('is-invalid');
    }
    function clearAllErrors() {
      // 'birthdateError' DIHAPUS dari array ini
      ['usernameError', 'phoneError', 'emailError', 'passwordError', 'passwordConfirmError'].forEach(id => {
        const errorElement = document.getElementById(id);
        if (errorElement) clearError(id); // Hanya panggil clearError jika elemennya ada
      });
      document.querySelectorAll('.form-control.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function validateForm() {
      let isValid = true;
      clearAllErrors();

      if (usernameInput.value.trim() === "") { showError('usernameError', 'Username wajib diisi.'); isValid = false; }

      if (phoneInput.value.trim() === "") { showError('phoneError', 'Nomor telepon wajib diisi.'); isValid = false; }
      else if (!/^08[0-9]{8,11}$/.test(phoneInput.value.trim())) { showError('phoneError', 'Format nomor telepon tidak valid (contoh: 081234567890).'); isValid = false; }

      // Validasi Tanggal Lahir DIHAPUS
      // if (birthdateInput && birthdateInput.value === "") { showError('birthdateError', 'Tanggal lahir wajib diisi.'); isValid = false; } 
      // else if (birthdateInput) { 
      // Validasi usia (opsional)
      // }

      if (emailInput.value.trim() === "") { showError('emailError', 'Email wajib diisi.'); isValid = false; }
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) { showError('emailError', 'Format email tidak valid.'); isValid = false; }

      if (passwordInput.value.length === 0) { showError('passwordError', 'Password wajib diisi.'); isValid = false; }
      else if (passwordInput.value.length < 8) { showError('passwordError', 'Password minimal harus 8 karakter.'); isValid = false; }

      if (passwordConfirmInput.value === "") { showError('passwordConfirmError', 'Mohon konfirmasi password Anda.'); isValid = false; }
      else if (passwordInput.value !== passwordConfirmInput.value) { showError('passwordConfirmError', 'Password tidak cocok.'); isValid = false; }

      return isValid;
    }

    registerForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      const submitButton = registerForm.querySelector('.btn-register');
      const originalButtonText = submitButton.innerHTML;

      clearAllErrors(); // Bersihkan error sebelum validasi baru

      if (validateForm()) { // Lakukan validasi sisi klien
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mendaftar...';

        const formData = new FormData(registerForm);
        // Tidak perlu menambahkan field birthdate ke formData karena sudah dihapus dari HTML

        try {
          const response = await fetch('proses_register.php', {
            method: 'POST',
            body: formData
          });
          const resultText = await response.text();
          try {
            const result = JSON.parse(resultText);
            if (result.success) {
              alert(result.message); // Pesan dari server
              if (result.redirect_url) {
                window.location.href = result.redirect_url; // Misal ke dashboard jika auto-login
              } else {
                window.location.href = 'login.php'; // Atau default ke login
              }
            } else {
              // Tampilkan error dari server, bisa di field tertentu atau sebagai alert umum
              if (result.message) {
                if (result.message.toLowerCase().includes('email')) {
                  showError('emailError', result.message);
                  emailInput.focus();
                } else if (result.message.toLowerCase().includes('username')) {
                  showError('usernameError', result.message);
                  usernameInput.focus();
                } else {
                  alert('Registrasi Gagal: ' + result.message);
                }
              } else {
                alert('Registrasi Gagal: Terjadi kesalahan tidak diketahui dari server.');
              }
            }
          } catch (jsonError) {
            console.error("Gagal parsing JSON dari server:", jsonError);
            console.error("Respons server (teks):", resultText);
            alert("Terjadi kesalahan pada respons server. Silakan coba lagi atau hubungi admin.");
          }
        } catch (error) {
          console.error('Error saat fetch:', error);
          alert('Terjadi kesalahan saat mencoba mendaftar. Periksa koneksi Anda dan coba lagi.');
        } finally {
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonText;
        }
      } else {
        const firstErrorField = document.querySelector('.form-control.is-invalid');
        if (firstErrorField) firstErrorField.focus();
      }
    });
  </script>
</body>

</html>
