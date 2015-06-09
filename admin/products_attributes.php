<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  $languages = tep_get_languages();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  $option_page = (isset($HTTP_GET_VARS['option_page']) && is_numeric($HTTP_GET_VARS['option_page'])) ? $HTTP_GET_VARS['option_page'] : 1;
  $value_page = (isset($HTTP_GET_VARS['value_page']) && is_numeric($HTTP_GET_VARS['value_page'])) ? $HTTP_GET_VARS['value_page'] : 1;
  $attribute_page = (isset($HTTP_GET_VARS['attribute_page']) && is_numeric($HTTP_GET_VARS['attribute_page'])) ? $HTTP_GET_VARS['attribute_page'] : 1;

  $page_info = 'option_page=' . $option_page . '&value_page=' . $value_page . '&attribute_page=' . $attribute_page;

  if (tep_not_null($action)) {
    switch ($action) {
      case 'add_product_options':
        $products_options_id = tep_db_prepare_input($HTTP_POST_VARS['products_options_id']);
        $option_name_array = $HTTP_POST_VARS['option_name'];

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = tep_db_prepare_input($option_name_array[$languages[$i]['id']]);

          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, products_options_name, language_id) values ('" . (int)$products_options_id . "', '" . tep_db_input($option_name) . "', '" . (int)$languages[$i]['id'] . "')");
        }
        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'add_product_option_values':
        $value_name_array = $HTTP_POST_VARS['value_name'];
        $value_id = tep_db_prepare_input($HTTP_POST_VARS['value_id']);
        $option_id = tep_db_prepare_input($HTTP_POST_VARS['option_id']);

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);

          tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$value_id . "', '" . (int)$languages[$i]['id'] . "', '" . tep_db_input($value_name) . "')");
        }

        tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id) values ('" . (int)$option_id . "', '" . (int)$value_id . "')");

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'add_product_attributes':
        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $options_id = tep_db_prepare_input($HTTP_POST_VARS['options_id']);
        $values_id = tep_db_prepare_input($HTTP_POST_VARS['values_id']);
        $value_price = tep_db_prepare_input($HTTP_POST_VARS['value_price']);
        $price_prefix = tep_db_prepare_input($HTTP_POST_VARS['price_prefix']);

        tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " values (null, '" . (int)$products_id . "', '" . (int)$options_id . "', '" . (int)$values_id . "', '" . (float)tep_db_input($value_price) . "', '" . tep_db_input($price_prefix) . "')");

        if (DOWNLOAD_ENABLED == 'true') {
          $products_attributes_id = tep_db_insert_id();

          $products_attributes_filename = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_filename']);
          $products_attributes_maxdays = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxdays']);
          $products_attributes_maxcount = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxcount']);

          if (tep_not_null($products_attributes_filename)) {
            tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " values (" . (int)$products_attributes_id . ", '" . tep_db_input($products_attributes_filename) . "', '" . tep_db_input($products_attributes_maxdays) . "', '" . tep_db_input($products_attributes_maxcount) . "')");
          }
        }

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'update_option_name':
        $option_name_array = $HTTP_POST_VARS['option_name'];
        $option_id = tep_db_prepare_input($HTTP_POST_VARS['option_id']);

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $option_name = tep_db_prepare_input($option_name_array[$languages[$i]['id']]);

          tep_db_query("update " . TABLE_PRODUCTS_OPTIONS . " set products_options_name = '" . tep_db_input($option_name) . "' where products_options_id = '" . (int)$option_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
        }

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'update_value':
        $value_name_array = $HTTP_POST_VARS['value_name'];
        $value_id = tep_db_prepare_input($HTTP_POST_VARS['value_id']);
        $option_id = tep_db_prepare_input($HTTP_POST_VARS['option_id']);

        for ($i=0, $n=sizeof($languages); $i<$n; $i ++) {
          $value_name = tep_db_prepare_input($value_name_array[$languages[$i]['id']]);

          tep_db_query("update " . TABLE_PRODUCTS_OPTIONS_VALUES . " set products_options_values_name = '" . tep_db_input($value_name) . "' where products_options_values_id = '" . tep_db_input($value_id) . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
        }

        tep_db_query("update " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " set products_options_id = '" . (int)$option_id . "'  where products_options_values_id = '" . (int)$value_id . "'");

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'update_product_attribute':
        $products_id = tep_db_prepare_input($HTTP_POST_VARS['products_id']);
        $options_id = tep_db_prepare_input($HTTP_POST_VARS['options_id']);
        $values_id = tep_db_prepare_input($HTTP_POST_VARS['values_id']);
        $value_price = tep_db_prepare_input($HTTP_POST_VARS['value_price']);
        $price_prefix = tep_db_prepare_input($HTTP_POST_VARS['price_prefix']);
        $attribute_id = tep_db_prepare_input($HTTP_POST_VARS['attribute_id']);

        tep_db_query("update " . TABLE_PRODUCTS_ATTRIBUTES . " set products_id = '" . (int)$products_id . "', options_id = '" . (int)$options_id . "', options_values_id = '" . (int)$values_id . "', options_values_price = '" . (float)tep_db_input($value_price) . "', price_prefix = '" . tep_db_input($price_prefix) . "' where products_attributes_id = '" . (int)$attribute_id . "'");

        if (DOWNLOAD_ENABLED == 'true') {
          $products_attributes_filename = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_filename']);
          $products_attributes_maxdays = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxdays']);
          $products_attributes_maxcount = tep_db_prepare_input($HTTP_POST_VARS['products_attributes_maxcount']);

          if (tep_not_null($products_attributes_filename)) {
            tep_db_query("replace into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " set products_attributes_id = '" . (int)$attribute_id . "', products_attributes_filename = '" . tep_db_input($products_attributes_filename) . "', products_attributes_maxdays = '" . tep_db_input($products_attributes_maxdays) . "', products_attributes_maxcount = '" . tep_db_input($products_attributes_maxcount) . "'");
          }
        }

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'delete_option':
        $option_id = tep_db_prepare_input($HTTP_GET_VARS['option_id']);

        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$option_id . "'");

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'delete_value':
        $value_id = tep_db_prepare_input($HTTP_GET_VARS['value_id']);

        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$value_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$value_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_values_id = '" . (int)$value_id . "'");

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
      case 'delete_attribute':
        $attribute_id = tep_db_prepare_input($HTTP_GET_VARS['attribute_id']);

        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int)$attribute_id . "'");

// added for DOWNLOAD_ENABLED. Always try to remove attributes, even if downloads are no longer enabled
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id = '" . (int)$attribute_id . "'");

        tep_redirect(tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="row">
	<div class="col-xs-12 col-md-6">   		 
		<table class="table table-hover table-responsive table-striped table-condensed">
<!-- bof options //-->
<?php
  if ($action == 'delete_product_option') { // delete product option
	$options = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$HTTP_GET_VARS['option_id'] . "' and language_id = '" . (int)$languages_id . "'");
	$options_values = tep_db_fetch_array($options);

	$products = tep_db_query("select p.products_id, pd.products_name, pov.products_options_values_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pov.language_id = '" . (int)$languages_id . "' and pd.language_id = '" . (int)$languages_id . "' and pa.products_id = p.products_id and pa.options_id='" . (int)$HTTP_GET_VARS['option_id'] . "' and pov.products_options_values_id = pa.options_values_id order by pd.products_name");
	if (tep_db_num_rows($products)) {
?>
				<thead>
                  <tr>
                    <th><?php echo TABLE_HEADING_ID; ?></th>
                    <th><?php echo TABLE_HEADING_PRODUCT; ?></th>
                    <th><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
                  </tr>
				</thead>
		<?php
			$rows = 0;
			while ($products_values = tep_db_fetch_array($products)) {
				$rows++;
		?>
                <tbody>
                  <tr>
                    <td><?php echo $products_values['products_id']; ?></td>
                    <td><?php echo $products_values['products_name']; ?></td>
                    <td><?php echo $products_values['products_options_values_name']; ?></td>
                  </tr>
				 </tbody> 
	  <?php 
			} 
	  ?>
<!-- BOF WARNING OPTION CANNOT BE DELETED PART //-->
                <div class="alert alert-danger">
				<h4><?php echo $options_values['products_options_name']; ?></h4>
                 <?php echo TEXT_WARNING_OF_DELETE; ?>
				  <br /><br />
                 <?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>               
				</div>
<!-- EOF WARNING OPTION CANNOT BE DELETED PART //-->				
<?php
    } else {
?>
<!-- BOF CONFIRM DELETE OPTION PART //--> 
                  <div class="alert alert-success">
				  <h4><?php echo $options_values['products_options_name']; ?></h4>
				   <?php echo TEXT_OK_TO_DELETE; ?>
                    <br /><br />
                   <?php echo tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_option&option_id=' . $HTTP_GET_VARS['option_id'] . '&' . $page_info, 'NONSSL'), 'primary', null, 'btn-danger') . 
				   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>
                  </div>
<!-- EOF CONFIRM DELETE OPTION PART //--> 
<?php
    }
?>
              
<?php
  } else {
?>
<div class="row">
	<div class="col-xs-6 col-md-8">
		<h3><?php echo HEADING_TITLE_OPT; ?></h3>
	</div>
<!-- PAGINATION START //-->			 
	<div class="col-xs-6 col-md-4 mt15 text-right">
	<?php
		$options = "select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "' order by products_options_id";
		$options_split = new splitPageResults($option_page, MAX_ROW_LISTS_OPTIONS, $options, $options_query_numrows);

		echo $options_split->display_links($options_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $option_page, 'value_page=' . $value_page . '&attribute_page=' . $attribute_page, 'option_page');
	?> 
	</div>
<!-- PAGINATION END //-->
</div>
			  <thead>
                <th><?php echo TABLE_HEADING_ID; ?></th>
                <th><?php echo TABLE_HEADING_OPT_NAME; ?></th>
                <th><?php echo TABLE_HEADING_ACTION; ?></th>
              </thead>
			  <tbody>
<?php
    $next_id = 1;
    $rows = 0;
    $options = tep_db_query($options);
    while ($options_values = tep_db_fetch_array($options)) {
      $rows++;
?>
<!-- BOF EDIT OPTION PART //--> 
<?php
	if (($action == 'update_option') && ($HTTP_GET_VARS['option_id'] == $options_values['products_options_id'])) {
		echo '<tr class="info">';
	} else {
		echo '<tr>';
	}

	if (($action == 'update_option') && ($HTTP_GET_VARS['option_id'] == $options_values['products_options_id'])) {
	     echo '<form class="form-horizontal" name="option" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option_name&' . $page_info, 'NONSSL') . '" method="post">';
        
		$inputs = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $option_name = tep_db_query("select products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $options_values['products_options_id'] . "' and language_id = '" . $languages[$i]['id'] . "'");
          $option_name = tep_db_fetch_array($option_name);
          $inputs .= '<label class="col-sm-1 control-label">' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;</label>
					  <div class="col-sm-11"><input type="text" class="form-control" name="option_name[' . $languages[$i]['id'] . ']" value="' . $option_name['products_options_name'] . '"></div>
					  <div class="clearfix"></div>';
        }
?>
				<td>
					<?php echo $options_values['products_options_id']; ?><input type="hidden" name="option_id" value="<?php echo $options_values['products_options_id']; ?>">
				</td>
				<td>
					<div class="form-group">
						<?php echo $inputs; ?>
					</div>
				</td>
				<td>
					<?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success btn-sm') . 
					'&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL'), '', null, 'btn-default btn-sm'); ?>
				</td>
<?php
		
        echo '</form>';
      } else {
?>
                <td><?php echo $options_values["products_options_id"]; ?></td>
                <td><?php echo $options_values["products_options_name"]; ?></td>
	
                <td><?php echo tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option&option_id=' . $options_values['products_options_id'] . '&' . $page_info, 'NONSSL'), '', null, 'btn-warning btn-sm') . 
					'&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_product_option&option_id=' . $options_values['products_options_id'] . '&' . $page_info, 'NONSSL'), '', null, 'btn-danger btn-sm'); ?>
				</td>
<!-- EOF EDIT OPTION PART //--> 
<?php
      }
?>
              </tr>
<?php
      $max_options_id_query = tep_db_query("select max(products_options_id) + 1 as next_id from " . TABLE_PRODUCTS_OPTIONS);
      $max_options_id_values = tep_db_fetch_array($max_options_id_query);
      $next_id = $max_options_id_values['next_id'];
    }
?>
<!-- BOF ADD NEW OPTION PART //-->             
<?php
    if ($action != 'update_option') {
?>
              <tr>
<?php
      echo '<form class="form-horizontal" name="options" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=add_product_options&' . $page_info, 'NONSSL') . '" method="post"><input type="hidden" name="products_options_id" value="' . $next_id . '">';
      $inputs = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $inputs .= '<label class="col-sm-1 control-label">' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;</label>
					<div class="col-sm-10"><input type="text" class="form-control" name="option_name[' . $languages[$i]['id'] . ']" placeholder="Add new option"></div>
					<div class="clearfix"></div>';
      }
?>
                <td><?php echo $next_id; ?></td>	
                <td>
					<div class="form-group">
						<?php echo $inputs; ?>
					</div>	
				</td>
                <td><?php echo tep_draw_button(IMAGE_INSERT, 'fa fa-plus', null, 'primary', null, 'btn-default'); ?></td>
<?php
      echo '</form>';
?>
              </tr>
           
<?php
    }
  }
?>
<!-- EOF ADD NEW OPTION PART //--> 
            </tbody>
		</table>
	</div> <!-- eof col-xs-12 col-md-6 //-->
<!-- eof options //-->	
	<div class="col-xs-12 col-md-6">
		<table class="table table-hover table-responsive table-striped table-condensed">
<!-- bof value //-->
<?php
  if ($action == 'delete_option_value') { // delete product option value
    $values = tep_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$HTTP_GET_VARS['value_id'] . "' and language_id = '" . (int)$languages_id . "'");
    $values_values = tep_db_fetch_array($values);
?>      
             
<?php
    $products = tep_db_query("select p.products_id, pd.products_name, po.products_options_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and po.language_id = '" . (int)$languages_id . "' and pa.products_id = p.products_id and pa.options_values_id='" . (int)$HTTP_GET_VARS['value_id'] . "' and po.products_options_id = pa.options_id order by pd.products_name");
    if (tep_db_num_rows($products)) {
?>
                  <thead>
                   <tr>
                    <th><?php echo TABLE_HEADING_ID; ?></th>
                    <th><?php echo TABLE_HEADING_PRODUCT; ?></th>
                    <th><?php echo TABLE_HEADING_OPT_NAME; ?></th>
                   </tr>
				  </thead>
                  
<?php
      while ($products_values = tep_db_fetch_array($products)) {
        $rows++;
?>
                 <tbody>
                  <tr>
                    <td><?php echo $products_values['products_id']; ?></td>
                    <td><?php echo $products_values['products_name']; ?></td>
                    <td><?php echo $products_values['products_options_name']; ?></td>
                  </tr>
				 </tbody>
<?php
      }
?>
<!-- BOF WARNING OPTION VALUE CANNOT BE DELETED PART //-->
                 <div class="alert alert-danger">
				  <h4><?php echo $values_values['products_options_values_name']; ?></h4>
                   <?php echo TEXT_WARNING_OF_DELETE; ?>
                    <br /><br />
                   <?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>
                 </div>
<!-- EOF WARNING OPTION VALUE CANNOT BE DELETED PART //-->				 
<?php
    } else {
?>
<!-- BOF CONFIRM DELETE OPTION VALUE //-->
                 <div class="alert alert-success">
				  <h4><?php echo $values_values['products_options_values_name']; ?></h4>
                   <?php echo TEXT_OK_TO_DELETE; ?>
				    <br /><br />
				   <?php echo tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_value&value_id=' . $HTTP_GET_VARS['value_id'] . '&' . $page_info, 'NONSSL'), 'primary', null, 'btn-danger') . 
				   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL')); ?>
                 </div>
<!-- EOF CONFIRM DELETE OPTION VALUE //-->
<?php
    }
?>
              
              
<?php
  } else {
?>
<div class="row">
	<div class="col-xs-6 col-md-8">
		<h3><?php echo HEADING_TITLE_VAL; ?></h3>
	</div>
	<!-- PAGINATION START //-->			   
	<div class="col-xs-6 col-md-4 mt15 text-right">
		<?php
			$values = "select pov.products_options_values_id, pov.products_options_values_name, pov2po.products_options_id from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po on pov.products_options_values_id = pov2po.products_options_values_id where pov.language_id = '" . (int)$languages_id . "' order by pov.products_options_values_id";
			$values_split = new splitPageResults($value_page, MAX_ROW_LISTS_OPTIONS, $values, $values_query_numrows);

			echo $values_split->display_links($values_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $value_page, 'option_page=' . $option_page . '&attribute_page=' . $attribute_page, 'value_page');
		?>
	</div>
<!-- PAGINATION END //-->	   
</div>
             <thead>  
              <tr>
                <th><?php echo TABLE_HEADING_ID; ?></th>
                <th><?php echo TABLE_HEADING_OPT_NAME; ?></th>
                <th><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
                <th><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
             </thead>
			 <tbody>
<?php
    $next_id = 1;
    $rows = 0;
    $values = tep_db_query($values);
    while ($values_values = tep_db_fetch_array($values)) {
      $options_name = tep_options_name($values_values['products_options_id']);
      $values_name = $values_values['products_options_values_name'];
      $rows++;
?>
<!-- BOF EDIT OPTION VALUE PART //-->
<?php 
	if (($action == 'update_option_value') && ($HTTP_GET_VARS['value_id'] == $values_values['products_options_values_id'])) {
		echo '<tr class="info">';
	} else {
		echo '<tr>';
	} 
?>
<?php
      if (($action == 'update_option_value') && ($HTTP_GET_VARS['value_id'] == $values_values['products_options_values_id'])) {
        echo '<form class="form-horizontal" name="values" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_value&' . $page_info, 'NONSSL') . '" method="post">';
        $inputs = '';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          $value_name = tep_db_query("select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$values_values['products_options_values_id'] . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          $value_name = tep_db_fetch_array($value_name);
          $inputs .= '<label class="col-sm-1 control-label">' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;</label>
					  <div class="col-sm-10"><input type="text" class="form-control" name="value_name[' . $languages[$i]['id'] . ']" value="' . $value_name['products_options_values_name'] . '"></div>
					  <div class="clearfix"></div>';
        }
?>
                <td><?php echo $values_values['products_options_values_id']; ?><input type="hidden" name="value_id" value="<?php echo $values_values['products_options_values_id']; ?>"></td>
                <td><select class="form-control" name="option_id">
<?php
        $options = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$languages_id . "' order by products_options_name");
        while ($options_values = tep_db_fetch_array($options)) {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '"';
          if ($values_values['products_options_id'] == $options_values['products_options_id']) { 
            echo ' selected';
          }
          echo '>' . $options_values['products_options_name'] . '</option>';
        } 
?>
                </select></td>
                <td><?php echo $inputs; ?></td>
                <td><?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success btn-sm') . 
				    '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL'), '', null, 'btn-default btn-sm'); ?></td>
<?php
        echo '</form>';
      } else {
?>
                <td><?php echo $values_values["products_options_values_id"]; ?>&nbsp;</td>
                <td><?php echo $options_name; ?></td>
                <td><?php echo $values_name; ?></td>
                <td><?php echo tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option_value&value_id=' . $values_values['products_options_values_id'] . '&' . $page_info, 'NONSSL'), '', null, 'btn-warning btn-sm') . 
				    '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_option_value&value_id=' . $values_values['products_options_values_id'] . '&' . $page_info, 'NONSSL'), '', null, 'btn-danger btn-sm'); ?></td>
<?php
      }
      $max_values_id_query = tep_db_query("select max(products_options_values_id) + 1 as next_id from " . TABLE_PRODUCTS_OPTIONS_VALUES);
      $max_values_id_values = tep_db_fetch_array($max_values_id_query);
      $next_id = $max_values_id_values['next_id'];
    }
?>
              </tr>
<!-- EOF EDIT OPTION VALUE PART //-->
<?php
    if ($action != 'update_option_value') {
?>
<!-- BOF ADD NEW OPTION VALUE PART //-->
              <tr>
<?php
      echo '<form class="form-horizontal" name="values" action="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=add_product_option_values&' . $page_info, 'NONSSL') . '" method="post">';
?>
                <td><?php echo $next_id; ?></td>
                <td><select class="form-control" name="option_id">
<?php
      $options = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
      while ($options_values = tep_db_fetch_array($options)) {
        echo '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
      }

      $inputs = '';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
        $inputs .= '<label class="col-sm-1 control-label">' . tep_image(tep_catalog_href_link(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], '', 'SSL'), $languages[$i]['name']) . '&nbsp;</label>
					<div class="col-sm-10"><input type="text" class="form-control" name="value_name[' . $languages[$i]['id'] . ']" placeholder="Add new option value"></div>
					<div class="clearfix"></div>';
      }
?>
                </select></td>
                <td><input type="hidden" name="value_id" value="<?php echo $next_id; ?>"><?php echo $inputs; ?></td>
                <td><?php echo tep_draw_button(IMAGE_INSERT, 'fa fa-plus', null, 'primary', null, 'btn-default'); ?></td>
<?php
      echo '</form>';
?>
              </tr>
            
<?php
    }
  }
?>
<!-- EOF ADD NEW OPTION VALUE PART //-->
              </tbody>
            </table>
		</div> <!-- eof col-xs-12 col-md-6 //-->		
	</div> <!-- EOF row //-->			
<hr class="devider">
<!-- bof products_attributes //--> 
<div class="row">
	<div class="col-xs-12">     
      <table class="table table-hover table-responsive table-striped table-condensed">
<?php
  if ($action == 'update_attribute') {
    $form_action = 'update_product_attribute';
  } else {
    $form_action = 'add_product_attributes';
  }
?>
<div class="row">
	<div class="col-xs-6 col-md-8">
		<h3><?php echo HEADING_TITLE_ATRIB; ?></h3>
	</div>
<!-- PAGINATION START //-->				
	<div class="col-xs-6 col-md-4 mt15 text-right">
		<?php
		  $attributes = "select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pa.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' order by pd.products_name";
		  $attributes_split = new splitPageResults($attribute_page, MAX_ROW_LISTS_OPTIONS, $attributes, $attributes_query_numrows);

		  echo $attributes_split->display_links($attributes_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $attribute_page, 'option_page=' . $option_page . '&value_page=' . $value_page, 'attribute_page');
		?>
	</div>
<!-- PAGINATION END //-->
</div>
	<form class="form-horizontal" name="attributes" action="<?php echo tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=' . $form_action . '&' . $page_info); ?>" method="post">
		
        <thead> 
          <tr>
            <th><?php echo TABLE_HEADING_ID; ?></th>
            <th><?php echo TABLE_HEADING_PRODUCT; ?></th>
            <th><?php echo TABLE_HEADING_OPT_NAME; ?></th>
            <th><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
            <th><?php echo TABLE_HEADING_OPT_PRICE; ?></th>
            <th><?php echo TABLE_HEADING_OPT_PRICE_PREFIX; ?></th>
            <th><?php echo TABLE_HEADING_ACTION; ?></th>
          </tr>
         </thead>
		 <tbody>
<?php
  $next_id = 1;
  $attributes = tep_db_query($attributes);
  while ($attributes_values = tep_db_fetch_array($attributes)) {
    $products_name_only = tep_get_products_name($attributes_values['products_id']);
    $options_name = tep_options_name($attributes_values['options_id']);
    $values_name = tep_values_name($attributes_values['options_values_id']);
    $rows++;
?>
<!-- BOF EDIT PRODUCT ATTRIBUTES PART //-->
<?php 
	if (($action == 'delete_product_attribute') && ($HTTP_GET_VARS['attribute_id'] == $attributes_values['products_attributes_id'])) { 
		echo '<tr class="danger">';
	} elseif (($action == 'update_attribute') && ($HTTP_GET_VARS['attribute_id'] == $attributes_values['products_attributes_id'])) {
		echo '<tr class="info">';
	} else {
	echo '<tr>';
	} 
?>		  
<?php
    if (($action == 'update_attribute') && ($HTTP_GET_VARS['attribute_id'] == $attributes_values['products_attributes_id'])) {
?>
            <td><?php echo $attributes_values['products_attributes_id']; ?><input type="hidden" name="attribute_id" value="<?php echo $attributes_values['products_attributes_id']; ?>"></td>
            <td><select class="form-control" name="products_id">
<?php
      $products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
      while($products_values = tep_db_fetch_array($products)) {
        if ($attributes_values['products_id'] == $products_values['products_id']) {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '" SELECTED>' . $products_values['products_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
        }
      } 
?>
            </select></td>
            <td><select class="form-control" name="options_id">
<?php
      $options = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
      while($options_values = tep_db_fetch_array($options)) {
        if ($attributes_values['options_id'] == $options_values['products_options_id']) {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '" SELECTED>' . $options_values['products_options_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
        }
      } 
?>
            </select></td>
            <td><select class="form-control" name="values_id">
<?php
      $values = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id ='" . $languages_id . "' order by products_options_values_name");
      while($values_values = tep_db_fetch_array($values)) {
        if ($attributes_values['options_values_id'] == $values_values['products_options_values_id']) {
          echo "\n" . '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '" SELECTED>' . $values_values['products_options_values_name'] . '</option>';
        } else {
          echo "\n" . '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '">' . $values_values['products_options_values_name'] . '</option>';
        }
      } 
?>        
            </select></td>
            <td><input type="text" class="form-control" name="value_price" value="<?php echo $attributes_values['options_values_price']; ?>" size="2"></td>
            <td width="2%"><input type="text" class="form-control text-center" name="price_prefix" value="<?php echo $attributes_values['price_prefix']; ?>" size="1"></td>
            <td><?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success btn-sm') . 
				'&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL'), '', null, 'btn-default btn-sm'); ?>
			</td>
<?php
      if (DOWNLOAD_ENABLED == 'true') {
        $download_query_raw ="select products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount 
                              from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " 
                              where products_attributes_id='" . $attributes_values['products_attributes_id'] . "'";
        $download_query = tep_db_query($download_query_raw);
        if (tep_db_num_rows($download_query) > 0) {
          $download = tep_db_fetch_array($download_query);
          $products_attributes_filename = $download['products_attributes_filename'];
          $products_attributes_maxdays  = $download['products_attributes_maxdays'];
          $products_attributes_maxcount = $download['products_attributes_maxcount'];
        }
?>
<?php 
	if (($action == 'update_attribute') && ($HTTP_GET_VARS['attribute_id'] == $attributes_values['products_attributes_id'])) {
		echo '<tr class="info">';
	} else {
		echo '<tr>';	
	}
	?>
				
                  <td><?php echo TABLE_HEADING_DOWNLOAD; ?></td>
                  <td colspan="2"><?php echo TABLE_TEXT_FILENAME . tep_draw_input_field('products_attributes_filename', $products_attributes_filename); ?></td>
                  <td><?php echo TABLE_TEXT_MAX_DAYS . tep_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'style="width:80px;" class="form-control text-center"'); ?></td>
                  <td colspan="3"><?php echo TABLE_TEXT_MAX_COUNT . tep_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'style="width:80px;" class="form-control text-center"'); ?></td>
				</tr>
          
<?php
      }
?>
<?php
    } elseif (($action == 'delete_product_attribute') && ($HTTP_GET_VARS['attribute_id'] == $attributes_values['products_attributes_id'])) {
?>
<!-- BOF CONFIRM DELETE PRODUCT ATTRIBUTES //-->
            <td><strong><?php echo $attributes_values["products_attributes_id"]; ?></strong></td>
            <td><strong><?php echo $products_name_only; ?></strong></td>
            <td><strong><?php echo $options_name; ?></strong></td>
            <td><strong><?php echo $values_name; ?></strong></td>
            <td><strong><?php echo $attributes_values["options_values_price"]; ?></strong></td>
            <td class="text-center"><strong><?php echo $attributes_values["price_prefix"]; ?></strong></td>
            <td><?php echo tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_attribute&attribute_id=' . $HTTP_GET_VARS['attribute_id'] . '&' . $page_info), 'primary', null, 'btn-danger btn-sm') . 
				'&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, $page_info, 'NONSSL'), '', null, 'btn-default btn-sm'); ?></td>
<!-- EOF CONFIRM DELETE PRODUCT ATTRIBUTES //-->				
<?php
    } else {
?>
            <td><?php echo $attributes_values["products_attributes_id"]; ?></td>
            <td><?php echo $products_name_only; ?></td>
            <td><?php echo $options_name; ?></td>
            <td><?php echo $values_name; ?></td>
            <td><?php echo $attributes_values["options_values_price"]; ?></td>
            <td class="text-center"><?php echo $attributes_values["price_prefix"]; ?></td>
            <td><?php echo tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_attribute&attribute_id=' . $attributes_values['products_attributes_id'] . '&' . $page_info, 'NONSSL'), '', null, 'btn-warning btn-sm') . 
				'&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_product_attribute&attribute_id=' . $attributes_values['products_attributes_id'] . '&' . $page_info, 'NONSSL'), '', null, 'btn-danger btn-sm'); ?>
			</td>
<?php
    }
    $max_attributes_id_query = tep_db_query("select max(products_attributes_id) + 1 as next_id from " . TABLE_PRODUCTS_ATTRIBUTES);
    $max_attributes_id_values = tep_db_fetch_array($max_attributes_id_query);
    $next_id = $max_attributes_id_values['next_id'];
?>
          </tr>
<!-- EOF EDIT PRODUCT ATTRIBUTES PART //-->
<?php
  }
  if ($action != 'update_attribute') {
?>
<!-- BOF ADD NEW PRODUCT ATTRIBUTES PART //-->    
          <tr>
            <td><?php echo $next_id; ?></td>
      	    <td><select class="form-control" name="products_id">
<?php
    $products = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
    while ($products_values = tep_db_fetch_array($products)) {
      echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
    } 
?>
            </select></td>
            <td><select class="form-control" name="options_id">
<?php
    $options = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_name");
    while ($options_values = tep_db_fetch_array($options)) {
      echo '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '">' . $options_values['products_options_name'] . '</option>';
    } 
?>
            </select></td>
            <td><select class="form-control" name="values_id">
<?php
    $values = tep_db_query("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . $languages_id . "' order by products_options_values_name");
    while ($values_values = tep_db_fetch_array($values)) {
      echo '<option name="' . $values_values['products_options_values_name'] . '" value="' . $values_values['products_options_values_id'] . '">' . $values_values['products_options_values_name'] . '</option>';
    } 
?>
            </select></td>
            <td><input type="text" class="form-control" name="value_price" size="2"></td>
            <td width="2%"><input type="text" class="form-control text-center" name="price_prefix" size="1" value="+"></td>
            <td><?php echo tep_draw_button(IMAGE_INSERT, 'fa fa-plus', null, 'primary', null, 'btn-default'); ?></td>
          </tr>
<?php
      if (DOWNLOAD_ENABLED == 'true') {
        $products_attributes_maxdays  = DOWNLOAD_MAX_DAYS;
        $products_attributes_maxcount = DOWNLOAD_MAX_COUNT;
?>
          
                <tr>
                  <td><?php echo TABLE_HEADING_DOWNLOAD; ?></td>
				  <td colspan="2"><?php echo TABLE_TEXT_FILENAME . tep_draw_input_field('products_attributes_filename', $products_attributes_filename); ?></td>
                  <td><?php echo TABLE_TEXT_MAX_DAYS . tep_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'style="width:80px;" class="form-control text-center"'); ?></td>
                  <td colspan="3"><?php echo TABLE_TEXT_MAX_COUNT . tep_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'style="width:80px;" class="form-control text-center"'); ?></td>
                </tr>

<?php
      } // end of DOWNLOAD_ENABLED section
?>
<?php
  }
?>
<!-- EOF ADD NEW PRODUCT ATTRIBUTES PART //-->
              </tbody>
            </table>
		</div><!-- EOF col-xs-12 //-->			
	</div><!-- EOF row //-->
</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>