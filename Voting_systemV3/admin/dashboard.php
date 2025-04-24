<?php
session_start();
require_once "../config/database.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Get elections statistics
$sql = "SELECT 
            e.id, 
            e.title, 
            e.status, 
            e.start_date, 
            e.end_date, 
            COUNT(DISTINCT c.id) as candidate_count, 
            COUNT(DISTINCT v.id) as vote_count 
        FROM 
            elections e 
        LEFT JOIN 
            candidates c ON e.id = c.election_id 
        LEFT JOIN 
            votes v ON e.id = v.election_id 
        GROUP BY 
            e.id 
        ORDER BY 
            CASE 
                WHEN e.status = 'active' THEN 1 
                WHEN e.status = 'upcoming' THEN 2 
                ELSE 3 
            END, 
            e.end_date DESC";
$elections = $pdo->query($sql)->fetchAll();

// Update election statuses
$sql = "UPDATE elections 
        SET status = 
            CASE 
                WHEN NOW() < start_date THEN 'upcoming' 
                WHEN NOW() BETWEEN start_date AND end_date THEN 'active' 
                ELSE 'completed' 
            END";
$pdo->exec($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Voting System - Admin</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="elections.php">Manage Elections</a></li>
                    <li><a href="candidates.php">Manage Candidates</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <h2>Admin Dashboard</h2>
            
            <div class="dashboard-actions">
                <a href="elections.php?action=add" class="btn btn-primary">Create New Election</a>
                <a href="candidates.php?action=add" class="btn btn-secondary">Add New Candidate</a>
            </div>
            
            <section class="elections-overview">
                <h3>Elections Overview</h3>
                <?php if(count($elections) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Candidates</th>
                                <th>Votes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($elections as $election): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($election["title"]); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($election["status"]); ?>">
                                            <?php echo ucfirst($election["status"]); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date("M j, Y, g:i a", strtotime($election["start_date"])); ?></td>
                                    <td><?php echo date("M j, Y, g:i a", strtotime($election["end_date"])); ?></td>
                                    <td><?php echo $election["candidate_count"]; ?></td>
                                    <td><?php echo $election["vote_count"]; ?></td>
                                    <td>
                                        <a href="elections.php?action=edit&id=<?php echo $election["id"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="results.php?id=<?php echo $election["id"]; ?>" class="btn btn-sm btn-secondary">Results</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No elections found. <a href="elections.php?action=add">Create your first election</a>.</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Voting System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>