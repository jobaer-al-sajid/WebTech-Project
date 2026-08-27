<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('employee');
$pageTitle = 'Equipments';
$active = 'equipments';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'report') {
    $id = (int) $_POST['id']; $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE equipments SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $success = 'Equipment status reported to admin.';
}

$equipments = $conn->query("SELECT * FROM equipments ORDER BY name ASC");
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>Clinic Equipments</h3></div>
  <table>
    <tr><th>Name</th><th>Category</th><th>Quantity</th><th>Status</th><th>Report Issue</th></tr>
    <?php if ($equipments->num_rows === 0): ?>
      <tr><td colspan="5" class="empty-state">No equipment recorded.</td></tr>
    <?php else: while ($eq = $equipments->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($eq['name']); ?></td>
        <td><?php echo h($eq['category']); ?></td>
        <td><?php echo (int)$eq['quantity']; ?></td>
        <td><span class="status status-<?php echo $eq['status']; ?>"><?php echo $eq['status']; ?></span></td>
        <td>
          <form method="POST" style="display:flex; gap:6px;">
            <input type="hidden" name="action" value="report">
            <input type="hidden" name="id" value="<?php echo $eq['id']; ?>">
            <select name="status" class="form-control" style="padding:6px; font-size:.8rem;">
              <option value="working" <?php echo $eq['status']==='working'?'selected':''; ?>>Working</option>
              <option value="maintenance" <?php echo $eq['status']==='maintenance'?'selected':''; ?>>Needs Maintenance</option>
              <option value="damaged" <?php echo $eq['status']==='damaged'?'selected':''; ?>>Damaged</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline">Update</button>
          </form>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
