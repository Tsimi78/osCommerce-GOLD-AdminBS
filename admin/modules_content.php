<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_CONTENT_INSTALLED' limit 1");
  if (tep_db_num_rows($check_query) < 1) {
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Installed Modules', 'MODULE_CONTENT_INSTALLED', '', 'This is automatically updated. No need to edit.', '6', '0', now())");
    define('MODULE_CONTENT_INSTALLED', '');
  }

  $modules_installed = (tep_not_null(MODULE_CONTENT_INSTALLED) ? explode(';', MODULE_CONTENT_INSTALLED) : array());
  $modules = array('installed' => array(), 'new' => array());

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));

  if ($maindir = @dir(DIR_FS_CATALOG_MODULES . 'content/')) {
    while ($group = $maindir->read()) {
      if ( ($group != '.') && ($group != '..') && is_dir(DIR_FS_CATALOG_MODULES . 'content/' . $group)) {
        if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'content/' . $group)) {
          while ($file = $dir->read()) {
            if (!is_dir(DIR_FS_CATALOG_MODULES . 'content/' . $group . '/' . $file)) {
              if (substr($file, strrpos($file, '.')) == $file_extension) {
                $class = substr($file, 0, strrpos($file, '.'));

                if (!tep_class_exists($class)) {
                  if ( file_exists(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/content/' . $group . '/' . $file) ) {
                    include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/content/' . $group . '/' . $file);
                  }

                  include(DIR_FS_CATALOG_MODULES . 'content/' . $group . '/' . $file);
                }

                if (tep_class_exists($class)) {
                  $module = new $class();

                  if (in_array($group . '/' . $class, $modules_installed)) {
                    $modules['installed'][] = array('code' => $class,
                                                    'title' => $module->title,
                                                    'group' => $group,
                                                    'sort_order' => (int)$module->sort_order);
                  } else {
                    $modules['new'][] = array('code' => $class,
                                              'title' => $module->title,
                                              'group' => $group);
                  }
                }
              }
            }
          }

          $dir->close();
        }
      }
    }

    $maindir->close();

    function _sortContentModulesInstalled($a, $b) {
      return strnatcmp($a['group'] . '-' . (int)$a['sort_order'] . '-' . $a['title'], $b['group'] . '-' . (int)$b['sort_order'] . '-' . $b['title']);
    }

    function _sortContentModuleFiles($a, $b) {
      return strnatcmp($a['group'] . '-' . $a['title'], $b['group'] . '-' . $b['title']);
    }

    usort($modules['installed'], '_sortContentModulesInstalled');
    usort($modules['new'], '_sortContentModuleFiles');
  }

// Update sort order in MODULE_CONTENT_INSTALLED
  $_installed = array();

  foreach ( $modules['installed'] as $m ) {
    $_installed[] = $m['group'] . '/' . $m['code'];
  }

  if ( implode(';', $_installed) != MODULE_CONTENT_INSTALLED ) {
    tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . implode(';', $_installed) . "' where configuration_key = 'MODULE_CONTENT_INSTALLED'");
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $class = basename($HTTP_GET_VARS['module']);

        foreach ( $modules['installed'] as $m ) {
          if ( $m['code'] == $class ) {
            foreach ($HTTP_POST_VARS['configuration'] as $key => $value) {
              $key = tep_db_prepare_input($key);
              $value = tep_db_prepare_input($value);

              tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($value) . "' where configuration_key = '" . tep_db_input($key) . "'");
            }

            break;
          }
        }

        tep_redirect(tep_href_link('modules_content.php', 'module=' . $class));

        break;

      case 'install':
        $class = basename($HTTP_GET_VARS['module']);

        foreach ( $modules['new'] as $m ) {
          if ( $m['code'] == $class ) {
            $module = new $class();

            $module->install();

            $modules_installed[] = $m['group'] . '/' . $m['code'];

            tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . implode(';', $modules_installed) . "' where configuration_key = 'MODULE_CONTENT_INSTALLED'");

            tep_redirect(tep_href_link('modules_content.php', 'module=' . $class . '&action=edit'));
          }
        }

        tep_redirect(tep_href_link('modules_content.php', 'action=list_new&module=' . $class));

        break;

      case 'remove':
        $class = basename($HTTP_GET_VARS['module']);

        foreach ( $modules['installed'] as $m ) {
          if ( $m['code'] == $class ) {
            $module = new $class();

            $module->remove();

            $modules_installed = explode(';', MODULE_CONTENT_INSTALLED);

            if (in_array($m['group'] . '/' . $m['code'], $modules_installed)) {
              unset($modules_installed[array_search($m['group'] . '/' . $m['code'], $modules_installed)]);
            }

            tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . implode(';', $modules_installed) . "' where configuration_key = 'MODULE_CONTENT_INSTALLED'");

            tep_redirect(tep_href_link('modules_content.php'));
          }
        }

        tep_redirect(tep_href_link('modules_content.php', 'module=' . $class));

        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <div class="row">
      <div class="col-md-6">
        <h3><?php echo HEADING_TITLE; ?></h3>
      </div>  
	  
<?php
  if ($action == 'list_new') {
    echo ' <div class="col-md-6 text-right">' . tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link('modules_content.php')) . '</div>';
  } else {
    echo ' <div class="col-md-6 text-right">' . tep_draw_button(IMAGE_MODULE_INSTALL . ' (' . count($modules['new']) . ')', 'fa fa-plus', tep_href_link('modules_content.php', 'action=list_new')) . '</div>';
  }
?>
    </div>
<div class="row">    
    <div class="col-md-8">            
		<table class="table table-hover table-condensed table-responsive table-striped">
<?php
  if ( $action == 'list_new' ) {
?>
			<thead>     
     		  <tr>
                <th><?php echo TABLE_HEADING_MODULES; ?></th>
                <th><?php echo TABLE_HEADING_GROUP; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
			</thead>
            <tbody>	
<?php
    foreach ( $modules['new'] as $m ) {
      $module = new $m['code']();

      if ((!isset($HTTP_GET_VARS['module']) || (isset($HTTP_GET_VARS['module']) && ($HTTP_GET_VARS['module'] == $module->code))) && !isset($mInfo)) {
        $module_info = array('code' => $module->code,
                             'title' => $module->title,
                             'description' => $module->description,
                             'signature' => (isset($module->signature) ? $module->signature : null),
                             'api_version' => (isset($module->api_version) ? $module->api_version : null));

        $mInfo = new objectInfo($module_info);
      }

      if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) {
        echo '  <tr class="info">';
      } else {
        echo '  <tr onclick="document.location.href=\'' . tep_href_link('modules_content.php', 'action=list_new&module=' . $module->code) . '\'">';
      }
?>
                <td><?php echo $module->title; ?></td>
                <td><?php echo $module->group; ?></td>
                <td class="text-right"><?php if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link('modules_content.php', 'action=list_new&module=' . $module->code) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
            </tbody>
<?php
  } else {
?>
 
			<thead>     
     		  <tr>
                <th><?php echo TABLE_HEADING_MODULES; ?></th>
                <th><?php echo TABLE_HEADING_GROUP; ?></th>
                <th><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
			</thead>
			<tbody>
<?php
    foreach ( $modules['installed'] as $m ) {
      $module = new $m['code']();

      if ((!isset($HTTP_GET_VARS['module']) || (isset($HTTP_GET_VARS['module']) && ($HTTP_GET_VARS['module'] == $module->code))) && !isset($mInfo)) {
        $module_info = array('code' => $module->code,
                             'title' => $module->title,
                             'description' => $module->description,
                             'signature' => (isset($module->signature) ? $module->signature : null),
                             'api_version' => (isset($module->api_version) ? $module->api_version : null),
                             'sort_order' => (int)$module->sort_order,
                             'keys' => array());

        foreach ($module->keys() as $key) {
          $key = tep_db_prepare_input($key);

          $key_value_query = tep_db_query("select configuration_title, configuration_value, configuration_description, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($key) . "'");
          $key_value = tep_db_fetch_array($key_value_query);

          $module_info['keys'][$key] = array('title' => $key_value['configuration_title'],
                                             'value' => $key_value['configuration_value'],
                                             'description' => $key_value['configuration_description'],
                                             'use_function' => $key_value['use_function'],
                                             'set_function' => $key_value['set_function']);
        }

        $mInfo = new objectInfo($module_info);
      }

      if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) {
        echo ' <tr class="info">';
      } else {
        echo ' <tr onclick="document.location.href=\'' . tep_href_link('modules_content.php', 'module=' . $module->code) . '\'">';
      }
?>
                <td><?php echo $module->title; ?></td>
                <td><?php echo $module->group; ?></td>
                <td><?php echo $module->sort_order; ?></td>
                <td class="text-right"><?php if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link('modules_content.php', 'module=' . $module->code) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
			</tbody>
<?php
  }
?>
		</table>
           	<div class="row">
		<div class="col-xs-12">
		<?php echo TEXT_MODULE_DIRECTORY . ' ' . DIR_FS_CATALOG_MODULES . 'content/'; ?></p>
		</div>
	</div>
	</div> <!-- EOF col-md-8 -->
	<div class="col-md-4">	
<?php
  switch ($action) {
    case 'edit':
      $keys = '';

      foreach ($mInfo->keys as $key => $value) {
        $keys .= '<strong>' . $value['title'] . '</strong><br />' . $value['description'] . '<br />';

        if ($value['set_function']) {
          eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
        } else {
          $keys .= tep_draw_input_field('configuration[' . $key . ']', $value['value']);
        }

        $keys .= '<br /><br />';
      }

      $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));

	  	echo '<div class="panel panel-primary">
			<div class="panel-heading"><span class="panel-title">' . $mInfo->title . '</span></div>';
			
			echo '<div class="panel-body">' .
					tep_draw_form('modules', 'modules_content.php', 'module=' . $mInfo->code . '&action=save') . $keys . 
					'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										     '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link('modules_content.php', 'module=' . $mInfo->code)) . '</div>' .
					'</div></div>';
      break;

    default:
      if ( isset($mInfo) ) {
		echo '<div class="panel panel-default">
			<div class="panel-heading"><span class="panel-title"><strong>' . $mInfo->title . '</strong></span></div>';
			
			echo '<div class="panel-body">';

        if ($action == 'list_new') {
          echo '<div class="text-center">' . tep_draw_button(IMAGE_MODULE_INSTALL, 'fa fa-plus', tep_href_link('modules_content.php', 'module=' . $mInfo->code . '&action=install')) . '</div>';

          if (isset($mInfo->signature) && (list($scode, $smodule, $sversion, $soscversion) = explode('|', $mInfo->signature))) {
            echo '<br /><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i>&nbsp;<strong>' . TEXT_INFO_VERSION . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $mInfo->signature . '" target="_blank">' . TEXT_INFO_ONLINE_STATUS . '</a>)';
          }

          if (isset($mInfo->api_version)) {
            echo '<i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i>&nbsp;<strong>' . TEXT_INFO_API_VERSION . '</strong> ' . $mInfo->api_version;
          }

          echo '<br />' . $mInfo->description;
        } else {
          $keys = '';

          foreach ($mInfo->keys as $value) {
            $keys .= '<strong>' . $value['title'] . '</strong><br />';

            if ($value['use_function']) {
              $use_function = $value['use_function'];

              if (preg_match('/->/', $use_function)) {
                $class_method = explode('->', $use_function);

                if (!isset(${$class_method[0]}) || !is_object(${$class_method[0]})) {
                  include(DIR_WS_CLASSES . $class_method[0] . '.php');
                  ${$class_method[0]} = new $class_method[0]();
                }

                $keys .= tep_call_function($class_method[1], $value['value'], ${$class_method[0]});
              } else {
                $keys .= tep_call_function($use_function, $value['value']);
              }
            } else {
              $keys .= $value['value'];
            }

            $keys .= '<br /><br />';
          }

          $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));

          echo '<div class="text-center">' .  tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link('modules_content.php', 'module=' . $mInfo->code . '&action=edit'), 'primary', null, 'btn-warning') . '&nbsp;' . tep_draw_button(IMAGE_MODULE_REMOVE, 'fa fa-minus', tep_href_link('modules_content.php', 'module=' . $mInfo->code . '&action=remove'), 'primary', null, 'btn-danger') . '</div>';

          if (isset($mInfo->signature) && (list($scode, $smodule, $sversion, $soscversion) = explode('|', $mInfo->signature))) {
            echo '<br /><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i>&nbsp;<strong>' . TEXT_INFO_VERSION . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $mInfo->signature . '" target="_blank">' . TEXT_INFO_ONLINE_STATUS . '</a>)';
          }

          if (isset($mInfo->api_version)) {
            echo '<i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i>&nbsp;<strong>' . TEXT_INFO_API_VERSION . '</strong> ' . $mInfo->api_version;
          }

          echo '<br />' . $mInfo->description;
          echo '<div class="mt15">' . $keys . '</div>';
        }
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
