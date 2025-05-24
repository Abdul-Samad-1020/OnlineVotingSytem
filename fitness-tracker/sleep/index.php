<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$pageTitle = "Sleep Records";

// Get all sleep records for the user
$sleepRecords = [];
$stmt = $conn->prepare("SELECT *, TIMESTAMPDIFF(HOUR, start_time, end_time) AS duration_hours FROM sleep WHERE user_id = ? ORDER BY start_time DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $sleepRecords[] = $row;
}
$stmt->close();

include '../../includes/header.php';
?>

<h2>Your Sleep Records</h2>
<a href="add.php" class="button">Add New Sleep Record</a>

<?php if (empty($sleepRecords)): ?>
    <p>No sleep records found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration (hours)</th>
                <th>Quality</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sleepRecords as $record): ?>
                <tr>
                    <td><?php echo date('M j, Y H:i', strtotime($record['start_time'])); ?></td>
                    <td><?php echo date('M j, Y H:i', strtotime($record['end_time'])); ?></td>
                    <td><?php echo number_format($record['duration_hours'], 2); ?></td>
                    <td><?php echo str_repeat('★', $record['quality']) . str_repeat('☆', 5 - $record['quality']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $record['id']; ?>">Edit</a>
                        <a href="delete.php?id=<?php echo $record['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>