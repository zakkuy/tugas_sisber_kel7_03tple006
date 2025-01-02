<?php
session_start();
include "../connection.php"; // Koneksi database

$profilePicture = isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'profile/default.png';

// Cek jika cookie user_session tidak ada
if (!isset($_COOKIE['user_session'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

// Cek jika pengguna tidak login atau bukan admin
if (!isset($_SESSION['email']) && !isset($_SESSION['username'])) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Arahkan ke halaman login atau ke halaman lain yang sesuai
    exit();
}

if ($_SESSION['username'] !== 'admin' && $_SESSION['email'] !== 'admin@nexlit.com') {
    // Menampilkan modal dengan style yang lebih menarik
    echo "
    <style>
        /* Styles for the modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }
        
        .modal {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: scale(0);
            animation: modalScaleUp 0.4s ease-out forwards;
        }
        
        .modal h2 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        
        .modal p {
            font-size: 18px;
            color: #555;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 25px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #45a049;
        }

        /* Animation keyframes */
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes modalScaleUp {
            to {
                transform: scale(1);
            }
        }
    </style>

    <script>
        window.onload = function() {
            var modalOverlay = document.createElement('div');
            modalOverlay.className = 'modal-overlay';
            
            var modal = document.createElement('div');
            modal.className = 'modal';
            
            var title = document.createElement('h2');
            title.textContent = 'Akses Ditolak!';
            
            var message = document.createElement('p');
            message.textContent = 'Hanya Developer yang bisa mengakses halaman ini!';
            
            var closeButton = document.createElement('button');
            closeButton.className = 'btn';
            closeButton.textContent = 'OK';
            closeButton.onclick = function() {
                window.location.href = 'index.php';
            };
            
            modal.appendChild(title);
            modal.appendChild(message);
            modal.appendChild(closeButton);
            modalOverlay.appendChild(modal);
            document.body.appendChild(modalOverlay);
        };
    </script>
    ";
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

// Jika tidak ada data yang ditemukan, arahkan ke halaman lain
if (!$user_data) {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php"); // Misalnya arahkan ke halaman login
    exit();
}

$query = "SELECT id, fullname, email, role, create_on, profile_picture, status FROM users WHERE role = 'Administrator'";
$result = $connection->query($query);

// Inisialisasi pesan error
$error = '';
$success = '';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ambil ID dari parameter URL

    // Query untuk menghapus data
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success = "Data Administrator Berhasil Di Hapus!";
        // Redirect ke halaman administrator untuk me-refresh data
        header("Location: administrator.php");
        exit(); // Pastikan setelah redirect, proses PHP tidak dilanjutkan
    } else {
        $error = "Data Administrator Gagal Di Hapus!";
    }

    $stmt->close();
}


// Update Here
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
	<link rel="icon" type="image/png" href="../assets/logorumah.jpg">
	<title>NexLit Administrator</title>
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
			<li class="active">
			<a href="administrator.php" class="btn" onclick="showLoadingAndRedirect(event, 'administrator.php')">
				<i class='bx bxs-user-circle'></i>
				<span class="text">Administrator</span>
			</a>
			</li>
			<li>
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
			<div class="head-title">
				<div class="left">
					<h1>Data Administrator</h1>
					<ul class="breadcrumb">
						<li>
							<a href="#">Administrator</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="text-decoration-none" href="index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a  class="active" href="index.php">Home</a>
						</li>
					</ul>
				</div>
				<a href="administrator-export.php" class="btn-download" value="Export Excel">
					<i class='bx bxs-cloud-download' ></i>
					<span class="text">Data Administrator</span>
				</a>
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

			<div class="head-title">
				<div class="left">
					<br>
					<a href="tambahadministrator.php" class="btn-download btn" onclick="showLoadingAndRedirect(event, 'tambahadministrator.php')">
						<i class='bx bxs-plus-circle' ></i>
						<span class="text">Tambah Administrator</span>
					</a>
				</div>
			</div>

			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Data Administrator</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>ID</th>
								<th>Foto</th>
								<th>Fullname</th>
								<th>Email</th>
								<th>Role</th>
								<th>Date Created</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							// Ambil data manager dari database

							$id = 1; // Inisialisasi ID berurut

							if ($result->num_rows > 0) {
								// Tampilkan data ke dalam tabel
								while ($row = $result->fetch_assoc()) {
									echo "<tr>";
									echo "<td style='padding-top:26px;'>" . $id . ".</td>"; // Menampilkan ID berurut

									// Tampilkan gambar profil
									$profilePic = !empty($row['profile_picture']) ? $row['profile_picture'] : 'profile/default.png'; // Cek jika foto tersedia, jika tidak gunakan default
									echo "<td><img src='{$profilePic}' alt='' style='width: 50px; height: 50px; border-radius: 50%;'></td>";

									echo "<td><p>{$row['fullname']}</p></td>";
									echo "<td>{$row['email']}</td>";
									echo "<td>{$row['role']}</td>";
									echo "<td>{$row['create_on']}</td>";
									// echo "<td>{$row['status']}</td>";
									
									// Menampilkan tombol status
									$status = $row['status'];
									$statusClass = ($status === 'Active') ? 'btn-status-active' : 'btn-status-restric';
									echo "<td><button class='btn-status $statusClass' disabled>{$status}</button></td>";

									// Menampilkan tombol ikon Update dan Delete
									echo "<td>
											<!-- Tombol Update: Arahkan ke halaman updateadministrator.php dengan parameter ID -->
											<a href='updateadministrator.php?id={$row['id']}' class='btn btn-success' title='Update'>
												<i class='bx bxs-edit'></i>
											</a>
											<a href='#' class='btn-icon btn-icon-danger' data-id='{$row['id']}' title='Delete'>
													<i class='bx bxs-trash'></i>
											</a>
										</td>";


									echo "</tr>";
									

									$id++; // Inkrementasi ID untuk baris berikutnya
								}
							} else {
								echo "<tr><td colspan='8'>No Data Found</td></tr>";
							}
							?>
						</tbody>
					</table>
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
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="script/main.js"></script>
	<script src="script.js"></script>

	<script>
		// Menambahkan konfirmasi sebelum hapus data
		document.addEventListener('DOMContentLoaded', function () {
			const deleteButtons = document.querySelectorAll('.btn-icon-danger');

			deleteButtons.forEach(function(button) {
				button.addEventListener('click', function(event) {
					event.preventDefault(); // Menghentikan aksi default dari link

					const id = this.getAttribute('data-id'); // Mendapatkan ID dari atribut data-id

					// Menampilkan SweetAlert2 untuk konfirmasi
					Swal.fire({
						title: 'Apakah Anda yakin?',
						text: 'Data Administrator ini akan dihapus secara permanen!',
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						confirmButtonText: 'Ya, Hapus!',
						cancelButtonText: 'Batal'
					}).then((result) => {
						if (result.isConfirmed) {
							// Jika konfirmasi di klik, lakukan penghapusan data
							window.location.href = `administrator.php?action=delete&id=${id}`; // Redirect ke halaman yang sama dengan parameter untuk menghapus
						}
					});
				});
			});
		});
	</script>

</body>
</html>
