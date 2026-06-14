<?php
session_start();
include 'conn.php';

if(isset($_POST['admin_login'])){
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];

  // Query khusus memeriksa tabel admin
  $query = mysqli_query($conn, "SELECT * FROM admin WHERE nama_admin='$username'");
  
  if(mysqli_num_rows($query) > 0){
    $data = mysqli_fetch_assoc($query);
    
    // Verifikasi password menggunakan HASH yang sudah aman kemarin
    if(password_verify($password, $data['password'])){
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_id']       = $data['id_admin'];
      $_SESSION['admin_name']     = $data['nama_admin'];
      $_SESSION['role']           = 'admin';
      
      header("Location: admin_dashboard.php");
      exit;
    }
  }
  $error = "Username atau Password Admin salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Admin Control Panel Login</title>
    <link rel="stylesheet" href="style.css"> <style>
        /* Sentuhan warna merah/gelap tegas khusus untuk menandakan area Administrator */
        .panel-left { background: linear-gradient(135deg, #0f172a, #1e293b) !important; color: #ffffff !important; }
        .bsub { background-color: #ef4444 !important; } /* Tombol Merah khas Admin */
        .bsub:hover { background-color: #dc2626 !important; }
    </style>
</head>
<body>
<div class="auth-card">
  <div class="panel-left">
    <div class="site-name">SIRAKELIKA</div>
    <p class="panel-tagline">Admin Control Panel</p>
    <p class="panel-desc">Area pembatasan hak akses tinggi. Segala aktivitas di dalam panel ini dicatat oleh log sistem.</p>
  </div>

  <div class="panel-right">
    <div class="form-box">
      <h2 class="ftitle">Sign In Admin</h2>

      <?php if(isset($error)): ?>
        <p style="color:#ef4444;font-size:13px;margin-bottom:12px;background:#fef2f2;padding:8px 12px;border-radius:8px;border:1px solid #fecaca;">
          ⚠️ <?php echo $error; ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="admin_login.php">
        <div class="fg">
          <label for="username">Username Admin</label>
          <div class="iw">
            <input type="text" id="username" name="username" placeholder="Masukkan username admin" required />
          </div>
        </div>

        <div class="fg">
          <label for="pw">Password</label>
          <div class="iw">
            <input type="password" id="pw" name="password" placeholder="••••••••" required/>
          </div>
        </div>

        <button class="bsub" type="submit" name="admin_login">Masuk ke Panel</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>