<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Manage Equipments';
$active = 'equipments';
$success = ''; $error = '';

// ---- ADD ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name']); $category = trim($_POST['category']);
    $quantity = (int) $_POST['quantity']; $price = (float) $_POST['price'];
    $purchase_date = $_POST['purchase_date'] ?: null; $status = $_POST['status'];

    if ($name === '') {
        $error = 'Equipment name is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO equipments (name, category, quantity, price, purchase_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidss', $name, $category, $quantity, $price, $purchase_date, $status);
        $stmt->execute();
        $success = 'Equipment added.';
    }
}

// ---- UPDATE STATUS ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $id = (int) $_POST['id']; $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE equipments SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $success = 'Equipment status updated.';
}

// ---- DELETE ----
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM equipments WHERE id = $id");
    $success = 'Equipment removed.';
}

$equipments = $conn->query("SELECT * FROM equipments ORDER BY created_at DESC");
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>Add New Equipment</h3></div>
  <form method="POST">
    <input type="hidden" name="action" value="add">
    <div class="grid grid-3">
      <div class="form-group"><label>Equipment Name</label><input type="text" name="name" class="form-control" required></div>
      <div class="form-group"><label>Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Imaging, Chair"></div>
      <div class="form-group"><label>Quantity</label><input type="number" name="quantity" class="form-control" value="1" min="1" required></div>
      <div class="form-group"><label>Price (Tk.)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
      <div class="form-group"><label>Purchase Date</label><input type="date" name="purchase_date" class="form-control"></div>
      <div class="form-group"><label>Status</label>
        <select name="status" class="form-control">
          <option value="working">Working</option>
          <option value="maintenance">Under Maintenance</option>
          <option value="damaged">Damaged</option>
        </select>
      </div>
    </div>
    <button type="submit" class="btn">+ Add Equipment</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h3>All Equipments</h3></div>
  <table>
    <tr><th>Name</th><th>Category</th><th>Qty</th><th>Price</th><th>Purchased</th><th>Status</th><th>Actions</th></tr>
    <?php if ($equipments->num_rows === 0): ?>
      <tr><td colspan="7" class="empty-state">No equipment recorded yet.</td></tr>
    <?php else: while ($eq = $equipments->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($eq['name']); ?></td>
        <td><?php echo h($eq['category']); ?></td>
        <td><?php echo (int)$eq['quantity']; ?></td>
        <td>Tk. <?php echo number_format($eq['price'], 2); ?></td>
        <td><?php echo $eq['purchase_date'] ? h(date('d M Y', strtotime($eq['purchase_date']))) : '—'; ?></td>
        <td>
          <form method="POST" style="display:flex; gap:6px; align-items:center;">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" value="<?php echo $eq['id']; ?>">
            <select name="status" class="form-control" style="padding:6px; font-size:.8rem;" onchange="this.form.submit()">
              <option value="working" <?php echo $eq['status']==='working'?'selected':''; ?>>Working</option>
              <option value="maintenance" <?php echo $eq['status']==='maintenance'?'selected':''; ?>>Maintenance</option>
              <option value="damaged" <?php echo $eq['status']==='damaged'?'selected':''; ?>>Damaged</option>
            </select>
          </form>
        </td>
        <td class="actions">
          <a href="?delete=<?php echo $eq['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Delete this equipment record?">Delete</a>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
