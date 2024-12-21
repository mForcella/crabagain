<?php

  // establish database connection
  include_once('config/db_config.php');
  $db = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

  // get queries
  $queries = [];
  $sql = "SELECT * FROM sql_query";
  $result = $db->query($sql);
  if ($result) {
    while($row = $result->fetch_assoc()) {
      $query = $row;
      $query['login'] = "";
      $query['character'] = "";
      // get login email and character name from IDs
      $sql = "SELECT email FROM login WHERE id = ".$row['login_id'];
      $result_1 = $db->query($sql);
      if ($result_1) {
        while($row_1 = $result_1->fetch_assoc()) {
          $query['login'] = $row_1['email'];
        }
      }
      if (isset($row['character_id']) && $row['character_id'] != NULL) {
        $sql = "SELECT character_name FROM user WHERE id = ".$row['character_id'];
        $result_2 = $db->query($sql);
        if ($result_2) {
          while($row_2 = $result_2->fetch_assoc()) {
            $query['character'] = $row_2['character_name'];
          }
        }
      }
      array_push($queries, $query);
    }
  }



?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height,  initial-scale=1.0, user-scalable=no, user-scalable=0"/>
    <meta name="robots" content="noindex">
    <title>SQL Queries</title>
    <link rel="icon" type="image/png" href="/assets/image/favicon-pentacle.ico"/>
    <link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.min.css">

    <style type="text/css">

    </style>

  </head>

  <body style="margin: 50px;">

      <table class="table table-striped" id="table">
        <thead>
          <tr>
            <th>Query</th>
            <th>Login</th>
            <th>Character</th>
            <th>Type</th>
            <th>Source</th>
            <th>Date/Time</th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach($queries as $query) {

              echo 
              "<tr class='table-row'>
                <td>".$query['query']."</td>
                <td>".$query['login']."</td>
                <td>".$query['character']."</td>
                <td>".$query['type']."</td>
                <td>".$query['source']."</td>
                <td>".$query['created_at']."</td>
              </tr>";

            }
          ?>
        </tbody>
    </table>

  </body>

  <script src="/assets/bootstrap/js/bootstrap.min.js"></script>
  <script src="/assets/jquery/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.datatables.net/2.1.5/js/dataTables.min.js"></script>

  <script type="text/javascript">

    // $('#table').DataTable();
    new DataTable('#table', {
        order: [[5, 'desc']],
        pageLength: 100
    });


  </script>

</html>