<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('employee');
$pageTitle = 'Employee Dashboard';
$active = 'dashboard';
$eid = $_SESSION['user_id'];

$today = date('Y-m-d');
$todayApts = $conn->query("SELECT COUNT(*) c FROM appointments WHERE employee_id = $eid AND appointment_date = '$today'")->fetch_assoc()['c'];
$pending   = $conn->query("SELECT COUNT(*) c FROM appointments WHERE employee_id = $eid AND status='pending'")->fetch_assoc()['c'];
$completed = $conn->query("SELECT COUNT(*) c FROM appointments WHERE employee_id = $eid AND status='completed'")->fetch_assoc()['c'];
$bills     = $conn->query("SELECT COUNT(*) c FROM billing WHERE employee_id = $eid")->fetch_assoc()['c'];

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="stat-cards">
  <div class="stat-card"><h4>Today's Appointments</h4><div class="num"><?php echo $todayApts; ?></div></div>
  <div class="stat-card"><h4>Pending</h4><div class="num"><?php echo $pending; ?></div></div>
  <div class="stat-card"><h4>Completed</h4><div class="num"><?php echo $completed; ?></div></div>
  <div class="stat-card"><h4>Invoices Created</h4><div class="num"><?php echo $bills; ?></div></div>
</div>

<div class="panel">
  <div class="panel-head"><h3>My Upcoming Appointments</h3><a href="appointments.php" class="btn btn-sm btn-outline">View All</a></div>
  <table>
    <tr><th>Customer</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr>
    <?php
    $rows = $conn->query("SELECT a.*, u.name cname, s.name sname FROM appointments a
                           JOIN users u ON a.customer_id = u.id
                           LEFT JOIN services s ON a.service_id = s.id
                           WHERE a.employee_id = $eid AND a.appointment_date >= '$today'
                           ORDER BY a.appointment_date ASC LIMIT 6");
    if ($rows->num_rows === 0): ?>
      <tr><td colspan="5" class="empty-state">No upcoming appointments assigned.</td></tr>
    <?php else: while ($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($r['cname']); ?></td>
        <td><?php echo h($r['sname'] ?? '—'); ?></td>
        <td><?php echo h($r['appointment_date']); ?></td>
        <td><?php echo h(substr($r['appointment_time'],0,5)); ?></td>
        <td><span class="status status-<?php echo $r['status']; ?>"><?php echo $r['status']; ?></span></td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
