<?php
$page_title = 'Kelola Kategori';
require_once 'header.php';
require_admin();

$success = '';
$error = '';

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    
    if (empty($name)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        
        try {
            if ($stmt->execute()) {
                $success = 'Kategori berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan kategori!';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = 'Kategori dengan nama "' . $name . '" sudah ada!';
            } else {
                $error = 'Kesalahan database: ' . $e->getMessage();
            }
        }
        $stmt->close();
    }
}

// Handle Edit Category
if (isset($_POST['edit_category'])) {
    $id = intval($_POST['id']);
    $name = sanitize($_POST['name']);
    
    if (empty($name)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        
        try {
            if ($stmt->execute()) {
                $success = 'Kategori berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui kategori!';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = 'Kategori dengan nama "' . $name . '" sudah ada!';
            } else {
                $error = 'Kesalahan database: ' . $e->getMessage();
            }
        }
        $stmt->close();
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Kategori berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus kategori!';
    }
    $stmt->close();
}

// Fetch all categories
$result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Fetch edit data if request exists
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_category = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
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
    <!-- Category Form Panel (Add / Edit) -->
    <div class="card layout-form-card" style="align-self: start;">
        <div class="card-header">
            <h3 class="card-title"><?= $edit_category ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h3>
        </div>
        <div class="card-body">
            <form action="categories.php" method="POST">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="id" value="<?= $edit_category['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Nama Kategori</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?= $edit_category ? $edit_category['name'] : '' ?>" 
                           placeholder="Contoh: Makanan Instan" required autocomplete="off">
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="submit" name="<?= $edit_category ? 'edit_category' : 'add_category' ?>" class="btn btn-primary" style="flex-grow: 1;">
                        <?= $edit_category ? 'Perbarui Kategori' : 'Simpan Kategori' ?>
                    </button>
                    <?php if ($edit_category): ?>
                        <a href="categories.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Category List Table Panel -->
    <div class="card layout-table-card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kategori</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">No</th>
                            <th>Nama Kategori</th>
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
                                    <td class="font-semibold"><?= $row['name'] ?></td>
                                    <td style="text-align: center;">
                                        <div class="table-actions" style="justify-content: center;">
                                            <a href="categories.php?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit Kategori">
                                                Edit
                                            </a>
                                            <a href="categories.php?delete=<?= $row['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Produk yang memiliki kategori ini akan diset tanpa kategori.');" 
                                               title="Hapus Kategori">
                                                Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--secondary); padding: 3rem;">
                                    Belum ada data kategori.
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
