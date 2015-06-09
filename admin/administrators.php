<?php
/*
  $Id$ osC Admin BS by Tsimi

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $htaccess_array = null;
  $htpasswd_array = null;
  $is_iis = stripos($HTTP_SERVER_VARS['SERVER_SOFTWARE'], 'iis');

  $authuserfile_array = array('##### OSCOMMERCE ADMIN PROTECTION - BEGIN #####',
                              'AuthType Basic',
                              'AuthName "osCommerce Online Merchant Administration Tool"',
                              'AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce',
                              'Require valid-user',
                              '##### OSCOMMERCE ADMIN PROTECTION - END #####');

  if (!$is_iis && file_exists(DIR_FS_ADMIN . '.htpasswd_oscommerce') && tep_is_writable(DIR_FS_ADMIN . '.htpasswd_oscommerce') && file_exists(DIR_FS_ADMIN . '.htaccess') && tep_is_writable(DIR_FS_ADMIN . '.htaccess')) {
    $htaccess_array = array();
    $htpasswd_array = array();

    if (filesize(DIR_FS_ADMIN . '.htaccess') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htaccess', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htaccess'));
      fclose($fg);

      $htaccess_array = explode("\n", $data);
    }

    if (filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce'));
      fclose($fg);

      $htpasswd_array = explode("\n", $data);
    }
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        require('includes/functions/password_funcs.php');

        $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
        $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

        $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " where user_name = '" . tep_db_input($username) . "' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query("insert into " . TABLE_ADMINISTRATORS . " (user_name, user_password) values ('" . tep_db_input($username) . "', '" . tep_db_input(tep_encrypt_password($password)) . "')");

          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($HTTP_POST_VARS['htaccess']) && ($HTTP_POST_VARS['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }

            $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
            fwrite($fp, implode("\n", $htpasswd_array));
            fclose($fp);

            if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
              array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
            } elseif (empty($htpasswd_array)) {
              for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
                if (in_array($htaccess_array[$i], $authuserfile_array)) {
                  unset($htaccess_array[$i]);
                }
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        } else {
          $messageStack->add_session(ERROR_ADMINISTRATOR_EXISTS, 'error');
        }

        tep_redirect(tep_href_link(FILENAME_ADMINISTRATORS));
        break;
      case 'save':
        require('includes/functions/password_funcs.php');

        $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
        $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

        $check_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$HTTP_GET_VARS['aID'] . "'");
        $check = tep_db_fetch_array($check_query);

// update username in current session if changed
        if ( ($check['id'] == $admin['id']) && ($check['user_name'] != $admin['username']) ) {
          $admin['username'] = $username;
        }

// update username in htpasswd if changed
        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ( ($check['user_name'] == $ht_username) && ($check['user_name'] != $username) ) {
              $htpasswd_array[$i] = $username . ':' . $ht_password;
            }
          }
        }

        tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_name = '" . tep_db_input($username) . "' where id = '" . (int)$HTTP_GET_VARS['aID'] . "'");

        if (tep_not_null($password)) {
// update password in htpasswd
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($HTTP_POST_VARS['htaccess']) && ($HTTP_POST_VARS['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }
          }

          tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . tep_db_input(tep_encrypt_password($password)) . "' where id = '" . (int)$HTTP_GET_VARS['aID'] . "'");
        } elseif (!isset($HTTP_POST_VARS['htaccess']) || ($HTTP_POST_VARS['htaccess'] != 'true')) {
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }
          }
        }

// write new htpasswd file
        if (is_array($htpasswd_array)) {
          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
            array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
          } elseif (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
          fwrite($fp, implode("\n", $htaccess_array));
          fclose($fp);
        }

        tep_redirect(tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . (int)$HTTP_GET_VARS['aID']));
        break;
      case 'deleteconfirm':
        $id = tep_db_prepare_input($HTTP_GET_VARS['aID']);

        $check_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");
        $check = tep_db_fetch_array($check_query);

        if ($admin['id'] == $check['id']) {
          tep_session_unregister('admin');
        }

        tep_db_query("delete from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");

        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ($ht_username == $check['user_name']) {
              unset($htpasswd_array[$i]);
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        }

        tep_redirect(tep_href_link(FILENAME_ADMINISTRATORS));
        break;
    }
  }

  $secMessageStack = new messageStack();

  if (is_array($htpasswd_array)) {
    if (empty($htpasswd_array)) {
      $secMessageStack->add(sprintf(HTPASSWD_INFO, implode('<br />', $authuserfile_array)), 'warning');
    } else {
      $secMessageStack->add(HTPASSWD_SECURED, 'success');
    }
  } else if (!$is_iis) {
    $secMessageStack->add(HTPASSWD_PERMISSIONS, 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <h3><?php echo HEADING_TITLE; ?></h3>
     
	<p><?php echo $secMessageStack->output(); ?></p>

<div class="col-md-8">
    <table class="table table-hover table-responsive">
		<thead>
			<tr>
				<th><?php echo TABLE_HEADING_ADMINISTRATORS; ?></th>
				<th class="text-center"><?php echo TABLE_HEADING_HTPASSWD; ?></th>
				<th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
			</tr>
		</thead>
		<tbody>
<?php
  $admins_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " order by user_name");
  while ($admins = tep_db_fetch_array($admins_query)) {
    if ((!isset($HTTP_GET_VARS['aID']) || (isset($HTTP_GET_VARS['aID']) && ($HTTP_GET_VARS['aID'] == $admins['id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
      $aInfo = new objectInfo($admins);
    }


    $htpasswd_secured = '<i class="fa fa-times-circle fa-lg icon-red"></i>';

    if ($is_iis) {
      $htpasswd_secured = 'N/A';
    }

    if (is_array($htpasswd_array)) {
      for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
        list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

        if ($ht_username == $admins['user_name']) {
          $htpasswd_secured = '<i class="fa fa-check-circle fa-lg icon-green"></i>';
          break;
        }
      }
    }
  if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) {
      echo '<tr onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '<tr onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admins['id']) . '\'">' . "\n";
    }
?>
				<td><?php echo $admins['user_name']; ?></td>
				<td align="center"><?php echo $htpasswd_secured; ?></td>
				<td align="right"><?php if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admins['id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?>&nbsp;</td>
			</tr>
<?php
  }
?>
		</tbody>
	</table>
	<div class="text-right">
		<?php echo tep_draw_button(IMAGE_INSERT, 'fa fa-user-plus', tep_href_link(FILENAME_ADMINISTRATORS, 'action=new'), '', null, 'btn-default'); ?>
	</div>
</div> <!-- EOF col-md-8 -->
<div class="col-md-4 mt15">
	<?php
		switch ($action) {
			case 'new':
				echo '<div class="panel panel-primary">
						<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_ADMINISTRATOR . '</span></div>';
			  
				echo '<div class="panel-body">' . 
						tep_draw_form('administrator', FILENAME_ADMINISTRATORS, 'action=insert', 'post', 'autocomplete="off"') . TEXT_INFO_INSERT_INTRO . 
				     '<br /><br />' . tep_draw_input_field('username', '', 'placeholder="' . ENTRY_USERNAME . '"') . 
				     '<br />' . tep_draw_password_field('password', '', '', 'placeholder="' . ENTRY_PASSWORD . '"');
			  
				  if (is_array($htpasswd_array)) {
					echo '<br />' . tep_draw_checkbox_field('htaccess', 'true') . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD;
				  }
			  
				echo '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
											'&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ADMINISTRATORS), 'primary', null, 'btn-default') . '</div>' .
					 '</div></div>';
			  
			break;
			case 'edit':
				echo '<div class="panel panel-primary">
						<div class="panel-heading"><span class="panel-title">' . $aInfo->user_name . '</span></div>';
			  
				echo '<div class="panel-body">' . 
						tep_draw_form('administrator', FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=save', 'post', 'autocomplete="off"') . TEXT_INFO_EDIT_INTRO . 
					 '<br /><br />' . tep_draw_input_field('username', $aInfo->user_name, 'placeholder="' . ENTRY_USERNAME . '"') . 
					 '<br />' . tep_draw_password_field('password', '', '', 'placeholder="' . ENTRY_PASSWORD . '"');

			  if (is_array($htpasswd_array)) {
				$default_flag = false;

				for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
				  list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

				  if ($ht_username == $aInfo->user_name) {
					$default_flag = true;
			break;
				  }
				}
				echo '<br />' . tep_draw_checkbox_field('htaccess', 'true', $default_flag) . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD . '<br />';
			  }
				echo '<br /><div class="text-center">' .  tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id), 'primary', null, 'btn-default') . '</div>' .
					 '</div></div>';
			break;
			case 'delete':
				echo '<div class="panel panel-primary">
						<div class="panel-heading"><span class="panel-title">' . $aInfo->user_name .  '</span></div>';
			   
				echo '<div class="panel-body">' .
						tep_draw_form('administrator', FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=deleteconfirm') . TEXT_INFO_DELETE_INTRO . 
					 '<br /><br /><strong>' . $aInfo->user_name . '</strong>' . 
					 '<br /><br /><div class="text-center">' .  tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
					  							     '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id), 'primary', null, 'btn-default') . '</div>' .
					 '</div></div>';
			break;
			default:
			  if (isset($aInfo) && is_object($aInfo)) {
				echo '<div class="panel panel-default">
						<div class="panel-heading"><span class="panel-title">' . $aInfo->user_name . '</span></div>';
				echo '<div class="panel-body">' .
					 '<br /><div class="text-center">' .  tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit'), 'primary', null, 'btn-warning') . 
											   '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
					 '</div></div>';
			  }
			break;
		}
	?>			
</div> <!-- EOF col-md-4 -->
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
