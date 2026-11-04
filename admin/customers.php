<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Manage Customers';
$active = 'customers';
$success = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $success = 'Customer removed.';
}
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $conn->query("UPDATE users SET status = IF(status='active','inactive','active') WHERE id = $id AND role='customer'");
    $success = 'Status updated.';
}

$customers = $conn->query("SELECT * FROM users WHERE role='customer' ORDER BY created_at DESC");
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>All Customers</h3></div>
  <table>
    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    <?php if ($customers->num_rows === 0): ?>
      <tr><td colspan="7" class="empty-state">No customers registered yet.</td></tr>
    <?php else: while ($c = $customers->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($c['name']); ?></td>
        <td><?php echo h($c['email']); ?></td>
        <td><?php echo h($c['phone']); ?></td>
        <td><?php echo h($c['address']); ?></td>
        <td><span class="status status-<?php echo $c['status']; ?>"><?php echo $c['status']; ?></span></td>
        <td><?php echo h(date('d M Y', strtotime($c['created_at']))); ?></td>
        <td class="actions">
          <a href="?toggle=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline"><?php echo $c['status']==='active' ? 'Deactivate' : 'Activate'; ?></a>
          <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Remove this customer? Their appointments and bills will also be deleted.">Delete</a>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
