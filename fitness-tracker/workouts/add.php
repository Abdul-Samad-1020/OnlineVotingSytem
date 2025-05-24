<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Add Workout";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $date = $_POST['date'];
    $notes = trim($_POST['notes']);

    // Validate inputs
    if (empty($name) || empty($date)) {
        $error = 'Please fill required fields.';
    } else {
        // Insert workout
        $stmt = $conn->prepare("INSERT INTO workouts (user_id, name, workout_date, notes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $name, $date, $notes);
        
        if ($stmt->execute()) {
            header("Location: index.php?added=1");
            exit();
        } else {
            $error = 'Failed to add workout. Please try again.';
        }
        $stmt->close();
    }
}

include '../../includes/header.php';
?>

<h2>Add New Workout</h2>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <div>
        <label for="name">Workout Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div>
        <label for="date">Date:</label>
        <input type="datetime-local" id="date" name="date" required value="<?php echo date('Y-m-d\TH:i'); ?>">
    </div>
    <div>
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>
    </div>
    <button type="submit">Add Workout</button>
    <a href="index.php" class="button">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>