<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class d_latest_news {
    var $code = 'd_latest_news';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_latest_news() {
      $this->title = MODULE_ADMIN_DASHBOARD_LATEST_NEWS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_LATEST_NEWS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_LATEST_NEWS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_LATEST_NEWS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_LATEST_NEWS_STATUS == 'True');
      }
    }

    function getOutput() {
      if (!class_exists('lastRSS')) {
        include(DIR_WS_CLASSES . 'rss.php');
      }

      $rss = new lastRSS;
      $rss->items_limit = 5;
      $rss->cache_dir = DIR_FS_CACHE;
      $rss->cache_time = 86400;
      $feed = $rss->get('http://feeds.feedburner.com/osCommerceNewsAndBlogs');

      $output = '<h3 class="sub-header">' . LATEST_NEWS_TITLE . '</h3>' . 
				'<table class="table table-hover table-responsive">' .
                '<thead>' .
				'  <tr>' . 
                '    <th>' . LATEST_NEWS_TEXT . '</th>' .
                '    <th>' . MODULE_ADMIN_DASHBOARD_LATEST_NEWS_DATE . '</th>' .
                '  </tr>' .
				'</thead>';

      if (is_array($feed) && !empty($feed)) {
        foreach ($feed['items'] as $item) {
          $output .= '<tbody>' .
					 '  <tr>' .
                     '    <td><a href="' . $item['link'] . '" target="_blank">' . $item['title'] . '</a></td>' .
                     '    <td>' . date("F j, Y", strtotime($item['pubDate'])) . '</td>' .
                     '  </tr>' . 
					 '</tbody>';
        }
      } else {
        $output .= '  <tr class="danger">' .
                   '    <td>' . MODULE_ADMIN_DASHBOARD_LATEST_NEWS_FEED_ERROR . '</td>' .
                   '  </tr>';
      }

      $output .= '  <tr class="text-right">' .
				 '    <td colspan="2"><a href="http://www.oscommerce.com/newsletter/subscribe" target="_blank"><i class="fa fa-file-text-o fa-lg" title="' . MODULE_ADMIN_DASHBOARD_LATEST_NEWS_ICON_NEWSLETTER . '"></i></a>&nbsp;<a href="http://www.facebook.com/pages/osCommerce/33387373079" target="_blank"><i class="fa fa-facebook-square fa-lg" title="' . MODULE_ADMIN_DASHBOARD_LATEST_NEWS_ICON_FACEBOOK . '"></i></a>&nbsp;<a href="http://twitter.com/osCommerce" target="_blank"><i class="fa fa-twitter-square fa-lg" title="' . MODULE_ADMIN_DASHBOARD_LATEST_NEWS_ICON_TWITTER . '"></i></a>&nbsp;<a href="http://feeds.feedburner.com/osCommerceNewsAndBlogs" target="_blank"><i class="fa fa-rss-square fa-lg" title="' . MODULE_ADMIN_DASHBOARD_LATEST_NEWS_ICON_RSS . '"></i></a></td>' .
                 '  </tr>' .
                 '</table>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_LATEST_NEWS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Latest News Module', 'MODULE_ADMIN_DASHBOARD_LATEST_NEWS_STATUS', 'True', 'Do you want to show the latest osCommerce News on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_LATEST_NEWS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_LATEST_NEWS_STATUS', 'MODULE_ADMIN_DASHBOARD_LATEST_NEWS_SORT_ORDER');
    }
  }
?>
