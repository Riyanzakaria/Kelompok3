// js/init.js
document.addEventListener('DOMContentLoaded', function () {
  const navbarActionsContainer = document.getElementById('navbarActions'); // Target ID yang sudah distandarisasi

  if (navbarActionsContainer) {
    // Variabel isLoggedIn, userName, dan userProfilePic DIASUMSIKAN SUDAH ADA
    // di scope global JavaScript, di-set oleh blok <script> di file PHP
    // sebelum skrip init.js ini dimuat.

    if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
        // Pengguna sudah login
        navbarActionsContainer.innerHTML = `
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUserMenuGlobal" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="${userProfilePic}" alt="${userName}" width="32" height="32" class="rounded-circle me-2 profile-icon" onerror="this.onerror=null; this.src='foto/default_avatar.png';">
                    <span class="d-none d-sm-inline">${userName}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow dropdown-menu-end" aria-labelledby="dropdownUserMenuGlobal">
                    <li><a class="dropdown-item" href="#">Masuk sebagai <strong>${userName}</strong></a></li>
                    <li><a class="dropdown-item" href="Profile.php">Profil Saya</a></li>
                    <li><a class="dropdown-item" href="dashboard_tiket_saya.php">Tiket Saya</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Keluar</a></li> 
                </ul>
            </div>
        `;
    } else {
        // Pengguna belum login
        navbarActionsContainer.innerHTML = `
            <a href="login.php" class="btn btn-outline-light me-2">Masuk</a>
            <a href="register.php" class="btn btn-warning">Daftar</a>
        `;
    }
  } else {
    // console.warn("Elemen #navbarActions tidak ditemukan di halaman ini.");
  }

  // Fungsi global untuk memeriksa login sebelum aksi tertentu (misalnya, beli tiket)
  // Ini juga menggunakan variabel global isLoggedIn.
  window.requireLoginGlobal = function(callbackIfLoggedIn) {
    if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
      if (callbackIfLoggedIn && typeof callbackIfLoggedIn === 'function') {
        callbackIfLoggedIn();
      }
      return true;
    } else {
      alert("Anda harus login terlebih dahulu untuk melakukan tindakan ini.");
      // Opsional: Simpan URL saat ini untuk redirect kembali setelah login
      // const currentPathForRedirect = window.location.pathname + window.location.search;
      // window.location.href = 'login.php?redirect=' + encodeURIComponent(currentPathForRedirect);
      window.location.href = 'login.php'; // Redirect sederhana ke login
      return false;
    }
  };

  // Catatan: Inisialisasi Swiper, search toggle, atau JavaScript spesifik halaman lainnya
  // sebaiknya tetap berada di skrip halaman masing-masing (dashboard.php, detail-event.php)
  // atau di file JS terpisah yang di-load oleh halaman tersebut, BUKAN di init.js ini
  // agar init.js tetap fokus pada fungsionalitas global seperti navbar.
});
// di init.js
// ...
const logoutButton = document.getElementById('logoutButtonInit');
if (logoutButton) {
    logoutButton.addEventListener('click', function(e) {
        e.preventDefault();
        // Opsional: Panggil endpoint logout PHP untuk membersihkan sesi server
        fetch('logout.php').then(() => {
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('userEmail');
            // ... item localStorage lainnya ...
            window.location.href = 'login.php';
        });
    });
}