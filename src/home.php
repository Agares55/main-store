<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('refresh:0;url=signin.html');
    exit();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Store - Home</title>
    <link rel="icon" type="image/png" href="icons/main_store.png">
    <link rel="stylesheet" href="apple_light.css">
    <style>
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .menu-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 20px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.65);
            border: 1px solid var(--border-subtle);
            text-decoration: none;
            color: var(--text-primary);
            transition: transform 0.15s ease, background-color 0.2s ease;
        }
        .menu-item:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-primary);
        }
        .menu-title {
            font-weight: 600;
            font-size: 1.05rem;
        }
        .menu-sub {
            font-size: 0.88rem;
            color: var(--text-secondary);
        }
    </style>
</head>

<body>
    <div class="apple-glow-third"></div>
    <div class="apple-layer apple-container">
        <div class="apple-card">
            <div class="topbar">
                <div>
                    <h2 style="margin: 0;">Welcome to main store</h2>
                    <div style="color: var(--text-secondary); margin-top: 4px;">
                        <?php echo $_SESSION['user_firstname']." " . $_SESSION['user_lastname']; ?>
                        &middot; <span><?php echo htmlspecialchars($role); ?></span>
                    </div>
                </div>
                <a href="logout.php" class="apple-btn apple-btn-secondary">Logout</a>
            </div>

            <div class="menu-grid">
                <a href="catalog.php" class="menu-item">
                    <span class="menu-title">Catalog</span>
                    <span class="menu-sub">Browse items and add to checkout</span>
                </a>

                <a href="checkout.php" class="menu-item">
                    <span class="menu-title">Checkout</span>
                    <span class="menu-sub">Review selected items and complete payment</span>
                </a>

                <?php if ($role === 'admin') { ?>
                    <a href="list_users.php" class="menu-item">
                        <span class="menu-title">List users</span>
                        <span class="menu-sub">Manage user accounts and roles</span>
                    </a>

                    <a href="manage_products.php" class="menu-item">
                        <span class="menu-title">Manage products</span>
                        <span class="menu-sub">Add, edit, toggle, or delete catalog items</span>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>