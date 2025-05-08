<?php
session_start();

// Get the current page name
$currentPage = basename($_SERVER['PHP_SELF']);

// Skip session check for change_pwd.php
if ($currentPage !== 'change_pwd.php') {
    // Check if session is set
    if (isset($_SESSION['username'])) {
        if ($_SESSION['role'] != 'admin' && $currentPage !== 'leave.php') {
            header("Location: leave.php");
            exit();
        }
    } else {
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <title>Management System || Update About Us</title>
  <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="assets/vendors/select2/select2.min.css">
  <link rel="stylesheet" href="assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/style.css">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

  <script>
    let notifier = new AWN(globalOptions);
  </script>

</head>

<body>
  <div id="page"></div>
  <div class="container-scroller">

    <!-- Navbar -->
    <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="navbar-brand-wrapper d-flex align-items-center">
        <a class="navbar-brand brand-logo ml-auto text-right" href="dashboard.php">
          <!-- <img src="assets/images/ul.png" alt="logo" /> -->
        </a>
        <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="assets/images/logo.png" alt="logo" /></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center flex-grow-1">
        <ul class="navbar-nav navbar-nav-right ml-auto">
          <?php if ($currentPage !== 'change_pwd.php'): ?>
            <li class="nav-item dropdown d-none d-xl-inline-flex user-dropdown">
              <img class="img-xs rounded-circle ml-2" src="assets/images/faces/face8.jpg" style="margin-right: 10px;"
                alt="Profile image">
              <span class="font-weight-normal"><?php echo ($_SESSION['username']); ?></span>
            </li>
          <?php endif; ?>
          <li class="nav-item d-xl-none">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
              aria-controls="offcanvasExample">
              <span class="navbar-toggler-icon"></span>
            </button>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
          data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>