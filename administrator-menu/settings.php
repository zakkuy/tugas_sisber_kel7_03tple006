<?php
session_start();
include "../connection.php"; // Koneksi database

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

// Ambil ID pengguna dari session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk mendapatkan data pengguna
$query = "SELECT id, fullname, username, email, role, create_on, profile_picture, about, status FROM users WHERE id = ?";
$stmt = $connection->prepare($query);

if (!$stmt) {
    die("Prepare statement gagal: " . $connection->error);
}

$stmt->bind_param("i", $user_id); // Bind parameter ID pengguna
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
    die("Data pengguna tidak ditemukan");
}

// Variabel untuk pesan error dan sukses
$error = '';
$success = '';

// Proses pembaruan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $about = $_POST['about'] ?? '';

    // Validasi data yang diperlukan
    if (empty($fullname) || empty($username) || empty($email) || empty($about)) {
        $_SESSION['error_message'] = "Semua kolom wajib diisi!";
    } else {
        // Cek apakah email atau username sudah digunakan
        $check_query = "SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?";
        $check_stmt = $connection->prepare($check_query);
        if (!$check_stmt) {
            $_SESSION['error_message'] = "Gagal melakukan pengecekan email/username: " . $connection->error;
        } else {
            $check_stmt->bind_param("ssi", $email, $username, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                $_SESSION['error_message'] = "Email atau Username sudah digunakan!";
            } else {
                // Menangani foto profil jika diubah
                $profile_picture = $user_data['profile_picture']; // Default, jika foto tidak diubah
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                    // Menangani upload foto
                    $upload_dir = '9ba88c4165381adc0eeae696d66c699577dd1d0e209d81c9644890bfe87546d8/901c1bb397753b8419382e08fc8652f6382a100d0984ded7d64aeff08bb814bd/';
                    $file_name = basename($_FILES['profile_picture']['name']);
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Buat nama file unik dengan menggunakan uniqid() atau bisa menggunakan md5()
                    $unique_file_name = uniqid('profile_', true) . '.' . $file_extension; // Nama file unik

                    // Validasi jenis file gambar
                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $upload_path = $upload_dir . $unique_file_name;
                        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                            $profile_picture = $upload_path; // Set path foto baru
                        } else {
                            $_SESSION['error_message'] = "Gagal mengunggah foto profil!";
                        }
                    } else {
                        $_SESSION['error_message'] = "Jenis file tidak didukung, hanya JPG, PNG, dan GIF yang diizinkan.";
                    }
                }

                // Update query jika tidak ada error
                if (!isset($_SESSION['error_message'])) {
                    $update_query = "UPDATE users SET fullname = ?, username = ?, email = ?, about = ?, profile_picture = ? WHERE id = ?";
                    $update_stmt = $connection->prepare($update_query);
                    if (!$update_stmt) {
                        $_SESSION['error_message'] = "Prepare statement gagal: " . $connection->error;
                    } else {
                        $update_stmt->bind_param("sssssi", $fullname, $username, $email, $about, $profile_picture, $user_id);
                        if ($update_stmt->execute()) {
                            $_SESSION['success_message'] = "Data berhasil diperbarui!";
                            // Redirect untuk memuat ulang data
                            header("Location: " . $_SERVER['PHP_SELF']); // Halaman ini akan dimuat ulang untuk menampilkan data terbaru
                            exit();
                        } else {
                            $_SESSION['error_message'] = "Gagal memperbarui data!";
                        }
                    }
                }
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

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- Sweetalert -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/bootstap.css">
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Settings</title>
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
				<i class='bx bxs-dashboard'></i>
				<span class="text">Dashboard</span>
			</a>
			</li>
			<li>
			<a href="belitoken.php" class="btn" onclick="showLoadingAndRedirect(event, 'belitoken.php')">
				<i class='bx bxs-zap'></i>
				<span class="text">Token</span>
			</a>
			</li>
			<li>
			<a href="customer.php" class="btn" onclick="showLoadingAndRedirect(event, 'customer.php')">
				<i class='bx bxs-user'></i>
				<span class="text">Data Customer</span>
			</a>
			</li>
			<li>
			<a href="report.php" class="btn" onclick="showLoadingAndRedirect(event, 'report.php')">
				<i class='bx bxs-report'></i>
				<span class="text">Report Transaksi</span>
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
				<i class='bx bxs-cog'></i>
				<span class="text">Settings</span>
			</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
			<a href="logout.php" class="logout btn" onclick="showLoadingAndRedirect(event, 'logout.php')">
				<i class='bx bxs-log-out-circle'></i>
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
			<!-- Alert area -->
			<div id="alert-container">
				<?php
				// Menampilkan pesan sukses
				if (isset($_SESSION['success_message'])) {
					echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
					unset($_SESSION['success_message']); // Menghapus pesan sukses setelah ditampilkan
				}

				// Menampilkan pesan error
				if (isset($_SESSION['error_message'])) {
					echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
					unset($_SESSION['error_message']); // Menghapus pesan error setelah ditampilkan
				}
				?>
			</div>

            <div class="table-data">
				<div class="order">
				<form action="" method="post" enctype="multipart/form-data">
						<div class="form-row">
							<div class="form-group col-md-6">
								<div class="profile-picture-container">
									<label class="profile-picture" for="inputPhoto">Profile Picture<span class="editable"> *</span></label>
									<div class="profile-picture-circle">
										<label for="inputPhoto">
											<img src="<?php echo htmlspecialchars($user_data['profile_picture'] ?? 'default.png'); ?>" alt="Profile Picture" id="profilePicturePreview">
										</label>
									</div>
								</div>
								<input type="file" class="form-control" id="inputPhoto" name="profile_picture" accept="image/*" onchange="previewProfilePicture(event)">
							</div>
						</div>
						<div class="form-group">
							<label for="customTextarea">About<span class="editablef"> *</span></label>
							<textarea id="customTextarea" name="about" rows="3" class="custom-textarea" maxlength="254" 
									oninput="updateCharacterCount()"><?php echo htmlspecialchars($user_data['about'] ?? ''); ?></textarea>
							<small id="charCount">0/254</small>
						</div>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="inputFullname">Fullname<span class="editablef"> *</span></label>
								<input type="text" class="form-control" id="inputFullname" name="fullname" value="<?php echo htmlspecialchars($user_data['fullname'] ?? ''); ?>" placeholder="Masukkan Fullname">
							</div>
							<div class="form-group col-md-6">
								<label for="inputUsername">Username<span class="editable"> *</span></label>
								<input type="text" class="form-control" id="inputUsername" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" placeholder="Masukkan Username">
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="inputEmail">Email<span class="editable"> *</span></label>
								<input type="email" class="form-control" id="inputEmail" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" placeholder="Masukkan Email Address">
							</div>
							<div class="form-group col-md-6">
								<label for="inputRole">Role</label>
                                <input type="text" class="form-control" id="inputRole" name="role" value="<?php echo htmlspecialchars($user_data['role']); ?>" readonly>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label for="inputStatus">Status</label>
                                <input type="text" class="form-control" id="inputStatus" name="status" value="<?php echo htmlspecialchars($user_data['status']); ?>" readonly>
							</div>
							<div class="form-group col-md-6">
								<label for="inputCreate On">Create On</label>
                                <input type="text" class="form-control" id="inputCreateOn" name="create_on" value="<?php echo htmlspecialchars($user_data['create_on']); ?>" readonly>
							</div>
						</div>
						<button type="submit" class="btn">Update Data</button>
						<button type="button" class="btn" onclick="showLoadingAndRedirect('changepassword.php')">Change Password</button>
						<button type="button" class="btn" onclick="showLoadingAndRedirect('index.php')">Kembali</button>
					</form>

					<!-- Alert area -->
					<div id="alert-container"></div>

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
	
	<script>
		function updateCharacterCount() {
			var textarea = document.getElementById("customTextarea");
			var charCount = document.getElementById("charCount");
			charCount.textContent = textarea.value.length + "/254";
		}

		// Panggil fungsi updateCharacterCount saat halaman dimuat untuk memperbarui jumlah karakter
		window.onload = function() {
			updateCharacterCount();
		};
	</script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
	<script src="script/main.js"></script>
	<script src="script.js"></script>
	<script src="application/json.js"></script>

</body>
</html>
