<?php
require 'config.php';
$err = ''; $done = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = cut($_POST['first'] ?? '', 30); $l = cut($_POST['last'] ?? '', 30);
    $g = cut($_POST['gender'] ?? '', 5); $c = cut($_POST['contact'] ?? '', 11);
    $age = (int)($_POST['age'] ?? 0); $em = cut($_POST['email'] ?? '', 30);
    $bg = cut($_POST['blood'] ?? '', 5); $ad = cut($_POST['address'] ?? '', 50);
    $pw = $_POST['password'] ?? '';
    if ($f === '' || $pw === '' || $age < 1) {
        $err = 'First name, age and password are required.';
    } else {
        $id = next_id('Patients', 'PatientID');
        $ok = run("INSERT INTO Patients (PatientID,First_Name,Last_Name,Gender,ContactNumber,Age,EmailID,BloodGroup,Address) VALUES (?,?,?,?,?,?,?,?,?)",
                  'issssisss', [$id, $f, $l, $g, $c, $age, $em, $bg, $ad]);
        if ($ok) $ok = run("INSERT INTO Users (userID,userType,Password) VALUES (?,?,?)", 'iss', [$id, 'Patient', $pw]);
        if ($ok) $done = $id; else $err = 'Could not save: ' . $db->error;
    }
}
include 'header.php';
?>
<div class="narrow card" style="max-width:640px">
  <h2>Patient Sign-up</h2>
  <?php if ($done): ?>
    <div class="ok">Account created! Your Patient ID is <b><?= $done ?></b>. Use it to log in.</div>
    <a class="btn" href="login.php?role=Patient">Go to login</a>
  <?php else: ?>
  <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <form method="post">
    <div class="row2">
      <div><label>First name</label><input name="first" required></div>
      <div><label>Last name</label><input name="last"></div>
      <div><label>Gender</label><select name="gender"><option>M</option><option>F</option></select></div>
      <div><label>Age</label><input type="number" name="age" min="1" max="120" required></div>
      <div><label>Contact number</label><input name="contact" maxlength="11"></div>
      <div><label>Email</label><input type="email" name="email" maxlength="30"></div>
      <div><label>Blood group</label>
        <select name="blood"><?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b) echo "<option>$b</option>"; ?></select></div>
      <div><label>Password</label><input type="password" name="password" required></div>
    </div>
    <label>Address</label><input name="address" maxlength="50">
    <button type="submit">Create account</button>
  </form>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
