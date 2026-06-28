<?php
session_start();
include '../config/conn.php';

if (!isset($_SESSION['admin_logged_in']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: login_admin.php");
    exit;
}

// Toggle status akun
if(isset($_POST['toggle_status'])){
    $id = (int)$_POST['id_user'];
    $status_baru = $_POST['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
    mysqli_query($conn, "UPDATE users SET status_akun='$status_baru' WHERE id_user=$id AND role='mahasiswa'");
    header("Location: kelola_mahasiswa.php?success=1");
    exit;
}

// Hapus akun
if(isset($_POST['hapus_akun'])){
    $id = (int)$_POST['id_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user=$id AND role='mahasiswa'");
    header("Location: kelola_mahasiswa.php?deleted=1");
    exit;
}

// Edit akun
if(isset($_POST['edit_akun'])){
    $id       = (int)$_POST['id_user'];
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Cek duplikat (kecuali akun ini sendiri)
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE (email='$email' OR username='$username') AND id_user != $id"));
    if($cek){
        header("Location: kelola_mahasiswa.php?error=duplikat");
        exit;
    }

    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE users SET username='$username', email='$email', password='$password' WHERE id_user=$id AND role='mahasiswa'");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', email='$email' WHERE id_user=$id AND role='mahasiswa'");
    }
    header("Location: kelola_mahasiswa.php?success=1");
    exit;
}

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where  = $search ? "AND (username LIKE '%$search%' OR email LIKE '%$search%')" : '';

$query = mysqli_query($conn, "SELECT * FROM users WHERE role='mahasiswa' $where ORDER BY created_at DESC");
$total = mysqli_num_rows($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mahasiswa - SIRAKELIKA</title>
    <link rel="stylesheet" href="dashboard_admin.css">
    <link rel="stylesheet" href="kelola_mahasiswa.css">
</head>
<body>

<aside class="sidebar">
    <div class="logo-area">
        <div class="logo-icon"></div>
        <div>
            <h1 class="logo-title">SIRAKELIKA</h1>
            <p class="logo-sub">ADMINISTRATOR</p>
        </div>
    </div>
    <nav class="nav-container">
        <div class="nav-group">SYSTEM CONTROL</div>
        <a href="dashboard_admin.php" class="nav-link">
            <span class="nav-text">Dashboard</span>
        </a>
        <div class="nav-group">MANAJEMEN</div>
        <a href="verifikasi_laporan.php" class="nav-link">
            <span class="nav-text">Verifikasi Laporan Masuk</span>
        </a>
        <a href="kelola_mahasiswa.php" class="nav-link active">
            <span class="nav-text">Kelola Akun Mahasiswa</span>
        </a>
        <a href="kelola_internal.php" class="nav-link">
            <span class="nav-text">Kelola Akun Pihak Internal</span>
        </a>
        <div class="nav-group">AKUN UTAMA</div>
        <a href="logout.php" class="nav-link logout">
            <span class="nav-text">Keluar</span>
        </a>
    </nav>
</aside>

<main class="main-content">
    <header class="topbar">
        <div></div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 2)); ?></div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <span class="user-role">Sistem Administrator</span>
            </div>
        </div>
    </header>

    <div class="content-title">
        <h2>Kelola Akun Mahasiswa</h2>
        <p>Manajemen akun pengguna dengan role mahasiswa</p>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert-success">✓ Perubahan akun berhasil disimpan.</div>
    <?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert-deleted">✓ Akun berhasil dihapus dari sistem.</div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error']==='duplikat'): ?>
    <div class="alert-error">⚠ Username atau email sudah dipakai akun lain.</div>
    <?php endif; ?>

    <!-- Search -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Cari username atau email..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Cari</button>
        <?php if($search): ?>
        <a href="kelola_mahasiswa.php" style="padding:10px 14px;background:#f1f5f9;border-radius:8px;font-size:13px;color:#64748b;text-decoration:none;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="table-container">
        <div class="table-header">
            <div>
                <h3>Daftar Akun Mahasiswa</h3>
                <p><?= $total ?> akun ditemukan<?= $search ? " untuk \"$search\"" : '' ?></p>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>USERNAME</th>
                    <th>EMAIL</th>
                    <th>STATUS</th>
                    <th>TERDAFTAR</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php if($total > 0): while($row = mysqli_fetch_assoc($query)): ?>
            <tr>
                <td class="id-case">#<?= $row['id_user'] ?></td>
                <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <span class="badge-<?= $row['status_akun'] ?>"><?= ucfirst($row['status_akun']) ?></span>
                </td>
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button type="button" class="btn-edit" onclick="openEditModal(<?= $row['id_user'] ?>, <?= htmlspecialchars(json_encode($row['username']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($row['email']), ENT_QUOTES) ?>)">Edit</button>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="toggle_status" value="1">
                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                        <input type="hidden" name="status_akun" value="<?= $row['status_akun'] ?>">
                        <button type="submit" class="<?= $row['status_akun'] === 'aktif' ? 'btn-toggle-on' : 'btn-toggle-off' ?>">
                            <?= $row['status_akun'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </button>
                    </form>
                    <!-- Hapus -->
                    <form method="POST" style="margin:0;" onsubmit="return confirm('Hapus akun ini permanen?')">
                        <input type="hidden" name="hapus_akun" value="1">
                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada akun mahasiswa ditemukan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Edit Akun -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <h3>Edit Akun Mahasiswa</h3>
        <p class="sub">Ubah username, email, atau reset password akun ini</p>
        <form method="POST">
            <input type="hidden" name="edit_akun" value="1">
            <input type="hidden" name="id_user" id="edit_id_user">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password" id="edit_password" placeholder="Kosongkan jika tidak ingin mengubah password">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modalEdit').classList.remove('show')">Batal</button>
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, username, email){
    document.getElementById('edit_id_user').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_password').value = '';
    document.getElementById('modalEdit').classList.add('show');
}
document.getElementById('modalEdit').addEventListener('click', function(e){
    if(e.target === this) this.classList.remove('show');
});
</script>

</body>
</html>

