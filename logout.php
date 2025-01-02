<?php
// Memulai session
session_start();

// Menghapus semua data dalam session
$_SESSION = array();

// Jika menggunakan cookie session, hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan session
session_destroy();

// Mengarahkan ke halaman login
header("Location: index.php");
exit();
?>
