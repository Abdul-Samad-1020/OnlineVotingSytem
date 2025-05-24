<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Add Activity";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $duration = (int)$_POST['duration'];
    $distance = !empty($_POST['distance']) ? (float)$_POST['distance'] : null;
    $calories = !empty($_POST['calories']) ? (int)$_POST['calories'] : null;
    $date = $_POST['date'];
    $notes = trim($_POST['notes']);

    // Validate inputs
    if (empty($type) || empty($duration) || empty($date)) {
        $error = 'Please fill required fields.';
    } else {
        // Insert activity
        $stmt = $conn->prepare("INSERT INTO activities (user_id, type, duration_minutes, distance_km, calories, activity_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isidiss", $_SESSION['user_id'], $type, $duration, $distance, $calories, $date, $notes);
        
        if ($stmt->execute()) {
            header("Location: index.php?added=1");
            exit();
        } else {
            $error = 'Failed to add activity. Please try again.';
        }
        $stmt->close();
    }
}

include '../../includes/header.php';
?>

<h2>Add New Activity</h2>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <div>
        <label for="type">Activity Type:</label>
        <input type="text" id="type" name="type" required>
    </div>
    <div>
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" required min="1">
    </div>
    <div>
        <label for="distance">Distance (km):</label>
        <input type="number" id="distance" name="distance" step="0.01" min="0">
    </div>
    <div>
        <label for="calories">Calories Burned:</label>
        <input type="number" id="calories" name="calories" min="0">
    </div>
    <div>
        <label for="date">Date:</label>
        <input type="datetime-local" id="date" name="date" required value="<?php echo date('Y-m-d\TH:i'); ?>">
    </div>
    <div>
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>
    </div>
    <button type="submit">Add Activity</button>
    <a href="index.php" class="button">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>