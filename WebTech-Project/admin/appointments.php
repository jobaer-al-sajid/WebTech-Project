<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Manage Appointments';
$active = 'appointments';
$success = '';

// ---- Assign employee / update status ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int) $_POST['id'];
    $employee_id = $_POST['employee_id'] !== '' ? (int) $_POST['employee_id'] : null;
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE appointments SET employee_id = ?, status = ? WHERE id = ?");
    $stmt->bind_param('isi', $employee_id, $status, $id);
    $stmt->execute();
    $success = 'Appointment updated.';
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $success = 'Appointment deleted.';
}

$employees = $conn->query("SELECT id, name FROM users WHERE role='employee' AND status='active'");
$employeeList = [];
while ($e = $employees->fetch_assoc()) $employeeList[] = $e;

$appointments = $conn->query("SELECT a.*, u.name AS cname, u.phone AS cphone, s.name AS sname, emp.name AS ename
                               FROM appointments a
                               JOIN users u ON a.customer_id = u.id
                               LEFT JOIN services s ON a.service_id = s.id
                               LEFT JOIN users emp ON a.employee_id = emp.id
                               ORDER BY a.appointment_date DESC, a.appointment_time DESC");

require_once __DIR__ . '/../includes/dash_header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h3>All Appointments</h3></div>
  <table>
    <tr><th>Customer</th><th>Phone</th><th>Service</th><th>Date</th><th>Time</th><th>Assigned To</th><th>Status</th><th>Actions</th></tr>
    <?php if ($appointments->num_rows === 0): ?>
      <tr><td colspan="8" class="empty-state">No appointments found.</td></tr>
    <?php else: while ($a = $appointments->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($a['cname']); ?></td>
        <td><?php echo h($a['cphone']); ?></td>
        <td><?php echo h($a['sname'] ?? '—'); ?></td>
        <td><?php echo h($a['appointment_date']); ?></td>
        <td><?php echo h(substr($a['appointment_time'],0,5)); ?></td>
        <td>
          <form method="POST" style="display:flex; gap:6px;">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
            <select name="employee_id" class="form-control" style="padding:6px; font-size:.8rem;">
              <option value="">Unassigned</option>
              <?php foreach ($employeeList as $emp): ?>
                <option value="<?php echo $emp['id']; ?>" <?php echo $a['employee_id']==$emp['id']?'selected':''; ?>><?php echo h($emp['name']); ?></option>
              <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="status" class="form-control" style="padding:6px; font-size:.8rem;">
              <?php foreach (['pending','confirmed','completed','cancelled'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $a['status']===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
              <?php endforeach; ?>
            </select>
        </td>
        <td class="actions">
            <button type="submit" class="btn btn-sm">Save</button>
          </form>
          <a href="?delete=<?php echo $a['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Delete this appointment?">Delete</a>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
