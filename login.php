<?php
require 'config.php';
$role = $_GET['role'] ?? ($_POST['role'] ?? 'Patient');
if (!in_array($role, ['Patient', 'Doctor', 'Admin'])) $role = 'Patient';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $pw = $_POST['password'] ?? '';
    if ($role === 'Admin') {
        if ($id === $ADMIN_USER && $pw === $ADMIN_PASS) {
            $_SESSION = ['role' => 'Admin', 'id' => 0, 'name' => 'Admin'];
            header('Location: admin.php'); exit;
        }
        $err = 'Invalid username or password.';
    } else {
        $u = rows("SELECT * FROM Users WHERE userID=? AND userType=? AND Password=?", 'iss', [(int)$id, $role, $pw]);
        if ($u) {
            $tbl = $role === 'Patient' ? 'Patients' : 'Doctors';
            $col = $role === 'Patient' ? 'PatientID' : 'DoctorID';
            $p = rows("SELECT First_Name, Last_Name FROM $tbl WHERE $col=?", 'i', [(int)$id]);
            $name = $p ? $p[0]['First_Name'] . ' ' . $p[0]['Last_Name'] : $role . ' ' . $id;
            $_SESSION = ['role' => $role, 'id' => (int)$id, 'name' => $name];
            header('Location: ' . strtolower($role) . '.php'); exit;
        }
        $err = 'Invalid ID or password.';
    }
}
include 'header.php';
?>
<div class="narrow card">
  <h2><?= e($role) ?> Login</h2>
  <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="role" value="<?= e($role) ?>">
    <label><?= $role === 'Admin' ? 'Username' : e($role) . ' ID' ?></label>
    <input name="id" required autofocus>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
    <?php if ($role === 'Patient'): ?> <a class="btn alt" href="register.php">New? Sign up</a><?php endif; ?>
  </form>
</div>
<?php include 'footer.php'; ?>
