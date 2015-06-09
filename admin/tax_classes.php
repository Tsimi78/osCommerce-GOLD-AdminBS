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
        $tax_class_title = tep_db_prepare_input($HTTP_POST_VARS['tax_class_title']);
        $tax_class_description = tep_db_prepare_input($HTTP_POST_VARS['tax_class_description']);

        tep_db_query("insert into " . TABLE_TAX_CLASS . " (tax_class_title, tax_class_description, date_added) values ('" . tep_db_input($tax_class_title) . "', '" . tep_db_input($tax_class_description) . "', now())");

        tep_redirect(tep_href_link(FILENAME_TAX_CLASSES));
        break;
      case 'save':
        $tax_class_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);
        $tax_class_title = tep_db_prepare_input($HTTP_POST_VARS['tax_class_title']);
        $tax_class_description = tep_db_prepare_input($HTTP_POST_VARS['tax_class_description']);

        tep_db_query("update " . TABLE_TAX_CLASS . " set tax_class_id = '" . (int)$tax_class_id . "', tax_class_title = '" . tep_db_input($tax_class_title) . "', tax_class_description = '" . tep_db_input($tax_class_description) . "', last_modified = now() where tax_class_id = '" . (int)$tax_class_id . "'");

        tep_redirect(tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tax_class_id));
        break;
      case 'deleteconfirm':
        $tax_class_id = tep_db_prepare_input($HTTP_GET_VARS['tID']);

        tep_db_query("delete from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int)$tax_class_id . "'");

        tep_redirect(tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page']));
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
                <th><?php echo TABLE_HEADING_TAX_CLASSES; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			</thead>
          <tbody>  
<?php
  $classes_query_raw = "select tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from " . TABLE_TAX_CLASS . " order by tax_class_title";
  $classes_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $classes_query_raw, $classes_query_numrows);
  $classes_query = tep_db_query($classes_query_raw);
  while ($classes = tep_db_fetch_array($classes_query)) {
    if ((!isset($HTTP_GET_VARS['tID']) || (isset($HTTP_GET_VARS['tID']) && ($HTTP_GET_VARS['tID'] == $classes['tax_class_id']))) && !isset($tcInfo) && (substr($action, 0, 3) != 'new')) {
      $tcInfo = new objectInfo($classes);
    }

    if (isset($tcInfo) && is_object($tcInfo) && ($classes['tax_class_id'] == $tcInfo->tax_class_id)) {
      echo '  <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '\'">';
    } else {
      echo'  <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $classes['tax_class_id']) . '\'">';
    }
?>
                <td><?php echo $classes['tax_class_title']; ?></td>
                <td class="text-right"><?php if (isset($tcInfo) && is_object($tcInfo) && ($classes['tax_class_id'] == $tcInfo->tax_class_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $classes['tax_class_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
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
			<div class="col-md-12 mb10 text-right">
				<?php echo tep_draw_button(IMAGE_NEW_TAX_CLASS, 'fa fa-plus', tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&action=new'), 'primary', null, 'btn-default'); ?>
			</div>        
<?php
  }
?>
           <div class="col-md-5">
				<?php echo $classes_split->display_count($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES); ?>
           </div>
           <div class="col-md-3 pull-right text-right">	     
				<?php echo $classes_split->display_links($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
           </div>
		</div>
	</div> <!-- EOF col-md-8 -->
    <div class="col-md-4">			  
<?php
  switch ($action) {
    case 'new':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</span></div>';	  

		echo '<div class="panel-body">' .	
				tep_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&action=insert') . TEXT_INFO_INSERT_INTRO .
				'<br /><br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_draw_input_field('tax_class_title') . 
				'<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . tep_draw_input_field('tax_class_description') .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'])) . '</div>' .
				'</div></div>';
      break;
    case 'edit':
		echo '<div class="panel panel-primary">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</span></div>';	  

		echo '<div class="panel-body">' .
				tep_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=save') . TEXT_INFO_EDIT_INTRO .
				'<br /><br />' . TEXT_INFO_CLASS_TITLE . '<br />' . tep_draw_input_field('tax_class_title', $tcInfo->tax_class_title) .
				'<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . tep_draw_input_field('tax_class_description', $tcInfo->tax_class_description) .
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . 
										 '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tcInfo->tax_class_id)) . '</div>' .
				'</div></div>';
	  break;
    case 'delete':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_TAX_CLASS . '</span></div>';	  

		echo '<div class="panel-body">' .
				tep_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=deleteconfirm') . TEXT_INFO_DELETE_INTRO .
				'<br /><br /><strong>' . $tcInfo->tax_class_title . '</strong>' .
				'<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
											   '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tcInfo->tax_class_id)) . '</div>' .
				'</div></div>';
	  break;
    default:
      if (isset($tcInfo) && is_object($tcInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $tcInfo->tax_class_title . '</strong></span></div>';	  

		echo '<div class="panel-body">' .		  
				'<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit'), 'primary', null, 'btn-warning') . 
										 '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_TAX_CLASSES, 'page=' . $HTTP_GET_VARS['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
				'<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($tcInfo->date_added) . 
				'<br />' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($tcInfo->last_modified) . 
				'<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . $tcInfo->tax_class_description .
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
