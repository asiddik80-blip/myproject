<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Web Test</title>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles

    <!-- plugins:css -->
    <link rel="stylesheet" href="stellar/dist/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="stellar/dist/assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="stellar/dist/assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="stellar/dist/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="stellar/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="stellar/dist/assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="stellar/dist/assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="stellar/dist/assets/vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="stellar/dist/assets/css/vertical-light-layout/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="stellar/dist/assets/images/favicon.png" />
    
    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <body>
    <div class="container-scroller">
      
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
          <a class="navbar-brand brand-logo" href="index.html">
            <h3 class="text-success">Abu Dhabi</h3>
          </a>
          <a class="navbar-brand brand-logo-mini" href="index.html"><img src="stellar/dist/assets/images/logo-mini.svg" alt="logo" /></a>
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="icon-menu"></span>
          </button>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center">
          <h5 class="mb-0 font-weight-medium d-none d-lg-flex">Web Test</h5>
          <ul class="navbar-nav navbar-nav-right">
            <form class="search-form d-none d-md-block" action="#">
              <i class="icon-magnifier"></i>
              <input type="search" class="form-control" placeholder="Search Here" title="Search here">
            </form>
            
            <li class="nav-item"><a href="#" class="nav-link"><i class="icon-chart"></i></a></li>

            <li class="nav-item dropdown language-dropdown d-none d-sm-flex align-items-center">
              <a class="nav-link d-flex align-items-center dropdown-toggle" id="LanguageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-inline-flex">
                  <i class="flag-icon flag-icon-us"></i>
                </div>
                <span class="profile-text font-weight-normal">Language</span>
              </a>
              <div class="dropdown-menu dropdown-menu-left navbar-dropdown py-2" aria-labelledby="LanguageDropdown">
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-us"></i> English </a>
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-fr"></i> Français </a>
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-ae"></i> Arabic </a>
                <a class="dropdown-item">
                  <i class="flag-icon flag-icon-ru"></i> Indonesia </a>
              </div>
            </li>
            <li class="nav-item dropdown d-none d-xl-inline-flex user-dropdown">
    <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
      
        <span class="font-weight-normal"> Mr. Fuadi </span>
    </a>

    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
        <div class="dropdown-header text-center">
          
            <p class="mb-1 mt-3">Mr. Nunung Fuadi</p>
            <p class="font-weight-light text-muted mb-0">fuadi@gmail.com</p>
        </div>

        <a class="dropdown-item"><i class="dropdown-item-icon icon-user text-primary"></i> My Profile <span class="badge badge-pill badge-danger">1</span></a>
        <a class="dropdown-item"><i class="dropdown-item-icon icon-speech text-primary"></i> Messages</a>

        <!-- Form logout tersembunyi -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

        <!-- Link logout -->
        <a class="dropdown-item" href="#" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="dropdown-item-icon icon-power text-primary"></i>Sign Out
        </a>
    </div>
</li>


            <li class="nav-item"><a href="#" class="nav-link"><i class="icon-chart"></i></a></li>

          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="icon-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <x-sidebar level="1" />    
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            @yield('content')
          </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024 ME. 
               
              
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="stellar/dist/assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="stellar/dist/assets/vendors/chart.js/chart.umd.js"></script>
    <script src="stellar/dist/assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="stellar/dist/assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="stellar/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="stellar/dist/assets/vendors/moment/moment.min.js"></script>
    <script src="stellar/dist/assets/vendors/daterangepicker/daterangepicker.js"></script>
    <script src="stellar/dist/assets/vendors/chartist/chartist.min.js"></script>
    <script src="stellar/dist/assets/vendors/progressbar.js/progressbar.min.js"></script>
    <script src="stellar/dist/assets/js/jquery.cookie.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="stellar/dist/assets/js/off-canvas.js"></script>
    <script src="stellar/dist/assets/js/hoverable-collapse.js"></script>
    <script src="stellar/dist/assets/js/misc.js"></script>
    <script src="stellar/dist/assets/js/settings.js"></script>
    <script src="stellar/dist/assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="stellar/dist/assets/js/dashboard.js"></script>
    <!-- End custom js for this page -->

    @yield('scripts')
    
  </body>
   
</html>