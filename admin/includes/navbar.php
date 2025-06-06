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
                                     <a class="dropdown-item" href="#">
                                         <i class="bx bx-user me-2"></i>
                                         <span class="align-middle">My Profile</span>
                                     </a>
                                 </li>
                                 <li>
                                     <a class="dropdown-item" href="#">
                                         <i class="bx bx-cog me-2"></i>
                                         <span class="align-middle">Settings</span>
                                     </a>
                                 </li>
                                 <li>
                                     <a class="dropdown-item" href="#">
                                         <span class="d-flex align-items-center align-middle">
                                             <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                                             <span class="flex-grow-1 align-middle">Billing</span>
                                             <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                                         </span>
                                     </a>
                                 </li>
                                 <li>
                                     <div class="dropdown-divider"></div>
                                 </li>
                                 <li>
                                     <a class="dropdown-item" href="auth-login-basic.html">
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