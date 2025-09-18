<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Web Tester Ali Hifni</title>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles

    <!-- plugins:css -->
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="../stellar/dist/assets/vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="../stellar/dist/assets/css/vertical-light-layout/style.css">
    <!-- End layout styles -->

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="../stellar/dist/assets/images/favicon.png" />
    
    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <body>
    <div class="container-scroller-fluid">
      
    
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
    <!-- container-scroller -->
     
    <!-- plugins:js -->
    <script src="../stellar/dist/assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="../stellar/dist/assets/vendors/chart.js/chart.umd.js"></script>
    <script src="../stellar/dist/assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="../stellar/dist/assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="../stellar/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="../stellar/dist/assets/vendors/moment/moment.min.js"></script>
    <script src="../stellar/dist/assets/vendors/daterangepicker/daterangepicker.js"></script>
    <script src="../stellar/dist/assets/vendors/chartist/chartist.min.js"></script>
    <script src="../stellar/dist/assets/vendors/progressbar.js/progressbar.min.js"></script>
    <script src="../stellar/dist/assets/js/jquery.cookie.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../stellar/dist/assets/js/off-canvas.js"></script>
    <script src="../stellar/dist/assets/js/hoverable-collapse.js"></script>
    <script src="../stellar/dist/assets/js/misc.js"></script>
    <script src="../stellar/dist/assets/js/settings.js"></script>
    <script src="../stellar/dist/assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="../stellar/dist/assets/js/dashboard.js"></script>
    <!-- End custom js for this page -->

    @yield('scripts')
    
  </body>
   
</html>