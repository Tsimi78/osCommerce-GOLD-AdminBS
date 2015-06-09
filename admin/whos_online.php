<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  $xx_mins_ago = (time() - 900);

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

// remove entries that have expired
  tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

   <h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">
	<div class="col-md-8">
	<table class="table table-hover table-condensed table-responsive table-striped">
		<thead>     
			<tr>
                <th><?php echo TABLE_HEADING_ONLINE; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_CUSTOMER_ID; ?></th>
                <th><?php echo TABLE_HEADING_FULL_NAME; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_IP_ADDRESS; ?></th>
                <th><?php echo TABLE_HEADING_ENTRY_TIME; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_LAST_CLICK; ?></th>
                <th><?php echo TABLE_HEADING_LAST_PAGE_URL; ?></th>
              </tr>
		</thead>
		<tbody>			  
<?php
  $whos_online_query = tep_db_query("select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id from " . TABLE_WHOS_ONLINE);
  while ($whos_online = tep_db_fetch_array($whos_online_query)) {
    $time_online = (time() - $whos_online['time_entry']);
    if ((!isset($HTTP_GET_VARS['info']) || (isset($HTTP_GET_VARS['info']) && ($HTTP_GET_VARS['info'] == $whos_online['session_id']))) && !isset($info)) {
      $info = new ObjectInfo($whos_online);
    }

    if (isset($info) && ($whos_online['session_id'] == $info->session_id)) {
      echo '  <tr class="info">';
    } else {
      echo '  <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_WHOS_ONLINE, tep_get_all_get_params(array('info', 'action')) . 'info=' . $whos_online['session_id'], 'NONSSL') . '\'">';
    }
?>
                <td><?php echo gmdate('H:i:s', $time_online); ?></td>
                <td class="text-center"><?php echo $whos_online['customer_id']; ?></td>
                <td><?php echo $whos_online['full_name']; ?></td>
                <td class="text-center"><?php echo $whos_online['ip_address']; ?></td>
                <td><?php echo date('H:i:s', $whos_online['time_entry']); ?></td>
                <td class="text-center"><?php echo date('H:i:s', $whos_online['time_last_click']); ?></td>
                <td><?php if (preg_match('/^(.*)osCsid=[A-Z0-9,-]+[&]*(.*)/i', $whos_online['last_page_url'], $array)) { echo $array[1] . $array[2]; } else { echo $whos_online['last_page_url']; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
            
  		</tbody>
	</table>
    <p><?php echo sprintf(TEXT_NUMBER_OF_CUSTOMERS, tep_db_num_rows($whos_online_query)); ?></p>
 	
	</div> <!-- EOF col-md-8 //-->        
	<div class="col-md-4">	   
	
<?php
  if (isset($info)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . TABLE_HEADING_SHOPPING_CART . '</strong></span></div>';
		
		echo '<div class="panel-body">';
    if ( $info->customer_id > 0 ) {
      $products_query = tep_db_query("select cb.customers_basket_quantity, cb.products_id, pd.products_name from " . TABLE_CUSTOMERS_BASKET . " cb, " . TABLE_PRODUCTS_DESCRIPTION . " pd where cb.customers_id = '" . (int)$info->customer_id . "' and cb.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'");

      if ( tep_db_num_rows($products_query) ) {
        $shoppingCart = new shoppingCart();

        while ( $products = tep_db_fetch_array($products_query) ) {
          echo '<br />' . $products['customers_basket_quantity'] . ' x ' . $products['products_name'];

          $attributes = array();

          if ( strpos($products['products_id'], '{') !== false ) {
            $combos = array();
            preg_match_all('/(\{[0-9]+\}[0-9]+){1}/', $products['products_id'], $combos);

            foreach ( $combos[0] as $combo ) {
              $att = array();
              preg_match('/\{([0-9]+)\}([0-9]+)/', $combo, $att);

              $attributes[$att[1]] = $att[2];
            }
          }

          $shoppingCart->add_cart(tep_get_prid($products['products_id']), $products['customers_basket_quantity'], $attributes);
        }

         echo '<br />' . tep_draw_separator('pixel_black.gif', '100%', '1');
         echo '<br /><div class="text-right">' . TEXT_SHOPPING_CART_SUBTOTAL . ' ' . $currencies->format($shoppingCart->show_total()) . '</div>';
      } else {
        echo '';
      }
	  echo '</div></div>';
    } else {
      echo 'N/A';
    }
  }
?>

  </div> <!-- EOF col-md-4 //--> 
 </div> <!-- EOF row //--> 

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
