<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('refresh:0;url=signin.html');
    exit();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
if ($role !== 'admin') {
    header('refresh:0;url=home.php');
    exit();
}

include('../settings/database.php');

// Crear nuevo artículo
if (isset($_POST['create_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $url_image = 'images/product_default.png';

    if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "webp") {
            $filename = time() . "_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . "." . $ext;
            $target = "images/" . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $url_image = $target;
            }
        }
    }

    if ($name !== "") {
        $sql_ins = "INSERT INTO products (name, description, price, stock, url_image, active) VALUES ($1, $2, $3, $4, $5, true)";
        pg_query_params($local_conn, $sql_ins, array($name, $description, $price, $stock, $url_image));
    }
    header("Location: manage_products.php");
    exit();
}

// Activar / desactivar artículo
if (isset($_GET['toggle_id'])) {
    $tid = (int)$_GET['toggle_id'];
    pg_query_params($local_conn, "UPDATE products SET active = NOT active, updated_at = NOW() WHERE id = $1", array($tid));
    header("Location: manage_products.php");
    exit();
}

// Eliminar artículo
if (isset($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    pg_query_params($local_conn, "DELETE FROM products WHERE id = $1", array($did));
    if (isset($_SESSION['cart'][$did])) {
        unset($_SESSION['cart'][$did]);
    }
    header("Location: manage_products.php");
    exit();
}

$res = pg_query($local_conn, "SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Store - Manage Products</title>
    <link rel="icon" type="image/png" href="icons/main_store.png">
    <link rel="stylesheet" href="apple_light.css">
    <style>
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
            padding: 18px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid var(--border-subtle);
        }
        .form-grid label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        table.prod-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.prod-table th,
        table.prod-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-subtle);
            text-align: left;
            vertical-align: middle;
        }
        .row-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="apple-glow-third"></div>
    <div class="apple-layer apple-container">
        <div class="apple-card">
            <div class="top-bar">
                <h2 style="margin: 0;">Manage Products</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="catalog.php" class="apple-btn apple-btn-secondary">Catalog</a>
                    <a href="checkout.php" class="apple-btn apple-btn-secondary">Checkout</a>
                    <a href="home.php" class="apple-btn apple-btn-secondary">Home</a>
                </div>
            </div>

            <form method="post" action="manage_products.php" enctype="multipart/form-data" class="form-grid">
                <div>
                    <label>Name</label>
                    <input type="text" name="name" class="apple-input" required>
                </div>
                <div>
                    <label>Price</label>
                    <input type="number" step="0.01" min="0" name="price" class="apple-input" required>
                </div>
                <div>
                    <label>Stock</label>
                    <input type="number" min="0" name="stock" class="apple-input" required>
                </div>
                <div>
                    <label>Image (JPG, PNG, WEBP)</label>
                    <input type="file" name="image" class="apple-input" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <textarea name="description" rows="2" class="apple-textarea"></textarea>
                </div>
                <div style="grid-column: 1 / -1;">
                    <button type="submit" name="create_product" class="apple-btn">Add Item</button>
                </div>
            </form>

            <div style="overflow-x: auto;">
                <table class="prod-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($p = pg_fetch_assoc($res)) { ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($p['url_image']); ?>" width="44" height="44" style="object-fit: cover; border-radius: 8px;" onerror="this.style.display='none';">
                            </td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td>$<?php echo number_format((float)$p['price'], 2); ?></td>
                            <td><?php echo (int)$p['stock']; ?></td>
                            <td><?php echo ($p['active'] === 't' || $p['active'] === true) ? 'Active' : 'Inactive'; ?></td>
                            <td>
                                <div class="row-actions">
                                    <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="apple-btn apple-btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">Edit</a>
                                    <a href="manage_products.php?toggle_id=<?php echo $p['id']; ?>" class="apple-btn apple-btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">Toggle</a>
                                    <a href="manage_products.php?delete_id=<?php echo $p['id']; ?>" class="apple-btn apple-btn-danger" style="padding: 6px 12px; font-size: 0.85rem;">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>