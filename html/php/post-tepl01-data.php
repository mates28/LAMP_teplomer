<?php
date_default_timezone_set('Europe/Prague');
$date = date('Y-m-d', time());
$dm=cal_days_in_month(CAL_GREGORIAN,date('m'),date('Y'));
$crontab = "save-date.php";
if (file_exists($crontab)) {
    include_once "$crontab";
}

$servername = "localhost";
$dbname = "teplomer_01_data";
$username = "writer";
$password = "Wrt20SQL20";

$api_key_value = "tPmAT5Ab3j7F9";

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

        $sql = "INSERT INTO SensorData (typ_sensoru, umisteni, teplota, vlhkost, atm_tlak, n_vyska)
        VALUES ('" . $sensor . "', '" . $location . "', '" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "')";

        if ($conn->query($sql) === TRUE) {
            echo "Nový záznam byl vytvořen.";
        } else {
            echo "Chyba: " . $sql . "<br>" . $conn->error;
        }
        
        if ($date != $saveDate) {
            $CTcontent = "<?php\r\r\t\$saveDate = '".date("Y-m-d")."';\r\r?>";
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
