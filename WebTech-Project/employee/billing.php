<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('employee');
$pageTitle = 'Billing';
$active = 'billing';
$eid = $_SESSION['user_id'];
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_bill') {
    $customer_id = (int) $_POST['customer_id'];
    $appointment_id = !empty($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : null;
    $item_names = $_POST['item_name'] ?? [];
    $item_qtys  = $_POST['item_qty'] ?? [];
    $item_prices= $_POST['item_price'] ?? [];
    $total = (float) $_POST['total_amount'];
    $paid  = (float) $_POST['paid_amount'];

    if ($customer_id <= 0 || empty($item_names)) {
        $error = 'Please select a customer and add at least one item.';
    } else {
        $lines = [];
        foreach ($item_names as $i => $name) {
            if (trim($name) === '') continue;
            $qty = (float) ($item_qtys[$i] ?? 0);
            $price = (float) ($item_prices[$i] ?? 0);
            $lines[] = trim($name) . " (x{$qty} @ Tk." . number_format($price,2) . ")";
        }
        $items = implode(', ', $lines);
        $paid = min($paid, $total);
        $status = $paid >= $total && $total > 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

        $stmt = $conn->prepare("INSERT INTO billing (customer_id, appointment_id, employee_id, items, total_amount, paid_amount, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiisdds', $customer_id, $appointment_id, $eid, $items, $total, $paid, $status);
        $stmt->execute();
        $success = 'Invoice created successfully.';
    }
}

$customers = $conn->query("SELECT id, name, phone FROM users WHERE role='customer' AND status='active' ORDER BY name");
$myBills = $conn->query("SELECT b.*, u.name cname FROM billing b JOIN users u ON b.customer_id = u.id WHERE b.employee_id = $eid ORDER BY b.created_at DESC");

require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>Create New Invoice</h3></div>
  <form method="POST" onsubmit="calcBillTotal();">
    <input type="hidden" name="action" value="create_bill">
    <div class="grid grid-3">
      <div class="form-group">
        <label>Customer</label>
        <select name="customer_id" class="form-control" required>
          <option value="">-- Select Customer --</option>
          <?php while ($c = $customers->fetch_assoc()): ?>
            <option value="<?php echo $c['id']; ?>"><?php echo h($c['name']); ?> (<?php echo h($c['phone']); ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Related Appointment ID (optional)</label>
        <input type="number" name="appointment_id" class="form-control" placeholder="e.g. 12">
      </div>
    </div>

    <h4 style="margin-top:20px;">Bill Items</h4>
    <div id="bill-items-wrap">
      <div class="bill-item-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
        <input type="text" name="item_name[]" class="form-control" placeholder="Item / Service name" style="flex:2;" required>
        <input type="number" name="item_qty[]" class="form-control item-qty" placeholder="Qty" value="1" min="1" style="flex:1;" oninput="calcBillTotal()">
        <input type="number" name="item_price[]" class="form-control item-price" placeholder="Unit Price" step="0.01" style="flex:1;" oninput="calcBillTotal()">
        <span style="flex:1;">Tk. <span class="item-line-total">0.00</span></span>
        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove(); calcBillTotal();">✕</button>
      </div>
    </div>
    <button type="button" class="btn btn-outline btn-sm" onclick="addBillRow()">+ Add Item</button>

    <div class="grid grid-3" style="margin-top:20px;">
      <div class="form-group">
        <label>Total Amount</label>
        <input type="text" id="bill-total-amount" name="total_amount" class="form-control" value="0.00" readonly>
      </div>
      <div class="form-group">
        <label>Paid Amount</label>
        <input type="number" step="0.01" id="bill-paid-amount" name="paid_amount" class="form-control" value="0" oninput="updateDueAmount()">
      </div>
      <div class="form-group">
        <label>Due Amount</label>
        <div class="form-control" style="background:#f4f9f8;">Tk. <span id="bill-due-display">0.00</span></div>
      </div>
    </div>
    <p>Grand Total: Tk. <b id="bill-total-display">0.00</b></p>

    <button type="submit" class="btn">Generate Invoice</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h3>Invoices I Created</h3></div>
  <table>
    <tr><th>Invoice #</th><th>Customer</th><th>Items</th><th>Total</th><th>Paid</th><th>Status</th><th>Date</th></tr>
    <?php if ($myBills->num_rows === 0): ?>
      <tr><td colspan="7" class="empty-state">You haven't created any invoices yet.</td></tr>
    <?php else: while ($b = $myBills->fetch_assoc()): ?>
      <tr>
        <td>#<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></td>
        <td><?php echo h($b['cname']); ?></td>
        <td><?php echo h(mb_strimwidth($b['items'], 0, 40, '…')); ?></td>
        <td>Tk. <?php echo number_format($b['total_amount'], 2); ?></td>
        <td>Tk. <?php echo number_format($b['paid_amount'], 2); ?></td>
        <td><span class="status status-<?php echo $b['payment_status']; ?>"><?php echo $b['payment_status']; ?></span></td>
        <td><?php echo h(date('d M Y', strtotime($b['created_at']))); ?></td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
