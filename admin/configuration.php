<?php
/*
  $Id$ osC Admin BS by Tsimi

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $configuration_value = tep_db_prepare_input($HTTP_POST_VARS['configuration_value']);
        $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($configuration_value) . "', last_modified = now() where configuration_id = '" . (int)$cID . "'");

        tep_redirect(tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $HTTP_GET_VARS['gID'] . '&cID=' . $cID));
        break;
    }
  }

  $gID = (isset($HTTP_GET_VARS['gID'])) ? $HTTP_GET_VARS['gID'] : 1;

  $cfg_group_query = tep_db_query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID . "'");
  $cfg_group = tep_db_fetch_array($cfg_group_query);

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    
<h3><?php echo $cfg_group['configuration_group_title']; ?></h3>
<div class="row">
 <div class="col-md-8">           
  <table class="table table-hover table-condensed table-striped table-responsive">
	<thead>
      <tr>
        <th><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></th>
        <th><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></th>
        <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
      </tr>
	</thead>
	<tbody>
<?php
  $configuration_query = tep_db_query("select configuration_id, configuration_title, configuration_value, use_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$gID . "' order by sort_order");
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    if (tep_not_null($configuration['use_function'])) {
      $use_function = $configuration['use_function'];
      if (preg_match('/->/', $use_function)) {
        $class_method = explode('->', $use_function);
        if (!is_object(${$class_method[0]})) {
          include(DIR_WS_CLASSES . $class_method[0] . '.php');
          ${$class_method[0]} = new $class_method[0]();
        }
        $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
      } else {
        $cfgValue = tep_call_function($use_function, $configuration['configuration_value']);
      }
    } else {
      $cfgValue = $configuration['configuration_value'];
    }

    if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $configuration['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cfg_extra_query = tep_db_query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$configuration['configuration_id'] . "'");
      $cfg_extra = tep_db_fetch_array($cfg_extra_query);

      $cInfo_array = array_merge($configuration, $cfg_extra);
      $cInfo = new objectInfo($cInfo_array);
    }

    if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
      echo '  <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $HTTP_GET_VARS['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '  <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $HTTP_GET_VARS['gID'] . '&cID=' . $configuration['configuration_id']) . '\'">' . "\n";
    }
?>
                <td><?php echo $configuration['configuration_title']; ?></td>
                <td><?php echo htmlspecialchars($cfgValue); ?></td>
                <td align="right"><?php if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) { echo '<i class="fa fa-edit fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $HTTP_GET_VARS['gID'] . '&cID=' . $configuration['configuration_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
	</tbody>
  </table>
  </div> <!-- EOF col-md-8 -->
  <div class="col-md-4">
<?php
	switch ($action) {
		case 'edit':
			echo '<div class="panel panel-primary">
					<div class="panel-heading"><span class="panel-title">' . $cInfo->configuration_title . '</span></div>';
		   
				if ($cInfo->set_function) {
					eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value) . '");');
				} else {
					$value_field = tep_draw_input_field('configuration_value', $cInfo->configuration_value);
				}
			
			echo '<div class="panel-body">' . 
					tep_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . $HTTP_GET_VARS['gID'] . '&cID=' . $cInfo->configuration_id . '&action=save') . TEXT_INFO_EDIT_INTRO . 
				 '<br /><br /><strong>' . $cInfo->configuration_title . '</strong><br />' . $cInfo->configuration_description . '<br />' . $value_field .
				 '<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $HTTP_GET_VARS['gID'] . '&cID=' . $cInfo->configuration_id)) . '</div>' .
				 '</div></div>';
		break;
		default:
			if (isset($cInfo) && is_object($cInfo)) {
				echo '<div class="panel panel-default">
						<div class="panel-heading"><h3 class="panel-title">' . $cInfo->configuration_title . '</h3></div>';

				echo '<div class="panel-body">
						<div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $HTTP_GET_VARS['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit'), 'primary', null, 'btn-warning') . '</div>' .
					 '<br />' . $cInfo->configuration_description . 
					 '<br /><br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added);
					 
					if (tep_not_null($cInfo->last_modified)) echo '<br />' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified);
					
				echo '</div></div>';
			}		
		break;
	}
?>
      </div> <!-- EOF col-md-4 -->
</div>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
