<?php
$page_title = 'Riwayat Transaksi';
require_once 'header.php';

$success = '';
$error = '';
$is_admin = ($_SESSION['role'] === 'admin');

// Handle Delete Transaction (Admin Only)
if ($is_admin && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Start database transaction to ensure safety
    $conn->begin_transaction();
    try {
        // Details are set to ON DELETE CASCADE in migrations, but we can verify or let database handle it.
        // Let's delete detail items first if needed, though CASCADE takes care of it.
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $conn->commit();
            $success = 'Transaksi berhasil dihapus dari riwayat!';
        } else {
            throw new Exception("Gagal menghapus data transaksi.");
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Kesalahan saat menghapus transaksi: ' . $e->getMessage();
    }
}

// Search and Date Filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';

$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "t.invoice_no LIKE '%$search%'";
}
if (!empty($start_date)) {
    $where_clauses[] = "t.created_at >= '$start_date 00:00:00'";
}
if (!empty($end_date)) {
    $where_clauses[] = "t.created_at <= '$end_date 23:59:59'";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch transactions
$query = "SELECT t.*, u.name as cashier_name 
          FROM transactions t 
          LEFT JOIN users u ON t.user_id = u.id 
          $where_sql 
          ORDER BY t.created_at DESC";
$result = $conn->query($query);
?>

<!-- Alert Feedback -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <?= $success ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <?= $error ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Semua Riwayat Transaksi Penjualan</h3>
    </div>
    
    <div class="card-body">
        <!-- Filter Form -->
        <form action="transactions.php" method="GET" class="filter-form">
            <div class="filter-group filter-grow">
                <label for="search" class="filter-label">No. Invoice</label>
                <input type="text" id="search" name="search" class="form-control" 
                       placeholder="Cari nomor invoice..." value="<?= $search ?>">
            </div>
            
            <div class="filter-group">
                <label for="start_date" class="filter-label">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $start_date ?>">
            </div>
            
            <div class="filter-group">
                <label for="end_date" class="filter-label">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $end_date ?>">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if (!empty($search) || !empty($start_date) || !empty($end_date)): ?>
                    <a href="transactions.php" class="btn btn-secondary" title="Reset Filter">Reset</a>
                <?php endif; ?>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Invoice</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir</th>
                        <th>Total Belanja</th>
                        <th>Uang Bayar</th>
                        <th>Uang Kembali</th>
                        <th style="width: 180px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php 
                        $no = 1;
                        while ($row = $result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="font-semibold text-primary"><?= $row['invoice_no'] ?></span></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
                                <td><?= $row['cashier_name'] ?? 'Sistem' ?></td>
                                <td class="font-bold text-success"><?= format_rupiah($row['total_price']) ?></td>
                                <td><?= format_rupiah($row['pay_amount']) ?></td>
                                <td><?= format_rupiah($row['change_amount']) ?></td>
                                <td style="text-align: center;">
                                    <div class="table-actions" style="justify-content: center;">
                                        <a href="transaction_detail.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm" title="Lihat Detail Nota">
                                            Detail
                                        </a>
                                        <?php if ($is_admin): ?>
                                            <a href="transactions.php?delete=<?= $row['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus data transaksi ini dari riwayat? Peringatan: Tindakan ini tidak dapat dibatalkan.');" 
                                               title="Hapus Transaksi">
                                                Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--secondary); padding: 3rem;">
                                Belum ada data transaksi yang ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
