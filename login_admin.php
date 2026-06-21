<?php
session_start();
include 'conn.php';

if(isset($_POST['admin_login'])){
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];

  $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND role IN ('admin','superadmin')");
  
  if(mysqli_num_rows($query) > 0){
    $data = mysqli_fetch_assoc($query);
    
    if(password_verify($password, $data['password'])){
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_id']       = $data['id_user'];
      $_SESSION['admin_name']     = $data['username'];
      
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
  <title>SIRAKELIKA — Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      background: #fef2f2;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .auth-card {
      display: grid;
      grid-template-columns: 1fr 1fr;
      width: 820px;
      min-height: 490px;
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 8px 48px rgba(185,28,28,0.13);
    }

    /* ─── LEFT PANEL ─── */
    .panel-left {
      background: #991b1b;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 2.75rem 2.5rem;
      position: relative;
      overflow: hidden;
    }

    /* lingkaran dekoratif */
    .panel-left::before {
      content: '';
      position: absolute;
      width: 340px; height: 340px;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
      top: -100px; right: -100px;
      pointer-events: none;
    }
    .panel-left::after {
      content: '';
      position: absolute;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(255,255,255,0.04);
      bottom: -60px; left: -50px;
      pointer-events: none;
    }

    /* garis aksen kecil atas */
    .left-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 10.5px;
      font-weight: 600;
      letter-spacing: 1.5px;
      color: #fca5a5;
      text-transform: uppercase;
    }
    .left-eyebrow span {
      width: 20px; height: 1.5px;
      background: #fca5a5;
      display: inline-block;
    }

    .left-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 2rem 0 1.5rem;
    }

    .left-title {
      font-size: 28px;
      font-weight: 600;
      color: #fff;
      line-height: 1.25;
      margin-bottom: 1rem;
    }

    .left-desc {
      font-size: 13px;
      color: #fca5a5;
      line-height: 1.75;
      max-width: 230px;
    }



    /* ─── RIGHT PANEL ─── */
    .panel-right {
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 3rem 2.75rem;
      background: #fff;
    }

    .form-box { width: 100%; }

    .ftitle {
      font-size: 20px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 0.35rem;
    }

    .fsub {
      font-size: 13px;
      color: #9ca3af;
      margin-bottom: 2rem;
      line-height: 1.55;
    }

    /* error */
    .err-box {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 12.5px;
      color: #b91c1c;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* field */
    .fg { margin-bottom: 1.1rem; }

    .fg label {
      display: block;
      font-size: 12px;
      font-weight: 500;
      color: #6b7280;
      margin-bottom: 6px;
    }

    .iw { position: relative; display: flex; align-items: center; }

    .iw input {
      width: 100%;
      height: 42px;
      padding: 0 12px;
      border: 1.5px solid #e5e7eb;
      border-radius: 8px;
      font-size: 13.5px;
      font-family: 'Inter', sans-serif;
      color: #111827;
      background: #fafafa;
      outline: none;
      transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .iw input::placeholder { color: #d1d5db; }
    .iw input:focus {
      border-color: #b91c1c;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(185,28,28,0.08);
    }

    .tpw {
      position: absolute;
      right: 11px;
      background: none;
      border: none;
      cursor: pointer;
      color: #d1d5db;
      display: flex;
      align-items: center;
      padding: 0;
    }
    .tpw:hover { color: #6b7280; }
    .tpw svg { width: 16px; height: 16px; }

    .bsub {
      width: 100%;
      height: 44px;
      background: #991b1b;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      margin-top: 0.5rem;
      transition: background .15s, transform .1s;
      letter-spacing: 0.2px;
    }
    .bsub:hover { background: #7f1d1d; }
    .bsub:active { transform: scale(0.99); }

    .bl {
      text-align: center;
      font-size: 12px;
      color: #9ca3af;
      margin-top: 1.25rem;
    }
    .bl a { color: #991b1b; text-decoration: none; font-weight: 500; }
    .bl a:hover { text-decoration: underline; }

    @media (max-width: 620px) {
      .auth-card { grid-template-columns: 1fr; }
      .panel-left { display: none; }
      .panel-right { padding: 2rem 1.5rem; }
    }
  </style>
</head>
<body>
<div class="auth-card">

  <!-- LEFT -->
  <div class="panel-left">
    <div class="left-eyebrow">
      <span></span> SIRAKELIKA
    </div>

    <div class="left-body">
      <h1 class="left-title">Admin<br>Control Panel</h1>
      <p class="left-desc">Area terbatas. Segala aktivitas dalam panel ini dicatat secara penuh oleh sistem audit.</p>
    </div>


  </div>

  <!-- RIGHT -->
  <div class="panel-right">
    <div class="form-box">
      <h2 class="ftitle">Masuk ke Panel</h2>
      <p class="fsub">Gunakan kredensial admin yang telah didaftarkan.</p>

      <?php if(isset($error)): ?>
        <div class="err-box">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="fg">
          <label for="username">Username Admin</label>
          <div class="iw">
            <input type="text" id="username" name="username" placeholder="nama_admin" required autocomplete="username" />
          </div>
        </div>

        <div class="fg">
          <label for="pw">Password</label>
          <div class="iw">
            <input type="password" id="pw" name="password" placeholder="••••••••" required autocomplete="current-password" />
            <button type="button" class="tpw" onclick="togglePw()" aria-label="Toggle password">
              <svg id="eye-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <button class="bsub" type="submit" name="admin_login">Masuk</button>
      </form>

      <p class="bl"><a href="login.php">← Kembali ke halaman utama</a></p>
    </div>
  </div>

</div>

<script>
function togglePw() {
  const input = document.getElementById('pw');
  const icon  = document.getElementById('eye-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    input.type = 'password';
    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
}
</script>
</body>
</html>