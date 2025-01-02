<?php
include 'connection.php';
session_start();

// Atur zona waktu
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk mendapatkan IP pengunjung
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$success = $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['multi_login'];
    $password = $_POST['password'];
    $rahasia = hash('sha256', $password);
    $ipAddress = getUserIP(); // Mendapatkan IP pengunjung

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        // Login dengan email
        $pikir = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $pikir->bind_param("s", $login);
        $pikir->execute();
        $result = $pikir->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if ($user['password'] == $rahasia) {
                if ($user['status'] === 'Active') {
                    // Update waktu login dan IP
                    $currentTime = date('Y-m-d H:i:s');
                    $updateLogin = $connection->prepare("UPDATE users SET last_login = ?, last_ip = ? WHERE id = ?");
                    $updateLogin->bind_param("ssi", $currentTime, $ipAddress, $user['id']);
                    $updateLogin->execute();

                    // Simpan data user ke session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];

                    // Tambahkan welcome message ke session
                    $_SESSION['welcome_message'] = "Selamat datang, " . htmlspecialchars($user['fullname']);

                    // Set cookie untuk sesi login (berakhir saat browser ditutup)
                    setcookie("user_session", session_id(), 0, "/");

                    // Redirect sesuai role
                    switch ($user['role']) {
                        case 'Administrator':
                            header("Location: administrator-menu/index.php");
                            exit();
                        default:
                            header("Location: error-404.php");
                            exit();
                    }
                } else {
                    $error = 'Akun Anda dinonaktifkan!';
                }
            } else {
                $error = 'Password atau Email salah!!';
            }
        } else {
            $error = 'Email tidak ditemukan!';
        }
    } else {
        // Login dengan username
        $pikir = $connection->prepare("SELECT * FROM users WHERE username = ?");
        $pikir->bind_param("s", $login);
        $pikir->execute();
        $result = $pikir->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if ($user['password'] == $rahasia) {
                if ($user['status'] === 'Active') {
                    // Update waktu login dan IP
                    $currentTime = date('Y-m-d H:i:s');
                    $updateLogin = $connection->prepare("UPDATE users SET last_login = ?, last_ip = ? WHERE id = ?");
                    $updateLogin->bind_param("ssi", $currentTime, $ipAddress, $user['id']);
                    $updateLogin->execute();

                    // Simpan data user ke session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];

                    // Tambahkan welcome message ke session
                    $_SESSION['welcome_message'] = "Selamat datang, " . htmlspecialchars($user['fullname']);

                    // Set cookie untuk sesi login (berakhir saat browser ditutup)
                    setcookie("user_session", session_id(), 0, "/");

                    // Redirect sesuai role
                    switch ($user['role']) {
                        case 'Administrator':
                            header("Location: administrator-menu/index.php");
                            exit();
                        default:
                            header("Location: error-404.php");
                            exit();
                    }
                } else {
                    $error = 'Akun Anda dinonaktifkan!';
                }
            } else {
                $error = 'Username atau Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="assets/logorumah.jpg">
    <title>NexLit LOGIN</title>
    <style>
        /* Menyembunyikan elemen lain selain gambar pada tampilan kecil */
        @media (max-width: 767px) {
            .text-white, .featured-image + p, .featured-image + p + small {
                display: none;
            }
            /* Memberikan margin-bottom pada gambar agar sedikit ke bawah */
            #featured-image {
                margin-top: 20px; /* Sesuaikan dengan nilai yang Anda inginkan */
            }
        }
    </style>
</head>
<body>
    <!----------------------- Main Container -------------------------->

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <!----------------------- Login Container -------------------------->

        <div class="row border rounded-5 p-3 bg-white shadow box-area">
            <!--------------------------- Left Box ----------------------------->

            <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #0d171b">
                <div class="featured-image mb-3">
                    <img id="featured-image" src="assets/logorumah.jpg" class="img-fluid" style="width: 300px" />
                </div>
                <p class="text-white fs-2" style="font-family: 'Courier New', Courier, monospace; font-weight: 600">N E X L I T</p>
                <small class="text-white text-wrap text-center" style="width: 17rem; font-family: 'Courier New', Courier, monospace">Power Up Your Life with NEXLIT <br> Powered By Kelompok 7 SISBER 03TPLE006</small>
            </div>

            <!-------------------- ------ Right Box ---------------------------->

            <div class="col-md-6 right-box">
                <div class="row align-items-center">
                    <div class="header-text mb-4">
                        <h2>N E X L I T</h2>
                        <p>Power Up Your Life with NEXLIT</p>
                    </div>
                    <!-- Display error messages -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <form action="" method="POST" class="form-login" id="loginForm">
                        <div class="input-group mb-3">
                            <input name="multi_login" type="text" class="form-control form-control-lg bg-light fs-6" placeholder="Email or Username" />
                        </div>
                        <div class="input-group mb-2">
                            <input name="password" type="password" class="form-control form-control-lg bg-light fs-6" placeholder="Password" />
                        </div>
                        <div class="input-group mb-4 d-flex justify-content-end">
                            <div class="forgot">
                                <small><a href="forgot-password.php">Forgot Password?</a></small>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <button name="submit" class="btn btn-lg btn-primary w-100 fs-6">Login</button>
                        </div>
                        <div class="row">
                            <small>Don't have account? Chat Administrator!</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            var login = document.querySelector('input[name="multi_login"]').value;
            var password = document.querySelector('input[name="password"]').value;
            var errorAlert = document.querySelector('.alert-danger');

            if (!login || !password) {
                event.preventDefault();
                if (errorAlert) {
                    errorAlert.innerText = 'Email atau Username dan Password Kosong!';
                    errorAlert.style.display = 'block';
                }
            }
        });

        function updateImageSrc() {
            var img = document.getElementById('featured-image');
            if (window.innerWidth < 768) {  // Breakpoint untuk mobile (bisa disesuaikan)
                img.src = 'assets/logo-nexlit-depan.png';
            } else {
                img.src = 'assets/logorumah.jpg';
            }
        }

        // Jalankan fungsi saat window diresize
        window.addEventListener('resize', updateImageSrc);

        // Jalankan fungsi saat halaman pertama kali dimuat
        updateImageSrc();
    </script>
</body>
</html>
