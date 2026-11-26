<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('refresh:0;url=signin.html');
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

include('../settings/database.php');

$paid_message = "";
$error_message = "";

// Quitar artículo de la selección de pago
if (isset($_GET['remove'])) {
    $rem_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$rem_id])) {
        unset($_SESSION['cart'][$rem_id]);
    }
    header("Location: checkout.php");
    exit();
}

// Procesar pago y descontar inventario
if (isset($_POST['pay_now'])) {
    if (empty($_SESSION['cart'])) {
        $error_message = "No items selected for payment.";
    } else {
        pg_query($local_conn, "BEGIN");
        $ok = true;

        foreach ($_SESSION['cart'] as $pid => $item) {
            $qty = (int)$item['qty'];
            $check = pg_query_params($local_conn, "SELECT stock, active FROM products WHERE id = $1 FOR UPDATE", array($pid));
            $prow = pg_fetch_assoc($check);

            if (!$prow || !$prow['active'] || (int)$prow['stock'] < $qty) {
                $ok = false;
                $error_message = "Insufficient stock for: " . htmlspecialchars($item['name']);
                break;
            }

            $upd = pg_query_params($local_conn, "UPDATE products SET stock = stock - $1, updated_at = NOW() WHERE id = $2", array($qty, $pid));
            if (!$upd) {
                $ok = false;
                $error_message = "Error updating inventory.";
                break;
            }
        }

        if ($ok) {
            pg_query($local_conn, "COMMIT");
            $_SESSION['cart'] = array();
            $paid_message = "Payment completed successfully.";
        } else {
            pg_query($local_conn, "ROLLBACK");
        }
    }
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += ((float)$item['price'] * (int)$item['qty']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Store - Checkout</title>
    <link rel="icon" type="image/png" href="icons/main_store.png">
    <link rel="stylesheet" href="apple_light.css">
    <style>
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        table.checkout-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.checkout-table th,
        table.checkout-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-subtle);
            text-align: left;
        }
        .checkout-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border-subtle);
        }
        .alert-ok {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            background: rgba(26, 127, 55, 0.12);
            color: var(--success);
            margin-bottom: 16px;
            font-weight: 500;
        }
        .alert-err {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            background: rgba(215, 0, 21, 0.12);
            color: var(--danger);
            margin-bottom: 16px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="apple-glow-third"></div>
    <div class="apple-layer apple-container">
        <div class="apple-card">
            <div class="top-row">
                <h2 style="margin: 0;">Checkout</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="catalog.php" class="apple-btn apple-btn-secondary">Catalog</a>
                    <a href="home.php" class="apple-btn apple-btn-secondary">Home</a>
                </div>
            </div>

            <?php if ($paid_message !== "") { ?>
                <div class="alert-ok"><?php echo $paid_message; ?></div>
            <?php } ?>
            <?php if ($error_message !== "") { ?>
                <div class="alert-err"><?php echo $error_message; ?></div>
            <?php } ?>

            <?php if (empty($_SESSION['cart'])) { ?>
                <p style="color: var(--text-secondary);">No items in checkout.</p>
            <?php } else { ?>
                <div style="overflow-x: auto;">
                    <table class="checkout-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($_SESSION['cart'] as $pid => $item) {
                            $sub = (float)$item['price'] * (int)$item['qty'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                                <td><?php echo (int)$item['qty']; ?></td>
                                <td>$<?php echo number_format($sub, 2); ?></td>
                                <td>
                                    <a href="checkout.php?remove=<?php echo $pid; ?>" class="apple-btn apple-btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;">Remove</a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

                <form method="post" action="checkout.php" class="checkout-summary">
                    <div style="font-size: 1.2rem; font-weight: 700;">
                        Total: $<?php echo number_format($total, 2); ?>
                    </div>
                    <button type="submit" name="pay_now" class="apple-btn">Confirm Payment</button>
                </form>
            <?php } ?>
        </div>
    </div>
</body>
</html>