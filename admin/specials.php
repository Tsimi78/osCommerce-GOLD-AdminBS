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
      case 'setflag':
        tep_set_specials_status($HTTP_GET_VARS['id'], $HTTP_GET_VARS['flag']);

        tep_redirect(tep_href_link(FILENAME_SPECIALS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'sID=' . $HTTP_GET_VARS['id'], 'NONSSL'));
        break;
      case 'insert':
        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $products_price = tep_db_prepare_input($HTTP_POST_VARS['products_price']);
        $specials_price = tep_db_prepare_input($HTTP_POST_VARS['specials_price']);
        $expdate = tep_db_prepare_input($HTTP_POST_VARS['expdate']);

        if (substr($specials_price, -1) == '%') {
          $new_special_insert_query = tep_db_query("select products_id, products_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
          $new_special_insert = tep_db_fetch_array($new_special_insert_query);

          $products_price = $new_special_insert['products_price'];
          $specials_price = ($products_price - (($specials_price / 100) * $products_price));
        }

        $expires_date = '';
        if (tep_not_null($expdate)) {
          $expires_date = substr($expdate, 0, 4) . substr($expdate, 5, 2) . substr($expdate, 8, 2);
        }

        tep_db_query("insert into " . TABLE_SPECIALS . " (products_id, specials_new_products_price, specials_date_added, expires_date, status) values ('" . (int)$products_id . "', '" . tep_db_input($specials_price) . "', now(), " . (tep_not_null($expires_date) ? "'" . tep_db_input($expires_date) . "'" : 'null') . ", '1')");

        tep_redirect(tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'update':
        $specials_id = tep_db_prepare_input($HTTP_POST_VARS['specials_id']);
        $products_price = tep_db_prepare_input($HTTP_POST_VARS['products_price']);
        $specials_price = tep_db_prepare_input($HTTP_POST_VARS['specials_price']);
        $expdate = tep_db_prepare_input($HTTP_POST_VARS['expdate']);

        if (substr($specials_price, -1) == '%') $specials_price = ($products_price - (($specials_price / 100) * $products_price));

        $expires_date = '';
        if (tep_not_null($expdate)) {
          $expires_date = substr($expdate, 0, 4) . substr($expdate, 5, 2) . substr($expdate, 8, 2);
        }

        tep_db_query("update " . TABLE_SPECIALS . " set specials_new_products_price = '" . tep_db_input($specials_price) . "', specials_last_modified = now(), expires_date = " . (tep_not_null($expires_date) ? "'" . tep_db_input($expires_date) . "'" : 'null') . " where specials_id = '" . (int)$specials_id . "'");

        tep_redirect(tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $specials_id));
        break;
      case 'deleteconfirm':
        $specials_id = tep_db_prepare_input($HTTP_GET_VARS['sID']);

        tep_db_query("delete from " . TABLE_SPECIALS . " where specials_id = '" . (int)$specials_id . "'");

        tep_redirect(tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

       <h3><?php echo HEADING_TITLE; ?></h3>
            
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
    $form_action = 'insert';
    if ( ($action == 'edit') && isset($HTTP_GET_VARS['sID']) ) {
      $form_action = 'update';

      $product_query = tep_db_query("select p.products_id, pd.products_name, p.products_price, s.specials_new_products_price, s.expires_date from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = s.products_id and s.specials_id = '" . (int)$HTTP_GET_VARS['sID'] . "'");
      $product = tep_db_fetch_array($product_query);

      $sInfo = new objectInfo($product);
    } else {
      $sInfo = new objectInfo(array());

// create an array of products on special, which will be excluded from the pull down menu of products
// (when creating a new product on special)
      $specials_array = array();
      $specials_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s where s.products_id = p.products_id");
      while ($specials = tep_db_fetch_array($specials_query)) {
        $specials_array[] = $specials['products_id'];
      }
    }
?>
    <form name="new_special" <?php echo 'action="' . tep_href_link(FILENAME_SPECIALS, tep_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action, 'NONSSL') . '"'; ?> method="post" class="form-horizontal"><?php if ($form_action == 'update') echo tep_draw_hidden_field('specials_id', $HTTP_GET_VARS['sID']); ?> 	
			<div class="form-group">
			<label class="control-label col-sm-2">
				<?php echo TEXT_SPECIALS_PRODUCT; ?>
			 </label>
			 <div class="col-sm-8">
			   <?php echo (isset($sInfo->products_name)) ? $sInfo->products_name . ' (' . $currencies->format($sInfo->products_price) . ')' : tep_draw_products_pull_down('products_id', 'class="form-control"', $specials_array); echo tep_draw_hidden_field('products_price', (isset($sInfo->products_price) ? $sInfo->products_price : '')); ?>
			</div>
			</div>
				         
		<div class="form-group">
           <label class="control-label col-sm-2">
		     <?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?>
		   </label>
            <div class="col-sm-8">
			 <?php echo tep_draw_input_field('specials_price', (isset($sInfo->specials_new_products_price) ? $sInfo->specials_new_products_price : '')); ?>
			</div>
        </div>
        <div class="form-group">
           <label class="control-label col-sm-2">
			 <?php echo TEXT_SPECIALS_EXPIRES_DATE; ?>
		   </label>	 
            <div class="col-sm-8">
			 <?php echo tep_draw_input_field('expdate', (tep_not_null($sInfo->expires_date) ? substr($sInfo->expires_date, 0, 4) . '-' . substr($sInfo->expires_date, 5, 2) . '-' . substr($sInfo->expires_date, 8, 2) : ''), 'id="expdate"') . ' <small>(YYYY-MM-DD)</small>'; ?>
          </div>
        </div>

<script type="text/javascript">
$('#expdate').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>

           <div class="alert alert-info">
			<?php echo TEXT_SPECIALS_PRICE_TIP; ?>
	       </div>	
           <div class="col-md-12 text-center"> 
			<?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . (isset($HTTP_GET_VARS['sID']) ? '&sID=' . $HTTP_GET_VARS['sID'] : ''))); ?>
          </div>
      </form>
<?php
  } else {
?>
<div class="row">
    <div class="col-md-8">   
       <table class="table table-hover table-responsive table-striped">
	    <thead>
          <tr>
            <th><?php echo TABLE_HEADING_PRODUCTS; ?></th>
            <th><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></th>
            <th><?php echo TABLE_HEADING_STATUS; ?></th>
            <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
          </tr>
		</thead>
        <tbody>		
<?php
    $specials_query_raw = "select p.products_id, pd.products_name, p.products_price, s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = s.products_id order by pd.products_name";
    $specials_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $specials_query_raw, $specials_query_numrows);
    $specials_query = tep_db_query($specials_query_raw);
    while ($specials = tep_db_fetch_array($specials_query)) {
      if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $specials['specials_id']))) && !isset($sInfo)) {
        $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$specials['products_id'] . "'");
        $products = tep_db_fetch_array($products_query);
        $sInfo_array = array_merge($specials, $products);
        $sInfo = new objectInfo($sInfo_array);
      }

      if (isset($sInfo) && is_object($sInfo) && ($specials['specials_id'] == $sInfo->specials_id)) {
        echo '  <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->specials_id . '&action=edit') . '\'">';
      } else {
        echo '  <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $specials['specials_id']) . '\'">';
      }
?>
			<td><?php echo $specials['products_name']; ?></td>
			<td><span class="oldPrice"><?php echo $currencies->format($specials['products_price']); ?></span> <span class="specialPrice"><?php echo $currencies->format($specials['specials_new_products_price']); ?></span></td>
			<td>
<?php
      if ($specials['status'] == '1') {
        echo '&nbsp;<i class="fa fa-circle icon-green"></i>&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, 'action=setflag&flag=0&id=' . $specials['specials_id'], 'NONSSL') . '"><i class="fa fa-circle icon-gray"></i></a>';
      } else {
        echo '&nbsp;<a href="' . tep_href_link(FILENAME_SPECIALS, 'action=setflag&flag=1&id=' . $specials['specials_id'], 'NONSSL') . '"><i class="fa fa-circle icon-gray"></i></a>&nbsp;&nbsp;<i class="fa fa-circle icon-red"></i>';
      }
?>          
			</td>
			<td class="text-right"><?php if (isset($sInfo) && is_object($sInfo) && ($specials['specials_id'] == $sInfo->specials_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $specials['specials_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
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
	<div class="col-md-12 mb10 text-right">
		 <?php echo tep_draw_button(IMAGE_NEW_PRODUCT, 'fa fa-plus', tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&action=new'), '', null, 'btn-default'); ?>
	</div>      
<?php
  }
?>				
	<div class="col-md-5">
		<?php echo $specials_split->display_count($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?>
	</div>
	<div class="col-md-3 pull-right text-right">  
		<?php echo $specials_split->display_links($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
	</div>  
</div>		
	<div class="clearfix"></div>
	</div> <!-- EOF col-md-8 -->       
	<div class="col-md-4">			  

<?php
  switch ($action) {
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</span></div>';
			
		echo '<div class="panel-body">' .
			 tep_draw_form('specials', FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->specials_id . '&action=deleteconfirm') . TEXT_INFO_DELETE_INTRO .
			 '<br /><br /><strong>' . $sInfo->products_name . '</strong>' .
			 '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->specials_id));
      break;
    default:
      if (is_object($sInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $sInfo->products_name . '</strong></span></div>';
			
		echo '<div class="panel-body">' .		  
			 '<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->specials_id . '&action=edit'), 'primary', null, 'btn-warning') . 
									  '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_SPECIALS, 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->specials_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
			 '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($sInfo->specials_date_added) .
			 '<br />' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($sInfo->specials_last_modified) .
			 '<br /><br />' . tep_info_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) .
			 '<br /><br />' . TEXT_INFO_ORIGINAL_PRICE . ' ' . $currencies->format($sInfo->products_price) .
			 '<br />' . TEXT_INFO_NEW_PRICE . ' ' . $currencies->format($sInfo->specials_new_products_price) .
			 '<br />' . TEXT_INFO_PERCENTAGE . ' ' . number_format(100 - (($sInfo->specials_new_products_price / $sInfo->products_price) * 100)) . '%' .
			 '<br />' . TEXT_INFO_EXPIRES_DATE . ' <strong>' . tep_date_short($sInfo->expires_date) . '</strong>' .
			 '<br />' . TEXT_INFO_STATUS_CHANGE . ' ' . tep_date_short($sInfo->date_status_change);
      }
      break;
  }
}
?>

  </div> <!-- EOF col-md-4 //--> 
</div>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
