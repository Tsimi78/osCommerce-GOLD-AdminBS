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

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($HTTP_GET_VARS['cID'])) $currency_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);
        $title = tep_db_prepare_input($HTTP_POST_VARS['title']);
        $code = tep_db_prepare_input($HTTP_POST_VARS['code']);
        $symbol_left = tep_db_prepare_input($HTTP_POST_VARS['symbol_left']);
        $symbol_right = tep_db_prepare_input($HTTP_POST_VARS['symbol_right']);
        $decimal_point = tep_db_prepare_input($HTTP_POST_VARS['decimal_point']);
        $thousands_point = tep_db_prepare_input($HTTP_POST_VARS['thousands_point']);
        $decimal_places = tep_db_prepare_input($HTTP_POST_VARS['decimal_places']);
        $value = tep_db_prepare_input($HTTP_POST_VARS['value']);

        $sql_data_array = array('title' => $title,
                                'code' => $code,
                                'symbol_left' => $symbol_left,
                                'symbol_right' => $symbol_right,
                                'decimal_point' => $decimal_point,
                                'thousands_point' => $thousands_point,
                                'decimal_places' => $decimal_places,
                                'value' => $value);

        if ($action == 'insert') {
          tep_db_perform(TABLE_CURRENCIES, $sql_data_array);
          $currency_id = tep_db_insert_id();
        } elseif ($action == 'save') {
          tep_db_perform(TABLE_CURRENCIES, $sql_data_array, 'update', "currencies_id = '" . (int)$currency_id . "'");
        }

        if (isset($HTTP_POST_VARS['default']) && ($HTTP_POST_VARS['default'] == 'on')) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_CURRENCY'");
        }

        tep_redirect(tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $currency_id));
        break;
      case 'deleteconfirm':
        $currencies_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        $currency_query = tep_db_query("select currencies_id from " . TABLE_CURRENCIES . " where code = '" . DEFAULT_CURRENCY . "'");
        $currency = tep_db_fetch_array($currency_query);

        if ($currency['currencies_id'] == $currencies_id) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
        }

        tep_db_query("delete from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");

        tep_redirect(tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'update':
        $server_used = CURRENCY_SERVER_PRIMARY;

        $currency_query = tep_db_query("select currencies_id, code, title from " . TABLE_CURRENCIES);
        while ($currency = tep_db_fetch_array($currency_query)) {
          $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
          $rate = $quote_function($currency['code']);

          if (empty($rate) && (tep_not_null(CURRENCY_SERVER_BACKUP))) {
            $messageStack->add_session(sprintf(WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency['title'], $currency['code']), 'warning');

            $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
            $rate = $quote_function($currency['code']);

            $server_used = CURRENCY_SERVER_BACKUP;
          }

          if (tep_not_null($rate)) {
            tep_db_query("update " . TABLE_CURRENCIES . " set value = '" . $rate . "', last_updated = now() where currencies_id = '" . (int)$currency['currencies_id'] . "'");

            $messageStack->add_session(sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency['title'], $currency['code'], $server_used), 'success');
          } else {
            $messageStack->add_session(sprintf(ERROR_CURRENCY_INVALID, $currency['title'], $currency['code'], $server_used), 'error');
          }
        }

        tep_redirect(tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $HTTP_GET_VARS['cID']));
        break;
      case 'delete':
        $currencies_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        $currency_query = tep_db_query("select code from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");
        $currency = tep_db_fetch_array($currency_query);

        $remove_currency = true;
        if ($currency['code'] == DEFAULT_CURRENCY) {
          $remove_currency = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_CURRENCY, 'error');
        }
        break;
    }
  }

  $currency_select = array('USD' => array('title' => 'U.S. Dollar', 'code' => 'USD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'EUR' => array('title' => 'Euro', 'code' => 'EUR', 'symbol_left' => '', 'symbol_right' => '€', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'JPY' => array('title' => 'Japanese Yen', 'code' => 'JPY', 'symbol_left' => '¥', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'GBP' => array('title' => 'Pounds Sterling', 'code' => 'GBP', 'symbol_left' => '£', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'CHF' => array('title' => 'Swiss Franc', 'code' => 'CHF', 'symbol_left' => '', 'symbol_right' => 'CHF', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'),
                           'AUD' => array('title' => 'Australian Dollar', 'code' => 'AUD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'CAD' => array('title' => 'Canadian Dollar', 'code' => 'CAD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'SEK' => array('title' => 'Swedish Krona', 'code' => 'SEK', 'symbol_left' => '', 'symbol_right' => 'kr', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'),
                           'HKD' => array('title' => 'Hong Kong Dollar', 'code' => 'HKD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'NOK' => array('title' => 'Norwegian Krone', 'code' => 'NOK', 'symbol_left' => 'kr', 'symbol_right' => '', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'),
                           'NZD' => array('title' => 'New Zealand Dollar', 'code' => 'NZD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'MXN' => array('title' => 'Mexican Peso', 'code' => 'MXN', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'SGD' => array('title' => 'Singapore Dollar', 'code' => 'SGD', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'BRL' => array('title' => 'Brazilian Real', 'code' => 'BRL', 'symbol_left' => 'R$', 'symbol_right' => '', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'),
                           'CNY' => array('title' => 'Chinese RMB', 'code' => 'CNY', 'symbol_left' => '￥', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'CZK' => array('title' => 'Czech Koruna', 'code' => 'CZK', 'symbol_left' => '', 'symbol_right' => 'Kč', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'),
                           'DKK' => array('title' => 'Danish Krone', 'code' => 'DKK', 'symbol_left' => '', 'symbol_right' => 'kr', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'),
                           'HUF' => array('title' => 'Hungarian Forint', 'code' => 'HUF', 'symbol_left' => '', 'symbol_right' => 'Ft', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'ILS' => array('title' => 'Israeli New Shekel', 'code' => 'ILS', 'symbol_left' => '₪', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'INR' => array('title' => 'Indian Rupee', 'code' => 'INR', 'symbol_left' => 'Rs.', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'MYR' => array('title' => 'Malaysian Ringgit', 'code' => 'MYR', 'symbol_left' => 'RM', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'PHP' => array('title' => 'Philippine Peso', 'code' => 'PHP', 'symbol_left' => 'Php', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'PLN' => array('title' => 'Polish Zloty', 'code' => 'PLN', 'symbol_left' => '', 'symbol_right' => 'zł', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2'),
                           'THB' => array('title' => 'Thai Baht', 'code' => 'THB', 'symbol_left' => '', 'symbol_right' => '฿', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'),
                           'TWD' => array('title' => 'Taiwan New Dollar', 'code' => 'TWD', 'symbol_left' => 'NT$', 'symbol_right' => '', 'decimal_point' => '.', 'thousands_point' => ',', 'decimal_places' => '2'));

  $currency_select_array = array(array('id' => '', 'text' => TEXT_INFO_COMMON_CURRENCIES));
  foreach ($currency_select as $cs) {
    if (!isset($currencies->currencies[$cs['code']])) {
      $currency_select_array[] = array('id' => $cs['code'], 'text' => '[' . $cs['code'] . '] ' . $cs['title']);
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<script type="text/javascript">
var currency_select = new Array();
<?php
  foreach ($currency_select_array as $cs) {
    if (!empty($cs['id'])) {
      echo 'currency_select["' . $cs['id'] . '"] = new Array("' . $currency_select[$cs['id']]['title'] . '", "' . $currency_select[$cs['id']]['symbol_left'] . '", "' . $currency_select[$cs['id']]['symbol_right'] . '", "' . $currency_select[$cs['id']]['decimal_point'] . '", "' . $currency_select[$cs['id']]['thousands_point'] . '", "' . $currency_select[$cs['id']]['decimal_places'] . '");' . "\n";
    }
  }
?>

function updateForm() {
  var cs = document.forms["currencies"].cs[document.forms["currencies"].cs.selectedIndex].value;

  document.forms["currencies"].title.value = currency_select[cs][0];
  document.forms["currencies"].code.value = cs;
  document.forms["currencies"].symbol_left.value = currency_select[cs][1];
  document.forms["currencies"].symbol_right.value = currency_select[cs][2];
  document.forms["currencies"].decimal_point.value = currency_select[cs][3];
  document.forms["currencies"].thousands_point.value = currency_select[cs][4];
  document.forms["currencies"].decimal_places.value = currency_select[cs][5];
  document.forms["currencies"].value.value = 1;
}
</script>

  <h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">	
    <div class="col-md-8">            
        <table class="table table-hover table-responsive table-striped">
          <thead>
		    <tr>
                <th><?php echo TABLE_HEADING_CURRENCY_NAME; ?></th>
                <th><?php echo TABLE_HEADING_CURRENCY_CODES; ?></th>
                <th><?php echo TABLE_HEADING_CURRENCY_VALUE; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			 </thead>
          <tbody>  
<?php
  $currency_query_raw = "select currencies_id, title, code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, last_updated, value from " . TABLE_CURRENCIES . " order by title";
  $currency_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $currency_query_raw, $currency_query_numrows);
  $currency_query = tep_db_query($currency_query_raw);
  while ($currency = tep_db_fetch_array($currency_query)) {
    if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $currency['currencies_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($currency);
    }

    if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id) ) {
      echo '  <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '\'">';
    } else {
      echo '  <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $currency['currencies_id']) . '\'">';
    }

    if (DEFAULT_CURRENCY == $currency['code']) {
      echo '                <td><strong>' . $currency['title'] . ' (' . TEXT_DEFAULT . ')</strong></td>' . "\n";
    } else {
      echo '                <td>' . $currency['title'] . '</td>' . "\n";
    }
?>
                <td><?php echo $currency['code']; ?></td>
                <td><?php echo number_format($currency['value'], 8); ?></td>
                <td class="text-right"><?php if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $currency['currencies_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
              </tr>
<?php
  }
?>
         </tbody>
        </table>
		 
		<div class="row">
                 
<?php
  if (empty($action)) {
?>
         
			<div class="col-md-6">
			<?php if (CURRENCY_SERVER_PRIMARY) { ?>
				<a class="btn btn-info" id="btnStartUploads" href="<?php echo tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id . '&action=update'); ?>" data-loading-text="<i class='fa fa-refresh fa-spin'></i> Updating..." role="button"><?php echo '<i class="fa fa-refresh"></i> ' . IMAGE_UPDATE_CURRENCIES; ?></a>
			<?php } ?>

<script>
// button state demo
$("#btnStartUploads").click(function(){
$("#btnStartUploads").button('loading');
});
</script>
			</div>
            <div class="col-md-6 text-right mb15">			
                  <?php echo tep_draw_button(IMAGE_NEW_CURRENCY, 'fa fa-plus', tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id . '&action=new'), 'primary', null, 'btn-default'); ?>
            </div>          
                   
<?php
  }
?>
          <div class="col-md-5">
            <?php echo $currency_split->display_count($currency_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_CURRENCIES); ?>
          </div>
          <div class="col-md-3 pull-right text-right">              
			  <?php echo $currency_split->display_links($currency_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
          </div>
	    </div> <!-- EOF row -->
    </div> <!-- EOF col-md-8 -->
    <div class="col-md-4">			  
<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_CURRENCY . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . (isset($cInfo) ? '&cID=' . $cInfo->currencies_id : '') . '&action=insert') . TEXT_INFO_INSERT_INTRO .
				'<br /><br />' . tep_draw_pull_down_menu('cs', $currency_select_array, '', 'onchange="updateForm();"') .
				'<br />' . TEXT_INFO_CURRENCY_TITLE . '<br />' . tep_draw_input_field('title') .
				'<br />' . TEXT_INFO_CURRENCY_CODE . '<br />' . tep_draw_input_field('code') .
				'<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br />' . tep_draw_input_field('symbol_left') .
				'<br />' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br />' . tep_draw_input_field('symbol_right') .
				'<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br />' . tep_draw_input_field('decimal_point') .
				'<br />' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br />' . tep_draw_input_field('thousands_point') .
				'<br />' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br />' . tep_draw_input_field('decimal_places') .
				'<br />' . TEXT_INFO_CURRENCY_VALUE . '<br />' . tep_draw_input_field('value') .
				'<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $HTTP_GET_VARS['cID'])) . '</div>' .
				'</div></div>';
      break;
    case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id . '&action=save') . TEXT_INFO_EDIT_INTRO .
				'<br /><br />' . TEXT_INFO_CURRENCY_TITLE . '<br />' . tep_draw_input_field('title', $cInfo->title) .
				'<br />' . TEXT_INFO_CURRENCY_CODE . '<br />' . tep_draw_input_field('code', $cInfo->code) .
				'<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br />' . tep_draw_input_field('symbol_left', $cInfo->symbol_left) .
				'<br />' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br />' . tep_draw_input_field('symbol_right', $cInfo->symbol_right) .
				'<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br />' . tep_draw_input_field('decimal_point', $cInfo->decimal_point) .
				'<br />' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br />' . tep_draw_input_field('thousands_point', $cInfo->thousands_point) .
				'<br />' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br />' . tep_draw_input_field('decimal_places', $cInfo->decimal_places) .
				'<br />' . TEXT_INFO_CURRENCY_VALUE . '<br />' . tep_draw_input_field('value', $cInfo->value);
				if (DEFAULT_CURRENCY != $cInfo->code) echo '<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT;
		   echo '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id)) . '</div>' . 
				'</div></div>';
      break;
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</span></div>';	  

		echo '<div class="panel-body">' .
				TEXT_INFO_DELETE_INTRO . 
				'<br /><br /><strong>' . $cInfo->title . '</strong>' .
				'<br /><br /><div class="text-center">' . (($remove_currency) ? tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id . '&action=deleteconfirm'), 'primary', null, 'btn-danger') : '') . 
															   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id)) . '</div>' .  
				'</div></div>';
      break;
    default:
      if (is_object($cInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $cInfo->title . '</strong></span></div>';	  

		echo '<div class="panel-body">' .		  
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit'), 'primary', null, 'btn-warning') . 
										 '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_CURRENCIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->currencies_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
				'<br />' . TEXT_INFO_CURRENCY_TITLE . ' ' . $cInfo->title .
				'<br />' . TEXT_INFO_CURRENCY_CODE . ' ' . $cInfo->code . 
				'<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . ' ' . $cInfo->symbol_left . 
				'<br />' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . ' ' . $cInfo->symbol_right .
				'<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . ' ' . $cInfo->decimal_point .
				'<br />' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . ' ' . $cInfo->thousands_point .
				'<br />' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . ' ' . $cInfo->decimal_places .
				'<br />' . TEXT_INFO_CURRENCY_LAST_UPDATED . ' ' . tep_date_short($cInfo->last_updated) .
				'<br />' . TEXT_INFO_CURRENCY_VALUE . ' ' . number_format($cInfo->value, 8) .
				'<br />' . TEXT_INFO_CURRENCY_EXAMPLE . '<br />' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code) .
				'</div></div>';
      }
      break;
  }
?>
	</div> <!-- EOF col-md-4 //-->
</div> <!-- EOF row //-->
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
