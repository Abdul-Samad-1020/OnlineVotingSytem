<?php
session_start();
require_once "../config/database.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "voter"){
    header("location: ../auth/login.php");
    exit;
}

// Get active elections
$sql = "SELECT * FROM elections WHERE status = 'active' AND NOW() BETWEEN start_date AND end_date ORDER BY end_date ASC";
$active_elections = $pdo->query($sql)->fetchAll();

// Get user's voting history
$sql = "SELECT v.*, e.title as election_title, c.name as candidate_name 
        FROM votes v 
        JOIN elections e ON v.election_id = e.id 
        JOIN candidates c ON v.candidate_id = c.id 
        WHERE v.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();
$voting_history = $stmt->fetchAll();

// Get elections the user has already voted in
$voted_elections = array();
foreach($voting_history as $vote) {
    $voted_elections[] = $vote['election_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Voting System</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            
            <section class="elections">
                <h3>Active Elections</h3>
                <?php if(count($active_elections) > 0): ?>
                    <div class="election-list">
                        <?php foreach($active_elections as $election): ?>
                            <div class="election-card">
                                <h4><?php echo htmlspecialchars($election["title"]); ?></h4>
                                <p><?php echo htmlspecialchars($election["description"]); ?></p>
                                <p>Ends: <?php echo date("F j, Y, g:i a", strtotime($election["end_date"])); ?></p>
                                
                                <?php if(in_array($election["id"], $voted_elections)): ?>
                                    <div class="voted-badge">You have voted</div>
                                <?php else: ?>
                                    <a href="vote.php?election=<?php echo $election["id"]; ?>" class="btn btn-primary">Vote Now</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No active elections at the moment.</p>
                <?php endif; ?>
            </section>
            
            <section class="voting-history">
                <h3>Your Voting History</h3>
                <?php if(count($voting_history) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Election</th>
                                <th>Candidate</th>
                                <th>Voted On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($voting_history as $vote): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vote["election_title"]); ?></td>
                                    <td><?php echo htmlspecialchars($vote["candidate_name"]); ?></td>
                                    <td><?php echo date("F j, Y, g:i a", strtotime($vote["voted_at"])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You haven't voted in any elections yet.</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Voting System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>