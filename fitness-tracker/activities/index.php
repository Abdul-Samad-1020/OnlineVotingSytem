<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Activities";

// Get all activities for the user
$activities = [];
$stmt = $conn->prepare("SELECT * FROM activities WHERE user_id = ? ORDER BY activity_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}
$stmt->close();

include '../../includes/header.php';
?>

<h2>Your Activities</h2>
<a href="add.php" class="button">Add New Activity</a>

<?php if (empty($activities)): ?>
    <p>No activities found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Duration (min)</th>
                <th>Distance (km)</th>
                <th>Calories</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?php echo htmlspecialchars($activity['type']); ?></td>
                    <td><?php echo $activity['duration_minutes']; ?></td>
                    <td><?php echo $activity['distance_km'] ?? '-'; ?></td>
                    <td><?php echo $activity['calories'] ?? '-'; ?></td>
                    <td><?php echo date('M j, Y', strtotime($activity['activity_date'])); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $activity['id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $activity['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>