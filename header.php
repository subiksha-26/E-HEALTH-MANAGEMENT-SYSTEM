<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>E-HealthCare Management System</title>
<link rel="stylesheet" href="style.css">
</head><body>
<header><div class="wrap bar">
  <a class="brand" href="index.php">&#10010; E-HealthCare</a>
  <nav>
  <?php if (!empty($_SESSION['role'])): ?>
    <span>Hi, <?= e($_SESSION['name']) ?> (<?= e($_SESSION['role']) ?>)</span>
    <a href="<?= strtolower($_SESSION['role']) ?>.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  <?php else: ?>
    <a href="index.php">Home</a><a href="register.php">Patient Sign-up</a>
  <?php endif; ?>
  </nav>
</div></header>
<main><div class="wrap">
