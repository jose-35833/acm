<?php
session_start();
if(!isset($_SESSION['member_id'])){
    header("Location: ../login.php");
    exit;
}

// Include database connection
include_once '../includes/db.php'; // <-- make sure the path is correct
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Church Member Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: #f0f2f5; }
.card { border-radius: 12px; }
</style>
</head>
<body>
