<?php
// Fungsi untuk menghasilkan nomor kwitansi
function generateInvoiceNumber($connection) {
    // Ambil bulan dan tahun saat ini
    $monthRoman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    $currentMonth = date('n'); // Bulan dalam angka (1-12)
    $currentYear = date('Y'); // Tahun penuh (contoh: 2024)
    $currentYearRoman = romanize(substr($currentYear, -2)); // Ambil 2 digit terakhir tahun dan ubah jadi angka romawi
    
    // Cek nomor urut terakhir di database untuk bulan dan tahun ini
    $stmt = $connection->prepare("SELECT COUNT(*) AS count FROM transaksi WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?");
    $stmt->bind_param("ii", $currentYear, $currentMonth);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $invoiceNumber = str_pad($data['count'] + 1, 4, '0', STR_PAD_LEFT); // Menambahkan nomor urut

    // Gabungkan format nomor kwitansi
    $invoice = "NXLT/INV/{$invoiceNumber}/{$monthRoman[$currentMonth - 1]}/{$currentYearRoman}";

    return $invoice;
}

// Fungsi untuk mengubah angka menjadi angka Romawi
function romanize($num) {
    $map = [
        1 => 'I', 4 => 'IV', 5 => 'V', 9 => 'IX', 10 => 'X', 
        40 => 'XL', 50 => 'L', 90 => 'XC', 100 => 'C', 400 => 'CD', 
        500 => 'D', 900 => 'CM', 1000 => 'M'
    ];
    
    $result = '';
    foreach (array_reverse($map, true) as $value => $roman) {
        while ($num >= $value) {
            $result .= $roman;
            $num -= $value;
        }
    }
    return $result;
}

?>

