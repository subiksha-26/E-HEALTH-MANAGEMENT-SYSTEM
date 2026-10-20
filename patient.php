<?php
require 'config.php';
need('Patient');
$pid = $_SESSION['id'];
$msg = ''; $err = '';
$tab = $_GET['tab'] ?? 'appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'book') {
        $did = (int)($_POST['doctor'] ?? 0);
        $prob = cut($_POST['problem'] ?? '', 50);
        $d = rows("SELECT * FROM Doctors WHERE DoctorID=?", 'i', [$did]);
        if (!$d || $prob === '') {
            $err = 'Please choose a doctor and describe your problem.';
        } else {
            $d = $d[0];
            $id = next_id('Appointments', 'AppointmentID');
            $ok = run("INSERT INTO Appointments (AppointmentID,Problem,PatientId,DoctorName,DoctorID,DoctorType,Qualification,DoctorFees,PaymentStatus,Appointment_Status) VALUES (?,?,?,?,?,?,?,?,?,?)",
                'isisississ', [$id, $prob, $pid, cut($d['First_Name'] . ' ' . $d['Last_Name'], 20), $did,
                cut($d['Doctor_Type'], 20), cut($d['Qualification'], 20), (int)$d['Entry_Charge'], 'Pending', 'Pending']);
            $ok ? $msg = "Appointment #$id booked." : $err = 'Could not book: ' . $db->error;
        }
    } elseif ($a === 'pay') {
        run("UPDATE Appointments SET PaymentStatus='Paid' WHERE AppointmentID=? AND PatientId=?", 'ii', [(int)$_POST['id'], $pid]);
        $msg = 'Payment recorded.';
    } elseif ($a === 'feedback') {
        $pts = max(1, min(5, (int)($_POST['points'] ?? 5)));
        $ok = run("INSERT INTO feedback (PatientID,points,Doc_Nature,Location,PatientComment) VALUES (?,?,?,?,?)",
            'iisss', [$pid, $pts, cut($_POST['nature'] ?? '', 200), cut($_POST['location'] ?? '', 200), cut($_POST['comment'] ?? '', 1000)]);
        $ok ? $msg = 'Thank you for your feedback!' : $err = 'Could not save feedback.';
        $tab = 'feedback';
    }
}

$doctors = rows("SELECT * FROM Doctors ORDER BY Doctor_Type, First_Name");
$appts = rows("SELECT * FROM Appointments WHERE PatientId=? ORDER BY AppointmentID DESC", 'i', [$pid]);
$reports = rows("SELECT r.*, a.Problem, a.DoctorName FROM Reports r JOIN Appointments a ON a.AppointmentID=r.appointmentID WHERE r.patientID=? ORDER BY r.ReportID DESC", 'i', [$pid]);
$me = rows("SELECT * FROM Patients WHERE PatientID=?", 'i', [$pid]);
$me = $me ? $me[0] : [];

function tagc($s) { return in_array($s, ['Paid', 'Completed', 'Confirmed']) ? 'g' : ($s === 'Cancelled' ? 'r' : 'y'); }
include 'header.php';
?>
<h1>Patient Dashboard</h1>
<p class="sub">Patient ID: <?= $pid ?></p>
<?php if ($msg): ?><div class="ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>

<div class="tabs">
  <?php foreach (['appointments' => 'My Appointments', 'book' => 'Book Appointment', 'reports' => 'My Reports', 'feedback' => 'Feedback', 'profile' => 'My Profile'] as $k => $v): ?>
    <a class="<?= $tab === $k ? 'on' : '' ?>" href="?tab=<?= $k ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<?php if ($tab === 'book'): ?>
<div class="card" style="max-width:640px">
  <h2>Book an Appointment</h2>
  <form method="post"><input type="hidden" name="action" value="book">
    <label>Doctor</label>
    <select name="doctor" required>
      <?php foreach ($doctors as $d): ?>
        <option value="<?= $d['DoctorID'] ?>"><?= e($d['First_Name'] . ' ' . $d['Last_Name']) ?> - <?= e($d['Doctor_Type']) ?> (<?= e($d['Qualification']) ?>) - Rs. <?= (int)$d['Entry_Charge'] ?></option>
      <?php endforeach; ?>
    </select>
    <label>Problem</label>
    <input name="problem" maxlength="50" required placeholder="e.g. chest pain, eye irritation">
    <button type="submit">Book appointment</button>
  </form>
</div>

<?php elseif ($tab === 'reports'): ?>
<div class="card wide"><h2>My Reports</h2>
  <?php if (!$reports): ?><p class="muted">No reports yet. A doctor adds a report after your appointment.</p><?php else: ?>
  <table><tr><th>#</th><th>Appointment</th><th>Doctor</th><th>Medicine</th><th>Doctor comment</th></tr>
  <?php foreach ($reports as $r): ?>
    <tr><td><?= $r['ReportID'] ?></td><td>#<?= $r['appointmentID'] ?> - <?= e($r['Problem']) ?></td><td><?= e($r['DoctorName']) ?></td>
    <td><?= e($r['MedicinePrescribed']) ?></td><td><?= e($r['DoctorComment']) ?></td></tr>
  <?php endforeach; ?></table><?php endif; ?>
</div>

<?php elseif ($tab === 'feedback'): ?>
<div class="card" style="max-width:640px"><h2>Give Feedback</h2>
  <form method="post"><input type="hidden" name="action" value="feedback">
    <label>Rating</label>
    <select name="points"><?php for ($i = 5; $i >= 1; $i--) echo "<option value='$i'>$i / 5</option>"; ?></select>
    <label>Doctor's nature</label><input name="nature" placeholder="e.g. friendly, patient">
    <label>Location / clinic</label><input name="location">
    <label>Comment</label><textarea name="comment" rows="3"></textarea>
    <button type="submit">Submit feedback</button>
  </form>
</div>

<?php elseif ($tab === 'profile'): ?>
<div class="card" style="max-width:640px"><h2>My Profile</h2>
  <table>
  <?php foreach ($me as $k => $v): ?><tr><th><?= e(str_replace('_', ' ', $k)) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?>
  </table>
</div>

<?php else: ?>
<div class="card wide"><h2>My Appointments</h2>
  <?php if (!$appts): ?><p class="muted">No appointments yet. Use "Book Appointment".</p><?php else: ?>
  <table><tr><th>#</th><th>Problem</th><th>Doctor</th><th>Type</th><th>Fees</th><th>Payment</th><th>Status</th><th></th></tr>
  <?php foreach ($appts as $a): ?>
    <tr><td><?= $a['AppointmentID'] ?></td><td><?= e($a['Problem']) ?></td><td><?= e($a['DoctorName']) ?></td><td><?= e($a['DoctorType']) ?></td>
    <td>Rs. <?= (int)$a['DoctorFees'] ?></td>
    <td><span class="tag <?= tagc($a['PaymentStatus']) ?>"><?= e($a['PaymentStatus']) ?></span></td>
    <td><span class="tag <?= tagc($a['Appointment_Status']) ?>"><?= e($a['Appointment_Status']) ?></span></td>
    <td><?php if ($a['PaymentStatus'] !== 'Paid' && $a['Appointment_Status'] !== 'Cancelled'): ?>
      <form method="post" style="margin:0"><input type="hidden" name="action" value="pay"><input type="hidden" name="id" value="<?= $a['AppointmentID'] ?>">
      <button class="btn sm" type="submit">Pay now</button></form><?php endif; ?></td></tr>
  <?php endforeach; ?></table><?php endif; ?>
</div>
<?php endif; ?>
<?php include 'footer.php'; ?>
