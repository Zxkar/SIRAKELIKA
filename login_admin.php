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
    <style>
        .panel-left { background: linear-gradient(135deg, #ff0000, #000000) !important; color: #ffffff !important; }
        .bsub { background-color: #dc2d2d !important; }
        .bsub:hover { background-color: #ff0000 !important; }

          @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap');

          *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

          html, body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
          }

          /* ─── CARD ─── */
          .auth-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 800px;
            min-height: 460px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
          }

          /* ─── LEFT PANEL ─── */
          .panel-left {
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.5rem;
          }

          .site-name {
            font-size: 13px;
            font-weight: 500;
            color: #0f172a;
            letter-spacing: 1.5px;
            margin-bottom: 2rem;
          }

          .panel-tagline {
            font-size: 22px;
            font-weight: 500;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 1rem;
          }

          .panel-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.65;
          }

          /* ─── RIGHT PANEL ─── */
          .panel-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.5rem;
          }

          .form-box { width: 100%; }

          .ftitle {
            font-size: 18px;
            font-weight: 500;
            color: #0f172a;
            margin-bottom: 1.75rem;
          }

          .fsub {
            font-size: 13px;
            color: #64748b;
            margin-top: -1.25rem;
            margin-bottom: 1.5rem;
            line-height: 1.55;
          }

          /* ─── FIELD GROUP ─── */
          .fg { margin-bottom: 1rem; }

          .fg > label,
          .lrow label {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 5px;
          }

          .lrow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
          }

          .lupa {
            font-size: 12px;
            color: #64748b;
            text-decoration: none;
          }
          .lupa:hover { color: #0f172a; }

          /* input wrapper */
          .iw { position: relative; display: flex; align-items: center; }

          .iw input {
            width: 100%;
            height: 36px;
            padding: 0 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
          }
          .iw input::placeholder { color: #cbd5e1; font-size: 12.5px; }
          .iw input:focus {
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.06);
          }

          /* password toggle */
          .tpw {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            cursor: pointer;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            padding: 0;
          }
          .tpw:hover { color: #64748b; }
          .tpw svg { width: 16px; height: 16px; }

          /* hint */
          .fhint { font-size: 11px; color: #94a3b8; margin-top: 4px; display: none; }
          .fhint.err { color: #ef4444; }
          .fhint.err.show { display: block; }

          /* submit */
          .bsub {
            width: 100%;
            height: 36px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
            transition: opacity .15s;
          }
          .bsub:hover { opacity: 0.85; }

          /* bottom link */
          .bl { text-align: center; font-size: 12px; color: #94a3b8; }
          .bl a { color: #64748b; text-decoration: none; }
          .bl a:hover { color: #0f172a; }

          /* required star */
          .req { color: #ef4444; }

          /* ─── RESPONSIVE ─── */
          @media (max-width: 600px) {
            .auth-card { grid-template-columns: 1fr; }
            .panel-left { display: none; }
            .panel-right { padding: 2rem 1.5rem; }
          }

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