<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');
$pageTitle = 'My Dashboard';
$active = 'dashboard';
$cid = $_SESSION['user_id'];

$upcoming = $conn->query("SELECT COUNT(*) c FROM appointments WHERE customer_id=$cid AND appointment_date >= CURDATE() AND status != 'cancelled'")->fetch_assoc()['c'];
$completed= $conn->query("SELECT COUNT(*) c FROM appointments WHERE customer_id=$cid AND status='completed'")->fetch_assoc()['c'];
$dueTotal = $conn->query("SELECT COALESCE(SUM(total_amount-paid_amount),0) s FROM billing WHERE customer_id=$cid")->fetch_assoc()['s'];
$spent    = $conn->query("SELECT COALESCE(SUM(paid_amount),0) s FROM billing WHERE customer_id=$cid")->fetch_assoc()['s'];

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="stat-cards">
  <div class="stat-card"><h4>Upcoming Appointments</h4><div class="num"><?php echo $upcoming; ?></div></div>
  <div class="stat-card"><h4>Completed Visits</h4><div class="num"><?php echo $completed; ?></div></div>
  <div class="stat-card"><h4>Total Spent</h4><div class="num">Tk. <?php echo number_format($spent,2); ?></div></div>
  <div class="stat-card"><h4>Amount Due</h4><div class="num">Tk. <?php echo number_format($dueTotal,2); ?></div></div>
</div>

<div class="panel">
  <div class="panel-head">
    <h3>My Upcoming Appointments</h3>
    <a href="appointments.php" class="btn btn-sm">+ Book Appointment</a>
  </div>
  <table>
    <tr><th>Service</th><th>Date</th><th>Time</th><th>Dentist</th><th>Status</th></tr>
    <?php
    $rows = $conn->query("SELECT a.*, s.name sname, e.name ename FROM appointments a
                           LEFT JOIN services s ON a.service_id = s.id
                           LEFT JOIN users e ON a.employee_id = e.id
                           WHERE a.customer_id = $cid AND a.appointment_date >= CURDATE()
                           ORDER BY a.appointment_date ASC LIMIT 6");
    if ($rows->num_rows === 0): ?>
      <tr><td colspan="5" class="empty-state">No upcoming appointments. <a href="appointments.php">Book one now</a>.</td></tr>
    <?php else: while ($r = $rows->fetch_assoc()): ?>
      <tr>
        <td><?php echo h($r['sname'] ?? '—'); ?></td>
        <td><?php echo h($r['appointment_date']); ?></td>
        <td><?php echo h(substr($r['appointment_time'],0,5)); ?></td>
        <td><?php echo h($r['ename'] ?? 'Not assigned yet'); ?></td>
        <td><span class="status status-<?php echo $r['status']; ?>"><?php echo $r['status']; ?></span></td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
