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
    if ($action == 'reset') {
      tep_reset_cache_block($HTTP_GET_VARS['block']);
    }

    tep_redirect(tep_href_link(FILENAME_CACHE));
  }

// check if the cache directory exists
  if (is_dir(DIR_FS_CACHE)) {
    if (!tep_is_writable(DIR_FS_CACHE)) $messageStack->add(ERROR_CACHE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_CACHE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

	<h3><?php echo HEADING_TITLE; ?></h3>

  <div class="row">
	<div class="col-xs-12">  
		<table class="table table-hover table-condensed table-responsive table-striped">
			<thead>
			  <tr>
                <th><?php echo TABLE_HEADING_CACHE; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_DATE_CREATED; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			</thead>
			<tbody>
<?php
  if ($messageStack->size < 1) {
    $languages = tep_get_languages();

    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      if ($languages[$i]['code'] == DEFAULT_LANGUAGE) {
        $language = $languages[$i]['directory'];
      }
    }

    for ($i=0, $n=sizeof($cache_blocks); $i<$n; $i++) {
      $cached_file = preg_replace('/-language/', '-' . $language, $cache_blocks[$i]['file']);

      if (file_exists(DIR_FS_CACHE . $cached_file)) {
        $cache_mtime = strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CACHE . $cached_file));
      } else {
        $cache_mtime = TEXT_FILE_DOES_NOT_EXIST;
        $dir = dir(DIR_FS_CACHE);

        while ($cache_file = $dir->read()) {
          $cached_file = preg_replace('/-language/', '-' . $language, $cache_blocks[$i]['file']);

          if (preg_match('/^' . $cached_file. '/', $cache_file)) {
            $cache_mtime = strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CACHE . $cache_file));
            break;
          }
        }

        $dir->close();
      }
?>
              <tr>
                <td><?php echo $cache_blocks[$i]['title']; ?></td>
                <td class="text-center"><?php echo $cache_mtime; ?></td>
                <td class="text-right"><?php echo '<a href="' . tep_href_link(FILENAME_CACHE, 'action=reset&block=' . $cache_blocks[$i]['code'], 'NONSSL') . '"><i class="fa fa-retweet fa-lg" title="Reset"></i></a>'; ?></td>
              </tr>
<?php
    }
  }
?>
			</tbody>
        </table>
		<p><?php echo TEXT_CACHE_DIRECTORY . ' ' . DIR_FS_CACHE; ?></p>
			
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
