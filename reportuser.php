<?php
session_start(); // Mulai session
include 'connection.php';

// Cek apakah session sudah ada, jika tidak, redirect ke halaman login
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect ke halaman login
    exit;
}

// Ambil data session pengguna
$username = $_SESSION['username'];
$nama_pelanggan = $_SESSION['nama_pelanggan']; // Ambil nama pelanggan dari session

// Tentukan jumlah data yang ditampilkan per halaman
$items_per_page = 5;

// Tentukan halaman saat ini, jika tidak ada maka default ke halaman pertama
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Hitung offset untuk query
$offset = ($current_page - 1) * $items_per_page;

// Query untuk mengambil transaksi berdasarkan created_by yang sama dengan username yang login
$query = "SELECT id, tanggal, invoice_number, created_by FROM transaksi WHERE created_by = ? LIMIT $offset, $items_per_page"; 
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $username); // Binding parameter username untuk memastikan hanya transaksi yang dibuat oleh pengguna yang aktif
$stmt->execute();
$result = $stmt->get_result();

// Query untuk menghitung jumlah total transaksi
$sql_total = "SELECT COUNT(*) AS total FROM transaksi WHERE created_by = ?";
$stmt_total = $connection->prepare($sql_total);
$stmt_total->bind_param("s", $username);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$row_total = $result_total->fetch_assoc();
$total_items = $row_total['total'];

// Hitung jumlah halaman
$total_pages = ceil($total_items / $items_per_page);

// Inisialisasi ID berurut
$id = $offset + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/logorumah.jpg">
    <title>Report Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="administrator-menu/style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        /* Footer styling */
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 14px;
            color: #666; /* Warna teks footer */
        }

        .footer p {
            margin: 0;
        }

        nav a.active {
            position: relative; /* Membuat elemen relatif agar pseudo-element bisa ditempatkan */
        }

        nav a.active::after {
            content: ''; /* Pseudo-element kosong untuk garis */
            position: absolute;
            bottom: -16px; /* Letakkan garis di bawah elemen */
            left: 0;
            width: 100%; /* Panjang garis mengikuti lebar elemen */
            height: 3px; /* Ketebalan garis */
            background-color: #f97316; /* Warna oranye */
            border-radius: 2px; /* Membulatkan ujung garis */
        }

        .btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .btn-success {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        color: #fff;
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn i {
        margin-right: 4px;
    }

    .btn:focus {
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    </style>
</head>
<body class="bg-gray-100">
<header class="bg-[#0d171b] p-4 flex justify-between items-center">
    <div class="flex items-center">
        <img src="assets/logorumah.jpg" alt="NexLit logo" class="mr-2" width="40" height="40">
        <span class="text-white text-lg font-bold">NexLit</span>
        <span class="text-white ml-2 text-sm sm:text-base md:text-lg lg:text-xl">| Power Up Your Life with NEXLIT</span>
    </div>
    <div class="text-white flex items-center justify-center">
        <div class="sm:hidden flex justify-center items-center w-12 h-12 bg-orange-500 text-white font-bold">
            <?= strtoupper(substr($nama_pelanggan, 0, 1)) ?>
        </div>
        <p class="hidden sm:block ml-2">Hi, <?= ucwords(strtolower($nama_pelanggan)) ?></p>
    </div>
</header>
<nav class="bg-white shadow-md">
    <div class="container mx-auto flex justify-around py-4">
        <a href="pembeliantoken.php" class="text-center">
            <i class="fas fa-lightbulb text-2xl text-orange-500"></i>
            <p>Token NexLit</p>
        </a>
        <a href="profileuser.php" class="text-center">
            <i class="fas fa-user text-2xl text-orange-500"></i>
            <p>Profile User</p>
        </a>
        <a href="#" class="text-center active">
            <i class="fas fa-file text-2xl text-orange-500"></i>
            <p>Report Pembelian</p>
        </a>
        <a href="logout.php" class="text-center">
            <i class="fas fas fa-sign-out-alt text-2xl text-red-500"></i>
            <p>Keluar</p>
        </a>
    </div>
</nav>

<main class="container mx-auto mt-6 lg:max-w-4xl">
    <form id="form-cari" class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Report Pembelian</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">ID</th>
                        <th class="px-4 py-2 border">Tanggal</th>
                        <th class="px-4 py-2 border">Invoice Number</th>
                        <th class="px-4 py-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Format tanggal menjadi d-m-Y
                        $formatted_date = date("d-m-Y", strtotime($row['tanggal']));                                 
                        $invoice_number = htmlspecialchars($row['invoice_number']);
        
                        echo "<tr>";
                        echo "<td class='px-4 py-2 border text-center align-middle'>{$id}.</td>";
                        echo "<td class='px-4 py-2 border text-center align-middle'>{$formatted_date}</td>";
                        echo "<td class='px-4 py-2 border text-center align-middle'>{$invoice_number}</td>";

                        echo "<td class='px-4 py-2 border text-center align-middle'>
                                <form action='invoice.php' method='get'>
                                    <input type='hidden' name='invoice_number' value='{$invoice_number}'>
                                    <button type='submit' class='btn btn-success' title='Print Invoice'>
                                        <i class='bx bxs-printer'></i>
                                    </button>
                                </form>
                            </td>";
                        echo "</tr>";
                        $id++; // Increment nomor urut
                    }
                } else {
                    echo "<tr><td colspan='4' class='px-4 py-2 border text-center align-middle'>No Data Found</td></tr>";
                }
                ?>
            </tbody>
            </table>
        </div>

        <!-- Pagination Control -->
        <div class="flex justify-center items-center mt-4 space-x-2">
            <!-- Prev Button -->
            <div>
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?= $current_page - 1 ?>" class="btn btn-success">Prev</a>
                <?php endif; ?>
            </div>

            <!-- Page Number Buttons -->
            <div class="flex space-x-2">
                <?php
                // Menampilkan halaman numerik dalam rentang 5 halaman
                $start_page = floor(($current_page - 1) / 5) * 5 + 1;
                $end_page = min($start_page + 4, $total_pages);

                for ($page = $start_page; $page <= $end_page; $page++) {
                    if ($page == $current_page) {
                        echo "<a href='?page=$page' class='btn btn-success'>$page</a>";
                    } else {
                        echo "<a href='?page=$page' class='btn'>$page</a>";
                    }
                }
                ?>
            </div>

            <!-- Next Button -->
            <div>
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $current_page + 1 ?>" class="btn btn-success">Next</a>
                <?php endif; ?>
            </div>
        </div>

    </form>
    
</main>
    <footer class="footer">
        <p>&copy; 2024 NexLit | Powered by Kelompok 7 03TPLE006</p>
    </footer>

</body>
</html>
