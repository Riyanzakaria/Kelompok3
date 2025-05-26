<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="foto/logoputih.png" rel="icon">
  <title>Bali Barber Expo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <style>
    body {
      background-color: #f9fafb;
    }

    .navbar {
      background-color: #1E3A8A;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      margin: 0 10px;
    }

    .navbar a:hover {
      text-decoration: underline;
    }

    .swiper-button-next,
    .swiper-button-prev {
      color: #1E3A8A;
    }

    .hero {
      background-color: #1E3A8A;
      color: white;
      padding: 80px 0;
      position: relative;
      overflow: hidden;
    }

    .hero .left-img,
    .hero .right-img {
      position: absolute;
      top: 0;
      width: 33%;
      height: 100%;
    }

    .hero .left-img {
      left: 0;
    }

    .hero .right-img {
      right: 0;
    }

    .hero img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .hero .content {
      z-index: 10;
      position: relative;
      text-align: center;
    }

    .hero .btn-yellow {
      background-color: #FACC15;
      color: #1E3A8A;
      font-weight: bold;
      border-radius: 9999px;
    }

    .event-card {
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: .5rem;
      overflow: hidden;
      background-color: white;
    }

    .event-card img {
      height: 160px;
      object-fit: cover;
    }

    .event-card .price {
      color: #DC2626;
      font-weight: bold;
    }

    .event-card .btn-buy {
      background-color: #1E3A8A;
      color: white;
      border-radius: 9999px;
    }

    .swiper-container {
      max-width: 1000px;
      margin: 0 auto;
      overflow: hidden;
      /* agar card tidak keluar area wrapper */
      position: relative;
    }

    .swiper-wrapper {
      padding: 0 20px;
      /* Tambahkan padding agar slide tidak mepet kiri kanan */
    }

    .swiper-slide {
      margin: 0 10px;
      /* Jarak antar card */
      transition: transform 0.3s ease-in-out;
    }

    .swiper-button-next,
    .swiper-button-prev {
      color: #1E3A8A;
      width: 36px;
      height: 36px;
      background-color: #fff;
      border-radius: 50%;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      top: 40%;
      transform: translateY(-50%);
      position: absolute;
      z-index: 10;
    }

    .swiper-button-prev {
      left: -20px;
    }

    .swiper-button-next {
      right: -20px;
    }

    .navbar {
      background-color: #1E3A8A;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      margin: 0 10px;
    }

    .navbar a:hover {
      text-decoration: underline;
    }

    .nav-button {
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      margin-right: 10px;
    }

    .nav-button.active {
      background-color: #1e3a8a;
      color: white;
    }

    .nav-button.inactive {
      background-color: white;
      color: black;
      border: 1px solid #ddd;
    }

    .image-landscape {
      aspect-ratio: 19 / 9;
      object-fit: cover;
      width: 100%;
      border-radius: 0.75rem;
    }

    .event-detail i {
      color: #1e3a8a;
      margin-right: 8px;
    }

    .event-detail strong {
      display: block;
      color: #6b7280;
      font-size: small;
    }

    .event-detail span {
      font-weight: 600;
      color: #111827;
    }

    .detail-box {
      background: transparent;
      padding: 0;
      box-shadow: none;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container d-flex justify-content-between align-items-center py-3">
      <a class="navbar-brand text-white fw-bold fs-4" href="dashboard3.html">Harmonix</a>
      <div class="d-none d-lg-flex">
        <a href="#">Jelajah</a>
        <a href="#">Event Creator</a>
        <a href="#">Hubungi Kami</a>
      </div>
      <div class="d-flex align-items-center">
        <img src="https://placehold.co/20x20" alt="Indonesian Flag" class="me-2" />
        <i class="fas fa-search text-white"></i>
      </div>
    </div>
  </nav>
  <div class="container my-4">
    <div class="row align-items-start">
      <!-- Kiri: Konten utama -->
      <div class="col-lg-8">
        <small class="text-muted">Seminar</small>
        <h4 class="mb-3">BALI BARBER EXPO</h4>
        <div class="mb-3">
          <img src="foto/konser2.png" class="image-landscape" alt="Bali Barber Expo" />
        </div>

        <!-- Info bawah gambar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center">
            <img src="foto/eo.jpg" alt="Logo Penyelenggara" width="40" height="40" class="me-2 rounded-circle" />
            <span class="fw-semibold">Penyelenggara<br><strong>Bali Barber Expo</strong></span>
          </div>
          <div class="text-end">
            <small class="d-block">Instagram</small>
            <a href="#" class="text-decoration-none text-dark fw-semibold">
              <i class="bi bi-instagram me-1"></i> balibarberexpo
            </a>
          </div>
        </div>

        <!-- Tombol navigasi -->
        <div class="mb-3">
          <button class="nav-button active" id="btnDeskripsi">
            <i class="bi bi-info-circle me-1"></i> Deskripsi
          </button>
          <button class="nav-button inactive" id="btnTiket">
            <i class="bi bi-ticket-perforated me-1"></i> Tiket
          </button>
        </div>

        <!-- Konten Deskripsi / Tiket -->
        <div id="contentArea">
          <div id="kontenDeskripsi">
            <h6>Deskripsi Event</h6>
            <p><em>Bali Barber Expo</em> adalah event barber tahunan yang diselenggarakan di pulau Dewata Bali yang
              mempertemukan para profesional industri barber dari seluruh Indonesia dan internasional. Dilengkapi dengan
              kompetisi, seminar, dan booth-brand ternama. Ini adalah ajang edukasi, inspirasi, dan networking terbaik
              bagi para barber dan pecinta dunia grooming pria.</p>
          </div>

          <div id="kontenTiket" style="display: none;">
            <div class="card p-3 mb-3 border border-light shadow-sm rounded-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Presale 3 - Festival</strong>
                <span class="badge bg-light text-primary border border-primary">On Sale</span>
              </div>
              <hr />
              <div class="mb-3">
                <div class="text-muted small">Harga</div>
                <div class="text-danger fw-bold">Rp 150.000</div>
              </div>

              <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                  <button class="btn btn-light border rounded-3 me-2" onclick="ubahJumlah(this, -1)">−</button>
                  <input type="text" class="form-control text-center jumlahTiketInput" value="1" style="width: 50px;"
                    readonly />
                  <button class="btn btn-light border rounded-3 ms-2" onclick="ubahJumlah(this, 1)">+</button>
                </div>

                <div class="d-flex align-items-center">
                  <button class="btn btn-primary rounded-3 px-4" style="background-color: #1e3a8a; border: none;"
                    onclick="lanjutPembelian('Presale 3 - Festival', 150000, this)">
                    Beli
                  </button>
                </div>
              </div>
            </div>

            <div class="card p-3 mb-3 border border-light shadow-sm rounded-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Presale 3 - VIP Access</strong>
                <span class="badge bg-light text-primary border border-primary">On Sale</span>
              </div>
              <hr />
              <div class="mb-3">
                <div class="text-muted small">Harga</div>
                <div class="text-danger fw-bold">Rp 300.000</div>
              </div>

              <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                  <button class="btn btn-light border rounded-3 me-2" onclick="ubahJumlah(this, -1)">−</button>
                  <input type="text" class="form-control text-center jumlahTiketInput" value="1" style="width: 50px;"
                    readonly />
                  <button class="btn btn-light border rounded-3 ms-2" onclick="ubahJumlah(this, 1)">+</button>
                </div>

                <div class="d-flex align-items-center">
                  <button class="btn btn-primary rounded-3 px-4" style="background-color: #1e3a8a; border: none;"
                    onclick="lanjutPembelian('Presale 3 - VIP Access', 300000, this)">
                    Beli
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Kanan: Detail event sejajar -->
      <div class="col-lg-4 mt-5 pt-4">
        <div class="detail-box mb-4">
          <h6 class="mb-3">Detail Event</h6>
          <div class="event-detail mb-3">
            <i class="bi bi-calendar-event"></i>
            <strong>Tanggal</strong>
            <span>05 – 06 Jul 2025</span>
          </div>
          <div class="event-detail mb-3">
            <i class="bi bi-clock"></i>
            <strong>Waktu</strong>
            <span>10:00 – 21:00</span>
          </div>
          <div class="event-detail mb-4">
            <i class="bi bi-geo-alt"></i>
            <strong>Lokasi</strong>
            <span>Dharma Negara Alaya</span>
          </div>
          <button class="btn btn-primary w-100" style="background-color: #1e3a8a; border: none;"
            onclick="document.getElementById('btnTiket').click()">Beli Tiket</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const btnDeskripsi = document.getElementById('btnDeskripsi');
    const btnTiket = document.getElementById('btnTiket');
    const kontenDeskripsi = document.getElementById('kontenDeskripsi');
    const kontenTiket = document.getElementById('kontenTiket');

    function ubahJumlah(button, perubahan) {
      const input = button.parentElement.querySelector('.jumlahTiketInput');
      let jumlah = parseInt(input.value) + perubahan;
      if (jumlah < 1) jumlah = 1;
      input.value = jumlah;
    }

    btnDeskripsi.addEventListener('click', () => {
      btnDeskripsi.classList.add('active');
      btnDeskripsi.classList.remove('inactive');
      btnTiket.classList.remove('active');
      btnTiket.classList.add('inactive');
      kontenDeskripsi.style.display = 'block';
      kontenTiket.style.display = 'none';
    });

    btnTiket.addEventListener('click', () => {
      btnTiket.classList.add('active');
      btnTiket.classList.remove('inactive');
      btnDeskripsi.classList.remove('active');
      btnDeskripsi.classList.add('inactive');
      kontenDeskripsi.style.display = 'none';
      kontenTiket.style.display = 'block';
    });

    function lanjutPembelian(namaTiket, hargaTiket, tombol) {
      const parent = tombol.closest('.card');
      const input = parent.querySelector('.jumlahTiketInput');
      const jumlah = parseInt(input.value);

      // Simpan data tiket ke localStorage
      const dataTiket = {
        eventName: "BALI BARBER EXPO",
        namaTiket: namaTiket,
        hargaTiket: hargaTiket,
        jumlah: jumlah,
        totalHarga: hargaTiket * jumlah,
        eventDate: "05 – 06 Jul 2025",
        eventTime: "10:00 – 21:00",
        eventLocation: "Dharma Negara Alaya"
      };

      localStorage.setItem('dataPembelianTiket', JSON.stringify(dataTiket));

      // Redirect ke halaman form pembelian
      window.location.href = 'form-pembelian.php';
    }
  </script>
</body>

</html>
