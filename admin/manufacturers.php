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
        if (isset($HTTP_GET_VARS['mID'])) $manufacturers_id = tep_db_prepare_input($HTTP_GET_VARS['mID']);
        $manufacturers_name = tep_db_prepare_input($HTTP_POST_VARS['manufacturers_name']);

        $sql_data_array = array('manufacturers_name' => $manufacturers_name);

        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
          $manufacturers_id = tep_db_insert_id();
        } elseif ($action == 'save') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        $manufacturers_image = new upload('manufacturers_image');
        $manufacturers_image->set_destination(DIR_FS_CATALOG_IMAGES);

        if ($manufacturers_image->parse() && $manufacturers_image->save()) {
          tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_image = '" . tep_db_input($manufacturers_image->filename) . "' where manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $manufacturers_url_array = $HTTP_POST_VARS['manufacturers_url'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('manufacturers_url' => tep_db_prepare_input($manufacturers_url_array[$language_id]));

          if ($action == 'insert') {
            $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                     'languages_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
          } elseif ($action == 'save') {
            tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "' and languages_id = '" . (int)$language_id . "'");
          }
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('manufacturers');
        }

        tep_redirect(tep_href_link(FILENAME_MANUFACTURERS, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'mID=' . $manufacturers_id));
        break;
      case 'deleteconfirm':
        $manufacturers_id = tep_db_prepare_input($HTTP_GET_VARS['mID']);

        if (isset($HTTP_POST_VARS['delete_image']) && ($HTTP_POST_VARS['delete_image'] == 'on')) {
          $manufacturer_query = tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
          $manufacturer = tep_db_fetch_array($manufacturer_query);

          $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $manufacturer['manufacturers_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

        tep_db_query("delete from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
        tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$manufacturers_id . "'");

        if (isset($HTTP_POST_VARS['delete_products']) && ($HTTP_POST_VARS['delete_products'] == 'on')) {
          $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
          while ($products = tep_db_fetch_array($products_query)) {
            tep_remove_product($products['products_id']);
          }
        } else {
          tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '' where manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('manufacturers');
        }

        tep_redirect(tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page']));
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
              <th><?php echo TABLE_HEADING_MANUFACTURERS; ?></th>
              <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
            </tr>
		  </thead>
		  <tbody>
<?php
  $manufacturers_query_raw = "select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from " . TABLE_MANUFACTURERS . " order by manufacturers_name";
  $manufacturers_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $manufacturers_query_raw, $manufacturers_query_numrows);
  $manufacturers_query = tep_db_query($manufacturers_query_raw);
  while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
    if ((!isset($HTTP_GET_VARS['mID']) || (isset($HTTP_GET_VARS['mID']) && ($HTTP_GET_VARS['mID'] == $manufacturers['manufacturers_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
      $manufacturer_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int)$manufacturers['manufacturers_id'] . "'");
      $manufacturer_products = tep_db_fetch_array($manufacturer_products_query);

      $mInfo_array = array_merge($manufacturers, $manufacturer_products);
      $mInfo = new objectInfo($mInfo_array);
    }

    if (isset($mInfo) && is_object($mInfo) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id)) {
      echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $manufacturers['manufacturers_id'] . '&action=edit') . '\'">';
    } else {
      echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '\'">';
    }
?>
                <td><?php echo $manufacturers['manufacturers_name']; ?></td>
				<td class="text-right"><?php if (isset($mInfo) && is_object($mInfo) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
            </tr>
<?php
  }
?>
        </tbody>
    </table>
	<div class="row mb25"> 
<?php
  if (empty($action)) {
?>
		<div class="col-md-12 mb10 text-right">
		  <?php echo tep_draw_button(IMAGE_INSERT, 'fa fa-plus', tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=new'), '', null, 'btn-default'); ?>
		</div>		
<?php
  }
?>       
		<div class="col-md-5">
		   <?php echo $manufacturers_split->display_count($manufacturers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS); ?>
		</div>
		<div class="col-md-3 pull-right text-right">
		  <?php echo $manufacturers_split->display_links($manufacturers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
		</div>
	</div>
		</div> <!-- EOF col-md-8 -->           
        <div class="col-md-4">			  

<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_HEADING_NEW_MANUFACTURER . '</span></div>';
		   
			echo '<div class="panel-body">' .
				 tep_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'action=insert', 'post', 'enctype="multipart/form-data"') . TEXT_NEW_INTRO .
				 '<br /><br />' . TEXT_MANUFACTURERS_NAME . '<br />' . tep_draw_input_field('manufacturers_name') .
				 '<br />' . TEXT_MANUFACTURERS_IMAGE . '<br />' . tep_draw_file_field('manufacturers_image');

			$manufacturer_inputs_string = '';
			$languages = tep_get_languages();
			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$manufacturer_inputs_string .= tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']');
			}

			echo '<br />' . TEXT_MANUFACTURERS_URL . '<br />' . $manufacturer_inputs_string .
				 '<br /><div class="text-center">' .  tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $HTTP_GET_VARS['mID'])) . '</div>' .
 				 '</div></div>';	
      break;
    case 'edit':
		echo '<div class="panel panel-primary">
			<div class="panel-heading"><span class="panel-title">' . TEXT_HEADING_EDIT_MANUFACTURER . '</span></div>';
			
			echo '<div class="panel-body">' .
				 tep_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=save', 'post', 'enctype="multipart/form-data"') . TEXT_EDIT_INTRO .
				 '<br /><br />' . TEXT_MANUFACTURERS_NAME . '<br />' . tep_draw_input_field('manufacturers_name', $mInfo->manufacturers_name) . 
				 '<br />' . TEXT_MANUFACTURERS_IMAGE . '<br />' . tep_draw_file_field('manufacturers_image') . $mInfo->manufacturers_image;

			$manufacturer_inputs_string = '';
			$languages = tep_get_languages();
			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$manufacturer_inputs_string .= tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', tep_get_manufacturer_url($mInfo->manufacturers_id, $languages[$i]['id']));
			}

			echo '<br /><br />' . TEXT_MANUFACTURERS_URL . '<br />' . $manufacturer_inputs_string .
				 '<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										  '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id));
				 '</div></div>';
      break;
    case 'delete':
			echo '<div class="panel panel-danger">
					<div class="panel-heading"><span class="panel-title">' . TEXT_HEADING_DELETE_MANUFACTURER . '</span></div>';
			
			echo '<div class="panel-body">' .
				 tep_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=deleteconfirm') . TEXT_DELETE_INTRO .
				 '<br /><br /><strong>' . $mInfo->manufacturers_name . '</strong>' .
				 '<br /><br />' . tep_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE . '<br />';

			if ($mInfo->products_count > 0) {
				echo '<br />' . tep_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS .
					 '<br /><br /><div class="alert alert-danger">' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count) . '</div>';
			}
			
			echo '<br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
				 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id)) . '</div>' .
				 '</div></div>';
      break;
    default:
		if (isset($mInfo) && is_object($mInfo)) {
			echo '<div class="panel panel-default">
					<div class="panel-heading"><span class="panel-title"><strong>' . $mInfo->manufacturers_name . '</strong></span></div>';
			
			echo '<div class="panel-body">' .		  
				 '<div class="text-center">' .tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=edit'), 'primary', null, 'btn-warning') . '&nbsp;' . 
				 tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_MANUFACTURERS, 'page=' . $HTTP_GET_VARS['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
				 '<br />' . TEXT_DATE_ADDED . ' ' . tep_date_short($mInfo->date_added);
			if (tep_not_null($mInfo->last_modified)) echo '<br />' . TEXT_LAST_MODIFIED . ' ' . tep_date_short($mInfo->last_modified);
			echo '<br />' . tep_info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name) .
				 '<br />' . TEXT_PRODUCTS . ' ' . $mInfo->products_count .
				 '</div></div>';			 
		}
      break;
  }
?>

  </div> <!-- EOF col-md-4 //--> 
</div>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>