<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Billing Overview';
$active = 'billing';
$success = '';

// ---- Record additional payment ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay') {
    $id = (int) $_POST['id'];
    $extra = (float) $_POST['amount'];

    $bill = $conn->query("SELECT * FROM billing WHERE id = $id")->fetch_assoc();
    if ($bill) {
        $newPaid = min($bill['paid_amount'] + $extra, $bill['total_amount']);
        $status = $newPaid >= $bill['total_amount'] ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid');
        $stmt = $conn->prepare("UPDATE billing SET paid_amount = ?, payment_status = ? WHERE id = ?");
        $stmt->bind_param('dsi', $newPaid, $status, $id);
        $stmt->execute();
        $success = 'Payment recorded.';
    }
}

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM billing WHERE id = " . (int) $_GET['delete']);
    $success = 'Invoice deleted.';
}

$bills = $conn->query("SELECT b.*, u.name AS cname FROM billing b JOIN users u ON b.customer_id = u.id ORDER BY b.created_at DESC");
require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>All Invoices</h3></div>
  <table>
    <tr><th>Invoice #</th><th>Customer</th><th>Items</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    <?php if ($bills->num_rows === 0): ?>
      <tr><td colspan="9" class="empty-state">No invoices yet.</td></tr>
    <?php else: while ($b = $bills->fetch_assoc()):
        $due = $b['total_amount'] - $b['paid_amount']; ?>
      <tr>
        <td>#<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></td>
        <td><?php echo h($b['cname']); ?></td>
        <td><?php echo h(mb_strimwidth($b['items'], 0, 40, '…')); ?></td>
        <td>Tk. <?php echo number_format($b['total_amount'], 2); ?></td>
        <td>Tk. <?php echo number_format($b['paid_amount'], 2); ?></td>
        <td>Tk. <?php echo number_format($due, 2); ?></td>
        <td><span class="status status-<?php echo $b['payment_status']; ?>"><?php echo $b['payment_status']; ?></span></td>
        <td><?php echo h(date('d M Y', strtotime($b['created_at']))); ?></td>
        <td class="actions">
          <?php if ($due > 0): ?>
          <form method="POST" style="display:inline-flex; gap:4px;">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
            <input type="number" step="0.01" name="amount" placeholder="Amount" class="form-control" style="width:100px; padding:5px;" required>
            <button type="submit" class="btn btn-sm">Pay</button>
          </form>
          <?php endif; ?>
          <a href="?delete=<?php echo $b['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Delete this invoice?">Delete</a>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
