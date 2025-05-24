<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Add Sleep Record";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $quality = (int)$_POST['quality'];
    $notes = trim($_POST['notes']);

    // Validate inputs
    if (empty($start_time) || empty($end_time)) {
        $error = 'Please fill required fields.';
    } elseif (strtotime($end_time) <= strtotime($start_time)) {
        $error = 'End time must be after start time.';
    } else {
        // Calculate duration in hours
        $duration = (strtotime($end_time) - strtotime($start_time)) / 3600;
        
        // Insert sleep record
        $stmt = $conn->prepare("INSERT INTO sleep (user_id, start_time, end_time, quality, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $_SESSION['user_id'], $start_time, $end_time, $quality, $notes);
        
        if ($stmt->execute()) {
            header("Location: index.php?added=1");
            exit();
        } else {
            $error = 'Failed to add sleep record. Please try again.';
        }
        $stmt->close();
    }
}

include '../../includes/header.php';
?>

<h2>Add Sleep Record</h2>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post" id="sleepForm">
    <div>
        <label for="start_time">Start Time:</label>
        <input type="datetime-local" id="start_time" name="start_time" required value="<?php echo date('Y-m-d\TH:i'); ?>">
    </div>
    <div>
        <label for="end_time">End Time:</label>
        <input type="datetime-local" id="end_time" name="end_time" required value="<?php echo date('Y-m-d\TH:i', strtotime('+8 hours')); ?>">
    </div>
    <div>
        <label for="duration">Duration (hours):</label>
        <input type="number" id="duration" name="duration" step="0.01" readonly>
    </div>
    <div>
        <label for="quality">Sleep Quality:</label>
        <select id="quality" name="quality" required>
            <option value="1">1 ★☆☆☆☆</option>
            <option value="2">2 ★★☆☆☆</option>
            <option value="3" selected>3 ★★★☆☆</option>
            <option value="4">4 ★★★★☆</option>
            <option value="5">5 ★★★★★</option>
        </select>
    </div>
    <div>
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>
    </div>
    <button type="submit">Add Sleep Record</button>
    <a href="index.php" class="button">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>