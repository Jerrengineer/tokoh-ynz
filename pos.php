<?php
$page_title = 'POS Kasir Penjualan';
require_once 'header.php';

// Fetch categories for filtering
$categories_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!-- Mobile Tab Switcher (Visible on Mobile/Tablet <= 1024px) -->
<div class="pos-mobile-nav">
    <button type="button" class="pos-mobile-tab-btn active" id="posTabCatalog">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
        </svg>
        Katalog Produk
    </button>
    <button type="button" class="pos-mobile-tab-btn" id="posTabCart">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        Keranjang (<span id="cartCountBadge">0</span>)
    </button>
</div>

<div class="pos-container">
    <!-- Left Panel: Product Catalog -->
    <div class="pos-catalog-panel">
        <div class="pos-search-bar">
            <input type="text" id="posSearch" class="form-control" placeholder="Cari nama produk atau barcode (kode)..." autocomplete="off" style="padding: 0.75rem 1rem;">
        </div>
        
        <!-- Categories tabs -->
        <div class="pos-categories-nav">
            <button class="pos-cat-btn active" data-category-id="0">Semua</button>
            <?php while ($cat = $categories_res->fetch_assoc()): ?>
                <button class="pos-cat-btn" data-category-id="<?= $cat['id'] ?>"><?= $cat['name'] ?></button>
            <?php endwhile; ?>
        </div>
        
        <!-- Products Grid Loader -->
        <div class="pos-products-grid" id="productsGrid">
            <!-- Dynamic products loaded via JS -->
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--secondary);">
                Memuat produk...
            </div>
        </div>
    </div>
    
    <!-- Right Panel: Shopping Cart -->
    <div class="pos-cart-panel">
        <div class="pos-cart-header">
            <h3>Keranjang Penjualan</h3>
            <button class="btn btn-secondary btn-sm" id="clearCartBtn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                Bersihkan
            </button>
        </div>
        
        <!-- Cart Items List -->
        <div class="pos-cart-items" id="cartItemsList">
            <div class="cart-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <p>Keranjang kosong</p>
                <p style="font-size: 0.75rem;">Klik produk di samping kiri untuk menambahkan</p>
            </div>
        </div>
        
        <!-- Cart Calculation Summary -->
        <div class="pos-cart-summary">
            <div class="summary-row">
                <span>Total Item:</span>
                <span id="totalItemsText" class="font-semibold">0</span>
            </div>
            <div class="summary-row total">
                <span>Total Belanja:</span>
                <span id="totalPriceText">Rp 0</span>
            </div>
            
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label for="payAmountInput" style="font-size: 0.8rem; font-weight: 600;">Jumlah Bayar (Rp)</label>
                <input type="number" id="payAmountInput" class="form-control" placeholder="Masukkan uang pembayaran..." min="0" step="500" disabled style="font-weight: 700; font-size: 1.1rem; padding: 0.5rem;">
            </div>
            
            <div class="summary-row" style="margin-bottom: 1rem;">
                <span style="font-weight: 500;">Kembalian:</span>
                <span id="changeAmountText" class="font-bold text-muted">Rp 0</span>
            </div>
            
            <button class="checkout-btn" id="checkoutBtn" disabled>
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Proses Transaksi (Bayar)
            </button>
        </div>
    </div>
</div>

<!-- Mobile Floating Cart Bar -->
<div class="pos-mobile-floating-bar" id="posMobileFloatingBar">
    <div class="floating-info">
        <span class="floating-count" id="floatingCountText">0 Item</span>
        <strong class="floating-total" id="floatingTotalText">Rp 0</strong>
    </div>
    <button type="button" class="btn btn-primary btn-sm" id="floatingCartBtn">
        <span>Lihat Keranjang</span>
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
        </svg>
    </button>
</div>

<!-- Checkout Success Modal -->
<div class="modal" id="successModal">
    <div class="modal-content">
        <div class="modal-header" style="background-color: var(--success); color: white;">
            <h3>Transaksi Berhasil!</h3>
            <button class="modal-close" id="modalCloseBtn" style="color: white;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1.5rem 2rem;">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <svg width="64" height="64" fill="none" stroke="var(--success)" stroke-width="2" viewBox="0 0 24 24" style="margin: 0 auto;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h4 style="font-size: 1.25rem; font-weight: 700; margin-top: 0.5rem; color: var(--success);" id="modalInvoiceNo">INV-XXXX</h4>
            </div>
            
            <div style="border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 1rem 0; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span class="text-muted">Total Belanja:</span>
                    <strong id="modalTotalVal">Rp 0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span class="text-muted">Jumlah Bayar:</span>
                    <strong id="modalPayVal">Rp 0</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Kembalian:</span>
                    <strong id="modalChangeVal" class="text-success">Rp 0</strong>
                </div>
            </div>
            
            <p style="text-align: center; font-size: 0.85rem; color: var(--secondary);">
                Stok barang telah otomatis terpotong secara sinkron pada database.
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="modalNewTxBtn">Transaksi Baru</button>
            <a href="#" class="btn btn-primary" id="modalPrintBtn" target="_blank">Detail / Cetak Nota</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // POS Cart & Products State
    let products = [];
    let cart = [];
    let activeCategory = 0;
    let searchQuery = '';
    
    // Elements
    const productsGrid = document.getElementById('productsGrid');
    const posSearch = document.getElementById('posSearch');
    const catButtons = document.querySelectorAll('.pos-cat-btn');
    const cartItemsList = document.getElementById('cartItemsList');
    const totalPriceText = document.getElementById('totalPriceText');
    const totalItemsText = document.getElementById('totalItemsText');
    const payAmountInput = document.getElementById('payAmountInput');
    const changeAmountText = document.getElementById('changeAmountText');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    
    // Modal Elements
    const successModal = document.getElementById('successModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalInvoiceNo = document.getElementById('modalInvoiceNo');
    const modalTotalVal = document.getElementById('modalTotalVal');
    const modalPayVal = document.getElementById('modalPayVal');
    const modalChangeVal = document.getElementById('modalChangeVal');
    const modalNewTxBtn = document.getElementById('modalNewTxBtn');
    const modalPrintBtn = document.getElementById('modalPrintBtn');
    
    // Mobile Tab Elements
    const posTabCatalog = document.getElementById('posTabCatalog');
    const posTabCart = document.getElementById('posTabCart');
    const cartCountBadge = document.getElementById('cartCountBadge');
    const posCatalogPanel = document.querySelector('.pos-catalog-panel');
    const posCartPanel = document.querySelector('.pos-cart-panel');
    const posMobileFloatingBar = document.getElementById('posMobileFloatingBar');
    const floatingCountText = document.getElementById('floatingCountText');
    const floatingTotalText = document.getElementById('floatingTotalText');
    const floatingCartBtn = document.getElementById('floatingCartBtn');
    let activeMobileTab = 'catalog';

    function switchMobileTab(tab) {
        activeMobileTab = tab;
        if (window.innerWidth <= 1024) {
            if (tab === 'catalog') {
                if (posTabCatalog) posTabCatalog.classList.add('active');
                if (posTabCart) posTabCart.classList.remove('active');
                if (posCatalogPanel) posCatalogPanel.classList.remove('hide-mobile');
                if (posCartPanel) posCartPanel.classList.add('hide-mobile');
                if (cart.length > 0 && posMobileFloatingBar) {
                    posMobileFloatingBar.classList.add('visible');
                }
            } else {
                if (posTabCart) posTabCart.classList.add('active');
                if (posTabCatalog) posTabCatalog.classList.remove('active');
                if (posCartPanel) posCartPanel.classList.remove('hide-mobile');
                if (posCatalogPanel) posCatalogPanel.classList.add('hide-mobile');
                if (posMobileFloatingBar) {
                    posMobileFloatingBar.classList.remove('visible');
                }
            }
        }
    }

    if (posTabCatalog && posTabCart) {
        posTabCatalog.addEventListener('click', () => switchMobileTab('catalog'));
        posTabCart.addEventListener('click', () => switchMobileTab('cart'));
    }
    if (floatingCartBtn) {
        floatingCartBtn.addEventListener('click', () => switchMobileTab('cart'));
    }

    // Initialize mobile view
    if (window.innerWidth <= 1024) {
        switchMobileTab('catalog');
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            if (posCatalogPanel) posCatalogPanel.classList.remove('hide-mobile');
            if (posCartPanel) posCartPanel.classList.remove('hide-mobile');
            if (posMobileFloatingBar) posMobileFloatingBar.classList.remove('visible');
        } else {
            switchMobileTab(activeMobileTab);
        }
    });

    // Load products initially
    loadProducts();
    
    // Search handler with debounce
    let searchTimeout;
    posSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchQuery = this.value.trim();
        searchTimeout = setTimeout(loadProducts, 200);
    });
    
    // Category tabs handler
    catButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            catButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeCategory = parseInt(this.getAttribute('data-category-id'));
            loadProducts();
        });
    });
    
    // Load products from API
    async function loadProducts() {
        try {
            const url = `api/get_products.php?search=${encodeURIComponent(searchQuery)}&category=${activeCategory}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network error');
            products = await response.json();
            renderProducts();
        } catch (error) {
            console.error('Error fetching products:', error);
            productsGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--danger);">
                    Gagal memuat produk. Silakan coba lagi.
                </div>
            `;
        }
    }
    
    // Render products in catalog
    function renderProducts() {
        if (products.length === 0) {
            productsGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--secondary);">
                    Produk tidak ditemukan.
                </div>
            `;
            return;
        }
        
        productsGrid.innerHTML = products.map(prod => {
            const isOutOfStock = prod.stock <= 0;
            const isLowStock = prod.stock > 0 && prod.stock <= prod.min_stock;
            
            let cardClass = 'product-item-card';
            let stockBadge = `<span class="badge badge-success">${prod.stock} unit</span>`;
            
            if (isOutOfStock) {
                cardClass += ' out-of-stock';
                stockBadge = `<span class="badge badge-danger">Habis</span>`;
            } else if (isLowStock) {
                cardClass += ' low-stock';
                stockBadge = `<span class="badge badge-warning">${prod.stock} unit</span>`;
            }
            
            return `
                <div class="${cardClass}" data-id="${prod.id}" ${isOutOfStock ? '' : `onclick="addToCart(${prod.id})"`}>
                    <div class="product-item-code">${prod.code}</div>
                    <div class="product-item-name">${escapeHTML(prod.name)}</div>
                    <div style="font-size: 0.75rem; color: var(--secondary); margin-bottom: 0.25rem;">
                        ${escapeHTML(prod.category_name || 'Tanpa Kategori')}
                    </div>
                    <div class="product-item-footer">
                        <div class="product-item-price">${prod.price_formatted}</div>
                        ${stockBadge}
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Helper to format Rupiah
    function rupiah(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(num);
    }
    
    // Helper to escape HTML to prevent XSS
    function escapeHTML(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Add product to cart
    window.addToCart = function(productId) {
        const prod = products.find(p => p.id == productId);
        if (!prod || prod.stock <= 0) return;
        
        const existingCartItem = cart.find(item => item.id == productId);
        
        if (existingCartItem) {
            // Check if adding exceeds stock
            if (existingCartItem.qty >= prod.stock) {
                alert(`Tidak dapat menambahkan lebih banyak! Stok tersedia hanya ${prod.stock} unit.`);
                return;
            }
            existingCartItem.qty++;
        } else {
            cart.push({
                id: prod.id,
                name: prod.name,
                price: parseFloat(prod.price),
                stock: parseInt(prod.stock),
                qty: 1
            });
        }
        
        updateCart();
    };
    
    // Update cart elements & totals
    function updateCart() {
        if (cart.length === 0) {
            cartItemsList.innerHTML = `
                <div class="cart-empty">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p>Keranjang kosong</p>
                    <p style="font-size: 0.75rem;">Klik produk di samping kiri untuk menambahkan</p>
                </div>
            `;
            totalPriceText.textContent = 'Rp 0';
            totalItemsText.textContent = '0';
            if (cartCountBadge) cartCountBadge.textContent = '0';
            if (floatingCountText) floatingCountText.textContent = '0 Item';
            if (floatingTotalText) floatingTotalText.textContent = 'Rp 0';
            if (posMobileFloatingBar) posMobileFloatingBar.classList.remove('visible');
            payAmountInput.disabled = true;
            payAmountInput.value = '';
            changeAmountText.textContent = 'Rp 0';
            changeAmountText.className = 'font-bold text-muted';
            checkoutBtn.disabled = true;
            return;
        }
        
        // Render Cart Items
        cartItemsList.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-details">
                    <div class="cart-item-name">${escapeHTML(item.name)}</div>
                    <div class="cart-item-price">${rupiah(item.price)}</div>
                    <div class="cart-item-qty-control">
                        <button class="qty-btn" onclick="adjustQty(${item.id}, -1)">-</button>
                        <input type="text" class="qty-input" value="${item.qty}" readonly>
                        <button class="qty-btn" onclick="adjustQty(${item.id}, 1)">+</button>
                    </div>
                </div>
                <div class="cart-item-subtotal">
                    <span class="cart-item-total-price">${rupiah(item.price * item.qty)}</span>
                    <button class="cart-item-remove" onclick="removeFromCart(${item.id})" title="Hapus Item">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
        
        // Totals calculations
        let totalItems = 0;
        let totalPrice = 0;
        
        cart.forEach(item => {
            totalItems += item.qty;
            totalPrice += item.price * item.qty;
        });
        
        totalPriceText.textContent = rupiah(totalPrice);
        totalItemsText.textContent = totalItems;
        if (cartCountBadge) cartCountBadge.textContent = totalItems;
        if (floatingCountText) floatingCountText.textContent = `${totalItems} Item`;
        if (floatingTotalText) floatingTotalText.textContent = rupiah(totalPrice);
        if (posMobileFloatingBar && activeMobileTab === 'catalog') {
            posMobileFloatingBar.classList.add('visible');
        }
        
        payAmountInput.disabled = false;
        
        // Update change calculation
        calculateChange(totalPrice);
    }
    
    // Adjust Qty
    window.adjustQty = function(id, val) {
        const item = cart.find(i => i.id == id);
        if (!item) return;
        
        const newQty = item.qty + val;
        
        if (newQty <= 0) {
            removeFromCart(id);
        } else if (newQty > item.stock) {
            alert(`Tidak dapat menambah jumlah! Stok tersedia hanya ${item.stock} unit.`);
        } else {
            item.qty = newQty;
            updateCart();
        }
    };
    
    // Remove from cart
    window.removeFromCart = function(id) {
        cart = cart.filter(item => item.id != id);
        updateCart();
    };
    
    // Clear whole cart
    clearCartBtn.addEventListener('click', function() {
        if (cart.length > 0 && confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
            cart = [];
            updateCart();
        }
    });
    
    // Calculate Change Amount
    function calculateChange(totalPrice) {
        const payAmount = parseFloat(payAmountInput.value) || 0;
        
        if (payAmount <= 0) {
            changeAmountText.textContent = 'Rp 0';
            changeAmountText.className = 'font-bold text-muted';
            checkoutBtn.disabled = true;
            return;
        }
        
        const change = payAmount - totalPrice;
        changeAmountText.textContent = rupiah(Math.max(0, change));
        
        if (change >= 0) {
            changeAmountText.className = 'font-bold text-success';
            checkoutBtn.disabled = false;
        } else {
            changeAmountText.textContent = 'Pembayaran kurang: ' + rupiah(Math.abs(change));
            changeAmountText.className = 'font-bold text-danger';
            checkoutBtn.disabled = true;
        }
    }
    
    // Change input typing listener
    payAmountInput.addEventListener('input', function() {
        let total = 0;
        cart.forEach(item => total += item.price * item.qty);
        calculateChange(total);
    });
    
    // Process Checkout Transaction
    checkoutBtn.addEventListener('click', async function() {
        if (cart.length === 0) return;
        
        let total = 0;
        cart.forEach(item => total += item.price * item.qty);
        
        const payAmount = parseFloat(payAmountInput.value) || 0;
        
        if (payAmount < total) {
            alert('Jumlah pembayaran kurang!');
            return;
        }
        
        if (!confirm('Apakah Anda yakin ingin memproses transaksi ini?')) return;
        
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Memproses...';
        
        try {
            const response = await fetch('api/process_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    items: cart.map(item => ({ id: item.id, qty: item.qty })),
                    pay_amount: payAmount
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show Success Modal
                modalInvoiceNo.textContent = result.invoice_no;
                modalTotalVal.textContent = rupiah(total);
                modalPayVal.textContent = rupiah(payAmount);
                modalChangeVal.textContent = rupiah(payAmount - total);
                modalPrintBtn.href = `transaction_detail.php?id=${result.transaction_id}&print=1`;
                
                successModal.classList.add('show');
                
                // Clear cart state
                cart = [];
                updateCart();
                
                // Reload Catalog stock count
                loadProducts();
            } else {
                alert('Transaksi gagal: ' + result.message);
            }
        } catch (error) {
            console.error('Error processing checkout:', error);
            alert('Terjadi kesalahan koneksi saat memproses transaksi.');
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = `
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Proses Transaksi (Bayar)
            `;
        }
    });
    
    // Close success modal and reset POS
    function resetPOS() {
        successModal.classList.remove('show');
        posSearch.value = '';
        searchQuery = '';
        activeCategory = 0;
        // Reset category buttons UI
        catButtons.forEach((b, i) => {
            if (i === 0) b.classList.add('active');
            else b.classList.remove('active');
        });
        loadProducts();
    }
    
    modalCloseBtn.addEventListener('click', resetPOS);
    modalNewTxBtn.addEventListener('click', resetPOS);
});
</script>

<?php
require_once 'footer.php';
?>
