<?php
require "config/db.php";

// Handle both "add new" and "update existing" from the same form, using a hidden action field.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname  = $_POST["fname"];
    $lname  = $_POST["lname"];
    $dob    = $_POST["dob"];
    $gender = $_POST["gender"];
    $phone  = $_POST["phone"];
    $blood  = $_POST["blood"];

    if ($_POST["action"] == "add") {
        $result = mysqli_query($conn, "SELECT IFNULL(MAX(Patient_ID),200)+1 AS next_id FROM PATIENT");
        $new_id = mysqli_fetch_assoc($result)["next_id"];

        $sql = "INSERT INTO PATIENT (Patient_ID, First_Name, Last_Name, Date_Of_Birth, Gender, Phone, Blood_Group)
                VALUES ('$new_id', '$fname', '$lname', '$dob', '$gender', '$phone', '$blood')";
        mysqli_query($conn, $sql);
    }

    if ($_POST["action"] == "update") {
        $edit_id = $_POST["patient_id"];
        // UPDATE + WHERE = change this one existing row, not a new row.
        $sql = "UPDATE PATIENT SET First_Name='$fname', Last_Name='$lname', Date_Of_Birth='$dob',
                Gender='$gender', Phone='$phone', Blood_Group='$blood'
                WHERE Patient_ID = '$edit_id'";
        mysqli_query($conn, $sql);
    }
}

// If the URL has ?edit=203, load that one patient's current data to pre-fill the form.
$editing = null;
if (isset($_GET["edit"])) {
    $edit_id = $_GET["edit"];
    $result = mysqli_query($conn, "SELECT * FROM PATIENT WHERE Patient_ID = '$edit_id'");
    $editing = mysqli_fetch_assoc($result);
}

$patients = mysqli_query($conn, "SELECT * FROM PATIENT ORDER BY Patient_ID");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Patients — MedCore</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand"><h1>MedCore</h1><span>Hospital Management</span></div>
    <nav>
      <a href="index.php">Dashboard</a>
      <a href="patients.php" class="active">Patients</a>
      <a href="appointments.php">Appointments</a>
      <a href="doctors.php">Doctors</a>
      <a href="billing.php">Billing</a>
      <a href="medicines.php">Medicines</a>
      <a href="medical_records.php">Medical Records</a>
    </nav>
  </aside>
  <main>
    <div class="topbar"><div><h2>Patients</h2><p>All registered patients</p></div></div>

    <div class="panel">
      <div class="panel-head"><h2><?= $editing ? "Edit patient #".$editing['Patient_ID'] : "Add patient" ?></h2></div>
      <form method="POST" class="form-grid">
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'add' ?>">
        <?php if ($editing): ?>
          <input type="hidden" name="patient_id" value="<?= $editing['Patient_ID'] ?>">
        <?php endif; ?>

        <div class="field"><label>First name</label>
          <input type="text" name="fname" required value="<?= $editing['First_Name'] ?? '' ?>"></div>
        <div class="field"><label>Last name</label>
          <input type="text" name="lname" required value="<?= $editing['Last_Name'] ?? '' ?>"></div>
        <div class="field"><label>Date of birth</label>
          <input type="date" name="dob" value="<?= $editing['Date_Of_Birth'] ?? '' ?>"></div>
        <div class="field"><label>Gender</label>
          <select name="gender">
            <?php foreach (["Male","Female","Other"] as $g): ?>
              <option <?= (isset($editing) && $editing['Gender']==$g) ? 'selected' : '' ?>><?= $g ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Phone</label>
          <input type="text" name="phone" value="<?= $editing['Phone'] ?? '' ?>"></div>
        <div class="field"><label>Blood group</label>
          <input type="text" name="blood" placeholder="e.g. B+" value="<?= $editing['Blood_Group'] ?? '' ?>"></div>
        <div class="form-actions">
          <button class="btn" type="submit"><?= $editing ? "Save changes" : "Add patient" ?></button>
          <?php if ($editing): ?>
            <a href="patients.php" style="margin-left:10px;font-size:.85rem;color:var(--ink-soft);">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="panel">
      <table>
        <tr><th>ID</th><th>Name</th><th>Gender</th><th>Blood Group</th><th>Phone</th><th></th></tr>
        <?php while ($row = mysqli_fetch_assoc($patients)): ?>
          <tr>
            <td><?= $row['Patient_ID'] ?></td>
            <td><?= $row['First_Name'].' '.$row['Last_Name'] ?></td>
            <td><?= $row['Gender'] ?></td>
            <td><?= $row['Blood_Group'] ?></td>
            <td><?= $row['Phone'] ?></td>
            <td><a href="?edit=<?= $row['Patient_ID'] ?>" class="btn" style="text-decoration:none;padding:4px 10px;">Edit</a></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </main>
</div>
</body>
</html>