<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? h($pageTitle) . ' - Dental Palace' : 'Dental Palace'; ?></title>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="brand"><span class="tooth">🦷</span> Dental Palace</div>
  <nav>
    <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
    <a href="<?php echo BASE_URL; ?>/index.php#services">Services</a>
    <a href="<?php echo BASE_URL; ?>/index.php#about">About</a>
    <a href="<?php echo BASE_URL; ?>/index.php#contact">Contact</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="<?php echo BASE_URL; ?>/<?php echo $_SESSION['role']; ?>/dashboard.php" class="btn btn-sm">Dashboard</a>
    <?php else: ?>
      <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline btn-sm">Login</a>
      <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-sm">Register</a>
    <?php endif; ?>
  </nav>
</header>
