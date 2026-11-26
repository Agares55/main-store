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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_POST['update_product'])) {
    $pid = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];

    $sql_upd = "UPDATE products SET name = $1, description = $2, price = $3, stock = $4, updated_at = NOW() WHERE id = $5";
    pg_query_params($local_conn, $sql_upd, array($name, $description, $price, $stock, $pid));

    if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "webp") {
            $filename = $pid . "_" . time() . "." . $ext;
            $target = "images/" . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                pg_query_params($local_conn, "UPDATE products SET url_image = $1 WHERE id = $2", array($target, $pid));
            }
        }
    }

    header("Location: manage_products.php");
    exit();
}

$res = pg_query_params($local_conn, "SELECT * FROM products WHERE id = $1 LIMIT 1", array($id));
$product = pg_fetch_assoc($res);
if (!$product) {
    header("Location: manage_products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Store - Edit Product</title>
    <link rel="icon" type="image/png" href="icons/main_store.png">
    <link rel="stylesheet" href="apple_light.css">
    <style>
        .edit-wrap {
            max-width: 560px;
            margin: 0 auto;
        }
        .field {
            margin-bottom: 14px;
        }
        .field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="apple-glow-third"></div>
    <div class="apple-layer apple-container">
        <div class="apple-card edit-wrap">
            <h2 style="margin-top: 0;">Edit Product</h2>

            <form method="post" action="edit_product.php?id=<?php echo $product['id']; ?>" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                <div class="field">
                    <label>Name</label>
                    <input type="text" name="name" class="apple-input" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="field">
                    <label>Price</label>
                    <input type="number" step="0.01" min="0" name="price" class="apple-input" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>

                <div class="field">
                    <label>Stock</label>
                    <input type="number" min="0" name="stock" class="apple-input" value="<?php echo (int)$product['stock']; ?>" required>
                </div>

                <div class="field">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="apple-textarea"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="field">
                    <label>Image (JPG, PNG, WEBP)</label>
                    <input type="file" name="image" class="apple-input" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 18px;">
                    <button type="submit" name="update_product" class="apple-btn">Save</button>
                    <a href="manage_products.php" class="apple-btn apple-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>