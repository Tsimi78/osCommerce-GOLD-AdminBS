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
                        'writable' => tep_is_writable($path . $filename));

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

  $whitelist_array = array();

  $whitelist_query = tep_db_query("select directory from " . TABLE_SEC_DIRECTORY_WHITELIST);
  while ($whitelist = tep_db_fetch_array($whitelist_query)) {
    $whitelist_array[] = $whitelist['directory'];
  }

  $admin_dir = basename(DIR_FS_ADMIN);

  if ($admin_dir != 'admin') {
    for ($i=0, $n=sizeof($whitelist_array); $i<$n; $i++) {
      if (substr($whitelist_array[$i], 0, 6) == 'admin/') {
        $whitelist_array[$i] = $admin_dir . substr($whitelist_array[$i], 5);
      }
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
	<div class="row">
		<div class="col-xs-12">
			<h3><?php echo HEADING_TITLE; ?></h3>

        <table class="table table-hover table-condensed table-responsive table-striped">
			<thead>     
				<tr>
					<th><?php echo TABLE_HEADING_DIRECTORIES; ?></th>
					<th class="text-center"><?php echo TABLE_HEADING_WRITABLE; ?></th>
					<th class="text-center"><?php echo TABLE_HEADING_RECOMMENDED; ?></th>
				</tr>
			</thead>
			<tbody>	
<?php
  foreach (tep_opendir(DIR_FS_CATALOG) as $file) {
    if ($file['is_dir']) {
?>
              <tr>
                <td><?php echo substr($file['name'], strlen(DIR_FS_CATALOG)); ?></td>
                <td class="text-center"><?php echo (($file['writable'] == true) ? '<i class="fa fa-check fa-lg icon-green"></i>' : '<i class="fa fa-times fa-lg icon-red"></i>'); ?></td>
                <td class="text-center"><?php echo (in_array(substr($file['name'], strlen(DIR_FS_CATALOG)), $whitelist_array) ? '<i class="fa fa-check fa-lg icon-green"></i>' : '<i class="fa fa-times fa-lg icon-red"></i>'); ?></td>
              </tr>
<?php
    }
  }
?>
          </tbody>
        </table>
                <?php echo TEXT_DIRECTORY . ' ' . DIR_FS_CATALOG; ?>
	</div>	
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
