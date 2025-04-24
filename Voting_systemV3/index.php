<?php
session_start();

// Check if the user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if($_SESSION["role"] === "admin") {
        header("location: admin/dashboard.php");
    } else {
        header("location: voter/dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="home-header">
            <h1>Online Voting System</h1>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="auth/login.php">Login</a></li>
                    <li><a href="auth/register.php">Register</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <section class="hero">
                <div class="hero-content">
                    <h2>Secure Online Voting</h2>
                    <p>Vote securely from anywhere, anytime. Our system ensures your vote is counted and kept confidential.</p>
                    <div class="hero-buttons">
                        <a href="auth/login.php" class="btn btn-primary">Login to Vote</a>
                        <a href="auth/register.php" class="btn btn-secondary">Register Now</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/images/voting-illustration.png" alt="Online Voting Illustration">
                </div>
            </section>
            
            <section class="features">
                <h2>Key Features</h2>
                <div class="feature-cards">
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure Voting</h3>
                        <p>Your vote is encrypted and securely stored in our database.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⏱️</div>
                        <h3>Vote Anytime</h3>
                        <p>Vote from anywhere during the election period.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Real-time Results</h3>
                        <p>Administrators can view real-time voting statistics.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h3>One Vote Policy</h3>
                        <p>Our system ensures each voter can only vote once per election.</p>
                    </div>
                </div>
            </section>
            
            <section class="how-it-works">
                <h2>How It Works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Register</h3>
                        <p>Create an account with your details.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Login</h3>
                        <p>Sign in to your account securely.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>View Elections</h3>
                        <p>Browse active elections you're eligible to vote in.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <h3>Cast Your Vote</h3>
                        <p>Select your preferred candidate and submit your vote.</p>
                    </div>
                </div>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Voting System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>