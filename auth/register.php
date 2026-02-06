<?php
include "../config/database.php";
session_start();

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi nama
    if (empty($nama)) {
        $error = "Nama lengkap harus diisi!";
    }
    // Validasi email
    else if (empty($email)) {
        $error = "Email harus diisi!";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    }
    // Validasi password panjang
    else if (empty($password)) {
        $error = "Password harus diisi!";
    }
    else if (strlen($password) < 6) {
        $error = "Password harus minimal 6 karakter!";
    }
    // Validasi konfirmasi password
    else if (empty($password_confirm)) {
        $error = "Konfirmasi password harus diisi!";
    }
    else if ($password !== $password_confirm) {
        $error = "Password dan konfirmasi password tidak sesuai!";
    }
    // Check jika email sudah terdaftar
    else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if(mysqli_num_rows($check) > 0){
            $error = "Email sudah terdaftar!";
        } else {
            // Hash password dan insert data
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $result = mysqli_query($conn,"
                INSERT INTO users (id_user, nama, email, password, role, created_at) 
                VALUES (null, '$nama', '$email', '$password_hash', 'user', NOW())
            ");
            if($result){
                $success = "Registrasi berhasil! Silakan login dengan akun Anda";
            } else {
                $error = "Gagal mendaftar. Silakan coba lagi!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Warungku</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
    color: white;
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
    box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.1);
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

.icon-header {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.feature-list {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    color: #666;
    font-size: 0.9rem;
}

.feature-item::before {
    content: "✓";
    color: #f5576c;
    font-weight: bold;
    margin-right: 10px;
    font-size: 1.1rem;
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

            <?php if($success): ?>
            <div class="alert alert-success" role="alert">
                <strong>✓ Sukses:</strong> <?= htmlspecialchars($success) ?><br>
                <a href="login.php" style="color: #155724; font-weight: 600;">Klik di sini untuk login</a>
            </div>
            <?php endif; ?>

            <!-- ================= FORM REGISTER ================= -->
            <form method="POST" id="formRegister">
                <h5 class="mb-4" style="color: #333; font-weight: 600;">Buat Akun Baru</h5>
                
                <div class="form-group-custom">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required>
                    <small class="text-muted d-block mt-1">✓ Masukkan nama lengkap Anda</small>
                </div>

                <div class="form-group-custom">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    <small class="text-muted d-block mt-1">✓ Email yang valid dan belum terdaftar</small>
                </div>

                <div class="form-group-custom">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Buat password yang kuat" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">✓ Minimal 6 karakter</small>
                    <div id="passwordStrength" style="margin-top: 8px;">
                        <div style="height: 4px; background: #e0e0e0; border-radius: 2px;">
                            <div id="strengthBar" style="height: 100%; background: #ccc; border-radius: 2px; width: 0%; transition: all 0.3s;"></div>
                        </div>
                        <small id="strengthText" class="text-muted d-block mt-1"></small>
                    </div>
                </div>

                <div class="form-group-custom">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Ketik ulang password Anda" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirm">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">✓ Pastikan password sama</small>
                    <div id="matchIndicator" style="margin-top: 8px;"></div>
                </div>

                <button name="register" type="submit" class="btn btn-auth">Daftar Sekarang</button>

                <div class="auth-link">
                    Sudah punya akun? 
                    <a href="login.php">Masuk di sini</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });

    document.getElementById('toggleConfirm').addEventListener('click', function() {
        const confirmInput = document.getElementById('password_confirm');
        const icon = this.querySelector('i');
        
        if (confirmInput.type === 'password') {
            confirmInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            confirmInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });

    // Password Strength Indicator
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let strengthLabel = '';
        let strengthColor = '';

        if (password.length >= 6) strength += 25;
        if (password.length >= 8) strength += 25;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 10;

        // Limit strength to 100
        strength = Math.min(strength, 100);

        if (password.length === 0) {
            strengthLabel = '';
            strengthColor = '#ccc';
            strength = 0;
        } else if (strength < 50) {
            strengthLabel = '❌ Lemah';
            strengthColor = '#ff4757';
        } else if (strength < 75) {
            strengthLabel = '⚠️ Sedang';
            strengthColor = '#ffc107';
        } else {
            strengthLabel = '✓ Kuat';
            strengthColor = '#28a745';
        }

        strengthBar.style.width = strength + '%';
        strengthBar.style.background = strengthColor;
        strengthText.textContent = strengthLabel;
        strengthText.style.color = strengthColor;

        // Check password match
        checkPasswordMatch();
    });

    // Password Match Indicator
    const confirmInput = document.getElementById('password_confirm');
    const matchIndicator = document.getElementById('matchIndicator');

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (confirm.length === 0) {
            matchIndicator.innerHTML = '';
            return;
        }

        if (password === confirm && password.length >= 6) {
            matchIndicator.innerHTML = '<small style="color: #28a745;"><i class="bi bi-check-circle-fill"></i> Password sesuai</small>';
        } else if (password === confirm) {
            matchIndicator.innerHTML = '<small style="color: #ffc107;"><i class="bi bi-info-circle-fill"></i> Password harus minimal 6 karakter</small>';
        } else {
            matchIndicator.innerHTML = '<small style="color: #ff4757;"><i class="bi bi-x-circle-fill"></i> Password tidak sesuai</small>';
        }
    }

    confirmInput.addEventListener('input', checkPasswordMatch);

    // Form Validation
    document.getElementById('formRegister').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (password.length < 6) {
            e.preventDefault();
            alert('Password harus minimal 6 karakter!');
            return;
        }

        if (password !== confirm) {
            e.preventDefault();
            alert('Password dan konfirmasi password tidak sesuai!');
            return;
        }
    });
</script>
