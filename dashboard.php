<?php
$page_title = 'Dashboard';
require_once 'header.php';

// Today's Date
$today_date = date('Y-m-d');

// Fetch general stats
// 1. Total Products
$prod_res = $conn->query("SELECT COUNT(*) as total FROM products");
$total_products = $prod_res->fetch_assoc()['total'] ?? 0;

// 2. Total Stock
$stock_res = $conn->query("SELECT SUM(stock) as total FROM products");
$total_stock = $stock_res->fetch_assoc()['total'] ?? 0;

// 3. Total Transactions (All-Time)
$trans_res = $conn->query("SELECT COUNT(*) as total FROM transactions");
$total_transactions = $trans_res->fetch_assoc()['total'] ?? 0;

// 4. Today's Transactions Count
$today_trans_res = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE DATE(created_at) = '$today_date'");
$today_transactions = $today_trans_res->fetch_assoc()['total'] ?? 0;

// 5. Today's Revenue / Income (Admin Only)
$today_income = 0;
if ($_SESSION['role'] === 'admin') {
    $income_res = $conn->query("SELECT SUM(total_price) as total FROM transactions WHERE DATE(created_at) = '$today_date'");
    $today_income = $income_res->fetch_assoc()['total'] ?? 0;
}

// 5b. Today's Gross Profit from Sales (Admin Only)
$today_sales_profit = 0;
if ($_SESSION['role'] === 'admin') {
    $sales_profit_res = $conn->query("SELECT SUM(td.quantity * (td.price - COALESCE(p.cost_price, td.price))) as total 
                                      FROM transaction_details td 
                                      JOIN transactions t ON td.transaction_id = t.id 
                                      LEFT JOIN products p ON td.product_id = p.id 
                                      WHERE DATE(t.created_at) = '$today_date'");
    $today_sales_profit = $sales_profit_res->fetch_assoc()['total'] ?? 0;
}

// 6. Today's Expenses (Admin Only)
$today_expenses = 0;
if ($_SESSION['role'] === 'admin') {
    $expense_res = $conn->query("SELECT SUM(amount) as total FROM expenses WHERE date = '$today_date'");
    $today_expenses = $expense_res->fetch_assoc()['total'] ?? 0;
}

// 7. Today's Net Profit (Admin Only)
$today_profit = $today_sales_profit - $today_expenses;

// Fetch low stock products (limit 5 for dashboard preview)
$low_stock_query = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.stock <= p.min_stock 
                    ORDER BY p.stock ASC LIMIT 5";
$low_stock_items = $conn->query($low_stock_query);

// Fetch recent transactions (limit 5)
$recent_trans_query = "SELECT t.*, u.name as cashier_name 
                       FROM transactions t 
                       LEFT JOIN users u ON t.user_id = u.id 
                       ORDER BY t.created_at DESC LIMIT 5";
$recent_trans = $conn->query($recent_trans_query);

// Fetch Most Popular Products (Admin Only, limit 5)
$popular_items = null;
if ($_SESSION['role'] === 'admin') {
    $popular_query = "SELECT p.code, p.name, SUM(td.quantity) as total_sold 
                      FROM transaction_details td 
                      JOIN products p ON td.product_id = p.id 
                      GROUP BY p.id, p.code, p.name 
                      ORDER BY total_sold DESC 
                      LIMIT 5";
    $popular_items = $conn->query($popular_query);
}

// Fetch Least Popular Products (Admin Only, limit 5)
$unpopular_items = null;
if ($_SESSION['role'] === 'admin') {
    $unpopular_query = "SELECT p.code, p.name, COALESCE(SUM(td.quantity), 0) as total_sold, p.stock 
                        FROM products p 
                        LEFT JOIN transaction_details td ON p.id = td.product_id 
                        GROUP BY p.id, p.code, p.name, p.stock 
                        ORDER BY total_sold ASC, p.stock DESC, p.name ASC 
                        LIMIT 5";
    $unpopular_items = $conn->query($unpopular_query);
}
?>

<!-- Info Alerts -->
<?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
    <div class="alert alert-danger">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman tersebut.
    </div>
<?php endif; ?>

<!-- Welcome Banner -->
<div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%); color: white; border: none; margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.5rem 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Selamat Datang, <?= $_SESSION['name'] ?>!</h1>
        <p style="opacity: 0.9; max-width: 700px; font-size: 0.9rem;">
            Sistem YNZ membantu memantau stok, mencatat pengeluaran, memproses transaksi kasir, dan menganalisis produk demi keputusan bisnis yang tepat.
        </p>
    </div>
</div>

<!-- Keuntungan & Keuangan Hari Ini (Admin Only) -->
<?php if ($_SESSION['role'] === 'admin'): ?>
    <h3 style="margin-bottom: 1rem; font-weight: 600; color: var(--dark);">Kinerja Keuangan Hari Ini</h3>
    <div class="grid grid-cols-4" style="margin-bottom: 2rem;">
        <!-- Pemasukan Hari Ini -->
        <div class="stat-card">
            <div class="stat-details">
                <p>Pemasukan Hari Ini</p>
                <h3><?= format_rupiah($today_income) ?></h3>
            </div>
            <div class="stat-icon success">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1"></path>
                </svg>
            </div>
        </div>

        <!-- Pengeluaran Hari Ini -->
        <div class="stat-card">
            <div class="stat-details">
                <p>Pengeluaran Hari Ini</p>
                <h3><?= format_rupiah($today_expenses) ?></h3>
            </div>
            <div class="stat-icon danger">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Keuntungan Hari Ini -->
        <div class="stat-card">
            <div class="stat-details">
                <p>Keuntungan Hari Ini</p>
                <h3 class="<?= $today_profit >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= format_rupiah($today_profit) ?>
                </h3>
            </div>
            <div class="stat-icon primary">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>

        <!-- Transaksi Hari Ini -->
        <div class="stat-card">
            <div class="stat-details">
                <p>Transaksi Hari Ini</p>
                <h3><?= number_format($today_transactions) ?></h3>
            </div>
            <div class="stat-icon warning">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Statistik Toko (All Time / General) -->
<h3 style="margin-bottom: 1rem; font-weight: 600; color: var(--dark);">Statistik Toko (Umum)</h3>
<div class="grid grid-cols-3" style="margin-bottom: 2rem;">
    <!-- Card 1: Total Produk -->
    <div class="stat-card">
        <div class="stat-details">
            <p>Total Jenis Produk</p>
            <h3><?= number_format($total_products) ?></h3>
        </div>
        <div class="stat-icon primary">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
        </div>
    </div>
    
    <!-- Card 2: Total Stok -->
    <div class="stat-card">
        <div class="stat-details">
            <p>Total Stok Barang</p>
            <h3><?= number_format($total_stock) ?></h3>
        </div>
        <div class="stat-icon warning">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
        </div>
    </div>
    
    <!-- Card 3: Total Transaksi -->
    <div class="stat-card">
        <div class="stat-details">
            <p>Total Transaksi</p>
            <h3><?= number_format($total_transactions) ?></h3>
        </div>
        <div class="stat-icon success">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>
    </div>
</div>

<!-- Stock Alerts & Recent Transactions Grid -->
<div class="grid grid-cols-2" style="margin-bottom: 2rem;">
    <!-- Left Column: Low Stock Warnings -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title text-danger" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Stok Hampir Habis
            </h3>
            <span class="badge badge-danger"><?= $low_stock_items->num_rows ?> Produk</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if ($low_stock_items->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th style="text-align: center;">Stok</th>
                                <th style="text-align: center;">Min. Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $low_stock_items->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="font-semibold text-muted"><?= $item['code'] ?></span></td>
                                    <td><?= $item['name'] ?></td>
                                    <td style="text-align: center;"><span class="font-bold text-danger"><?= $item['stock'] ?></span></td>
                                    <td style="text-align: center;"><span class="text-muted"><?= $item['min_stock'] ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding: 0.75rem; text-align: center; border-top: 1px solid var(--border);">
                    <a href="products.php?filter=low_stock" class="btn btn-secondary btn-sm" style="width: 100%;">
                        Lihat Semua Stok Rendah
                    </a>
                </div>
            <?php else: ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--secondary);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom: 0.5rem; color: var(--success);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p style="font-weight: 500;">Semua stok aman!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Right Column: Recent Transactions -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">Transaksi Terbaru</h3>
            <span class="badge badge-primary">Hari Ini: <?= $today_transactions ?></span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if ($recent_trans->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Waktu</th>
                                <th>Kasir</th>
                                <th>Total</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($t = $recent_trans->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="font-semibold text-primary"><?= $t['invoice_no'] ?></span></td>
                                    <td><span style="font-size: 0.8rem;"><?= date('H:i', strtotime($t['created_at'])) ?></span></td>
                                    <td><?= $t['cashier_name'] ?? 'Sistem' ?></td>
                                    <td><span class="font-bold"><?= format_rupiah($t['total_price']) ?></span></td>
                                    <td style="text-align: center;">
                                        <a href="transaction_detail.php?id=<?= $t['id'] ?>" class="btn btn-secondary btn-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding: 0.75rem; text-align: center; border-top: 1px solid var(--border);">
                    <a href="transactions.php" class="btn btn-secondary btn-sm" style="width: 100%;">
                        Lihat Semua Riwayat
                    </a>
                </div>
            <?php else: ?>
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--secondary);">
                    <p style="font-weight: 500; margin-bottom: 0.5rem;">Belum ada transaksi</p>
                    <a href="pos.php" class="btn btn-primary btn-sm">
                        Buka POS Kasir
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Popularity Analytics (Admin Only) -->
<?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="grid grid-cols-2">
        <!-- Produk Terlaris -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3 class="card-title text-success" style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                    Produk Paling Laku (Terlaris)
                </h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if ($popular_items && $popular_items->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 70px; text-align: center;">Rank</th>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    <th style="text-align: center;">Jumlah Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                while ($item = $popular_items->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <span class="badge <?= $rank === 1 ? 'badge-warning' : ($rank === 2 ? 'badge-primary' : 'badge-secondary') ?>" style="padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                                #<?= $rank++ ?>
                                            </span>
                                        </td>
                                        <td><span class="font-semibold text-muted"><?= $item['code'] ?></span></td>
                                        <td><span class="font-semibold"><?= $item['name'] ?></span></td>
                                        <td style="text-align: center;"><span class="font-bold text-success"><?= number_format($item['total_sold']) ?> unit</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="padding: 3rem 1.5rem; text-align: center; color: var(--secondary);">
                        <p style="font-weight: 500;">Belum ada penjualan produk.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Produk Jarang Dibeli -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3 class="card-title text-warning" style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Produk yang Jarang Dibeli (Evaluasi Stok)
                </h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if ($unpopular_items && $unpopular_items->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    <th style="text-align: center;">Jumlah Terjual</th>
                                    <th style="text-align: center;">Stok Menumpuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $unpopular_items->fetch_assoc()): ?>
                                    <tr>
                                        <td><span class="font-semibold text-muted"><?= $item['code'] ?></span></td>
                                        <td><?= $item['name'] ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-secondary"><?= number_format($item['total_sold']) ?> unit</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="font-bold <?= $item['stock'] > 10 ? 'text-warning' : '' ?>"><?= number_format($item['stock']) ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="padding: 3rem 1.5rem; text-align: center; color: var(--secondary);">
                        <p style="font-weight: 500;">Belum ada data produk.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require_once 'footer.php';
?>
