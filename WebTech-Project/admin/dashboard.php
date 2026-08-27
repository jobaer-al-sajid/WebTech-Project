<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pageTitle = 'Admin Dashboard';
$active = 'dashboard';

$employees   = $conn->query("SELECT COUNT(*) c FROM users WHERE role='employee'")->fetch_assoc()['c'];
$customers   = $conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$appointments= $conn->query("SELECT COUNT(*) c FROM appointments")->fetch_assoc()['c'];
$pendingApts = $conn->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$equipments  = $conn->query("SELECT COUNT(*) c FROM equipments")->fetch_assoc()['c'];
$revenue     = $conn->query("SELECT COALESCE(SUM(paid_amount),0) s FROM billing")->fetch_assoc()['s'];
$due         = $conn->query("SELECT COALESCE(SUM(total_amount - paid_amount),0) s FROM billing")->fetch_assoc()['s'];

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="stat-cards">
  <div class="stat-card"><h4>Employees</h4><div class="num"><?php echo $employees; ?></div></div>
  <div class="stat-card"><h4>Customers</h4><div class="num"><?php echo $customers; ?></div></div>
  <div class="stat-card"><h4>Total Appointments</h4><div class="num"><?php echo $appointments; ?></div></div>
  <div class="stat-card"><h4>Pending Appointments</h4><div class="num"><?php echo $pendingApts; ?></div></div>
  <div class="stat-card"><h4>Equipments</h4><div class="num"><?php echo $equipments; ?></div></div>
  <div class="stat-card"><h4>Revenue Collected</h4><div class="num">Tk. <?php echo number_format($revenue, 2); ?></div></div>
  <div class="stat-card"><h4>Due Amount</h4><div class="num">Tk. <?php echo number_format($due, 2); ?></div></div>
</div>

<div class="panel">
  <div class="panel-head"><h3>Recent Appointments</h3><a href="appointments.php" class="btn btn-sm btn-outline">View All</a></div>
  <table>
    <tr><th>Customer</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr>
    <?php
    $recent = $conn->query("SELECT a.*, u.name AS cname, s.name AS sname FROM appointments a
                             JOIN users u ON a.customer_id = u.id
                             LEFT JOIN services s ON a.service_id = s.id
                             ORDER BY a.created_at DESC LIMIT 6");
    if ($recent->num_rows === 0): ?>
      <tr><td colspan="5" class="empty-state">No appointments yet.</td></tr>
    <?php else: while ($r = $recent->fetch_assoc()): ?>
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
