<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $type = (isset($HTTP_GET_VARS['type']) ? $HTTP_GET_VARS['type'] : '');

  $banner_extension = tep_banner_image_extension();

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

  $banner_query = tep_db_query("select banners_title from " . TABLE_BANNERS . " where banners_id = '" . (int)$HTTP_GET_VARS['bID'] . "'");
  $banner = tep_db_fetch_array($banner_query);

  $years_array = array();
  $years_query = tep_db_query("select distinct year(banners_history_date) as banner_year from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$HTTP_GET_VARS['bID'] . "'");
  while ($years = tep_db_fetch_array($years_query)) {
    $years_array[] = array('id' => $years['banner_year'],
                           'text' => $years['banner_year']);
  }

  $months_array = array();
  for ($i=1; $i<13; $i++) {
    $months_array[] = array('id' => $i,
                            'text' => strftime('%B', mktime(0,0,0,$i)));
  }

  $type_array = array(array('id' => 'daily',
                            'text' => STATISTICS_TYPE_DAILY),
                      array('id' => 'monthly',
                            'text' => STATISTICS_TYPE_MONTHLY),
                      array('id' => 'yearly',
                            'text' => STATISTICS_TYPE_YEARLY));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

	<?php echo tep_draw_form('year', FILENAME_BANNER_STATISTICS, '', 'get'); ?>
		<h3><?php echo HEADING_TITLE; ?></h3>
<div class="row">
	<div class="col-sm-4">
		<?php echo TITLE_TYPE . ' ' . tep_draw_pull_down_menu('type', $type_array, (tep_not_null($type) ? $type : 'daily'), 'onchange="this.form.submit();"'); ?>
		<noscript><input type="submit" value="GO"></noscript>
 <?php
  switch ($type) {
    case 'yearly': break;
    case 'monthly':
      echo TITLE_YEAR . ' ' . tep_draw_pull_down_menu('year', $years_array, (isset($HTTP_GET_VARS['year']) ? $HTTP_GET_VARS['year'] : date('Y')), 'onchange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
    default:
    case 'daily':
      echo TITLE_MONTH . ' ' . tep_draw_pull_down_menu('month', $months_array, (isset($HTTP_GET_VARS['month']) ? $HTTP_GET_VARS['month'] : date('n')), 'onchange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>' . TITLE_YEAR . ' ' . tep_draw_pull_down_menu('year', $years_array, (isset($HTTP_GET_VARS['year']) ? $HTTP_GET_VARS['year'] : date('Y')), 'onchange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
  }
?>
		<?php echo tep_draw_hidden_field('page', $HTTP_GET_VARS['page']) . tep_draw_hidden_field('bID', $HTTP_GET_VARS['bID']) . tep_hide_session_id(); ?>
	</div>	
</form>
	
<?php
  if (function_exists('imagecreate') && ($dir_ok == true) && tep_not_null($banner_extension)) {
?>
	<div class="col-sm-8">
<?php 	
    $banner_id = (int)$HTTP_GET_VARS['bID'];

    switch ($type) {
      case 'yearly':
        include(DIR_WS_INCLUDES . 'graphs/banner_yearly.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);
        break;
      case 'monthly':
        include(DIR_WS_INCLUDES . 'graphs/banner_monthly.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);
        break;
      default:
      case 'daily':
        include(DIR_WS_INCLUDES . 'graphs/banner_daily.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner_id . '.' . $banner_extension);
        break;
    }
?>
	</div>

	<div class="col-xs-12">  
      <table class="table table-hover table-condensed table-responsive table-striped">
	   <thead>
          <tr>
             <th><?php echo TABLE_HEADING_SOURCE; ?></th>
             <th class="text-center"><?php echo TABLE_HEADING_VIEWS; ?></th>
             <th class="text-center"><?php echo TABLE_HEADING_CLICKS; ?></th>
           </tr>
		<thead>
		<tbody>
<?php
    for ($i=0, $n=sizeof($stats); $i<$n; $i++) {
      echo '<tr>' .
           '  <td>' . $stats[$i][0] . '</td>' .
           '  <td class="text-center">' . number_format($stats[$i][1]) . '</td>' .
           '  <td class="text-center">' . number_format($stats[$i][2]) . '</td>' .
           '</tr>';
    }
?>
         </tbody>
      </table>
	</div>  
 
<?php
  } else {
?>
	<div class="col-sm-8">	  
<?php  
  include(DIR_WS_FUNCTIONS . 'html_graphs.php');

    switch ($type) {
      case 'yearly':
        echo tep_banner_graph_yearly($HTTP_GET_VARS['bID']);
        break;
      case 'monthly':
        echo tep_banner_graph_monthly($HTTP_GET_VARS['bID']);
        break;
      default:
      case 'daily':
        echo tep_banner_graph_daily($HTTP_GET_VARS['bID']);
        break;
    }
	?>
	</div>
<?php	
  }
?>
	<div class="text-center">
		<?php echo tep_draw_button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $HTTP_GET_VARS['bID'])); ?>
	</div>      
</div> 
     
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
