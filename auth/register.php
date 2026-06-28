<?php
session_start();
include '../config/conn.php';

if(isset($_POST['register'])){

  $nama     = $_POST['username']; 
  $email    = $_POST['email'];
  $password = $_POST['password'];
  $confirm  = $_POST['confirm'];

  if(strlen($password) < 8){
    $error = "Kata sandi minimal 8 karakter.";
  } elseif($password !== $confirm){
    $error = "Konfirmasi kata sandi tidak cocok.";
  } else {

    $stmt_cek = mysqli_prepare($conn, "SELECT id_user FROM users WHERE username = ? OR email = ?");
    mysqli_stmt_bind_param($stmt_cek, "ss", $nama, $email);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);

    if(mysqli_stmt_num_rows($stmt_cek) > 0){
      $error = "Username atau Email sudah digunakan oleh pengguna lain.";
      mysqli_stmt_close($stmt_cek);
    } else {
      mysqli_stmt_close($stmt_cek);

      $hash = password_hash($password, PASSWORD_DEFAULT);

      // 3. Masukkan data ke tabel 'users'
      $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role, status_akun) VALUES (?, ?, ?, 'mahasiswa', 'aktif')");
      mysqli_stmt_bind_param($stmt_insert, "sss", $nama, $email, $hash);
      
      if(mysqli_stmt_execute($stmt_insert)){

        $new_user_id = mysqli_insert_id($conn);
        
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id']        = $new_user_id;
        $_SESSION['username']       = $nama;
        $_SESSION['role']           = 'mahasiswa';
        
        header("Location: dashboard.php");
        exit;
        
      } else {
        $error = "Registrasi gagal, silakan coba lagi.";
      }
      mysqli_stmt_close($stmt_insert);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>SIRAKELIKA – Daftar Akun</title>
<link rel="stylesheet" href="register.css"/>
</head>
<body>
<div class="auth-card" style="min-height:520px">

  <div class="panel-left">
    <div class="site-name">SIRAKELIKA</div>
    <p class="panel-tagline">Bergabung dan mulai lapor dengan aman.</p>
    <p class="panel-desc">Khusus civitas akademika Institut Teknologi B.J. Habibie. Data dienkripsi dan identitas terlindungi.</p>
  </div>

  <div class="panel-right">
    <div class="form-box">
      <h2 class="ftitle">Daftar akun</h2>

      <?php if(isset($error)): ?>
        <p style="color:#ef4444;font-size:13px;margin-bottom:12px;background:#fef2f2;padding:8px 12px;border-radius:8px;border:1px solid #fecaca;">
          <?php echo $error; ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="register.php">

        <div class="fg">
          <label for="uname">Username</label>
          <div class="iw">
            <input
              type="text"
              id="uname"
              name="username"
              placeholder="Nama pengguna"
              value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
              required
            />
          </div>
        </div>

        <div class="fg">
          <label for="email">Email</label>
          <div class="iw">
            <input
              type="email"
              id="email"
              name="email"
              placeholder="contoh@email.com"
              value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
              required
            />
          </div>
        </div>

        <div class="fg">
          <label for="pw">Kata sandi</label>
          <div class="iw">
            <input type="password" id="pw" name="password" placeholder="Min. 8 karakter" required/>
            <button class="tpw" type="button" onclick="tgl('pw',this)" aria-label="Lihat kata sandi">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <p class="fhint err" id="pw-hint">Kata sandi minimal 8 karakter</p>
        </div>

        <div class="fg">
          <label for="cpw">Konfirmasi kata sandi</label>
          <div class="iw">
            <input type="password" id="cpw" name="confirm" placeholder="Ulangi kata sandi" required/>
            <button class="tpw" type="button" onclick="tgl('cpw',this)" aria-label="Lihat konfirmasi">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <p class="fhint err" id="cpw-hint">Kata sandi tidak cocok</p>
        </div>

        <button class="bsub" type="submit" name="register">Buat akun</button>

      </form>

      <p class="bl">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
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
document.getElementById('pw').addEventListener('input', function(){
  const h=document.getElementById('pw-hint');
  if(this.value.length>0&&this.value.length<8) h.classList.add('show');
  else h.classList.remove('show');
});
document.getElementById('cpw').addEventListener('input', function(){
  const h=document.getElementById('cpw-hint');
  const p=document.getElementById('pw').value;
  if(this.value.length>0&&this.value!==p) h.classList.add('show');
  else h.classList.remove('show');
});
</script>
</body>
</html>

