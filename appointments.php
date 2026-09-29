<?php
// 1. Bring in the DB connection we already made ($conn is now available here)
require "config/db.php";

// 2. Handle form submissions. We use a hidden "action" field to tell the two forms apart.
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "book") {
    // $_POST['field_name'] grabs whatever the user typed into the form below.
    $patient_id = $_POST["patient_id"];
    $doctor_id  = $_POST["doctor_id"];
    $date       = $_POST["date"];
    $time       = $_POST["time"];
    $reason     = $_POST["reason"];

    // Find the next free Appointment_ID (mirrors what your procedure does)
    $result = mysqli_query($conn, "SELECT IFNULL(MAX(Appointment_ID),300)+1 AS next_id FROM APPOINTMENT");
    $row = mysqli_fetch_assoc($result);
    $new_id = $row["next_id"];

    // Run the INSERT. Values are wrapped in quotes because they're strings/dates.
    $sql = "INSERT INTO APPOINTMENT (Appointment_ID, Patient_ID, Doctor_ID, Appointment_Date, Appointment_Time, Status, Reason)
            VALUES ('$new_id', '$patient_id', '$doctor_id', '$date', '$time', 'Scheduled', '$reason')";
    mysqli_query($conn, $sql);
}

// 2b. Handle a status update from the table below (a different form, same page).
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "update_status") {
    $appt_id    = $_POST["appointment_id"];
    $new_status = $_POST["status"];

    // UPDATE changes an EXISTING row instead of creating a new one — that's the key difference from INSERT.
    // WHERE is critical here: without it, EVERY row would get updated.
    $sql = "UPDATE APPOINTMENT SET Status = '$new_status' WHERE Appointment_ID = '$appt_id'";
    mysqli_query($conn, $sql);
}

// 3. Fetch every appointment, joined with patient/doctor names — same logic as Query 1 in your script.
$sql = "SELECT a.Appointment_ID, CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name,
               d.Doctor_Name, a.Appointment_Date, a.Appointment_Time, a.Reason, a.Status
        FROM APPOINTMENT a
        JOIN PATIENT p ON a.Patient_ID = p.Patient_ID
        JOIN DOCTOR d ON a.Doctor_ID = d.Doctor_ID
        ORDER BY a.Appointment_Date DESC";
$appointments = mysqli_query($conn, $sql);

// 4. Also fetch patients and doctors, to fill the dropdowns in the booking form.
$patients = mysqli_query($conn, "SELECT Patient_ID, First_Name, Last_Name FROM PATIENT");
$doctors  = mysqli_query($conn, "SELECT Doctor_ID, Doctor_Name FROM DOCTOR");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Appointments — MedCore</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand"><h1>MedCore</h1><span>Hospital Management</span></div>
    <nav>
      <a href="index.php">Dashboard</a>
      <a href="patients.php">Patients</a>
      <a href="appointments.php" class="active">Appointments</a>
      <a href="doctors.php">Doctors</a>
      <a href="billing.php">Billing</a>
      <a href="medicines.php">Medicines</a>
      <a href="medical_records.php">Medical Records</a>
    </nav>
  </aside>

  <main>
    <div class="topbar">
      <div><h2>Appointments</h2><p>Book and track visits</p></div>
    </div>

    <!-- 5. The booking form. method="POST" sends the data back to THIS same file (see step 2 above). -->
    <div class="panel">
      <div class="panel-head"><h2>Book new appointment</h2></div>
      <form method="POST" class="form-grid">
        <input type="hidden" name="action" value="book">
        <div class="field">
          <label>Patient</label>
          <select name="patient_id" required>
            <?php while ($p = mysqli_fetch_assoc($patients)): ?>
              <option value="<?= $p['Patient_ID'] ?>"><?= $p['First_Name'].' '.$p['Last_Name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field">
          <label>Doctor</label>
          <select name="doctor_id" required>
            <?php while ($d = mysqli_fetch_assoc($doctors)): ?>
              <option value="<?= $d['Doctor_ID'] ?>"><?= $d['Doctor_Name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Date</label><input type="date" name="date" required></div>
        <div class="field"><label>Time</label><input type="time" name="time" required></div>
        <div class="field" style="grid-column:1/-1;"><label>Reason</label><input type="text" name="reason" placeholder="e.g. Follow-up"></div>
        <div class="form-actions"><button class="btn" type="submit">Book appointment</button></div>
      </form>
    </div>

    <!-- 6. The table of existing appointments -->
    <div class="panel">
      <table>
        <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Update</th></tr>
        <?php while ($row = mysqli_fetch_assoc($appointments)): ?>
          <tr>
            <td><?= $row['Appointment_ID'] ?></td>
            <td><?= $row['Patient_Name'] ?></td>
            <td><?= $row['Doctor_Name'] ?></td>
            <td><?= $row['Appointment_Date'] ?></td>
            <td><?= $row['Appointment_Time'] ?></td>
            <td><?= $row['Reason'] ?></td>
            <td><span class="badge b-<?= strtolower($row['Status']) ?>"><?= $row['Status'] ?></span></td>
            <td>
              <!-- One tiny form per row. Submitting it posts back to this same page with action=update_status -->
              <form method="POST" style="display:flex;gap:6px;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="appointment_id" value="<?= $row['Appointment_ID'] ?>">
                <select name="status" style="padding:4px;border-radius:4px;border:1px solid var(--line);">
                  <option <?= $row['Status']=='Scheduled'?'selected':'' ?>>Scheduled</option>
                  <option <?= $row['Status']=='Completed'?'selected':'' ?>>Completed</option>
                  <option <?= $row['Status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
                <button class="btn" type="submit" style="padding:4px 10px;">Save</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </main>
</div>
</body>
</html>