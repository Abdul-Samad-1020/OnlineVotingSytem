<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Meals";

// Get all meals for the user
$meals = [];
$stmt = $conn->prepare("SELECT * FROM meals WHERE user_id = ? ORDER BY meal_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}
$stmt->close();

include '../../includes/header.php';
?>

<h2>Your Meals</h2>
<a href="add.php" class="button">Add New Meal</a>

<?php if (empty($meals)): ?>
    <p>No meals found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Calories</th>
                <th>Protein</th>
                <th>Carbs</th>
                <th>Fat</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($meals as $meal): ?>
                <tr>
                    <td><?php echo htmlspecialchars($meal['name']); ?></td>
                    <td><?php echo $meal['calories']; ?></td>
                    <td><?php echo $meal['protein_g'] ?? '-'; ?>g</td>
                    <td><?php echo $meal['carbs_g'] ?? '-'; ?>g</td>
                    <td><?php echo $meal['fat_g'] ?? '-'; ?>g</td>
                    <td><?php echo date('M j, Y', strtotime($meal['meal_date'])); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $meal['id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $meal['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>