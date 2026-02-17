<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('transaksi');
require_once '../config/database.php';

// Load restaurant config (jika sudah dibuat)
$restaurant_config_file = '../config/restaurant.php';
if (file_exists($restaurant_config_file)) {
    require_once $restaurant_config_file;
    $resto_info = getRestaurantInfo();
} else {
    // Default values jika config belum dibuat
    $resto_info = [
        'name' => 'RESTO JEPANG',
        'tagline' => 'Authentic Japanese Cuisine',
        'address' => 'Jl. Contoh No. 123',
        'city' => 'Jakarta, Indonesia',
        'phone' => '(021) 1234-5678',
        'email' => 'info@restojepang.com',
        'website' => 'www.restojepang.com',
        'instagram' => '@restojepang',
        'logo' => '🍱',
        'thank_you' => 'TERIMA KASIH',
        'message' => 'Selamat Menikmati!',
        'footer' => 'Mohon simpan struk ini',
        'tax_percentage' => 0,
        'service_charge' => 0,
    ];
}

// Ambil nomor bukti
$nomor_bukti = $_GET['nomor_bukti'] ?? '';
if (!$nomor_bukti) {
    header("Location: index.php");
    exit;
}

// Ambil data transaksi
$stmt = mysqli_prepare($connection, "SELECT * FROM transaksi WHERE nomor_bukti = ?");
mysqli_stmt_bind_param($stmt, "s", $nomor_bukti);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$transaksi) {
    header("Location: index.php");
    exit;
}

// Cek apakah transaksi sudah lunas
if ($transaksi['status_bayar'] !== 'lunas') {
    die('
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial; text-align: center; padding: 50px; }
                .alert { background: #fff3cd; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; display: inline-block; }
                .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="alert">
                <h2>⚠️ Struk Tidak Dapat Dicetak</h2>
                <p>Struk hanya dapat dicetak untuk transaksi yang sudah <strong>LUNAS</strong>.</p>
                <p>Status transaksi ini: <strong>' . strtoupper($transaksi['status_bayar']) . '</strong></p>
            </div>
            <br>
            <a href="detail.php?nomor_bukti=' . urlencode($nomor_bukti) . '" class="btn">Kembali ke Detail</a>
        </body>
        </html>
    ');
}

// Ambil detail items
$stmt_detail = mysqli_prepare(
    $connection,
    "SELECT dt.*, m.nama_menu, m.kode_menu 
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu = m.id_menu
     WHERE dt.nomor_bukti = ?
     ORDER BY dt.id_detail"
);
mysqli_stmt_bind_param($stmt_detail, "s", $nomor_bukti);
mysqli_stmt_execute($stmt_detail);
$result_detail = mysqli_stmt_get_result($stmt_detail);
$items = [];
while ($row = mysqli_fetch_assoc($result_detail)) {
    $items[] = $row;
}
mysqli_stmt_close($stmt_detail);

// Hitung pajak dan service charge (AMBIL DARI DATABASE)
$subtotal = $transaksi['total_bayar'];
$tax_amount = $transaksi['tax_amount'] ?? 0;
$service_amount = $transaksi['service_amount'] ?? 0;
$grand_total = $transaksi['grand_total'] ?? $subtotal;

// Nama kasir dari session
$nama_kasir = $_SESSION['username'] ?? 'Admin';

// Format metode pembayaran untuk display
$metode_pembayaran_label = [
    'tunai' => 'TUNAI',
    'debit_card' => 'DEBIT CARD',
    'qris' => 'QRIS / E-WALLET'
];
$metode_display = $metode_pembayaran_label[$transaksi['metode_pembayaran']] ?? '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= htmlspecialchars($nomor_bukti) ?></title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Print Settings - Thermal 58mm */
        @page {
            size: 58mm auto;
            margin: 0;
        }

        /* Body */
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: #f5f5f5;
            padding: 10px;
        }

        /* Receipt Container */
        .receipt {
            width: 58mm;
            background: white;
            padding: 8px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .restaurant-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .restaurant-tagline {
            font-size: 8px;
            font-style: italic;
            margin-bottom: 4px;
        }

        .restaurant-info {
            font-size: 9px;
            line-height: 1.4;
        }

        /* Transaction Info */
        .transaction-info {
            margin-bottom: 8px;
            font-size: 9px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        /* Items Table */
        .items {
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .item {
            margin-bottom: 6px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 1px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }

        .item-note {
            font-size: 8px;
            font-style: italic;
            color: #666;
            margin-top: 1px;
        }

        /* Total Section */
        .total-section {
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .total-row.grand {
            font-size: 12px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 4px;
        }

        /* Payment Section (NEW) */
        .payment-section {
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .payment-method {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
            padding: 4px;
            background: #f0f0f0;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 10px;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 8px;
        }

        .thank-you {
            font-weight: bold;
            margin-bottom: 4px;
        }

        /* Print Button (hidden when printing) */
        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            font-family: Arial;
        }

        .print-button:hover {
            background: #218838;
        }

        .back-button {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 15px 30px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            text-decoration: none;
            display: inline-block;
            font-family: Arial;
        }

        .back-button:hover {
            background: #545b62;
        }

        /* Hide buttons when printing */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
                margin: 0;
            }

            .print-button,
            .back-button {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="logo"><?= $resto_info['logo'] ?></div>
            <div class="restaurant-name"><?= strtoupper($resto_info['name']) ?></div>
            <div class="restaurant-tagline"><?= $resto_info['tagline'] ?></div>
            <div class="restaurant-info">
                <?= $resto_info['address'] ?><br>
                <?= $resto_info['city'] ?><br>
                Telp: <?= $resto_info['phone'] ?>
            </div>
        </div>

        <!-- Transaction Info -->
        <div class="transaction-info">
            <div class="info-row">
                <span>No. Bukti</span>
                <span><?= htmlspecialchars($transaksi['nomor_bukti']) ?></span>
            </div>
            <div class="info-row">
                <span>Tanggal</span>
                <span><?= date('d/m/Y', strtotime($transaksi['tanggal'])) ?></span>
            </div>
            <div class="info-row">
                <span>Waktu</span>
                <span><?= htmlspecialchars($transaksi['waktu']) ?></span>
            </div>
            <?php if ($transaksi['id_meja']): ?>
            <div class="info-row">
                <span>Meja</span>
                <span>No. <?= htmlspecialchars($transaksi['id_meja']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($transaksi['nama_pelanggan']): ?>
            <div class="info-row">
                <span>Pelanggan</span>
                <span><?= htmlspecialchars($transaksi['nama_pelanggan']) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span>Kasir</span>
                <span><?= htmlspecialchars($nama_kasir) ?></span>
            </div>
        </div>

        <!-- Items -->
        <div class="items">
            <?php foreach ($items as $item): ?>
            <div class="item">
                <div class="item-name"><?= htmlspecialchars($item['nama_menu']) ?></div>
                <div class="item-detail">
                    <span><?= $item['jumlah'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                    <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
                <?php if ($item['catatan_item']): ?>
                <div class="item-note">* <?= htmlspecialchars($item['catatan_item']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal</span>
                <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
            </div>
            
            <?php if ($resto_info['tax_percentage'] > 0): ?>
            <div class="total-row">
                <span>Pajak (<?= $resto_info['tax_percentage'] ?>%)</span>
                <span>Rp <?= number_format($tax_amount, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($resto_info['service_charge'] > 0): ?>
            <div class="total-row">
                <span>Service (<?= $resto_info['service_charge'] ?>%)</span>
                <span>Rp <?= number_format($service_amount, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            
            <div class="total-row grand">
                <span>TOTAL</span>
                <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Payment Section (NEW) -->
        <?php if ($transaksi['metode_pembayaran']): ?>
        <div class="payment-section">
            <div class="payment-method">
                <?= $metode_display ?>
            </div>
            
            <?php if ($transaksi['metode_pembayaran'] === 'tunai'): ?>
            <div class="payment-row">
                <span>Jumlah Bayar</span>
                <span>Rp <?= number_format($transaksi['jumlah_bayar'], 0, ',', '.') ?></span>
            </div>
            <div class="payment-row">
                <span>Kembalian</span>
                <span>Rp <?= number_format($transaksi['kembalian'] ?? 0, 0, ',', '.') ?></span>
            </div>
            <?php else: ?>
            <div class="payment-row">
                <span>Jumlah Dibayar</span>
                <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you"><?= strtoupper($resto_info['thank_you']) ?></div>
            <div><?= $resto_info['message'] ?></div>
            <div style="margin-top: 4px;"><?= $resto_info['website'] ?></div>
            <?php if (!empty($resto_info['instagram'])): ?>
            <div style="margin-top: 2px;">IG: <?= $resto_info['instagram'] ?></div>
            <?php endif; ?>
            <div style="margin-top: 6px; font-size: 8px; border-top: 1px dashed #000; padding-top: 4px;">
                <?= $resto_info['footer'] ?>
            </div>
            <div style="margin-top: 4px; font-size: 8px;">
                Dicetak: <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <button class="print-button" onclick="window.print()">
        🖨️ Cetak Struk
    </button>
    <a href="detail.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>" class="back-button">
        ← Kembali
    </a>

    <script>
        // Keyboard shortcut
        document.addEventListener('keydown', function(e) {
            // Ctrl+P or Cmd+P untuk print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // ESC untuk kembali
            if (e.key === 'Escape') {
                window.location.href = 'detail.php?nomor_bukti=<?= urlencode($nomor_bukti) ?>';
            }
        });
    </script>
</body>
</html>