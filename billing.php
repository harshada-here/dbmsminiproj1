<?php
require "config/db.php";

// Two things can be submitted from this page: a brand new bill, or a status update on an existing one.
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "add") {
    $patient_id = $_POST["patient_id"];
    $appt_id    = $_POST["appointment_id"];
    $consult    = $_POST["consultation"];
    $medicine   = $_POST["medicine"];
    $other      = $_POST["other"];
    $total      = $consult + $medicine + $other;

    $result = mysqli_query($conn, "SELECT IFNULL(MAX(Bill_ID),700)+1 AS next_id FROM BILL");
    $new_id = mysqli_fetch_assoc($result)["next_id"];

    $sql = "INSERT INTO BILL (Bill_ID, Patient_ID, Appointment_ID, Bill_Date, Consultation_Charge, Medicine_Charge, Other_Charges, Total_Amount, Payment_Status)
            VALUES ('$new_id', '$patient_id', '$appt_id', CURDATE(), '$consult', '$medicine', '$other', '$total', 'Unpaid')";
    mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "update_status") {
    $bill_id    = $_POST["bill_id"];
    $new_status = $_POST["payment_status"];
    $sql = "UPDATE BILL SET Payment_Status = '$new_status' WHERE Bill_ID = '$bill_id'";
    mysqli_query($conn, $sql);
}

$patients     = mysqli_query($conn, "SELECT Patient_ID, First_Name, Last_Name FROM PATIENT");
$appointments = mysqli_query($conn, "SELECT Appointment_ID FROM APPOINTMENT");

$sql = "SELECT b.Bill_ID, CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name,
               b.Consultation_Charge, b.Medicine_Charge, b.Other_Charges, b.Total_Amount, b.Payment_Status
        FROM BILL b JOIN PATIENT p ON b.Patient_ID = p.Patient_ID
        ORDER BY b.Bill_ID";
$bills = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Billing — MedCore</title>
<link rel="stylesheet" href="css/style.css">
<style>
.b-paid{background:#e2f3e8;color:#1f7a45;}
.b-pending{background:#fbeed9;color:var(--amber);}
.b-unpaid{background:#f7e6e5;color:var(--red);}
</style>
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
      <a href="billing.php" class="active">Billing</a>
      <a href="medicines.php">Medicines</a>
      <a href="medical_records.php">Medical Records</a>
    </nav>
  </aside>
  <main>
    <div class="topbar"><div><h2>Billing</h2><p>Revenue and payment status</p></div></div>

    <div class="panel">
      <div class="panel-head"><h2>Create bill</h2></div>
      <form method="POST" class="form-grid">
        <input type="hidden" name="action" value="add">
        <div class="field"><label>Patient</label>
          <select name="patient_id" required>
            <?php while ($p = mysqli_fetch_assoc($patients)): ?>
              <option value="<?= $p['Patient_ID'] ?>"><?= $p['First_Name'].' '.$p['Last_Name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Appointment ID</label>
          <select name="appointment_id" required>
            <?php while ($a = mysqli_fetch_assoc($appointments)): ?>
              <option value="<?= $a['Appointment_ID'] ?>"><?= $a['Appointment_ID'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Consultation charge</label><input type="number" step="0.01" name="consultation" value="0"></div>
        <div class="field"><label>Medicine charge</label><input type="number" step="0.01" name="medicine" value="0"></div>
        <div class="field"><label>Other charges</label><input type="number" step="0.01" name="other" value="0"></div>
        <div class="form-actions"><button class="btn" type="submit">Create bill</button></div>
      </form>
    </div>

    <div class="panel">
      <table>
        <tr><th>Bill ID</th><th>Patient</th><th>Consultation</th><th>Medicine</th><th>Total</th><th>Status</th><th>Update</th></tr>
        <?php while ($row = mysqli_fetch_assoc($bills)): ?>
          <tr>
            <td><?= $row['Bill_ID'] ?></td>
            <td><?= $row['Patient_Name'] ?></td>
            <td>₹<?= $row['Consultation_Charge'] ?></td>
            <td>₹<?= $row['Medicine_Charge'] ?></td>
            <td>₹<?= $row['Total_Amount'] ?></td>
            <td><span class="badge b-<?= strtolower($row['Payment_Status']) ?>"><?= $row['Payment_Status'] ?></span></td>
            <td>
              <form method="POST" style="display:flex;gap:6px;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="bill_id" value="<?= $row['Bill_ID'] ?>">
                <select name="payment_status" style="padding:4px;border-radius:4px;border:1px solid var(--line);">
                  <option <?= $row['Payment_Status']=='Paid'?'selected':'' ?>>Paid</option>
                  <option <?= $row['Payment_Status']=='Pending'?'selected':'' ?>>Pending</option>
                  <option <?= $row['Payment_Status']=='Unpaid'?'selected':'' ?>>Unpaid</option>
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