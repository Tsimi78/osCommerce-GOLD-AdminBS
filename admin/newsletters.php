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
      case 'lock':
      case 'unlock':
        $newsletter_id = tep_db_prepare_input($HTTP_GET_VARS['nID']);
        $status = (($action == 'lock') ? '1' : '0');

        tep_db_query("update " . TABLE_NEWSLETTERS . " set locked = '" . $status . "' where newsletters_id = '" . (int)$newsletter_id . "'");

        tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']));
        break;
      case 'insert':
      case 'update':
        if (isset($HTTP_POST_VARS['newsletter_id'])) $newsletter_id = tep_db_prepare_input($HTTP_POST_VARS['newsletter_id']);
        $newsletter_module = tep_db_prepare_input($HTTP_POST_VARS['module']);
        $title = tep_db_prepare_input($HTTP_POST_VARS['title']);
        $content = tep_db_prepare_input($HTTP_POST_VARS['content']);

        $newsletter_error = false;
        if (empty($title)) {
          $messageStack->add(ERROR_NEWSLETTER_TITLE, 'error');
          $newsletter_error = true;
        }

        if (empty($newsletter_module)) {
          $messageStack->add(ERROR_NEWSLETTER_MODULE, 'error');
          $newsletter_error = true;
        }

        if ($newsletter_error == false) {
          $sql_data_array = array('title' => $title,
                                  'content' => $content,
                                  'module' => $newsletter_module);

          if ($action == 'insert') {
            $sql_data_array['date_added'] = 'now()';
            $sql_data_array['status'] = '0';
            $sql_data_array['locked'] = '0';

            tep_db_perform(TABLE_NEWSLETTERS, $sql_data_array);
            $newsletter_id = tep_db_insert_id();
          } elseif ($action == 'update') {
            tep_db_perform(TABLE_NEWSLETTERS, $sql_data_array, 'update', "newsletters_id = '" . (int)$newsletter_id . "'");
          }

          tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'nID=' . $newsletter_id));
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $newsletter_id = tep_db_prepare_input($HTTP_GET_VARS['nID']);

        tep_db_query("delete from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$newsletter_id . "'");

        tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'delete':
      case 'new': if (!isset($HTTP_GET_VARS['nID'])) break;
      case 'send':
      case 'confirm_send':
        $newsletter_id = tep_db_prepare_input($HTTP_GET_VARS['nID']);

        $check_query = tep_db_query("select locked from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$newsletter_id . "'");
        $check = tep_db_fetch_array($check_query);

        if ($check['locked'] < 1) {
          switch ($action) {
            case 'delete': $error = ERROR_REMOVE_UNLOCKED_NEWSLETTER; break;
            case 'new': $error = ERROR_EDIT_UNLOCKED_NEWSLETTER; break;
            case 'send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
            case 'confirm_send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
          }

          $messageStack->add_session($error, 'error');

          tep_redirect(tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']));
        }
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

	<h3><?php echo HEADING_TITLE; ?></h3>
	
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('title' => '',
                        'content' => '',
                        'module' => '');

    $nInfo = new objectInfo($parameters);

    if (isset($HTTP_GET_VARS['nID'])) {
      $form_action = 'update';

      $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

      $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
      $newsletter = tep_db_fetch_array($newsletter_query);

      $nInfo->objectInfo($newsletter);
    } elseif ($HTTP_POST_VARS) {
      $nInfo->objectInfo($HTTP_POST_VARS);
    }

    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
    $directory_array = array();
    if ($dir = dir(DIR_WS_MODULES . 'newsletters/')) {
      while ($file = $dir->read()) {
        if (!is_dir(DIR_WS_MODULES . 'newsletters/' . $file)) {
          if (substr($file, strrpos($file, '.')) == $file_extension) {
            $directory_array[] = $file;
          }
        }
      }
      sort($directory_array);
      $dir->close();
    }

    for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
      $modules_array[] = array('id' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')), 'text' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')));
    }
?>

	<?php echo tep_draw_form('newsletter', FILENAME_NEWSLETTERS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'action=' . $form_action); if ($form_action == 'update') echo tep_draw_hidden_field('newsletter_id', $nID); ?>
        
	<div class="form-horizontal">  
		<div class="form-group has-feedback">
				<label class="control-label col-sm-3">
					<?php echo TEXT_NEWSLETTER_MODULE; ?>
				</label>
            <div class="col-sm-7">
				<?php echo tep_draw_pull_down_menu('module', $modules_array, $nInfo->module); ?>
			</div>
        </div>
		<div class="form-group has-feedback">
				<label class="control-label col-sm-3">
					<?php echo TEXT_NEWSLETTER_TITLE; ?>
				</label>
            <div class="col-sm-7">
				<?php echo tep_draw_input_field('title', $nInfo->title, '', true); ?>
			</div>
        </div> 
		<div class="form-group has-feedback">
				<label class="control-label col-sm-3">
					<?php echo TEXT_NEWSLETTER_CONTENT; ?>
				</label>
            <div class="col-sm-7">
				<?php echo tep_draw_textarea_field('content', 'soft', '100%', '20', $nInfo->content); ?>
			</div>
        </div>
    </div>
		<div class="text-center">
			<?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_NEWSLETTERS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . (isset($HTTP_GET_VARS['nID']) ? 'nID=' . $HTTP_GET_VARS['nID'] : ''))); ?>
		</div>
  </form>
<?php
  } elseif ($action == 'preview') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);
?>
	<p><?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'])); ?></p>
	<div class="col-md-6 well"><?php echo nl2br($nInfo->content); ?></div>
	<div class="clearfix"></div>
	<p><?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'])); ?></p>
<?php
  } elseif ($action == 'send') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . $language . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <?php if ($module->show_choose_audience) { echo $module->choose_audience(); } else { echo $module->confirm(); } ?>
      
<?php
  } elseif ($action == 'confirm') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . $language . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <?php echo $module->confirm(); ?>
	  
<?php
  } elseif ($action == 'confirm_send') {
    $nID = tep_db_prepare_input($HTTP_GET_VARS['nID']);

    $newsletter_query = tep_db_query("select newsletters_id, title, content, module from " . TABLE_NEWSLETTERS . " where newsletters_id = '" . (int)$nID . "'");
    $newsletter = tep_db_fetch_array($newsletter_query);

    $nInfo = new objectInfo($newsletter);

    include(DIR_WS_LANGUAGES . $language . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
	<div class="col-md-6 well">  
		<strong><?php echo TEXT_PLEASE_WAIT; ?></strong>
		<br />
<?php
  tep_set_time_limit(0);
  flush();
  $module->send($nInfo->newsletters_id);
?>
	
		<font color="#ff0000"><strong><?php echo TEXT_FINISHED_SENDING_EMAILS; ?></strong></font>
	</div>
	<div class="clearfix"></div>
	<?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'])); ?>
    
<?php
  } else {
?>
  <div class="row">
	<div class="col-md-8">  
		<table class="table table-hover table-condensed table-responsive table-striped">
			<thead>
				<tr>
					<th><?php echo TABLE_HEADING_NEWSLETTERS; ?></th>
					<th class="text-right"><?php echo TABLE_HEADING_SIZE; ?></th>
					<th class="text-right"><?php echo TABLE_HEADING_MODULE; ?></th>
					<th class="text-center"><?php echo TABLE_HEADING_SENT; ?></th>
					<th class="text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
					<th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			</thead>
			<tbody>
<?php
    $newsletters_query_raw = "select newsletters_id, title, length(content) as content_length, module, date_added, date_sent, status, locked from " . TABLE_NEWSLETTERS . " order by date_added desc";
    $newsletters_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $newsletters_query_raw, $newsletters_query_numrows);
    $newsletters_query = tep_db_query($newsletters_query_raw);
    while ($newsletters = tep_db_fetch_array($newsletters_query)) {
    if ((!isset($HTTP_GET_VARS['nID']) || (isset($HTTP_GET_VARS['nID']) && ($HTTP_GET_VARS['nID'] == $newsletters['newsletters_id']))) && !isset($nInfo) && (substr($action, 0, 3) != 'new')) {
        $nInfo = new objectInfo($newsletters);
      }

      if (isset($nInfo) && is_object($nInfo) && ($newsletters['newsletters_id'] == $nInfo->newsletters_id) ) {
        echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '\'">';
      } else {
        echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $newsletters['newsletters_id']) . '\'">';
      }
?>
					<td><?php echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $newsletters['newsletters_id'] . '&action=preview') . '"><i class="fa fa-eye fa-lg" title="'. ICON_PREVIEW .'"></i></a>&nbsp;' . $newsletters['title']; ?></td>
					<td class="text-right"><?php echo number_format($newsletters['content_length']) . ' bytes'; ?></td>
					<td class="text-right"><?php echo $newsletters['module']; ?></td>
					<td class="text-center"><?php if ($newsletters['status'] == '1') { echo '<i class="fa fa-check fa-lg icon-green"></i>'; } else { echo '<i class="fa fa-times fa-lg icon-red"></i>'; } ?></td>
					<td class="text-center"><?php if ($newsletters['locked'] > 0) { echo '<i class="fa fa-lock fa-lg"></i>'; } else { echo '<i class="fa fa-unlock fa-lg"></i>'; } ?></td>
					<td class="text-right"><?php if (isset($nInfo) && is_object($nInfo) && ($newsletters['newsletters_id'] == $nInfo->newsletters_id) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $newsletters['newsletters_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
              </tr>
<?php
    }
?>
            </tbody>
        </table>
		<div class="row mb25"> 	
			<div class="col-md-12 mb10 text-right">		
                <?php echo tep_draw_button(IMAGE_NEW_NEWSLETTER, 'fa fa-plus', tep_href_link(FILENAME_NEWSLETTERS, 'action=new')); ?>
			</div>
			<div class="col-md-5">
				<?php echo $newsletters_split->display_count($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_NEWSLETTERS); ?>
			</div>
			<div class="col-md-3 pull-right text-right">
				<?php echo $newsletters_split->display_links($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>		
			</div>
		</div>
	</div> <!-- EOF col-md-8 //-->        
  <div class="col-md-4">				
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . $nInfo->title . '</span></div>';
		
		echo '<div class="panel-body">' .
				tep_draw_form('newsletters', FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=deleteconfirm') .
				TEXT_INFO_DELETE_INTRO .
				'<br /><br /><strong>' . $nInfo->title . '</strong>' .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'])) . '</div>' .
				'</div></div>';
      break;
    default:
      if (isset($nInfo) && is_object($nInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $nInfo->title . '</strong></span></div>';
		
		echo '<div class="panel-body">';
		
        if ($nInfo->locked > 0) {
          echo '<div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new'), 'primary', null, 'btn-warning') . '&nbsp;' . 
											 tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete'), 'primary', null, 'btn-danger') . '&nbsp;' . 
											 tep_draw_button(IMAGE_PREVIEW, 'fa fa-eye', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview'), 'primary', null, 'btn-default') . '&nbsp;' .
											 tep_draw_button(IMAGE_SEND, 'fa fa-envelope', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send'), 'primary', null, 'btn-default') . '&nbsp;' .
											 tep_draw_button(IMAGE_UNLOCK, 'fa fa-unlock', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=unlock'), 'primary', null, 'btn-default') . '</div>';
        } else {
          echo '<div class="text-center">' . tep_draw_button(IMAGE_PREVIEW, 'fa fa-eye', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview')) . '&nbsp;' .
											 tep_draw_button(IMAGE_LOCK, 'fa fa-lock', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $nInfo->newsletters_id . '&action=lock')) . '</div>';
        }
		  echo '<br />' . TEXT_NEWSLETTER_DATE_ADDED . ' ' . tep_date_short($nInfo->date_added);
        if ($nInfo->status == '1')echo '<br />' . TEXT_NEWSLETTER_DATE_SENT . ' ' . tep_date_short($nInfo->date_sent);
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
