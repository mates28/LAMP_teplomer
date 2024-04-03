import machine, requests, network, time, random
from machine import Pin
from pimoroni import RGBLED
from pimoroni import Button
from picographics import PicoGraphics, DISPLAY_PICO_DISPLAY_2

display = PicoGraphics(display=DISPLAY_PICO_DISPLAY_2, rotate=0)

display.set_backlight(0.8)
display.set_font("bitmap8")

WIDTH, HEIGHT = display.get_bounds()

led = RGBLED(6, 7, 8)

button_a = Button(12)
button_b = Button(13)
button_x = Button(14)
button_y = Button(15)

ssid = "SPSELIT-IoT"
password = "SPSIoT2023"
url = "http://172.16.17.168/php/post-tdisplayl01-data.php"
api_key = "ae59110e-f718-4e33-9196-9e9eb291db51"
sensor_name = "RPi PICO Tdisplayloměr 02"
sensor_location = "Učebna VT1"

delay = 60

wlan = network.WLAN(network.STA_IF)
wlan.active(True)
wlan.connect(ssid,password)

RED = display.create_pen(209, 34, 41)
GREEN = display.create_pen(0, 121, 64)
BLUE = display.create_pen(116, 215, 238)
WHITE = display.create_pen(255, 255, 255)
BLACK = display.create_pen(0, 0, 0)

wait = 10
while wait > 0:
    if wlan.status() < 0 or wlan.status() >= 3:
        break
    wait -= 1
    led.set_rgb(0, 80, 0)
    print('Čekám na připojení...')
    time.sleep(1.5)
    led.set_rgb(0, 0, 0)
    time.sleep(1.5)
 
if wlan.status() != 3:
    raise RuntimeError('Nelze se připojit!')
else:
    led.set_rgb(0, 0, 0)
    print('Připojeno!')
    print('IP: ', wlan.ifconfig()[0])
    time.sleep(2)
    
def clear():
    display.set_pen(BLACK)
    display.clear()
    display.update()

def get_data():
    temp = random.randint(20, 30)
    hum = random.randint(40, 100)
    press = random.randint(950, 1050)
    alt = 287
    return [temp, hum, press, alt]

while True:
    print("Status: "+str(wlan.status()))
    if wlan.isconnected():
        clear()
        led.set_rgb(0, 80, 0)
        data = get_data()
        #
        display.set_pen(WHITE)
        display.text("Teplomer 02 - data", 20, 30, 320, 3)
        display.text("Teplota: "+str(data[0])+" stupnu C", 60, 80, 320, 2)
        display.text("Vlhkost: "+str(data[1])+" %", 60, 120, 320, 2)
        display.text("Atm. tlak: "+str(data[2])+" hPa", 60, 160, 320, 2)
        display.text("Nadm. vyska: "+str(data[3])+" m", 60, 200, 320, 2)
        display.update()
        time.sleep(0.5)
        led.set_rgb(0, 0, 0)
        #
        print(data)
        headers = {'Content-Type': 'application/x-www-form-urlencoded'}
        d = "api_key="+api_key+"&typ_sensoru="+sensor_name+"&umisteni="+sensor_location+"&tdisplaylota="+str(data[0])+"&vlhkost="+str(data[1])+"&atm_tlak="+str(data[2])+"&n_vyska="+str(data[3])+"&end="+str(0)
        res = requests.post(url, data=str(d), headers=headers)
        print("Data byla odeslána: ", res.text)
        time.sleep(delay)
