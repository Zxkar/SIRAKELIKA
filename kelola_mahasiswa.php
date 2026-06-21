<?php
session_start();
include 'conn.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin'){
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
    <style>
        .search-bar { display:flex; gap:10px; margin-bottom:20px; }
        .search-bar input { flex:1; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px;
                            font-size:13px; outline:none; font-family:inherit; }
        .search-bar input:focus { border-color:#dc2626; }
        .search-bar button { padding:10px 20px; background:#dc2626; color:#fff; border:none;
                             border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
        .search-bar button:hover { background:#b91c1c; }
        .badge-aktif    { background:#f0fdf4; color:#16a34a; font-size:11px; font-weight:600;
                          padding:4px 10px; border-radius:20px; display:inline-block; }
        .badge-nonaktif { background:#fef2f2; color:#dc2626; font-size:11px; font-weight:600;
                          padding:4px 10px; border-radius:20px; display:inline-block; }
        .btn-toggle-on  { background:#dc2626; color:#fff; border:none; padding:5px 12px;
                          border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; }
        .btn-toggle-off { background:#10b981; color:#fff; border:none; padding:5px 12px;
                          border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; }
        .btn-hapus      { background:#fff; color:#dc2626; border:1px solid #fecaca; padding:5px 12px;
                          border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; }
        .btn-hapus:hover { background:#fef2f2; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a;
                         padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; font-weight:600; }
        .alert-deleted { background:#fef2f2; border:1px solid #fecaca; color:#dc2626;
                         padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; font-weight:600; }
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
            <div class="avatar">ADM</div>
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
    <div class="alert-success">✓ Status akun berhasil diperbarui.</div>
    <?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert-deleted">✓ Akun berhasil dihapus dari sistem.</div>
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
                    <!-- Toggle status -->
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

</body>
</html>