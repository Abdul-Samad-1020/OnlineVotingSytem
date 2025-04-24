<?php
session_start();
require_once "../config/database.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Check if election ID is provided
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: dashboard.php");
    exit;
}

$election_id = $_GET["id"];

// Get election details
$sql = "SELECT * FROM elections WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":id", $election_id, PDO::PARAM_INT);
$stmt->execute();

if($stmt->rowCount() != 1){
    header("location: dashboard.php");
    exit;
}

$election = $stmt->fetch();

// Get voting results
$sql = "SELECT 
            c.id, 
            c.name, 
            c.position, 
            c.photo, 
            COUNT(v.id) as vote_count 
        FROM 
            candidates c 
        LEFT JOIN 
            votes v ON c.id = v.candidate_id 
        WHERE 
            c.election_id = :election_id 
        GROUP BY 
            c.id 
        ORDER BY 
            vote_count DESC, 
            c.name ASC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();

// Get total votes
$sql = "SELECT COUNT(*) FROM votes WHERE election_id = :election_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
$stmt->execute();
$total_votes = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Voting System - Admin</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="elections.php">Manage Elections</a></li>
                    <li><a href="candidates.php">Manage Candidates</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <h2>Election Results: <?php echo htmlspecialchars($election["title"]); ?></h2>
            <p class="election-status">Status: 
                <span class="status-badge status-<?php echo strtolower($election["status"]); ?>">
                    <?php echo ucfirst($election["status"]); ?>
                </span>
            </p>
            <p class="election-dates">
                Start: <?php echo date("F j, Y, g:i a", strtotime($election["start_date"])); ?><br>
                End: <?php echo date("F j, Y, g:i a", strtotime($election["end_date"])); ?>
            </p>
            
            <div class="results-summary">
                <h3>Total Votes: <?php echo $total_votes; ?></h3>
            </div>
            
            <?php if(count($results) > 0): ?>
                <div class="results-container">
                    <div class="results-chart">
                        <canvas id="resultsChart"></canvas>
                    </div>
                    
                    <div class="results-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Position</th>
                                    <th>Votes</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $result): ?>
                                    <tr>
                                        <td>
                                            <div class="candidate-info">
                                                <img src="../assets/images/candidates/<?php echo htmlspecialchars($result["photo"]); ?>" alt="<?php echo htmlspecialchars($result["name"]); ?>" class="candidate-thumbnail">
                                                <span><?php echo htmlspecialchars($result["name"]); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($result["position"]); ?></td>
                                        <td><?php echo $result["vote_count"]; ?></td>
                                        <td>
                                            <?php 
                                                $percentage = ($total_votes > 0) ? round(($result["vote_count"] / $total_votes) * 100, 2) : 0;
                                                echo $percentage . "%";
                                            ?>
                                            <div class="progress-bar">
                                                <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="export-options">
                    <button id="printResults" class="btn btn-secondary">Print Results</button>
                    <button id="exportCSV" class="btn btn-secondary">Export as CSV</button>
                </div>
                
                <script>
                    // Chart.js implementation
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('resultsChart').getContext('2d');
                        
                        // Prepare data for chart
                        const labels = [<?php echo implode(', ', array_map(function($result) { return "'" . addslashes($result["name"]) . "'"; }, $results)); ?>];
                        const data = [<?php echo implode(', ', array_map(function($result) { return $result["vote_count"]; }, $results)); ?>];
                        
                        // Generate random colors
                        const backgroundColors = generateColors(<?php echo count($results); ?>);
                        
                        const chart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: data,
                                    backgroundColor: backgroundColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return `${label}: ${value} votes (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                        
                        // Print results
                        document.getElementById('printResults').addEventListener('click', function() {
                            window.print();
                        });
                        
                        // Export as CSV
                        document.getElementById('exportCSV').addEventListener('click', function() {
                            const rows = [
                                ['Candidate', 'Position', 'Votes', 'Percentage'],
                                <?php foreach($results as $result): ?>
                                [
                                    '<?php echo addslashes($result["name"]); ?>', 
                                    '<?php echo addslashes($result["position"]); ?>', 
                                    <?php echo $result["vote_count"]; ?>, 
                                    '<?php echo ($total_votes > 0) ? round(($result["vote_count"] / $total_votes) * 100, 2) : 0; ?>%'
                                ],
                                <?php endforeach; ?>
                            ];
                            
                            let csvContent = "data:text/csv;charset=utf-8,";
                            
                            rows.forEach(function(rowArray) {
                                const row = rowArray.join(",");
                                csvContent += row + "\r\n";
                            });
                            
                            const encodedUri = encodeURI(csvContent);
                            const link = document.createElement("a");
                            link.setAttribute("href", encodedUri);
                            link.setAttribute("download", "election_results_<?php echo $election_id; ?>.csv");
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        });
                        
                        // Generate random colors for chart
                        function generateColors(count) {
                            const colors = [];
                            for (let i = 0; i < count; i++) {
                                const hue = (i * 137) % 360; // Use golden angle approximation for better distribution
                                colors.push(`hsl(${hue}, 70%, 60%)`);
                            }
                            return colors;
                        }
                    });
                </script>
            <?php else: ?>
                <p>No candidates found for this election.</p>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Voting System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>