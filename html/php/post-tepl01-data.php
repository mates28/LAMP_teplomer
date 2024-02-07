<?php
date_default_timezone_set('Europe/Prague');
$date = date('Y-m-d', time());
$dm=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
$crontab = "save-date.php";
if (file_exists($crontab)) {
    include_once "$crontab";
}

$servername = "localhost";
$dbname = "teplomer";
$username = "webuser";
$password = "Lamp2024";

$api_key_value = "ae59110e-f718-4e33-9196-9e9eb291db51";

$api_key= $sensor = $location = $value1 = $value2 = $value3 = $value4 = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"]);
    if($api_key == $api_key_value) {
        $sensor = test_input($_POST["typ_sensoru"]);
        $location = test_input($_POST["umisteni"]);
        $value1 = test_input($_POST["teplota"]);
        $value2 = test_input($_POST["vlhkost"]);
        $value3 = test_input($_POST["atm_tlak"]);
	    $value4 = test_input($_POST["n_vyska"]);

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Připojení k DB selhalo: " . $conn->connect_error);
        }

        $sqlCreate = "CREATE TABLE `SensorData` (`id` INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL, `typ_sensoru` VARCHAR(30) NOT NULL, `umisteni` VARCHAR(30) NOT NULL, `teplota` INT(6) NOT NULL, `vlhkost` INT(6) NOT NULL, `atm_tlak` INT(6) NOT NULL, `n_vyska` INT(6) NOT NULL, `cas_zapisu` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP()) DEFAULT CHARACTER SET='utf8';";
        
        $sqlInsert = "INSERT INTO `SensorData` (`typ_sensoru`, `umisteni`, `teplota`, `vlhkost`, `atm_tlak`, `n_vyska`) VALUES ('" . $sensor . "', '" . $location . "', '" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "')";
        
        if ($conn->query("DESCRIBE `SensorData`;")) {
          $tbExists = true;
        } else {
          $tbExists = false;
        }
        
        if (!$tbExists) {
          $conn->query($sqlCreate);
          $conn->query($sqlInsert);
          echo "Tabulka byla vytvořena a nový záznam byl zapsán.";
        } else {
          $conn->query($sqlInsert);
          echo "Nový záznam byl zapsán.";
        }

        if ($date != $saveDate) {
            $CTcontent = "<?php\r\r\t\$saveDate = '".date('Y-m-d')."';\r\r?>";
            if ($handle = fopen($crontab, 'w')) {
                fwrite($handle, $CTcontent);
            }
            fclose($handle);
            
            $sql = "DELETE FROM SensorData WHERE cas_zapisu < date_sub(now(), interval ".$dm." day);";

            if ($conn->query($sql) === TRUE) {
                echo "Smazání záznamů starších ".$dm." dnů bylo provedeno.";
            }
            else {
                echo "Chyba: " . $sql . "<br>" . $conn->error;
            }

            $conn->close();
        } else {
            $conn->close();
        }
    }
    else {
        echo "Zadán nesprávný klíč API.";
    }

}
else {
    echo "HTTP POST neobsahuje žádná data.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
