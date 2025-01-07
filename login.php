<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: users.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'fundas');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate admin credentials
    $sql = "SELECT * FROM users WHERE email='$username' and is_admin='1'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: users.php');
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Admin Login</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
</body>
</html>
