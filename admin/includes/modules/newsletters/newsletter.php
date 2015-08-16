<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  class newsletter {
    var $show_choose_audience, $title, $content;

    function newsletter($title, $content) {
      $this->show_choose_audience = false;
      $this->title = $title;
      $this->content = $content;
    }

    function choose_audience() {
      return false;
    }

    function confirm() {
      global $HTTP_GET_VARS;

      $mail_query = tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " where customers_newsletter = '1'");
      $mail = tep_db_fetch_array($mail_query);

      $confirm_string = '<div class="col-md-6 well">' .
						'<font color="#ff0000"><strong>' . sprintf(TEXT_COUNT_CUSTOMERS, $mail['count']) . '</strong></font>' .
                        '<br /><br /><strong>' . $this->title . '</strong>' . 
                        '<br /><br />' . nl2br($this->content) .
						'</div>' .
						'<div class="clearfix"></div>' .
                        tep_draw_button(IMAGE_SEND, 'fa fa-envelope', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'] . '&action=confirm_send'), 'primary') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'])) . '</div>';

      return $confirm_string;
    }

    function send($newsletter_id) {
      $mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_newsletter = '1'");

      $mimemessage = new email(array('X-Mailer: osCommerce'));

      // Build the text version
      $text = strip_tags($this->content);
      if (EMAIL_USE_HTML == 'true') {
        $mimemessage->add_html($this->content, $text);
      } else {
        $mimemessage->add_text($text);
      }

      $mimemessage->build_message();
      while ($mail = tep_db_fetch_array($mail_query)) {
        $mimemessage->send($mail['customers_firstname'] . ' ' . $mail['customers_lastname'], $mail['customers_email_address'], '', EMAIL_FROM, $this->title);
      }

      $newsletter_id = tep_db_prepare_input($newsletter_id);
      tep_db_query("update " . TABLE_NEWSLETTERS . " set date_sent = now(), status = '1' where newsletters_id = '" . tep_db_input($newsletter_id) . "'");
    }
  }
?>
