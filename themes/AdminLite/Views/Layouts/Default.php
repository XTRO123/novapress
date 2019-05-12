<?php
/**
 * Frontend Default Layout
 */

$siteName = Config::get('app.name');

// Generate the Language Changer menu.
$langCode = Language::code();
$langName = Language::name();

$languages = Config::get('languages');

?>
<!DOCTYPE html>
<html lang="<?= $langCode; ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $title; ?> | <?= $siteName; ?></title>
    <?= isset($meta) ? $meta : ''; // Place to pass data ?>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<?php

echo Asset::render('css', array(
    // Bootstrap 3.3.5
    vendor_url('bower_components/bootstrap/dist/css/bootstrap.min.css', 'almasaeed2010/adminlte'),
    // Bootstrap XL
    asset_url('css/bootstrap-xl-mod.min.css', 'themes/admin-lite'),
    // Font Awesome
    vendor_url('bower_components/font-awesome/css/font-awesome.min.css', 'almasaeed2010/adminlte'),
    // Ionicons
    vendor_url('bower_components/Ionicons/css/ionicons.min.css', 'almasaeed2010/adminlte'),
    // Theme style
    vendor_url('dist/css/AdminLTE.min.css', 'almasaeed2010/adminlte'),
    // AdminLTE Skins
    vendor_url('dist/css/skins/_all-skins.min.css', 'almasaeed2010/adminlte'),
    // iCheck
    vendor_url('plugins/iCheck/square/blue.css', 'almasaeed2010/adminlte'),
    // Custom CSS
    asset_url('css/style.css', 'themes/admin-lite'),
));

?>

<?= Asset::position('header', 'css'); ?>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<?php

echo Asset::render('js', array(
    vendor_url('bower_components/jquery/dist/jquery.min.js', 'almasaeed2010/adminlte'),
));

?>

<?= Asset::position('header', 'js'); ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition skin-<?= Config::get('app.color_scheme', 'blue'); ?> layout-top-nav">
<div class="wrapper">
  <header class="main-header">
    <nav class="navbar navbar-static-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <a href="<?= site_url(); ?>" class="navbar-brand"><strong><?= $siteName; ?></strong></a>
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
            <i class="fa fa-bars"></i>
          </button>
        </div>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown language-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class='fa fa-language'></i> <?= $langName; ?>
                    </a>
                    <ul class="dropdown-menu">
                    <?php foreach ($languages as $code => $info) { ?>
                        <li <?= ($code == $langCode) ? 'class="active"' : ''; ?>>
                            <a href='<?= site_url('language/' .$code); ?>' title='<?= $info['info']; ?>'><?= $info['name']; ?></a>
                        </li>
                    <?php } ?>
                    </ul>
                </li>
                <?php if (Auth::check()) { ?>
                <li>
                    <a href='<?= site_url('dashboard'); ?>'><i class='fa fa-home'></i> <?= __d('admin_lite', 'Frontend'); ?></a>
                </li>
                <li>
                    <a href='<?= site_url('logout'); ?>' onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class='fa fa-sign-out'></i> <?= __d('admin_lite', 'Logout'); ?>
                    </a>
                    <form id="logout-form" action="<?= site_url('logout'); ?>" method="POST" style="display: none;">
                        <?= csrf_field(); ?>
                    </form>
                </li>
                <?php } else { ?>
                <?php $path = Request::path(); ?>
                <li <?= ($path == 'register') ? 'class="active"' : ''; ?>>
                    <a href='<?= site_url('register'); ?>'><i class='fa fa-user'></i> <?= __d('admin_lite', 'Sign Up'); ?></a>
                </li>
                <li <?= ($path == 'login') ? 'class="active"' : ''; ?>>
                    <a href='<?= site_url('login'); ?>'><i class='fa fa-sign-in'></i> <?= __d('admin_lite', 'Sign In'); ?></a>
                </li>
                </li>
                <li <?= ($path == 'authorize') ? 'class="active"' : ''; ?>>
                    <a href='<?= site_url('authorize'); ?>'><i class='fa fa-send'></i> <?= __d('admin_lite', 'On-Time Login'); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <!-- /.navbar-custom-menu -->
      </div>
      <!-- /.container-fluid -->
    </nav>
  </header>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="container">
            <!-- Main content -->
            <section class="content">
                <?= $content; ?>
            </section>
        </div>
    </div>

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
      <small><!-- DO NOT DELETE! - Statisticss --></small>
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="http://www.novaframework.com/" target="_blank"><b>Nova Press <?= VERSION; ?> / Nova Framework <?= App::version(); ?></b></a> - </strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->
<?php

echo Asset::render('js', array(
    // Bootstrap 3.3.5
    vendor_url('bower_components/bootstrap/dist/js/bootstrap.min.js', 'almasaeed2010/adminlte'),
    // iCheck
    vendor_url('plugins/iCheck/icheck.min.js', 'almasaeed2010/adminlte'),
    // AdminLTE App
    vendor_url('dist/js/adminlte.min.js', 'almasaeed2010/adminlte'),
));

?>

<?= Asset::position('footer', 'js'); ?>

<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>

<!-- DO NOT DELETE! - Profiler -->

</body>
</html>
