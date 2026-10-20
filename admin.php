<?php
require 'config.php';
need('Admin');
$msg = ''; $err = '';
$tab = $_GET['tab'] ?? 'appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'adddoctor') {
    $f = cut($_POST['first'] ?? '', 30); $pw = $_POST['password'] ?? '';
    if ($f === '' || $pw === '') { $err = 'First name and password are required.'; }
    else {
        $id = next_id('Doctors', 'DoctorID');
        $ok = run("INSERT INTO Doctors (DoctorID,First_Name,Last_Name,Gender,ContactNumber,Age,Entry_Charge,Qualification,Doctor_Type,Email_Id) VALUES (?,?,?,?,?,?,?,?,?,?)",
            'issssiisss', [$id, $f, cut($_POST['last'] ?? '', 30), cut($_POST['gender'] ?? '', 10), cut($_POST['contact'] ?? '', 11),
            (int)($_POST['age'] ?? 0), (int)($_POST['fee'] ?? 0), cut($_POST['qual'] ?? '', 50), cut($_POST['type'] ?? '', 50), cut($_POST['email'] ?? '', 30)]);
        if ($ok) $ok = run("INSERT INTO Users (userID,userType,Password) VALUES (?,?,?)", 'iss', [$id, 'Doctor', $pw]);
        $ok ? $msg = "Doctor added. Doctor ID is $id." : $err = 'Could not add doctor: ' . $db->error;
        $tab = 'doctors';
    }
}

$counts = [
    'Patients' => rows("SELECT COUNT(*) c FROM Patients")[0]['c'],
    'Doctors' => rows("SELECT COUNT(*) c FROM Doctors")[0]['c'],
    'Appointments' => rows("SELECT COUNT(*) c FROM Appointments")[0]['c'],
    'Reports' => rows("SELECT COUNT(*) c FROM Reports")[0]['c'],
];
include 'header.php';
?>
<h1>Admin Dashboard</h1>
<p class="sub">Overview of the system</p>
<?php if ($msg): ?><div class="ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>

<div class="grid" style="margin-bottom:20px">
<?php foreach ($counts as $k => $v): ?>
  <div class="card" style="margin:0;text-align:center"><div style="font-size:30px;font-weight:700"><?= $v ?></div><div class="muted"><?= $k ?></div></div>
<?php endforeach; ?>
</div>

<div class="tabs">
  <?php foreach (['appointments' => 'Appointments', 'patients' => 'Patients', 'doctors' => 'Doctors', 'add' => 'Add Doctor', 'feedback' => 'Feedback'] as $k => $v): ?>
    <a class="<?= $tab === $k ? 'on' : '' ?>" href="?tab=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<?php
function table($head, $data, $keys) {
    if (!$data) { echo '<p class="muted">No records.</p>'; return; }
    echo '<table><tr>'; foreach ($head as $h) echo '<th>' . e($h) . '</th>'; echo '</tr>';
    foreach ($data as $r) { echo '<tr>'; foreach ($keys as $k) echo '<td>' . e($r[$k] ?? '') . '</td>'; echo '</tr>'; }
    echo '</table>';
}
if ($tab === 'patients') {
    echo '<div class="card wide"><h2>All Patients</h2>';
    table(['ID', 'First', 'Last', 'Gender', 'Contact', 'Age', 'Email', 'Blood', 'Address'],
        rows("SELECT * FROM Patients ORDER BY PatientID"),
        ['PatientID', 'First_Name', 'Last_Name', 'Gender', 'ContactNumber', 'Age', 'EmailID', 'BloodGroup', 'Address']);
    echo '</div>';
} elseif ($tab === 'doctors') {
    echo '<div class="card wide"><h2>All Doctors</h2>';
    table(['ID', 'First', 'Last', 'Gender', 'Contact', 'Age', 'Fee', 'Qualification', 'Type', 'Email'],
        rows("SELECT * FROM Doctors ORDER BY DoctorID"),
        ['DoctorID', 'First_Name', 'Last_Name', 'Gender', 'ContactNumber', 'Age', 'Entry_Charge', 'Qualification', 'Doctor_Type', 'Email_Id']);
    echo '</div>';
} elseif ($tab === 'feedback') {
    echo '<div class="card wide"><h2>Patient Feedback</h2>';
    table(['Patient', 'Rating', 'Doctor nature', 'Location', 'Comment'],
        rows("SELECT * FROM feedback ORDER BY PatientID"),
        ['PatientID', 'points', 'Doc_Nature', 'Location', 'PatientComment']);
    echo '</div>';
} elseif ($tab === 'add') { ?>
<div class="card" style="max-width:700px"><h2>Add a Doctor</h2>
  <form method="post"><input type="hidden" name="action" value="adddoctor">
    <div class="row2">
      <div><label>First name</label><input name="first" required></div>
      <div><label>Last name</label><input name="last"></div>
      <div><label>Gender</label><select name="gender"><option>M</option><option>F</option></select></div>
      <div><label>Age</label><input type="number" name="age" min="20" max="90"></div>
      <div><label>Contact</label><input name="contact" maxlength="11"></div>
      <div><label>Email</label><input name="email" maxlength="30"></div>
      <div><label>Qualification</label><input name="qual" placeholder="MBBS, MD..."></div>
      <div><label>Specialty</label><input name="type" placeholder="Heart, Eye, Ear..."></div>
      <div><label>Entry fee (Rs.)</label><input type="number" name="fee" min="0"></div>
      <div><label>Login password</label><input type="password" name="password" required></div>
    </div>
    <button type="submit">Add doctor</button>
  </form>
</div>
<?php } else {
    echo '<div class="card wide"><h2>All Appointments</h2>';
    table(['#', 'Problem', 'Patient ID', 'Doctor', 'Type', 'Fees', 'Payment', 'Status'],
        rows("SELECT * FROM Appointments ORDER BY AppointmentID DESC"),
        ['AppointmentID', 'Problem', 'PatientId', 'DoctorName', 'DoctorType', 'DoctorFees', 'PaymentStatus', 'Appointment_Status']);
    echo '</div>';
}
include 'footer.php';
