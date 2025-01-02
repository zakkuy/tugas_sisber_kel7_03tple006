<?php

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=data-customer.xls");

session_start();
include "../connection.php"; // Koneksi database

// Validasi sesi dan user session
if (!isset($_COOKIE['user_session']) || !isset($_SESSION['email']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../d5d10c898b50ab6c8c2406ee318952e8d354fd2041f06fcc1755d0e90224eca8.php");
    exit();
}

// Ambil data dari database
$query = "SELECT id, no_meteran, nama_pelanggan, kontak_pelanggan, tipe_rumah, alamat, id_tarif, daya FROM customer";
$result = $connection->query($query);

if (!$result) {
    die("Query error: " . $connection->error);
}

$id = 1; // ID incremental
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Customer</title>
</head>
<body>
    <h3 style="text-align: center;">Data Customer</h3>
    <table style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 5px;">ID</th>
                <th style="border: 1px solid #000; padding: 5px;">No Meteran</th>
                <th style="border: 1px solid #000; padding: 5px;">Nama Pelanggan</th>
                <th style="border: 1px solid #000; padding: 5px;">Kontak</th>
                <th style="border: 1px solid #000; padding: 5px;">Tipe Rumah</th>
                <th style="border: 1px solid #000; padding: 5px;">Alamat</th>
                <th style="border: 1px solid #000; padding: 5px;">Tarif</th>
                <th style="border: 1px solid #000; padding: 5px;">Daya</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td style='border: 1px solid #000; padding: 5px;'>" . $id . "</td>";

                    // Pastikan No Meteran dan Kontak dibaca sebagai teks
                    echo "<td style='border: 1px solid #000; padding: 5px;'>=\"" . htmlspecialchars($row['no_meteran']) . "\"</td>";
                    echo "<td style='border: 1px solid #000; padding: 5px;'>{$row['nama_pelanggan']}</td>";
                    echo "<td style='border: 1px solid #000; padding: 5px;'>=\"" . htmlspecialchars($row['kontak_pelanggan']) . "\"</td>";
                    
                    echo "<td style='border: 1px solid #000; padding: 5px;'>{$row['tipe_rumah']}</td>";
                    echo "<td style='border: 1px solid #000; padding: 5px;'>{$row['alamat']}</td>";
                    echo "<td style='border: 1px solid #000; padding: 5px;'>{$row['id_tarif']}</td>";
                    echo "<td style='border: 1px solid #000; padding: 5px;'>{$row['daya']}</td>";
                    echo "</tr>";

                    $id++; // Increment ID
                }
            } else {
                echo "<tr><td colspan='8' style='border: 1px solid #000; padding: 5px; text-align: center;'>No Data Found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
