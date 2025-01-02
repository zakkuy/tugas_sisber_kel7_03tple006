<?php
session_start(); // Mulai sesi
include 'connection.php';

// Pastikan email disimpan di sesi
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit(); // Hentikan eksekusi jika tidak ada sesi email
}

$email = $_SESSION['reset_email'];
$success = '';
$error = '';


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

    // Cek apakah username sesuai dengan yang ada di database
        $sql = "SELECT username FROM users WHERE email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($db_username);
        $stmt->fetch();
        $stmt->close();

        if ($username !== $db_username) {
            $error = 'Username tidak sesuai!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Password tidak sama!';
        } else {
            // Hash password baru
            $hashed_password = hash('sha256', $new_password);

            // Update password di database
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $email);

            if ($stmt->execute()) {
                $success = 'Password berhasil diubah!';
                // Hapus email dari sesi setelah berhasil
                unset($_SESSION['reset_email']);
            } else {
                $error = 'Password gagal diubah!';
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
    <title>Change Password</title>
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
                    <div class="header-text mb-4">
                        <h2>N E X L I T</h2>
                        <p>Next Generation Your Listrik Token</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="forgot-password-confirm.php" method="post">
                        <div class="input-group mb-2">
                            <input type="text" class="form-control form-control-lg bg-light fs-6" value="<?php echo htmlspecialchars($email); ?>" disabled />
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" name="username" class="form-control form-control-lg bg-light fs-6" placeholder="Confirm Username!" required />
                        </div>
                        <div class="input-group mb-2">
                            <input type="password" name="new_password" class="form-control form-control-lg bg-light fs-6" placeholder="New Password" required />
                        </div>
                        <div class="input-group mb-2">
                            <input type="password" name="confirm_password" class="form-control form-control-lg bg-light fs-6" placeholder="Confirm New Password" required />
                        </div>
                        <div class="input-group mb-5">
                            <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Send</button>
                        </div>
                        <div class="row">
                            <small>Login your account? <a href="administrator-menu/index.php">Login</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
