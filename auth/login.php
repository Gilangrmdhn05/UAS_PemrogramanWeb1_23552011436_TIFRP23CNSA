<?php
session_start();
include "../config/database.php";

$error = "";

/* ================= LOGIN ================= */
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $q = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($q) == 1) {
        $u = mysqli_fetch_assoc($q);
        if (password_verify($pass, $u['password'])) {
            $_SESSION['id_user'] = $u['id_user'];
            $_SESSION['nama']    = $u['nama'];
            $_SESSION['role']    = $u['role'];
            header("Location: ../index.php");
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Email tidak ditemukan";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Warungku</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    height: 100%;
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.auth-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}

.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 420px;
    width: 100%;
    overflow: hidden;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-header {
    background: linear-gradient(135deg, #08833f 0%, #0baf62 100%);
    color: White;
    padding: 30px 20px;
    text-align: center;
}

.auth-header h2 {
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 5px;
}

.auth-header p {
    font-size: 0.95rem;
    opacity: 0.9;
}

.auth-body {
    padding: 40px;
}

.form-group-custom {
    margin-bottom: 20px;
}

.form-control {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control::placeholder {
    color: #999;
}

.btn-auth {
    background: linear-gradient(135deg, #08833f 0%, #0baf62 100%);
    border: none;
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
    font-size: 1rem;
    width: 100%;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-auth:active {
    transform: translateY(0);
}

.auth-link {
    text-align: center;
    margin-top: 20px;
    color: #666;
}

.auth-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.auth-link a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.alert {
    border-radius: 10px;
    border: none;
    margin-bottom: 20px;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-danger {
    background-color: #ffe5e5;
    color: #c41e3a;
}

.alert-success {
    background-color: #e5f9e5;
    color: #155724;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.divider {
    position: relative;
    text-align: center;
    margin: 25px 0;
    color: #999;
    font-size: 0.9rem;
}

.icon-header {
    font-size: 2.5rem;
    margin-bottom: 10px;
}
</style>
</head>

<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="icon-header">🛒</div>
            <h2>Warungku</h2>
            <p>Pesan barang favorit Anda</p>
        </div>

        <div class="auth-body">
            <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <strong>⚠️ Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
            <div class="alert alert-success" role="alert">
                <strong>✓ Sukses:</strong> <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <!-- ================= FORM LOGIN ================= -->
            <form method="POST" id="loginForm">
                <h5 class="mb-4" style="color: #333; font-weight: 600;">Masuk ke Akun Anda</h5>
                
                <div class="form-group-custom">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                </div>

                <div class="form-group-custom">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button name="login" class="btn btn-auth">Masuk</button>

                <div class="auth-link">
                    Belum punya akun? 
                    <a href="register.php">Daftar di sini</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showLogin(){
    document.getElementById('registerForm').style.display='none';
    document.getElementById('loginForm').style.display='block';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>