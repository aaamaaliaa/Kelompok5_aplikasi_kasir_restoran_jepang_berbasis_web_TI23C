<?php
/**
 * KONFIGURASI INFORMASI RESTORAN
 * File ini berisi informasi yang akan ditampilkan di struk
 * 
 * Cara pakai:
 * 1. Edit informasi di bawah sesuai dengan data restoran Anda
 * 2. Simpan file ini di: config/restaurant.php
 * 3. Require file ini di struk.php
 */

// Definisikan konstanta informasi restoran
define('RESTAURANT_NAME', 'NeoSushi');
define('RESTAURANT_TAGLINE', 'Authentic Japanese Cuisine');
define('RESTAURANT_ADDRESS', 'Jl. Bhayangkara II');
define('RESTAURANT_CITY', 'Jakarta, Indonesia 12345');
define('RESTAURANT_PHONE', '(021) 1234-5678');
define('RESTAURANT_EMAIL', 'info@NeoSushii11.com');
define('RESTAURANT_WEBSITE', 'www.NeoSushi11.com');
define('RESTAURANT_INSTAGRAM', '@NeoSushi11');

// Logo (emoji atau bisa diganti dengan path image)
define('RESTAURANT_LOGO', '🍱'); // Bisa diganti dengan <img src="...">

// Pesan footer struk
define('RECEIPT_THANK_YOU', 'TERIMA KASIH');
define('RECEIPT_MESSAGE', 'Selamat Menikmati!');
define('RECEIPT_FOOTER', 'Mohon simpan struk ini sebagai bukti pembayaran');

// Informasi pajak (dalam persen)
define('TAX_PERCENTAGE', 10); // 0 = tidak ada pajak, 10 = pajak 10%
define('SERVICE_CHARGE', 5); // 0 = tidak ada service charge, 5 = 5%

// Jam operasional (untuk ditampilkan di struk - opsional)
define('OPERATING_HOURS', 'Setiap Hari: 10:00 - 22:00');

// Return as array (untuk kemudahan akses)
function getRestaurantInfo() {
    return [
        'name' => RESTAURANT_NAME,
        'tagline' => RESTAURANT_TAGLINE,
        'address' => RESTAURANT_ADDRESS,
        'city' => RESTAURANT_CITY,
        'phone' => RESTAURANT_PHONE,
        'email' => RESTAURANT_EMAIL,
        'website' => RESTAURANT_WEBSITE,
        'instagram' => RESTAURANT_INSTAGRAM,
        'logo' => RESTAURANT_LOGO,
        'thank_you' => RECEIPT_THANK_YOU,
        'message' => RECEIPT_MESSAGE,
        'footer' => RECEIPT_FOOTER,
        'tax_percentage' => TAX_PERCENTAGE,
        'service_charge' => SERVICE_CHARGE,
        'operating_hours' => OPERATING_HOURS,
    ];
}