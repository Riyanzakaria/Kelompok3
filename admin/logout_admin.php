<?php
session_start();

// Hapus semua variabel sesi yang terkait admin
unset($_SESSION['admin_id']); 
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

// Atau untuk menghancurkan semua data sesi (jika sesi hanya untuk admin)
session_unset();
session_destroy();

// Arahkan ke halaman login admin
header("Location: admin_login.php");
exit;
?>