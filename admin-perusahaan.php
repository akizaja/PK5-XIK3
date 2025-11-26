<?php
session_start();
include "config.php";

// Cek Akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// --- PROSES TAMBAH MITRA ---
if (isset($_POST['tambah_mitra'])) {
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_perusahaan']);
    $alamat  = mysqli_real_escape_string($conn, $_POST['alamat']);
    $profil  = mysqli_real_escape_string($conn, $_POST['profil']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $bidang  = mysqli_real_escape_string($conn, $_POST['bidang']);

    // Generate ID Manual (karena di DB kamu tidak auto increment)
    $max_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(id_perusahaan) as max FROM perusahaan"));
    $new_id = $max_id['max'] + 1;

    $query = "INSERT INTO perusahaan (id_perusahaan, nama_perusahaan, alamat, profil_lembaga_industri, nomer_telepon, bidang) 
              VALUES ('$new_id', '$nama', '$alamat', '$profil', '$telepon', '$bidang')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Mitra Industri berhasil ditambahkan!'); window.location='admin-perusahaan.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}

// --- PROSES HAPUS MITRA (YANG SUDAH DIPERBAIKI) ---
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);

    $query = "DELETE FROM perusahaan WHERE id_perusahaan = '$id_hapus'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Mitra berhasil dihapus.'); window.location='admin-perusahaan.php';</script>";
    } else {
        // Tampilkan error spesifik
        echo "<script>alert('Gagal menghapus! Error: " . mysqli_error($conn) . "'); window.location='admin-perusahaan.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mitra - Admin PKL</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        /* Style biar tabel alamat gak kepanjangan */
        td { vertical-align: middle; }
        .text-truncate { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block; }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="logo">ADMIN PANEL</div>
        <ul class="nav-links">
            <li><a href="dashboard admin.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
            <li><a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<main class="data-page">
    <div class="data-container animate-fade-up">
        <div class="header-action">
            <div>
                <h1>Data Mitra Industri</h1>
                <p class="subtitle" style="text-align: left; margin-bottom: 0;">Kelola daftar perusahaan tempat PKL.</p>
            </div>
            <button class="btn-simpan-laporan" onclick="openModal()" style="width: auto; margin-top: 0;">
                <i class="fas fa-plus"></i> Tambah Mitra
            </button>
        </div>
        
        <div class="table-wrapper">
            <table id="laporan-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Perusahaan</th>
                        <th>Bidang</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $result = mysqli_query($conn, "SELECT * FROM perusahaan ORDER BY id_perusahaan DESC");
                    
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td style="font-weight: bold;"><?php echo $row['nama_perusahaan']; ?></td>
                        <td><span style="linear-gradient(to bottom, #1a1a3a, #3c3c8c): #0284c7; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;"><?php echo $row['bidang']; ?></span></td>
                        <td><?php echo $row['nomer_telepon']; ?></td>
                        <td><span class="text-truncate" title="<?php echo $row['alamat']; ?>"><?php echo $row['alamat']; ?></span></td>
                        <td>
                            <a href="admin-perusahaan.php?hapus=<?php echo $row['id_perusahaan']; ?>" 
            x                   class="btn-download-row" 
                               style="background: #ef4444; display:inline-block; text-decoration:none;"
                               onclick="return confirm('Yakin ingin menghapus perusahaan ini?');">
                               <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>Belum ada data perusahaan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="mitraModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h2>Tambah Perusahaan Baru</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Perusahaan</label>
                    <input type="text" name="nama_perusahaan" required placeholder="Contoh: PT. Teknologi Maju">
                </div>
                
                <div class="form-group" style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Bidang Usaha</label>
                        <input type="text" name="bidang" required placeholder="Contoh: IT / Otomotif">
                    </div>
                    <div style="flex: 1;">
                        <label>No. Telepon</label>
                        <input type="number" name="telepon" required placeholder="021xxxx">
                    </div>
                </div>

                <div class="form-group">
                    <label>Profil Singkat</label>
                    <input type="text" name="profil" placeholder="Deskripsi singkat perusahaan...">
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" required rows="3" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;" placeholder="Masukkan alamat lengkap"></textarea>
                </div>

                <div class="form-actions" style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn-dashboard secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" name="tambah_mitra" class="btn-dashboard">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
    function openModal() { document.getElementById('mitraModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('mitraModal').style.display = 'none'; }
    window.onclick = function(event) {
        var modal = document.getElementById('mitraModal');
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>
</body>
</html>