<?php
session_start();
include '..config/conn.php';

if(isset($_POST['login'])){

  $login_input = mysqli_real_escape_string($conn, $_POST['login_input']);
  $password = $_POST['password'];
  $error = "";

  $query = mysqli_query($conn, "SELECT * FROM users WHERE (email='$login_input' OR username='$login_input')");

  if(mysqli_num_rows($query) > 0){
    $data = mysqli_fetch_assoc($query);

    if($data['status_akun'] == 'nonaktif'){
      $error = "Akun Anda telah dinonaktifkan oleh administrator.";
    } else {
      if(password_verify($password, $data['password'])){
        
        $_SESSION['user_logged_in'] = true;
        $_SESSION['id_user']        = $data['id_user'];
        $_SESSION['username']       = $data['username'];
        $_SESSION['nama']           = $data['nama_lengkap'] ?? $data['username'];
        $_SESSION['email']          = $data['email'];
        $_SESSION['role']           = $data['role'];

        switch($data['role']){
          case 'mahasiswa':
            header("Location: ../mahasiswa/dashboard.php");
            break;
          case 'investigasi':
            header("Location: ../tim-investigasi/dashboard_investigasi.php");
            break;
          case 'manajemen':
            header("Location: ../manajemen_kampus/dashboard_manajemen.php");
            break;
          default:
            header("Location: login.php");
            break;
        }
        exit;

      } else {
        $error = "Kata sandi yang Anda masukkan salah.";
      }
    }
  } else {
    $error = "Akun tidak ditemukan.";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>SIRAKELIKA – Masuk</title>
    <link rel="stylesheet" href="login.css"/>
</head>
<body>
<div class="auth-card">

  <div class="panel-left">
    <div class="site-name">SIRAKELIKA</div>
    <p class="panel-tagline">Platform pelaporan kekerasan kampus.</p>
    <p class="panel-desc">Aman, terpercaya, dan menjaga anonimitas setiap pelapor.</p>
  </div>

  <div class="panel-right">
    <div class="form-box">
      <h2 class="ftitle">Masuk</h2>

      <?php if(!empty($error)): ?>
        <p style="color:#ef4444;font-size:13px;margin-bottom:12px;background:#fef2f2;padding:8px 12px;border-radius:8px;border:1px solid #fecaca;">
          ⚠️ <?php echo $error; ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="login.php">

        <div class="fg">
          <label for="login_input">Username atau Email</label> <div class="iw">
            <input
              type="text" 
              id="login_input"
              name="login_input"
              value="<?php echo isset($_POST['login_input']) ? htmlspecialchars($_POST['login_input']) : ''; ?>"
              placeholder="Masukkan username atau email"
              required
            />
          </div>
        </div>

        <div class="fg">
          <div class="lrow">
            <label for="pw">Kata sandi</label>
            <a href="forgot-password.php" class="lupa">Lupa password?</a>
          </div>
          <div class="iw">
            <input type="password" id="pw" name="password" placeholder="Masukkan kata sandi" required/>
            <button class="tpw" type="button" onclick="tgl('pw',this)" aria-label="Lihat kata sandi">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <button class="bsub" type="submit" name="login">Masuk</button>

      </form>

      <p class="bl">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    </div>
  </div>

</div>
<script>
function tgl(id,btn){
  const el=document.getElementById(id),s=el.type==='password';
  el.type=s?'text':'password';
  btn.innerHTML=s
    ?'<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
    :'<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
</script>
</body>
</html>
