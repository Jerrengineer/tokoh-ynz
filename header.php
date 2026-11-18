<?php
require_once 'config.php';
check_login();

// Get notification count for low stock products
$low_stock_count = get_low_stock_count();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Toko Kelontong YNZ' ?> - Sistem Informasi</title>
    <!-- CSS link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="app-sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-info">
                    <h3>Toko YNZ</h3>
                    <span><?= $_SESSION['role'] ?></span>
                </div>
                <button class="sidebar-close" id="sidebarClose" aria-label="Tutup Menu">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="sidebar-user">
                <div class="sidebar-user-name"><?= $_SESSION['name'] ?></div>
                <div class="sidebar-user-role">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Role: <?= $_SESSION['role'] ?>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">
                    <a href="categories.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Kategori Barang
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
                    <a href="products.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Daftar Produk
                    </a>
                </li>
                
                <li class="<?= $current_page == 'pos.php' ? 'active' : '' ?>">
                    <a href="pos.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Transaksi POS Kasir
                    </a>
                </li>
                
                <li class="<?= $current_page == 'transactions.php' || $current_page == 'transaction_detail.php' ? 'active' : '' ?>">
                    <a href="transactions.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Riwayat Transaksi
                    </a>
                </li>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="<?= $current_page == 'expenses.php' ? 'active' : '' ?>">
                    <a href="expenses.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M15 7h3a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2h3"></path>
                        </svg>
                        Pengeluaran Toko
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="sidebar-footer">
                <a href="logout.php">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Keluar (Logout)
                </a>
            </div>
        </aside>
        
        <!-- Sidebar Backdrop (Mobile/Tablet) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
        
        <!-- Main Content Wrapper -->
        <div class="app-main">
            <!-- Navbar -->
            <header class="app-navbar">
                <div class="navbar-left">
                    <button class="menu-toggle" id="menuToggle" aria-label="Buka Menu Navigasi">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="navbar-title"><?= $page_title ?? 'Dashboard' ?></h2>
                </div>
                
                <div class="navbar-right">
                    <!-- Notification Bell for Stock -->
                    <div class="nav-notification" onclick="window.location.href='products.php?filter=low_stock'" title="Stok Hampir Habis">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <?php if ($low_stock_count > 0): ?>
                            <span class="notification-badge"><?= $low_stock_count ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-muted navbar-date">
                        <?= date('d M Y') ?>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="app-content">
