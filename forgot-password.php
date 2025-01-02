<?php
require 'connection.php'; // Pastikan file ini berisi koneksi ke database
session_start(); // Mulai sesi

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil email dari form
    $email = trim($_POST['email']);

    // Validasi email
    if (empty($email)) {
        $error = 'Email is required.';
    } else {
        // Cek keberadaan email di database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email ditemukan, simpan email dalam sesi dan redirect ke halaman konfirmasi
            $_SESSION['reset_email'] = $email;
            header("Location: forgot-password-confirm.php");
            exit(); // Pastikan untuk menghentikan eksekusi lebih lanjut setelah redirect
        } else {
            // Email tidak ditemukan, set pesan error
            $error = 'Email tidak ditemukan, periksa kembali email anda!';
        }

        $stmt->close();
        $connection->close();
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
    <title>Forgot Password</title>
</head>
<body>
    <!----------------------- Main Container -------------------------->

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <!----------------------- Login Container -------------------------->

        <div class="row border rounded-5 p-3 bg-white shadow box-area">
            <!--------------------------- Left Box ----------------------------->

            <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #0d171b">
                <div class="featured-image mb-3">
                    <img src="assets/logorumah.jpg" class="img-fluid" style="width: 250px" />
                </div>
                <p class="text-white fs-2" style="font-family: 'Courier New', Courier, monospace; font-weight: 600">N E X L I T</p>
                <small class="text-white text-wrap text-center" style="width: 17rem; font-family: 'Courier New', Courier, monospace">Power Up Your Life with NEXLIT Powered By Kelompok 7 SISBER 03TPLE006</small>
            </div>

            <!-------------------- ------ Right Box ---------------------------->

            <div class="col-md-6 right-box">
                <div class="row align-items-center">
                    <div class="header-text mb-3">
                        <h2>Forgot Password</h2>
                        <p>Enter your registered email</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="forgot-password.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <div class="input-group mb-2">
                            <input type="text" name="email" class="form-control form-control-lg bg-light fs-6" placeholder="Email address" required />
                        </div>
                        <div class="input-group mb-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Send</button>
                        </div>
                    </form>
                    <small>You Have an account? <a href="administrator-menu/index.php">Login</a></small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
