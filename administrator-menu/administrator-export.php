<?php

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=administrator.xls");

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

$query = "SELECT id, fullname, email, role, create_on, profile_picture, status, about, last_login, last_ip FROM users WHERE role = 'Administrator'";
$result = $connection->query($query);

// Inisialisasi pesan error
$error = '';
$success = '';

// Update Here
?>
<!DOCTYPE html>
<html lang="en">
<body>

	<!-- CONTENT -->
	<section id="content">
		<!-- MAIN -->
		<main>
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3 style="text-align: center;">Data Administrator</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
						<thead>
							<tr>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">ID</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">Fullname</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">Email</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">Role</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">About</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">Last Login</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">Last Address</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">Date Created</th>
								<th style="border: 1px solid black; padding: 8px; text-align: left;">Status</th>
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
									echo "<td style='border: 1px solid black; padding: 8px;'>" . $id . "</td>"; // Menampilkan ID berurut tanpa format tambahan
									echo "<td style='border: 1px solid black; padding: 8px;'><p>{$row['fullname']}</p></td>";
									echo "<td style='border: 1px solid black; padding: 8px;'>{$row['email']}</td>";
									echo "<td style='border: 1px solid black; padding: 8px;'>{$row['role']}</td>";
									echo "<td style='border: 1px solid black; padding: 8px;'>{$row['about']}</td>";
									echo "<td style='border: 1px solid black; padding: 8px;'>{$row['last_login']}</td>";
									echo "<td style='border: 1px solid black; padding: 8px;'>{$row['last_ip']}</td>";
									echo "<td style='border: 1px solid black; padding: 8px;'>{$row['create_on']}</td>";

									// Menampilkan tombol status
									$status = $row['status'];
									$statusClass = ($status === 'Active') ? 'btn-status-active' : 'btn-status-restric';
									echo "<td style='border: 1px solid black; padding: 8px;'><button class='btn-status $statusClass' disabled>{$status}</button></td>";

									echo "</tr>";

									$id++; // Inkrementasi ID untuk baris berikutnya
								}
							} else {
								echo "<tr><td colspan='9' style='border: 1px solid black; padding: 8px; text-align: center;'>No Data Found</td></tr>";
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

</body>
</html>
