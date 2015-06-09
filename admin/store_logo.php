<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $error = false;

        $store_logo = new upload('store_logo');
        $store_logo->set_extensions('png');
        $store_logo->set_destination(DIR_FS_CATALOG_IMAGES);

        if ($store_logo->parse()) {
          $store_logo->set_filename('store_logo.png');

          if ($store_logo->save()) {
            $messageStack->add_session(SUCCESS_LOGO_UPDATED, 'success');
          } else {
            $error = true;
          }
        } else {
          $error = true;
        }

        if ($error == false) {
          tep_redirect(tep_href_link(FILENAME_STORE_LOGO));
        }
        break;
    }
  }

  if (!tep_is_writable(DIR_FS_CATALOG_IMAGES)) {
    $messageStack->add(sprintf(ERROR_IMAGES_DIRECTORY_NOT_WRITEABLE, tep_href_link(FILENAME_SEC_DIR_PERMISSIONS)), 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
<div class="row">
	<div class="col-sm-6"> 
		<h3><?php echo HEADING_TITLE; ?></h3>
			<p><?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . 'store_logo.png'); ?></p>	
			<?php echo tep_draw_form('logo', FILENAME_STORE_LOGO, 'action=save', 'post', 'enctype="multipart/form-data"') . 
				'<br />' . TEXT_LOGO_IMAGE . 
				'<br />' . tep_draw_file_field('store_logo') . 
				'<br />' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success'); 
			?>
	</div>
	<div class="clearfix"></div>
</div>	
<p>
	<div class="alert alert-dismissable alert-info"><?php echo '<span class="fa-stack fa-lg"><i class="fa fa-info-circle fa-stack-2x"></i></span>' . TEXT_FORMAT_AND_LOCATION . '&nbsp;' . DIR_FS_CATALOG_IMAGES . 'store_logo.png'; ?></div>
</p>	
</form>
		
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
