<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $current_version = tep_get_version();
  $major_version = (int)substr($current_version, 0, 1);

  $releases = null;
  $new_versions = array();
  $check_message = array();

  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://www.oscommerce.com/version/online_merchant/' . $major_version);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = trim(curl_exec($ch));
    curl_close($ch);

    if (!empty($response)) {
      $releases = explode("\n", $response);
    }
  } else {
    if ($fp = @fsockopen('www.oscommerce.com', 80, $errno, $errstr, 30)) {
      $header = 'GET /version/online_merchant/' . $major_version . ' HTTP/1.0' . "\r\n" .
                'Host: www.oscommerce.com' . "\r\n" .
                'Connection: close' . "\r\n\r\n";

      fwrite($fp, $header);

      $response = '';
      while (!feof($fp)) {
        $response .= fgets($fp, 1024);
      }

      fclose($fp);

      $response = explode("\r\n\r\n", $response); // split header and content

      if (isset($response[1]) && !empty($response[1])) {
        $releases = explode("\n", trim($response[1]));
      }
    }
  }

  if (is_array($releases) && !empty($releases)) {
    $serialized = serialize($releases);
    if ($f = @fopen(DIR_FS_CACHE . 'oscommerce_version_check.cache', 'w')) {
      fwrite ($f, $serialized, strlen($serialized));
      fclose($f);
    }

    foreach ($releases as $version) {
      $version_array = explode('|', $version);

      if (version_compare($current_version, $version_array[0], '<')) {
        $new_versions[] = $version_array;
      }
    }

    if (!empty($new_versions)) {
      $check_message = array('class' => 'alert alert-warning',
                             'message' => sprintf(VERSION_UPGRADES_AVAILABLE, $new_versions[0][0]));
    } else {
      $check_message = array('class' => 'alert alert-success',
                             'message' => VERSION_RUNNING_LATEST);
    }
  } else {
    $check_message = array('class' => 'alert alert-danger',
                           'message' => ERROR_COULD_NOT_CONNECT);
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    
    <h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">
	<div class="col-xs-12">
      <p><?php echo TITLE_INSTALLED_VERSION . ' <strong>osCommerce Online Merchant v' . $current_version . '</strong>'; ?></p>
     
       <div class="<?php echo $check_message['class']; ?>">
          <p class="smallText"><?php echo $check_message['message']; ?></p>
        </div>
<?php
  if (!empty($new_versions)) {
?>
    <table class="table table-hover table-condensed table-responsive table-striped">
		<thead>     
			<tr>
                <th><?php echo TABLE_HEADING_VERSION; ?></th>
                <th><?php echo TABLE_HEADING_RELEASED; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
		</thead>
		<tbody>

<?php
    foreach ($new_versions as $version) {
?>
              <tr>
                <td><?php echo '<a href="' . $version[2] . '" target="_blank">osCommerce Online Merchant v' . $version[0] . '</a>'; ?></td>
                <td><?php echo tep_date_long(substr($version[1], 0, 4) . '-' . substr($version[1], 4, 2) . '-' . substr($version[1], 6, 2)); ?></td>
                <td class="text-right"><?php echo '<a href="' . $version[2] . '" target="_blank"><i class="fa fa-info-circle fa-lg"></i></a>'; ?></td>
              </tr>
<?php
    }
?>
		</tbody>
	</table>

<?php
  }
?>

  </div>
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
