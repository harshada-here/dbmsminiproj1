<?php
require "config/db.php";

// Quick counts for the stat cards
$total_patients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM PATIENT"))['c'];
$today_appts    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM APPOINTMENT"))['c'];
$revenue        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(Total_Amount),0) AS c FROM BILL WHERE Payment_Status='Paid'"))['c'];
$pending_bills  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM BILL WHERE Payment_Status='Pending'"))['c'];

// Recent appointments (same join pattern as appointments.php)
$sql = "SELECT CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, d.Doctor_Name, dept.Department_Name,
               a.Appointment_Date, a.Status
        FROM APPOINTMENT a
        JOIN PATIENT p ON a.Patient_ID = p.Patient_ID
        JOIN DOCTOR d ON a.Doctor_ID = d.Doctor_ID
        JOIN DEPARTMENT dept ON d.Department_ID = dept.Department_ID
        ORDER BY a.Appointment_Date DESC LIMIT 5";
$recent = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard — MedCore</title>
<link rel="stylesheet" href="css/style.css">
<style>
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:26px;}
.stat{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:16px 18px;}
.stat .num{font-size:1.7rem;font-weight:700;color:var(--teal-deep);font-family:Georgia,serif;}
.stat .label{font-size:.8rem;color:var(--ink-soft);margin-top:4px;}
@media(max-width:760px){.stat-row{grid-template-columns:1fr 1fr;}}
</style>
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand"><h1>MedCore</h1><span>Hospital Management</span></div>
    <nav>
      <a href="index.php" class="active">Dashboard</a>
      <a href="patients.php">Patients</a>
      <a href="appointments.php">Appointments</a>
      <a href="doctors.php">Doctors</a>
      <a href="billing.php">Billing</a>
      <a href="medicines.php">Medicines</a>
      <a href="medical_records.php">Medical Records</a>
    </nav>
  </aside>
  <main>
    <div class="topbar"><div><h2>Dashboard</h2><p>Overview</p></div></div>

    <div class="stat-row">
      <div class="stat"><div class="num"><?= $total_patients ?></div><div class="label">Total patients</div></div>
      <div class="stat"><div class="num"><?= $today_appts ?></div><div class="label">Total appointments</div></div>
      <div class="stat"><div class="num">₹<?= number_format($revenue,2) ?></div><div class="label">Revenue collected</div></div>
      <div class="stat"><div class="num"><?= $pending_bills ?></div><div class="label">Pending bills</div></div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Recent appointments</h2></div>
      <table>
        <tr><th>Patient</th><th>Doctor</th><th>Department</th><th>Date</th><th>Status</th></tr>
        <?php while ($row = mysqli_fetch_assoc($recent)): ?>
          <tr>
            <td><?= $row['Patient_Name'] ?></td>
            <td><?= $row['Doctor_Name'] ?></td>
            <td><?= $row['Department_Name'] ?></td>
            <td><?= $row['Appointment_Date'] ?></td>
            <td><span class="badge b-<?= strtolower($row['Status']) ?>"><?= $row['Status'] ?></span></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </main>
</div>
</body>
</html>