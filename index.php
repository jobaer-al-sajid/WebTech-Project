<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Home';

// Pull services to show on the homepage
$services = $conn->query("SELECT * FROM services ORDER BY id ASC LIMIT 6");

require_once __DIR__ . '/includes/site_header.php';
?>

<section class="hero">
  <div class="content">
    <h1 style="color:#fff;">Your Smile, Our Priority</h1>
    <p>Dental Palace is a full-service dental clinic offering modern, painless, and affordable dental care for the whole family — from routine checkups to advanced cosmetic treatments.</p>
    <div style="margin-top:26px; display:flex; gap:14px; flex-wrap:wrap;">
      <a href="<?php echo BASE_URL; ?>/register.php" class="btn" style="background:#fff; color:var(--teal-900);">Book an Appointment</a>
      <a href="#services" class="btn btn-outline" style="border-color:#fff; color:#fff;">Our Services</a>
    </div>
  </div>
  <div class="art">🦷</div>
</section>

<section class="section" id="services">
  <div class="section-title">
    <h2>Our Services</h2>
    <p>Comprehensive dental care under one roof</p>
  </div>
  <div class="grid grid-3">
    <?php while ($s = $services->fetch_assoc()): ?>
      <div class="card">
        <div class="icon">🦷</div>
        <h3><?php echo h($s['name']); ?></h3>
        <p><?php echo h($s['description']); ?></p>
        <div class="price">Tk. <?php echo number_format($s['price'], 2); ?></div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

<section class="section" id="about" style="background:var(--teal-100); border-radius:16px;">
  <div class="section-title">
    <h2>Why Choose Dental Palace?</h2>
  </div>
  <div class="grid grid-4">
    <div class="card"><div class="icon">👨‍⚕️</div><h3>Expert Dentists</h3><p>Experienced and certified dental professionals.</p></div>
    <div class="card"><div class="icon">🛠️</div><h3>Modern Equipment</h3><p>Latest technology for accurate, painless treatment.</p></div>
    <div class="card"><div class="icon">💳</div><h3>Transparent Billing</h3><p>Clear, itemized bills with no hidden charges.</p></div>
    <div class="card"><div class="icon">📅</div><h3>Easy Appointments</h3><p>Book and track appointments online anytime.</p></div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>
