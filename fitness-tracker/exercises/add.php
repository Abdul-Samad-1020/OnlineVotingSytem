<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Add Exercise";
$error = '';

$workout_id = isset($_GET['workout_id']) ? (int)$_GET['workout_id'] : 0;

// Verify workout belongs to user
if ($workout_id) {
    $stmt = $conn->prepare("SELECT id FROM workouts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $workout_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        header("Location: ../workouts/index.php");
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workout_id = (int)$_POST['workout_id'];
    $name = trim($_POST['name']);
    $sets = !empty($_POST['sets']) ? (int)$_POST['sets'] : null;
    $reps = !empty($_POST['reps']) ? (int)$_POST['reps'] : null;
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $duration = !empty($_POST['duration']) ? (int)$_POST['duration'] : null;
    $notes = trim($_POST['notes']);

    // Validate inputs
    if (empty($name) || empty($workout_id)) {
        $error = 'Please fill required fields.';
    } else {
        // Insert exercise
        $stmt = $conn->prepare("INSERT INTO exercises (workout_id, name, sets, reps, weight_kg, duration_minutes, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiids", $workout_id, $name, $sets, $reps, $weight, $duration, $notes);
        
        if ($stmt->execute()) {
            header("Location: ../workouts/index.php?exercise_added=1");
            exit();
        } else {
            $error = 'Failed to add exercise. Please try again.';
        }
        $stmt->close();
    }
}

include '../../includes/header.php';
?>

<h2>Add Exercise</h2>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="workout_id" value="<?php echo $workout_id; ?>">
    
    <div>
        <label for="name">Exercise Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div>
        <label for="sets">Sets:</label>
        <input type="number" id="sets" name="sets" min="0">
    </div>
    <div>
        <label for="reps">Reps:</label>
        <input type="number" id="reps" name="reps" min="0">
    </div>
    <div>
        <label for="weight">Weight (kg):</label>
        <input type="number" id="weight" name="weight" step="0.01" min="0">
    </div>
    <div>
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" min="0">
    </div>
    <div>
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>
    </div>
    <button type="submit">Add Exercise</button>
    <a href="../workouts/index.php" class="button">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>