<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  function tep_opendir($path) {
    $path = rtrim($path, '/') . '/';

    $exclude_array = array('.', '..', '.DS_Store', 'Thumbs.db');

    $result = array();

    if ($handle = opendir($path)) {
      while (false !== ($filename = readdir($handle))) {
        if (!in_array($filename, $exclude_array)) {
          $file = array('name' => $path . $filename,
                        'is_dir' => is_dir($path . $filename),
                        'writable' => tep_is_writable($path . $filename),
                        'size' => filesize($path . $filename),
                        'last_modified' => strftime(DATE_TIME_FORMAT, filemtime($path . $filename)));

          $result[] = $file;

          if ($file['is_dir'] == true) {
            $result = array_merge($result, tep_opendir($path . $filename));
          }
        }
      }

      closedir($handle);
    }

    return $result;
  }

  if (!isset($HTTP_GET_VARS['lngdir'])) $HTTP_GET_VARS['lngdir'] = $language;

  $languages_array = array();
  $languages = tep_get_languages();
  $lng_exists = false;
  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
    if ($languages[$i]['directory'] == $HTTP_GET_VARS['lngdir']) $lng_exists = true;

    $languages_array[] = array('id' => $languages[$i]['directory'],
                               'text' => $languages[$i]['name']);
  }

  if (!$lng_exists) $HTTP_GET_VARS['lngdir'] = $language;

  if (isset($HTTP_GET_VARS['filename'])) {
    $file_edit = realpath(DIR_FS_CATALOG_LANGUAGES . $HTTP_GET_VARS['filename']);

    if (substr($file_edit, 0, strlen(DIR_FS_CATALOG_LANGUAGES)) != DIR_FS_CATALOG_LANGUAGES) {
      tep_redirect(tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir']));
    }
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        if (isset($HTTP_GET_VARS['lngdir']) && isset($HTTP_GET_VARS['filename'])) {
          $file = DIR_FS_CATALOG_LANGUAGES . $HTTP_GET_VARS['filename'];

          if (file_exists($file) && tep_is_writable($file)) {
            $new_file = fopen($file, 'w');
            $file_contents = stripslashes($HTTP_POST_VARS['file_contents']);
            fwrite($new_file, $file_contents, strlen($file_contents));
            fclose($new_file);
          }

          tep_redirect(tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir']));
        }
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
		  
     <div class="row">
        <div class="col-md-8 mb10">
			<h3><?php echo HEADING_TITLE; ?></h3>
	    </div>
        <div class="col-md-4 mb10">
            <?php 
			   echo tep_draw_form('lng', FILENAME_DEFINE_LANGUAGE, '', 'get') .
					tep_draw_pull_down_menu('lngdir', $languages_array, $HTTP_GET_VARS['lngdir'], 'onchange="this.form.submit();"') .
					tep_hide_session_id() . '</form>';
	         ?>
        </div>    
     </div>  
	 
<?php
  if (isset($HTTP_GET_VARS['lngdir']) && isset($HTTP_GET_VARS['filename'])) {
    $file = DIR_FS_CATALOG_LANGUAGES . $HTTP_GET_VARS['filename'];

    if (file_exists($file)) {
      $file_array = file($file);
      $contents = implode('', $file_array);

      $file_writeable = true;
      if (!tep_is_writable($file)) {
        $file_writeable = false;
        $messageStack->reset();
        $messageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $file), 'error');
        echo $messageStack->output();
      }

?>

          <?php echo tep_draw_form('language', FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir'] . '&filename=' . $HTTP_GET_VARS['filename'] . '&action=save'); ?>
  
	<div class="row">
		<div class="col-md-8 col-md-offset-2">  
			<strong><?php echo $HTTP_GET_VARS['filename']; ?></strong><br />
			<?php echo tep_draw_textarea_field('file_contents', 'soft', '80', '25', $contents, (($file_writeable) ? '' : 'readonly') . ' style="width: 100%;"'); ?><br />
			<div class="text-center"><?php if ($file_writeable == true) { echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir'])); } else { echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir'])); } ?></div><br />
			</form>
			<div class="well"><?php echo TEXT_EDIT_NOTE; ?></div>
		</div>
    </div>
<?php
	} else {
?>
        <div class="well"><strong><?php echo TEXT_FILE_DOES_NOT_EXIST; ?></strong></div>
        <?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir'])); ?>
        
<?php
    }
  } else {
    $filename = $HTTP_GET_VARS['lngdir'] . '.php';
    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
?>
        <table class="table table-hover table-responsive table-striped table-condensed">
			<thead>
				<tr>
					<th><?php echo TABLE_HEADING_FILES; ?></th>
					<th class="text-center"><?php echo TABLE_HEADING_WRITABLE; ?></th>
					<th class="text-right"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></th>
				</tr>
			</thead>
			<tbody> 
				<tr>
					<td><a href="<?php echo tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir'] . '&filename=' . $filename); ?>"><strong><?php echo $filename; ?></strong></a></td>
					<td class="text-center"><?php echo ((tep_is_writable(DIR_FS_CATALOG_LANGUAGES . $filename) == true) ? '<i class="fa fa-check fa-lg icon-green"></i>' : '<i class="fa fa-times fa-lg icon-red"></i>'); ?></td>
					<td class="text-right"><?php echo strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CATALOG_LANGUAGES . $filename)); ?></td>
				</tr>
<?php
    foreach (tep_opendir(DIR_FS_CATALOG_LANGUAGES . $HTTP_GET_VARS['lngdir']) as $file) {
      if (substr($file['name'], strrpos($file['name'], '.')) == $file_extension) {
        $filename = substr($file['name'], strlen(DIR_FS_CATALOG_LANGUAGES));

        echo '  <tr>' .
             '      <td><a href="' . tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $HTTP_GET_VARS['lngdir'] . '&filename=' . $filename) . '">' . substr($filename, strlen($HTTP_GET_VARS['lngdir'] . '/')) . '</a></td>' .
             '      <td class="text-center">' . (($file['writable'] == true) ? '<i class="fa fa-check fa-lg icon-green"></i>' : '<i class="fa fa-times fa-lg icon-red"></i>') . '</td>' .
             '      <td class="text-right">' . $file['last_modified'] . '</td>' .
             '  </tr>';
      }
    }
?>
            </tbody>
        </table>
          
<?php
  }
?>
     
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
