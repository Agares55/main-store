<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('refresh:0;url=signin.html');
    exit();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

include('../settings/database.php');

// Agregar artículo al carrito / pago
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

    $sql_p = "SELECT id, name, price, stock, url_image, active FROM products WHERE id = $1 AND active = true LIMIT 1";
    $res_p = pg_query_params($local_conn, $sql_p, array($product_id));
    $prod = pg_fetch_assoc($res_p);

    if ($prod && (int)$prod['stock'] > 0) {
        $max_qty = (int)$prod['stock'];
        $current_qty = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id]['qty'] : 0;
        $new_qty = min($max_qty, $current_qty + $qty);

        $_SESSION['cart'][$product_id] = array(
            'id' => $prod['id'],
            'name' => $prod['name'],
            'price' => (float)$prod['price'],
            'qty' => $new_qty,
            'url_image' => $prod['url_image']
        );
    }
    header("Location: catalog.php");
    exit();
}

$sql = "SELECT id, name, description, price, stock, url_image, active FROM products WHERE active = true ORDER BY id DESC";
$res = pg_query($local_conn, $sql);

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += (int)$item['qty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Store - Catalog</title>
    <link rel="icon" type="image/png" href="icons/main_store.png">
    <link rel="stylesheet" href="apple_light.css">
    <style>
        .header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .nav-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 18px;
        }
        .product-card {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
        }
        .product-thumb {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            background: rgba(0, 0, 0, 0.04);
        }
        .product-title {
            font-weight: 600;
            font-size: 1.05rem;
            margin: 8px 0 4px;
        }
        .product-desc {
            color: var(--text-secondary);
            font-size: 0.88rem;
            margin: 0;
        }
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-weight: 600;
        }
        .add-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="apple-glow-third"></div>
    <div class="apple-layer apple-container">
        <div class="apple-card">
            <div class="header-bar">
                <div>
                    <h2 style="margin: 0;">Catalog</h2>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                        <?php echo $_SESSION['user_firstname'] . " " . $_SESSION['user_lastname']; ?>
                    </div>
                </div>
                <div class="nav-actions">
                    <a href="checkout.php" class="apple-btn">Checkout (<?php echo $cart_count; ?>)</a>
                    <?php if ($role === 'admin') { ?>
                        <a href="manage_products.php" class="apple-btn apple-btn-secondary">Manage products</a>
                    <?php } ?>
                    <a href="home.php" class="apple-btn apple-btn-secondary">Home</a>
                </div>
            </div>

            <div class="catalog-grid">
                <?php while ($row = pg_fetch_assoc($res)) {
                    $img = !empty($row['url_image']) ? $row['url_image'] : 'images/product_default.png';
                ?>
                    <div class="product-card">
                        <div>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-thumb" onerror="this.style.display='none';">
                            <div class="product-title"><?php echo htmlspecialchars($row['name']); ?></div>
                            <p class="product-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="product-meta">
                                <span>$<?php echo number_format((float)$row['price'], 2); ?></span>
                                <span style="color: var(--text-secondary); font-weight: 500; font-size: 0.85rem;">
                                    Stock: <?php echo (int)$row['stock']; ?>
                                </span>
                            </div>
                        </div>

                        <?php if ((int)$row['stock'] > 0) { ?>
                            <form method="post" action="catalog.php" class="add-form">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="number" name="qty" value="1" min="1" max="<?php echo (int)$row['stock']; ?>" class="apple-input" style="width: 76px;">
                                <button type="submit" name="add_to_cart" class="apple-btn" style="flex: 1;">Add</button>
                            </form>
                        <?php } else { ?>
                            <div style="color: var(--danger); font-size: 0.88rem; font-weight: 500;">Out of stock</div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>