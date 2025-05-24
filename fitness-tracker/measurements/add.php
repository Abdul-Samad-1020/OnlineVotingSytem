<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Add Body Measurement";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $height = !empty($_POST['height']) ? (float)$_POST['height'] : null;
    $chest = !empty($_POST['chest']) ? (float)$_POST['chest'] : null;
    $waist = !empty($_POST['waist']) ? (float)$_POST['waist'] : null;
    $hips = !empty($_POST['hips']) ? (float)$_POST['hips'] : null;
    $notes = trim($_POST['notes']);

    // Validate inputs
    if (empty($date)) {
        $error = 'Please fill required fields.';
    } else {
        // Insert measurement
        $stmt = $conn->prepare("INSERT INTO body_measurements (user_id, record_date, weight_kg, height_cm, chest_cm, waist_cm, hips_cm, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddddds", $_SESSION['user_id'], $date, $weight, $height, $chest, $waist, $hips, $notes);
        
        if ($stmt->execute()) {
            header("Location: index.php?added=1");
            exit();
        } else {
            $error = 'Failed to add measurement. Please try again.';
        }
        $stmt->close();
    }
}

include '../../includes/header.php';
?>

<h2>Add Body Measurement</h2>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <div>
        <label for="date">Date:</label>
        <input type="datetime-local" id="date" name="date" required value="<?php echo date('Y-m-d\TH:i'); ?>">
    </div>
    <div>
        <label for="weight">Weight (kg):</label>
        <input type="number" id="weight" name="weight" step="0.1" min="0">
    </div>
    <div>
        <label for="height">Height (cm):</label>
        <input type="number" id="height" name="height" step="0.1" min="0">
    </div>
    <div>
        <label for="chest">Chest (cm):</label>
        <input type="number" id="chest" name="chest" step="0.1" min="0">
    </div>
    <div>
        <label for="waist">Waist (cm):</label>
        <input type="number" id="waist" name="waist" step="0.1" min="0">
    </div>
    <div>
        <label for="hips">Hips (cm):</label>
        <input type="number" id="hips" name="hips" step="0.1" min="0">
    </div>
    <div>
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>
    </div>
    <button type="submit">Add Measurement</button>
    <a href="index.php" class="button">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>