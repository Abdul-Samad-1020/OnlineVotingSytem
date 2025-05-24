<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Add Meal";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $date = $_POST['date'];
    $calories = (int)$_POST['calories'];
    $protein = !empty($_POST['protein']) ? (float)$_POST['protein'] : null;
    $carbs = !empty($_POST['carbs']) ? (float)$_POST['carbs'] : null;
    $fat = !empty($_POST['fat']) ? (float)$_POST['fat'] : null;
    $notes = trim($_POST['notes']);

    // Validate inputs
    if (empty($name) || empty($date) || empty($calories)) {
        $error = 'Please fill required fields.';
    } else {
        // Insert meal
        $stmt = $conn->prepare("INSERT INTO meals (user_id, name, meal_date, calories, protein_g, carbs_g, fat_g, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiddds", $_SESSION['user_id'], $name, $date, $calories, $protein, $carbs, $fat, $notes);
        
        if ($stmt->execute()) {
            header("Location: index.php?added=1");
            exit();
        } else {
            $error = 'Failed to add meal. Please try again.';
        }
        $stmt->close();
    }
}

include '../../includes/header.php';
?>

<h2>Add New Meal</h2>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <div>
        <label for="name">Meal Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div>
        <label for="date">Date:</label>
        <input type="datetime-local" id="date" name="date" required value="<?php echo date('Y-m-d\TH:i'); ?>">
    </div>
    <div>
        <label for="calories">Calories:</label>
        <input type="number" id="calories" name="calories" required min="0">
    </div>
    <div>
        <label for="protein">Protein (g):</label>
        <input type="number" id="protein" name="protein" step="0.1" min="0">
    </div>
    <div>
        <label for="carbs">Carbs (g):</label>
        <input type="number" id="carbs" name="carbs" step="0.1" min="0">
    </div>
    <div>
        <label for="fat">Fat (g):</label>
        <input type="number" id="fat" name="fat" step="0.1" min="0">
    </div>
    <div>
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"></textarea>
    </div>
    <button type="submit">Add Meal</button>
    <a href="index.php" class="button">Cancel</a>
</form>

<?php include '../../includes/footer.php'; ?>