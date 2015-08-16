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

  if ( ($action == 'send_email_to_user') && isset($HTTP_POST_VARS['customers_email_address']) && !isset($HTTP_POST_VARS['back_x']) ) {
    switch ($HTTP_POST_VARS['customers_email_address']) {
      case '***':
        $mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS);
        $mail_sent_to = TEXT_ALL_CUSTOMERS;
        break;
      case '**D':
        $mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_newsletter = '1'");
        $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
        break;
      default:
        $customers_email_address = tep_db_prepare_input($HTTP_POST_VARS['customers_email_address']);

        $mail_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($customers_email_address) . "'");
        $mail_sent_to = $HTTP_POST_VARS['customers_email_address'];
        break;
    }

    $from = tep_db_prepare_input($HTTP_POST_VARS['from']);
    $subject = tep_db_prepare_input($HTTP_POST_VARS['subject']);
    $message = tep_db_prepare_input($HTTP_POST_VARS['message']);

    //Let's build a message object using the email class
    $mimemessage = new email(array('X-Mailer: osCommerce'));

    // Build the text version
    $text = strip_tags($message);
    if (EMAIL_USE_HTML == 'true') {
      $mimemessage->add_html($message, $text);
    } else {
      $mimemessage->add_text($text);
    }

    $mimemessage->build_message();
    while ($mail = tep_db_fetch_array($mail_query)) {
      $mimemessage->send($mail['customers_firstname'] . ' ' . $mail['customers_lastname'], $mail['customers_email_address'], '', $from, $subject);
    }

    tep_redirect(tep_href_link(FILENAME_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to)));
  }

  if ( ($action == 'preview') && !isset($HTTP_POST_VARS['customers_email_address']) ) {
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
  }

  if (isset($HTTP_GET_VARS['mail_sent_to'])) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $HTTP_GET_VARS['mail_sent_to']), 'success');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

   <h3><?php echo HEADING_TITLE; ?></h3>
   
<?php
  if ( ($action == 'preview') && isset($HTTP_POST_VARS['customers_email_address']) ) {
    switch ($HTTP_POST_VARS['customers_email_address']) {
      case '***':
        $mail_sent_to = TEXT_ALL_CUSTOMERS;
        break;
      case '**D':
        $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
        break;
      default:
        $mail_sent_to = $HTTP_POST_VARS['customers_email_address'];
        break;
    }
?>
        <?php echo tep_draw_form('mail', FILENAME_MAIL, 'action=send_email_to_user'); ?>
     
           
					<label class="col-sm-3">
						<?php echo TEXT_CUSTOMER; ?>
					</label>
					
						<?php echo $mail_sent_to; ?>
				
					<label class="col-sm-3">
						<?php echo TEXT_FROM; ?>
					</label>
					<div class="col-sm-7">
						<?php echo htmlspecialchars(stripslashes($HTTP_POST_VARS['from'])); ?>
					</div>
				
					<label class="col-sm-3">
						<?php echo TEXT_SUBJECT; ?>
					</label>
					<div class="col-sm-7">	
						<?php echo htmlspecialchars(stripslashes($HTTP_POST_VARS['subject'])); ?>
					</div>
				
					<label class="col-sm-3">
						<?php echo TEXT_MESSAGE; ?>
					</label>
					<div class="col-sm-7">
						<?php echo nl2br(htmlspecialchars(stripslashes($HTTP_POST_VARS['message']))); ?>
					</div>
		          
<?php
/* Re-Post all POST'ed variables */
    reset($HTTP_POST_VARS);
    while (list($key, $value) = each($HTTP_POST_VARS)) {
      if (!is_array($HTTP_POST_VARS[$key])) {
        echo tep_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
      }
    }
	echo '<div class="text-center">' . tep_draw_button(IMAGE_SEND_EMAIL, 'fa fa-paper-plane-o', null, 'primary',null, 'btn-default') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_MAIL)) . '</div>'
?>
     
          </form>
<?php
  } else {
?>
        <?php echo tep_draw_form('mail', FILENAME_MAIL, 'action=preview'); ?>
           
<?php
    $customers = array();
    $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
    $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
    $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
    $mail_query = tep_db_query("select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " order by customers_lastname");
    while($customers_values = tep_db_fetch_array($mail_query)) {
      $customers[] = array('id' => $customers_values['customers_email_address'],
                           'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')');
    }
?>
		<div class="form-horizontal">  
			<div class="form-group has-feedback">
				<label class="control-label col-sm-3">
					<?php echo TEXT_CUSTOMER; ?>
				</label>
				<div class="col-sm-7">		
					<?php echo tep_draw_pull_down_menu('customers_email_address', $customers, (isset($HTTP_GET_VARS['customer']) ? $HTTP_GET_VARS['customer'] : ''));?>
				</div>
			</div>
			<div class="form-group has-feedback">
				<label class="control-label col-sm-3">
					<?php echo TEXT_FROM; ?>
				</label>
				<div class="col-sm-7">					
					<?php echo tep_draw_input_field('from', EMAIL_FROM); ?>
				</div>
			</div>
			<div class="form-group has-feedback">
				<label class="control-label col-sm-3">              
					<?php echo TEXT_SUBJECT; ?>
				</label>
				<div class="col-sm-7">					
					<?php echo tep_draw_input_field('subject'); ?>
				</div>
			</div>
			<div class="form-group has-feedback">
				<label class="control-label col-sm-3">  
                    <?php echo TEXT_MESSAGE; ?>
				</label>
				<div class="col-sm-7">				
					<?php echo tep_draw_textarea_field('message', 'soft', '60', '15'); ?>
				</div>
			</div>
		</div>
		<div class="text-center">
			<?php echo tep_draw_button(IMAGE_PREVIEW, 'document', null, 'primary'); ?>	
		</div>		
          </form>
<?php
  }
?>


<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
