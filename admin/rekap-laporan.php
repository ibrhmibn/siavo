<?php
$page_title = 'Rekap Laporan';
$page_active = 'rekap';

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if (getUserRole() != 'admin') {
    redirect('../index.php');
}

// Ambil statistik per status
$status_stats = [];
$sql = "SELECT status, COUNT(*) as count FROM laporan GROUP BY status";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $status_stats[$row['status']] = $row['count'];
}

// Ambil statistik per kategori
$kategori_stats = [];
$sql = "SELECT k.nama_kategori, COUNT(l.id) as count 
        FROM kategori k 
        LEFT JOIN laporan l ON k.id = l.kategori_id 
        GROUP BY k.id 
        ORDER BY count DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $kategori_stats[] = $row;
}

// Ambil statistik per bulan (6 bulan terakhir)
$bulan_stats = [];
$sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as count 
        FROM laporan 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY bulan 
        ORDER BY bulan ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $bulan_stats[] = $row;
}

// Total keseluruhan
$total = array_sum(array_column($status_stats, null));

include '../admin/includes/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark" style="color: var(--siavo-red-heading) !important;">Rekap Laporan</h1>
            <p class="text-muted small mb-0">Statistik dan ringkasan seluruh data laporan</p>
        </div>
    </div>

    <!-- Total Card -->
    <div class="stats-grid">
        <div class="stat-card-modern border-red" style="border-left-color: #d00018;">
            <div class="stat-left">
                <div class="stat-label">Total Laporan</div>
                <div class="stat-number" style="font-size: 2.5rem;"><?php echo $total; ?></div>
            </div>
            <div class="stat-icon" style="background: #fdeaec; color: #d00018;"><i class="fas fa-file-alt"></i></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chart Status -->
        <div class="col-md-6">
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-chart-pie"></i>
                    <h5>Status Laporan</h5>
                </div>
                <div class="card-modern-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart Kategori -->
        <div class="col-md-6">
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-chart-bar"></i>
                    <h5>Kategori Laporan</h5>
                </div>
                <div class="card-modern-body">
                    <canvas id="kategoriChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-chart-line"></i>
                    <h5>Tren 6 Bulan Terakhir</h5>
                </div>
                <div class="card-modern-body">
                    <canvas id="bulanChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail -->
    <div class="mt-4">
        <div class="table-modern-wrapper">
            <div class="table-header">
                <div class="title"><i class="fas fa-list me-2" style="color:#d00018;"></i>Detail per Kategori</div>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah Laporan</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kategori_stats)): ?>
                        <?php foreach ($kategori_stats as $k): 
                            $persen = $total > 0 ? round(($k['count'] / $total) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($k['nama_kategori']); ?></td>
                            <td><?php echo $k['count']; ?></td>
                            <td>
                                <div class="progress" style="height: 8px; border-radius: 4px; background: #e9ecef;">
                                    <div class="progress-bar"
                                        style="width: <?php echo $persen; ?>%; background: #d00018; border-radius: 4px;">
                                    </div>
                                </div>
                                <span class="small text-muted ms-2"><?php echo $persen; ?>%</span>
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

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    const ctx1 = document.getElementById('statusChart').getContext('2d');
    const statusLabels = <?php echo json_encode(array_keys($status_stats)); ?>;
    const statusData = <?php echo json_encode(array_values($status_stats)); ?>;
    const statusColors = {
        'pengajuan': '#3b82f6',
        'verifikasi': '#d97706',
        'tindak_lanjut': '#0d9488',
        'selesai': '#16a34a'
    };
    const bgColors = statusLabels.map(s => statusColors[s] || '#6c757d');

    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ')),
            datasets: [{
                data: statusData,
                backgroundColor: bgColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });

    // Kategori Chart
    const ctx2 = document.getElementById('kategoriChart').getContext('2d');
    const kategoriLabels = <?php echo json_encode(array_column($kategori_stats, 'nama_kategori')); ?>;
    const kategoriData = <?php echo json_encode(array_column($kategori_stats, 'count')); ?>;

    const colors = [
        '#d00018', '#e53e3e', '#ed8936', '#ecc94b',
        '#48bb78', '#38b2ac', '#4299e1', '#805ad5'
    ];

    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: kategoriLabels,
            datasets: [{
                label: 'Jumlah Laporan',
                data: kategoriData,
                backgroundColor: colors.slice(0, kategoriLabels.length),
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
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

    // Bulan Chart
    const ctx3 = document.getElementById('bulanChart').getContext('2d');
    const bulanLabels = <?php echo json_encode(array_column($bulan_stats, 'bulan')); ?>;
    const bulanData = <?php echo json_encode(array_column($bulan_stats, 'count')); ?>;

    new Chart(ctx3, {
        type: 'line',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Laporan per Bulan',
                data: bulanData,
                borderColor: '#d00018',
                backgroundColor: 'rgba(208, 0, 24, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#d00018',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
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
});
</script>

<?php include '../admin/includes/sidebar-footer.php'; ?>