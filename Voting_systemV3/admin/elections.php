<?php
session_start();
require_once "../config/database.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

$action = isset($_GET["action"]) ? $_GET["action"] : "list";
$election_id = isset($_GET["id"]) ? $_GET["id"] : 0;

// Initialize variables
$title = $description = $start_date = $end_date = "";
$title_err = $description_err = $start_date_err = $end_date_err = "";
$success_message = $error_message = "";

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate title
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";
    } else{
        $title = trim($_POST["title"]);
    }
    
    // Validate description
    if(empty(trim($_POST["description"]))){
        $description_err = "Please enter a description.";
    } else{
        $description = trim($_POST["description"]);
    }
    
    // Validate start date
    if(empty(trim($_POST["start_date"]))){
        $start_date_err = "Please enter a start date.";
    } else{
        $start_date = trim($_POST["start_date"]);
    }
    
    // Validate end date
    if(empty(trim($_POST["end_date"]))){
        $end_date_err = "Please enter an end date.";
    } else{
        $end_date = trim($_POST["end_date"]);
        
        // Check if end date is after start date
        if(!empty($start_date) && strtotime($end_date) <= strtotime($start_date)){
            $end_date_err = "End date must be after start date.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($title_err) && empty($description_err) && empty($start_date_err) && empty($end_date_err)){
        if($action == "add"){
            // Prepare an insert statement
            $sql = "INSERT INTO elections (title, description, start_date, end_date, status) VALUES (:title, :description, :start_date, :end_date, :status)";
            
            if($stmt = $pdo->prepare($sql)){
                // Set parameters
                $param_title = $title;
                $param_description = $description;
                $param_start_date = $start_date;
                $param_end_date = $end_date;
                
                // Determine status based on dates
                $now = date("Y-m-d H:i:s");
                if(strtotime($start_date) > strtotime($now)){
                    $param_status = "upcoming";
                } elseif(strtotime($end_date) < strtotime($now)){
                    $param_status = "completed";
                } else{
                    $param_status = "active";
                }
                
                // Bind parameters
                $stmt->bindParam(":title", $param_title, PDO::PARAM_STR);
                $stmt->bindParam(":description", $param_description, PDO::PARAM_STR);
                $stmt->bindParam(":start_date", $param_start_date, PDO::PARAM_STR);
                $stmt->bindParam(":end_date", $param_end_date, PDO::PARAM_STR);
                $stmt->bindParam(":status", $param_status, PDO::PARAM_STR);
                
                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    $success_message = "Election created successfully.";
                    // Clear form fields
                    $title = $description = $start_date = $end_date = "";
                } else{
                    $error_message = "Something went wrong. Please try again later.";
                }
                
                // Close statement
                unset($stmt);
            }
        } elseif($action == "edit" && $election_id > 0){
            // Prepare an update statement
            $sql = "UPDATE elections SET title = :title, description = :description, start_date = :start_date, end_date = :end_date, status = :status WHERE id = :id";
            
            if($stmt = $pdo->prepare($sql)){
                // Set parameters
                $param_title = $title;
                $param_description = $description;
                $param_start_date = $start_date;
                $param_end_date = $end_date;
                $param_id = $election_id;
                
                // Determine status based on dates
                $now = date("Y-m-d H:i:s");
                if(strtotime($start_date) > strtotime($now)){
                    $param_status = "upcoming";
                } elseif(strtotime($end_date) < strtotime($now)){
                    $param_status = "completed";
                } else{
                    $param_status = "active";
                }
                
                // Bind parameters
                $stmt->bindParam(":title", $param_title, PDO::PARAM_STR);
                $stmt->bindParam(":description", $param_description, PDO::PARAM_STR);
                $stmt->bindParam(":start_date", $param_start_date, PDO::PARAM_STR);
                $stmt->bindParam(":end_date", $param_end_date, PDO::PARAM_STR);
                $stmt->bindParam(":status", $param_status, PDO::PARAM_STR);
                $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);
                
                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    $success_message = "Election updated successfully.";
                } else{
                    $error_message = "Something went wrong. Please try again later.";
                }
                
                // Close statement
                unset($stmt);
            }
        }
    }
}

// If editing, get election data
if($action == "edit" && $election_id > 0){
    $sql = "SELECT * FROM elections WHERE id = :id";
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":id", $election_id, PDO::PARAM_INT);
        if($stmt->execute()){
            if($stmt->rowCount() == 1){
                $election = $stmt->fetch();
                $title = $election["title"];
                $description = $election["description"];
                $start_date = $election["start_date"];
                $end_date = $election["end_date"];
            } else{
                // Election not found
                header("location: dashboard.php");
                exit;
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
        unset($stmt);
    }
}

// Get all elections for listing
if($action == "list"){
    $sql = "SELECT * FROM elections ORDER BY 
            CASE 
                WHEN status = 'active' THEN 1 
                WHEN status = 'upcoming' THEN 2 
                ELSE 3 
            END, 
            end_date DESC";
    $elections = $pdo->query($sql)->fetchAll();
}

// Delete election
if($action == "delete" && $election_id > 0){
    // Check if there are votes for this election
    $sql = "SELECT COUNT(*) FROM votes WHERE election_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $election_id, PDO::PARAM_INT);
    $stmt->execute();
    $vote_count = $stmt->fetchColumn();
    
    if($vote_count > 0){
        $error_message = "Cannot delete election with existing votes.";
    } else{
        // Delete candidates first (due to foreign key constraint)
        $sql = "DELETE FROM candidates WHERE election_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $election_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Delete the election
        $sql = "DELETE FROM elections WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $election_id, PDO::PARAM_INT);
        if($stmt->execute()){
            $success_message = "Election deleted successfully.";
        } else{
            $error_message = "Something went wrong. Please try again later.";
        }
    }
    
    // Redirect to list after deletion
    $action = "list";
    $sql = "SELECT * FROM elections ORDER BY 
            CASE 
                WHEN status = 'active' THEN 1 
                WHEN status = 'upcoming' THEN 2 
                ELSE 3 
            END, 
            end_date DESC";
    $elections = $pdo->query($sql)->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Elections - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Voting System - Admin</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="elections.php" class="active">Manage Elections</a></li>
                    <li><a href="candidates.php">Manage Candidates</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <?php if($action == "list"): ?>
                <h2>Manage Elections</h2>
                
                <div class="action-bar">
                    <a href="elections.php?action=add" class="btn btn-primary">Create New Election</a>
                </div>
                
                <?php if(!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if(count($elections) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
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
                                    <td>
                                        <a href="elections.php?action=edit&id=<?php echo $election["id"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="results.php?id=<?php echo $election["id"]; ?>" class="btn btn-sm btn-secondary">Results</a>
                                        <?php if($election["status"] == "upcoming"): ?>
                                            <a href="elections.php?action=delete&id=<?php echo $election["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this election?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No elections found. <a href="elections.php?action=add">Create your first election</a>.</p>
                <?php endif; ?>
                
            <?php else: ?>
                <h2><?php echo $action == "add" ? "Create New Election" : "Edit Election"; ?></h2>
                
                <?php if(!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=" . $action . ($election_id > 0 ? "&id=" . $election_id : "")); ?>" method="post" class="form">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                        <span class="invalid-feedback"><?php echo $title_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>"><?php echo $description; ?></textarea>
                        <span class="invalid-feedback"><?php echo $description_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="datetime-local" name="start_date" class="form-control <?php echo (!empty($start_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $start_date ? date("Y-m-d\TH:i", strtotime($start_date)) : ''; ?>">
                        <span class="invalid-feedback"><?php echo $start_date_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="datetime-local" name="end_date" class="form-control <?php echo (!empty($end_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $end_date ? date("Y-m-d\TH:i", strtotime($end_date)) : ''; ?>">
                        <span class="invalid-feedback"><?php echo $end_date_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="<?php echo $action == "add" ? "Create Election" : "Update Election"; ?>">
                        <a href="elections.php" class="btn btn-secondary">Cancel</a>
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