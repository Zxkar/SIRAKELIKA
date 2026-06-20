<?php
session_start();
include 'conn.php';

if(isset($_POST['admin_login'])){
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];
  $error = "";

  $query = mysqli_query($conn, "SELECT * FROM users WHERE (username='$username' OR email='$username') AND (role='admin' OR role='superadmin')");
  
  if(mysqli_num_rows($query) > 0){
    $data = mysqli_fetch_assoc($query);
    
    if($data['status_akun'] == 'nonaktif'){
      $error = "Akun admin Anda telah dinonaktifkan.";
    } else {

      if(password_verify($password, $data['password'])){
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['id_user']         = $data['id_user'];
        $_SESSION['username']        = $data['username'];
        $_SESSION['nama']            = $data['username'];
        $_SESSION['role']            = $data['role'];
        
        if($data['role'] === 'superadmin'){
            header("Location: dashboard_superadmin.php");
            exit;
        } else {
            header("Location: dashboard_admin.php");
            exit;
        }
      } else {
        $error = "Username atau Password Admin salah!";
      }
    }
  } else {
    $error = "Username atau Password Admin salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Admin Control Panel Login</title>
    <link rel="stylesheet" href="login_admin.css">
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