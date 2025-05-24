<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

// Get activity ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Delete activity
$stmt = $conn->prepare("DELETE FROM activities WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

header("Location: index.php?deleted=1");
exit();
?>