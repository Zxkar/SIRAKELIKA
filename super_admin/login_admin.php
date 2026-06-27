<?php
session_start();
include 'conn.php';

// 1. PERBAIKAN: Jika sudah login, alihkan ke dashboard masing-masing, BUKAN ke login_admin.php
if (isset($_SESSION['admin_logged_in'])) {
    if ($_SESSION['role'] === 'superadmin') {
        header("Location: dashboard_superadmin.php");
        exit;
    } else if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit;
    }
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? AND role IN ('admin', 'superadmin') LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['role'] = $user['role']; //
        $_SESSION['admin_name'] = $user['username'];

        if($user['role'] === 'superadmin'){
            header("Location: dashboard_superadmin.php");
        } else {
            header("Location: dashboard_admin.php");
        }
        exit;
    } else {
        $error = "Username atau password tidak valid.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA — Administrator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root{
            --maroon: #991B1B;
            --maroon-deep: #7F1D1D;
            --ink: #1F2430;
            --grey: #8B92A0;
            --grey-light: #C7CCD6;
            --bg: #F4F6F9;
            --border: #E5E7EB;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        @media (prefers-reduced-motion: reduce){
            * { transition: none !important; }
        }

        .login-wrap {
            width: 100%;
            max-width: 920px;
        }

        .card {
            display: flex;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 12px 32px rgba(31,36,48,0.10);
        }

        .panel-brand {
            position: relative;
            flex: 1 1 46%;
            background: linear-gradient(165deg, var(--maroon) 0%, var(--maroon-deep) 100%);
            color: #fff;
            padding: 40px 38px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 560px;
        }

        .watermark {
            position: absolute;
            right: -60px;
            bottom: -50px;
            width: 320px;
            height: 320px;
            opacity: 0.10;
            transform: rotate(-8deg);
            pointer-events: none;
        }

        .eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.75);
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 56px;
            position: relative;
            z-index: 1;
        }
        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px; height: 30px;
            border: 1.5px solid rgba(255,255,255,0.35);
            border-radius: 8px;
            flex-shrink: 0;
        }
        .brand-logo svg { color: #fff; }
        .brand-name {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 17px;
            letter-spacing: 0.2px;
        }

        .panel-brand h1 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 34px;
            line-height: 1.2;
            letter-spacing: -0.3px;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .panel-brand p {
            font-size: 14px;
            line-height: 1.65;
            color: rgba(255,255,255,0.72);
            max-width: 320px;
            position: relative;
            z-index: 1;
        }

        .panel-brand .spacer { flex: 1; }

        .institute {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            position: relative;
            z-index: 1;
            border-top: 1px solid rgba(255,255,255,0.14);
            padding-top: 16px;
        }

        .panel-form {
            flex: 1 1 54%;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            font-size: 21px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
        }
        .form-sub {
            font-size: 13.5px;
            color: var(--grey);
            margin-bottom: 28px;
        }

        .error-box {
            background: #FFF5F5;
            border: 1px solid #FED7D7;
            color: #C53030;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .field { margin-bottom: 18px; }
        .field label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-wrap input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: #FAFAFA;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }
        .input-wrap input::placeholder { color: #D1D5DB; }
        .input-wrap input:focus {
            border-color: var(--maroon);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(111,29,43,0.10);
        }
        .input-wrap input:focus-visible {
            outline: 2px solid var(--maroon);
            outline-offset: 1px;
        }
        .toggle-pw {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; padding: 4px;
            color: #9CA3AF;
            display: flex; align-items: center;
            transition: color 0.15s;
        }
        .toggle-pw:hover { color: var(--maroon); }

        .btn {
            width: 100%;
            padding: 12px;
            background: var(--maroon);
            color: #fff;
            border: none;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            margin-top: 6px;
        }
        .btn:hover {
            background: var(--maroon-deep);
            box-shadow: 0 4px 14px rgba(111,29,43,0.25);
        }
        .btn:active { transform: scale(0.99); }

        .divider {
            border: none;
            border-top: 1px solid #F3F4F6;
            margin: 24px 0 18px;
        }

        .back-link { text-align: center; }
        .back-link a {
            font-size: 12.5px;
            color: var(--grey);
            text-decoration: none;
            transition: color 0.15s;
        }
        .back-link a:hover { color: var(--maroon); }

        .footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #C4C9D4;
        }
        .footer a { color: #9CA3AF; text-decoration: none; }
        .footer a:hover { color: var(--maroon); }

        /* ---------- Responsive ---------- */
        @media (max-width: 760px){
            .card { flex-direction: column; }
            .panel-brand { min-height: auto; padding: 28px 28px 26px; }
            .panel-brand h1 { font-size: 26px; }
            .panel-brand .spacer { flex: 0; height: 18px; }
            .brand-row { margin-bottom: 22px; }
            .institute { display: none; }
            .watermark { width: 220px; height: 220px; }
            .panel-form { padding: 32px 26px; }
        }
    </style>
</head>
<body>
<div class="login-wrap">

    <div class="card">

        <div class="panel-brand">
            <svg class="watermark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
            </svg>

            <div class="eyebrow">Akses Terbatas</div>

            <div class="brand-row">
                <div class="brand-logo">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                    </svg>
                </div>
                <div class="brand-name">SIRAKELIKA</div>
            </div>

            <h1>Otorisasi Personel</h1>
            <p>Halaman ini khusus untuk administrator yang telah terverifikasi. Setiap aktivitas tercatat dalam log audit sistem.</p>

            <div class="spacer"></div>

            
        </div>

        <div class="panel-form">
            <div class="form-title">Verifikasi Identitas</div>
            <div class="form-sub">Masukkan kredensial admin yang terdaftar untuk melanjutkan.</div>

            <?php if($error): ?>
            <div class="error-box">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Username</label>
                    <div class="input-wrap">
                        <input type="text" name="username" placeholder="Masukkan username" required autofocus
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>
                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="pw" placeholder="••••••••" required>
                        <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Tampilkan password">
                            <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn">Masuk</button>
            </form>

            <hr class="divider">

         
        </div>

    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> SIRAKELIKA &nbsp;·&nbsp; <a href="#">Kebijakan Privasi</a>
    </div>

</div>

<script>
function togglePw() {
    const pw = document.getElementById('pw');
    const icon = document.getElementById('eye-icon');
    if(pw.type === 'password'){
        pw.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        pw.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>
</body>
</html>