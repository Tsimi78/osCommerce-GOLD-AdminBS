<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License

  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('Error: Error 1', 'error');
  $messageStack->add('Error: Error 2', 'warning');
  if ($messageStack->size > 0) echo $messageStack->output();
*/

  class messageStack extends tableBlock {
    var $size = 0;

    function messageStack() {
      global $messageToStack;

      $this->errors = array();

      if (tep_session_is_registered('messageToStack')) {
        for ($i = 0, $n = sizeof($messageToStack); $i < $n; $i++) {
          $this->add($messageToStack[$i]['text'], $messageToStack[$i]['type']);
        }
        tep_session_unregister('messageToStack');
      }
    }

    function add($message, $type = 'error') {
      if ($type == 'error') {
        $this->errors[] = array('params' => 'class="alert alert-danger alert-dismissable break"', 'text' => '<i class="fa fa-times fa-lg mr10"></i>' . $message);
      } elseif ($type == 'warning') {
        $this->errors[] = array('params' => 'class="alert alert-warning alert-dismissable break"', 'text' => '<i class="fa fa-warning fa-lg mr10"></i>' . $message);
      } elseif ($type == 'success') {
        $this->errors[] = array('params' => 'class="alert alert-success alert-dismissable break"', 'text' => '<i class="fa fa-check fa-lg mr10"></i>' . $message);
      } else {
        $this->errors[] = array('params' => 'class="alert alert-info alert-dismissable break"', 'text' => '<i class="fa fa-info-circle fa-lg mr10"></i>' . $message);
      }

      $this->size++;
    }

    function add_session($message, $type = 'error') {
      global $messageToStack;

      if (!tep_session_is_registered('messageToStack')) {
        tep_session_register('messageToStack');
        $messageToStack = array();
      }

      $messageToStack[] = array('text' => $message, 'type' => $type);
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output() {
      $this->table_data_parameters = 'class="messageBox"';
      return $this->tableBlock($this->errors);
    }
  }
?>
