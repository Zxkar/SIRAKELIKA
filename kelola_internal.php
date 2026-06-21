<?php
session_start();
include 'conn.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin'){
    header("Location: login_admin.php");
    exit;
}

// Tambah akun internal
if(isset($_POST['tambah_akun'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role     = in_array($_POST['role'], ['investigasi','manajemen']) ? $_POST['role'] : 'investigasi';

    // Cek duplikat
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE email='$email' OR username='$username'"));
    if($cek){
        header("Location: kelola_internal.php?error=duplikat");
    } else {
        mysqli_query($conn, "INSERT INTO users (username, email, password, role, status_akun)
                             VALUES ('$username', '$email', '$password', '$role', 'aktif')");
        header("Location: kelola_internal.php?success=1");
    }
    exit;
}

// Toggle status
if(isset($_POST['toggle_status'])){
    $id = (int)$_POST['id_user'];
    $status_baru = $_POST['status_akun'] === 'aktif' ? 'nonaktif' : 'aktif';
    mysqli_query($conn, "UPDATE users SET status_akun='$status_baru' WHERE id_user=$id AND role IN ('investigasi','manajemen')");
    header("Location: kelola_internal.php?success=2");
    exit;
}

// Hapus
if(isset($_POST['hapus_akun'])){
    $id = (int)$_POST['id_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user=$id AND role IN ('investigasi','manajemen')");
    header("Location: kelola_internal.php?deleted=1");
    exit;
}

// Edit akun
if(isset($_POST['edit_akun'])){
    $id       = (int)$_POST['id_user'];
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $role     = in_array($_POST['role'], ['investigasi','manajemen']) ? $_POST['role'] : 'investigasi';

    // Cek duplikat (kecuali akun ini sendiri)
    $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM users WHERE (email='$email' OR username='$username') AND id_user != $id"));
    if($cek){
        header("Location: kelola_internal.php?error=duplikat");
        exit;
    }

    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE users SET username='$username', email='$email', role='$role', password='$password' WHERE id_user=$id AND role IN ('investigasi','manajemen')");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$username', email='$email', role='$role' WHERE id_user=$id AND role IN ('investigasi','manajemen')");
    }
    header("Location: kelola_internal.php?success=3");
    exit;
}

// Filter role
$filter_role = isset($_GET['role']) && in_array($_GET['role'], ['investigasi','manajemen']) ? $_GET['role'] : '';
$where_role  = $filter_role ? "AND role='$filter_role'" : "AND role IN ('investigasi','manajemen')";

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
    <style>
        .search-bar { display:flex; gap:10px; margin-bottom:20px; }
        .search-bar input { flex:1; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px;
                            font-size:13px; outline:none; font-family:inherit; }
        .search-bar input:focus { border-color:#dc2626; }
        .search-bar button { padding:10px 20px; background:#dc2626; color:#fff; border:none;
                             border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
        .search-bar button:hover { background:#b91c1c; }
        .top-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
        .filter-bar  { display:flex; gap:8px; }
        .filter-btn  { padding:7px 16px; border-radius:20px; border:1px solid #e2e8f0; background:#fff;
                       font-size:12px; font-weight:600; cursor:pointer; color:#64748b; text-decoration:none; transition:all 0.2s; }
        .filter-btn:hover, .filter-btn.active { background:#dc2626; color:#fff; border-color:#dc2626; }
        .btn-tambah  { padding:9px 18px; background:#dc2626; color:#fff; border:none; border-radius:8px;
                       font-size:13px; font-weight:600; cursor:pointer; }
        .btn-tambah:hover { background:#b91c1c; }
        .badge-investigasi { background:#eff6ff; color:#2563eb; font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; display:inline-block; }
        .badge-manajemen   { background:#fdf4ff; color:#9333ea; font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; display:inline-block; }
        .badge-aktif    { background:#f0fdf4; color:#16a34a; font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; display:inline-block; }
        .badge-nonaktif { background:#fef2f2; color:#dc2626; font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; display:inline-block; }
        .btn-toggle-on  { background:#dc2626; color:#fff; border:none; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; }
        .btn-toggle-off { background:#10b981; color:#fff; border:none; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; }
        .btn-hapus      { background:#fff; color:#dc2626; border:1px solid #fecaca; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; }
        .btn-hapus:hover { background:#fef2f2; }
        .btn-edit       { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; }
        .btn-edit:hover { background:#dbeafe; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; font-weight:600; }
        .alert-deleted { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; font-weight:600; }
        .alert-error   { background:#fffbeb; border:1px solid #fde68a; color:#d97706; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; font-weight:600; }
        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100; align-items:center; justify-content:center; }
        .modal-overlay.show { display:flex; }
        .modal { background:#fff; border-radius:16px; padding:32px; width:460px; max-width:95vw; }
        .modal h3 { font-size:18px; font-weight:700; margin-bottom:4px; }
        .modal p.sub { font-size:13px; color:#64748b; margin-bottom:20px; }
        .form-group { margin-bottom:14px; }
        .form-group label { font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px; }
        .form-group input, .form-group select {
            width:100%; padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px;
            font-size:13px; color:#1e293b; font-family:inherit; outline:none; }
        .form-group input:focus, .form-group select:focus { border-color:#dc2626; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
        .btn-cancel { padding:9px 20px; border-radius:8px; border:1px solid #e2e8f0; background:#fff; font-size:13px; font-weight:600; cursor:pointer; color:#64748b; }
        .btn-submit { padding:9px 20px; border-radius:8px; border:none; background:#dc2626; font-size:13px; font-weight:600; cursor:pointer; color:#fff; }
    </style>
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

<main class="main-content">
    <header class="topbar">
        <div></div>
        <div class="user-profile">
            <div class="avatar">ADM</div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <span class="user-role">Sistem Administrator</span>
            </div>
        </div>
    </header>

    <div class="content-title">
        <h2>Kelola Akun Pihak Internal</h2>
        <p>Manajemen akun Tim Investigasi dan Manajemen Kampus</p>
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

    <!-- Search -->
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
                <td><span class="badge-<?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span></td>
                <td><span class="badge-<?= $row['status_akun'] ?>"><?= ucfirst($row['status_akun']) ?></span></td>
                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button type="button" class="btn-edit" onclick="openEditModal(<?= $row['id_user'] ?>, '<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>', '<?= $row['role'] ?>')">Edit</button>
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

<!-- Modal Tambah Akun -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <h3>Tambah Akun Internal</h3>
        <p class="sub">Buat akun baru untuk Tim Investigasi atau Manajemen Kampus</p>
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

<!-- Modal Edit Akun -->
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

<script>
function openEditModal(id, username, email, role){
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