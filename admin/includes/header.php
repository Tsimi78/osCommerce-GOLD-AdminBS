<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- TOP MENU BAR STARTS //-->
<div class="row" style="background-color:#000;">
	<div class="col-xs-12 col-sm-7 col-md-8">

		<?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'oscommerce.png', 'osCommerce Online Merchant v' . tep_get_version()) . '</a>'; ?>

	</div>
	<div class="col-xs-12 col-sm-5 col-md-4 text-align">
	<!-- OSC LINKS //-->
		<div class="btn-group mt5">
			<button class="btn btn-link dropdown-toggle" data-toggle="dropdown">
				<span class="icon-white">osC Links <i class="fa fa-caret-down"></i></span>
			</button>
			<ul class="pull-right dropdown-menu" style="z-index:10000;">
				<?php echo '<li><a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '" class="headerLink">' . HEADER_TITLE_ADMINISTRATION . '</a></li>'; ?>
				<?php echo '<li><a href="' . tep_catalog_href_link() . '" target="blank" class="headerLink">' . HEADER_TITLE_ONLINE_CATALOG . '</a></li>'; ?>
				<?php echo '<li><a href="http://www.oscommerce.com" target="blank" class="headerLink">' . HEADER_TITLE_OSC_SITE . '</a></li>'; ?>
				<?php echo '<li><a href="http://forums.oscommerce.com/" target="blank" class="headerLink">' . HEADER_TITLE_FORUM . '</a></li>'; ?>
				<?php echo '<li><a href="http://addons.oscommerce.com" target="blank" class="headerLink">' . HEADER_TITLE_ADDONS . '</a></li>'; ?>
			</ul>
		</div>
	<!-- eof OSC LINKS //-->
	<!-- LOGIN/LOGOUT //-->
	<?php if (tep_session_is_registered('admin')) { ?>
		<div class="btn-group mt5">
			<button class="btn btn-link dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-user icon-white"></i> <span class="icon-white"><?php echo $admin['username']; ?></span> <b class="caret icon-white"></b>
			</button>
		  <ul class="pull-right dropdown-menu" style="z-index:10000;">
            <li>
              <a href="<?php echo tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admin['id'] . '&action=edit'); ?>"><i class="fa fa-fw fa-user"></i> Edit Profile</a>
            </li>
            <li class="divider"></li>
            <li>
              <a href="<?php echo tep_href_link(FILENAME_LOGIN, 'action=logoff'); ?>"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
            </li>
          </ul>
		</div>
	<?php 
	} else {
		echo '';
	}
	?>	
	<!-- eof LOGIN/LOGOUT //-->
	<!-- LANGUAGES //-->
	<?php if (sizeof($languages_array) > 1) { ?>
		<div class="btn-group hidden-xs">
			<div class="langheader">
				<?php echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get') . tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onchange="this.form.submit();"' ) . tep_hide_session_id() . '</form>'; ?>
			</div>
		</div>
	<?php } ?>
    <!-- eof LANGUAGES //-->
	</div>
</div> <!-- eof row //-->

<?php if (DISPLAY_SIDE_MENU != 'true') { ?>
	
<div class="row">
<div class="nav-wrapper">
<div id="affix-nav" data-spy="affix" data-offset-top="60" data-offset-bottom="200">
<!-- ADMIN NAV MENU STARTS //-->
<nav class="navbar navbar-default navbar-static-top" role="navigation">
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-inverse-collapse">
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
  </div>
  <div class="navbar-collapse collapse navbar-inverse-collapse">
<?php 
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
<?php
  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }
?>
<?php	
	
 echo '<ul class="nav navbar-nav">';
	foreach ($cl_box_groups as $groups) {  
		echo '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" title="' . $groups['heading'] . '">' . $groups['icon'] . '<span class="hidden-sm hidden-md"><strong>' . $groups['heading'] . '</strong></span><b class="caret"></b></a>' .
				'<ul class="dropdown-menu">';

		foreach ($groups['apps'] as $app) {
			echo '<li><a href="' . $app['link'] . '">' . $app['title'] . '</a></li>';
		}
        echo '</ul></li>';
    }
 echo '</ul>';	
}
?>
  </div>
</nav>
</div>
</div>
</div>

<?php } ?>