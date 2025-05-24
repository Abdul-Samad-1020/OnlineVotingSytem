<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = "Dashboard";

// Get recent activities
$activities = [];
$stmt = $conn->prepare("SELECT type, duration_minutes, activity_date FROM activities WHERE user_id = ? ORDER BY activity_date DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}
$stmt->close();

// Get recent workouts
$workouts = [];
$stmt = $conn->prepare("SELECT name, workout_date FROM workouts WHERE user_id = ? ORDER BY workout_date DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $workouts[] = $row;
}
$stmt->close();

// Get recent meals
$meals = [];
$stmt = $conn->prepare("SELECT name, calories, meal_date FROM meals WHERE user_id = ? ORDER BY meal_date DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}
$stmt->close();

include 'includes/header.php';
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3>Recent Activities</h3>
        <?php if (empty($activities)): ?>
            <p>No activities logged yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($activities as $activity): ?>
                    <li>
                        <?php echo htmlspecialchars($activity['type']); ?> - 
                        <?php echo $activity['duration_minutes']; ?> min
                        <small><?php echo date('M j', strtotime($activity['activity_date'])); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="activities/add.php" class="button">Add Activity</a>
    </div>

    <div class="dashboard-card">
        <h3>Recent Workouts</h3>
        <?php if (empty($workouts)): ?>
            <p>No workouts logged yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($workouts as $workout): ?>
                    <li>
                        <?php echo htmlspecialchars($workout['name']); ?>
                        <small><?php echo date('M j', strtotime($workout['workout_date'])); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="workouts/add.php" class="button">Add Workout</a>
    </div>

    <div class="dashboard-card">
        <h3>Recent Meals</h3>
        <?php if (empty($meals)): ?>
            <p>No meals logged yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($meals as $meal): ?>
                    <li>
                        <?php echo htmlspecialchars($meal['name']); ?> - 
                        <?php echo $meal['calories']; ?> kcal
                        <small><?php echo date('M j', strtotime($meal['meal_date'])); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="meals/add.php" class="button">Add Meal</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>