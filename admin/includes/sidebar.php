<?php

$current_uri = $_SERVER['REQUEST_URI'];

function isActive($path)
{
    global $current_uri;
    return strpos($current_uri, $path) !== false ? 'active' : '';
}

?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="<?= $base_url ?>/admin/index.php" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="<?= $base_url ?>/assets/img/Logo.png" alt="Logo" width="50" height="50">
            </span>
            <span class="menu-text fw-medium fs-6 ms-2">Fashion Stock Inventory Management</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item <?= isActive('/admin/index.php') ?>">
            <a href="<?= $base_url ?>/admin/index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>


        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pages</span>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div data-i18n="Account Settings">Master Data</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('/admin/bahan_baku') ?>">
                    <a href="<?= $base_url ?>/admin/bahan_baku/list.php" class="menu-link">
                        <div data-i18n="Account">Bahan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/produk') ?>">
                    <a href="<?= $base_url ?>/admin/produk/list.php" class="menu-link">
                        <div data-i18n="Notifications">Produk</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/pemotong') ?>">
                    <a href="<?= $base_url ?>/admin/pemotong/list.php" class="menu-link">
                        <div data-i18n="Notifications">Pemotong</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/penjahit') ?>">
                    <a href="<?= $base_url ?>/admin/penjahit/list.php" class="menu-link">
                        <div data-i18n="Connections">Penjahit</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/reseller') ?>">
                    <a href="<?= $base_url ?>/admin/reseller/list.php" class="menu-link">
                        <div data-i18n="Connections">Reseller</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Components -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Components</span></li>
        <!-- Cards -->
        <li class="menu-item <?= isActive('/produksi') ?>">
            <a href="<?= $base_url ?>/admin/produksi/index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Basic">Produksi</div>
            </a>
        </li>
        <li class="menu-item <?= isActive('/stok') ?>">
            <a href="<?= $base_url ?>/admin/stok/index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="Basic">Stok</div>
            </a>
        </li>
        <li class="menu-item <?= isActive('/penjualan') ?>">
            <a href="<?= $base_url ?>/admin/penjualan/list.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div data-i18n="Basic">Penjualan</div>
            </a>
        </li>

        <li class="menu-item <?= isActive('/laporan') ?>">
            <a href="<?= $base_url ?>/admin/laporan/keuangan.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div data-i18n="Basic">Keuangan</div>
            </a>
        </li>
    </ul>
</aside>