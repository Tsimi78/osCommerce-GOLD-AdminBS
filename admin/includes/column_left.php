<?php
/*
  $Id$ Admin BS

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  if (tep_session_is_registered('admin')) {
  
  $cl_box_groups = array();

    if ($dir = @dir(DIR_FS_ADMIN . 'includes/boxes')) {
      $files = array();

      while ($file = $dir->read()) {
        if (!is_dir($dir->path . '/' . $file)) {
          if (substr($file, strrpos($file, '.')) == '.php') {
            $files[] = $file;
          }
        }
      }

      $dir->close();

      natcasesort($files);

      foreach ( $files as $file ) {
        if ( file_exists(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file) ) {
          include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file);
        }

        include($dir->path . '/' . $file);
      }
    }

    function tep_sort_admin_boxes($a, $b) {
      return strcasecmp($a['heading'], $b['heading']);
    }

    usort($cl_box_groups, 'tep_sort_admin_boxes');

    function tep_sort_admin_boxes_links($a, $b) {
      return strcasecmp($a['title'], $b['title']);
    }

    foreach ( $cl_box_groups as &$group ) {
      usort($group['apps'], 'tep_sort_admin_boxes_links');
    }
?>
  
   <div class="col-lg-2 sidebar-offcanvas" id="sidebar" role="navigation" style="padding:0px;">
	 <div class="panel-group-side" id="accordion">
		
		<div class="panel-side panel-default-side">
			<div class="panel-heading">
			<?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' .
					   '<h3 class="panel-title"><i class="fa fa-dashboard fa-fw"></i> Dashboard</h3>' .
					   '</a>'; ?>	 
			</div>
		</div>
    
<?php
    foreach ($cl_box_groups as $groups) {
	
	echo '<div class="panel-side panel-default-side">';
    
	$counter++;

    echo '<div class="panel-heading accordion-toggle collapsed" href="#toggle' . $counter  .'"  data-toggle="collapse" data-parent="#accordion">' . 
		 '<h3 class="panel-title"><a href="#">' . $groups['icon'] . $groups['heading'] . '</a></h3>' . 
		 '</div>' .
		 '<div id="toggle' . $counter  .'" class="panel-collapse collapse">';
		 
    foreach ($groups['apps'] as $app) {
      echo '<div class="list-group-side"><a class="list-group-item-side" href="' . $app['link'] . '">' . $app['title'] . '</a></div>';
      }
		 echo '</div></div>';
    }
?>

		</div> <!-- eof panel-group-side //-->
    </div> <!-- eof col-lg-2 sidebar-offcanvas //-->


<?php
  }
?>
