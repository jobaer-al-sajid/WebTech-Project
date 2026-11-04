<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('employee');
$pageTitle = 'My Appointments';
$active = 'appointments';
$eid = $_SESSION['user_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id = (int) $_POST['id']; $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND employee_id = ?");
    $stmt->bind_param('sii', $status, $id, $eid);
    $stmt->execute();
    $success = 'Appointment status updated.';
}

$appointments = $conn->query("SELECT a.*, u.name cname, u.phone cphone, s.name sname FROM appointments a
                               JOIN users u ON a.customer_id = u.id
                               LEFT JOIN services s ON a.service_id = s.id
                               WHERE a.employee_id = $eid
                               ORDER BY a.appointment_date DESC, a.appointment_time DESC");

require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>Appointments Assigned To Me</h3></div>
  <table>
    <tr><th>Customer</th><th>Phone</th><th>Service</th><th>Date</th><th>Time</th><th>Notes</th><th>Status</th><th>Actions</th></tr>
    <?php if ($appointments->num_rows === 0): ?>
      <tr><td colspan="8" class="empty-state">No appointments assigned to you yet.</td></tr>
    <?php else: while ($a = $appointments->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($a['cname']); ?></td>
        <td><?php echo h($a['cphone']); ?></td>
        <td><?php echo h($a['sname'] ?? '—'); ?></td>
        <td><?php echo h($a['appointment_date']); ?></td>
        <td><?php echo h(substr($a['appointment_time'],0,5)); ?></td>
        <td><?php echo h($a['notes']); ?></td>
        <td><span class="status status-<?php echo $a['status']; ?>"><?php echo $a['status']; ?></span></td>
        <td class="actions">
          <form method="POST" style="display:flex; gap:6px;">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
            <select name="status" class="form-control" style="padding:6px; font-size:.8rem;">
              <?php foreach (['pending','confirmed','completed','cancelled'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $a['status']===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm">Save</button>
          </form>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
