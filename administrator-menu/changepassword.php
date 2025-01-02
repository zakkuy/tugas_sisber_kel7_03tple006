<?php
session_start();
include "../connection.php"; // Pastikan koneksi database diimpor

$profilePicture = isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'profile/default.png';


// Cek jika cookie user_session tidak ada
if (!isset($_COOKIE['user_session'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

// Cek jika pengguna tidak login atau bukan admin
if (!isset($_SESSION['email']) && !isset($_SESSION['username']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

// Ambil data pengguna dari database
$userId = $_SESSION['user_id']; // Pastikan session ini sudah di-set saat login
$query = "SELECT id, fullname, username, email, role, create_on, profile_picture, about, status FROM users WHERE id = ?";
$stmt = $connection->prepare($query);

if (!$stmt) {
    die("Prepare statement gagal: " . $connection->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Inisialisasi pesan error dan sukses
$error = '';
$success = '';

// Proses jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldPassword = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmNewPassword = isset($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : '';

    // Validasi form
    if (empty($oldPassword) || empty($newPassword) || empty($confirmNewPassword)) {
        $error = 'Semua kolom harus diisi!';
    } elseif ($newPassword !== $confirmNewPassword) {
        $error = 'Password baru dan konfirmasi password tidak cocok!';
    } else {
        // Ambil email dari sesi
        $email = $_SESSION['email'];
        
        // Hash password
        $hashedOldPassword = hash('sha256', $oldPassword);
        $hashedNewPassword = hash('sha256', $newPassword);

        // Periksa password lama
        $stmt = $connection->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && $user['password'] == $hashedOldPassword) {
            // Update password
            $stmt = $connection->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashedNewPassword, $email);
            
            if ($stmt->execute()) {
                $success = 'Password berhasil diubah!';
            } else {
                $error = 'Terjadi kesalahan, coba lagi!';
            }
        } else {
            $error = 'Password lama salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Bootsrap -->
	 <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/bootstap.css">
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Change Password</title>
</head>
<body onload="hide_loading();">

	<!-- SIDEBAR -->
	<section id="sidebar" class="hide">
		<a href="index.php" class="brand btn" onclick="showLoadingAndRedirect(event, 'index.php')">
			<i class='bx bxs-like'></i>
			<span class="text">NexLit</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="index.php" class="btn" onclick="showLoadingAndRedirect(event, 'index.php')">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
			<li>
				<a href="belitoken.php" class="btn" onclick="showLoadingAndRedirect(event, 'belitoken.php')">
					<i class='bx bxs-zap' ></i>
					<span class="text">Token</span>
				</a>
			</li>
			<li>
				<a href="customer.php" class="btn" onclick="showLoadingAndRedirect(event, 'customer.php')">
					<i class='bx bxs-user' ></i>
					<span class="text">Data Customer</span>
				</a>
			</li>
			<li>
				<a href="administrator.php" class="btn" onclick="showLoadingAndRedirect(event, 'administrator.php')">
					<i class='bx bxs-user-circle'></i>
					<span class="text">Administrator</span>
				</a>
			</li>
			<li class="active">
				<a href="settings.php" class="btn" onclick="showLoadingAndRedirect(event, 'settings.php')">
					<i class='bx bxs-cog' ></i>
					<span class="text">Settings</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="logout.php" class="logout btn" onclick="showLoadingAndRedirect(event, 'logout.php')">
					<i class='bx bxs-log-out-circle' ></i>
					<span class="text">Logout</span>
				</a>
			</li>
		</ul>
	</section>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu' ></i>
			<form action="#">
			</form>

			<a href="settings.php" class="profile btn" onclick="showLoadingAndRedirect(event, 'settings.php')">
				<img src="<?php echo htmlspecialchars($user_data['profile_picture']); ?>" alt="Profile Image">
			</a>

			<a href="logout.php" class="notification">
				<i class='bx bxs-exit' style="color: gray; transition: color 0.3s ease;" onmouseover="this.style.color='red'" onmouseout="this.style.color='gray'"></i>
				<!-- <span class="num">8</span> -->
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>

            <div class="table-data">
				<div class="order">
						<div class="form-row">
							<div class="form-group col-md-6">
								<div class="profile-picture-container">
									<label class="profile-picture" for="inputPhoto">Profile Picture</label>
									<div class="profile-picture-circle">
										<label for="inputPhoto">
										<a href="settings.php" class="profile btn" onclick="showLoadingAndRedirect(event, 'settings.php')">
											<img src="<?php echo htmlspecialchars($user_data['profile_picture']); ?>" alt="Profile Image">
										</a>
										</label>
									</div>
								</div>
							</div>
						</div>

						<div class="container mt-4">
							<?php if ($success): ?>
								<div class="alert alert-success">
									<?php echo htmlspecialchars($success); ?>
								</div>
							<?php endif; ?>
							<?php if ($error): ?>
								<div class="alert alert-danger">
									<?php echo htmlspecialchars($error); ?>
								</div>
							<?php endif; ?>
						</div>

						<form action="changepassword.php" method="POST">
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="inputPassword">Old Password</label>
									<input type="password" class="form-control" id="inputPassword" name="old_password" placeholder="Masukkan Password Lama">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="inputNewPassword">New Password</label>
									<input type="password" class="form-control" id="inputNewPassword" name="new_password" placeholder="Masukkan Password Baru">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="inputConfimNewPassword">Confirm New Password</label>
									<input type="password" class="form-control" id="inputConfimNewPassword" name="confirm_new_password" placeholder="Masukkan Konfirmasi Password">
								</div>
							</div>
						<button type="submit" class="btn">Change Password</button>
						<button type="button" class="btn" onclick="showLoadingAndRedirect('settings.php')">Cancel</button>
					</form>
				</div>
			</div>
            
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<!-- Animasi Loading -->
	 <div class="loading-overlay">
		<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
	 </div>
	

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
	<script src="script/main.js"></script>
	<script src="script.js"></script>

</body>
</html>
