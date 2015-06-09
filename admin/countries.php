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
        $countries_name = tep_db_prepare_input($HTTP_POST_VARS['countries_name']);
        $countries_iso_code_2 = tep_db_prepare_input($HTTP_POST_VARS['countries_iso_code_2']);
        $countries_iso_code_3 = tep_db_prepare_input($HTTP_POST_VARS['countries_iso_code_3']);
        $address_format_id = tep_db_prepare_input($HTTP_POST_VARS['address_format_id']);

        tep_db_query("insert into " . TABLE_COUNTRIES . " (countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) values ('" . tep_db_input($countries_name) . "', '" . tep_db_input($countries_iso_code_2) . "', '" . tep_db_input($countries_iso_code_3) . "', '" . (int)$address_format_id . "')");

        tep_redirect(tep_href_link(FILENAME_COUNTRIES));
        break;
      case 'save':
        $countries_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);
        $countries_name = tep_db_prepare_input($HTTP_POST_VARS['countries_name']);
        $countries_iso_code_2 = tep_db_prepare_input($HTTP_POST_VARS['countries_iso_code_2']);
        $countries_iso_code_3 = tep_db_prepare_input($HTTP_POST_VARS['countries_iso_code_3']);
        $address_format_id = tep_db_prepare_input($HTTP_POST_VARS['address_format_id']);

        tep_db_query("update " . TABLE_COUNTRIES . " set countries_name = '" . tep_db_input($countries_name) . "', countries_iso_code_2 = '" . tep_db_input($countries_iso_code_2) . "', countries_iso_code_3 = '" . tep_db_input($countries_iso_code_3) . "', address_format_id = '" . (int)$address_format_id . "' where countries_id = '" . (int)$countries_id . "'");

        tep_redirect(tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $countries_id));
        break;
      case 'deleteconfirm':
        $countries_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        tep_db_query("delete from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$countries_id . "'");

        tep_redirect(tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

   <h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">   
    <div class="col-md-8">            
        <table class="table table-hover table-responsive table-condensed table-striped">
          <thead>
		    <tr>
              <th><?php echo TABLE_HEADING_COUNTRY_NAME; ?></th>
              <th><?php echo TABLE_HEADING_COUNTRY_CODES; ?></th>
              <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
		  </thead>
          <tbody>
		  
<?php
  $countries_query_raw = "select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id from " . TABLE_COUNTRIES . " order by countries_name";
  $countries_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $countries_query_raw, $countries_query_numrows);
  $countries_query = tep_db_query($countries_query_raw);
  while ($countries = tep_db_fetch_array($countries_query)) {
    if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $countries['countries_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cInfo = new objectInfo($countries);
    }

    if (isset($cInfo) && is_object($cInfo) && ($countries['countries_id'] == $cInfo->countries_id)) {
      echo '  <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->countries_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '  <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $countries['countries_id']) . '\'">' . "\n";
    }
?>
                <td><?php echo $countries['countries_name']; ?></td>
                <td><?php echo $countries['countries_iso_code_2']; ?>&nbsp;|&nbsp;<?php echo $countries['countries_iso_code_3']; ?></td>
                <td class="text-right"><?php if (isset($cInfo) && is_object($cInfo) && ($countries['countries_id'] == $cInfo->countries_id) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $countries['countries_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
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
	<?php echo tep_draw_button(IMAGE_NEW_COUNTRY, 'fa fa-plus', tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&action=new'), 'primary', null, 'btn-default'); ?>                
  </div>          
<?php
  }
?>
			<div class="col-md-5">
				<?php echo $countries_split->display_count($countries_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?>
			</div>
			<div class="col-md-3 pull-right text-right">		
				<?php echo $countries_split->display_links($countries_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
			</div>
		</div>
	</div> <!-- EOF col-md-8 -->
    <div class="col-md-4">			  
<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_COUNTRY . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&action=insert') . TEXT_INFO_INSERT_INTRO .
				'<br /><br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . tep_draw_input_field('countries_name') .
				'<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . tep_draw_input_field('countries_iso_code_2') .
				'<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . tep_draw_input_field('countries_iso_code_3') .
				'<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . tep_draw_pull_down_menu('address_format_id', tep_get_address_formats()) .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
				'&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'])) . '</div>' .   
				'</div></div>';
	  break;
    case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_COUNTRY . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->countries_id . '&action=save') . TEXT_INFO_EDIT_INTRO .
				'<br /><br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . tep_draw_input_field('countries_name', $cInfo->countries_name) .
				'<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . tep_draw_input_field('countries_iso_code_2', $cInfo->countries_iso_code_2) .
				'<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . tep_draw_input_field('countries_iso_code_3', $cInfo->countries_iso_code_3) .
				'<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . tep_draw_pull_down_menu('address_format_id', tep_get_address_formats(), $cInfo->address_format_id) .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->countries_id)) . '</div>' .
				'</div></div>';
      break;
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_COUNTRY . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->countries_id . '&action=deleteconfirm') . TEXT_INFO_DELETE_INTRO .
				'<br /><br /><strong>' . $cInfo->countries_name . '</strong>' .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->countries_id)) . '</div>' . 
				'</div></div>';											   
      break;
    default:
      if (is_object($cInfo)) {		  
  		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $cInfo->countries_name . '</strong></span></div>';	  

		echo '<div class="panel-body">' .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->countries_id . '&action=edit'), 'primary', null, 'btn-warning') . 
										 '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_COUNTRIES, 'page=' . $HTTP_GET_VARS['page'] . '&cID=' . $cInfo->countries_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
				'<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . $cInfo->countries_name . 
				'<br />' . TEXT_INFO_COUNTRY_CODE_2 . ' ' . $cInfo->countries_iso_code_2 .
				'<br />' . TEXT_INFO_COUNTRY_CODE_3 . ' ' . $cInfo->countries_iso_code_3 .
				'<br />' . TEXT_INFO_ADDRESS_FORMAT . ' ' . $cInfo->address_format_id .
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
