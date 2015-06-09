<?php
/*
  $Id$ osC Admin BS by Tsimi

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/
?>
		</div> <!-- eof col-xs-12 //-->
	</div> <!-- eof row //-->
</div> <!-- eof container-fluid //-->

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

<!-- Back-to-top Button-->
<a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="" data-toggle="tooltip" data-placement="left"><span class="glyphicon glyphicon-chevron-up"></span></a>  
<script type="text/javascript">
$(document).ready(function(){$(window).scroll(function () {if ($(this).scrollTop() > 150) {$('#back-to-top').fadeIn();} else {$('#back-to-top').fadeOut();}});$('#back-to-top').click(function () {$('#back-to-top').tooltip('hide');$('body,html').animate({scrollTop: 0}, 400); return false; }); $('#back-to-top').tooltip('show');});
</script>
<!-- End Back-to-top Button-->

</body>
</html>

