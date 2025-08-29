<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        require_once "db_con.php";
        $username = $_POST['username'];

        $con->query("SET SQL_SAFE_UPDATES = 0");

        $query = "DELETE FROM users WHERE username = ?";
        $stmt = $con->prepare($query);

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $con->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $con->query("SET SQL_SAFE_UPDATES = 1");

        if ($stmt->affected_rows > 0) {
            session_destroy();
            header("Location: ../login.php?msg=User+deleted+successfully!");
            exit();
        } else {
            throw new Exception("No user found with the given username.");
        }

        $stmt->close();
        $con->close();
    } catch (Exception $ex) {
        echo "<script>alert('Error: " . htmlspecialchars($ex->getMessage()) . "');</script>";
        header("Refresh: 0; url=../login.php");
        die();
    }
} else {
    header("Location: ../login.php");
    die();
}
?>