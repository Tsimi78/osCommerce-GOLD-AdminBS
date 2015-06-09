<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  tep_db_query("update " . TABLE_PRODUCTS . " set products_date_available = '' where to_days(now()) > to_days(products_date_available)");

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">	
       <div class="col-md-8">            
        <table class="table table-hover table-responsive table-striped">
          <thead>
		    <tr>
              <th><?php echo TABLE_HEADING_PRODUCTS; ?></th>
              <th class="text-center"><?php echo TABLE_HEADING_DATE_EXPECTED; ?></th>
              <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
		  </thead>
		  <tbody>
<?php
  $products_query_raw = "select pd.products_id, pd.products_name, p.products_date_available from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p where p.products_id = pd.products_id and p.products_date_available != '' and pd.language_id = '" . (int)$languages_id . "' order by p.products_date_available DESC";
  $products_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
  $products_query = tep_db_query($products_query_raw);
  while ($products = tep_db_fetch_array($products_query)) {
    if ((!isset($HTTP_GET_VARS['pID']) || (isset($HTTP_GET_VARS['pID']) && ($HTTP_GET_VARS['pID'] == $products['products_id']))) && !isset($pInfo)) {
      $pInfo = new objectInfo($products);
    }

    if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) {
      echo '  <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'pID=' . $products['products_id'] . '&action=new_product') . '\'">';
    } else {
      echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $HTTP_GET_VARS['page'] . '&pID=' . $products['products_id']) . '\'">';
    }
?>
                <td><?php echo $products['products_name']; ?></td>
                <td class="text-center"><?php echo tep_date_short($products['products_date_available']); ?></td>
                <td class="text-right"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $HTTP_GET_VARS['page'] . '&pID=' . $products['products_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
              </tr>
<?php
  }
?>
             </tbody>
			</table>
			<div class="row">
				<div class="col-md-5">
					<?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS_EXPECTED); ?></td>
				</div>
				<div class="col-md-3 pull-right text-right">
					<?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
				</div>
			</div>	 
		</div> <!-- EOF col-md-8 -->
        <div class="col-md-4">			  
<?php
  if (isset($pInfo) && is_object($pInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $pInfo->products_name  . '</strong></span></div>';
			
		echo '<div class="panel-body">' .		  
			 '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_CATEGORIES, 'pID=' . $pInfo->products_id . '&action=new_product'), 'primary', null, 'btn-warning') . '</div>' .
			 '<br />' . TEXT_INFO_DATE_EXPECTED . ' ' . tep_date_short($pInfo->products_date_available) .
			 '</div></div>';
  }
?>

    </div> <!-- EOF col-md-4 //--> 
</div> <!-- EOF row //--> 

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>