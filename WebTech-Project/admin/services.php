<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Manage Services';
$active = 'services';
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name']); $description = trim($_POST['description']); $price = (float) $_POST['price'];
    if ($name === '') {
        $error = 'Service name is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO services (name, description, price) VALUES (?, ?, ?)");
        $stmt->bind_param('ssd', $name, $description, $price);
        $stmt->execute();
        $success = 'Service added.';
    }
}
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM services WHERE id = " . (int) $_GET['delete']);
    $success = 'Service removed.';
}

$services = $conn->query("SELECT * FROM services ORDER BY id DESC");
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>Add New Service</h3></div>
  <form method="POST">
    <input type="hidden" name="action" value="add">
    <div class="grid grid-3">
      <div class="form-group"><label>Service Name</label><input type="text" name="name" class="form-control" required></div>
      <div class="form-group"><label>Price (Tk.)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
      <div class="form-group"><label>Description</label><input type="text" name="description" class="form-control"></div>
    </div>
    <button type="submit" class="btn">+ Add Service</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h3>All Services</h3></div>
  <table>
    <tr><th>Name</th><th>Description</th><th>Price</th><th>Actions</th></tr>
    <?php if ($services->num_rows === 0): ?>
      <tr><td colspan="4" class="empty-state">No services added yet.</td></tr>
    <?php else: while ($s = $services->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($s['name']); ?></td>
        <td><?php echo h($s['description']); ?></td>
        <td>Tk. <?php echo number_format($s['price'], 2); ?></td>
        <td class="actions"><a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Delete this service?">Delete</a></td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
