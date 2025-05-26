<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
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

    .col-md-4 {
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

    .form-section {
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
    }

    .btn-login {
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

    .btn-login:hover {
      background-color: #4B5563;
    }

    .btn-login:active {
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

    .create-account-link {
      display: block;
      text-align: center;
      margin-top: 15px;
      color: white;
      text-decoration: none;
      font-weight: bold;
    }

    .create-account-link:hover {
      text-decoration: underline;
    }

    .remember-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      margin-top: 10px;
    }

    .forgot-link {
      color: #38BDF8;
      text-decoration: none;
    }

    .forgot-link:hover {
      text-decoration: underline;
    }

    .checkbox-remember {
      appearance: none;
      width: 18px;
      height: 18px;
      border: 2px solid #ffffff;
      background-color: transparent;
      border-radius: 4px;
      position: relative;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .checkbox-remember:checked {
      background-color: white;
      border-color: white;
    }

    .checkbox-remember:checked::after {
      content: "✔";
      font-size: 14px;
      color: #111827;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-weight: bold;
    }

    .captcha-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      gap: 10px;
    }

    .input_field.captch_box {
      display: flex;
      align-items: center;
      width: 48%;
      border-radius: 5px;
    }

    .input_field.captch_box input {
      background: rgba(255, 255, 255, 0.9);
      border: none;
      color: black;
      border-radius: 5px 0 0 5px;
      padding: 8px;
      flex-grow: 1;
      font-size: 14px;
      height: 38px;
    }

    .refresh_button {
      background: rgba(255, 255, 255, 0.9);
      border: none;
      color: black;
      cursor: pointer;
      padding: 8px 12px;
      border-radius: 0 5px 5px 0;
      height: 38px;
      display: flex;
      align-items: center;
    }

    .refresh_button i {
      font-size: 14px;
    }

    .input_field.captch_input {
      width: 48%;
    }

    .input_field.captch_input input {
      width: 100%;
      border-radius: 5px;
      padding: 8px;
      font-size: 14px;
      height: 38px;
    }

    .message {
      color: white;
      font-size: 12px;
      margin-top: 5px;
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
          <a href="register" class="sign-link">Sign Up!</a>
        </div>
      </nav>        
      <div class="row w-100">
        <div class="col-md-4 d-flex align-items-center">
          <div class="form-section">
            <form>
              <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="user@gmail.com">
              </div>
              <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="••••••••">
              </div>
              <div class="mb-3">
                <div class="captcha-section">
                  <div class="input_field captch_box">
                    <input type="text" value="" disabled />
                    <button class="refresh_button">
                      <i class="fa-solid fa-rotate-right"></i>
                    </button>
                  </div>
                  <div class="input_field captch_input">
                    <input class="form-control" type="text" placeholder="Enter captcha" />
                  </div>
                </div>
                <div class="message">Entered captcha is correct</div>
              </div>
              <div class="remember-forgot">
                <label>
                  <input type="checkbox" class="checkbox-remember"> Remember Me
                </label>
                <a href="#" class="forgot-link">Forgot Password?</a>
              </div>
              <button class="btn btn-google" type="button">
                <i class="fab fa-google me-2"></i> Continue with Google
              </button>
              <button class="btn-login" type="submit">LOGIN</button>
              <a href="register" class="create-account-link">CREATE ACCOUNT!</a>
            </form>
          </div>
        </div>
        <div class="col-md-8 background-section"></div>
      </div>
    </div>
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
  </body>
</html>
