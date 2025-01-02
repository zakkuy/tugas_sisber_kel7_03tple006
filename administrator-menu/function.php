<?php
// Fungsi untuk mengirim pesan ke Telegram
function sendToTelegram($id, $list, $status, $notes, $create_on, $end_time) {
    $botToken = "7376987061:AAGIEEaSf6zN9onLWg_HtOVmdZAoLmgNeC0"; // Ganti dengan token bot Telegram Anda
    $chatId = "-1002229418526"; // Ganti dengan ID chat Telegram Anda
    $messageThreadId = "23"; // Ganti dengan ID thread jika perlu

    $message = "To-Do List Updated:\n\n";
    $message .= "ID: $id\n";
    $message .= "List: $list\n";
    $message .= "Status: $status\n";
    $message .= "Notes: $notes\n";
    $message .= "Created On: $create_on\n";
    $message .= "Completed On: $end_time\n";

    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'message_thread_id' => $messageThreadId // Optional, remove if not used
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        // Handle error
        error_log("Error sending message to Telegram");
    }
}

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

