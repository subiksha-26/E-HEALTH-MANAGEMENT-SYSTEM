<?php
require 'config.php';
need('Doctor');
$did = $_SESSION['id'];
$msg = ''; $err = '';
$tab = $_GET['tab'] ?? 'appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    $aid = (int)($_POST['id'] ?? 0);
    if ($a === 'status') {
        $s = $_POST['status'] ?? '';
        if (in_array($s, ['Confirmed', 'Completed', 'Cancelled'])) {
            run("UPDATE Appointments SET Appointment_Status=? WHERE AppointmentID=? AND DoctorID=?", 'sii', [$s, $aid, $did]);
            $msg = "Appointment #$aid marked $s.";
        }
    } elseif ($a === 'report') {
        $ap = rows("SELECT * FROM Appointments WHERE AppointmentID=? AND DoctorID=?", 'ii', [$aid, $did]);
        if (!$ap) { $err = 'Appointment not found.'; }
        else {
            $rid = next_id('Reports', 'ReportID');
            $ok = run("INSERT INTO Reports (ReportID,appointmentID,patientID,DoctorID,MedicinePrescribed,DoctorComment) VALUES (?,?,?,?,?,?)",
                'iiiiss', [$rid, $aid, (int)$ap[0]['PatientId'], $did, cut($_POST['medicine'] ?? '', 200), cut($_POST['comment'] ?? '', 200)]);
            if ($ok) {
                run("UPDATE Appointments SET Appointment_Status='Completed' WHERE AppointmentID=?", 'i', [$aid]);
                $msg = "Report #$rid saved and appointment completed.";
            } else $err = 'Could not save report: ' . $db->error;
        }
        $tab = 'appointments';
    }
}

$appts = rows("SELECT a.*, p.First_Name, p.Last_Name, p.Age, p.BloodGroup FROM Appointments a LEFT JOIN Patients p ON p.PatientID=a.PatientId WHERE a.DoctorID=? ORDER BY a.AppointmentID DESC", 'i', [$did]);
$reports = rows("SELECT r.*, a.Problem, p.First_Name, p.Last_Name FROM Reports r JOIN Appointments a ON a.AppointmentID=r.appointmentID LEFT JOIN Patients p ON p.PatientID=r.patientID WHERE r.DoctorID=? ORDER BY r.ReportID DESC", 'i', [$did]);
$write = (int)($_GET['write'] ?? 0);
function tagc($s) { return in_array($s, ['Paid', 'Completed', 'Confirmed']) ? 'g' : ($s === 'Cancelled' ? 'r' : 'y'); }
include 'header.php';
?>
<h1>Doctor Dashboard</h1>
<p class="sub">Doctor ID: <?= $did ?></p>
<?php if ($msg): ?><div class="ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>

<div class="tabs">
  <a class="<?= $tab === 'appointments' ? 'on' : '' ?>" href="?tab=appointments">Appointments</a>
  <a class="<?= $tab === 'reports' ? 'on' : '' ?>" href="?tab=reports">Reports I wrote</a>
</div>

<?php if ($write): ?>
<div class="card" style="max-width:640px"><h2>Write report for appointment #<?= $write ?></h2>
  <form method="post"><input type="hidden" name="action" value="report"><input type="hidden" name="id" value="<?= $write ?>">
    <label>Medicine prescribed</label><textarea name="medicine" rows="3" maxlength="200" required></textarea>
    <label>Doctor comment</label><textarea name="comment" rows="3" maxlength="200"></textarea>
    <button type="submit">Save report</button> <a class="btn alt" href="doctor.php">Cancel</a>
  </form>
</div>
<?php endif; ?>

<?php if ($tab === 'reports'): ?>
<div class="card wide"><h2>Reports I wrote</h2>
  <?php if (!$reports): ?><p class="muted">No reports yet.</p><?php else: ?>
  <table><tr><th>#</th><th>Patient</th><th>Problem</th><th>Medicine</th><th>Comment</th></tr>
  <?php foreach ($reports as $r): ?>
    <tr><td><?= $r['ReportID'] ?></td><td><?= e($r['First_Name'] . ' ' . $r['Last_Name']) ?></td><td><?= e($r['Problem']) ?></td>
    <td><?= e($r['MedicinePrescribed']) ?></td><td><?= e($r['DoctorComment']) ?></td></tr>
  <?php endforeach; ?></table><?php endif; ?>
</div>
<?php else: ?>
<div class="card wide"><h2>My Appointments</h2>
  <?php if (!$appts): ?><p class="muted">No appointments booked with you yet.</p><?php else: ?>
  <table><tr><th>#</th><th>Patient</th><th>Age</th><th>Problem</th><th>Payment</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($appts as $a): ?>
    <tr><td><?= $a['AppointmentID'] ?></td><td><?= e($a['First_Name'] . ' ' . $a['Last_Name']) ?></td><td><?= e($a['Age']) ?></td>
    <td><?= e($a['Problem']) ?></td>
    <td><span class="tag <?= tagc($a['PaymentStatus']) ?>"><?= e($a['PaymentStatus']) ?></span></td>
    <td><span class="tag <?= tagc($a['Appointment_Status']) ?>"><?= e($a['Appointment_Status']) ?></span></td>
    <td>
      <?php if (!in_array($a['Appointment_Status'], ['Completed', 'Cancelled'])): ?>
      <form method="post" style="display:inline;margin:0"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $a['AppointmentID'] ?>">
        <button class="btn sm" name="status" value="Confirmed" type="submit">Confirm</button>
        <button class="btn sm alt" name="status" value="Cancelled" type="submit">Cancel</button></form>
      <a class="btn sm" href="?write=<?= $a['AppointmentID'] ?>">Write report</a>
      <?php endif; ?>
    </td></tr>
  <?php endforeach; ?></table><?php endif; ?>
</div>
<?php endif; ?>
<?php include 'footer.php'; ?>
