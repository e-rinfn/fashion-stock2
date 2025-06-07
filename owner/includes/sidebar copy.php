<div class="sidebar bg-dark text-white">
    <div class="sidebar-header p-3">
        <h5>Sneat Fashion</h5>
        <small>Owner Panel</small>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="../index.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'laporan/') ? 'active' : '' ?>" data-bs-toggle="collapse" href="#laporanMenu">
                <i class="fas fa-file-alt me-2"></i> Laporan
            </a>
            <div class="collapse <?= str_contains($_SERVER['PHP_SELF'], 'laporan/') ? 'show' : '' ?>" id="laporanMenu">
                <ul class="nav flex-column ps-4">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'stok.php' ? 'active' : '' ?>" href="laporan/stok.php">Stok</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'produksi.php' ? 'active' : '' ?>" href="laporan/produksi.php">Produksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'penjualan.php' ? 'active' : '' ?>" href="laporan/penjualan.php">Penjualan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'keuangan.php' ? 'active' : '' ?>" href="laporan/keuangan.php">Keuangan</a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</div>