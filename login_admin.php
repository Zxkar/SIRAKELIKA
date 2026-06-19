<?php
session_start();
include 'conn.php';

if(isset($_POST['admin_login'])){
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];

  $query = mysqli_query($conn, "SELECT * FROM admin WHERE nama_admin='$username'");
  
  if(mysqli_num_rows($query) > 0){
    $data = mysqli_fetch_assoc($query);
    
    if(password_verify($password, $data['password'])){
      
      // Set Session Utama Admin
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_id']       = $data['id_admin'];
      $_SESSION['admin_name']     = $data['nama_admin'];
      
      //membaca kolom database
      if($data['role'] === 'superadmin'){
          $_SESSION['role'] = 'superadmin';
          header("Location: dashboard_superadmin.php");
          exit;
      } else {
          $_SESSION['role'] = 'admin';
          header("Location: dashboard_admin.php");
          exit;
      }
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
    <link rel="stylesheet" href="style.css"> 
    <style>
        .panel-left { background: linear-gradient(135deg, #ff0000, #1e293b) !important; color: #ffffff !important; }
        .bsub { background-color: #ef4444 !important; } 
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

      <form method="POST" action="">
        <div class="fg">
          <label for="username">Username Admin</label>
          <div class="iw">
            <input type="text" id="username" name="username" required />
          </div>
        </div>

        <div class="fg">
          <label for="pw">Password</label>
          <div class="iw">
            <input type="password" id="pw" name="password" required/>
          </div>
        </div>

        <button class="bsub" type="submit" name="admin_login">Masuk</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>