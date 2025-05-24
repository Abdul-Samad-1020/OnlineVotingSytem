<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Edit Activity";
$error = '';

// Get activity ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch activity data
$stmt = $conn->prepare("SELECT * FROM activities WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$activity = $result->fetch_assoc();
$stmt->close();

if (!$activity) {
    header("Location: index.php");
    exit();
}

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
        // Update activity
        $stmt = $conn->prepare("UPDATE activities SET type = ?, duration_minutes = ?, distance_km = ?, calories = ?, activity_date = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sidissii", $type, $duration, $distance, $calories, $date, $notes, $id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            header("Location: index.php?updated=1");
            exit();
        } else {
            $error = 'Failed to update activity. Please try again.';
        }
        $stmt->close();
    }
}

include '../../includes/header.php';
?>

<h2>Edit Activity</h2>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <div>
        <label for="type">Activity Type:</label>
        <input type="text" id="type" name="type" required value="<?php echo htmlspecialchars($activity['type']); ?>">
    </div>
    <div>
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" required min="1" value="<?php echo $activity['duration_minutes']; ?>">
    </div>
    <div>
        <label for="distance">Distance (km):</label>
        <input type="number" id="distance" name="distance" step="0.01" min="0" value="<?php echo $activity['distance_km'] ?? ''; ?>">
    </div>
    <div>
        <label for="calories">Calories Burned:</label>
        <input type="number" id="calories" name="calories" min="0" value="<?php echo $activity['calories'] ?? ''; ?>">
    </div>
    <div>
        <label for="date">Date:</label>
        <input type="datetime-local" id="date" name="date" required value="<?php echo date('Y-m-d\TH:i', strtotime($activity['activity_date'])); ?>">
    </div>
    <div>
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"><?php echo htmlspecialchars($activity['notes']); ?></textarea>
    </div>
    <button type="submit">Update Activity</button>
    <a href="index.php" class="button">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>