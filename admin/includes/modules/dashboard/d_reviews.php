<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class d_reviews {
    var $code = 'd_reviews';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_reviews() {
      $this->title = MODULE_ADMIN_DASHBOARD_REVIEWS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_REVIEWS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS == 'True');
      }
    }

    function getOutput() {
      global $languages_id;

      $output = '<h3 class="sub-header">' . REVIEWS_TITLE . '</h3>' .
				'<table class="table table-hover table-responsive">' .
                '<thead>' .
				'  <tr>' .
                '    <th>' . DASHBOARD_REVIEWS_TEXT . '</th>' .
                '    <th class="hidden-xs">' . MODULE_ADMIN_DASHBOARD_REVIEWS_DATE . '</th>' .
                '    <th class="hidden-xs">' . MODULE_ADMIN_DASHBOARD_REVIEWS_REVIEWER . '</th>' .
                '    <th>' . MODULE_ADMIN_DASHBOARD_REVIEWS_RATING . '</th>' .
                '    <th class="text-center">' . MODULE_ADMIN_DASHBOARD_REVIEWS_REVIEW_STATUS . '</th>' .
                '  </tr>' . 
				'</thead>';

      $reviews_query = tep_db_query("select r.reviews_id, r.date_added, pd.products_name, r.customers_name, r.reviews_rating, r.reviews_status from " . TABLE_REVIEWS . " r, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = r.products_id and pd.language_id = '" . (int)$languages_id . "' order by r.date_added desc limit 6");
      while ($reviews = tep_db_fetch_array($reviews_query)) {
        $status_icon = ($reviews['reviews_status'] == '1') ? '<i class="fa fa-check-circle-o fa-lg" title="' . IMAGE_ICON_STATUS_GREEN . '"></i>' : '<i class="fa fa-times-circle-o fa-lg" title="' . IMAGE_ICON_STATUS_RED . '"></i>';
        $output .= '<tbody>' .
				   '  <tr>' .
                   '    <td><a href="' . tep_href_link(FILENAME_REVIEWS, 'rID=' . (int)$reviews['reviews_id'] . '&action=edit') . '">' . $reviews['products_name'] . '</a></td>' .
                   '    <td class="hidden-xs">' . tep_date_short($reviews['date_added']) . '</td>' .
                   '    <td class="hidden-xs">' . tep_output_string_protected($reviews['customers_name']) . '</td>' .
                   '    <td>' . tep_draw_stars($reviews['reviews_rating']) . '</td>' .
                   '    <td class="text-center">' . $status_icon . '</td>' .
                   '  </tr>' . 
				   '</tbody>';
      }

      $output .= '</table>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Reviews Module', 'MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS', 'True', 'Do you want to show the latest reviews on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_REVIEWS_STATUS', 'MODULE_ADMIN_DASHBOARD_REVIEWS_SORT_ORDER');
    }
  }
?>