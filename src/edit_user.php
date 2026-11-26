<?php
/*
Use this Endpoint to test request
http://localhost/main-store/src/edit_user.php?userID=ANY-ID-NUMBER
*/
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
 
$id = $_GET['id'];   // el número que llegó en la URL

$sql = 'SELECT * FROM users WHERE id = $1';
$result = pg_query_params($local_conn, $sql, array($id));
$user = pg_fetch_assoc($result);   // una sola fila: el usuario a editar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar usuario</title>
    <link rel="icon" type="image/png" href="icons/main_store.png">
    <link rel="stylesheet" href="apple_light.css">
    <style>
        .form-card {
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
        <div class="apple-card form-card">
            <h1 style="margin-top: 0;">Editar usuario</h1>

            <form action="update_user.php" method="post" enctype="multipart/form-data">

                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                <div class="field">
                    <label>Nombre</label>
                    <input type="text" name="firstname" class="apple-input" value="<?php echo $user['firstname']; ?>" required>
                </div>

                <div class="field">
                    <label>Apellido</label>
                    <input type="text" name="lastname" class="apple-input" value="<?php echo $user['lastname']; ?>" required>
                </div>

                <div class="field">
                    <label>Correo</label>
                    <input type="email" name="email" class="apple-input" value="<?php echo $user['email']; ?>" required>
                </div>

                <div class="field">
                    <label>Celular</label>
                    <input type="text" name="mobile_phone" class="apple-input" value="<?php echo $user['mobile_phone']; ?>" required>
                </div>

                <div class="field">
                    <label>Rol</label>
                    <select name="role" class="apple-select">
                        <option value="user" <?php if($user['role'] === 'user') echo 'selected'; ?>>user</option>
                        <option value="admin" <?php if($user['role'] === 'admin') echo 'selected'; ?>>admin</option>
                    </select>
                </div>

                <?php if ($user['url_photo'] != "") { ?>
                    <div class="field">
                        <p style="margin: 0 0 6px;">Foto actual:</p>
                        <img src="<?php echo $user['url_photo']; ?>" width="120" style="border-radius: 12px;">
                    </div>
                <?php } ?>

                <div class="field">
                    <label>Foto (JPG o PNG)</label>
                    <input type="file" name="photo" class="apple-input" accept=".jpg,.jpeg,.png">
                </div>

                <div style="display: flex; gap: 10px; align-items: center; margin-top: 18px;">
                    <button type="submit" class="apple-btn">Guardar</button>
                    <a href="list_users.php" class="apple-btn apple-btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>