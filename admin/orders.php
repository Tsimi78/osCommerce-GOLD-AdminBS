<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update_order':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);
        $status = tep_db_prepare_input($HTTP_POST_VARS['status']);
        $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);

        $order_updated = false;
        $check_status_query = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
        $check_status = tep_db_fetch_array($check_status_query);

        if ( ($check_status['orders_status'] != $status) || tep_not_null($comments)) {
          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$oID . "'");

          $customer_notified = '0';
          if (isset($HTTP_POST_VARS['notify']) && ($HTTP_POST_VARS['notify'] == 'on')) {
            $notify_comments = '';
            if (isset($HTTP_POST_VARS['notify_comments']) && ($HTTP_POST_VARS['notify_comments'] == 'on')) {
              $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
            }

            $email = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . $notify_comments . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);

            tep_mail($check_status['customers_name'], $check_status['customers_email_address'], EMAIL_TEXT_SUBJECT, $email, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            $customer_notified = '1';
          }

          tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$oID . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_input($comments)  . "')");

          $order_updated = true;
        }

        if ($order_updated == true) {
         $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        tep_remove_order($oID, $HTTP_POST_VARS['restock']);

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action'))));
        break;
    }
  }

  if (($action == 'edit') && isset($HTTP_GET_VARS['oID'])) {
    $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
    $order_exists = true;
    if (!tep_db_num_rows($orders_query)) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
  }

  include(DIR_WS_CLASSES . 'order.php');

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

   
<?php
  if (($action == 'edit') && ($order_exists == true)) {
    $order = new order($oID);
?>
<div class="row">
    <div class="col-md-6">
		<h3><?php echo HEADING_TITLE; ?></h3>
    </div>
	<div class="col-md-6 mt15 text-right">
		<?php 
			echo tep_draw_button(IMAGE_ORDERS_INVOICE, 'fa fa-file-text-o', tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $HTTP_GET_VARS['oID']), null, array('newwindow' => true)) . '&nbsp;' . 
				 tep_draw_button(IMAGE_ORDERS_PACKINGSLIP, 'fa fa-file-text', tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $HTTP_GET_VARS['oID']), null, array('newwindow' => true)) . '&nbsp;' . 
				 tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')))); 
		?>
    </div>
 </div>		 
		<div class="row mt15">
			<div class="col-sm-4">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title"><?php echo ENTRY_CUSTOMER; ?></h3>
					</div>
					<div class="panel-body">
						<?php echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title"><?php echo ENTRY_SHIPPING_ADDRESS; ?></h3>
					</div>
					<div class="panel-body">
						<?php echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?>
					</div>
				</div>			
			</div>
			<div class="col-sm-4">
				<div class="panel panel-danger">
					<div class="panel-heading">
						<h3 class="panel-title"><?php echo ENTRY_BILLING_ADDRESS; ?></h3>
					</div>
					<div class="panel-body">
						<?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?>
					</div>
				</div>			
			</div>
		</div> 
		<div class="row">
			<div class="col-sm-6 col-md-4">
				<div class="panel panel-default">
					<div class="panel-body">
						<label><?php echo ENTRY_TELEPHONE_NUMBER . ':'; ?></label>
						<?php echo $order->customer['telephone']; ?><br />
						<label><?php echo ENTRY_EMAIL_ADDRESS . ':'; ?></label>
						<?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?><br />
						<label><?php echo ENTRY_PAYMENT_METHOD . ':'; ?></label>
						<?php echo $order->info['payment_method']; ?>
					</div>
				</div>
			</div>
<?php
    if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>			
			<div class="col-sm-6 col-md-8 col-lg-4">
				<div class="panel panel-default">
					<div class="panel-body">
						<label><?php echo ENTRY_CREDIT_CARD_TYPE . ':'; ?></label>
						<?php echo $order->info['cc_type']; ?>
						<br />
						<label><?php echo ENTRY_CREDIT_CARD_OWNER . ':'; ?></label>
						<?php echo $order->info['cc_owner']; ?>
						<br />
						<label><?php echo ENTRY_CREDIT_CARD_NUMBER . ':'; ?></label>
						<?php echo $order->info['cc_number']; ?>
						<br />
						<label><?php echo ENTRY_CREDIT_CARD_EXPIRES . ':'; ?></label>
						<?php echo $order->info['cc_expires']; ?>
					</div>
				</div>
			</div>
		</div>
<?php
    }
?>	      

	<div class="col-xs-12">
		<table class="table table-striped table-responsive table-bordered">
			<thead>
			  <tr>
				<th class="text-center"><?php echo TABLE_HEADING_QUANTITY; ?></th>
				<th><?php echo TABLE_HEADING_PRODUCTS; ?></th>
				<th class="hidden-xs"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
				<th><?php echo TABLE_HEADING_TAX; ?></th>
				<th class="hidden-xs"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></th>
				<th><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></th>
				<th class="hidden-xs"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></th>
				<th><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></th>
			  </tr>
		   </thead>
		   <tbody>	   
	<?php
		for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
		  echo '          <tr>' .
			   '            <td class="text-center">' . $order->products[$i]['qty'] .
			   '            <td>' . $order->products[$i]['name'];

		  if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
			for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
			  echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
			  if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
			  echo '</i></small></nobr>';
			}
		  }

		  echo '	</td>' .
			   '    <td class="hidden-xs">' . $order->products[$i]['model'] . '</td>' .
			   '    <td class="text-right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' .
			   '    <td class="text-right hidden-xs"><strong>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' .
			   '    <td class="text-right"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true), true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' .
			   '    <td class="text-right hidden-xs"><strong>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' .
			   '    <td class="text-right"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>';
		  echo '  </tr>';
		}
	?>
			</tbody>
		</table>
	</div>

     <div class="col-sm-6 col-sm-offset-6 text-right">	
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo $order->totals[$i]['title'] . "\n" .
           $order->totals[$i]['text'] . '<br />' . "\n";
    }
?>     
     </div>       
    <div class="col-xs-12 col-md-10 col-lg-7 mt15"> 
		<table class="table table-striped table-responsive table-bordered table-condensed">
		   <thead>
			  <tr>
				<th style="width:30%;"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
				<th class="text-center" style="width:10%;"><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></th>
				<th class="text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
				<th class="hidden-xs text-center" style="width:40%;"><?php echo TABLE_HEADING_COMMENTS; ?></th>
			  </tr>
		   </thead>
		   <tbody>  		 
	<?php
		$orders_history_query = tep_db_query("select orders_status_id, date_added, customer_notified, comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
		if (tep_db_num_rows($orders_history_query)) {
		  while ($orders_history = tep_db_fetch_array($orders_history_query)) {
			echo '<tr>' .
				 '	<td>' . tep_datetime_short($orders_history['date_added']) . '</td>' .
				 '  <td class="text-center">';
			if ($orders_history['customer_notified'] == '1') {
			  echo '<i class="fa fa-check fa-lg icon-green"></i>' . '</td>';
			} else {
			  echo '<i class="fa fa-times fa-lg icon-red"></i>' . '</td>';
			}
			echo '  <td class="text-center">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' .
				 '  <td class="hidden-xs">' . nl2br(tep_db_output($orders_history['comments'])) . '&nbsp;</td>' .
				 '</tr>';
		  }
		} else {
			echo '<tr>' .
				 '  <td>' . TEXT_NO_ORDER_HISTORY . '</td>' .
				 '</tr>';
		}
	?>
			 </tbody>
        </table>
    </div> 
<div class="clearfix"></div>
	<div class="col-xs-12 col-md-10 col-lg-7 clearfix">
		<strong><?php echo TABLE_HEADING_COMMENTS; ?></strong>
		<?php echo tep_draw_form('status', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=update_order'); ?>
		<?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?>
	</div>

	<div class="col-xs-12 col-md-10 col-lg-7 mt10 clearfix">
		<div class="row">
			<div class="col-sm-4 inline">
				<label><?php echo ENTRY_STATUS; ?></label>
				<?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?>
			</div>
			<div class="col-sm-4">	
				<label><?php echo ENTRY_NOTIFY_CUSTOMER; ?></label> <?php echo tep_draw_checkbox_field('notify', '', true); ?>
			</div>
			<div class="col-sm-4"> 
				<label><?php echo ENTRY_NOTIFY_COMMENTS; ?></label> <?php echo tep_draw_checkbox_field('notify_comments', '', true); ?>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-10 col-lg-7 text-center">
		<?php echo tep_draw_button(IMAGE_UPDATE, 'fa fa-refresh', null, 'primary', null, 'btn-success'); ?>
	</div>	

</form>

<?php
  } else {
?>
<div class="row">
   <div class="col-md-12 mb10">
		<div class="row">
			<div class="col-md-4">
				<h3><?php echo HEADING_TITLE; ?></h3>
			</div>
			<div class="col-md-4">
      <?php 
	      echo tep_draw_form('orders', FILENAME_ORDERS, '', 'get'); 
          echo '<label>' . HEADING_TITLE_SEARCH . '</label>' . tep_draw_input_field('oID', '', 'size="12"') . tep_draw_hidden_field('action', 'edit');
          echo tep_hide_session_id() . '</form>';
	   ?>
			</div>
			<div class="col-md-4">  
      <?php 
	      echo tep_draw_form('status', FILENAME_ORDERS, '', 'get'); 
          echo '<label>' . HEADING_TITLE_STATUS . '</label>' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onchange="this.form.submit();"');
          echo tep_hide_session_id() . '</form>';
	  ?>
			</div>
		</div>
	</div>
</div>
<div class="row">
     <div class="col-md-8">
         <table class="table table-hover table-responsive table-striped table-condensed">
			<thead>
              <tr>
                <th><?php echo TABLE_HEADING_CUSTOMERS; ?></th>
                <th class="hidden-xs"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></th>
                <th><?php echo TABLE_HEADING_DATE_PURCHASED; ?></th>
                <th><?php echo TABLE_HEADING_STATUS; ?></th>
                <th class="text-right hidden-xs"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			</thead>
			<tbody>  
<?php
    if (isset($HTTP_GET_VARS['cID'])) {
      $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by orders_id DESC";
    } elseif (isset($HTTP_GET_VARS['status']) && is_numeric($HTTP_GET_VARS['status']) && ($HTTP_GET_VARS['status'] > 0)) {
      $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and s.orders_status_id = '" . (int)$status . "' and ot.class = 'ot_total' order by o.orders_id DESC";
    } else {
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by o.orders_id DESC";
    }
    $orders_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
    if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $orders['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders);
      }

      if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
        echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '\'">' . "\n";
      }
?>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit') . '"><i class="fa fa-eye fa-lg" title="'. ICON_PREVIEW .'"></i></a>&nbsp;' . $orders['customers_name']; ?></td>
                <td class="hidden-xs"><?php echo strip_tags($orders['order_total']); ?></td>
                <td><?php echo tep_datetime_short($orders['date_purchased']); ?></td>
                <td><?php echo $orders['orders_status_name']; ?></td>
                <td class="text-right hidden-xs"><?php if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
              </tr>
<?php
    }
?>
		</tbody>
	</table>
		<div class="row mt20">          
			<div class="col-sm-6 mb10">
				<?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?>
			</div>
			<div class="col-sm-6 mb15 pull-right text-right">     
				<?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?>
			</div>
		</div>
	</div> <!-- EOF col-md-8 //-->
	<div class="col-md-4">			  
<?php
  switch ($action) {
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_ORDER . '</span></div>';
		
		echo '<div class="panel-body">' .		
				tep_draw_form('orders', FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm') .
				TEXT_INFO_DELETE_INTRO . 
				'<br /><br />' . tep_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id)) . '</div>' .
				'</div></div>'; 
 break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</strong></span></div>';
		
		echo '<div class="panel-body">		  
				<div class="text-center mb5">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit'), 'primary', null, 'btn-warning') . 
								  '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
								  '<div class="text-center">' . tep_draw_button(IMAGE_ORDERS_INVOICE, 'fa fa-file-text-o', tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id), null, array('newwindow' => true)) . 
								  '&nbsp;' . tep_draw_button(IMAGE_ORDERS_PACKINGSLIP, 'fa fa-file-text', tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id), null, array('newwindow' => true)) . '</div>' . 
				'<br />' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased);
        if (tep_not_null($oInfo->last_modified)) echo '<br />' . TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . tep_date_short($oInfo->last_modified);
        echo '<br />' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method .
		'</div></div>';
      }
      break;
  }
}
?>
    
	</div> <!-- EOF col-md-4 //--> 
 </div> <!-- EOF row //--> 

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
