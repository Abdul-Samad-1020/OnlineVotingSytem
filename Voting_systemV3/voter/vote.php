<?php
session_start();
require_once "../config/database.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "voter"){
    header("location: ../auth/login.php");
    exit;
}

// Check if election ID is provided
if(!isset($_GET["election"]) || empty($_GET["election"])){
    header("location: dashboard.php");
    exit;
}

$election_id = $_GET["election"];
$user_id = $_SESSION["id"];
$error_message = "";
$success_message = "";

// Check if the election exists and is active
$sql = "SELECT * FROM elections WHERE id = :id AND status = 'active' AND NOW() BETWEEN start_date AND end_date";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $election_id, PDO::PARAM_INT);
$stmt->execute();

if($stmt->rowCount() != 1){
    header("location: dashboard.php");
    exit;
}

$election = $stmt->fetch();

// Check if user has already voted in this election
$sql = "SELECT * FROM votes WHERE user_id = :user_id AND election_id = :election_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
$stmt->execute();

if($stmt->rowCount() > 0){
    header("location: dashboard.php");
    exit;
}

// Get candidates for this election
$sql = "SELECT * FROM candidates WHERE election_id = :election_id ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
$stmt->execute();
$candidates = $stmt->fetchAll();

// Process vote submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["candidate_id"])){
    $candidate_id = $_POST["candidate_id"];
    
    // Verify candidate exists and belongs to this election
    $sql = "SELECT * FROM candidates WHERE id = :id AND election_id = :election_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $candidate_id, PDO::PARAM_INT);
    $stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if($stmt->rowCount() == 1){
        // Record the vote
        $sql = "INSERT INTO votes (user_id, candidate_id, election_id) VALUES (:user_id, :candidate_id, :election_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":candidate_id", $candidate_id, PDO::PARAM_INT);
        $stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            $success_message = "Your vote has been recorded successfully!";
            // Redirect after a short delay
            header("refresh:2;url=dashboard.php");
        } catch(PDOException $e) {
            if($e->getCode() == 23000) { // Duplicate entry error
                $error_message = "You have already voted in this election.";
            } else {
                $error_message = "An error occurred while recording your vote. Please try again.";
            }
        }
    } else {
        $error_message = "Invalid candidate selection.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Voting System</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <h2>Vote: <?php echo htmlspecialchars($election["title"]); ?></h2>
            <p class="election-description"><?php echo htmlspecialchars($election["description"]); ?></p>
            
            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php else: ?>
                <div class="voting-instructions">
                    <h3>Instructions</h3>
                    <p>Select one candidate and click "Submit Vote". You can only vote once in this election.</p>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?election=" . $election_id); ?>" class="vote-form">
                    <div class="candidate-list">
                        <?php foreach($candidates as $candidate): ?>
                            <div class="candidate-card">
                                <input type="radio" name="candidate_id" id="candidate_<?php echo $candidate["id"]; ?>" value="<?php echo $candidate["id"]; ?>" required>
                                <label for="candidate_<?php echo $candidate["id"]; ?>">
                                    <div class="candidate-photo">
                                        <img src="../assets/images/candidates/<?php echo htmlspecialchars($candidate["photo"]); ?>" alt="<?php echo htmlspecialchars($candidate["name"]); ?>">
                                    </div>
                                    <div class="candidate-info">
                                        <h4><?php echo htmlspecialchars($candidate["name"]); ?></h4>
                                        <p class="candidate-position"><?php echo htmlspecialchars($candidate["position"]); ?></p>
                                        <p class="candidate-bio"><?php echo htmlspecialchars($candidate["bio"]); ?></p>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Submit Vote</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Voting System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>