<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'], $_GET['action'])) {
    $report_id = intval($_GET['id']);
    $action = $_GET['action'];
    $admin_id = $_SESSION["user_id"];

    $status = '';
    $msg = '';

    if ($action === 'warn') {
        $status = 'warned';
        $msg = 'Member has been warned.';
    } elseif ($action === 'suspend') {
        $status = 'suspended';
        $msg = 'Member has been suspended.';
    } elseif ($action === 'clear') {
        $status = 'cleared';
        $msg = 'Report has been cleared.';
    }

    if ($status !== '') {
        $stmt = $conn->prepare("UPDATE reports SET status=?, action_by=?, action_date=NOW() WHERE id=?");
        $stmt->bind_param("sii", $status, $admin_id, $report_id);
        $stmt->execute();
    }

    header("Location: reported_members.php?msg=" . urlencode($msg));
    exit;
}

header("Location: reported_members.php");
exit;
