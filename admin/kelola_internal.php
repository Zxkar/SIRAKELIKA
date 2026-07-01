<?php
session_start();
include 'conn.php';


if (!isset($_SESSION['admin_logged_in']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: login_admin.php");
    exit;
}

$is_superadmin = ($_SESSION['role'] === 'superadmin');

if(isset($_POST['tambah_akun'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    $allowed_roles = $is_superadmin ? ['investigasi','manajemen','admin'] : ['investigasi','manajemen'];
    $role     = in_array($_POST['role'], $allowed_roles) ? $_POST['role'] : 'investigasi';

    // Cek duplikat
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE email='$email' OR username='$username'"));
    if($cek){
        header("Location: kelola_internal.php?error=duplikat");
    } else {
        mysqli_query($conn, "INSERT INTO users (username, email, password, role, status_akun) VALUES ('$username', '$email', '$password', '$role', 'aktif')");
        header("Location: kelola_internal.php?success=1");
    }
    exit;
}

if(isset($_POST['toggle_status'])){
    $id = (int)$_POST['id_user'];
    $status_baru = $_POST['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
    $allowed_roles_sql = $is_superadmin ? "'investigasi','manajemen','admin'" : "'investigasi','manajemen'";

    $cek_akun = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE id_user=$id AND role IN ($allowed_roles_sql)"));
    if(!$cek_akun){
        header("Location: kelola_internal.php?error=forbidden");
        exit;
    }

    mysqli_query($conn, "UPDATE users SET status_akun='$status_baru' WHERE id_user=$id AND role IN ($allowed_roles_sql)");
    header("Location: kelola_internal.php?success=2");
    exit;
}

if(isset($_POST['hapus_akun'])){
    $id = (int)$_POST['id_user'];
    $allowed_roles_sql = $is_superadmin ? "'investigasi','manajemen','admin'" : "'investigasi','manajemen'";

    $cek_akun = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE id_user=$id AND role IN ($allowed_roles_sql)"));
    if(!$cek_akun){
        header("Location: kelola_internal.php?error=forbidden");
        exit;
    }

    mysqli_query($conn, "DELETE FROM users WHERE id_user=$id AND role IN ($allowed_roles_sql)");
    header("Location: kelola_internal.php?deleted=1");
    exit;
}

if(isset($_POST['edit_akun'])){
    $id       = (int)$_POST['id_user'];
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    
    $allowed_roles = $is_superadmin ? ['investigasi','manajemen','admin'] : ['investigasi','manajemen'];
    $role     = in_array($_POST['role'], $allowed_roles) ? $_POST['role'] : 'investigasi';
    $allowed_roles_sql = $is_superadmin ? "'investigasi','manajemen','admin'" : "'investigasi','manajemen'";

    $cek_akun = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE id_user=$id AND role IN ($allowed_roles_sql)"));
    if(!$cek_akun){
        header("Location: kelola_internal.php?error=forbidden");
        exit;
    }

    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE (email='$email' OR username='$username') AND id_user != $id"));
    if($cek){
        header("Location: kelola_internal.php?error=duplikat");
        exit;
    }

    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE users SET username='$username', email='$email', role='$role', password='$password' WHERE id_user=$id AND role IN ($allowed_roles_sql)");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', email='$email', role='$role' WHERE id_user=$id AND role IN ($allowed_roles_sql)");
    }
    header("Location: kelola_internal.php?success=3");
    exit;
}

$allowed_filters = $is_superadmin ? ['investigasi','manajemen','admin'] : ['investigasi','manajemen'];
$filter_role = isset($_GET['role']) && in_array($_GET['role'], $allowed_filters) ? $_GET['role'] : '';

if ($filter_role) {
    $where_role = "AND role='$filter_role'";
} else {
    // Jika filter "Semua", Super Admin bisa melihat role admin, Admin biasa dibatasi
    $where_role = $is_superadmin ? "AND role IN ('investigasi','manajemen','admin')" : "AND role IN ('investigasi','manajemen')";
}

// Search (by username atau email)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where_search = $search ? "AND (username LIKE '%$search%' OR email LIKE '%$search%')" : '';

$query = mysqli_query($conn, "SELECT * FROM users WHERE 1=1 $where_role $where_search ORDER BY role, created_at DESC");
$total = mysqli_num_rows($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pihak Internal - SIRAKELIKA</title>
    <link rel="stylesheet" href="dashboard_admin.css">
    <link rel="stylesheet" href="kelola_internal.css">
</head>
<body>

<aside class="sidebar">
    <div class="logo-area">
        <div class="logo-icon"></div>
        <div>
            <h1 class="logo-title">SIRAKELIKA</h1>
            <p class="logo-sub"><?= $is_superadmin ? 'ADMINISTRATOR' : 'ADMINISTRATOR' ?></p>
        </div>
    </div>
    <nav class="nav-container">
        <div class="nav-group">SYSTEM CONTROL</div>
        <a href="<?= $is_superadmin ? '../super_admin/dashboard_superadmin.php' : 'dashboard_admin.php' ?>" class="nav-link">
            <span class="nav-text">Dashboard</span>
        </a>
        <div class="nav-group">MANAJEMEN</div>
        <a href="verifikasi_laporan.php" class="nav-link">
            <span class="nav-text">Verifikasi Laporan Masuk</span>
        </a>
        <a href="kelola_mahasiswa.php" class="nav-link">
            <span class="nav-text">Kelola Akun Mahasiswa</span>
        </a>
        <a href="kelola_internal.php" class="nav-link active">
            <span class="nav-text">Kelola Akun Pihak Internal</span>
        </a>
        <div class="nav-group">AKUN UTAMA</div>
        <a href="logout.php" class="nav-link logout">
            <span class="nav-text">Keluar</span>
        </a>
    </nav>
</aside>

<input type="hidden" id="session_role_user" value="<?= htmlspecialchars($_SESSION['role']) ?>">

<main class="main-content">
    <header class="topbar">
        <div></div>
        <div class="user-profile">
            <div class="avatar"><?= strtoupper(substr($_SESSION['admin_name'], 0, 2)) ?></div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <span class="user-role"><?= $is_superadmin ? 'Super Administrator' : 'Sistem Administrator' ?></span>
            </div>
        </div>
    </header>

    <div class="content-title">
        <h2>Kelola Akun Pihak Internal</h2>
        <p>Manajemen akun Tim Investigasi, Manajemen Kampus<?= $is_superadmin ? ', dan Administrator' : '' ?></p>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert-success">✓ <?= $_GET['success']==1 ? 'Akun berhasil ditambahkan.' : ($_GET['success']==2 ? 'Status akun berhasil diperbarui.' : 'Data akun berhasil diperbarui.') ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert-deleted">✓ Akun berhasil dihapus.</div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error']==='duplikat'): ?>
    <div class="alert-error">⚠ Username atau email sudah terdaftar di sistem.</div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error']==='forbidden'): ?>
    <div class="alert-error">⚠ Anda tidak memiliki otoritas untuk mengubah akun ini.</div>
    <?php endif; ?>

    <form method="GET" class="search-bar">
        <?php if($filter_role): ?><input type="hidden" name="role" value="<?= htmlspecialchars($filter_role) ?>"><?php endif; ?>
        <input type="text" name="search" placeholder="Cari username atau email..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Cari</button>
        <?php if($search): ?>
        <a href="kelola_internal.php<?= $filter_role ? '?role='.$filter_role : '' ?>" style="padding:10px 14px;background:#f1f5f9;border-radius:8px;font-size:13px;color:#64748b;text-decoration:none;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="top-actions">
        <div class="filter-bar">
            <a href="kelola_internal.php<?= $search ? '?search='.urlencode($search) : '' ?>" class="filter-btn <?= !$filter_role ? 'active' : '' ?>">Semua</a>
            <a href="?role=investigasi<?= $search ? '&search='.urlencode($search) : '' ?>" class="filter-btn <?= $filter_role==='investigasi' ? 'active' : '' ?>">Tim Investigasi</a>
            <a href="?role=manajemen<?= $search ? '&search='.urlencode($search) : '' ?>" class="filter-btn <?= $filter_role==='manajemen' ? 'active' : '' ?>">Manajemen Kampus</a>
            
            <?php if($is_superadmin): ?>
                <a href="?role=admin<?= $search ? '&search='.urlencode($search) : '' ?>" class="filter-btn <?= $filter_role==='admin' ? 'active' : '' ?>">Admin Pusat</a>
            <?php endif; ?>
        </div>
        <button class="btn-tambah" onclick="document.getElementById('modalTambah').classList.add('show')">
            + Tambah Akun Internal
        </button>
    </div>

    <div class="table-container">
        <div class="table-header">
            <div>
                <h3>Daftar Akun Internal</h3>
                <p><?= $total ?> akun ditemukan<?= $search ? " untuk \"".htmlspecialchars($search)."\"" : '' ?></p>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>USERNAME</th>
                    <th>EMAIL</th>
                    <th>ROLE</th>
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
                    <?php if($row['role'] === 'admin'): ?>
                        <span class="badge-admin">Admin Pusat</span>
                    <?php else: ?>
                        <span class="badge-<?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span>
                    <?php endif; ?>
                </td>
                <td><span class="badge-<?= $row['status_akun'] ?>"><?= ucfirst($row['status_akun']) ?></span></td>
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button type="button" class="btn-edit" onclick="openEditModal(<?= $row['id_user'] ?>, <?= htmlspecialchars(json_encode($row['username']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($row['email']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($row['role']), ENT_QUOTES) ?>)">Edit</button>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="toggle_status" value="1">
                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                        <input type="hidden" name="status_akun" value="<?= $row['status_akun'] ?>">
                        <button type="submit" class="<?= $row['status_akun']==='aktif' ? 'btn-toggle-on' : 'btn-toggle-off' ?>">
                            <?= $row['status_akun']==='aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </button>
                    </form>
                    <form method="POST" style="margin:0;" onsubmit="return confirm('Hapus akun ini permanen?')">
                        <input type="hidden" name="hapus_akun" value="1">
                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada akun internal ditemukan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <h3>Tambah Akun Internal</h3>
        <p class="sub">Buat akun baru untuk mengelola operasional sistem internal</p>
        <form method="POST">
            <input type="hidden" name="tambah_akun" value="1">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username..." required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email..." required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Buat password..." required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="investigasi">Tim Investigasi</option>
                    <option value="manajemen">Manajemen Kampus</option>
                    <?php if($is_superadmin): ?>
                        <option value="admin">Admin Pusat</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('modalTambah').classList.remove('show')">Batal</button>
                <button type="submit" class="btn-submit">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('modalTambah').addEventListener('click', function(e){
    if(e.target === this) this.classList.remove('show');
});
</script>

<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <h3>Edit Akun Internal</h3>
        <p class="sub">Ubah username, email, role, atau reset password akun ini</p>
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
                <label>Role</label>
                <select name="role" id="edit_role">
                    <option value="investigasi">Tim Investigasi</option>
                    <option value="manajemen">Manajemen Kampus</option>
                    <?php if($is_superadmin): ?>
                        <option value="admin">Admin Pusat</option>
                    <?php endif; ?>
                </select>
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

<input type="hidden" id="session_role_user" value="<?= htmlspecialchars($_SESSION['role']) ?>">

<script>
function openEditModal(id, username, email, role){
    var current_role = document.getElementById('session_role_user').value;
    
    // Mencegah gangguan data: Jika admin biasa mendeteksi ada role admin, kunci dropdown editnya
    if(role === 'admin' && current_role !== 'superadmin') {
        alert('Anda tidak memiliki otoritas untuk mengedit akun Administrator.');
        return;
    }

    document.getElementById('edit_id_user').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_password').value = '';
    document.getElementById('modalEdit').classList.add('show');
}
document.getElementById('modalEdit').addEventListener('click', function(e){
    if(e.target === this) this.classList.remove('show');
});
</script>
</body>
</html>

