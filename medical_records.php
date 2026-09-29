<?php
require "config/db.php";

// Add a new medical record (diagnosis) — this is what a prescription is written against.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id  = $_POST["patient_id"];
    $doctor_id   = $_POST["doctor_id"];
    $appt_id     = $_POST["appointment_id"];
    $diagnosis   = $_POST["diagnosis"];
    $symptoms    = $_POST["symptoms"];
    $treatment   = $_POST["treatment"];

    $result = mysqli_query($conn, "SELECT IFNULL(MAX(Record_ID),400)+1 AS next_id FROM MEDICAL_RECORD");
    $new_id = mysqli_fetch_assoc($result)["next_id"];

    $sql = "INSERT INTO MEDICAL_RECORD (Record_ID, Patient_ID, Doctor_ID, Appointment_ID, Diagnosis, Symptoms, Treatment, Record_Date)
            VALUES ('$new_id', '$patient_id', '$doctor_id', '$appt_id', '$diagnosis', '$symptoms', '$treatment', CURDATE())";
    mysqli_query($conn, $sql);
}

$patients = mysqli_query($conn, "SELECT Patient_ID, First_Name, Last_Name FROM PATIENT");
$doctors  = mysqli_query($conn, "SELECT Doctor_ID, Doctor_Name FROM DOCTOR");
$appointments = mysqli_query($conn, "SELECT Appointment_ID FROM APPOINTMENT");

$sql = "SELECT mr.Record_ID, CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, d.Doctor_Name,
               mr.Diagnosis, mr.Symptoms, mr.Treatment, mr.Record_Date
        FROM MEDICAL_RECORD mr
        JOIN PATIENT p ON mr.Patient_ID = p.Patient_ID
        JOIN DOCTOR d ON mr.Doctor_ID = d.Doctor_ID
        ORDER BY mr.Record_ID DESC";
$records = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Medical Records — MedCore</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand"><h1>MedCore</h1><span>Hospital Management</span></div>
    <nav>
      <a href="index.php">Dashboard</a>
      <a href="patients.php">Patients</a>
      <a href="appointments.php">Appointments</a>
      <a href="doctors.php">Doctors</a>
      <a href="billing.php">Billing</a>
      <a href="medicines.php">Medicines</a>
      <a href="medical_records.php" class="active">Medical Records</a>
    </nav>
  </aside>
  <main>
    <div class="topbar"><div><h2>Medical Records</h2><p>Diagnoses — required before writing a prescription</p></div></div>

    <div class="panel">
      <div class="panel-head"><h2>Add medical record</h2></div>
      <form method="POST" class="form-grid">
        <div class="field"><label>Patient</label>
          <select name="patient_id" required>
            <?php while ($p = mysqli_fetch_assoc($patients)): ?>
              <option value="<?= $p['Patient_ID'] ?>"><?= $p['First_Name'].' '.$p['Last_Name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Doctor</label>
          <select name="doctor_id" required>
            <?php while ($d = mysqli_fetch_assoc($doctors)): ?>
              <option value="<?= $d['Doctor_ID'] ?>"><?= $d['Doctor_Name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Appointment</label>
          <select name="appointment_id" required>
            <?php while ($a = mysqli_fetch_assoc($appointments)): ?>
              <option value="<?= $a['Appointment_ID'] ?>">#<?= $a['Appointment_ID'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Diagnosis</label><input type="text" name="diagnosis" required></div>
        <div class="field" style="grid-column:1/-1;"><label>Symptoms</label><input type="text" name="symptoms"></div>
        <div class="field" style="grid-column:1/-1;"><label>Treatment</label><input type="text" name="treatment"></div>
        <div class="form-actions"><button class="btn" type="submit">Add record</button></div>
      </form>
    </div>

    <div class="panel">
      <table>
        <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Symptoms</th><th>Treatment</th><th>Date</th></tr>
        <?php while ($row = mysqli_fetch_assoc($records)): ?>
          <tr>
            <td>#<?= $row['Record_ID'] ?></td>
            <td><?= $row['Patient_Name'] ?></td>
            <td><?= $row['Doctor_Name'] ?></td>
            <td><?= $row['Diagnosis'] ?></td>
            <td><?= $row['Symptoms'] ?></td>
            <td><?= $row['Treatment'] ?></td>
            <td><?= $row['Record_Date'] ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </main>
</div>
</body>
</html>