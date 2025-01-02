<?php
session_start();
include "../connection.php"; // Koneksi database

// Default foto profil jika tidak tersedia
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

// Ambil ID dari URL
if (isset($_GET['id'])) {
    $adminId = $_GET['id'];

    // Ambil data administrator berdasarkan ID
    $query = "SELECT id, fullname, username, email, role, profile_picture, status FROM users WHERE id = ?";
    $stmt = $connection->prepare($query);

    if (!$stmt) {
        die("Prepare statement gagal: " . $connection->error);
    }

    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_data = $result->fetch_assoc();

    // Jika data tidak ditemukan, arahkan ke halaman administrator
    if (!$admin_data) {
        header("Location: administrator.php");
        exit();
    }
} else {
    // Jika tidak ada ID, arahkan ke halaman administrator
    header("Location: administrator.php");
    exit();
}

// Inisialisasi pesan error atau sukses
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input data
    $fullname = mysqli_real_escape_string($connection, $_POST['fullname']);
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    
    // Check if the username is unique
    $usernameCheckQuery = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt = $connection->prepare($usernameCheckQuery);
    $stmt->bind_param("si", $username, $adminId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Username Sudah Digunakan!";
    } else {
        // Handle password
        if (!empty($_POST['password']) && !empty($_POST['confirmPassword'])) {
            // Periksa apakah password dan konfirmasi password cocok
            if ($_POST['password'] !== $_POST['confirmPassword']) {
                $error = "Password Tidak Sama!";
            } else {
                // Hash password baru with SHA-256
                $password = hash('sha256', $_POST['password']); // Using SHA-256 hash
            }
        } else {
            // If no password is provided, use the existing password
            $password = null;
        }

        // If no error, update the data
        if (empty($error)) {
            // If password is provided, update password, otherwise skip password
            $updateQuery = "UPDATE users SET fullname = ?, username = ?, email = ?, status = ?";

            // Add password if new password is set
            if ($password !== null) {
                $updateQuery .= ", password = ?";
            }

            $updateQuery .= " WHERE id = ?";

            // Prepare the update statement
            $stmt = $connection->prepare($updateQuery);

            if ($stmt) {
                if ($password !== null) {
                    // If updating password, bind the parameters with the new password
                    $stmt->bind_param("sssssi", $fullname, $username, $email, $status, $password, $adminId);
                } else {
                    // If no password, just bind without password
                    $stmt->bind_param("ssssi", $fullname, $username, $email, $status, $adminId);
                }

                // Execute the update query
                if ($stmt->execute()) {
                    $success = "Administrator data updated successfully!";
                } else {
                    $error = "Error updating administrator data!";
                }
            } else {
                $error = "Prepare statement failed: " . $connection->error;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.21/dist/sweetalert2.min.css" rel="stylesheet">
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/bootstap.css">
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Update Admin</title>
</head>
<body>

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
				<a href="report.php" class="btn" onclick="showLoadingAndRedirect(event, 'report.php')">
					<i class='bx bxs-report'></i>
					<span class="text">Report Transaksi</span>
				</a>
			</li>
			<li class="active">
				<a href="administrator.php" class="btn" onclick="showLoadingAndRedirect(event, 'administrator.php')">
					<i class='bx bxs-user-circle'></i>
					<span class="text">Administrator</span>
				</a>
			</li>
			<li>
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
			<form action="#"></form>

			<a href="settings.php" class="profile btn" onclick="showLoadingAndRedirect(event, 'settings.php')">
				<img src="<?php echo htmlspecialchars($user_data['profile_picture']); ?>" alt="Profile Image">
			</a>

			<a href="logout.php" class="notification">
				<i class='bx bxs-exit' style="color: gray; transition: color 0.3s ease;" onmouseover="this.style.color='red'" onmouseout="this.style.color='gray'"></i>
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Update Data Administrator</h1>
					<ul class="breadcrumb">
						<li><a href="#">Update Administrator</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a href="administrator.php">Administrator</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
						<li><a class="active" href="index.php">Home</a></li>
					</ul>
				</div>
			</div>

			<!-- Display success or error messages -->
			<div class="container mt-4">
				<?php if ($success): ?>
					<div class="alert alert-success">
						<?php echo htmlspecialchars($success); ?>
					</div>
				<?php endif; ?>
				<?php if ($error): ?>
					<div class="alert alert-error">
						<?php echo htmlspecialchars($error); ?>
					</div>
				<?php endif; ?>
			</div>

            <div class="table-data">
				<div class="order">
				<form method="POST">
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputFullname">Fullname</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($admin_data['fullname']); ?>" required>
						</div>
						<div class="form-group col-md-6">
							<label for="inputUsername">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputEmail">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
						</div>
						<div class="form-group col-md-6">
							<label for="inputRole">Role</label>
							<input type="text" class="form-control" id="inputRole" value="Administrator" disabled>
						</div>
					</div>
					<div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputPassword">Password</label>
                            <input type="password" class="form-control" id="inputPassword" name="password" placeholder="Masukkan Password">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputConfirmPassword">Confirm Password</label>
                            <input type="password" class="form-control" id="inputConfirmPassword" name="confirmPassword" placeholder="Masukkan Confirm Password">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputstatus">Status</label>
                            <select class="form-control" id="inputstatus" name="status" required>
                                <option value="Active" <?php echo ($admin_data['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Restric" <?php echo ($admin_data['status'] == 'Restric') ? 'selected' : ''; ?>>Restric</option>
                            </select>
                        </div>
                    </div>
					<button type="submit" class="btn" onclick="confirmUpdate()">Update Data</button>
					<button type="button" class="btn" onclick="showLoadingAndRedirect('administrator.php')">Cancel</button>
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
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.21/dist/sweetalert2.all.min.js"></script>
    <script src="script.js"></script>

    <script>
        // Fungsi untuk menampilkan SweetAlert
		function showSuccessAlert() {
			// Menampilkan alert sukses
			Swal.fire({
				title: 'Berhasil!',
				text: 'Data Administrator berhasil diupdate.',
				icon: 'success',
				confirmButtonText: 'Setuju',
				allowOutsideClick: false, // Tidak dapat klik di luar untuk menutup
				showCancelButton: false // Hanya ada tombol Setuju
			}).then((result) => {
				if (result.isConfirmed) {
					// Setelah tombol setuju ditekan, Anda bisa melakukan redirect atau aksi lainnya
					window.location.href = "administrator.php"; // Contoh redirect ke halaman customer
				}
			});
		}

		function showErrorAlert() {
			// Menampilkan alert error
			Swal.fire({
				title: 'Error!',
				text: 'Terjadi kesalahan saat mengupdate Data Administrator.',
				icon: 'error',
				confirmButtonText: 'Coba Lagi',
				allowOutsideClick: false, // Tidak dapat klik di luar untuk menutup
				showCancelButton: false // Hanya ada tombol Coba Lagi
			});
		}

		// Mengecek apakah ada sukses atau error
		<?php if ($success): ?>
			showSuccessAlert(); // Tampilkan alert sukses
		<?php endif; ?>
		<?php if ($error): ?>
			showErrorAlert(); // Tampilkan alert error
		<?php endif; ?>
    </script>

</body>
</html>
