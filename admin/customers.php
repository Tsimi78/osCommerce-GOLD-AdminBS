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

  $error = false;
  $processed = false;

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update':
        $customers_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);
        $customers_firstname = tep_db_prepare_input($HTTP_POST_VARS['customers_firstname']);
        $customers_lastname = tep_db_prepare_input($HTTP_POST_VARS['customers_lastname']);
        $customers_email_address = tep_db_prepare_input($HTTP_POST_VARS['customers_email_address']);
        $customers_telephone = tep_db_prepare_input($HTTP_POST_VARS['customers_telephone']);
        $customers_fax = tep_db_prepare_input($HTTP_POST_VARS['customers_fax']);
        $customers_newsletter = tep_db_prepare_input($HTTP_POST_VARS['customers_newsletter']);

        $customers_gender = tep_db_prepare_input($HTTP_POST_VARS['customers_gender']);
        $customers_dob = tep_db_prepare_input($HTTP_POST_VARS['customers_dob']);

        $default_address_id = tep_db_prepare_input($HTTP_POST_VARS['default_address_id']);
        $entry_street_address = tep_db_prepare_input($HTTP_POST_VARS['entry_street_address']);
        $entry_suburb = tep_db_prepare_input($HTTP_POST_VARS['entry_suburb']);
        $entry_postcode = tep_db_prepare_input($HTTP_POST_VARS['entry_postcode']);
        $entry_city = tep_db_prepare_input($HTTP_POST_VARS['entry_city']);
        $entry_country_id = tep_db_prepare_input($HTTP_POST_VARS['entry_country_id']);

        $entry_company = tep_db_prepare_input($HTTP_POST_VARS['entry_company']);
        $entry_state = tep_db_prepare_input($HTTP_POST_VARS['entry_state']);
        if (isset($HTTP_POST_VARS['entry_zone_id'])) $entry_zone_id = tep_db_prepare_input($HTTP_POST_VARS['entry_zone_id']);

        if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_firstname_error = true;
        } else {
          $entry_firstname_error = false;
        }

        if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
          $error = true;
          $entry_lastname_error = true;
        } else {
          $entry_lastname_error = false;
        }

        if (ACCOUNT_DOB == 'true') {
          if ((strlen($customers_dob) >= ENTRY_DOB_MIN_LENGTH) && ((is_numeric(tep_date_raw($customers_dob)) && @checkdate(substr(tep_date_raw($customers_dob), 4, 2), substr(tep_date_raw($customers_dob), 6, 2), substr(tep_date_raw($customers_dob), 0, 4))) || empty($customers_dob))) {
            $entry_date_of_birth_error = false;
          } else {
            $error = true;
            $entry_date_of_birth_error = true;
          }
        }

        if (strlen($customers_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
          $error = true;
          $entry_email_address_error = true;
        } else {
          $entry_email_address_error = false;
        }

        if (!tep_validate_email($customers_email_address)) {
          $error = true;
          $entry_email_address_check_error = true;
        } else {
          $entry_email_address_check_error = false;
        }

        if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
          $error = true;
          $entry_street_address_error = true;
        } else {
          $entry_street_address_error = false;
        }

        if (strlen($entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
          $error = true;
          $entry_post_code_error = true;
        } else {
          $entry_post_code_error = false;
        }

        if (strlen($entry_city) < ENTRY_CITY_MIN_LENGTH) {
          $error = true;
          $entry_city_error = true;
        } else {
          $entry_city_error = false;
        }

        if ($entry_country_id == false) {
          $error = true;
          $entry_country_error = true;
        } else {
          $entry_country_error = false;
        }

        if (ACCOUNT_STATE == 'true') {
          if ($entry_country_error == true) {
            $entry_state_error = true;
          } else {
            $zone_id = 0;
            $entry_state_error = false;
            $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$entry_country_id . "'");
            $check_value = tep_db_fetch_array($check_query);
            $entry_state_has_zones = ($check_value['total'] > 0);
            if ($entry_state_has_zones == true) {
              $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$entry_country_id . "' and zone_name = '" . tep_db_input($entry_state) . "'");
              if (tep_db_num_rows($zone_query) == 1) {
                $zone_values = tep_db_fetch_array($zone_query);
                $entry_zone_id = $zone_values['zone_id'];
              } else {
                $error = true;
                $entry_state_error = true;
              }
            } else {
              if (strlen($entry_state) < ENTRY_STATE_MIN_LENGTH) {
                $error = true;
                $entry_state_error = true;
              }
            }
         }
      }

      if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $error = true;
        $entry_telephone_error = true;
      } else {
        $entry_telephone_error = false;
      }

      $check_email = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($customers_email_address) . "' and customers_id != '" . (int)$customers_id . "'");
      if (tep_db_num_rows($check_email)) {
        $error = true;
        $entry_email_address_exists = true;
      } else {
        $entry_email_address_exists = false;
      }

      if ($error == false) {

        $sql_data_array = array('customers_firstname' => $customers_firstname,
                                'customers_lastname' => $customers_lastname,
                                'customers_email_address' => $customers_email_address,
                                'customers_telephone' => $customers_telephone,
                                'customers_fax' => $customers_fax,
                                'customers_newsletter' => $customers_newsletter);

        if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $customers_gender;
        if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($customers_dob);

        tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "'");

        tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customers_id . "'");

        if ($entry_zone_id > 0) $entry_state = '';

        $sql_data_array = array('entry_firstname' => $customers_firstname,
                                'entry_lastname' => $customers_lastname,
                                'entry_street_address' => $entry_street_address,
                                'entry_postcode' => $entry_postcode,
                                'entry_city' => $entry_city,
                                'entry_country_id' => $entry_country_id);

        if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $entry_company;
        if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $entry_suburb;

        if (ACCOUNT_STATE == 'true') {
          if ($entry_zone_id > 0) {
            $sql_data_array['entry_zone_id'] = $entry_zone_id;
            $sql_data_array['entry_state'] = '';
          } else {
            $sql_data_array['entry_zone_id'] = '0';
            $sql_data_array['entry_state'] = $entry_state;
          }
        }

        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$default_address_id . "'");

        tep_redirect(tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $customers_id));

        } else if ($error == true) {
          $cInfo = new objectInfo($HTTP_POST_VARS);
          $processed = true;
        }

        break;
      case 'deleteconfirm':
        $customers_id = tep_db_prepare_input($HTTP_GET_VARS['cID']);

        if (isset($HTTP_POST_VARS['delete_reviews']) && ($HTTP_POST_VARS['delete_reviews'] == 'on')) {
          $reviews_query = tep_db_query("select reviews_id from " . TABLE_REVIEWS . " where customers_id = '" . (int)$customers_id . "'");
          while ($reviews = tep_db_fetch_array($reviews_query)) {
            tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int)$reviews['reviews_id'] . "'");
          }

          tep_db_query("delete from " . TABLE_REVIEWS . " where customers_id = '" . (int)$customers_id . "'");
        } else {
          tep_db_query("update " . TABLE_REVIEWS . " set customers_id = null where customers_id = '" . (int)$customers_id . "'");
        }

        tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customers_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customers_id . "'");
        tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where customer_id = '" . (int)$customers_id . "'");

        tep_redirect(tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action'))));
        break;
      default:
        $customers_query = tep_db_query("select c.customers_id, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, a.entry_company, a.entry_street_address, a.entry_suburb, a.entry_postcode, a.entry_city, a.entry_state, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_default_address_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . (int)$HTTP_GET_VARS['cID'] . "'");
        $customers = tep_db_fetch_array($customers_query);
        $cInfo = new objectInfo($customers);
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');

  if ($action == 'edit' || $action == 'update') {
?>
<script type="text/javascript"><!--

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  var customers_firstname = document.customers.customers_firstname.value;
  var customers_lastname = document.customers.customers_lastname.value;
<?php if (ACCOUNT_COMPANY == 'true') echo 'var entry_company = document.customers.entry_company.value;' . "\n"; ?>
<?php if (ACCOUNT_DOB == 'true') echo 'var customers_dob = document.customers.customers_dob.value;' . "\n"; ?>
  var customers_email_address = document.customers.customers_email_address.value;
  var entry_street_address = document.customers.entry_street_address.value;
  var entry_postcode = document.customers.entry_postcode.value;
  var entry_city = document.customers.entry_city.value;
  var customers_telephone = document.customers.customers_telephone.value;

<?php if (ACCOUNT_GENDER == 'true') { ?>
  if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
  } else {
    error_message = error_message + "<?php echo JS_GENDER; ?>";
    error = 1;
  }
<?php } ?>

  if (customers_firstname.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_FIRST_NAME; ?>";
    error = 1;
  }

  if (customers_lastname.length < <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_LAST_NAME; ?>";
    error = 1;
  }

<?php if (ACCOUNT_DOB == 'true') { ?>
  if (customers_dob.length < <?php echo ENTRY_DOB_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_DOB; ?>";
    error = 1;
  }
<?php } ?>

  if (customers_email_address.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_EMAIL_ADDRESS; ?>";
    error = 1;
  }

  if (entry_street_address.length < <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_ADDRESS; ?>";
    error = 1;
  }

  if (entry_postcode.length < <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_POST_CODE; ?>";
    error = 1;
  }

  if (entry_city.length < <?php echo ENTRY_CITY_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_CITY; ?>";
    error = 1;
  }

<?php
  if (ACCOUNT_STATE == 'true') {
?>
  if (document.customers.elements['entry_state'].type != "hidden") {
    if (document.customers.entry_state.value.length < <?php echo ENTRY_STATE_MIN_LENGTH; ?>) {
       error_message = error_message + "<?php echo JS_STATE; ?>";
       error = 1;
    }
  }
<?php
  }
?>

  if (document.customers.elements['entry_country_id'].type != "hidden") {
    if (document.customers.entry_country_id.value == 0) {
      error_message = error_message + "<?php echo JS_COUNTRY; ?>";
      error = 1;
    }
  }

  if (customers_telephone.length < <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo JS_TELEPHONE; ?>";
    error = 1;
  }

  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
//--></script>
<?php
  }
?>

    
<?php
  if ($action == 'edit' || $action == 'update') {
    $newsletter_array = array(array('id' => '1', 'text' => ENTRY_NEWSLETTER_YES),
                              array('id' => '0', 'text' => ENTRY_NEWSLETTER_NO));
?>
      
       <h3><?php echo HEADING_TITLE; ?></h3>
	   
<!-- Registration Section Starts -->
<section class="registration-area">
	<div class="row">
		<div class="col-xs-12">
		
	  <!-- Registration Block Starts -->
		<div class="panel panel-smart">
		
	<div class="row">
		<div class="col-xs-12 col-sm-4 col-sm-offset-8">
			<span class="inputRequirement pull-right text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
		</div>
	</div>
	<div class="clearfix"></div>
		
		<?php echo tep_draw_form('customers', FILENAME_CUSTOMERS, tep_get_all_get_params(array('action')) . 'action=update', 'post', 'class="form-horizontal" onsubmit="return check_form();"') . tep_draw_hidden_field('default_address_id', $cInfo->customers_default_address_id); ?>	
		
<!-- PERSONAL AREA START //-->		
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo CATEGORY_PERSONAL; ?></h3>
		</div>
		<div class="panel-body">
		
<?php
    if (ACCOUNT_GENDER == 'true') {
?>
        <div class="form-group has-feedback">
			<label class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_GENDER . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">		
<?php
    if ($error == true) {
      if ($entry_gender_error == true) {
        echo '<label class="radio-inline">' . tep_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . ' ' . MALE . '</label>
			  <label class="radio-inline">' . tep_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . ' ' . FEMALE . '</label>' . ENTRY_GENDER_ERROR;  
	  } else {
        echo ($cInfo->customers_gender == 'm') ? MALE : FEMALE;
        echo tep_draw_hidden_field('customers_gender');
      }
    } else {
      echo '<label class="radio-inline">' . tep_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender) . ' ' . MALE . '</label>
			<label class="radio-inline">' . tep_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender) . ' ' . FEMALE . '</label>';
	}
	echo FORM_REQUIRED_INPUT;
?>  
			</div>  
	    </div> 			 
<?php
    }
?>
        <div class="form-group has-feedback">
			<label for="inputFirstName" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_FIRST_NAME . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
  if ($error == true) {
    if ($entry_firstname_error == true) {
      echo tep_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"') . ' ' . ENTRY_FIRST_NAME_ERROR;
    } else {
      echo $cInfo->customers_firstname . tep_draw_hidden_field('customers_firstname');
    }
  } else {
    echo tep_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'required aria-required="true" id="inputFirstName" maxlength="32"', true);
  }
  echo FORM_REQUIRED_INPUT;
?>
			</div>
	    </div> 
	   
        <div class="form-group has-feedback">
			<label for="inputLastName" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_LAST_NAME . ':'; ?></label>
            <div class="col-xs-12 col-sm-9">
<?php
  if ($error == true) {
    if ($entry_lastname_error == true) {
      echo tep_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"') . ' ' . ENTRY_LAST_NAME_ERROR;
    } else {
      echo $cInfo->customers_lastname . tep_draw_hidden_field('customers_lastname');
    }
  } else {
    echo tep_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'required aria-required="true" id="inputLastName" maxlength="32"', true);
  }
  echo FORM_REQUIRED_INPUT;
?>        
           </div>
        </div>
         
<?php
    if (ACCOUNT_DOB == 'true') {
?>
        <div class="form-group has-feedback">
			<label for="customers_dob" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_DATE_OF_BIRTH . ':'; ?></label>
            <div class="col-xs-12 col-sm-9">
<?php
    if ($error == true) {
      if ($entry_date_of_birth_error == true) {
        echo tep_draw_input_field('customers_dob', tep_date_short($cInfo->customers_dob), 'maxlength="10"') . ' ' . ENTRY_DATE_OF_BIRTH_ERROR;
      } else {
        echo $cInfo->customers_dob . tep_draw_hidden_field('customers_dob');
      }
    } else {
      echo tep_draw_input_field('customers_dob', tep_date_short($cInfo->customers_dob), 'required aria-required="true" id="customers_dob" maxlength="10"', true);
    }
	echo FORM_REQUIRED_INPUT;
?>
              <script type="text/javascript">$('#customers_dob').datepicker({dateFormat: '<?php echo JQUERY_DATEPICKER_FORMAT; ?>', changeMonth: true, changeYear: true, yearRange: '-100:+0'});</script>
            </div>
            </div>
          
<?php
    }
?>
        <div class="form-group has-feedback">
			<label for="inputEmail" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_EMAIL_ADDRESS . ':'; ?></label>
		    <div class="col-xs-12 col-sm-9">
<?php
  if ($error == true) {
    if ($entry_email_address_error == true) {
      echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . ' ' . ENTRY_EMAIL_ADDRESS_ERROR;
    } elseif ($entry_email_address_check_error == true) {
      echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . ' ' . ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
    } elseif ($entry_email_address_exists == true) {
      echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"') . ' ' . ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
    } else {
      echo $customers_email_address . tep_draw_hidden_field('customers_email_address');
    }
  } else {
    echo tep_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'required aria-required="true" id="inputEmail"', true);
  }
  echo FORM_REQUIRED_INPUT;
?>             
              </div>
            </div>
<!-- PERSONAL AREA END //-->	
<!-- COMPANY AREA START //-->			
<?php
    if (ACCOUNT_COMPANY == 'true') {
?>

 <h3 class="panel-heading inner"><?php echo CATEGORY_COMPANY; ?></h3>
  
		<div class="form-group">
			<label for="inputCompany" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_COMPANY . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
           
<?php
    if ($error == true) {
      echo $cInfo->entry_company . tep_draw_hidden_field('entry_company');
    } else {
      echo tep_draw_input_field('entry_company', $cInfo->entry_company, 'id="inputCompany" maxlength="32"');
    }
?>
           </div>
        </div>

<?php
    }
?>
<!-- COMPANY AREA END //-->
<!-- ADDRESS AREA START //-->
  <h3 class="panel-heading inner"><?php echo CATEGORY_ADDRESS; ?></h3>
  
        <div class="form-group has-feedback">
			<label for="inputStreet" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_STREET_ADDRESS . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">

<?php
  if ($error == true) {
    if ($entry_street_address_error == true) {
      echo tep_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"') . ' ' . ENTRY_STREET_ADDRESS_ERROR;
    } else {
      echo $cInfo->entry_street_address . tep_draw_hidden_field('entry_street_address');
    }
  } else {
    echo tep_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'required aria-required="true" id="inputStreet" maxlength="64"', true);
  }
  echo FORM_REQUIRED_INPUT;
?>
			</div>
        </div>
<?php
    if (ACCOUNT_SUBURB == 'true') {
?>
        <div class="form-group">
			<label for="inputSuburb" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_SUBURB . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
    if ($error == true) {
      echo $cInfo->entry_suburb . tep_draw_hidden_field('entry_suburb');
    } else {
      echo tep_draw_input_field('entry_suburb', $cInfo->entry_suburb, 'id="inputSuburb" maxlength="32"');
    }
?>
            </div>
        </div>
         
<?php
    }
?>
		<div class="form-group has-feedback">
			<label for="inputZip" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_POST_CODE . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">

<?php
  if ($error == true) {
    if ($entry_post_code_error == true) {
      echo tep_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"') . ' ' . ENTRY_POST_CODE_ERROR;
    } else {
      echo $cInfo->entry_postcode . tep_draw_hidden_field('entry_postcode');
    }
  } else {
    echo tep_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'required aria-required="true" id="inputZip" maxlength="8"', true);
  }
  echo FORM_REQUIRED_INPUT;
?>
           </div>
        </div>
		<div class="form-group has-feedback">
			<label for="inputCity" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_CITY . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
  if ($error == true) {
    if ($entry_city_error == true) {
      echo tep_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"') . ' ' . ENTRY_CITY_ERROR;
    } else {
      echo $cInfo->entry_city . tep_draw_hidden_field('entry_city');
    }
  } else {
    echo tep_draw_input_field('entry_city', $cInfo->entry_city, 'required aria-required="true" id="inputCity" maxlength="32"', true);
  }
  echo FORM_REQUIRED_INPUT;
?>
            </div>
        </div>
         
<?php
    if (ACCOUNT_STATE == 'true') {
?>
		<div class="form-group has-feedback">
			<label for="inputState" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_STATE . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
    $entry_state = tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state);
    if ($error == true) {
      if ($entry_state_error == true) {
        if ($entry_state_has_zones == true) {
          $zones_array = array();
          $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . tep_db_input($cInfo->entry_country_id) . "' order by zone_name");
          while ($zones_values = tep_db_fetch_array($zones_query)) {
            $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
          }
          echo tep_draw_pull_down_menu('entry_state', $zones_array) . '&nbsp;' . ENTRY_STATE_ERROR;
        } else {
          echo tep_draw_input_field('entry_state', tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state)) . ' ' . ENTRY_STATE_ERROR;
        }
      } else {
        echo $entry_state . tep_draw_hidden_field('entry_zone_id') . tep_draw_hidden_field('entry_state');
      }
    } else {
      echo tep_draw_input_field('entry_state', tep_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state), 'id="inputState"');
    }
   echo FORM_REQUIRED_INPUT;
?>
            </div>
        </div>
<?php
    }
?>
		<div class="form-group has-feedback">
			<label for="inputCountry" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_COUNTRY . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
  if ($error == true) {
    if ($entry_country_error == true) {
      echo tep_draw_pull_down_menu('entry_country_id', tep_get_countries(), $cInfo->entry_country_id) . ' ' . ENTRY_COUNTRY_ERROR;
    } else {
      echo tep_get_country_name($cInfo->entry_country_id) . tep_draw_hidden_field('entry_country_id');
    }
  } else {
    echo tep_draw_pull_down_menu('entry_country_id', tep_get_countries(), $cInfo->entry_country_id);
  }
  echo FORM_REQUIRED_INPUT;
?>
               </div>
              </div>
<!-- ADDRESS AREA END //-->     
<!-- CONTACT AREA START //-->
  <h3 class="panel-heading inner"><?php echo CATEGORY_CONTACT; ?></h3>
  
		<div class="form-group has-feedback"> 
			<label for="inputTelephone" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_TELEPHONE_NUMBER . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
  if ($error == true) {
    if ($entry_telephone_error == true) {
      echo tep_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"') . ' ' . ENTRY_TELEPHONE_NUMBER_ERROR;
    } else {
      echo $cInfo->customers_telephone . tep_draw_hidden_field('customers_telephone');
    }
  } else {
    echo tep_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"', true);
  }
  echo FORM_REQUIRED_INPUT;
?>
			</div>
        </div>
		<div class="form-group">
			<label for="inputFax" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_FAX_NUMBER . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
  if ($processed == true) {
    echo $cInfo->customers_fax . tep_draw_hidden_field('customers_fax');
  } else {
    echo tep_draw_input_field('customers_fax', $cInfo->customers_fax, 'id="inputFax" maxlength="32"');
  }
?>
             </div>
        </div>
<!-- CONTACT AREA END //-->
<!-- OPTIONS AREA START //-->
  <h3 class="panel-heading inner"><?php echo CATEGORY_OPTIONS; ?></h3>
  
		<div class="form-group">
			<label for="inputNewsletter" class="control-label col-xs-12 col-sm-3"><?php echo ENTRY_NEWSLETTER . ':'; ?></label>
			<div class="col-xs-12 col-sm-9">
<?php
  if ($processed == true) {
    if ($cInfo->customers_newsletter == '1') {
      echo ENTRY_NEWSLETTER_YES;
    } else {
      echo ENTRY_NEWSLETTER_NO;
    }
    echo tep_draw_hidden_field('customers_newsletter');
  } else {
    echo tep_draw_pull_down_menu('customers_newsletter', $newsletter_array, (($cInfo->customers_newsletter == '1') ? '1' : '0'));
  }
?>
            </div>
        </div>
<!-- OPTIONS AREA END //-->			  
   <div class="row">
    <div class="text-center">
    <?php echo tep_draw_button(IMAGE_SAVE, 'fa fa-floppy-o', null, 'primary', null, 'btn-success') . '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('action')))); ?>
     </div>
     </div>
					</div> <!-- .panel-body -->
				</div> <!-- .panel-smart -->
			<!-- Registration Block Ends -->	
			
			</div> <!-- .col-sm-12 -->
		</div> <!-- .row -->
	</section>
<!-- Registration Section Ends -->	 
	 </form>
<?php
  } else {
?>
     <div class="row">
        <div class="col-md-8 mb10">
			<h3><?php echo HEADING_TITLE; ?></h3>
	    </div>
        <div class="col-md-4 mb10">
          <?php 
				echo tep_draw_form('search', FILENAME_CUSTOMERS, '', 'get') . 
				'<label>' . HEADING_TITLE_SEARCH . '</label>' . 
				tep_draw_input_field('search') . tep_hide_session_id(); 
			?>
		  </form>
        </div>    
     </div>    

  <div class="row">
	<div class="col-md-8">  
      <table class="table table-hover table-condensed table-responsive table-striped">
	   <thead>
          <tr>
            <th><?php echo TABLE_HEADING_LASTNAME; ?></th>
            <th><?php echo TABLE_HEADING_FIRSTNAME; ?></th>
            <th><?php echo TABLE_HEADING_ACCOUNT_CREATED; ?></th>
            <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
          </tr>
	   </thead>
       <tbody>	   
<?php
    $search = '';
    if (isset($HTTP_GET_VARS['search']) && tep_not_null($HTTP_GET_VARS['search'])) {
      $keywords = tep_db_input(tep_db_prepare_input($HTTP_GET_VARS['search']));
      $search = "where c.customers_lastname like '%" . $keywords . "%' or c.customers_firstname like '%" . $keywords . "%' or c.customers_email_address like '%" . $keywords . "%'";
    }
    $customers_query_raw = "select c.customers_id, c.customers_lastname, c.customers_firstname, c.customers_email_address, a.entry_country_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id " . $search . " order by c.customers_lastname, c.customers_firstname";
    $customers_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $customers_query_raw, $customers_query_numrows);
    $customers_query = tep_db_query($customers_query_raw);
    while ($customers = tep_db_fetch_array($customers_query)) {
      $info_query = tep_db_query("select customers_info_date_account_created as date_account_created, customers_info_date_account_last_modified as date_account_last_modified, customers_info_date_of_last_logon as date_last_logon, customers_info_number_of_logons as number_of_logons from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . $customers['customers_id'] . "'");
      $info = tep_db_fetch_array($info_query);

      if ((!isset($HTTP_GET_VARS['cID']) || (isset($HTTP_GET_VARS['cID']) && ($HTTP_GET_VARS['cID'] == $customers['customers_id']))) && !isset($cInfo)) {
        $country_query = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$customers['entry_country_id'] . "'");
        $country = tep_db_fetch_array($country_query);

        $reviews_query = tep_db_query("select count(*) as number_of_reviews from " . TABLE_REVIEWS . " where customers_id = '" . (int)$customers['customers_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);

        $customer_info = array_merge($country, $info, $reviews);

        $cInfo_array = array_merge($customers, $customer_info);
        $cInfo = new objectInfo($cInfo_array);
      }

      if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id)) {
        echo ' <tr class="info" onclick="document.location.href=\'' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit') . '\'">';
      } else {
        echo ' <tr onclick="document.location.href=\'' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '\'">';
      }
?>
                <td><?php echo $customers['customers_lastname']; ?></td>
                <td><?php echo $customers['customers_firstname']; ?></td>
                <td><?php echo tep_date_short($info['date_account_created']); ?></td>
                <td class="text-right"><?php if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id)) { echo '<i class="fa fa-chevron-circle-right fa-lg mouse"></i>'; } else { echo '<a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '"><i class="fa fa-info-circle fa-lg" title="'. IMAGE_ICON_INFO .'"></i></a>'; } ?></td>
              </tr>
<?php
    }
?>
            </tbody>
           </table>

                    
                    
<?php
    if (isset($HTTP_GET_VARS['search']) && tep_not_null($HTTP_GET_VARS['search'])) {
?>
	<div class="row">              
		<div class="col-xs-12 text-right"><?php echo tep_draw_button(IMAGE_RESET, 'fa fa-refresh', tep_href_link(FILENAME_CUSTOMERS)); ?></div>
	</div>	
<?php
    }
?>
		<div class="row mt20">          
			<div class="col-sm-6 mb10">
				<?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?>
			</div>
			<div class="col-sm-6 mb15 pull-right text-right"> 
				<?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?>
			</div>
		</div>
	</div> <!-- EOF col-md-8 //-->        
	<div class="col-md-4">			  
<?php
  switch ($action) {
    case 'confirm':
		echo '<div class="panel panel-danger">
				<div class="panel-heading"><span class="panel-title">' . TEXT_INFO_HEADING_DELETE_CUSTOMER . '</span></div>';
		
		echo '<div class="panel-body">' .	
				tep_draw_form('customers', FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=deleteconfirm') .
				'<br />' . TEXT_DELETE_INTRO . '<br /><br /><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>';
				
      if (isset($cInfo->number_of_reviews) && ($cInfo->number_of_reviews) > 0) echo '<br />' . tep_draw_checkbox_field('delete_reviews', 'on', true) . ' ' . sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews);
		echo '<br /><br /><div class="text-center">' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', null, 'primary', null, 'btn-danger') . 
									  '&nbsp;' . tep_draw_button(IMAGE_CANCEL, 'fa fa-ban icon-red', tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id)) . '</div>' .
			 '</div></div>';
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
		echo '<div class="panel panel-default">
				<div class="panel-heading"><span class="panel-title"><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong></span></div>';
		
		echo '<div class="panel-body">' .
			 '<br /><div class="text-center">' . tep_draw_button(IMAGE_EDIT, 'fa fa-pencil', tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit'), 'primary', null, 'btn-warning btn-sm') . 
									  '&nbsp;' . tep_draw_button(IMAGE_DELETE, 'fa fa-trash-o', tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=confirm'), 'primary', null, 'btn-danger btn-sm') . 
									  '&nbsp;' . tep_draw_button(IMAGE_ORDERS, 'fa fa-inbox', tep_href_link(FILENAME_ORDERS, 'cID=' . $cInfo->customers_id), 'primary', null, 'btn-info btn-sm') . 
									  '&nbsp;' . tep_draw_button(IMAGE_EMAIL, 'fa fa-envelope-o', tep_href_link(FILENAME_MAIL, 'customer=' . $cInfo->customers_email_address), 'primary', null, 'btn-default btn-sm') . '</div>' .
			 '<br />' . TEXT_DATE_ACCOUNT_CREATED . ' ' . tep_date_short($cInfo->date_account_created) .
			 '<br />' . TEXT_DATE_ACCOUNT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->date_account_last_modified) .
			 '<br />' . TEXT_INFO_DATE_LAST_LOGON . ' '  . tep_date_short($cInfo->date_last_logon) .
			 '<br />' . TEXT_INFO_NUMBER_OF_LOGONS . ' ' . $cInfo->number_of_logons .
			 '<br />' . TEXT_INFO_COUNTRY . ' ' . $cInfo->countries_name .
			 '<br />' . TEXT_INFO_NUMBER_OF_REVIEWS . ' ' . $cInfo->number_of_reviews;
			 '</div></div>';
      }
      break;
  }
?>
  </div> <!-- EOF col-md-4 //--> 
 </div> <!-- EOF row //--> 
<?php
  }
?>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>