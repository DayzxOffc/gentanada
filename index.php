<?php
// report_visit.php

// Izinkan akses dari domain Anda.
// PENTING: Untuk produksi, ganti '*' dengan domain spesifik website Anda
// Contoh: header("Access-Control-Allow-Origin: https://gentanadaband.sch.id");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Tangani permintaan OPTIONS untuk preflight CORS (penting untuk browser modern)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set zona waktu ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

// --- Konfigurasi Notifikasi ---

// --- Opsi 1: Notifikasi Telegram ---
// Set ke true jika ingin mengaktifkan notifikasi Telegram
$enable_telegram_notification = true; // Ubah ke false jika tidak ingin notifikasi Telegram

// *** PENTING: GANTI DENGAN TOKEN BOT TELEGRAM ANDA (dari @BotFather) ***
$telegram_bot_token = "7599993058:AAEGx3i3A00FUNyGHFaYLwcGduRbRaWYNxk";
// *** PENTING: GANTI DENGAN CHAT ID TELEGRAM ANDA (dari https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates) ***
$telegram_chat_id = "7666363970";

// --- Opsi 2: Notifikasi Email ---
// Set ke true jika ingin mengaktifkan notifikasi Email
$enable_email_notification = true; // Ubah ke false jika tidak ingin notifikasi Email

// *** PENTING: GANTI DENGAN ALAMAT EMAIL ANDA YANG AKAN MENERIMA NOTIFIKASI ***
$to_email = "dayzxshop@gmail.com";
// *** PENTING: GANTI DENGAN ALAMAT EMAIL DARI DOMAIN ANDA (misal: noreply@gentanadaband.sch.id) ***
$sender_email = "noreply@yourwebsite.com";
$subject = "Notifikasi Kunjungan Website Genta Nada Band!";

// --- Ambil informasi pengunjung ---
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$visit_time = date('Y-m-d H:i:s');
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct/Unknown';

$hostname = gethostbyaddr($ip_address);
if ($hostname === $ip_address) {
    $hostname = "Tidak diketahui";
}
// Untuk lokasi yang lebih akurat, Anda perlu menggunakan GeoIP database atau API pihak ketiga.
$location_info = "Tidak diketahui";

// Isi pesan notifikasi
$message_content = "Halo Dayzx,\n\n" .
                   "Seseorang baru saja mengunjungi website Genta Nada Band!\n\n" .
                   "Detail Kunjungan:\n" .
                   "------------------\n" .
                   "Waktu Kunjungan: " . $visit_time . " WIB\n" .
                   "Alamat IP Pengunjung: " . $ip_address . " (" . $hostname . ")\n" .
                   "User Agent (Browser/OS): " . $user_agent . "\n" .
                   "Halaman Asal: " . $referer . "\n" .
                   "Lokasi Perkiraan: " . $location_info . "\n\n" .
                   "Terima kasih!\n" .
                   "Sistem Notifikasi Genta Nada Band.";

// --- Kirim Notifikasi Email ---
if ($enable_email_notification) {
    $headers = "From: " . $sender_email . "\r\n";
    $headers .= "Reply-To: " . $sender_email . "\r\n";
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

    if (mail($to_email, $subject, $message_content, $headers)) {
        // Notifikasi email berhasil
    } else {
        error_log("Failed to send email notification to " . $to_email);
    }
}

// --- Kirim Notifikasi Telegram ---
if ($enable_telegram_notification && !empty($telegram_bot_token) && !empty($telegram_chat_id)) {
    $telegram_message_encoded = urlencode($message_content);
    // Menggunakan HTML parse_mode untuk format pesan di Telegram (bold, italic, dll.)
    $telegram_full_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage?chat_id={$telegram_chat_id}&text={$telegram_message_encoded}&parse_mode=HTML";

    // Inisialisasi cURL untuk mengirim permintaan HTTP
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $telegram_full_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        // Notifikasi Telegram berhasil
    } else {
        error_log("Failed to send Telegram notification. HTTP Code: " . $http_code . " Response: " . $response);
    }
}

// Beri respons balik ke klien (JavaScript) agar tahu permintaan berhasil
echo json_encode(["status" => "success", "message" => "Visit reported"]);
exit();
?>