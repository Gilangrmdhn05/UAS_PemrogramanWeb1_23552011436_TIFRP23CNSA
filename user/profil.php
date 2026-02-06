<?php
session_start();
include "../config/database.php";

if ($_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'"));

// Get cart count
$cart_count = 0;
if(isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach($_SESSION['keranjang'] as $item) {
        $cart_count += $item['qty'];
    }
}

$success_message = '';
$error_message = '';

// Check if form submitted
if (isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']) ?? ''; 
    $email = mysqli_real_escape_string($conn, $_POST['email']) ?? '';
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']) ?? '';
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']) ?? '';
    $kota = mysqli_real_escape_string($conn, $_POST['kota']) ?? '';
    $kode_pos = mysqli_real_escape_string($conn, $_POST['kode_pos']) ?? '';
    
    $foto_query_part = "";
    
    // Handle Foto Profil Upload
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['foto_profil']['name'];
        $filesize = $_FILES['foto_profil']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($filesize < 2000000) { // 2MB limit
                $new_filename = "profil_" . $id_user . "_" . time() . "." . $ext;
                $upload_path = "../assets/images/profil/";
                
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_path . $new_filename)) {
                    // Delete old photo if it's not default
                    if ($user['foto_profil'] != 'default.png' && !empty($user['foto_profil'])) {
                        if (file_exists($upload_path . $user['foto_profil'])) {
                            unlink($upload_path . $user['foto_profil']);
                        }
                    }
                    $foto_query_part = ", foto_profil='$new_filename'";
                } else {
                    $error_message = "Gagal mengupload file.";
                }
            } else {
                $error_message = "Ukuran file terlalu besar (Maks 2MB).";
            }
        } else {
            $error_message = "Format file tidak didukung (JPG, JPEG, PNG, GIF).";
        }
    }
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid!";
    } else if ($email != $user['email']) {
        // Check jika email sudah digunakan
        $check_email = mysqli_query($conn, "SELECT id_user FROM users WHERE email='$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error_message = "Email sudah digunakan!";
        } else {
            // Update data user
            $update_query = "UPDATE users SET nama='$nama', email='$email', no_hp='$no_hp', alamat='$alamat', kota='$kota', kode_pos='$kode_pos' $foto_query_part WHERE id_user='$id_user'";
            if (mysqli_query($conn, $update_query)) {
                $success_message = "Data profil berhasil diperbarui!";
                $_SESSION['nama'] = $nama;
                // Refresh user data
                $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'"));
            } else {
                $error_message = "Gagal mengupdate data. Silakan coba lagi.";
            }
        }
    } else {
        // Email tidak berubah, langsung update
        $update_query = "UPDATE users SET nama='$nama', email='$email', no_hp='$no_hp', alamat='$alamat', kota='$kota', kode_pos='$kode_pos' $foto_query_part WHERE id_user='$id_user'";
        if (mysqli_query($conn, $update_query)) {
            $success_message = "Data profil berhasil diperbarui!";
            $_SESSION['nama'] = $nama;
            // Refresh user data
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'"));
        } else {
            $error_message = "Gagal mengupdate data. Silakan coba lagi.";
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $password_lama = $_POST['password_lama'] ?? '' ;
    $password_baru = $_POST['password_baru'] ?? '' ;
    $password_konfirmasi = $_POST['password_konfirmasi'] ?? '' ;
    
    // Verify old password
    if (!password_verify($password_lama, $user['password'])) {
        $error_message = "Password lama tidak sesuai!";
    } 
    else if (strlen($password_baru) < 6) {
        $error_message = "Password baru harus minimal 6 karakter!";
    } 
    else if ($password_baru != $password_konfirmasi) {
        $error_message = "Password baru dan konfirmasi tidak sesuai!";
    } 
    else {
        $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
        $update_pass = "UPDATE users SET password='$password_hash' WHERE id_user='$id_user'";
        if (mysqli_query($conn, $update_pass)) {
            $success_message = "Password berhasil diubah!";
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'"));
        } else {
            $error_message = "Gagal mengubah password. Silakan coba lagi.";
        }
    }
}

$page = isset($_GET['page']) ? $_GET['page'] : 'profil';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Warungku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: #2d3436;
        }
        
        .navbar {
            background: linear-gradient(135deg, #198754 0%, #198754 100%);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .badge-cart {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff6b6b;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 3rem;
        }
        
        .profile-avatar i {
            color: white;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h5 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .form-group label {
            font-weight: 500;
            color: #2d3436;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 30px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 6px;
            border: none;
            margin-bottom: 20px;
        }
        
        .sidebar {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
            position: sticky;
            top: 20px;
        }
        
        .sidebar a {
            display: block;
            padding: 12px 20px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #2d3436;
            border-radius: 6px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #f0f0f0;
            color: #667eea;
            border-left-color: #667eea;
        }
        
        .footer {
            background: #2d3436;
            color: white;
            padding: 30px;
            text-align: center;
            margin-top: 50px;
        }
        
        .nav-icon {
            margin-right: 10px;
            width: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="home.php"><i class="bi bi-shop"></i> Warungku</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php"><i class="bi bi-house"></i> Home</a>
                    </li>
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="keranjang.php"><i class="bi bi-cart"></i> Keranjang <?php if($cart_count > 0): ?><span class="badge-cart"><?php echo $cart_count; ?></span><?php endif; ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesanan.php"><i class="bi bi-box2"></i> Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profil.php"><i class="bi bi-person-circle"></i> Profil</a>
                    </li>
                    
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4 mb-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div class="profile-avatar" style="width: 80px; height: 80px; margin: 0 auto;">
                            <?php 
                            $foto_path = "../assets/images/profil/" . ($user['foto_profil'] ?? 'default.png');
                            // Cek apakah file ada, jika tidak gunakan default
                            if ($user['foto_profil'] == 'default.png' || !file_exists($foto_path)) {
                                echo '<i class="bi bi-person-fill"></i>';
                            } else {
                                echo '<img src="'.$foto_path.'" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">';
                            }
                            ?>
                        </div>
                        <h6 style="margin-top: 10px; color: #2d3436;"><?php echo htmlspecialchars($user['nama']); ?></h6>
                        <small style="color: #999;"><?php echo htmlspecialchars($user['email']); ?></small>
                    </div>
                    <hr>
                    <a href="profil.php" class="<?= $page=='profil'?'active':'' ?>"><i class="bi bi-person nav-icon"></i>Profil Saya</a>
                    <a href="?page=pengaturan" class="<?= $page=='pengaturan'?'active':'' ?>"><i class="bi bi-gear nav-icon"></i>Pengaturan</a>
                    <a href="home.php"><i class="bi bi-house nav-icon"></i>Home</a>
                    <a href="pesanan.php"><i class="bi bi-box2 nav-icon"></i>Pesanan Saya</a>
                    <a href="../auth/logout.php"><i class="bi bi-box-arrow-right nav-icon"></i>Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Alert Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($page == 'profil'): ?>
                <!-- VIEW PROFIL (READ ONLY) -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php 
                            $foto_path = "../assets/images/profil/" . ($user['foto_profil'] ?? 'default.png');
                            if ($user['foto_profil'] == 'default.png' || !file_exists($foto_path)) {
                                echo '<i class="bi bi-person-fill"></i>';
                            } else {
                                echo '<img src="'.$foto_path.'" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">';
                            }
                            ?>
                        </div>
                        <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <small class="text-secondary">Member sejak <?php echo date('d F Y', strtotime($user['created_at'])); ?></small>
                    </div>

                    <div class="row px-4 pb-4">
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-secondary small mb-1">Nomor HP</label>
                            <div class="fs-5 text-dark"><?= !empty($user['no_hp']) ? htmlspecialchars($user['no_hp']) : '-' ?></div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="fw-bold text-secondary small mb-1">Kota & Kode Pos</label>
                            <div class="fs-5 text-dark">
                                <?= !empty($user['kota']) ? htmlspecialchars($user['kota']) : '-' ?> 
                                <?= !empty($user['kode_pos']) ? '('.htmlspecialchars($user['kode_pos']).')' : '' ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold text-secondary small mb-1">Alamat Lengkap</label>
                            <div class="fs-5 text-dark"><?= !empty($user['alamat']) ? nl2br(htmlspecialchars($user['alamat'])) : '-' ?></div>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <a href="?page=pengaturan" class="btn btn-primary px-4"><i class="bi bi-pencil-square"></i> Edit Profil</a>
                    </div>
                </div>
                <?php elseif($page == 'pengaturan'): ?>
                <!-- Profile Information Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php 
                            $foto_path = "../assets/images/profil/" . ($user['foto_profil'] ?? 'default.png');
                            if ($user['foto_profil'] == 'default.png' || !file_exists($foto_path)) {
                                echo '<i class="bi bi-person-fill"></i>';
                            } else {
                                echo '<img src="'.$foto_path.'" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">';
                            }
                            ?>
                        </div>
                        <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <small class="text-secondary">Member sejak <?php echo date('d F Y', strtotime($user['created_at'])); ?></small>
                    </div>

                    <!-- Edit Profile Form -->
                    <div class="form-section">
                        <h5><i class="bi bi-pencil-square"></i> Edit Data Pribadi</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="no_hp">Nomor HP</label>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>" placeholder="Contoh: 08123456789">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kota">Kota</label>
                                        <input type="text" class="form-control" id="kota" name="kota" value="<?php echo htmlspecialchars($user['kota'] ?? ''); ?>" placeholder="Contoh: Jakarta">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kode_pos">Kode Pos</label>
                                        <input type="text" class="form-control" id="kode_pos" name="kode_pos" value="<?php echo htmlspecialchars($user['kode_pos'] ?? ''); ?>" placeholder="Contoh: 40123">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="alamat">Alamat Lengkap</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Jalan, Nomor, RT/RW, Kelurahan, Kecamatan"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="foto_profil">Foto Profil</label>
                                <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                            </div>

                            <button type="submit" name="update_profil" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>

                    <hr>

                    <!-- Change Password Form -->
                    <div class="form-section">
                        <h5><i class="bi bi-lock"></i> Ubah Password</h5>
                        <form method="POST">
                            <div class="form-group">
                                <label for="password_lama">Password Lama</label>
                                <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_baru">Password Baru</label>
                                        <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_konfirmasi">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" id="password_konfirmasi" name="password_konfirmasi" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="bi bi-lock-fill"></i> Ubah Password
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Copyright by 23552011436_GILANG RAMADHAN HERDIAN_TIF RP 23 CNS A_UAS WEB 1</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
