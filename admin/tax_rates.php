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

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        $tax_zone_id = tep_db_prepare_input($HTTP_POST_VARS['tax_zone_id']);
        $tax_class_id = tep_db_prepare_input($HTTP_POST_VARS['tax_class_id']);
        $tax_rate = tep_db_prepare_input($HTTP_POST_VARS['tax_rate']);
        $tax_description = tep_db_prepare_input($HTTP_POST_VARS['tax_description']);
        $tax_priority = tep_db_prepare_input($HTTP_POST_VARS['tax_priority']);

        tep_db_query("insert into " . TABLE_TAX_RATES . " (tax_zone_id, tax_class_id, tax_rate, tax_description, tax_priority, date_added) values ('" . (int)$tax_zone_id . "', '" . (int)$tax_class_id . "', '" . tep_db_input($tax_rate) . "', '" . tep_db_input($tax_description) . "', '" . tep_db_input($tax_priority) . "', now())");

        tep_redirect(tep_href_link(FILENAME_TAX_RATES));
        break;
      case 'save':
        $tax_rates_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);
        $tax_zone_id = tep_db_prepare_input($HTTP_POST_VARS['tax_zone_id']);
        $tax_class_id = tep_db_prepare_input($HTTP_POST_VARS['tax_class_id']);
        $tax_rate = tep_db_prepare_input($HTTP_POST_VARS['tax_rate']);
        $tax_description = tep_db_prepare_input($HTTP_POST_VARS['tax_description']);
        $tax_priority = tep_db_prepare_input($HTTP_POST_VARS['tax_priority']);

        tep_db_query("update " . TABLE_TAX_RATES . " set tax_rates_id = '" . (int)$tax_rates_id . "', tax_zone_id = '" . (int)$tax_zone_id . "', tax_class_id = '" . (int)$tax_class_id . "', tax_rate = '" . tep_db_input($tax_rate) . "', tax_description = '" . tep_db_input($tax_description) . "', tax_priority = '" . tep_db_input($tax_priority) . "', last_modified = now() where tax_rates_id = '" . (int)$tax_rates_id . "'");

        tep_redirect(tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tax_rates_id));
        break;
      case 'deleteconfirm':
        $tax_rates_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);

        tep_db_query("delete from " . TABLE_TAX_RATES . " where tax_rates_id = '" . (int)$tax_rates_id . "'");

        tep_redirect(tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

     <h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">
    <div class="col-md-8">           
        <table class="table table-hover table-responsive table-striped">
          <thead>
		    <tr>
                <th><?php echo TABLE_HEADING_TAX_RATE_PRIORITY; ?></th>
                <th><?php echo TABLE_HEADING_TAX_CLASS_TITLE; ?></th>
                <th><?php echo TABLE_HEADING_ZONE; ?></th>
                <th><?php echo TABLE_HEADING_TAX_RATE; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			</thead>
          <tbody>  
<?php
  $rates_query_raw = "select r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_GEO_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id";
  $rates_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $rates_query_raw, $rates_query_numrows);
  $rates_query = tep_db_query($rates_query_raw);
  while ($rates = tep_db_fetch_array($rates_query)) {
    if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $rates['tax_rates_id']))) && !isset($trInfo) && (substr($action, 0, 3) != 'new')) {
      $trInfo = new objectInfo($rates);
    }

    if (isset($trInfo) && is_object($trInfo) && ($rates['tax_rates_id'] == $trInfo->tax_rates_id)) {
      echo '   <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '\'">';
    } else {
      echo '   <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $rates['tax_rates_id']) . '\'">';
    }
?>
                <td><?php echo $rates['tax_priority']; ?></td>
                <td><?php echo $rates['tax_class_title']; ?></td>
                <td><?php echo $rates['geo_zone_name']; ?></td>
                <td><?php echo tep_display_tax_value($rates['tax_rate']); ?>%</td>
                <td class="text-right"><?php if (isset($trInfo) && is_object($trInfo) && ($rates['tax_rates_id'] == $trInfo->tax_rates_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $rates['tax_rates_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
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
        <div class="col-md-12 mb15 text-right">
            <?php echo tep_draw_button(IMAGE_NEW_TAX_RATE, 'fa fa-plus', tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&action=new'), 'primary', null, 'btn-default'); ?>
        </div>          
<?php
  }
?>
              <div class="col-md-5">
                   <?php echo $rates_split->display_count($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_TAX_RATES); ?>
              </div>
              <div class="col-md-3 pull-right text-right">	       
				   <?php echo $rates_split->display_links($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
           	  </div>
		</div>
	</div> <!-- EOF col-md-8 -->
    <div class="col-md-4">			  
<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_TAX_RATE . '</span></div>';	  

		echo '<div class="panel-body">' .		
				 tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&action=insert') . TEXT_INFO_INSERT_INTRO .
				 '<br /><br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_tax_classes_pull_down('name="tax_class_id"') .
				 '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id"') .
				 '<br />' . TEXT_INFO_TAX_RATE . '<br />' . tep_draw_input_field('tax_rate') .
				 '<br />' . TEXT_INFO_RATE_DESCRIPTION . '<br />' . tep_draw_input_field('tax_description') .
				 '<br />' . TEXT_INFO_TAX_RATE_PRIORITY . '<br />' . tep_draw_input_field('tax_priority') .
				 '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										  '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'])) . '</div>' .
				 '</div></div>';
      break;
    case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</span></div>';	  

		echo '<div class="panel-body">' .	
				 tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=save') . TEXT_INFO_EDIT_INTRO .
				 '<br /><br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_tax_classes_pull_down('name="tax_class_id"', $trInfo->tax_class_id) .
				 '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . tep_geo_zones_pull_down('name="tax_zone_id"', $trInfo->geo_zone_id) .
				 '<br />' . TEXT_INFO_TAX_RATE . '<br />' . tep_draw_input_field('tax_rate', $trInfo->tax_rate) .
				 '<br />' . TEXT_INFO_RATE_DESCRIPTION . '<br />' . tep_draw_input_field('tax_description', $trInfo->tax_description) .
				 '<br />' . TEXT_INFO_TAX_RATE_PRIORITY . '<br />' . tep_draw_input_field('tax_priority', $trInfo->tax_priority) .
				 '<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
				 						  '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $trInfo->tax_rates_id)) . '</div>' .
	  			 '</div></div>';
      break;
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_TAX_RATE . '</span></div>';	  

		echo '<div class="panel-body">' .		
				 tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=deleteconfirm') . TEXT_INFO_DELETE_INTRO .
				 '<br /><br /><strong>' . $trInfo->tax_class_title . ' ' . number_format($trInfo->tax_rate, TAX_DECIMAL_PLACES) . '%</strong>' .
				 '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											    '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $trInfo->tax_rates_id)) . '</div>' .
	  			 '</div></div>';
      break;
    default:
      if (is_object($trInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $trInfo->tax_class_title . '</strong></span></div>';	  

		echo '<div class="panel-body">' .			  
				 '<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit'), 'primary', null, 'btn-warning') . 
										  '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_TAX_RATES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
				 '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($trInfo->date_added) .
				 '<br />' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($trInfo->last_modified) .
				 '<br />' . TEXT_INFO_RATE_DESCRIPTION . '<br />' . $trInfo->tax_description .
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