<?php
$page_title = 'Daftar Produk';
require_once 'header.php';

$success = '';
$error = '';
$is_admin = ($_SESSION['role'] === 'admin');

// Handle Add Product (Admin Only)
if ($is_admin && isset($_POST['add_product'])) {
    $code = sanitize($_POST['code']);
    $category_id = empty($_POST['category_id']) ? null : intval($_POST['category_id']);
    $name = sanitize($_POST['name']);
    $price = floatval($_POST['price']);
    $cost_price = floatval($_POST['cost_price']);
    $stock = intval($_POST['stock']);
    $min_stock = intval($_POST['min_stock']);
    
    if (empty($code) || empty($name) || $price <= 0 || $cost_price <= 0) {
        $error = 'Kode, Nama, Harga Jual, dan Harga Modal wajib diisi dengan benar!';
    } else {
        $stmt = $conn->prepare("INSERT INTO products (code, category_id, name, price, cost_price, stock, min_stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisddii", $code, $category_id, $name, $price, $cost_price, $stock, $min_stock);
        
        try {
            if ($stmt->execute()) {
                $success = 'Produk berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan produk!';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = 'Produk dengan kode "' . $code . '" sudah ada!';
            } else {
                $error = 'Kesalahan database: ' . $e->getMessage();
            }
        }
        $stmt->close();
    }
}

// Handle Edit Product (Admin Only)
if ($is_admin && isset($_POST['edit_product'])) {
    $id = intval($_POST['id']);
    $code = sanitize($_POST['code']);
    $category_id = empty($_POST['category_id']) ? null : intval($_POST['category_id']);
    $name = sanitize($_POST['name']);
    $price = floatval($_POST['price']);
    $cost_price = floatval($_POST['cost_price']);
    $stock = intval($_POST['stock']);
    $min_stock = intval($_POST['min_stock']);
    
    if (empty($code) || empty($name) || $price <= 0 || $cost_price <= 0) {
        $error = 'Kode, Nama, Harga Jual, dan Harga Modal wajib diisi dengan benar!';
    } else {
        $stmt = $conn->prepare("UPDATE products SET code = ?, category_id = ?, name = ?, price = ?, cost_price = ?, stock = ?, min_stock = ? WHERE id = ?");
        $stmt->bind_param("sisddiii", $code, $category_id, $name, $price, $cost_price, $stock, $min_stock, $id);
        
        try {
            if ($stmt->execute()) {
                $success = 'Produk berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui produk!';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = 'Produk dengan kode "' . $code . '" sudah ada!';
            } else {
                $error = 'Kesalahan database: ' . $e->getMessage();
            }
        }
        $stmt->close();
    }
}

// Handle Delete Product (Admin Only)
if ($is_admin && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Produk berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus produk!';
    }
    $stmt->close();
}

// Filters and Search Setup
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$stock_filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : '';

$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "(p.code LIKE '%$search%' OR p.name LIKE '%$search%')";
}
if ($cat_filter > 0) {
    $where_clauses[] = "p.category_id = $cat_filter";
}
if ($stock_filter === 'low_stock') {
    $where_clauses[] = "p.stock <= p.min_stock";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch all products
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   $where_sql 
                   ORDER BY p.name ASC";
$products_res = $conn->query($products_query);

// Fetch categories for form and filter dropdown
$categories_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($cat = $categories_res->fetch_assoc()) {
    $categories[] = $cat;
}

// Fetch single product data for edit form (if requested)
$edit_product = null;
if ($is_admin && isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
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

<div class="grid grid-cols-3">
    <!-- Form Panel (Admin Add/Edit Form) -->
    <?php if ($is_admin): ?>
        <div class="card" style="grid-column: span 1; align-self: start;">
            <div class="card-header">
                <h3 class="card-title"><?= $edit_product ? 'Edit Produk' : 'Tambah Produk Baru' ?></h3>
            </div>
            <div class="card-body">
                <form action="products.php" method="POST">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="code">Kode Produk (SKU / Barcode)</label>
                        <input type="text" id="code" name="code" class="form-control" 
                               value="<?= $edit_product ? $edit_product['code'] : 'PRD' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) ?>" 
                               placeholder="Contoh: PRD001" required autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Nama Produk</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= $edit_product ? $edit_product['name'] : '' ?>" 
                               placeholder="Contoh: Teh Botol Sosro" required autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" class="form-control">
                            <option value="">-- Tanpa Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($edit_product && $edit_product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= $cat['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2" style="gap: 1rem;">
                        <div class="form-group">
                            <label for="cost_price">Harga Modal</label>
                            <input type="number" id="cost_price" name="cost_price" class="form-control" 
                                   value="<?= $edit_product ? $edit_product['cost_price'] : '' ?>" 
                                   placeholder="0" min="0" step="100" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Harga Jual</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   value="<?= $edit_product ? $edit_product['price'] : '' ?>" 
                                   placeholder="0" min="0" step="100" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2" style="gap: 1rem;">
                        <div class="form-group">
                            <label for="stock">Stok Awal</label>
                            <input type="number" id="stock" name="stock" class="form-control" 
                                   value="<?= $edit_product ? $edit_product['stock'] : '0' ?>" 
                                   placeholder="0" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="min_stock">Stok Minimum</label>
                            <input type="number" id="min_stock" name="min_stock" class="form-control" 
                                   value="<?= $edit_product ? $edit_product['min_stock'] : '5' ?>" 
                                   placeholder="5" min="0" required>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                        <button type="submit" name="<?= $edit_product ? 'edit_product' : 'add_product' ?>" class="btn btn-primary" style="flex-grow: 1;">
                            <?= $edit_product ? 'Perbarui Produk' : 'Simpan Produk' ?>
                        </button>
                        <?php if ($edit_product): ?>
                            <a href="products.php" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Table List Panel -->
    <div class="card" style="grid-column: span <?= $is_admin ? '2' : '3' ?>;">
        <div class="card-header">
            <h3 class="card-title">Daftar Produk & Stok</h3>
            <?php if ($stock_filter === 'low_stock'): ?>
                <span class="badge badge-danger">Filter: Stok Rendah</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form action="products.php" method="GET" style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                <?php if ($stock_filter === 'low_stock'): ?>
                    <input type="hidden" name="filter" value="low_stock">
                <?php endif; ?>
                
                <div class="form-group" style="margin-bottom: 0; flex-grow: 1; min-width: 200px;">
                    <label for="search" style="font-size: 0.75rem;">Cari Produk</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Ketik kode atau nama produk..." value="<?= $search ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0; width: 180px;">
                    <label for="category" style="font-size: 0.75rem;">Kategori</label>
                    <select id="category" name="category" class="form-control">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($cat_filter == $cat['id']) ? 'selected' : '' ?>>
                                <?= $cat['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if (!empty($search) || $cat_filter > 0 || $stock_filter === 'low_stock'): ?>
                        <a href="products.php" class="btn btn-secondary" title="Reset Filter">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <?php if ($is_admin): ?>
                                <th>Harga Modal</th>
                            <?php endif; ?>
                            <th>Harga Jual</th>
                            <th style="text-align: center;">Stok</th>
                            <?php if ($is_admin): ?>
                                <th style="width: 130px; text-align: center;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products_res->num_rows > 0): ?>
                            <?php while ($row = $products_res->fetch_assoc()): 
                                $is_low_stock = ($row['stock'] <= $row['min_stock']);
                                $stock_class = $is_low_stock ? 'text-danger font-bold' : '';
                            ?>
                                <tr class="<?= $is_low_stock ? 'low-stock-tr' : '' ?>">
                                    <td><span class="font-semibold text-muted"><?= $row['code'] ?></span></td>
                                    <td>
                                        <div class="font-semibold"><?= $row['name'] ?></div>
                                        <?php if ($is_low_stock): ?>
                                            <span class="badge badge-danger" style="font-size: 0.6rem; padding: 0.1rem 0.3rem;">Stok Rendah (Min. <?= $row['min_stock'] ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $row['category_name'] ? 'badge-primary' : 'badge-secondary' ?>">
                                            <?= $row['category_name'] ?? 'Tanpa Kategori' ?>
                                        </span>
                                    </td>
                                    <?php if ($is_admin): ?>
                                        <td><?= format_rupiah($row['cost_price']) ?></td>
                                    <?php endif; ?>
                                    <td class="font-bold text-success"><?= format_rupiah($row['price']) ?></td>
                                    <td style="text-align: center;">
                                        <span class="<?= $stock_class ?>"><?= $row['stock'] ?></span>
                                    </td>
                                    <?php if ($is_admin): ?>
                                        <td style="text-align: center;">
                                            <div class="table-actions" style="justify-content: center;">
                                                <a href="products.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit Produk">
                                                    Edit
                                                </a>
                                                <a href="products.php?delete=<?= $row['id'] ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" 
                                                   title="Hapus Produk">
                                                    Hapus
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $is_admin ? '7' : '5' ?>" style="text-align: center; color: var(--secondary); padding: 3rem;">
                                    Tidak ada data produk yang cocok dengan pencarian Anda.
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
