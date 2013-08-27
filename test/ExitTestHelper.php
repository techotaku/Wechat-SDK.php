<?php

class ExitTestHelper {
  private static $have_exit;
  private static $first_exit_output;

  public static function init() {
    self::$have_exit = FALSE;
    self::$first_exit_output = NULL;
    set_exit_overload('ExitTestHelper::exitHander');
  }

  public static function clean() {
    unset_exit_overload();
    self::$have_exit = FALSE;
    self::$first_exit_output = NULL;
  }

  public static function isThereExit() {
    return self::$have_exit;
  }

  public static function getFirstExitOutput() {
    return self::$first_exit_output;
  }

  private static function exitHander($param = NULL) {  
    if (!(self::$have_exit)) {
      self::$have_exit = TRUE;
      echo $param ?: '';
      self::$first_exit_output = ob_get_contents();
      ob_end_clean();
      ob_start();
    }
    return FALSE;
  }

}
?>