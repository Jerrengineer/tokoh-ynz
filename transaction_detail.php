<?php
$page_title = 'Detail Transaksi';
require_once 'config.php';
check_login();

if (!isset($_GET['id'])) {
    header("Location: transactions.php");
    exit;
}

$transaction_id = intval($_GET['id']);

// Fetch main transaction data
$stmt = $conn->prepare("SELECT t.*, u.name as cashier_name 
                        FROM transactions t 
                        LEFT JOIN users u ON t.user_id = u.id 
                        WHERE t.id = ?");
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$tx = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tx) {
    header("Location: transactions.php?error=not_found");
    exit;
}

// Fetch transaction details (products list)
$details_query = "SELECT td.*, p.name as product_name, p.code as product_code 
                  FROM transaction_details td 
                  LEFT JOIN products p ON td.product_id = p.id 
                  WHERE td.transaction_id = $transaction_id";
$details_res = $conn->query($details_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota <?= $tx['invoice_no'] ?> - Toko Kelontong YNZ</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Print Styles */
        @media print {
            body {
                background-color: white;
                color: black;
                font-size: 12pt;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-area {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .receipt-ticket {
                width: 80mm; /* standard thermal printer size */
                margin: 0 auto;
                font-family: monospace;
            }
            .receipt-ticket table {
                font-size: 9pt;
            }
            .app-layout, .app-main, .app-content {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
        
        .receipt-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed var(--border);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .receipt-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .receipt-total-section {
            border-top: 2px dashed var(--border);
            padding-top: 1rem;
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }

        @media (max-width: 640px) {
            .receipt-container {
                margin: 0.75rem auto;
                padding: 1.25rem 1rem;
                border-radius: var(--radius);
            }
            .receipt-header {
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }
            .receipt-header h2 {
                font-size: 1.3rem !important;
            }
            .receipt-info-grid {
                font-size: 0.8rem;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }
            .receipt-total-section div {
                width: 100% !important;
            }
            .receipt-ticket table {
                font-size: 8.5pt;
            }
        }
    </style>
</head>
<body style="background-color: var(--light);">

    <div class="no-print" style="max-width: 600px; margin: 1rem auto 0 auto; padding: 0 1rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="transactions.php" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        
        <button onclick="window.print()" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak Nota
        </button>
    </div>

    <!-- Print area wrapper -->
    <div class="receipt-container print-area">
        <div class="receipt-header">
            <h2 style="font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">Toko Kelontong YNZ</h2>
            <p style="font-size: 0.85rem; color: var(--secondary);">Jl. Raya No. 123, Kota YNZ</p>
            <p style="font-size: 0.85rem; color: var(--secondary);">Telp: 0812-3456-7890</p>
        </div>
        
        <div class="receipt-info-grid">
            <div>
                <p class="text-muted">No. Invoice:</p>
                <strong style="color: var(--primary);"><?= $tx['invoice_no'] ?></strong>
            </div>
            <div style="text-align: right;">
                <p class="text-muted">Tanggal / Waktu:</p>
                <strong><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?></strong>
            </div>
            <div>
                <p class="text-muted">Kasir:</p>
                <strong><?= $tx['cashier_name'] ?? 'Sistem' ?></strong>
            </div>
            <div style="text-align: right;">
                <p class="text-muted">Status:</p>
                <span class="badge badge-success">Lunas</span>
            </div>
        </div>
        
        <!-- Purchased items list -->
        <table class="table" style="margin-top: 1rem; width: 100%;">
            <thead>
                <tr>
                    <th style="padding: 0.5rem 0; background: none; border-bottom: 1px solid var(--border);">Item</th>
                    <th style="padding: 0.5rem 0; background: none; border-bottom: 1px solid var(--border); text-align: center;">Harga</th>
                    <th style="padding: 0.5rem 0; background: none; border-bottom: 1px solid var(--border); text-align: center; width: 60px;">Qty</th>
                    <th style="padding: 0.5rem 0; background: none; border-bottom: 1px solid var(--border); text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $details_res->fetch_assoc()): ?>
                    <tr>
                        <td style="padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9;">
                            <span class="font-semibold"><?= $row['product_name'] ?? 'Produk Terhapus' ?></span><br>
                            <span style="font-size: 0.75rem; color: var(--secondary);"><?= $row['product_code'] ?? '-' ?></span>
                        </td>
                        <td style="padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            <?= format_rupiah($row['price']) ?>
                        </td>
                        <td style="padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            <?= $row['quantity'] ?>
                        </td>
                        <td style="padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; text-align: right;" class="font-semibold">
                            <?= format_rupiah($row['subtotal']) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="receipt-total-section">
            <div style="width: 250px; display: flex; justify-content: space-between; font-size: 0.95rem;">
                <span class="text-muted">Total Belanja:</span>
                <strong><?= format_rupiah($tx['total_price']) ?></strong>
            </div>
            <div style="width: 250px; display: flex; justify-content: space-between; font-size: 0.95rem;">
                <span class="text-muted">Bayar:</span>
                <strong><?= format_rupiah($tx['pay_amount']) ?></strong>
            </div>
            <div style="width: 250px; display: flex; justify-content: space-between; font-size: 1.1rem; border-top: 1px solid var(--border); padding-top: 0.5rem; margin-top: 0.25rem;">
                <span class="font-semibold">Kembali:</span>
                <strong class="text-success"><?= format_rupiah($tx['change_amount']) ?></strong>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem; color: var(--secondary); font-size: 0.8rem;">
            <p>Terima Kasih Atas Kunjungan Anda!</p>
            <p style="margin-top: 0.25rem; font-family: monospace;">Sistem POS Kelontong YNZ</p>
        </div>
    </div>

    <!-- Automatically open browser print preview if requested -->
    <?php if (isset($_GET['print']) && $_GET['print'] == 1): ?>
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    <?php endif; ?>
</body>
</html>
