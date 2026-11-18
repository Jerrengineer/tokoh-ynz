<?php
$page_title = 'Pengeluaran Toko';
require_once 'header.php';
require_admin(); // Hanya admin/pemilik toko yang boleh akses data pengeluaran

$success = '';
$error = '';

// Handle Add Expense
if (isset($_POST['add_expense'])) {
    $name = sanitize($_POST['name']);
    $category = sanitize($_POST['category']);
    $amount = floatval($_POST['amount']);
    $date = sanitize($_POST['date']);
    $description = sanitize($_POST['description']);
    
    if (empty($name) || empty($category) || $amount <= 0 || empty($date)) {
        $error = 'Nama, Kategori, Nominal, dan Tanggal wajib diisi!';
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses (name, category, amount, date, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $category, $amount, $date, $description);
        
        if ($stmt->execute()) {
            $success = 'Pengeluaran berhasil dicatat!';
        } else {
            $error = 'Gagal mencatat pengeluaran!';
        }
        $stmt->close();
    }
}

// Handle Edit Expense
if (isset($_POST['edit_expense'])) {
    $id = intval($_POST['id']);
    $name = sanitize($_POST['name']);
    $category = sanitize($_POST['category']);
    $amount = floatval($_POST['amount']);
    $date = sanitize($_POST['date']);
    $description = sanitize($_POST['description']);
    
    if (empty($name) || empty($category) || $amount <= 0 || empty($date)) {
        $error = 'Nama, Kategori, Nominal, dan Tanggal wajib diisi!';
    } else {
        $stmt = $conn->prepare("UPDATE expenses SET name = ?, category = ?, amount = ?, date = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssdssi", $name, $category, $amount, $date, $description, $id);
        
        if ($stmt->execute()) {
            $success = 'Pengeluaran berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui pengeluaran!';
        }
        $stmt->close();
    }
}

// Handle Delete Expense
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Pengeluaran berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus pengeluaran!';
    }
    $stmt->close();
}

// Filter Setup
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';

$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
}
if (!empty($cat_filter)) {
    $where_clauses[] = "category = '$cat_filter'";
}
if (!empty($start_date)) {
    $where_clauses[] = "date >= '$start_date'";
}
if (!empty($end_date)) {
    $where_clauses[] = "date <= '$end_date'";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch Expenses
$query = "SELECT * FROM expenses $where_sql ORDER BY date DESC, id DESC";
$result = $conn->query($query);

// Fetch edit data if requested
$edit_expense = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_expense = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Categories list
$categories = [
    'Belanja Stok',
    'Listrik',
    'Air',
    'Transportasi',
    'Biaya Operasional',
    'Lainnya'
];
?>

<!-- Action Status Message -->
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

<div class="grid grid-cols-3 layout-split">
    <!-- Form Panel -->
    <div class="card layout-form-card" style="align-self: start;">
        <div class="card-header">
            <h3 class="card-title"><?= $edit_expense ? 'Edit Pengeluaran' : 'Catat Pengeluaran Baru' ?></h3>
        </div>
        <div class="card-body">
            <form action="expenses.php" method="POST">
                <?php if ($edit_expense): ?>
                    <input type="hidden" name="id" value="<?= $edit_expense['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Nama Pengeluaran</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?= $edit_expense ? $edit_expense['name'] : '' ?>" 
                           placeholder="Contoh: Bayar Listrik Mei" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($edit_expense && $edit_expense['category'] === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Nominal (Rupiah)</label>
                    <input type="number" id="amount" name="amount" class="form-control" 
                           value="<?= $edit_expense ? intval($edit_expense['amount']) : '' ?>" 
                           placeholder="Masukkan nominal" min="1" step="100" required>
                </div>
                
                <div class="form-group">
                    <label for="date">Tanggal</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           value="<?= $edit_expense ? $edit_expense['date'] : date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Keterangan / Catatan</label>
                    <textarea id="description" name="description" class="form-control" rows="3" 
                              placeholder="Keterangan tambahan (optional)"><?= $edit_expense ? $edit_expense['description'] : '' ?></textarea>
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="submit" name="<?= $edit_expense ? 'edit_expense' : 'add_expense' ?>" class="btn btn-primary" style="flex-grow: 1;">
                        <?= $edit_expense ? 'Perbarui Pengeluaran' : 'Simpan Pengeluaran' ?>
                    </button>
                    <?php if ($edit_expense): ?>
                        <a href="expenses.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Table List Panel -->
    <div class="card layout-table-card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pengeluaran Toko</h3>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form action="expenses.php" method="GET" class="filter-form">
                <div class="filter-group filter-grow">
                    <label for="search_filter" class="filter-label">Cari Nama / Ket.</label>
                    <input type="text" id="search_filter" name="search" class="form-control" 
                           placeholder="Cari..." value="<?= $search ?>">
                </div>
                
                <div class="filter-group">
                    <label for="cat_filter" class="filter-label">Kategori</label>
                    <select id="cat_filter" name="category" class="form-control">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($cat_filter === $cat) ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <?php if (!empty($search) || !empty($cat_filter) || !empty($start_date) || !empty($end_date)): ?>
                        <a href="expenses.php" class="btn btn-secondary" title="Reset Filter">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Pengeluaran</th>
                            <th>Kategori</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                            <th style="width: 130px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php 
                            $total_amount = 0;
                            while ($row = $result->fetch_assoc()): 
                                $total_amount += $row['amount'];
                            ?>
                                <tr>
                                    <td><span style="font-size: 0.85rem; font-weight: 500;"><?= date('d/m/Y', strtotime($row['date'])) ?></span></td>
                                    <td><span class="font-semibold"><?= $row['name'] ?></span></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= $row['category'] ?>
                                        </span>
                                    </td>
                                    <td class="font-bold text-danger"><?= format_rupiah($row['amount']) ?></td>
                                    <td><span class="text-muted" style="font-size: 0.85rem;"><?= !empty($row['description']) ? $row['description'] : '-' ?></span></td>
                                    <td style="text-align: center;">
                                        <div class="table-actions" style="justify-content: center;">
                                            <a href="expenses.php?edit=<?= $row['id'] ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($cat_filter) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-warning btn-sm" title="Edit">
                                                Edit
                                            </a>
                                            <a href="expenses.php?delete=<?= $row['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus pengeluaran ini?');" 
                                               title="Hapus">
                                                Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <!-- Show Total Row -->
                            <tr style="background-color: var(--light); font-weight: 700;">
                                <td colspan="3" style="text-align: right;">TOTAL:</td>
                                <td class="text-danger" style="font-size: 1.05rem;"><?= format_rupiah($total_amount) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--secondary); padding: 3rem;">
                                    Belum ada data pengeluaran yang cocok.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
