<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class d_version_check {
    var $code = 'd_version_check';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_version_check() {
      $this->title = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS == 'True');
      }
    }

    function getOutput() {
      $cache_file = DIR_FS_CACHE . 'oscommerce_version_check.cache';
      $current_version = tep_get_version();
      $new_version = false;

      if (file_exists($cache_file)) {
        $date_last_checked = tep_datetime_short(date('Y-m-d H:i:s', filemtime($cache_file)));

        $releases = unserialize(implode('', file($cache_file)));

        foreach ($releases as $version) {
          $version_array = explode('|', $version);

          if (version_compare($current_version, $version_array[0], '<')) {
            $new_version = true;
            break;
          }
        }
      } else {
        $date_last_checked = MODULE_ADMIN_DASHBOARD_VERSION_CHECK_NEVER;
      }

      $output = '<table class="table table-hover table-responsive table-bordered table-condensed">' .
				'<thead>' .
				'  <tr>' . 
                '    <th>' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_TITLE . '</th>' .
                '    <th class="text-right">' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_DATE . '</th>' .
                '  </tr>' . 
				'</thead>';

      if ($new_version == true) {
        $output .= '  <tr class="warning">' .
                   '    <td colspan="2"><strong>' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_UPDATE_AVAILABLE . '</strong></td>' .
                   '  </tr>';
      }

      $output .= '<tbody>' .
				 '  <tr>' .
                 '    <td><a href="' . tep_href_link(FILENAME_VERSION_CHECK) . '">' . MODULE_ADMIN_DASHBOARD_VERSION_CHECK_CHECK_NOW . '</a></td>' .
                 '    <td class="text-right">' . $date_last_checked . '</td>' .
                 '  </tr>' .
				 '</tbody>' . 
                 '</table>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Version Check Module', 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS', 'True', 'Do you want to show the version check results on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_VERSION_CHECK_STATUS', 'MODULE_ADMIN_DASHBOARD_VERSION_CHECK_SORT_ORDER');
    }
  }
?>
