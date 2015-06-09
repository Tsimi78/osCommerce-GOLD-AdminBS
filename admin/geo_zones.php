<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $saction = (isset($HTTP_GET_VARS['saction']) ? $HTTP_GET_VARS['saction'] : '');

  if (tep_not_null($saction)) {
    switch ($saction) {
      case 'insert_sub':
        $zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);
        $zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
        $zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);

        tep_db_query("insert into " . TABLE_ZONES_TO_GEO_ZONES . " (zone_country_id, zone_id, geo_zone_id, date_added) values ('" . (int)$zone_country_id . "', '" . (int)$zone_id . "', '" . (int)$zID . "', now())");
        $new_subzone_id = tep_db_insert_id();

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $new_subzone_id));
        break;
      case 'save_sub':
        $sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);
        $zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);
        $zone_country_id = tep_db_prepare_input($HTTP_POST_VARS['zone_country_id']);
        $zone_id = tep_db_prepare_input($HTTP_POST_VARS['zone_id']);

        tep_db_query("update " . TABLE_ZONES_TO_GEO_ZONES . " set geo_zone_id = '" . (int)$zID . "', zone_country_id = '" . (int)$zone_country_id . "', zone_id = " . (tep_not_null($zone_id) ? "'" . (int)$zone_id . "'" : 'null') . ", last_modified = now() where association_id = '" . (int)$sID . "'");

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $HTTP_GET_VARS['sID']));
        break;
      case 'deleteconfirm_sub':
        $sID = tep_db_prepare_input($HTTP_GET_VARS['sID']);

        tep_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where association_id = '" . (int)$sID . "'");

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage']));
        break;
    }
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert_zone':
        $geo_zone_name = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_name']);
        $geo_zone_description = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_description']);

        tep_db_query("insert into " . TABLE_GEO_ZONES . " (geo_zone_name, geo_zone_description, date_added) values ('" . tep_db_input($geo_zone_name) . "', '" . tep_db_input($geo_zone_description) . "', now())");
        $new_zone_id = tep_db_insert_id();

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $new_zone_id));
        break;
      case 'save_zone':
        $zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);
        $geo_zone_name = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_name']);
        $geo_zone_description = tep_db_prepare_input($HTTP_POST_VARS['geo_zone_description']);

        tep_db_query("update " . TABLE_GEO_ZONES . " set geo_zone_name = '" . tep_db_input($geo_zone_name) . "', geo_zone_description = '" . tep_db_input($geo_zone_description) . "', last_modified = now() where geo_zone_id = '" . (int)$zID . "'");

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID']));
        break;
      case 'deleteconfirm_zone':
        $zID = tep_db_prepare_input($HTTP_GET_VARS['zID']);

        tep_db_query("delete from " . TABLE_GEO_ZONES . " where geo_zone_id = '" . (int)$zID . "'");
        tep_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$zID . "'");

        tep_redirect(tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage']));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');

  if (isset($HTTP_GET_VARS['zID']) && (($saction == 'edit') || ($saction == 'new'))) {
?>
<script type="text/javascript"><!--
function resetZoneSelected(theForm) {
  if (theForm.state.value != '') {
    theForm.zone_id.selectedIndex = '0';
    if (theForm.zone_id.options.length > 0) {
      theForm.state.value = '<?php echo JS_STATE_SELECT; ?>';
    }
  }
}

function update_zone(theForm) {
  var NumState = theForm.zone_id.options.length;
  var SelectedCountry = "";

  while(NumState > 0) {
    NumState--;
    theForm.zone_id.options[NumState] = null;
  }         

  SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;

<?php echo tep_js_zone_list('SelectedCountry', 'theForm', 'zone_id'); ?>

}
//--></script>
<?php
  }
?>

<h3><?php echo HEADING_TITLE; if (isset($HTTP_GET_VARS['zone'])) echo '<br /><span class="smallText">' . tep_get_geo_zone_name($HTTP_GET_VARS['zone']) . '</span>'; ?></h3>
<div class="row">
    <div class="col-md-8">   
<?php
  if ($action == 'list') {
?>            
        <table class="table table-hover table-responsive table-striped">
          <thead>
		    <tr>
                <th><?php echo TABLE_HEADING_COUNTRY; ?></th>
                <th><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
		   <thead>
          <tbody>		   
<?php
    $rows = 0;
    $zones_query_raw = "select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_GEO_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.geo_zone_id = " . $HTTP_GET_VARS['zID'] . " order by association_id";
    $zones_split = new splitPageResults($HTTP_GET_VARS['spage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
    $zones_query = tep_db_query($zones_query_raw);
    while ($zones = tep_db_fetch_array($zones_query)) {
      $rows++;
      if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $zones['association_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
        $sInfo = new objectInfo($zones);
      }
      if (isset($sInfo) && is_object($sInfo) && ($zones['association_id'] == $sInfo->association_id)) {
        echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '\'">';
      } else {
        echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $zones['association_id']) . '\'">';
      }
?>
                <td><?php echo (($zones['countries_name']) ? $zones['countries_name'] : TEXT_ALL_COUNTRIES); ?></td>
                <td><?php echo (($zones['zone_id']) ? $zones['zone_name'] : PLEASE_SELECT); ?></td>
                <td class="text-right"><?php if (isset($sInfo) && is_object($sInfo) && ($zones['association_id'] == $sInfo->association_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $zones['association_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
              </tr>
<?php
    }
?>
          </tbody>
        </table>
		<div class="row"> 
			<div class="col-md-12 mb15 text-right">
				<?php if (empty($saction)) echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'])) . '&nbsp;' . tep_draw_button(IMAGE_INSERT, 'fa fa-plus', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($sInfo) ? 'sID=' . $sInfo->association_id . '&' : '') . 'saction=new')); ?>
			</div>
			<div class="col-md-5">
				<?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['spage'], TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?>
			</div>
			<div class="col-md-3 pull-right text-right">    
				<?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['spage'], 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list', 'spage'); ?>
			</div>
		</div>
<?php
  } else {
?>
        <table class="table table-hover table-responsive table-striped">
           <thead>
		      <tr>
                <th><?php echo TABLE_HEADING_TAX_ZONES; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
		    </thead>
           <tbody> 			
<?php
    $zones_query_raw = "select geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added from " . TABLE_GEO_ZONES . " order by geo_zone_name";
    $zones_split = new splitPageResults($HTTP_GET_VARS['zpage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
    $zones_query = tep_db_query($zones_query_raw);
    while ($zones = tep_db_fetch_array($zones_query)) {
      if ((!isset($HTTP_GET_VARS['zID']) || (isset($HTTP_GET_VARS['zID']) && ($HTTP_GET_VARS['zID'] == $zones['geo_zone_id']))) && !isset($zInfo) && (substr($action, 0, 3) != 'new')) {
        $num_zones_query = tep_db_query("select count(*) as num_zones from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)$zones['geo_zone_id'] . "' group by geo_zone_id");
        $num_zones = tep_db_fetch_array($num_zones_query);

        if ($num_zones['num_zones'] > 0) {
          $zones['num_zones'] = $num_zones['num_zones'];
        } else {
          $zones['num_zones'] = 0;
        }

        $zInfo = new objectInfo($zones);
      }
      if (isset($zInfo) && is_object($zInfo) && ($zones['geo_zone_id'] == $zInfo->geo_zone_id)) {
        echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '\'">';
      } else {
        echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zones['geo_zone_id']) . '\'">';
      }
?>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zones['geo_zone_id'] . '&action=list') . '"><i class="fa fa-folder fa-lg"></i></a>&nbsp;' . $zones['geo_zone_name']; ?></td>
                <td class="text-right"><?php if (isset($zInfo) && is_object($zInfo) && ($zones['geo_zone_id'] == $zInfo->geo_zone_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zones['geo_zone_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
              </tr>
<?php
    }
?>
          </tbody>
        </table>
	<div class="row">
		<div class="col-md-12 mb15 text-right">
				 <?php if (!$action) echo tep_draw_button(IMAGE_INSERT, 'fa fa-plus', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=new_zone')); ?>
		</div>
		<div class="col-md-5">	
				 <?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['zpage'], TEXT_DISPLAY_NUMBER_OF_TAX_ZONES); ?>
		</div>
		<div class="col-md-3 pull-right text-right">	
				 <?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['zpage'], '', 'zpage'); ?>
		</div>            
	</div>				
              
<?php
  }
?>
    </div> <!-- EOF col-md-8 -->
	<div class="col-md-4">			  
<?php
  if ($action == 'list') {
    switch ($saction) {
      case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_SUB_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .	  
				tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($HTTP_GET_VARS['sID']) ? 'sID=' . $HTTP_GET_VARS['sID'] . '&' : '') . 'saction=insert_sub') .
				TEXT_INFO_NEW_SUB_ZONE_INTRO .
				'<br /><br />' . TEXT_INFO_COUNTRY . '<br />' . tep_draw_pull_down_menu('zone_country_id', tep_get_countries(TEXT_ALL_COUNTRIES), '', 'onchange="update_zone(this.form);"') .
				'<br />' . TEXT_INFO_COUNTRY_ZONE . '<br />' . tep_draw_pull_down_menu('zone_id', tep_prepare_country_zones_pull_down()) .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&' . (isset($HTTP_GET_VARS['sID']) ? 'sID=' . $HTTP_GET_VARS['sID'] : ''))) . '</div>' .
				'</div></div>';										 
        break;
      case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .	  
				tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=save_sub') .
				TEXT_INFO_EDIT_SUB_ZONE_INTRO .
				'<br /><br />' . TEXT_INFO_COUNTRY . '<br />' . tep_draw_pull_down_menu('zone_country_id', tep_get_countries(TEXT_ALL_COUNTRIES), $sInfo->zone_country_id, 'onchange="update_zone(this.form);"') .
				'<br />' . TEXT_INFO_COUNTRY_ZONE . '<br />' . tep_draw_pull_down_menu('zone_id', tep_prepare_country_zones_pull_down($sInfo->zone_country_id), $sInfo->zone_id) .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-primary') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id)) . '</div>' .
				'</div></div>';	
        break;
      case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .	  
				tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=deleteconfirm_sub') .
				TEXT_INFO_DELETE_SUB_ZONE_INTRO .
				'<br /><br /><strong>' . $sInfo->countries_name . '</strong>' .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban -icon-red', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id)) . '</div>' .
				'</div></div>';	
        break;
      default:
        if (isset($sInfo) && is_object($sInfo)) {
			echo '<div class="panel panel-default">
					<div class="panel-heading"><span class="panel-title"><strong>' . $sInfo->countries_name . '</strong></span></div>';	  

			echo '<div class="panel-body">' .				
					'<div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit'), 'primary', null, 'btn-warning') . 
									   '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=list&spage=' . $HTTP_GET_VARS['spage'] . '&sID=' . $sInfo->association_id . '&saction=delete'), 'primary', null, 'btn-danger') . '</div>' .
					'<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($sInfo->date_added);
					if (tep_not_null($sInfo->last_modified)) echo '<br />' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($sInfo->last_modified);
					'</div></div>';	
		}
        break;
    }
  } else {
    switch ($action) {
      case 'new_zone':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .	  
				tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'] . '&action=insert_zone') . TEXT_INFO_NEW_ZONE_INTRO .
				'<br /><br />' . TEXT_INFO_ZONE_NAME . '<br />' . tep_draw_input_field('geo_zone_name') .
				'<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . tep_draw_input_field('geo_zone_description') .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $HTTP_GET_VARS['zID'])) . '</div>' .
				'</div></div>';	
        break;
      case 'edit_zone':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .		  
				tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=save_zone') . TEXT_INFO_EDIT_ZONE_INTRO .
				'<br />' . TEXT_INFO_ZONE_NAME . '<br />' . tep_draw_input_field('geo_zone_name', $zInfo->geo_zone_name) .
				'<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . tep_draw_input_field('geo_zone_description', $zInfo->geo_zone_description) .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id)) . '</div>' .
				'</div></div>';	
        break;
      case 'delete_zone':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_ZONE . '</span></div>';	  

		echo '<div class="panel-body">' .	  
				tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=deleteconfirm_zone') . TEXT_INFO_DELETE_ZONE_INTRO .
				'<br /><br /><strong>' . $zInfo->geo_zone_name . '</strong>' .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id)) . '</div>' .
				'</div></div>';	
        break;
      default:
        if (isset($zInfo) && is_object($zInfo)) {
			echo '<div class="panel panel-default">
					<div class="panel-heading"><span class="panel-title"><strong>' . $zInfo->geo_zone_name . '</strong></span></div>';	  

			echo '<div class="panel-body">			
					<div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=edit_zone'), 'primary', null, 'btn-warning') .
											'&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=delete_zone'), 'primary', null, 'btn-danger') . 
											'&nbsp;' . tep_draw_button(IMAGE_DETAILS, 'fa fa-search-plus', tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $HTTP_GET_VARS['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list'), 'primary', null, 'btn-info') . '</div>' . 
					'<br />' . TEXT_INFO_NUMBER_ZONES . ' ' . $zInfo->num_zones .
					'<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($zInfo->date_added);
					if (tep_not_null($zInfo->last_modified))  TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($zInfo->last_modified);
					'<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . $zInfo->geo_zone_description .
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
