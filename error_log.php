<?php

  exec('tail '.dirname(__FILE__).'/error_log', $error_logs);
  foreach($error_logs as $error_log) {
    echo "<br />".$error_log;
  }

  exec('tail '.dirname(__FILE__).'/scripts/error_log', $error_logs);
  foreach($error_logs as $error_log) {
    echo "<br />".$error_log;
  }

?>