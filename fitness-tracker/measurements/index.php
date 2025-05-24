<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Body Measurements";

// Get all measurements for the user
$measurements = [];
$stmt = $conn->prepare("SELECT * FROM body_measurements WHERE user_id = ? ORDER BY record_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $measurements[] = $row;
}
$stmt->close();

include '../../includes/header.php';
?>

<h2>Your Body Measurements</h2>
<a href="add.php" class="button">Add New Measurement</a>

<?php if (empty($measurements)): ?>
    <p>No measurements found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Weight (kg)</th>
                <th>Height (cm)</th>
                <th>Chest (cm)</th>
                <th>Waist (cm)</th>
                <th>Hips (cm)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($measurements as $measurement): ?>
                <tr>
                    <td><?php echo date('M j, Y', strtotime($measurement['record_date'])); ?></td>
                    <td><?php echo $measurement['weight_kg'] ?? '-'; ?></td>
                    <td><?php echo $measurement['height_cm'] ?? '-'; ?></td>
                    <td><?php echo $measurement['chest_cm'] ?? '-'; ?></td>
                    <td><?php echo $measurement['waist_cm'] ?? '-'; ?></td>
                    <td><?php echo $measurement['hips_cm'] ?? '-'; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $measurement['id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $measurement['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>