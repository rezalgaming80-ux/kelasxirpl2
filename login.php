<?php
require_once 'config.php';

if (isset($_SESSION['login_admin'])) {
    header("Location: admin.php");
    exit;
}

$error = false;
$pesan_error = "";

if (isset($_POST['login'])) {
    $username = bersihkan($_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login_admin'] = true;
            $_SESSION['admin_id']    = (int)$row['id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_name']  = $row['nama_lengkap'];
            header("Location: admin.php");
            exit;
        }
    }
    $error = true;
    $pesan_error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - XII RPL II</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: var(--bg-dark); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .login-box { background: var(--bg-card); padding: 2.5rem; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); border-top: 5px solid var(--accent); width: 100%; max-width: 400px; text-align: center; }
        .login-box h2 { color: #fff; margin-bottom: 2rem; font-size: 1.6rem; }
        .input-group { margin-bottom: 1.5rem; text-align: left; position: relative; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--accent); }
        .input-group input { width: 100%; padding: 12px 15px 12px 45px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; background: rgba(255,255,255,0.08); color: #fff; outline: none; transition: 0.3s; font-size: 0.95rem; }
        .input-group input:focus { background: rgba(255,255,255,0.15); border-color: var(--accent); box-shadow: 0 0 10px rgba(233, 69, 96, 0.4); }
        .btn-login { width: 100%; padding: 12px; background: var(--accent); color: #fff; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 1rem; }
        .btn-login:hover { background: #ff5b77; transform: translateY(-2px); }
        .error-msg { background: rgba(233, 69, 96, 0.2); border: 1px solid var(--accent); color: #ff6b6b; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fa-solid fa-user-shield"></i> Login Admin</h2>
        <?php if($error): ?>
            <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $pesan_error ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Username" required autofocus autocomplete="off">
            </div>
            <div class="input-group">
                <i class="fa-solid fa-key"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Masuk Dashboard</button>
        </form>
        <a href="index.php" style="display:inline-block; margin-top:20px; color:#aaa; font-size:0.9rem; text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Website</a>
    </div>
</body>
</html>
