<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');
$pageTitle = 'My Bills';
$active = 'billing';
$cid = $_SESSION['user_id'];

$bills = $conn->query("SELECT b.*, e.name ename FROM billing b LEFT JOIN users e ON b.employee_id = e.id
                        WHERE b.customer_id = $cid ORDER BY b.created_at DESC");

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="panel">
  <div class="panel-head"><h3>My Invoices</h3></div>
  <table>
    <tr><th>Invoice #</th><th>Items</th><th>Billed By</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th>Date</th></tr>
    <?php if ($bills->num_rows === 0): ?>
      <tr><td colspan="8" class="empty-state">You have no invoices yet.</td></tr>
    <?php else: while ($b = $bills->fetch_assoc()):
        $due = $b['total_amount'] - $b['paid_amount']; ?>
      <tr>
        <td>#<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></td>
        <td><?php echo h($b['items']); ?></td>
        <td><?php echo h($b['ename'] ?? '—'); ?></td>
        <td>Tk. <?php echo number_format($b['total_amount'], 2); ?></td>
        <td>Tk. <?php echo number_format($b['paid_amount'], 2); ?></td>
        <td>Tk. <?php echo number_format($due, 2); ?></td>
        <td><span class="status status-<?php echo $b['payment_status']; ?>"><?php echo $b['payment_status']; ?></span></td>
        <td><?php echo h(date('d M Y', strtotime($b['created_at']))); ?></td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
