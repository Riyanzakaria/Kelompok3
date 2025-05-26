<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    <link rel="icon" href="foto/logoputih.png" type="img/png" sizes="16x16">
  </head>
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
      background: rgba(0, 0, 0, 0.5);
      z-index: -1;
    }

  
    .container {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: flex-start; 
      padding: 0 !important;
      width: 100%;
      max-width: none;
    }

    .row {
      height: 100%;
      margin: 0;
      width: 100%;
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
    }

    .col-md-8.background-section {
      height: 100vh;
      background: transparent;
      padding: 0;
    }

    .navbar {
      background: transparent !important;
      padding: 10px 20px;
      z-index: 2;
      width: 100%;
      position: fixed;
      top: 0;
    }

    .navbar-brand {
      font-size: 18px;
      font-weight: bold;
    }

    .sign-link {
      color: white;
      text-decoration: none;
    }

    .sign-link:hover {
      text-decoration: underline;
    }

    .form-section-inner {
      background: transparent; 
      padding: 40px 20px;
      width: 100%;
      max-width: none; 
      margin: 0;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.9);
      border: none;
      color: black;
      border-radius: 5px;
      padding: 10px;
      width: 100%;
      font-size: 16px;
      margin-bottom: 15px; 
      display: block;
    }

    .form-control::placeholder {
      color: rgba(0, 0, 0, 0.5);
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 1);
      color: black;
      outline: none;
      box-shadow: none;
    }

    .form-label {
      color: white;
      margin-bottom: 5px;
      display: block;
    }

    .password-row {
      display: flex;
      gap: 15px; 
    }
    
    .password-row > div {
        flex: 1; 
    }

    .password-row .form-control {
      margin-bottom: 0; 
    }
    .password-row .mb-3 { 
        margin-bottom: 0 !important; 
    }

    .btn-register {
      background-color: #1f2937;
      color: white;
      border: none;
      padding: 10px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.1s;
      display: block;
      width: 100%;
      text-align: center;
      margin-top: 15px; 
    }

    .btn-register:hover {
      background-color: #4B5563;
    }

    .btn-register:active {
      background-color: #6B7280;
      transform: scale(0.95);
    }

    .btn-google {
      background-color: white;
      color: black;
      border: 1px solid #d1d5db;
      padding: 10px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      display: block;
      width: 100%;
      text-align: center;
      margin-top: 15px;
      transition: background-color 0.3s;
    }

    .btn-google:hover {
      background-color: #f3f4f6;
    }

    @media (max-width: 767.98px) { 
      .col-md-4.form-column {
        height: auto; 
        min-height: 100vh; 
        width: 100% !important; 
      }
      .col-md-8.background-section {
        display: none; 
      }
      .container {
        justify-content: center; 
      }
      .form-section-inner {
        padding: 40px; 
        max-width: 450px; 
      }
      .password-row {
        flex-direction: column; 
        gap: 0; 
      }
      .password-row > div {
        margin-bottom: 15px; 
      }
      .password-row > div:last-child {
        margin-bottom: 0; 
      }
    }
  </style>
  <body>
    <div class="container">
      <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
          <a class="navbar-brand text-white d-flex align-items-center" href="#">
            <img src="foto/logoputih.png" alt="Logo" width="30" height="30" class="me-2">
            HARMONIXX
          </a>
          <a href="Login1.html" class="sign-link">Sign In!</a>
        </div>
      </nav>        
      <div class="row w-100">
        <div class="col-md-4 form-column">
          <div class="form-section-inner"> 
            <form>
              <div class="mb-3">
                <label for="exampleUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="exampleUsername" placeholder="username">
              </div>
              <div class="mb-3">
                <label for="examplePhoneNumber" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="examplePhoneNumber" placeholder="08123456789">
              </div>
              <div class="mb-3">
                <label for="exampleBirthdate" class="form-label">Birth Date</label>
                <input type="date" class="form-control" id="exampleBirthdate">
              </div>
              <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="user@email.com">
              </div>
              
              <div class="mb-3"> 
                <div class="password-row">
                  <div> 
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="password">
                  </div>
                  <div> 
                    <label for="examplePasswordConfirm" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="examplePasswordConfirm" placeholder="password">
                  </div>
                </div>
              </div>

              <button class="btn btn-google" type="button">
                <i class="fab fa-google me-2"></i> Continue with Google
              </button>

              <button class="btn-register" type="submit">Register</button>
            </form>
          </div>
        </div>
        <div class="col-md-8 background-section"></div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
  </body>
</html>
