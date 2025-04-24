<?php
session_start();
require_once "../config/database.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

$action = isset($_GET["action"]) ? $_GET["action"] : "list";
$candidate_id = isset($_GET["id"]) ? $_GET["id"] : 0;

// Initialize variables
$name = $position = $bio = $photo = $election_id = "";
$name_err = $position_err = $bio_err = $photo_err = $election_id_err = "";
$success_message = $error_message = "";

// Get all elections for dropdown
$sql = "SELECT id, title FROM elections ORDER BY 
        CASE 
            WHEN status = 'active' THEN 1 
            WHEN status = 'upcoming' THEN 2 
            ELSE 3 
        END, 
        end_date DESC";
$all_elections = $pdo->query($sql)->fetchAll();

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter a name.";
    } else{
        $name = trim($_POST["name"]);
    }
    
    // Validate position
    if(empty(trim($_POST["position"]))){
        $position_err = "Please enter a position.";
    } else{
        $position = trim($_POST["position"]);
    }
    
    // Validate bio
    if(empty(trim($_POST["bio"]))){
        $bio_err = "Please enter a bio.";
    } else{
        $bio = trim($_POST["bio"]);
    }
    
    // Validate election
    if(empty($_POST["election_id"])){
        $election_id_err = "Please select an election.";
    } else{
        $election_id = $_POST["election_id"];
    }
    
    // Handle photo upload
    if($action == "add" || !empty($_FILES["photo"]["name"])){
        $target_dir = "../assets/images/candidates/";
        
        // Create directory if it doesn't exist
        if(!file_exists($target_dir)){
            mkdir($target_dir, 0777, true);
        }
        
        if(!empty($_FILES["photo"]["name"])){
            $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
            $allowed_extensions = array("jpg", "jpeg", "png", "gif");
            
            if(!in_array($file_extension, $allowed_extensions)){
                $photo_err = "Only JPG, JPEG, PNG & GIF files are allowed.";
            } elseif($_FILES["photo"]["size"] > 5000000){ // 5MB max
                $photo_err = "File is too large. Maximum size is 5MB.";
            } else{
                $photo = uniqid() . "." . $file_extension;
                $target_file = $target_dir . $photo;
                
                if(!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)){
                    $photo_err = "There was an error uploading your file.";
                }
            }
        } elseif($action == "add"){
            $photo = "default.jpg"; // Default photo if none provided
        }
    } else{
        // Keep existing photo for edit
        $sql = "SELECT photo FROM candidates WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $candidate_id, PDO::PARAM_INT);
        $stmt->execute();
        $photo = $stmt->fetchColumn();
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($position_err) && empty($bio_err) && empty($photo_err) && empty($election_id_err)){
        if($action == "add"){
            // Prepare an insert statement
            $sql = "INSERT INTO candidates (name, position, bio, photo, election_id) VALUES (:name, :position, :bio, :photo, :election_id)";
            
            if($stmt = $pdo->prepare($sql)){
                // Bind parameters
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":position", $position, PDO::PARAM_STR);
                $stmt->bindParam(":bio", $bio, PDO::PARAM_STR);
                $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                $stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
                
                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    $success_message = "Candidate added successfully.";
                    // Clear form fields
                    $name = $position = $bio = "";
                } else{
                    $error_message = "Something went wrong. Please try again later.";
                }
                
                // Close statement
                unset($stmt);
            }
        } elseif($action == "edit" && $candidate_id > 0){
            // Prepare an update statement
            $sql = "UPDATE candidates SET name = :name, position = :position, bio = :bio, photo = :photo, election_id = :election_id WHERE id = :id";
            
            if($stmt = $pdo->prepare($sql)){
                // Bind parameters
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":position", $position, PDO::PARAM_STR);
                $stmt->bindParam(":bio", $bio, PDO::PARAM_STR);
                $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                $stmt->bindParam(":election_id", $election_id, PDO::PARAM_INT);
                $stmt->bindParam(":id", $candidate_id, PDO::PARAM_INT);
                
                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    $success_message = "Candidate updated successfully.";
                } else{
                    $error_message = "Something went wrong. Please try again later.";
                }
                
                // Close statement
                unset($stmt);
            }
        }
    }
}

// If editing, get candidate data
if($action == "edit" && $candidate_id > 0){
    $sql = "SELECT * FROM candidates WHERE id = :id";
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":id", $candidate_id, PDO::PARAM_INT);
        if($stmt->execute()){
            if($stmt->rowCount() == 1){
                $candidate = $stmt->fetch();
                $name = $candidate["name"];
                $position = $candidate["position"];
                $bio = $candidate["bio"];
                $photo = $candidate["photo"];
                $election_id = $candidate["election_id"];
            } else{
                // Candidate not found
                header("location: candidates.php");
                exit;
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
        unset($stmt);
    }
}

// Get all candidates for listing
if($action == "list"){
    $sql = "SELECT c.*, e.title as election_title, e.status as election_status 
            FROM candidates c 
            JOIN elections e ON c.election_id = e.id 
            ORDER BY e.status, e.end_date DESC, c.name ASC";
    $candidates = $pdo->query($sql)->fetchAll();
}

// Delete candidate
if($action == "delete" && $candidate_id > 0){
    // Check if there are votes for this candidate
    $sql = "SELECT COUNT(*) FROM votes WHERE candidate_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $candidate_id, PDO::PARAM_INT);
    $stmt->execute();
    $vote_count = $stmt->fetchColumn();
    
    if($vote_count > 0){
        $error_message = "Cannot delete candidate with existing votes.";
    } else{
        // Get photo filename before deleting
        $sql = "SELECT photo FROM candidates WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $candidate_id, PDO::PARAM_INT);
        $stmt->execute();
        $photo = $stmt->fetchColumn();
        
        // Delete the candidate
        $sql = "DELETE FROM candidates WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $candidate_id, PDO::PARAM_INT);
        if($stmt->execute()){
            // Delete photo file if not default
            if($photo != "default.jpg"){
                $photo_path = "../assets/images/candidates/" . $photo;
                if(file_exists($photo_path)){
                    unlink($photo_path);
                }
            }
            $success_message = "Candidate deleted successfully.";
        } else{
            $error_message = "Something went wrong. Please try again later.";
        }
    }
    
    // Redirect to list after deletion
    $action = "list";
    $sql = "SELECT c.*, e.title as election_title, e.status as election_status 
            FROM candidates c 
            JOIN elections e ON c.election_id = e.id 
            ORDER BY e.status, e.end_date DESC, c.name ASC";
    $candidates = $pdo->query($sql)->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Online Voting System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Voting System - Admin</h1>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="elections.php">Manage Elections</a></li>
                    <li><a href="candidates.php" class="active">Manage Candidates</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <?php if($action == "list"): ?>
                <h2>Manage Candidates</h2>
                
                <div class="action-bar">
                    <a href="candidates.php?action=add" class="btn btn-primary">Add New Candidate</a>
                </div>
                
                <?php if(!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if(count($candidates) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Election</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($candidates as $candidate): ?>
                                <tr>
                                    <td>
                                        <img src="../assets/images/candidates/<?php echo htmlspecialchars($candidate["photo"]); ?>" alt="<?php echo htmlspecialchars($candidate["name"]); ?>" class="candidate-thumbnail">
                                    </td>
                                    <td><?php echo htmlspecialchars($candidate["name"]); ?></td>
                                    <td><?php echo htmlspecialchars($candidate["position"]); ?></td>
                                    <td><?php echo htmlspecialchars($candidate["election_title"]); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($candidate["election_status"]); ?>">
                                            <?php echo ucfirst($candidate["election_status"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="candidates.php?action=edit&id=<?php echo $candidate["id"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <?php if($candidate["election_status"] == "upcoming"): ?>
                                            <a href="candidates.php?action=delete&id=<?php echo $candidate["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this candidate?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No candidates found. <a href="candidates.php?action=add">Add your first candidate</a>.</p>
                <?php endif; ?>
                
            <?php else: ?>
                <h2><?php echo $action == "add" ? "Add New Candidate" : "Edit Candidate"; ?></h2>
                
                <?php if(!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=" . $action . ($candidate_id > 0 ? "&id=" . $candidate_id : "")); ?>" method="post" enctype="multipart/form-data" class="form">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <span class="invalid-feedback"><?php echo $name_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" name="position" class="form-control <?php echo (!empty($position_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $position; ?>">
                        <span class="invalid-feedback"><?php echo $position_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" class="form-control <?php echo (!empty($bio_err)) ? 'is-invalid' : ''; ?>"><?php echo $bio; ?></textarea>
                        <span class="invalid-feedback"><?php echo $bio_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Election</label>
                        <select name="election_id" class="form-control <?php echo (!empty($election_id_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Election</option>
                            <?php foreach($all_elections as $election): ?>
                                <option value="<?php echo $election["id"]; ?>" <?php echo ($election_id == $election["id"]) ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($election["title"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="invalid-feedback"><?php echo $election_id_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Photo</label>
                        <?php if($action == "edit" && !empty($photo)): ?>
                            <div class="current-photo">
                                <img src="../assets/images/candidates/<?php echo htmlspecialchars($photo); ?>" alt="Current Photo" class="candidate-photo-preview">
                                <p>Current photo</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="photo" class="form-control <?php echo (!empty($photo_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $photo_err; ?></span>
                        <small class="form-text text-muted">Allowed formats: JPG, JPEG, PNG, GIF. Max size: 5MB.</small>
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="<?php echo $action == "add" ? "Add Candidate" : "Update Candidate"; ?>">
                        <a href="candidates.php" class="btn btn-secondary">Cancel</a>
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