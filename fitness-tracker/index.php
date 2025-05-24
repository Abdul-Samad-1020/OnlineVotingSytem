<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$pageTitle = "Home";

include 'includes/header.php';
?>
<style>
    /* Homepage Styles */
.hero {
    text-align: center;
    padding: 50px 20px;
    background: #f8f9fa;
    margin-bottom: 40px;
    border-radius: 4px;
}

.hero h1 {
    font-size: 2.5em;
    margin-bottom: 20px;
}

.hero p {
    font-size: 1.2em;
    max-width: 700px;
    margin: 0 auto 30px;
}

.auth-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.button.secondary {
    background: #6c757d;
}

.button.secondary:hover {
    background: #5a6268;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.feature {
    background: white;
    padding: 20px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.feature h3 {
    margin-top: 0;
    color: #2c3e50;
}
</style>
<div class="hero">
    <h1>Track Your Fitness Journey</h1>
    <p>Monitor your activities, workouts, meals, sleep, and body measurements all in one place.</p>
    
    <?php if (isLoggedIn()): ?>
        <a href="dashboard.php" class="button">Go to Dashboard</a>
    <?php else: ?>
        <div class="auth-buttons">
            <a href="login.php" class="button">Login</a>
            <a href="register.php" class="button secondary">Register</a>
        </div>
    <?php endif; ?>
</div>

<div class="features">
    <div class="feature">
        <h3>Activities</h3>
        <p>Track your runs, swims, cycling, and other exercises with duration, distance, and calories burned.</p>
    </div>
    <div class="feature">
        <h3>Workouts</h3>
        <p>Log your gym sessions and exercises with sets, reps, and weights.</p>
    </div>
    <div class="feature">
        <h3>Nutrition</h3>
        <p>Record your meals and track calories and macros (protein, carbs, fat).</p>
    </div>
    <div class="feature">
        <h3>Sleep</h3>
        <p>Monitor your sleep patterns and quality to optimize recovery.</p>
    </div>
    <div class="feature">
        <h3>Measurements</h3>
        <p>Track changes in your weight and body measurements over time.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>