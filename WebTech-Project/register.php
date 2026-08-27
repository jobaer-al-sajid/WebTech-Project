<?php
require_once __DIR__ . '/includes/auth.php';
if (isset($_SESSION['user_id'])) redirect_to_dashboard();

$pageTitle = 'Register';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Name, email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, 'customer', ?, ?)");
            $stmt->bind_param('sssss', $name, $email, $hash, $phone, $address);
            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}

require_once __DIR__ . '/includes/site_header.php';
?>
<div class="auth-wrap">
  <div class="auth-box" style="max-width:480px;">
    <h2>🦷 Create Account</h2>
    <p class="sub">Register as a customer to book appointments</p>

    <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?> <a href="<?php echo BASE_URL; ?>/login.php">Login now &rarr;</a></div><?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" class="form-control" required value="<?php echo h($_POST['name'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" required value="<?php echo h($_POST['email'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" class="form-control" value="<?php echo h($_POST['phone'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label>Address</label>
        <input type="text" name="address" class="form-control" value="<?php echo h($_POST['address'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn" style="width:100%;">Register</button>
    </form>
    <?php endif; ?>

    <p class="foot-link">Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Login</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
