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
      case 'save':
        if (isset($HTTP_GET_VARS['oID'])) $orders_status_id = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $orders_status_name_array = $HTTP_POST_VARS['orders_status_name'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('orders_status_name' => tep_db_prepare_input($orders_status_name_array[$language_id]),
                                  'public_flag' => ((isset($HTTP_POST_VARS['public_flag']) && ($HTTP_POST_VARS['public_flag'] == '1')) ? '1' : '0'),
                                  'downloads_flag' => ((isset($HTTP_POST_VARS['downloads_flag']) && ($HTTP_POST_VARS['downloads_flag'] == '1')) ? '1' : '0'));

          if ($action == 'insert') {
            if (empty($orders_status_id)) {
              $next_id_query = tep_db_query("select max(orders_status_id) as orders_status_id from " . TABLE_ORDERS_STATUS . "");
              $next_id = tep_db_fetch_array($next_id_query);
              $orders_status_id = $next_id['orders_status_id'] + 1;
            }

            $insert_sql_data = array('orders_status_id' => $orders_status_id,
                                     'language_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);
          } elseif ($action == 'save') {
            tep_db_perform(TABLE_ORDERS_STATUS, $sql_data_array, 'update', "orders_status_id = '" . (int)$orders_status_id . "' and language_id = '" . (int)$language_id . "'");
          }
        }

        if (isset($HTTP_POST_VARS['default']) && ($HTTP_POST_VARS['default'] == 'on')) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($orders_status_id) . "' where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        }

        tep_redirect(tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $orders_status_id));
        break;
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        $orders_status_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        $orders_status = tep_db_fetch_array($orders_status_query);

        if ($orders_status['configuration_value'] == $oID) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        }

        tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . tep_db_input($oID) . "'");

        tep_redirect(tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'delete':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        $status_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . (int)$oID . "'");
        $status = tep_db_fetch_array($status_query);

        $remove_status = true;
        if ($oID == DEFAULT_ORDERS_STATUS_ID) {
          $remove_status = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_ORDER_STATUS, 'error');
        } elseif ($status['count'] > 0) {
          $remove_status = false;
          $messageStack->add(ERROR_STATUS_USED_IN_ORDERS, 'error');
        } else {
          $history_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_status_id = '" . (int)$oID . "'");
          $history = tep_db_fetch_array($history_query);
          if ($history['count'] > 0) {
            $remove_status = false;
            $messageStack->add(ERROR_STATUS_USED_IN_HISTORY, 'error');
          }
        }
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
                <th><?php echo TABLE_HEADING_ORDERS_STATUS; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_PUBLIC_STATUS; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_DOWNLOADS_STATUS; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			 </thead>
          <tbody> 
<?php
  $orders_status_query_raw = "select * from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_id";
  $orders_status_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_status_query_raw, $orders_status_query_numrows);
  $orders_status_query = tep_db_query($orders_status_query_raw);
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $orders_status['orders_status_id']))) && !isset($oInfo) && (substr($action, 0, 3) != 'new')) {
      $oInfo = new objectInfo($orders_status);
    }

    if (isset($oInfo) && is_object($oInfo) && ($orders_status['orders_status_id'] == $oInfo->orders_status_id)) {
      echo '   <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit') . '\'">';
    } else {
      echo '   <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $orders_status['orders_status_id']) . '\'">';
    }

    if (DEFAULT_ORDERS_STATUS_ID == $orders_status['orders_status_id']) {
      echo '  	<td><strong>' . $orders_status['orders_status_name'] . ' (' . TEXT_DEFAULT . ')</strong></td>';
    } else {
      echo '    <td>' . $orders_status['orders_status_name'] . '</td>';
    }
?>
                <td class="text-center"><?php echo (($orders_status['public_flag'] == '1') ? '<i class="fa fa-check fa-lg icon-green"></i>' : '<i class="fa fa-times fa-lg icon-red"></i>'); ?></td>
                <td class="text-center"><?php echo (($orders_status['downloads_flag'] == '1') ? '<i class="fa fa-check fa-lg icon-green"></i>' : '<i class="fa fa-times fa-lg icon-red"></i>'); ?></td>
                <td class="text-right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders_status['orders_status_id'] == $oInfo->orders_status_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $orders_status['orders_status_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
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
			   <div class="col-md-12 text-right mb15">
					<?php echo tep_draw_button(IMAGE_INSERT, 'fa fa-plus', tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&action=new'), 'primary', null, 'btn-default'); ?>
			   </div>
<?php
  }
?>
			  <div class="col-md-5">
					<?php echo $orders_status_split->display_count($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS); ?>
			  </div>
			  <div class="col-md-3 pull-right text-right">      
					<?php echo $orders_status_split->display_links($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
			  </div>
		</div>
    </div> <!-- EOF col-md-8 -->
    <div class="col-md-4">			  
<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&action=insert') . TEXT_INFO_INSERT_INTRO;

      $orders_status_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $orders_status_inputs_string .= '<br />' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']');
      }

        echo '<br /><br />' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string .
			 '<br />' . tep_draw_checkbox_field('public_flag', '1') . ' ' . TEXT_SET_PUBLIC_STATUS .
		 	 '<br />' . tep_draw_checkbox_field('downloads_flag', '1') . ' ' . TEXT_SET_DOWNLOADS_STATUS .
			 '<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT .
			 '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
									  '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'])) . '</div>' .
			 '</div></div>';  
      break;
    case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oInfo->orders_status_id  . '&action=save') . TEXT_INFO_EDIT_INTRO;

      $orders_status_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $orders_status_inputs_string .= '<br />' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']', tep_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']));
      }
		
		echo '<br /><br />' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string .
			 '<br />' . tep_draw_checkbox_field('public_flag', '1', $oInfo->public_flag) . ' ' . TEXT_SET_PUBLIC_STATUS . 
			 '<br />' . tep_draw_checkbox_field('downloads_flag', '1', $oInfo->downloads_flag) . ' ' . TEXT_SET_DOWNLOADS_STATUS;
			 if (DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id) echo '<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT;
		echo '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
									  '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oInfo->orders_status_id)) . '</div>' .
			 '</div></div>';  
      break;
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_ORDERS_STATUS . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oInfo->orders_status_id  . '&action=deleteconfirm') . TEXT_INFO_DELETE_INTRO .
				'<br /><br /><strong>' . $oInfo->orders_status_name . '</strong>';
				if ($remove_status) echo '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
																  '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oInfo->orders_status_id)) . '</div>';
		echo '</div></div>';
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $oInfo->orders_status_name . '</strong></span></div>';	  

		echo '<div class="panel-body">' .		  
			 '<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit'), 'primary', null, 'btn-warning') . 
											'&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_ORDERS_STATUS, 'page=' . $HTTP_GET_VARS['page'] . '&oID=' . $oInfo->orders_status_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>';

        $orders_status_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $orders_status_inputs_string .= '<br />' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']);
        }

        echo $orders_status_inputs_string .
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
