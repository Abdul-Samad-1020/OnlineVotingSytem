<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Workouts";

// Get all workouts for the user
$workouts = [];
$stmt = $conn->prepare("SELECT * FROM workouts WHERE user_id = ? ORDER BY workout_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $workouts[] = $row;
}
$stmt->close();

include '../../includes/header.php';
?>

<h2>Your Workouts</h2>
<a href="add.php" class="button">Add New Workout</a>

<?php if (empty($workouts)): ?>
    <p>No workouts found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($workouts as $workout): ?>
                <tr>
                    <td><?php echo htmlspecialchars($workout['name']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($workout['workout_date'])); ?></td>
                    <td><?php echo htmlspecialchars(substr($workout['notes'], 0, 50)) . (strlen($workout['notes']) > 50 ? '...' : ''); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $workout['id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $workout['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        <a href="../exercises/add.php?workout_id=<?php echo $workout['id']; ?>">Add Exercise</a>
                        <a href="../exercises/?workout_id=<?php echo $workout['id']; ?>">View Exercises</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>