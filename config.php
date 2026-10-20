<?php
// ===== SETTINGS (change here if needed) =====
$DB_HOST = '127.0.0.1';
$DB_PORT = 3307;                       // your MySQL port in XAMPP
$DB_USER = 'root';
$DB_PASS = '';                         // XAMPP default = empty
$DB_NAME = 'HealthcareMangaementSystem';
$ADMIN_USER = 'admin';                 // put the same values as in Admin.java
$ADMIN_PASS = 'admin';
// ============================================

session_start();
mysqli_report(MYSQLI_REPORT_OFF);
$db = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($db->connect_errno) {
    die('<h2 style="font-family:sans-serif">Database connection failed</h2><p style="font-family:sans-serif">'
        . htmlspecialchars($db->connect_error)
        . '<br>Check: XAMPP MySQL is running, port in config.php is correct.</p>');
}
$db->set_charset('utf8mb4');

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function rows($sql, $types = '', $params = []) {
    global $db;
    $st = $db->prepare($sql);
    if (!$st) return [];
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    $out = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $st->close();
    return $out;
}

function run($sql, $types = '', $params = []) {
    global $db;
    $st = $db->prepare($sql);
    if (!$st) return false;
    if ($types !== '') $st->bind_param($types, ...$params);
    $ok = $st->execute();
    $st->close();
    return $ok;
}

function next_id($table, $col) {
    $r = rows("SELECT COALESCE(MAX($col),0)+1 AS n FROM $table");
    return (int)$r[0]['n'];
}

function need($role) {
    if (($_SESSION['role'] ?? '') !== $role) { header('Location: index.php'); exit; }
}

function cut($s, $n) { return mb_substr(trim($s), 0, $n); }
