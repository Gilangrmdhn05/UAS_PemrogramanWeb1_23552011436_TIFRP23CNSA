<?php
if(!isset($_SESSION)){ session_start(); }
if($_SESSION['role']!='admin'){
    header("Location:../auth/login.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Warungku</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="../assets/css/admin.css" rel="stylesheet">
<!-- Bootstrap icons for nav -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<!-- Topbar -->
<div class="topbar">
  <div class="d-flex align-items-center gap-3">
    <button id="sidebarToggle" class="btn btn-light" title="Toggle sidebar"><i class="bi bi-list"></i></button>     
  </div>
  <div class="d-flex align-items-center gap-3">
    <div class="d-none d-md-block text-muted-small"><strong>Administrator</strong></div>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px">W</div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item text-danger" href="../auth/logout.php">Keluar</a></li>
      </ul>
    </div>
  </div>
</div>
