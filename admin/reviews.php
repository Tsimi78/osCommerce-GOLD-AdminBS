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
      case 'setflag':
        if ( ($HTTP_GET_VARS['flag'] == '0') || ($HTTP_GET_VARS['flag'] == '1') ) {
          if (isset($HTTP_GET_VARS['rID'])) {
            tep_set_review_status($HTTP_GET_VARS['rID'], $HTTP_GET_VARS['flag']);
          }
        }

        tep_redirect(tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $HTTP_GET_VARS['rID']));
        break;
      case 'update':
        $reviews_id = tep_db_prepare_input($HTTP_GET_VARS['rID']);
        $reviews_rating = tep_db_prepare_input($HTTP_POST_VARS['reviews_rating']);
        $reviews_text = tep_db_prepare_input($HTTP_POST_VARS['reviews_text']);
        $reviews_status = tep_db_prepare_input($HTTP_POST_VARS['reviews_status']);

        tep_db_query("update " . TABLE_REVIEWS . " set reviews_rating = '" . tep_db_input($reviews_rating) . "', reviews_status = '" . tep_db_input($reviews_status) . "', last_modified = now() where reviews_id = '" . (int)$reviews_id . "'");
        tep_db_query("update " . TABLE_REVIEWS_DESCRIPTION . " set reviews_text = '" . tep_db_input($reviews_text) . "' where reviews_id = '" . (int)$reviews_id . "'");

        tep_redirect(tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $reviews_id));
        break;
      case 'deleteconfirm':
        $reviews_id = tep_db_prepare_input($HTTP_GET_VARS['rID']);

        tep_db_query("delete from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "'");
        tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int)$reviews_id . "'");

        tep_redirect(tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

   <h3><?php echo HEADING_TITLE; ?></h3>
     	    
<?php
  if ($action == 'edit') {
    $rID = tep_db_prepare_input($HTTP_GET_VARS['rID']);

    $reviews_query = tep_db_query("select r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating, r.reviews_status from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$rID . "' and r.reviews_id = rd.reviews_id");
    $reviews = tep_db_fetch_array($reviews_query);

    $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
    $products = tep_db_fetch_array($products_query);

    $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
    $products_name = tep_db_fetch_array($products_name_query);

    $rInfo_array = array_merge($reviews, $products, $products_name);
    $rInfo = new objectInfo($rInfo_array);

    if (!isset($rInfo->reviews_status)) $rInfo->reviews_status = '1';
    switch ($rInfo->reviews_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }
?>
<?php echo tep_draw_form('review', FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $HTTP_GET_VARS['rID'] . '&action=preview'); ?>	
    <div class="row">
      <div class="col-md-10"> 
		<div class="row">	  
			<div class="col-xs-12"><label><?php echo ENTRY_PRODUCT; ?></label>&nbsp;<?php echo $rInfo->products_name; ?></div>
			<div class="col-xs-12"><label><?php echo ENTRY_FROM; ?></label>&nbsp;<?php echo $rInfo->customers_name; ?></div>
			<div class="col-xs-12"><label><?php echo ENTRY_DATE; ?></label>&nbsp;<?php echo tep_date_short($rInfo->date_added); ?></div>
            <div class="col-xs-12"><label><?php echo TEXT_INFO_REVIEW_STATUS; ?></label>&nbsp;<?php echo tep_draw_radio_field('reviews_status', '1', $in_status) . '&nbsp;' . TEXT_REVIEW_PUBLISHED . '&nbsp;' . tep_draw_radio_field('reviews_status', '0', $out_status) . '&nbsp;' . TEXT_REVIEW_NOT_PUBLISHED; ?></div>			
		</div> 
      </div> 
	  <div class="col-md-2"><?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></div>         
	  <div class="col-xs-12 col-sm-6"><label><?php echo ENTRY_REVIEW; ?></label><?php echo tep_draw_textarea_field('reviews_text', 'soft', '60', '15', $rInfo->reviews_text); ?></div>
	  <div class="col-xs-12"><?php echo ENTRY_REVIEW_TEXT; ?></div>
	  <div class="col-xs-12 mt20 mb20"><label><?php echo ENTRY_RATING; ?></label>&nbsp;<?php echo TEXT_BAD; ?>&nbsp;<?php for ($i=1; $i<=5; $i++) echo tep_draw_radio_field('reviews_rating', $i, '', $rInfo->reviews_rating) . '&nbsp;'; echo TEXT_GOOD; ?></div>
	  <div class="col-xs-12 text-center"><?php echo tep_draw_hidden_field('reviews_id', $rInfo->reviews_id) . tep_draw_hidden_field('products_id', $rInfo->products_id) . tep_draw_hidden_field('customers_name', $rInfo->customers_name) . tep_draw_hidden_field('products_name', $rInfo->products_name) . tep_draw_hidden_field('products_image', $rInfo->products_image) . tep_draw_hidden_field('date_added', $rInfo->date_added) . tep_draw_button(IMAGE_PREVIEW, 'fa fa-eye', null, 'primary', null, 'btn-default') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $HTTP_GET_VARS['rID'])); ?></div>
	</div>		  
</form>
<?php
  } elseif ($action == 'preview') {
    if (tep_not_null($HTTP_POST_VARS)) {
      $rInfo = new objectInfo($HTTP_POST_VARS);
    } else {
      $rID = tep_db_prepare_input($HTTP_GET_VARS['rID']);

      $reviews_query = tep_db_query("select r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating, r.reviews_status from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$rID . "' and r.reviews_id = rd.reviews_id");
      $reviews = tep_db_fetch_array($reviews_query);

      $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
      $products = tep_db_fetch_array($products_query);

      $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
      $products_name = tep_db_fetch_array($products_name_query);

      $rInfo_array = array_merge($reviews, $products, $products_name);
      $rInfo = new objectInfo($rInfo_array);
    }
?>
<?php echo tep_draw_form('update', FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $HTTP_GET_VARS['rID'] . '&action=update', 'post', 'enctype="multipart/form-data"'); ?>
<div class="row">
	<div class="col-md-10">
		<div class="row">
			<div class="col-xs-12"><label><?php echo ENTRY_PRODUCT; ?></label>&nbsp;<?php echo $rInfo->products_name; ?></div>
			<div class="col-xs-12"><label><?php echo ENTRY_FROM; ?></label>&nbsp;<?php echo $rInfo->customers_name; ?></div>
			<div class="col-xs-12"><label><?php echo ENTRY_DATE; ?></label>&nbsp;<?php echo tep_date_short($rInfo->date_added); ?></div>
		</div> 
	</div> 
	<div class="col-md-2">
	  <?php echo tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?>
	</div>         
    <div class="col-md-12"><label><?php echo ENTRY_REVIEW; ?></label></div>
	<div class="col-md-6">            
	   <div class="panel panel-default">
			<div class="panel-body">
				<?php echo nl2br(tep_db_output(tep_break_string($rInfo->reviews_text,50))); ?>
			</div>
	   </div>
	</div>
	<div class="col-md-12"><label><?php echo ENTRY_RATING; ?></label>&nbsp;<?php echo tep_draw_stars($rInfo->reviews_rating); ?>&nbsp;<small>[<?php echo sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating); ?>]</small></div>
<?php
    if (tep_not_null($HTTP_POST_VARS)) {
/* Re-Post all POST'ed variables */
      reset($HTTP_POST_VARS);
      while (list($key, $value) = each($HTTP_POST_VARS)) echo tep_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
?>
    <div class="col-md-12 text-center">
		<?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $rInfo->reviews_id)); ?>
    </div>  
	
	  </form>
<?php
    } else {
      if (isset($HTTP_GET_VARS['origin'])) {
        $back_url = $HTTP_GET_VARS['origin'];
        $back_url_params = '';
      } else {
        $back_url = FILENAME_REVIEWS;
        $back_url_params = 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $rInfo->reviews_id;
      }
?>
    <div class="col-xs-12 text-center">
		<?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link($back_url, $back_url_params, 'NONSSL')); ?>
    </div>
<?php
    }
	echo '</div>'; // oef .row
  } else {
?>
<div class="row">
    <div class="col-md-8">
        <table class="table table-hover table-responsive table-striped">
			<thead>
		      <tr>
                <th><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                <th><?php echo TABLE_HEADING_RATING; ?></th>
                <th><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
                <th><?php echo TABLE_HEADING_STATUS; ?></th>
                <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
			</thead>
			<tbody>
<?php
    $reviews_query_raw = "select reviews_id, products_id, date_added, last_modified, reviews_rating, reviews_status from " . TABLE_REVIEWS . " order by date_added DESC";
    $reviews_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
    $reviews_query = tep_db_query($reviews_query_raw);
    while ($reviews = tep_db_fetch_array($reviews_query)) {
      if ((!isset($HTTP_GET_VARS['rID']) || (isset($HTTP_GET_VARS['rID']) && ($HTTP_GET_VARS['rID'] == $reviews['reviews_id']))) && !isset($rInfo)) {
        $reviews_text_query = tep_db_query("select r.reviews_read, r.customers_name, length(rd.reviews_text) as reviews_text_size from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$reviews['reviews_id'] . "' and r.reviews_id = rd.reviews_id");
        $reviews_text = tep_db_fetch_array($reviews_text_query);

        $products_image_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
        $products_image = tep_db_fetch_array($products_image_query);

        $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
        $products_name = tep_db_fetch_array($products_name_query);

        $reviews_average_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$reviews['products_id'] . "'");
        $reviews_average = tep_db_fetch_array($reviews_average_query);

        $review_info = array_merge($reviews_text, $reviews_average, $products_name);
        $rInfo_array = array_merge($reviews, $review_info, $products_image);
        $rInfo = new objectInfo($rInfo_array);
      }

      if (isset($rInfo) && is_object($rInfo) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) {
        echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $rInfo->reviews_id . '&action=preview') . '\'">';
      } else {
        echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $reviews['reviews_id']) . '\'">';
      }
?>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $reviews['reviews_id'] . '&action=preview') . '"><i class="fa fa-eye fa-lg" title="'. ICON_PREVIEW .'"></i></a>&nbsp;' . tep_get_products_name($reviews['products_id']); ?></td>
                <td><?php echo tep_draw_stars($reviews['reviews_rating']); ?></td>
                <td><?php echo tep_date_short($reviews['date_added']); ?></td>
                <td>
<?php
      if ($reviews['reviews_status'] == '1') {
        echo '&nbsp;<i class="fa fa-circle icon-green"></i>&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, 'action=setflag&flag=0&rID=' . $reviews['reviews_id'] . '&page=' . $HTTP_GET_VARS['page']) . '"><i class="fa fa-circle icon-gray"></i></a>';
      } else {
        echo '&nbsp;<a href="' . tep_href_link(FILENAME_REVIEWS, 'action=setflag&flag=1&rID=' . $reviews['reviews_id'] . '&page=' . $HTTP_GET_VARS['page']) . '"><i class="fa fa-circle icon-gray"></i></a>&nbsp;&nbsp;<i class="fa fa-circle icon-red"></i>';
      }
?>
              </td>
                <td class="text-right"><?php if ( (is_object($rInfo)) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $reviews['reviews_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              </tbody>
            </table>
		<div class="row">	
             <div class="col-md-5">
                <?php echo $reviews_split->display_count($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?>
             </div>
             <div class="col-md-3 pull-right text-right">     
				<?php echo $reviews_split->display_links($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?>
             </div>
		</div>	 
        <div class="clearfix"></div>
	</div> <!-- EOF col-md-8 -->
	<div class="col-md-4">			  
<?php
    switch ($action) {
      case 'delete':
	  		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_REVIEW . '</span></div>';
			
			echo '<div class="panel-body">' .
				 tep_draw_form('reviews', FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $rInfo->reviews_id . '&action=deleteconfirm') . TEXT_INFO_DELETE_REVIEW_INTRO .
				 '<br /><br /><strong>' . $rInfo->products_name . '</strong>' .
				 '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $rInfo->reviews_id)) .
				 '</div></div>';
        break;
      default:
      if (isset($rInfo) && is_object($rInfo)) {
			echo '<div class="panel panel-default">
					<div class="panel-heading"><span class="panel-title"><strong>' . $rInfo->products_name . '</strong></span></div>';		  
			echo '<div class="panel-body">' .
					'<div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $rInfo->reviews_id . '&action=edit'), 'primary', null, 'btn-warning') . 
												   '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_REVIEWS, 'page=' . $HTTP_GET_VARS['page'] . '&rID=' . $rInfo->reviews_id . '&action=delete'), 'primary', null, 'btn-danger') . '</div>' .
					'<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($rInfo->date_added);
			if (tep_not_null($rInfo->last_modified)) echo '<br />' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($rInfo->last_modified);
			echo '<br />' . tep_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) .
				 '<br />' . TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name .
				 '<br />' . TEXT_INFO_REVIEW_RATING . ' ' . tep_draw_stars($rInfo->reviews_rating) .
				 '<br />' . TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read .
				 '<br />' . TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes' .
				 '<br />' . TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format($rInfo->average_rating, 2) . '%' .
				 '</div></div>';
      }
        break;
    }
?>
  </div> <!-- EOF col-md-4 //-->  
</div>
<?php
  }
?>
    

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
