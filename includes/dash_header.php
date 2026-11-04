<?php
/* Expects $active (string) and $pageTitle set by the including page,
   and require_role() already called. */
$u = current_user();
$role = $u['role'];

$menus = [
    'admin' => [
        'dashboard'    => ['🏠 Dashboard', 'dashboard.php'],
        'employees'    => ['🧑‍⚕️ Employees', 'employees.php'],
        'customers'    => ['🧑 Customers', 'customers.php'],
        'appointments' => ['📅 Appointments', 'appointments.php'],
        'equipments'   => ['🛠️ Equipments', 'equipments.php'],
        'billing'      => ['💳 Billing', 'billing.php'],
        'services'     => ['🦷 Services', 'services.php'],
    ],
    'employee' => [
        'dashboard'    => ['🏠 Dashboard', 'dashboard.php'],
        'appointments' => ['📅 Appointments', 'appointments.php'],
        'billing'      => ['💳 Billing', 'billing.php'],
        'equipments'   => ['🛠️ Equipments', 'equipments.php'],
    ],
    'customer' => [
        'dashboard'    => ['🏠 Dashboard', 'dashboard.php'],
        'appointments' => ['📅 My Appointments', 'appointments.php'],
        'billing'      => ['💳 My Bills', 'billing.php'],
        'profile'      => ['👤 My Profile', 'profile.php'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? h($pageTitle) . ' - Dental Palace' : 'Dental Palace'; ?></title>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<div class="dash">
  <aside class="sidebar">
    <div class="brand">🦷 Dental Palace</div>
    <?php foreach ($menus[$role] as $key => $item): ?>
      <a href="<?php echo $item[1]; ?>" class="<?php echo ($active ?? '') === $key ? 'active' : ''; ?>"><?php echo $item[0]; ?></a>
    <?php endforeach; ?>
    <a href="<?php echo BASE_URL; ?>/logout.php">🚪 Logout</a>
  </aside>
  <main class="main">
    <div class="topbar">
      <h2><?php echo h($pageTitle ?? ''); ?></h2>
      <div class="user">
        <?php echo h($u['name']); ?>
        <span class="badge badge-<?php echo $role; ?>"><?php echo ucfirst($role); ?></span>
      </div>
    </div>
