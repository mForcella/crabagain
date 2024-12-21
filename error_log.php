<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height,  initial-scale=1.0, user-scalable=no, user-scalable=0"/>
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>
  </head>

  <body>
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
  </body>
</html>