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
        $name = tep_db_prepare_input($HTTP_POST_VARS['name']);
        $code = tep_db_prepare_input(substr($HTTP_POST_VARS['code'], 0, 2));
        $image = tep_db_prepare_input($HTTP_POST_VARS['image']);
        $directory = tep_db_prepare_input($HTTP_POST_VARS['directory']);
        $sort_order = (int)tep_db_prepare_input($HTTP_POST_VARS['sort_order']);

        tep_db_query("insert into " . TABLE_LANGUAGES . " (name, code, image, directory, sort_order) values ('" . tep_db_input($name) . "', '" . tep_db_input($code) . "', '" . tep_db_input($image) . "', '" . tep_db_input($directory) . "', '" . tep_db_input($sort_order) . "')");
        $insert_id = tep_db_insert_id();

// create additional categories_description records
        $categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where cd.language_id = '" . (int)$languages_id . "'");
        while ($categories = tep_db_fetch_array($categories_query)) {
          tep_db_query("insert into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name) values ('" . (int)$categories['categories_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($categories['categories_name']) . "')");
        }

// create additional products_description records
        $products_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, pd.products_url from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where pd.language_id = '" . (int)$languages_id . "'");
        while ($products = tep_db_fetch_array($products_query)) {
          tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url) values ('" . (int)$products['products_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($products['products_name']) . "', '" . tep_db_input($products['products_description']) . "', '" . tep_db_input($products['products_url']) . "')");
        }

// create additional products_options records
        $products_options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "'");
        while ($products_options = tep_db_fetch_array($products_options_query)) {
          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . (int)$products_options['products_options_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($products_options['products_options_name']) . "')");
        }

// create additional products_options_values records
        $products_options_values_query = tep_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int)$languages_id . "'");
        while ($products_options_values = tep_db_fetch_array($products_options_values_query)) {
          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$products_options_values['products_options_values_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($products_options_values['products_options_values_name']) . "')");
        }

// create additional manufacturers_info records
        $manufacturers_query = tep_db_query("select m.manufacturers_id, mi.manufacturers_url from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = '" . (int)$languages_id . "'");
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
          tep_db_query("insert into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_url) values ('" . $manufacturers['manufacturers_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($manufacturers['manufacturers_url']) . "')");
        }

// create additional orders_status records
        $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
          tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . (int)$orders_status['orders_status_id'] . "', '" . (int)$insert_id . "', '" . tep_db_input($orders_status['orders_status_name']) . "')");
        }

        if (isset($HTTP_POST_VARS['default']) && ($HTTP_POST_VARS['default'] == 'on')) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
        }

        tep_redirect(tep_href_link(FILENAME_LANGUAGES, (isset($HTTP_GET_VARS['page']) ? 'page=' . $HTTP_GET_VARS['page'] . '&' : '') . 'lID=' . $insert_id));
        break;
      case 'save':
        $lID = tep_db_prepare_input($HTTP_GET_VARS['lID']);
        $name = tep_db_prepare_input($HTTP_POST_VARS['name']);
        $code = tep_db_prepare_input(substr($HTTP_POST_VARS['code'], 0, 2));
        $image = tep_db_prepare_input($HTTP_POST_VARS['image']);
        $directory = tep_db_prepare_input($HTTP_POST_VARS['directory']);
        $sort_order = (int)tep_db_prepare_input($HTTP_POST_VARS['sort_order']);

        tep_db_query("update " . TABLE_LANGUAGES . " set name = '" . tep_db_input($name) . "', code = '" . tep_db_input($code) . "', image = '" . tep_db_input($image) . "', directory = '" . tep_db_input($directory) . "', sort_order = '" . tep_db_input($sort_order) . "' where languages_id = '" . (int)$lID . "'");

        if ($HTTP_POST_VARS['default'] == 'on') {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
        }

        tep_redirect(tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $HTTP_GET_VARS['lID']));
        break;
      case 'deleteconfirm':
        $lID = tep_db_prepare_input($HTTP_GET_VARS['lID']);

        $lng_query = tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_CURRENCY . "'");
        $lng = tep_db_fetch_array($lng_query);
        if ($lng['languages_id'] == $lID) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
        }

        tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$lID . "'");
        tep_db_query("delete from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");

        tep_redirect(tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page']));
        break;
      case 'delete':
        $lID = tep_db_prepare_input($HTTP_GET_VARS['lID']);

        $lng_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
        $lng = tep_db_fetch_array($lng_query);

        $remove_language = true;
        if ($lng['code'] == DEFAULT_LANGUAGE) {
          $remove_language = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
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
                <th><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></td>
                <th><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></td>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></td>
              </tr>
			</thead>
          <tbody>
<?php
  $languages_query_raw = "select languages_id, name, code, image, directory, sort_order from " . TABLE_LANGUAGES . " order by sort_order";
  $languages_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $languages_query_raw, $languages_query_numrows);
  $languages_query = tep_db_query($languages_query_raw);

  while ($languages = tep_db_fetch_array($languages_query)) {
    if ((!isset($HTTP_GET_VARS['lID']) || (isset($HTTP_GET_VARS['lID']) && ($HTTP_GET_VARS['lID'] == $languages['languages_id']))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
      $lInfo = new objectInfo($languages);
    }

    if (isset($lInfo) && is_object($lInfo) && ($languages['languages_id'] == $lInfo->languages_id) ) {
      echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">';
    } else {
      echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $languages['languages_id']) . '\'">';
    }

    if (DEFAULT_LANGUAGE == $languages['code']) {
      echo '    <td><strong>' . $languages['name'] . ' (' . TEXT_DEFAULT . ')</strong></td>';
    } else {
      echo '    <td>' . $languages['name'] . '</td>';
    }
?>
                <td><?php echo $languages['code']; ?></td>
                <td class="text-right"><?php if (isset($lInfo) && is_object($lInfo) && ($languages['languages_id'] == $lInfo->languages_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $languages['languages_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
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
             <div class="col-xs-12 text-right mb15">
                 <?php echo tep_draw_button(IMAGE_NEW_LANGUAGE, 'fa fa-plus', tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=new'), 'primary', null, 'btn-default'); ?>
			 </div>      
<?php
  }
?>
			<div class="col-md-5">
				<?php echo $languages_split->display_count($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?>
			</div>
			<div class="col-md-3 pull-right text-right">           
				<?php echo $languages_split->display_links($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
			</div>
        </div>
    </div> <!-- EOF col-md-8 -->
    <div class="col-md-4">			  
<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</span></div>';	  

		echo '<div class="panel-body">' .		
				tep_draw_form('languages', FILENAME_LANGUAGES, 'action=insert') . TEXT_INFO_INSERT_INTRO .
				'<br /><br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('name') .
				'<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('code') .
				'<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . tep_draw_input_field('image', 'icon.gif') .
				'<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . tep_draw_input_field('directory') . 
				'<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order') .
				'<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $HTTP_GET_VARS['lID'])) . '</div>' . 
				'</div></div>';
	  break;
    case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('languages', FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=save') . TEXT_INFO_EDIT_INTRO .
				'<br /><br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . tep_draw_input_field('name', $lInfo->name) .
				'<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . tep_draw_input_field('code', $lInfo->code) .
				'<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . tep_draw_input_field('image', $lInfo->image) .
				'<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . tep_draw_input_field('directory', $lInfo->directory) .
				'<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . tep_draw_input_field('sort_order', $lInfo->sort_order);
				if (DEFAULT_LANGUAGE != $lInfo->code) echo '<br />' . tep_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT;
		   echo '<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id)) . '</div>' . 
				'</div></div>';
      break;
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</span></div>';	  

		echo '<div class="panel-body">' .	
				TEXT_INFO_DELETE_INTRO .
				'<br /><br /><strong>' . $lInfo->name . '</strong>' .
				'<br /><br /><div class="text-center">' . (($remove_language) ? tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm'), 'primary', null, 'btn-danger') : '') . 
															   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id)) . '</div>' . 
				'</div></div>';     
	  break;
    default:
      if (is_object($lInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $lInfo->name . '</strong></span></div>';	  

		echo '<div class="panel-body">' .		  
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=edit'), 'primary', null, 'btn-warning') . 
										 '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_LANGUAGES, 'page=' . $HTTP_GET_VARS['page'] . '&lID=' . $lInfo->languages_id . '&action=delete'), 'primary', null, 'btn-danger') . 
										 '&nbsp;' . tep_draw_button(IMAGE_DETAILS, 'fa fa-search-plus', tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $lInfo->directory), 'primary', null, 'btn-info') . '</div>' . 
				'<br />' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name .
				'<br />' . TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code .
				'<br />' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $lInfo->directory . '/images/' . $lInfo->image, '', 'SSL'), $lInfo->name) .
				'<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . DIR_WS_CATALOG_LANGUAGES . '<strong>' . $lInfo->directory . '</strong>' .
				'<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order .
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
