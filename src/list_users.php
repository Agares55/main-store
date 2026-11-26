<?php
/*
Use this URL to print data in browser:
http://localhost:80/main-store/src/list_users.php
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

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

//Get data from users table
$sql="  
    select	
        id,
        firstname || ' ' || lastname as fullname,
        email,
        mobile_phone,
        role,
        status,
        case when status = true then 'Active' else 'Inactive' end as status,
        url_photo
    from
        users
";

if ($filter === 'active') {
    $sql .= " where status = true";
} elseif ($filter === 'inactive') {
    $sql .= " where status = false";
}

$sql .= " order by id asc";
$res = pg_query($local_conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Store - List users</title>
    <link rel="icon" type="image/png" href="icons/main_store.png">
    <link rel="stylesheet" href="apple_light.css">
    <style>
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        table.apple-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        table.apple-table th,
        table.apple-table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-subtle);
            vertical-align: middle;
        }
        table.apple-table thead th {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.45);
        }
        .action-icons {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .action-icons a {
            display: inline-flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="apple-glow-third"></div>
    <div class="apple-layer apple-container">
        <div class="apple-card">
            <div class="toolbar">
                <h2 style="margin: 0;">Users</h2>
                <form method="get" action="list_users.php" style="display: flex; gap: 8px; align-items: center;">
                    <select name="filter" class="apple-select" style="width: auto;" onchange="this.form.submit()">
                        <option value="all" <?php if($filter === 'all') echo 'selected'; ?>>List all users</option>
                        <option value="active" <?php if($filter === 'active') echo 'selected'; ?>>List active users only</option>
                        <option value="inactive" <?php if($filter === 'inactive') echo 'selected'; ?>>List desactive users only</option>
                    </select>
                </form>
            </div>

            <div class="table-wrap">
                <table class="apple-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Fullname</th>
                            <th>Email</th>
                            <th>Mobile phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row = pg_fetch_assoc($res)) { ?>
                        <tr>
                            <!-- Here print data from database -->
                            <td>
                                <img src="<?= $row['url_photo'] ?>" width="40" height="40" style="object-fit: cover; border-radius: 50%;" alt="User photo profile">
                            </td>
                            <td><?= $row['fullname'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['mobile_phone'] ?></td>
                            <td><?= $row['role'] ?></td>
                            <td><?= $row['status'] ?></td>
                            <td>
                                <div class="action-icons">
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>">
                                        <img src="icons/edit.png" alt="Edit user" width="20">
                                    </a>
                                    <a href="#">
                                        <img src="icons/search.png" alt="Search user" width="20">
                                    </a>
                                    <a href="delete_user.php?userID=<?= $row['id'] ?>">
                                        <img src="icons/delete.png" alt="Delete user user" width="20">
                                    </a>
                                    <a href="update_user_status.php?userID=<?= $row['id'] ?>&userStatus=<?= $row['status'] ?>">
                                        <?php
                                            if($row['status'] == 'Active'){
                                                echo "<img src='icons/disable.png' alt='Inactive user' width='20'>";
                                            }else{
                                                echo "<img src='icons/enable.png' alt='Active user' width='20'>";
                                            }
                                        ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px;">
                <a href="home.php" class="apple-btn apple-btn-secondary">Go to home</a>
            </div>
        </div>
    </div>
</body>
</html>