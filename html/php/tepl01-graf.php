<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>RPi - Teploměr 01</title>
    <style>
        article, aside, figcaption, figure, footer, header, hgroup, menu, nav, section, label {
            display: block;
        }
        body {
            font-size: 100%;
            font-family: Arial, sans-serif;
            width: 800px;
            margin: 40px auto;
        }
    </style>
    <?php
        $servername = "localhost";
        $dbname = "test";
        $username = "webuser";
        $password = "Lamp2024";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Připojení k DB selhalo: " . $conn->connect_error);
        }

        $sql = "SELECT cas_zapisu, teplota FROM SensorData ORDER BY id DESC LIMIT 500;"; 

        if ($result = $conn->query($sql)) {
            $grafData = "[";
            $endData = "]";
            while ($item = $result->fetch_assoc()) {
                $tepl = $item["teplota"];
                $date = explode(" ",$item["cas_zapisu"])[0];
                $time = explode(" ",$item["cas_zapisu"])[1];
                $month = explode("0",explode("-",$date)[1])[1]-1; 
                $grafData = $grafData."{ x: new Date(".explode("-",$date)[0].", "."0".$month.", ".explode("-",$date)[2].", ".explode(":",$time)[0].", ".explode(":",$time)[1]."), y: ".$tepl."},";
                //{ x: new Date(2012, 01, 27), y: 60},
            }
            $grafData = $grafData.$endData;
            //echo $grafData;
            $result->free();
        }

        $conn->close();
    ?>
    <script type="text/javascript">
        window.onload = function () {
            var chart = new CanvasJS.Chart("chartContainer",
            {
                title:{
                    text: "Vývoj teploty učebna VT2"
                },
                axisX:{
                    title: "Datum",
                    gridThickness: 2
                },
                axisY: {
                    title: "Teplota °C"
                },
                data: [
                    {        
                        type: "area",
                        dataPoints: <?php echo $grafData; ?>
                    }
                ]
            });
            chart.render();
        }
    </script>
    <script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
  </head>
  <body>
    <div id="chartContainer" style="height: 400px; width: 100%;">
    </div>
  </body>
</html>