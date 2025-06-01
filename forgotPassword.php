<?php
session_start();
require_once 'db.php'; 

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        if (empty($email)) {
            $error_message = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['reset_email'] = $email;
                header("Location: forgotPassword2.php");
                exit();
            } else {
                $error_message = 'Email address not found in our records.';
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
    <title>Forgot Password - Step 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        content: ""; position: fixed; top: 0; left: 0;
        width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: -1;
      }
      .container {
        height: 100vh; display: flex; align-items: center; justify-content: flex-start;
        padding: 0 !important; width: 100%; max-width: none;
      }
      .row { height: 100%; margin: 0; width: 100%; }
      .col-md-4 {
        background-color: #142F86; height: 100vh; display: flex;
        align-items: center; justify-content: center;
        padding-left: 0 !important; padding-right: 0 !important; z-index: 1;
      }
      .col-md-8.background-section { height: 100vh; background: transparent; padding: 0; }
      .navbar { background: transparent !important; padding: 10px 20px; z-index: 2; width: 100%; }
      .navbar-brand { font-size: 18px; font-weight: bold; }
      .sign-link { color: white; text-decoration: none; }
      .sign-link:hover { text-decoration: underline; }
      .form-section { background: transparent; padding: 40px 20px; width: 100%; max-width: none; margin: 0; }
      .form-control {
        background: rgba(255, 255, 255, 0.9); border: none; color: black;
        border-radius: 5px; padding: 10px; width: 100%; font-size: 16px;
      }
      .form-control::placeholder { color: rgba(0, 0, 0, 0.5); }
      .form-control:focus {
        background: rgba(255, 255, 255, 1); color: black; outline: none; box-shadow: none;
      }
      .form-label { color: white; }
      .btn-confirm {
        background-color: #1f2937; color: white; border: none; padding: 10px;
        font-size: 16px; border-radius: 5px; cursor: pointer;
        transition: background-color 0.3s, transform 0.1s;
        display: block; width: 100%; text-align: center; margin-top: 15px;
      }
      .btn-confirm:hover { background-color: #4B5563; }
      .btn-confirm:active { background-color: #6B7280; transform: scale(0.95); }
      .register-link {
        display: block; text-align: center; margin-top: 15px;
        color: white; text-decoration: none; font-weight: bold;
      }
      .register-link:hover { text-decoration: underline; }
      .form-error, .form-success { /* Combined for similar styling */
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
          <a class="navbar-brand text-white d-flex align-items-center" href="login.php"> 
            <img src="foto/logoputih.png" alt="Logo" width="30" height="30" class="me-2">
            HARMONIXX
          </a>
          <a href="register.php" class="sign-link">Sign Up!</a> 
        </div>
      </nav>        
      <div class="row w-100">
        <div class="col-md-4 d-flex align-items-center">
          <div class="form-section">
            <form id="forgotPasswordForm" method="POST" action="forgotPassword.php"> 
              <div>
                <h3 style="text-align: center;">Forgot Password</h3>
                <p class="text-center small mb-3">Enter your email address and we'll assist you in resetting your password.</p>
              </div>

              <?php if (!empty($error_message)): ?>
                <div class="form-error"><?php echo $error_message; ?></div>
              <?php endif; ?>
              <?php if (!empty($success_message)): ?>
                <div class="form-success"><?php echo $success_message; ?></div>
              <?php endif; ?>

              <div class="mb-3">
                <label for="emailInput" class="form-label">Email address</label>
                <input type="email" class="form-control" id="emailInput" name="email" aria-describedby="emailHelp" placeholder="user@email.com" required>
              </div>
              <button class="btn-confirm" type="submit">Confirm Email</button> 
              <a href="login.php" class="register-link mt-3">Back to Login</a>
              <a href="register.php" class="register-link">Create Account?</a>
            </form>
          </div>
        </div>
        <div class="col-md-8 background-section"></div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
