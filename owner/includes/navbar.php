<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">


        <ul class="navbar-nav flex-row align-items-center ms-auto">

            <!-- Nama User yang Sedang Login -->
            <li class="nav-item me-2 me-xl-0">
                <span class="nav-link d-flex align-items-center">
                    <span class="d-none d-xl-inline-block me-2">Hai,</span>
                    <span class="fw-semibold d-none d-xl-inline-block">
                        <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?>
                    </span>
                </span>
            </li>

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <div class="w-px-40 h-px-40 rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 1.25rem; line-height: 1;">
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        </div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="w-px-40 h-px-40 rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 1.25rem; line-height: 1;">
                                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">
                                        <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?>
                                    </span>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($_SESSION['role'] ?? '-') ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $base_url ?>/owner/profile/index.php">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $base_url ?>/auth/logout.php">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>

<!-- build:js assets/vendor/js/core.js -->
<script src="<?= $base_url ?>/assets/vendor/libs/jquery/jquery.js"></script>
<script src="<?= $base_url ?>/assets/vendor/libs/popper/popper.js"></script>
<script src="<?= $base_url ?>/assets/vendor/js/bootstrap.js"></script>
<script src="<?= $base_url ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

<script src="<?= $base_url ?>/assets/vendor/js/menu.js"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="<?= $base_url ?>/assets/vendor/libs/apex-charts/apexcharts.js"></script>

<!-- Main JS -->
<script src="<?= $base_url ?>/assets/js/main.js"></script>

<!-- Page JS -->
<script src="<?= $base_url ?>/assets/js/dashboards-analytics.js"></script>

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>