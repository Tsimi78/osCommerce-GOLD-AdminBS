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
        $zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
        $zone_code = tep_db_prepare_input($HTTP_POST_VARS['zone_code']);
        $zone_name = tep_db_prepare_input($HTTP_POST_VARS['zone_name']);

        tep_db_query("insert into " . TABLE_ZONES . " (zone_country_id, zone_code, zone_name) values ('" . (int)$zone_country_id . "', '" . tep_db_input($zone_code) . "', '" . tep_db_input($zone_name) . "')");

        tep_redirect(tep_href_link(FILENAME_ZONES));
        break;
      case 'save':
        $zone_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);
        $zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
        $zone_code = tep_db_prepare_input($HTTP_POST_VARS['zone_code']);
        $zone_name = tep_db_prepare_input($HTTP_POST_VARS['zone_name']);

        tep_db_query("update " . TABLE_ZONES . " set zone_country_id = '" . (int)$zone_country_id . "', zone_code = '" . tep_db_input($zone_code) . "', zone_name = '" . tep_db_input($zone_name) . "' where zone_id = '" . (int)$zone_id . "'");

        tep_redirect(tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $zone_id));
        break;
      case 'deleteconfirm':
        $zone_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        tep_db_query("delete from " . TABLE_ZONES . " where zone_id = '" . (int)$zone_id . "'");

        tep_redirect(tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">
    <div class="col-md-8">              
        <table class="table table-hover table-condensed table-responsive table-striped">
          <thead>
		    <tr>
                <th><?php echo TABLE_HEADING_COUNTRY_NAME; ?></th>
                <th><?php echo TABLE_HEADING_ZONE_NAME; ?></th>
                <th><?php echo TABLE_HEADING_ZONE_CODE; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			</thead>
          <tbody>
			  
<?php
  $zones_query_raw = "select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.zone_country_id = c.countries_id order by c.countries_name, z.zone_name";
  $zones_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
  $zones_query = tep_db_query($zones_query_raw);
  while ($zones = tep_db_fetch_array($zones_query)) {
    if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $zones['zone_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($zones);
    }

    if (isset($cInfo) && is_object($cInfo) && ($zones['zone_id'] == $cInfo->zone_id)) {
      echo '  <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->zone_id . '&action=edit') . '\'">';
    } else {
      echo '  <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $zones['zone_id']) . '\'">';
    }
?>
                <td><?php echo $zones['countries_name']; ?></td>
                <td><?php echo $zones['zone_name']; ?></td>
                <td><?php echo $zones['zone_code']; ?></td>
                <td class="text-right"><?php if (isset($cInfo) && is_object($cInfo) && ($zones['zone_id'] == $cInfo->zone_id) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $zones['zone_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
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
					<?php echo tep_draw_button(IMAGE_NEW_ZONE, 'fa fa-plus', tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&action=new'), '', null, 'btn-default'); ?>
                </div> 
<?php
  }
?>
              <div class="col-md-5">
                <?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_ZONES); ?>
              </div>
              <div class="col-md-3 pull-right text-right">  
				<?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
              </div>
	    </div>
    </div> <!-- EOF col-md-8 -->
    <div class="col-md-4">			  
<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .		
				tep_draw_form('zones', FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&action=insert') . TEXT_INFO_INSERT_INTRO .
				'<br /><br />' . TEXT_INFO_ZONES_NAME . '<br />' . tep_draw_input_field('zone_name') . 
				'<br />' . TEXT_INFO_ZONES_CODE . '<br />' . tep_draw_input_field('zone_code') .
				'<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . tep_draw_pull_down_menu('zone_country_id', tep_get_countries()) .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'])) . '</div>' .
				'</div></div>';
      break;
    case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .		
				tep_draw_form('zones', FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->zone_id . '&action=save') . TEXT_INFO_EDIT_INTRO .
				'<br /><br />' . TEXT_INFO_ZONES_NAME . '<br />' . tep_draw_input_field('zone_name', $cInfo->zone_name) .
				'<br />' . TEXT_INFO_ZONES_CODE . '<br />' . tep_draw_input_field('zone_code', $cInfo->zone_code) .
				'<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . tep_draw_pull_down_menu('zone_country_id', tep_get_countries(), $cInfo->countries_id) .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->zone_id)) . '</div>' .
				'</div></div>';
      break;
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('zones', FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->zone_id . '&action=deleteconfirm') . TEXT_INFO_DELETE_INTRO .
				'<br /><br /><strong>' . $cInfo->zone_name . '</strong>' .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->zone_id)) . '</div>' .
				'</div></div>';
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $cInfo->zone_name . '</strong></span></div>';	  

		echo '<div class="panel-body">' .		  
				'<div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->zone_id . '&action=edit'), 'primary', null, 'btn-warning') . 
								   '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_ZONES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->zone_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
				'<br />' . TEXT_INFO_ZONES_NAME . '<br />' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')' . 
				'<br />' . TEXT_INFO_COUNTRY_NAME . ' ' . $cInfo->countries_name . 
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
