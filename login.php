<?php
session_start();
include 'connection.php';

if(isset($_POST['login'])){

  $login_input = mysqli_real_escape_string($conn, $_POST['login_input']);
  $password = $_POST['password'];
  $login_success = false;

  // Pengecekan Kondisional: Berdasarkan Email (Mahasiswa) atau Username (Internal)
  if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
    
$query = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE email='$login_input'");
if(mysqli_num_rows($query) > 0){
  $data = mysqli_fetch_assoc($query);
  if(password_verify($password, $data['password'])){
    $_SESSION['username']     = $data['nama_mahasiswa'];
    $_SESSION['email']        = $data['email'];
    $_SESSION['role']         = 'mahasiswa';
    $_SESSION['id_mahasiswa'] = $data['id_mahasiswa'];   // ← TAMBAHKAN INI
    $login_success = true;
    header("Location: dashboard.php");
    exit;
  }
}
  } else {
    // Jika input BUKAN email, melainkan USERNAME (Admin / Tim Investigasi / Manajemen Kampus)

    // 2. Verifikasi Tim Investigasi (Kini Menggunakan HASH)
    $query = mysqli_query($conn, "SELECT * FROM tim_investigasi WHERE nama_tim='$login_input'");
    if(mysqli_num_rows($query) > 0){
      $data = mysqli_fetch_assoc($query);
      if(password_verify($password, $data['password'])){ 
        $_SESSION['username'] = $data['nama_tim'];
        $_SESSION['email']    = $data['email'];
        $_SESSION['role']     = 'investigasi';
        $login_success = true;
        header("Location: dashboard_investigasi.php");
        exit;
      }
    }

    // 3. Verifikasi Manajemen Kampus (Kini Menggunakan HASH)
    if(!$login_success){
      $query = mysqli_query($conn, "SELECT * FROM manajemen_kampus WHERE nama_manajemen='$login_input'");
      if(mysqli_num_rows($query) > 0){
        $data = mysqli_fetch_assoc($query);
        if(password_verify($password, $data['password'])){ 
          $_SESSION['username'] = $data['nama_manajemen'];
          $_SESSION['email']    = $data['email'];
          $_SESSION['role']     = 'manajemen';
          $login_success = true;
          header("Location: dashboard.php");
          exit;
        }
      }
    } 
  }

  // Jika login gagal
  if(!$login_success){
    $error = "Email/Username atau password salah.";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>SIRAKELIKA – Masuk</title>
<link rel="stylesheet" href="style.css"/>
<style>
  .panel-left { background: linear-gradient(135deg, #2563eb, #38bdf8) !important; color: #ffffff !important; }
  .site-name { color: #ffffff !important; }
  .panel-tagline { color: #f0f9ff !important; }
  .panel-desc { color: #e0f2fe !important; }
  .bsub { background-color: #3b82f6 !important; color: #ffffff !important; transition: background-color 0.2s ease; }
  .bsub:hover { background-color: #1d4ed8 !important; }
  .lupa, .bl a { color: #3b82f6 !important; }
  .lupa:hover, .bl a:hover { text-decoration: underline !important; }
  .iw input:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important; }
  .tpw:hover { color: #3b82f6 !important; }
</style>
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

      <?php if(isset($error)): ?>
        <p style="color:#ef4444;font-size:13px;margin-bottom:12px;background:#fef2f2;padding:8px 12px;border-radius:8px;border:1px solid #fecaca;">
          ⚠️ <?php echo $error; ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="login.php">

        <div class="fg">
          <label for="login_input">Email atau Username</label>
          <div class="iw">
            <input
              type="text" 
              id="login_input"
              name="login_input"
              placeholder="contoh@email.com atau username"
              value="<?php echo isset($_POST['login_input']) ? htmlspecialchars($_POST['login_input']) : ''; ?>"
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
            <input type="password" id="pw" name="password" placeholder="••••••••" required/>
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