<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');
$pageTitle = 'My Profile';
$active = 'profile';
$cid = $_SESSION['user_id'];
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); $phone = trim($_POST['phone']); $address = trim($_POST['address']);
    $newPassword = $_POST['new_password'] ?? '';

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=?, password=? WHERE id=?");
                $stmt->bind_param('ssssi', $name, $phone, $address, $hash, $cid);
                $stmt->execute();
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
            $stmt->bind_param('sssi', $name, $phone, $address, $cid);
            $stmt->execute();
        }
        if (!$error) {
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully.';
        }
    }
}

$me = $conn->query("SELECT * FROM users WHERE id = $cid")->fetch_assoc();
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel" style="max-width:600px;">
  <div class="panel-head"><h3>Update Profile</h3></div>
  <form method="POST">
    <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" value="<?php echo h($me['name']); ?>" required></div>
    <div class="form-group"><label>Email (cannot be changed)</label><input type="email" class="form-control" value="<?php echo h($me['email']); ?>" disabled></div>
    <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo h($me['phone']); ?>"></div>
    <div class="form-group"><label>Address</label><input type="text" name="address" class="form-control" value="<?php echo h($me['address']); ?>"></div>
    <div class="form-group"><label>New Password (leave blank to keep current)</label><input type="password" name="new_password" class="form-control"></div>
    <button type="submit" class="btn">Save Changes</button>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
