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

// Filter tanggal
$filter_type = $_GET['filter'] ?? 'today';
$start_date = '';
$end_date = '';

switch ($filter_type) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'yesterday':
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $end_date = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'this_week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d');
        break;
    case 'this_month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        break;
    case 'last_month':
        $start_date = date('Y-m-01', strtotime('first day of last month'));
        $end_date = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'custom':
        $start_date = $_GET['start_date'] ?? date('Y-m-d');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
}

// Query ringkasan penjualan
$query_summary = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(grand_total) as total_pendapatan,
    AVG(grand_total) as rata_rata_transaksi,
    SUM(CASE WHEN status_bayar = 'lunas' THEN 1 ELSE 0 END) as transaksi_lunas,
    SUM(CASE WHEN status_bayar != 'lunas' THEN 1 ELSE 0 END) as transaksi_pending
FROM transaksi
WHERE tanggal BETWEEN ? AND ?";

$stmt = mysqli_prepare($connection, $query_summary);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Query per metode pembayaran
$query_payment = "SELECT 
    metode_pembayaran,
    COUNT(*) as jumlah,
    SUM(grand_total) as total
FROM transaksi
WHERE tanggal BETWEEN ? AND ? 
  AND status_bayar = 'lunas'
  AND metode_pembayaran IS NOT NULL
GROUP BY metode_pembayaran";

$stmt = mysqli_prepare($connection, $query_payment);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result_payment = mysqli_stmt_get_result($stmt);
$payment_methods = [];
while ($row = mysqli_fetch_assoc($result_payment)) {
    $payment_methods[] = $row;
}
mysqli_stmt_close($stmt);

// Query menu terlaris
$query_bestseller = "SELECT 
    m.nama_menu,
    m.kategori,
    SUM(dt.jumlah) as total_terjual,
    SUM(dt.subtotal) as total_pendapatan
FROM detail_transaksi dt
JOIN menu m ON dt.id_menu = m.id_menu
JOIN transaksi t ON dt.nomor_bukti = t.nomor_bukti
WHERE t.tanggal BETWEEN ? AND ?
  AND t.status_bayar = 'lunas'
GROUP BY dt.id_menu
ORDER BY total_terjual DESC
LIMIT 10";

$stmt = mysqli_prepare($connection, $query_bestseller);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result_bestseller = mysqli_stmt_get_result($stmt);
$bestsellers = [];
while ($row = mysqli_fetch_assoc($result_bestseller)) {
    $bestsellers[] = $row;
}
mysqli_stmt_close($stmt);

// Query daftar transaksi
$query_transactions = "SELECT * FROM transaksi 
WHERE tanggal BETWEEN ? AND ?
ORDER BY tanggal DESC, waktu DESC
LIMIT 50";

$stmt = mysqli_prepare($connection, $query_transactions);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result_transactions = mysqli_stmt_get_result($stmt);
$transactions = [];
while ($row = mysqli_fetch_assoc($result_transactions)) {
    $transactions[] = $row;
}
mysqli_stmt_close($stmt);

// Label metode pembayaran
$payment_labels = [
    'tunai' => 'Tunai',
    'debit_card' => 'Debit Card',
    'qris' => 'QRIS'
];
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/sidebar.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>
<?php include '../views/'.$THEME.'/upper_block.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-graph-up"></i> Laporan Penjualan</h2>
            <p class="text-muted mb-0">
                Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>
            </p>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Periode</label>
                    <select name="filter" id="filterType" class="form-select" onchange="toggleCustomDate()">
                        <option value="today" <?= $filter_type == 'today' ? 'selected' : '' ?>>Hari Ini</option>
                        <option value="yesterday" <?= $filter_type == 'yesterday' ? 'selected' : '' ?>>Kemarin</option>
                        <option value="this_week" <?= $filter_type == 'this_week' ? 'selected' : '' ?>>Minggu Ini</option>
                        <option value="this_month" <?= $filter_type == 'this_month' ? 'selected' : '' ?>>Bulan Ini</option>
                        <option value="last_month" <?= $filter_type == 'last_month' ? 'selected' : '' ?>>Bulan Lalu</option>
                        <option value="custom" <?= $filter_type == 'custom' ? 'selected' : '' ?>>Custom</option>
                    </select>
                </div>
                
                <div class="col-md-3" id="customDateStart" style="display: <?= $filter_type == 'custom' ? 'block' : 'none' ?>">
                    <label class="form-label fw-bold">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                </div>
                
                <div class="col-md-3" id="customDateEnd" style="display: <?= $filter_type == 'custom' ? 'block' : 'none' ?>">
                    <label class="form-label fw-bold">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Transaksi</h6>
                            <h3 class="mb-0"><?= number_format($summary['total_transaksi']) ?></h3>
                            <small class="text-success">
                                <?= $summary['transaksi_lunas'] ?> Lunas
                            </small>
                        </div>
                        <div class="fs-1 text-primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Pendapatan</h6>
                            <h3 class="mb-0 text-success">Rp <?= number_format($summary['total_pendapatan']) ?></h3>
                            <small class="text-muted">
                                Include tax & service
                            </small>
                        </div>
                        <div class="fs-1 text-success">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Rata-rata per Transaksi</h6>
                            <h3 class="mb-0 text-info">Rp <?= number_format($summary['rata_rata_transaksi']) ?></h3>
                            <small class="text-muted">Average order value</small>
                        </div>
                        <div class="fs-1 text-info">
                            <i class="bi bi-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0 text-warning"><?= number_format($summary['transaksi_pending']) ?></h3>
                            <small class="text-muted">Belum lunas</small>
                        </div>
                        <div class="fs-1 text-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Payment Methods Chart -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Pembayaran per Metode</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($payment_methods)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">Belum ada data pembayaran</p>
                    </div>
                    <?php else: ?>
                    <canvas id="paymentChart" height="250"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bestseller Chart -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Menu Terlaris</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($bestsellers)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">Belum ada data penjualan</p>
                    </div>
                    <?php else: ?>
                    <canvas id="bestsellerChart" height="250"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bestseller Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Menu Terlaris</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Menu</th>
                            <th width="15%">Kategori</th>
                            <th width="15%" class="text-center">Terjual</th>
                            <th width="20%" class="text-end">Total Pendapatan</th>
                            <th width="10%">Grafik</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bestsellers)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Tidak ada data untuk periode ini
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php 
                        $max_qty = max(array_column($bestsellers, 'total_terjual'));
                        $no = 1;
                        foreach ($bestsellers as $item): 
                            $percentage = ($item['total_terjual'] / $max_qty) * 100;
                        ?>
                        <tr>
                            <td class="text-center">
                                <?php if ($no <= 3): ?>
                                    <span class="badge bg-<?= $no == 1 ? 'warning' : ($no == 2 ? 'secondary' : 'bronze') ?>">
                                        #<?= $no ?>
                                    </span>
                                <?php else: ?>
                                    <?= $no ?>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($item['nama_menu']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($item['kategori']) ?></span></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= number_format($item['total_terjual']) ?>x</span>
                            </td>
                            <td class="text-end fw-bold">Rp <?= number_format($item['total_pendapatan']) ?></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: <?= $percentage ?>%">
                                        <?= round($percentage) ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        $no++;
                        endforeach; 
                        ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Transaksi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor Bukti</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Meja</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Service</th>
                            <th class="text-end">Grand Total</th>
                            <th>Metode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Tidak ada transaksi untuk periode ini
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($transactions as $trx): ?>
                        <tr>
                            <td>
                                <a href="../transaksi/detail.php?nomor_bukti=<?= urlencode($trx['nomor_bukti']) ?>">
                                    <?= htmlspecialchars($trx['nomor_bukti']) ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($trx['tanggal'] . ' ' . $trx['waktu'])) ?></td>
                            <td><?= htmlspecialchars($trx['nama_pelanggan'] ?: '-') ?></td>
                            <td><?= $trx['id_meja'] ? 'No. ' . $trx['id_meja'] : '-' ?></td>
                            <td class="text-end">Rp <?= number_format($trx['total_bayar']) ?></td>
                            <td class="text-end">Rp <?= number_format($trx['tax_amount'] ?? 0) ?></td>
                            <td class="text-end">Rp <?= number_format($trx['service_amount'] ?? 0) ?></td>
                            <td class="text-end fw-bold">Rp <?= number_format($trx['grand_total'] ?? $trx['total_bayar']) ?></td>
                            <td>
                                <?php if ($trx['metode_pembayaran']): ?>
                                    <span class="badge bg-<?= $trx['metode_pembayaran'] == 'tunai' ? 'success' : 'primary' ?>">
                                        <?= $payment_labels[$trx['metode_pembayaran']] ?? $trx['metode_pembayaran'] ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $trx['status_bayar'] == 'lunas' ? 'success' : 'warning' ?>">
                                    <?= strtoupper(str_replace('_', ' ', $trx['status_bayar'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle custom date fields
function toggleCustomDate() {
    const filterType = document.getElementById('filterType').value;
    const customStart = document.getElementById('customDateStart');
    const customEnd = document.getElementById('customDateEnd');
    
    if (filterType === 'custom') {
        customStart.style.display = 'block';
        customEnd.style.display = 'block';
    } else {
        customStart.style.display = 'none';
        customEnd.style.display = 'none';
    }
}

<?php if (!empty($payment_methods)): ?>
// Payment Methods Chart
const paymentData = <?= json_encode($payment_methods) ?>;
const paymentLabels = paymentData.map(p => {
    const labels = {'tunai': 'Tunai', 'debit_card': 'Debit Card', 'qris': 'QRIS'};
    return labels[p.metode_pembayaran] || p.metode_pembayaran;
});
const paymentValues = paymentData.map(p => parseFloat(p.total));

new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
        labels: paymentLabels,
        datasets: [{
            data: paymentValues,
            backgroundColor: ['#28a745', '#007bff', '#ffc107']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed || 0;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = ((value / total) * 100).toFixed(1);
                        return label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
<?php endif; ?>

<?php if (!empty($bestsellers)): ?>
// Bestseller Chart
const bestsellerData = <?= json_encode($bestsellers) ?>;
const bestsellerLabels = bestsellerData.slice(0, 5).map(b => b.nama_menu);
const bestsellerValues = bestsellerData.slice(0, 5).map(b => parseInt(b.total_terjual));

new Chart(document.getElementById('bestsellerChart'), {
    type: 'bar',
    data: {
        labels: bestsellerLabels,
        datasets: [{
            label: 'Jumlah Terjual',
            data: bestsellerValues,
            backgroundColor: '#28a745'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<style>
.badge.bg-bronze {
    background-color: #cd7f32 !important;
}
</style>

<?php include '../views/'.$THEME.'/lower_block.php'; ?>
<?php include '../views/'.$THEME.'/footer.php'; ?>