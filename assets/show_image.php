<?php
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("No user ID specified.");
}

$user_id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['profile_image']) {
    header("Content-Type: image/png");
    echo $user['profile_image'];
} else {
    header("Content-Type: image/jpg");
    echo file_get_contents('./placeholder.jpg');
}
?>
