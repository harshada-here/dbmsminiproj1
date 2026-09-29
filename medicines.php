<?php
require "config/db.php";

// Add a new medicine to stock
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["form"] == "medicine") {
    $name  = $_POST["name"];
    $cat   = $_POST["cat"];
    $manu  = $_POST["manu"];
    $price = $_POST["price"];
    $stock = $_POST["stock"];
    $exp   = $_POST["exp"];

    $result = mysqli_query($conn, "SELECT IFNULL(MAX(Medicine_ID),600)+1 AS next_id FROM MEDICINE");
    $new_id = mysqli_fetch_assoc($result)["next_id"];

    $sql = "INSERT INTO MEDICINE (Medicine_ID, Medicine_Name, Category, Manufacturer, Unit_Price, Stock_Quantity, Expiry_Date)
            VALUES ('$new_id', '$name', '$cat', '$manu', '$price', '$stock', '$exp')";
    mysqli_query($conn, $sql);
}

// Create a prescription WITH multiple medicines in one submission.
// The medicine fields arrive as arrays because the form fields are named medicine_id[], dosage[] etc —
// one array slot per medicine row the doctor added on the page.
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["form"] == "new_prescription") {
    $record_id    = $_POST["record_id"];
    $instructions = $_POST["instructions"];

    // Step 1: create ONE prescription header row.
    $result = mysqli_query($conn, "SELECT IFNULL(MAX(Prescription_ID),500)+1 AS next_id FROM PRESCRIPTION");
    $new_pres_id = mysqli_fetch_assoc($result)["next_id"];

    $sql = "INSERT INTO PRESCRIPTION (Prescription_ID, Record_ID, Prescription_Date, Instructions)
            VALUES ('$new_pres_id', '$record_id', CURDATE(), '$instructions')";
    mysqli_query($conn, $sql);

    // Step 2: loop through however many medicine rows were submitted, inserting one
    // PRESCRIPTION_MEDICINE row per medicine — this is what lets one prescription hold many medicines.
    $medicine_ids = $_POST["medicine_id"];   // e.g. [601, 602, 603]
    $dosages      = $_POST["dosage"];
    $frequencies  = $_POST["frequency"];
    $durations    = $_POST["duration"];
    $quantities   = $_POST["quantity"];

    for ($i = 0; $i < count($medicine_ids); $i++) {
        // Skip a row if it was left completely blank (e.g. user added a row but didn't fill it)
        if ($medicine_ids[$i] == "") continue;

        $med_id = $medicine_ids[$i];
        $dosage = $dosages[$i];
        $freq   = $frequencies[$i];
        $dur    = $durations[$i];
        $qty    = $quantities[$i];

        // This INSERT is what fires your trg_update_stock trigger automatically, once per medicine.
        $sql2 = "INSERT INTO PRESCRIPTION_MEDICINE (Prescription_ID, Medicine_ID, Dosage, Frequency, Duration, Quantity)
                 VALUES ('$new_pres_id', '$med_id', '$dosage', '$freq', '$dur', '$qty')";
        mysqli_query($conn, $sql2);
    }
}

$medicines = mysqli_query($conn, "SELECT * FROM MEDICINE ORDER BY Medicine_ID");

$records_sql = "SELECT mr.Record_ID, CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, mr.Diagnosis
                FROM MEDICAL_RECORD mr JOIN PATIENT p ON mr.Patient_ID = p.Patient_ID";
$records = mysqli_query($conn, $records_sql);

// Fetch medicine options TWICE into an array (not a mysqli result), since we need to loop over
// this same list repeatedly — once for each medicine row on the page.
$medicine_list = [];
$res = mysqli_query($conn, "SELECT Medicine_ID, Medicine_Name FROM MEDICINE");
while ($m = mysqli_fetch_assoc($res)) { $medicine_list[] = $m; }

// Grouped listing: one row per prescription, with all its medicines joined into one cell,
// so you can actually SEE the one-to-many relationship on screen.
$sql = "SELECT pr.Prescription_ID, CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, pr.Instructions,
               GROUP_CONCAT(CONCAT(m.Medicine_Name,' (',pm.Dosage,', ',pm.Frequency,', ',pm.Duration,')') SEPARATOR '; ') AS Medicines
        FROM PRESCRIPTION_MEDICINE pm
        JOIN MEDICINE m ON pm.Medicine_ID = m.Medicine_ID
        JOIN PRESCRIPTION pr ON pm.Prescription_ID = pr.Prescription_ID
        JOIN MEDICAL_RECORD mr ON pr.Record_ID = mr.Record_ID
        JOIN PATIENT p ON mr.Patient_ID = p.Patient_ID
        GROUP BY pr.Prescription_ID
        ORDER BY pr.Prescription_ID";
$prescriptions = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Medicines — MedCore</title>
<link rel="stylesheet" href="css/style.css">
<style>
.med-row{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr 0.7fr auto;gap:10px;align-items:end;margin-bottom:10px;}
.med-row select, .med-row input{padding:8px 10px;border:1px solid var(--line);border-radius:4px;font-family:inherit;font-size:.88rem;width:100%;}
.remove-row{background:none;border:1px solid var(--line);color:var(--red);border-radius:4px;padding:8px 10px;cursor:pointer;}
.add-row-btn{background:none;border:1px dashed var(--teal);color:var(--teal-deep);padding:8px 14px;border-radius:4px;cursor:pointer;font-size:.85rem;margin:4px 18px 10px;}
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
      <a href="billing.php">Billing</a>
      <a href="medicines.php" class="active">Medicines</a>
      <a href="medical_records.php">Medical Records</a>
    </nav>
  </aside>
  <main>
    <div class="topbar"><div><h2>Medicines</h2><p>Inventory and prescriptions</p></div></div>

    <div class="panel">
      <div class="panel-head"><h2>Add medicine</h2></div>
      <form method="POST" class="form-grid">
        <input type="hidden" name="form" value="medicine">
        <div class="field"><label>Name</label><input type="text" name="name" required></div>
        <div class="field"><label>Category</label><input type="text" name="cat"></div>
        <div class="field"><label>Manufacturer</label><input type="text" name="manu"></div>
        <div class="field"><label>Unit price</label><input type="number" step="0.01" name="price" required></div>
        <div class="field"><label>Stock quantity</label><input type="number" name="stock" value="0"></div>
        <div class="field"><label>Expiry date</label><input type="date" name="exp"></div>
        <div class="form-actions"><button class="btn" type="submit">Add medicine</button></div>
      </form>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Stock</h2></div>
      <table>
        <tr><th>ID</th><th>Name</th><th>Category</th><th>Manufacturer</th><th>Unit Price</th><th>Stock</th><th>Expiry</th></tr>
        <?php while ($row = mysqli_fetch_assoc($medicines)): ?>
          <tr>
            <td><?= $row['Medicine_ID'] ?></td>
            <td><?= $row['Medicine_Name'] ?></td>
            <td><?= $row['Category'] ?></td>
            <td><?= $row['Manufacturer'] ?></td>
            <td>₹<?= $row['Unit_Price'] ?></td>
            <td><?= $row['Stock_Quantity'] ?></td>
            <td><?= $row['Expiry_Date'] ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Write prescription</h2></div>
      <form method="POST" id="presForm">
        <input type="hidden" name="form" value="new_prescription">
        <div class="form-grid" style="padding-bottom:0;">
          <div class="field" style="grid-column:1/-1;"><label>Medical record (patient — diagnosis)</label>
            <select name="record_id" required>
              <?php while ($r = mysqli_fetch_assoc($records)): ?>
                <option value="<?= $r['Record_ID'] ?>"><?= $r['Patient_Name'] ?> — <?= $r['Diagnosis'] ?> (Record #<?= $r['Record_ID'] ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="field" style="grid-column:1/-1;"><label>Instructions</label>
            <input type="text" name="instructions" placeholder="e.g. Take after food, follow up in 1 week"></div>
        </div>

        <div style="padding:0 18px;">
          <label style="font-size:.78rem;color:var(--ink-soft);display:block;margin-bottom:8px;">Medicines</label>
          <div id="medRows">
            <!-- One medicine row template — JS clones this to add more -->
            <div class="med-row">
              <select name="medicine_id[]" required>
                <?php foreach ($medicine_list as $m): ?>
                  <option value="<?= $m['Medicine_ID'] ?>"><?= $m['Medicine_Name'] ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="dosage[]" placeholder="Dosage e.g. 500mg" required>
              <input type="text" name="frequency[]" placeholder="Frequency e.g. Twice daily" required>
              <input type="text" name="duration[]" placeholder="Duration e.g. 5 days" required>
              <input type="number" name="quantity[]" placeholder="Qty" required>
              <button type="button" class="remove-row" onclick="removeRow(this)">✕</button>
            </div>
          </div>
          <button type="button" class="add-row-btn" onclick="addRow()">+ Add another medicine</button>
        </div>

        <div class="form-actions" style="padding:0 18px 18px;">
          <button class="btn" type="submit">Save prescription</button>
        </div>
      </form>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Prescriptions</h2></div>
      <table>
        <tr><th>Prescription</th><th>Patient</th><th>Medicines</th></tr>
        <?php while ($row = mysqli_fetch_assoc($prescriptions)): ?>
          <tr>
            <td>#<?= $row['Prescription_ID'] ?></td>
            <td><?= $row['Patient_Name'] ?></td>
            <td><?= $row['Medicines'] ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </main>
</div>

<script>
// Clones the first medicine row so a doctor can add as many medicines as needed
// before submitting — all of them get sent together as arrays (medicine_id[], dosage[], etc).
function addRow() {
  const rows = document.getElementById('medRows');
  const first = rows.querySelector('.med-row');
  const clone = first.cloneNode(true);
  // Clear the cloned inputs so it starts empty, not copying the first row's values
  clone.querySelectorAll('input').forEach(i => i.value = '');
  clone.querySelector('select').selectedIndex = 0;
  rows.appendChild(clone);
}
function removeRow(btn) {
  const rows = document.getElementById('medRows');
  if (rows.querySelectorAll('.med-row').length > 1) {
    btn.closest('.med-row').remove();
  }
}
</script>
</body>
</html>