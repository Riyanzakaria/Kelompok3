<?php
session_start();
// Jika pengguna sudah login, langsung arahkan ke dashboard
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
  <title>Login - Harmonix</title>
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

    .btn-login {
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

    .btn-login:hover {
      background-color: #374151;
    }

    .btn-login:active {
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

    .create-account-link {
      display: block;
      text-align: center;
      margin-top: 1.5rem;
      color: #9ca3af;
      text-decoration: none;
    }

    .create-account-link strong {
      color: white;
    }

    .create-account-link:hover strong {
      text-decoration: underline;
    }

    .remember-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      margin-top: 0.75rem;
      margin-bottom: 1rem;
      font-size: 0.875rem;
    }

    .forgot-link {
      color: #60a5fa;
      text-decoration: none;
    }

    .forgot-link:hover {
      text-decoration: underline;
      color: #93c5fd;
    }

    .checkbox-remember {
      appearance: none;
      width: 16px;
      height: 16px;
      border: 1.5px solid #9ca3af;
      background-color: transparent;
      border-radius: 4px;
      position: relative;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-right: 0.3rem;
      vertical-align: middle;
    }

    .checkbox-remember:checked {
      background-color: white;
      border-color: white;
    }

    .checkbox-remember:checked::after {
      content: "✔";
      font-size: 11px;
      color: #142F86;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-weight: bold;
    }

    .form-check-label {
      color: #d1d5db;
    }

    .captcha-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      gap: 10px;
      margin-bottom: 0.5rem;
    }

    .input_field.captch_box {
      display: flex;
      align-items: center;
      width: 60%;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.9);
      overflow: hidden;
    }

    .input_field.captch_box input {
      background: transparent;
      border: none;
      color: black !important;
      padding: 10px;
      flex-grow: 1;
      font-size: 0.95rem;
      height: 42px;
      outline: none;
      text-align: center;
      letter-spacing: 2px;
    }

    .refresh_button {
      background: transparent;
      border: none;
      color: #4b5563;
      cursor: pointer;
      padding: 0 12px;
      height: 42px;
      display: flex;
      align-items: center;
      border-left: 1px solid #d1d5db;
    }

    .refresh_button i {
      font-size: 0.9rem;
    }

    .input_field.captch_input {
      width: 40%;
    }

    .input_field.captch_input input {
      width: 100%;
      border-radius: 8px;
      padding: 10px;
      font-size: 0.95rem;
      height: 42px;
      color: black;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid transparent;
    }

    .input_field.captch_input input:focus {
      background: white;
      color: black;
      outline: none;
      box-shadow: 0 0 0 0.25rem rgba(31, 41, 55, 0.25);
      border-color: #1f2937;
    }

    .message {
      color: #fca5a5;
      font-size: 0.8rem;
      margin-top: 0.25rem;
      display: block;
      height: 1.2em;
    }

    .message.success {
      color: #86efac;
    }

    .login-error-message {
      color: #fca5a5;
      background-color: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 4px;
      padding: 0.5rem 0.8rem;
      font-size: 0.9rem;
      margin-bottom: 1rem;
      display: none;
    }

    /* Untuk error login global */
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

      .navbar {
        padding: 10px 15px;
      }

      .captcha-section {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
      }

      .input_field.captch_box,
      .input_field.captch_input {
        width: 100%;
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
        <span class="text-white me-2 d-none d-sm-inline">Belum punya akun?</span>
        <a href="register.php" class="sign-link">Daftar Sekarang!</a>
      </div>
    </nav>
    <div class="form-column-wrapper">
      <div class="col-md-4 form-column">
        <div class="form-section-inner">
          <h2 class="form-title">Selamat Datang Kembali!</h2>
          <div class="login-error-message" id="loginErrorMessage"></div>
          <form id="loginForm" novalidate>
            <div class="mb-3">
              <label for="exampleInputEmail1" class="form-label">Alamat Email</label>
              <input type="email" class="form-control" id="exampleInputEmail1" name="email"
                placeholder="nama@contoh.com" required>
            </div>
            <div class="mb-3">
              <label for="exampleInputPassword1" class="form-label">Password</label>
              <input type="password" class="form-control" id="exampleInputPassword1" name="password"
                placeholder="Masukkan password Anda" required>
            </div>
            <div class="mb-3">
              <label for="captchaInput" class="form-label">Verifikasi CAPTCHA</label>
              <div class="captcha-section">
                <div class="input_field captch_box">
                  <input type="text" value="" disabled id="captchaTextBox" />
                  <button type="button" class="refresh_button" id="refreshCaptcha"> <i
                      class="fa-solid fa-rotate-right"></i> </button>
                </div>
                <div class="input_field captch_input">
                  <input class="form-control" type="text" placeholder="Masukkan Captcha" id="captchaInput" required />
                </div>
              </div>
              <div class="message" id="captchaMessage"></div>
            </div>
            <div class="remember-forgot">
              <div class="form-check">
                <input type="checkbox" class="form-check-input checkbox-remember" id="rememberMe" name="rememberMe">
                <label class="form-check-label" for="rememberMe">Ingat Saya</label>
              </div>
              <a href="forgotPassword.php" class="forgot-link">Lupa Password?</a>
            </div>
            <button class="btn-login" type="submit">MASUK</button>
            <p class="text-center my-3" style="color: #adb5bd;">atau</p>
            <button class="btn btn-google" type="button"> <i class="fab fa-google"></i> Lanjutkan dengan Google
            </button>
            <p class="text-center mt-3 create-account-link">Belum punya akun? <a href="register.php"><strong>Buat
                  Akun!</strong></a></p>
          </form>
        </div>
      </div>
      <div class="col-md-8 background-section d-none d-md-block">
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const captchaTextBox = document.getElementById("captchaTextBox");
    const refreshButton = document.getElementById("refreshCaptcha");
    const captchaInputBox = document.getElementById("captchaInput");
    const captchaMessage = document.getElementById("captchaMessage"); // Pesan untuk CAPTCHA
    const loginErrorMessageDiv = document.getElementById("loginErrorMessage"); // Pesan untuk error login global
    const loginForm = document.getElementById("loginForm");
    const emailLoginInput = document.getElementById('exampleInputEmail1');
    const passwordLoginInput = document.getElementById('exampleInputPassword1');

    let captchaText = null;

    // Fungsi CAPTCHA (implementasi Anda sebelumnya sudah baik)
    const generateCaptchaImpl = () => { const randomString = Math.random().toString(36).substring(2, 8); const randomStringArray = randomString.split(""); const changeString = randomStringArray.map((char) => (Math.random() > 0.5 ? char.toUpperCase() : char)); captchaText = changeString.join(" "); captchaTextBox.value = captchaText; captchaMessage.innerText = ""; captchaTextBox.style.letterSpacing = "2px"; captchaInputBox.classList.remove('is-invalid', 'is-valid'); };
    const refreshBtnClickImpl = () => { generateCaptchaImpl(); captchaInputBox.value = ""; captchaInputBox.classList.remove('is-invalid', 'is-valid'); captchaMessage.innerText = ""; loginErrorMessageDiv.style.display = 'none'; };
    const captchaKeyUpValidateImpl = () => { const inputText = captchaInputBox.value.split(" ").join("").toLowerCase(); const captchaCompareText = captchaText ? captchaText.split(" ").join("").toLowerCase() : ""; captchaInputBox.classList.remove('is-invalid', 'is-valid'); captchaMessage.innerText = ""; if (inputText.length === 0) { return false; } if (inputText === captchaCompareText) { captchaMessage.style.color = "#86efac"; captchaMessage.innerText = "Captcha cocok!"; captchaInputBox.classList.add('is-valid'); return true; } else { captchaMessage.style.color = "#fca5a5"; captchaMessage.innerText = "Captcha tidak cocok."; captchaInputBox.classList.add('is-invalid'); return false; } };

    refreshButton.addEventListener("click", refreshBtnClickImpl);
    captchaInputBox.addEventListener("input", captchaKeyUpValidateImpl);
    window.addEventListener("load", () => {
      generateCaptchaImpl();
      captchaInputBox.value = "";
      captchaMessage.innerText = "";
      loginErrorMessageDiv.style.display = 'none';
    });

    loginForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      const submitButton = loginForm.querySelector('.btn-login');
      const originalButtonText = submitButton.innerHTML;

      loginErrorMessageDiv.style.display = 'none'; // Sembunyikan error login lama
      captchaMessage.innerText = ""; // Bersihkan pesan captcha lama

      let clientSideValid = true;
      if (emailLoginInput.value.trim() === "") {
        loginErrorMessageDiv.innerText = "Alamat email wajib diisi.";
        loginErrorMessageDiv.style.display = 'block';
        emailLoginInput.focus();
        clientSideValid = false;
      } else if (passwordLoginInput.value === "") {
        loginErrorMessageDiv.innerText = "Password wajib diisi.";
        loginErrorMessageDiv.style.display = 'block';
        passwordLoginInput.focus();
        clientSideValid = false;
      }

      // Hanya lanjut jika input dasar terisi dan CAPTCHA benar
      if (clientSideValid && captchaKeyUpValidateImpl()) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sedang memproses...';

        const formData = new FormData();
        formData.append('email', emailLoginInput.value.trim());
        formData.append('password', passwordLoginInput.value); // Password tidak di-trim

        try {
          const response = await fetch('proses_login.php', {
            method: 'POST',
            body: formData
          });
          const resultText = await response.text(); // Baca sebagai teks dulu
          try {
            const result = JSON.parse(resultText); // Coba parse JSON
            if (result.success) {
              // alert(result.message); // Opsional
              if (result.redirect_url) {
                window.location.href = result.redirect_url;
              } else {
                window.location.href = 'dashboard.php'; // Fallback redirect
              }
            } else {
              loginErrorMessageDiv.innerText = result.message || "Login gagal. Silakan coba lagi.";
              loginErrorMessageDiv.style.display = 'block';
            }
          } catch (jsonError) {
            console.error("Gagal parsing JSON dari server:", jsonError);
            console.error("Respons server (teks):", resultText);
            loginErrorMessageDiv.innerText = "Terjadi kesalahan pada respons server.";
            loginErrorMessageDiv.style.display = 'block';
          }
        } catch (error) {
          console.error('Error saat fetch:', error);
          loginErrorMessageDiv.innerText = 'Terjadi kesalahan koneksi. Periksa jaringan Anda dan coba lagi.';
          loginErrorMessageDiv.style.display = 'block';
        } finally {
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonText;
        }

      } else {
        if (!clientSideValid) {
          // Pesan error sudah ditampilkan di loginErrorMessageDiv
        } else if (captchaInputBox.value.length === 0) {
          captchaMessage.style.color = "#fca5a5";
          captchaMessage.innerText = "Silakan masukkan CAPTCHA.";
          captchaInputBox.focus();
        } else {
          // Pesan "Captcha tidak cocok" sudah diatur oleh captchaKeyUpValidateImpl
          captchaInputBox.focus();
        }
      }
    });
  </script>
</body>

</html>
