<?php
/*
  $Id$ osC Admin BS by Tsimi

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo TITLE; ?></title>
<base href="<?php echo HTTP_SERVER . DIR_WS_ADMIN; ?>" />

<!-- BOF CSS DEFINITIONS //-->

<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_href_link('ext/jquery/ui/redmond/jquery-ui-1.10.4.min.css'); ?>">
<!-- Bootstrap CSS //-->
<link rel="stylesheet" type="text/css" href="<?php echo tep_catalog_href_link('ext/bootstrap/css/bootstrap.css'); ?>">
<!-- Font Awesome CSS //-->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<!-- Custom CSS //-->
<link rel="stylesheet" type="text/css" href="includes/custom.css">

<!-- EOF CSS DEFINITIONS //-->

<!-- BOF JAVASCRIPT DEFINITIONS //-->

<!--[if IE]><script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/flot/excanvas.min.js'); ?>"></script><![endif]-->
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/jquery-1.11.1.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/ui/jquery-ui-1.10.4.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/flot/jquery.flot.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/flot/jquery.flot.time.min.js', '', 'SSL'); ?>"></script>

<script type="text/javascript">
// fix jQuery 1.8.0 and jQuery UI 1.8.22 bug with dialog buttons; http://bugs.jqueryui.com/ticket/8484
if ( $.attrFn ) { $.attrFn.text = true; }
</script>
<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/jquery/ui/i18n/jquery.ui.datepicker-' . JQUERY_DATEPICKER_I18N_CODE . '.js'); ?>"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>
<!-- Stock osC JS //-->
<script type="text/javascript" src="includes/general.js"></script>
<!-- Bootstrap JS //-->
<script type="text/javascript" src="<?php echo tep_catalog_href_link('ext/bootstrap/js/bootstrap.js'); ?>"></script>
<!-- JS for Browse for file button //-->
<script type="text/javascript" src="ext/bootstrap-filestyle.min.js"></script>
<!-- Side Menu -->
<script type="text/javascript" src="ext/offcanvas.js"></script>

<!-- EOF JAVASCRIPT DEFINITIONS //-->

</head>
<body>
	
<div class="container-fluid">
	
	<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
	
  <?php if (DISPLAY_SIDE_MENU != 'true') { ?>

			<div class="row">
				<div class="col-xs-12">

  <?php } else { ?>

			<div class="row row-offcanvas row-offcanvas-left">

		  <?php if (tep_session_is_registered('admin')) { 
		  
					include(DIR_WS_INCLUDES . 'column_left.php'); ?>
		
					<div class="col-xs-12 col-lg-10 mt10">  
			 		 
					<p class="visible-xs visible-sm visible-md menu_button">
						<button type="button" class="btn btn-primary" data-toggle="offcanvas">Menu <i class="fa fa-bars"></i></button>
					</p>
					
		  <?php }  else { ?> 
		 
					<div class="col-xs-12">  
				
		  <?php } ?>
		  
  <?php } ?>
  <?php
	if ($messageStack->size > 0) {
	echo '<div class="row"><div class="col-xs-12">' . $messageStack->output() . '</div></div>';
	}
  ?>