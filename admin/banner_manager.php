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

  $banner_extension = tep_banner_image_extension();

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          tep_set_banner_status($HTTP_GET_VARS['bID'], $HTTP_GET_VARS['flag']);

          $messageStack->add_session(SUCCESS_BANNER_STATUS_UPDATED, 'success');
        } else {
          $messageStack->add_session(ERROR_UNKNOWN_STATUS_FLAG, 'error');
        }

        tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $HTTP_GET_VARS['bID']));
        break;
      case 'insert':
      case 'update':
        if (isset($HTTP_POST_VARS['banners_id'])) $banners_id = tep_db_prepare_input($HTTP_POST_VARS['banners_id']);
        $banners_title = tep_db_prepare_input($HTTP_POST_VARS['banners_title']);
        $banners_url = tep_db_prepare_input($HTTP_POST_VARS['banners_url']);
        $new_banners_group = tep_db_prepare_input($HTTP_POST_VARS['new_banners_group']);
        $banners_group = (empty($new_banners_group)) ? tep_db_prepare_input($HTTP_POST_VARS['banners_group']) : $new_banners_group;
        $banners_html_text = tep_db_prepare_input($HTTP_POST_VARS['banners_html_text']);
        $banners_image_local = tep_db_prepare_input($HTTP_POST_VARS['banners_image_local']);
        $banners_image_target = tep_db_prepare_input($HTTP_POST_VARS['banners_image_target']);
        $db_image_location = '';
        $expires_date = tep_db_prepare_input($HTTP_POST_VARS['expires_date']);
        $expires_impressions = tep_db_prepare_input($HTTP_POST_VARS['expires_impressions']);
        $date_scheduled = tep_db_prepare_input($HTTP_POST_VARS['date_scheduled']);

        $banner_error = false;
        if (empty($banners_title)) {
          $messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
          $banner_error = true;
        }

        if (empty($banners_group)) {
          $messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
          $banner_error = true;
        }

        if (empty($banners_html_text)) {
          if (empty($banners_image_local)) {
            $banners_image = new upload('banners_image');
            $banners_image->set_destination(DIR_FS_CATALOG_IMAGES . $banners_image_target);
            if ( ($banners_image->parse() == false) || ($banners_image->save() == false) ) {
              $banner_error = true;
            }
          }
        }

        if ($banner_error == false) {
          $db_image_location = (tep_not_null($banners_image_local)) ? $banners_image_local : $banners_image_target . $banners_image->filename;
          $sql_data_array = array('banners_title' => $banners_title,
                                  'banners_url' => $banners_url,
                                  'banners_image' => $db_image_location,
                                  'banners_group' => $banners_group,
                                  'banners_html_text' => $banners_html_text,
                                  'expires_date' => 'null',
                                  'expires_impressions' => 0,
                                  'date_scheduled' => 'null');

          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => 'now()',
                                     'status' => '1');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_BANNERS, $sql_data_array);

            $banners_id = tep_db_insert_id();

            $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            tep_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = '" . (int)$banners_id . "'");

            $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
          }

          if (tep_not_null($expires_date)) {
            $expires_date = substr($expires_date, 0, 4) . substr($expires_date, 5, 2) . substr($expires_date, 8, 2);

            tep_db_query("update " . TABLE_BANNERS . " set expires_date = '" . tep_db_input($expires_date) . "', expires_impressions = null where banners_id = '" . (int)$banners_id . "'");
          } elseif (tep_not_null($expires_impressions)) {
            tep_db_query("update " . TABLE_BANNERS . " set expires_impressions = '" . tep_db_input($expires_impressions) . "', expires_date = null where banners_id = '" . (int)$banners_id . "'");
          }

          if (tep_not_null($date_scheduled)) {
            $date_scheduled = substr($date_scheduled, 0, 4) . substr($date_scheduled, 5, 2) . substr($date_scheduled, 8, 2);

            tep_db_query("update " . TABLE_BANNERS . " set status = '0', date_scheduled = '" . tep_db_input($date_scheduled) . "' where banners_id = '" . (int)$banners_id . "'");
          }

          tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'bID=' . $banners_id));
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $banners_id = tep_db_prepare_input($HTTP_GET_VARS['bID']);

        if (isset($HTTP_POST_VARS['delete_image']) && ($HTTP_POST_VARS['delete_image'] == 'on')) {
          $banner_query = tep_db_query("select banners_image from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
          $banner = tep_db_fetch_array($banner_query);

          if (is_file(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
            if (tep_is_writable(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
              unlink(DIR_FS_CATALOG_IMAGES . $banner['banners_image']);
            } else {
              $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
            }
          } else {
            $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
          }
        }

        tep_db_query("delete from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
        tep_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners_id . "'");

        if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
          if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension);
            }
          }

          if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension);
            }
          }

          if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension);
            }
          }

          if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension);
            }
          }
        }

        $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');

        tep_redirect(tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }

// check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      if (tep_is_writable(DIR_WS_IMAGES . 'graphs')) {
        $dir_ok = true;
      } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<script type="text/javascript"><!--
function popupImageWindow(url) {
  window.open(url,'popupImageWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>

	<h3><?php echo HEADING_TITLE; ?></h3>
	 
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('expires_date' => '',
                        'date_scheduled' => '',
                        'banners_title' => '',
                        'banners_url' => '',
                        'banners_group' => '',
                        'banners_image' => '',
                        'banners_html_text' => '',
                        'expires_impressions' => '');

    $bInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['bID'])) {
      $form_action = 'update';

      $bID = tep_db_prepare_input($HTTP_GET_VARS['bID']);

      $banner_query = tep_db_query("select banners_title, banners_url, banners_image, banners_group, banners_html_text, status, date_format(date_scheduled, '%Y/%m/%d') as date_scheduled, date_format(expires_date, '%Y/%m/%d') as expires_date, expires_impressions, date_status_change from " . TABLE_BANNERS . " where banners_id = '" . (int)$bID . "'");
      $banner = tep_db_fetch_array($banner_query);

      $bInfo->objectInfo($banner);
    } elseif (tep_not_null($HTTP_POST_VARS)) {
      $bInfo->objectInfo($HTTP_POST_VARS);
    }

    $groups_array = array();
    $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS . " order by banners_group");
    while ($groups = tep_db_fetch_array($groups_query)) {
      $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
    }
?>
<?php echo tep_draw_form('new_banner', FILENAME_BANNER_MANAGER, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"'); if ($form_action == 'update') echo tep_draw_hidden_field('banners_id', $bID); ?>
	<div class="form-horizontal">  
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_TITLE; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_input_field('banners_title', $bInfo->banners_title, '', true); ?>
			</div>
        </div>
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_URL; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_input_field('banners_url', $bInfo->banners_url); ?>
			</div>
        </div>
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_GROUP; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group) . TEXT_BANNERS_NEW_GROUP . '<br />' . tep_draw_input_field('new_banners_group', '', '', ((sizeof($groups_array) > 0) ? false : true)); ?>
			</div>
        </div>		
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_IMAGE . '<br /><small>' . DIR_FS_CATALOG_IMAGES . '</small>'; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_file_field('banners_image') . ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br />' . tep_draw_input_field('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : '')); ?>
			</div>
        </div>		
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_IMAGE_TARGET . '<br /><small>' . DIR_FS_CATALOG_IMAGES . '</small>'; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_input_field('banners_image_target'); ?>
			</div>
        </div>			
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_HTML_TEXT; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_textarea_field('banners_html_text', 'soft', '60', '5', $bInfo->banners_html_text); ?>
			</div>
        </div>		
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_SCHEDULED_AT; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_input_field('date_scheduled', $bInfo->date_scheduled, 'id="date_scheduled"') . ' <small>(YYYY-MM-DD)</small>'; ?>
			</div>
        </div>			
		<div class="form-group has-feedback">
			<label class="control-label col-sm-3">
				<?php echo TEXT_BANNERS_EXPIRES_ON; ?>
			</label>
            <div class="col-sm-7">
				<?php echo tep_draw_input_field('expires_date', $bInfo->expires_date, 'id="expires_date"') . ' <small>(YYYY-MM-DD)</small>' . TEXT_BANNERS_OR_AT . '<br />' . tep_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?>
			</div>
        </div>		

<script type="text/javascript">
$('#date_scheduled').datepicker({
  dateFormat: 'yy-mm-dd'
});
$('#expires_date').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>
	</div>
	<div class="well">
        <?php echo TEXT_BANNERS_BANNER_NOTE . '<br />' . TEXT_BANNERS_INSERT_NOTE . '<br />' . TEXT_BANNERS_EXPIRCY_NOTE . '<br />' . TEXT_BANNERS_SCHEDULE_NOTE; ?>
	</div>		
	<div class="text-center">
		<?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . '&nbsp;' .	tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_BANNER_MANAGER, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . (isset($HTTP_GET_VARS['bID']) ? 'bID=' . $HTTP_GET_VARS['bID'] : ''))); ?>
	</div>
 </form>
<?php
  } else {
?>

  <div class="row">
	<div class="col-md-8">  
		<table class="table table-hover table-condensed table-responsive table-striped">
			<thead>
				<tr>
					<th><?php echo TABLE_HEADING_BANNERS; ?></td>
					<th><?php echo TABLE_HEADING_GROUPS; ?></th>
					<th class="text-center"><?php echo TABLE_HEADING_STATISTICS; ?></th>
					<th class="text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
					<th class="text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
<?php
    $banners_query_raw = "select banners_id, banners_title, banners_image, banners_group, status, expires_date, expires_impressions, date_status_change, date_scheduled, date_added from " . TABLE_BANNERS . " order by banners_title, banners_group";
    $banners_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $banners_query_raw, $banners_query_numrows);
    $banners_query = tep_db_query($banners_query_raw);
    while ($banners = tep_db_fetch_array($banners_query)) {
      $info_query = tep_db_query("select sum(banners_shown) as banners_shown, sum(banners_clicked) as banners_clicked from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners['banners_id'] . "'");
      $info = tep_db_fetch_array($info_query);

      if ((!isset($HTTP_GET_VARS['bID']) || (isset($HTTP_GET_VARS['bID']) && ($HTTP_GET_VARS['bID'] == $banners['banners_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
        $bInfo_array = array_merge($banners, $info);
        $bInfo = new objectInfo($bInfo_array);
      }

      $banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
      $banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';

      if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id)) {
        echo '<tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->banners_id) . '\'">';
      } else {
        echo '<tr onclick="document.location.href=\'' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $banners['banners_id']) . '\'">';
      }
?>
                <td><?php echo '<a href="javascript:popupImageWindow(\'' . FILENAME_POPUP_IMAGE . '?banner=' . $banners['banners_id'] . '\')"><i class="fa fa-eye fa-lg" title="View Banner"></i></a>&nbsp;' . $banners['banners_title']; ?></td>
                <td><?php echo $banners['banners_group']; ?></td>
                <td class="text-center"><?php echo $banners_shown . ' / ' . $banners_clicked; ?></td>
                <td class="text-center">
<?php
      if ($banners['status'] == '1') {
		echo '&nbsp;<i class="fa fa-circle icon-green"></i>&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=0') . '"><i class="fa fa-circle icon-gray"></i></a>';
	  } else {
        echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=1') . '"><i class="fa fa-circle icon-gray"></i></a>&nbsp;&nbsp;<i class="fa fa-circle icon-red"></i>';
      }
?>
				</td>
                <td class="text-right"><?php echo '<a href="' . tep_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $banners['banners_id']) . '"><i class="fa fa-line-chart fa-lg mr15" title="' . ICON_STATISTICS . '"></i></a>'; if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $banners['banners_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
            </tbody>
        </table>
		<div class="row mb25"> 	
			<div class="col-md-12 mb10 text-right">
				<?php echo tep_draw_button(IMAGE_NEW_BANNER, 'fa fa-plus', tep_href_link(FILENAME_BANNER_MANAGER, 'action=new')); ?>
			</div>
			<div class="col-md-5">
				<?php echo $banners_split->display_count($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_BANNERS); ?>
			</div>
			<div class="col-md-3 pull-right text-right">
				<?php echo $banners_split->display_links($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
			</div>
		</div>
	</div> <!-- EOF col-md-8 //-->        
	<div class="col-md-4">		
<?php
  switch ($action) {
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . $bInfo->banners_title . '</span></div>';
		
		echo '<div class="panel-body">' .	
				tep_draw_form('banners', FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->banners_id . '&action=deleteconfirm') . 
				TEXT_INFO_DELETE_INTRO .
				'<br /><br /><strong>' . $bInfo->banners_title . '</strong>';
		if ($bInfo->banners_image) echo '<br />' . tep_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE;
		echo '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											'&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $HTTP_GET_VARS['bID'])) . '</div>' .
			 '</div></div>';
  break;
    default:
      if (is_object($bInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $bInfo->banners_title . '</strong></span></div>';
		
		echo '<div class="panel-body">		  
				<div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->banners_id . '&action=new'), 'primary', null, 'btn-warning') . 
								  '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->banners_id . '&action=delete'), 'primary', null, 'btn-danger') . 
								  '&nbsp;' . tep_draw_button(IMAGE_DETAILS, 'fa fa-search-plus', tep_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $bInfo->banners_id), 'primary', null, 'btn-info') . '</div>' .
				'<br />' . TEXT_BANNERS_DATE_ADDED . ' ' . tep_date_short($bInfo->date_added);
		echo '<div class="text-center">';
    if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {
          $banner_id = $bInfo->banners_id;
          $days = '3';
			
          include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
		echo '<br />' . tep_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension);
    } else {
          include(DIR_WS_FUNCTIONS . 'html_graphs.php');
		echo '<br />' . tep_banner_graph_infoBox($bInfo->banners_id, '3');
    }
			echo '</div>';
			echo '<br />' . tep_image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br />' . tep_image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS;

        if ($bInfo->date_scheduled) echo '<br />' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, tep_date_short($bInfo->date_scheduled));

        if ($bInfo->expires_date) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, tep_date_short($bInfo->expires_date)));
        } elseif ($bInfo->expires_impressions) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
        }

        if ($bInfo->date_status_change) $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_STATUS_CHANGE, tep_date_short($bInfo->date_status_change)));
      '</div></div>';
	  }
      break;
  }
?>
  </div> <!-- EOF col-md-4 //--> 
 </div> <!-- EOF row //--> 
<?php
  }
?>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
