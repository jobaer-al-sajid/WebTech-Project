<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');
$pageTitle = 'My Appointments';
$active = 'appointments';
$cid = $_SESSION['user_id'];
$success = ''; $error = '';

// ---- Book new appointment ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'book') {
    $service_id = (int) $_POST['service_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $notes = trim($_POST['notes']);

    if ($date === '' || $time === '') {
        $error = 'Please choose a date and time.';
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = 'Appointment date cannot be in the past.';
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (customer_id, service_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iisss', $cid, $service_id, $date, $time, $notes);
        $stmt->execute();
        $success = 'Appointment requested! We will confirm it shortly.';
    }
}

// ---- Cancel own appointment (only if still pending/confirmed) ----
if (isset($_GET['cancel'])) {
    $id = (int) $_GET['cancel'];
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND customer_id = ? AND status IN ('pending','confirmed')");
    $stmt->bind_param('ii', $id, $cid);
    $stmt->execute();
    $success = 'Appointment cancelled.';
}

$services = $conn->query("SELECT * FROM services ORDER BY name");
$myApts = $conn->query("SELECT a.*, s.name sname, e.name ename FROM appointments a
                         LEFT JOIN services s ON a.service_id = s.id
                         LEFT JOIN users e ON a.employee_id = e.id
                         WHERE a.customer_id = $cid
                         ORDER BY a.appointment_date DESC, a.appointment_time DESC");

require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>Book a New Appointment</h3></div>
  <form method="POST">
    <input type="hidden" name="action" value="book">
    <div class="grid grid-3">
      <div class="form-group">
        <label>Service</label>
        <select name="service_id" class="form-control" required>
          <option value="">-- Select Service --</option>
          <?php while ($s = $services->fetch_assoc()): ?>
            <option value="<?php echo $s['id']; ?>"><?php echo h($s['name']); ?> — Tk. <?php echo number_format($s['price'],2); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Date</label>
        <input type="date" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
      </div>
      <div class="form-group">
        <label>Time</label>
        <input type="time" name="appointment_time" class="form-control" required>
      </div>
    </div>
    <div class="form-group">
      <label>Notes (optional)</label>
      <input type="text" name="notes" class="form-control" placeholder="Describe your concern, e.g. tooth pain, sensitivity...">
    </div>
    <button type="submit" class="btn">Request Appointment</button>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h3>My Appointment History</h3></div>
  <table>
    <tr><th>Service</th><th>Date</th><th>Time</th><th>Dentist</th><th>Notes</th><th>Status</th><th>Actions</th></tr>
    <?php if ($myApts->num_rows === 0): ?>
      <tr><td colspan="7" class="empty-state">You have no appointments yet.</td></tr>
    <?php else: while ($a = $myApts->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($a['sname'] ?? '—'); ?></td>
        <td><?php echo h($a['appointment_date']); ?></td>
        <td><?php echo h(substr($a['appointment_time'],0,5)); ?></td>
        <td><?php echo h($a['ename'] ?? 'Not assigned'); ?></td>
        <td><?php echo h($a['notes']); ?></td>
        <td><span class="status status-<?php echo $a['status']; ?>"><?php echo $a['status']; ?></span></td>
        <td class="actions">
          <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
            <a href="?cancel=<?php echo $a['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Cancel this appointment?">Cancel</a>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
