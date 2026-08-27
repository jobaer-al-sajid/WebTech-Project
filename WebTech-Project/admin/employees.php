<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Manage Employees';
$active = 'employees';
$error = ''; $success = '';

// ---- CREATE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']); $email = trim($_POST['email']);
    $phone = trim($_POST['phone']); $address = trim($_POST['address']);
    $designation = trim($_POST['designation']); $password = $_POST['password'];

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Name, email and password are required.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address, designation) VALUES (?, ?, ?, 'employee', ?, ?, ?)");
            $stmt->bind_param('ssssss', $name, $email, $hash, $phone, $address, $designation);
            $stmt->execute();
            $success = 'Employee added successfully.';
        }
    }
}

// ---- DELETE ----
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $success = 'Employee removed.';
}

// ---- TOGGLE STATUS ----
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $conn->query("UPDATE users SET status = IF(status='active','inactive','active') WHERE id = $id AND role='employee'");
    $success = 'Status updated.';
}

$employees = $conn->query("SELECT * FROM users WHERE role='employee' ORDER BY created_at DESC");
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>Add New Employee</h3></div>
  <form method="POST">
    <input type="hidden" name="action" value="add">
    <div class="grid grid-3">
      <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
      <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
      <div class="form-group"><label>Designation</label><input type="text" name="designation" class="form-control" placeholder="e.g. Dentist, Receptionist"></div>
      <div class="form-group"><label>Address</label><input type="text" name="address" class="form-control"></div>
    </div>
    <button type="submit" class="btn">+ Add Employee</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h3>All Employees</h3></div>
  <table>
    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Designation</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    <?php if ($employees->num_rows === 0): ?>
      <tr><td colspan="7" class="empty-state">No employees added yet.</td></tr>
    <?php else: while ($e = $employees->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($e['name']); ?></td>
        <td><?php echo h($e['email']); ?></td>
        <td><?php echo h($e['phone']); ?></td>
        <td><?php echo h($e['designation']); ?></td>
        <td><span class="status status-<?php echo $e['status']; ?>"><?php echo $e['status']; ?></span></td>
        <td><?php echo h(date('d M Y', strtotime($e['created_at']))); ?></td>
        <td class="actions">
          <a href="?toggle=<?php echo $e['id']; ?>" class="btn btn-sm btn-outline"><?php echo $e['status']==='active' ? 'Deactivate' : 'Activate'; ?></a>
          <a href="?delete=<?php echo $e['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Remove this employee?">Delete</a>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
