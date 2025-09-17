<?php
session_start();
include "koneksi.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$messageType = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $menuId = (int)$_POST['menu_id'];
                $quantity = (int)$_POST['quantity'] ?: 1;
                
                if (isset($_SESSION['cart'][$menuId])) {
                    $_SESSION['cart'][$menuId] += $quantity;
                } else {
                    $_SESSION['cart'][$menuId] = $quantity;
                }
                
                echo json_encode(['success' => true, 'cart_count' => array_sum($_SESSION['cart'])]);
                exit;
                break;
                
            case 'remove':
                $menuId = (int)$_POST['menu_id'];
                unset($_SESSION['cart'][$menuId]);
                break;
                
            case 'update':
                $menuId = (int)$_POST['menu_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $_SESSION['cart'][$menuId] = $quantity;
                } else {
                    unset($_SESSION['cart'][$menuId]);
                }
                break;
                
            case 'clear':
                $_SESSION['cart'] = [];
                break;
                
            case 'checkout':
                // Process checkout
                if (!empty($_SESSION['cart'])) {
                    try {
                        $pdo->beginTransaction();
                        
                        // Insert customer
                        $stmt = $pdo->prepare("INSERT INTO Customer (Nama, Email, NomorHp, TanggalDaftar) VALUES (?, ?, ?, NOW())");
                        $stmt->execute([
                            $_POST['nama'],
                            $_POST['email'],
                            $_POST['nomor_hp']
                        ]);
                        $customerId = $pdo->lastInsertId();
                        
                        // Calculate total
                        $total = 0;
                        $cartItems = [];
                        
                        foreach ($_SESSION['cart'] as $menuId => $quantity) {
                            $stmt = $pdo->prepare("SELECT * FROM Menu WHERE MenuID = ?");
                            $stmt->execute([$menuId]);
                            $menu = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($menu) {
                                $subtotal = $menu['Harga'] * $quantity;
                                $total += $subtotal;
                                $cartItems[] = [
                                    'menu' => $menu,
                                    'quantity' => $quantity,
                                    'subtotal' => $subtotal
                                ];
                            }
                        }
                        
                        // Insert transaction
                        $stmt = $pdo->prepare("INSERT INTO Transaksi (CustomerID, Tanggal, TotalBayar, MetodePembayaran) VALUES (?, NOW(), ?, ?)");
                        $stmt->execute([$customerId, $total, $_POST['metode_pembayaran']]);
                        $transaksiId = $pdo->lastInsertId();
                        
                        // Insert transaction details
                        foreach ($cartItems as $item) {
                            $stmt = $pdo->prepare("INSERT INTO DetailTransaksi (TransaksiID, MenuID, Jumlah, Subtotal) VALUES (?, ?, ?, ?)");
                            $stmt->execute([
                                $transaksiId,
                                $item['menu']['MenuID'],
                                $item['quantity'],
                                $item['subtotal']
                            ]);
                            
                            // Update stock
                            $stmt = $pdo->prepare("UPDATE Menu SET Stok = Stok - ? WHERE MenuID = ?");
                            $stmt->execute([$item['quantity'], $item['menu']['MenuID']]);
                        }
                        
                        $pdo->commit();
                        $_SESSION['cart'] = [];
                        $message = 'Order berhasil dibuat! ID Transaksi: ' . $transaksiId;
                        $messageType = 'success';
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = 'Terjadi kesalahan: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
                break;
        }
    }
}

// Get cart items with details
$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $menuId => $quantity) {
        $stmt = $pdo->prepare("SELECT * FROM Menu WHERE MenuID = ?");
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($menu) {
            $subtotal = $menu['Harga'] * $quantity;
            $cartItems[] = [
                'menu' => $menu,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Shopping Cart - ORDOCOFFEE</title>
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            margin-top: 5rem;
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--white-color);
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
            align-items: start;
        }
        
        .cart-items {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 1rem;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 80px 1fr auto auto auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            background: var(--first-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .item-details h3 {
            color: var(--white-color);
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            color: var(--first-color);
            font-weight: var(--font-semi-bold);
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            background: var(--first-color);
            color: var(--white-color);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--white-color);
            padding: 0.25rem;
        }
        
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
        }
        
        .checkout-sidebar {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 1rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .checkout-form {
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            color: var(--white-color);
            margin-bottom: 0.5rem;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 0.5rem;
            color: var(--white-color);
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .total-section {
            padding: 1rem 0;
            border-top: 2px solid var(--first-color);
            margin-top: 1rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .total-final {
            font-size: 1.25rem;
            font-weight: var(--font-semi-bold);
            color: var(--first-color);
        }
        
        .empty-cart {
            text-align: center;
            color: var(--white-color);
            padding: 4rem 2rem;
        }
        
        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--first-color);
        }
        
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .message.success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid #2ecc71;
            color: #2ecc71;
        }
        
        .message.error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #e74c3c;
        }
        
        @media screen and (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header" id="header">
        <nav class="nav container">
            <a href="index.php" class="nav__logo">ORDOCOFFEE</a>
            
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li><a href="index.php#home" class="nav__link">HOME</a></li>
                    <li><a href="index.php#popular" class="nav__link">POPULAR</a></li>
                    <li><a href="index.php#about" class="nav__link">ABOUT US</a></li>
                    <li><a href="index.php#products" class="nav__link">PRODUCTS</a></li>
                    <li><a href="index.php#contact" class="nav__link">CONTACT</a></li>
                </ul>
                <div class="nav__close" id="nav-close">
                    <i class="ri-close-large-line"></i>
                </div>
            </div>
            
            <div class="nav__actions">
                <a href="cart.php" class="nav__cart">
                    <i class="ri-shopping-cart-line"></i>
                    <span class="cart-count"><?= array_sum($_SESSION['cart'] ?? []) ?></span>
                </a>
                <div class="nav__toggle" id="nav-toggle">
                    <i class="ri-apps-2-fill"></i>
                </div>
            </div>
        </nav>
    </header>

    <main class="cart-container">
        <h1 class="cart-header">Shopping Cart</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="ri-shopping-cart-line"></i>
                <h2>Your cart is empty</h2>
                <p>Add some delicious coffee to get started!</p>
                <a href="index.php" class="button" style="margin-top: 1rem;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <h2 style="color: var(--white-color); margin-bottom: 1.5rem;">Cart Items</h2>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <i class="ri-cup-fill"></i>
                            </div>
                            
                            <div class="item-details">
                                <h3><?= htmlspecialchars($item['menu']['NamaMenu']) ?></h3>
                                <p class="item-price">$<?= number_format($item['menu']['Harga'], 2) ?></p>
                            </div>
                            
                            <div class="quantity-controls">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="menu_id" value="<?= $item['menu']['MenuID'] ?>">
                                    <input type="hidden" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>">
                                    <button type="submit" class="quantity-btn">-</button>
                                </form>
                                
                                <input type="number" value="<?= $item['quantity'] ?>" class="quantity-input" readonly>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="menu_id" value="<?= $item['menu']['MenuID'] ?>">
                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                    <button type="submit" class="quantity-btn">+</button>
                                </form>
                            </div>
                            
                            <div style="color: var(--first-color); font-weight: var(--font-semi-bold);">
                                $<?= number_format($item['subtotal'], 2) ?>
                            </div>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="menu_id" value="<?= $item['menu']['MenuID'] ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 2rem;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="button" style="background: #e74c3c;">Clear Cart</button>
                        </form>
                    </div>
                </div>
                
                <div class="checkout-sidebar">
                    <h3 style="color: var(--white-color); margin-bottom: 1.5rem;">Checkout</h3>
                    
                    <form method="POST" class="checkout-form">
                        <input type="hidden" name="action" value="checkout">
                        
                        <div class="form-group">
                            <label for="nama">Nama Lengkap *</label>
                            <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required placeholder="your@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="nomor_hp">Nomor HP *</label>
                            <input type="tel" id="nomor_hp" name="nomor_hp" required placeholder="08xxxxxxxxxx">
                        </div>
                        
                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran *</label>
                            <select id="metode_pembayaran" name="metode_pembayaran" required>
                                <option value="">Pilih metode pembayaran</option>
                                <option value="Cash">Cash</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="E-Wallet">E-Wallet</option>
                            </select>
                        </div>
                        
                        <div class="total-section">
                            <div class="total-row">
                                <span style="color: var(--white-color);">Subtotal:</span>
                                <span style="color: var(--white-color);">$<?= number_format($total, 2) ?></span>
                            </div>
                            <div class="total-row">
                                <span style="color: var(--white-color);">Tax (0%):</span>
                                <span style="color: var(--white-color);">$0.00</span>
                            </div>
                            <div class="total-row total-final">
                                <span>Total:</span>
                                <span>$<?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="button" style="width: 100%; margin-top: 1rem;">
                            Place Order
                        </button>
                    </form>
                    
                    <a href="index.php" class="button" style="width: 100%; margin-top: 1rem; background: var(--dark-color); text-align: center; display: block;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <script src="assets/js/main.js"></script>
</body>
</html>