<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  if (tep_session_is_registered('admin')) {
  ?>
  
   <div class="col-lg-2 sidebar-offcanvas" id="sidebar" role="navigation">
	<div class="panel-group-side" id="accordion">

  <div class="panel-side panel-default-side">
   	<div class="panel-heading alpha_panel" style="background: #f8f8f8;">
     <?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">'; ?>  
	  <h3 class="panel-title">
       <i class="fa fa-dashboard fa-fw"></i><span class="hidden-xs"> Dashboard</span>
      </h3>
     <?php echo '</a>'; ?>	 
    </div>
  </div>
  
  <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" style="background: #f8f8f8;">
     <h3 class="panel-title">
	  <a href="#">
          <i class="fa fa-bar-chart-o fa-fw"></i><span class="hidden-xs"> Catalog</span>
      </a>
	  </h3>
	 </div>
    <div id="collapseOne" class="panel-collapse collapse">
      <div class="list-group-side">
	    <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CATEGORIES, '', 'NONSSL') . '">' . SIDE_CATEGORIES . '</a>'; ?>
	    <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_MANUFACTURERS, '', 'NONSSL') . '">' . SIDE_MANUFACTURERS . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, '', 'NONSSL') . '">' . SIDE_PRODUCTS_ATTRIBUTES . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_PRODUCTS_EXPECTED, '', 'NONSSL') . '">' . SIDE_PRODUCTS_EXPECTED . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_REVIEWS, '', 'NONSSL') . '">' . SIDE_REVIEWS . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_SPECIALS, '', 'NONSSL') . '">' . SIDE_SPECIALS . '</a>'; ?>	  
	  </div>
    </div>
  </div>
<!-- EOF collapseOne//-->
 
   <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" style="background: #f8f8f8;">
     <h3 class="panel-title">
        <a href="#">
		 <i class="fa fa-table fa-fw"></i><span class="hidden-xs"> Configuration</span>
		</a>
		</h3>
	 </div>
    <div id="collapseTwo" class="panel-collapse collapse">
      <div class="list-group-side">
		<?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_ADMINISTRATORS, '', 'NONSSL') . '">' . SIDE_ADMINISTRATORS . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=11', 'NONSSL') . '">' . SIDE_CACHE . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=5', 'NONSSL') . '"> ' . SIDE_CUSTOMER_DETAILS . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=13', 'NONSSL') . '"> ' . SIDE_DOWNLOAD . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=12', 'NONSSL') . '"> ' . SIDE_EMAIL_OPTIONS . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=14', 'NONSSL') . '"> ' . SIDE_GZIP . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=4', 'NONSSL') . '"> ' . SIDE_IMAGES . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=10', 'NONSSL') . '"> ' . SIDE_LOGGING . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=3', 'NONSSL') . '"> ' . SIDE_MAX_VALUES . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=2', 'NONSSL') . '"> ' . SIDE_MIN_VALUES . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=1', 'NONSSL') . '"> ' . SIDE_MY_STORE . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=8', 'NONSSL') . '"> ' . SIDE_PRODUCT_LISTING . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=15', 'NONSSL') . '"> ' . SIDE_SESSIONS . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=7', 'NONSSL') . '"> ' . SIDE_SHIPPING_PACKING . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=9', 'NONSSL') . '"> ' . SIDE_STOCK . '</a>'; ?>
        <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_STORE_LOGO, '', 'NONSSL') . '"> ' . SIDE_STORE_LOGO . '</a>'; ?>
      </div>
    </div>
</div>
<!-- EOF collapseTwo//-->
  
  <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" style="background: #f8f8f8;">
     <h3 class="panel-title">
	  <a href="#">
          <i class="fa fa-edit fa-fw"></i><span class="hidden-xs"> Customers</span>
      </a>
	  </h3>
	 </div>
    <div id="collapseThree" class="panel-collapse collapse">
      <div class="list-group-side">
	    <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_CUSTOMERS, '', 'NONSSL') . '">' . SIDE_CUSTOMERS . '</a>'; ?>
	    <?php echo '<a class="list-group-item-side" href="' . tep_href_link(FILENAME_ORDERS, '', 'NONSSL') . '">' . SIDE_ORDERS . '</a>'; ?>	  
	  </div>
    </div>
  </div>
<!-- EOF collapseThree//-->

  <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseEight" style="background: #f8f8f8;">
	  <h3 class="panel-title">
       <a href="#">
		<i class="fa fa-wrench fa-fw"></i><span class="hidden-xs"> Localization</span>
       </a>
	  </h3>
    </div>
    <div id="collapseEight" class="panel-collapse collapse">
      <div class="list-group-side">
        <a class="list-group-item-side" href="panels-wells.html">Panels and Wells</a>
        <a class="list-group-item-side" href="#">Buttons</a>
        <a class="list-group-item-side" href="#">Notifications</a>
        <a class="list-group-item-side" href="#">Typography</a>
        <a class="list-group-item-side" href="#">Grid</a>
      </div>
    </div>
</div>
<!-- EOF collapseEight//-->
  
  <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseNine" style="background: #f8f8f8;">
      <h3 class="panel-title">
       <a href="#"> 
		<i class="fa fa-sitemap fa-fw"></i><span class="hidden-xs"> Locations / Taxes</span>
       </a>
	  </h3> 
    </div>
    <div id="collapseNine" class="panel-collapse collapse">
      <div class="list-group-side">
        <a class="list-group-item-side" href="#">Second Level Item</a>
        <a class="list-group-item-side" href="#">Second Level Item</a>
        <a class="list-group-item-side" href="#">Third Level Item</a>
      </div>
    </div>
  </div>
<!-- EOF collapseNine//-->
 
  <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" style="background: #f8f8f8;">
      <h3 class="panel-title">
        <a href="#">
		<i class="fa fa-files-o fa-fw"></i><span class="hidden-xs"> Modules</span>
       </a>
	  </h3> 
    </div>
    <div id="collapseFour" class="panel-collapse collapse">
      <div class="list-group-side">
        <a class="list-group-item-side" href="blank.html">Blank Page</a>
        <a class="list-group-item-side" href="login.html">Login Page</a>
      </div>
    </div>
  </div>
<!-- EOF collapseFour//-->

  <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFive" style="background: #f8f8f8;">
      <h3 class="panel-title">
        <a href="#">
		<i class="fa fa-files-o fa-fw"></i><span class="hidden-xs"> Reports</span>
       </a>
	  </h3> 
    </div>
    <div id="collapseFive" class="panel-collapse collapse">
      <div class="list-group-side">
        <a class="list-group-item-side" href="#">Blank Page</a>
        <a class="list-group-item-side" href="#">Login Page</a>
      </div>
    </div>
  </div>
<!-- EOF collapseFive//-->

  <div class="panel-side panel-default-side">
    <div class="panel-heading accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseSix" style="background: #f8f8f8;">
      <h3 class="panel-title">
        <a href="#">
		<i class="fa fa-files-o fa-fw"></i><span class="hidden-xs"> Tools</span>
       </a>
	  </h3> 
    </div>
    <div id="collapseSix" class="panel-collapse collapse">
      <div class="list-group-side">
        <a class="list-group-item-side" href="#">Blank Page</a>
        <a class="list-group-item-side" href="#">Login Page</a>
      </div>
    </div>
  </div>
<!-- EOF collapseSix //-->

      </div> <!-- /.panel-group #accordion -->
        </div>

<?php
  }
?>
