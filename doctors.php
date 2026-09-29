<?php
require "config/db.php";

// Add a new doctor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = $_POST["name"];
    $spec  = $_POST["spec"];
    $dept  = $_POST["dept"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];

    $result = mysqli_query($conn, "SELECT IFNULL(MAX(Doctor_ID),100)+1 AS next_id FROM DOCTOR");
    $new_id = mysqli_fetch_assoc($result)["next_id"];

    $sql = "INSERT INTO DOCTOR (Doctor_ID, Department_ID, Doctor_Name, Specialization, Phone, Email)
            VALUES ('$new_id', '$dept', '$name', '$spec', '$phone', '$email')";
    mysqli_query($conn, $sql);
}

$departments = mysqli_query($conn, "SELECT Department_ID, Department_Name FROM DEPARTMENT");

$sql = "SELECT d.Doctor_ID, d.Doctor_Name, d.Specialization, d.Phone, dept.Department_Name
        FROM DOCTOR d JOIN DEPARTMENT dept ON d.Department_ID = dept.Department_ID
        ORDER BY d.Doctor_ID";
$doctors = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Doctors — MedCore</title>
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
      <a href="doctors.php" class="active">Doctors</a>
      <a href="billing.php">Billing</a>
      <a href="medicines.php">Medicines</a>
      <a href="medical_records.php">Medical Records</a>
    </nav>
  </aside>
  <main>
    <div class="topbar"><div><h2>Doctors</h2><p>All doctors by department</p></div></div>

    <div class="panel">
      <div class="panel-head"><h2>Add doctor</h2></div>
      <form method="POST" class="form-grid">
        <div class="field"><label>Name</label><input type="text" name="name" required placeholder="Dr. First Last"></div>
        <div class="field"><label>Specialization</label><input type="text" name="spec"></div>
        <div class="field"><label>Department</label>
          <select name="dept" required>
            <?php while ($d = mysqli_fetch_assoc($departments)): ?>
              <option value="<?= $d['Department_ID'] ?>"><?= $d['Department_Name'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Phone</label><input type="text" name="phone"></div>
        <div class="field"><label>Email</label><input type="email" name="email"></div>
        <div class="form-actions"><button class="btn" type="submit">Add doctor</button></div>
      </form>
    </div>

    <div class="panel">
      <table>
        <tr><th>ID</th><th>Name</th><th>Specialization</th><th>Department</th><th>Phone</th></tr>
        <?php while ($row = mysqli_fetch_assoc($doctors)): ?>
          <tr>
            <td><?= $row['Doctor_ID'] ?></td>
            <td><?= $row['Doctor_Name'] ?></td>
            <td><?= $row['Specialization'] ?></td>
            <td><?= $row['Department_Name'] ?></td>
            <td><?= $row['Phone'] ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </main>
</div>
</body>
</html>