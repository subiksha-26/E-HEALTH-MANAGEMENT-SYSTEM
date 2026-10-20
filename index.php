<?php
require 'config.php';
if (!empty($_SESSION['role'])) { header('Location: ' . strtolower($_SESSION['role']) . '.php'); exit; }
include 'header.php';
?>
<h1>Welcome to E-HealthCare</h1>
<p class="sub">Choose how you want to log in.</p>
<div class="grid">
  <a class="role" href="login.php?role=Patient"><b>Patient Login</b><span>Book appointments, view reports</span></a>
  <a class="role" href="login.php?role=Doctor"><b>Doctor Login</b><span>Manage appointments, write reports</span></a>
  <a class="role" href="login.php?role=Admin"><b>Admin Login</b><span>Manage doctors and patients</span></a>
  <a class="role" href="register.php"><b>Patient Sign-up</b><span>Create a new patient account</span></a>
</div>
<?php include 'footer.php'; ?>
