import machine, requests, network, time, random
from machine import Pin

led = Pin("LED", Pin.OUT)

ssid = "SPSELIT-IoT"
password = "SPSIoT2023"
url = "http://172.16.17.78/php/post-tepl01-data.php"
api_key = "ae59110e-f718-4e33-9196-9e9eb291db51"
sensor_name = "RPi PICO Teploměr 06"
sensor_location = "Učebna VT1"

delay = 600

wlan = network.WLAN(network.STA_IF)
wlan.active(True)
wlan.connect(ssid,password)

wait = 10
while wait > 0:
    if wlan.status() < 0 or wlan.status() >= 3:
        break
    wait -= 1
    led.value(True)
    print('Čekám na připojení...')
    time.sleep(1.5)
    led.value(False)
    time.sleep(1.5)
 
if wlan.status() != 3:
    raise RuntimeError('Nelze se připojit!')
else:
    led.value(False)
    print('Připojeno!')
    print('IP: ', wlan.ifconfig()[0])
    time.sleep(2)

def get_data():
    temp = random.randint(20, 30)
    hum = random.randint(40, 100)
    press = random.randint(950, 1050)
    alt = 287
    return [temp, hum, press, alt]

while True:
    print("Status: "+str(wlan.status()))
    if wlan.isconnected():
        led.value(False)
        data = get_data()
        print(data)
        headers = {'Content-Type': 'application/x-www-form-urlencoded'}
        d = "api_key="+api_key+"&typ_sensoru="+sensor_name+"&umisteni="+sensor_location+"&teplota="+str(data[0])+"&vlhkost="+str(data[1])+"&atm_tlak="+str(data[2])+"&n_vyska="+str(data[3])+"&end="+str(0)
        res = requests.post(url, data=str(d), headers=headers)
        print("Data byla odeslána: ", res.text)
        time.sleep(delay)
