<!DOCTYPE html>
<html lang="cs">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>RPi - Teploměr 01</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css" >
    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="../fonts/line-icons.css">
    <!-- Slicknav -->
    <link rel="stylesheet" type="text/css" href="../css/slicknav.css">
    <!-- Off Canvas Menu -->
    <link rel="stylesheet" type="text/css" href="../css/menu_sideslide.css">
    <!-- Color Switcher -->
    <link rel="stylesheet" type="text/css" href="../css/vegas.min.css">
    <!-- Animate -->
    <link rel="stylesheet" type="text/css" href="../css/animate.css">
    <!-- Main Style -->
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <!-- Responsive Style -->
    <link rel="stylesheet" type="text/css" href="../css/responsive.css">
    <!-- Table Style -->
    <link rel="stylesheet" type="text/css" href="../css/table.css">

  </head>
  <body>
  <?php
  $servername = "localhost";

  $dbname = "teplomer_01_data";
  $username = "writer";
  $password = "Wrt20SQL20";

  $conn = new mysqli($servername, $username, $password, $dbname);
  if ($conn->connect_error) {
      die("Připojení k DB selhalo: " . $conn->connect_error);
  }

  $sql = "SELECT id, typ_sensoru, umisteni, teplota, vlhkost, atm_tlak, n_vyska, cas_zapisu FROM SensorData ORDER BY id DESC";

  echo '<table id="racetimes" cellspacing="5" cellpadding="5">
        <tr id="firstrow">
          <td>ID</td>
          <td>Typ sensoru</td>
          <td>Umístění</td>
          <td>Teplota (°C)</td>
          <td>Vlhkost (%)</td>
          <td>Atm. tlak (hPa)</td>
          <td>Nadm. výška (m)</td>
          <td>Čas zápisu</td>
        </tr>';

  if ($result = $conn->query($sql)) {
      while ($row = $result->fetch_assoc()) {
          $row_id = $row["id"];
          $row_sensor = $row["typ_sensoru"];
          $row_location = $row["umisteni"];
          $row_value1 = $row["teplota"];
          $row_value2 = $row["vlhkost"];
          $row_value3 = $row["atm_tlak"];
          $row_value4 = $row["n_vyska"];
          $row_reading_time = $row["cas_zapisu"];

          echo '<tr>
                  <td>' . $row_id . '</td>
                  <td>' . $row_sensor . '</td>
                  <td>' . $row_location . '</td>
                  <td>' . $row_value1 . '</td>
                  <td>' . $row_value2 . '</td>
                  <td>' . $row_value3 . '</td>
                  <td>' . $row_value4 . '</td>
                  <td>' . $row_reading_time . '</td>
                </tr>';
      }
      $result->free();
  }

  $conn->close();
  ?>
  </table>
  </body>
</html>
