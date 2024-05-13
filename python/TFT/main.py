import machine, time, network, json, socket
from machine import Pin
from pimoroni import RGBLED
from pimoroni import Button
from picographics import PicoGraphics, DISPLAY_PICO_DISPLAY_2

led = Pin("LED", Pin.OUT)

display = PicoGraphics(display=DISPLAY_PICO_DISPLAY_2, rotate=0)

display.set_backlight(0.8)
display.set_font("bitmap8")

WIDTH, HEIGHT = display.get_bounds()

rgbled = RGBLED(6, 7, 8)
rgbled.set_rgb(0, 0, 0)

button_a = Button(12)
button_b = Button(13)
button_x = Button(14)
button_y = Button(15)

RED = display.create_pen(209, 34, 41)
GREEN = display.create_pen(0, 121, 64)
BLUE = display.create_pen(116, 215, 238)
WHITE = display.create_pen(255, 255, 255)
BLACK = display.create_pen(0, 0, 0)

delay = 60 #600

print("Připojuji se k serveru")

wlan = network.WLAN(network.STA_IF)
wlan.config(pm=wlan.PM_NONE)
wlan.active(True)
wlan.connect("Tepl01", "tepl01pass")

def GetData(url):
    scheme, _, host, path = url.split('/', 3)
    s = socket.socket()
    try:
        s.connect((host, 80))
        request=bytes('GET /%s HTTP/1.1\r\nHost: %s\r\n\r\n' % (path, host), 'utf8')     
        #print("Načítám /%s z adresy %s\n" % (path, host))
        s.send(request)
        while True:
            data = "{"+str(s.recv(500), 'utf8').split("{")[1]
            return json.loads(data)
    finally:
        s.close()

wait = 10
while wait > 0:
    if wlan.status() < 0 or wlan.status() >= 3:
        break
    wait -= 1
    led.value(True)
    print("Čekám na připojení...")
    time.sleep(1)
    led.value(False)
    time.sleep(1)
    
if wlan.status() != 3:
    print("Nelze se pripojit!")
else:
    print("Připojeno k serveru: {}".format(wlan.ifconfig()))
    gateway = wlan.ifconfig()[2]
    url = "http://{}/data.json".format(gateway)
    
def clear():
    display.set_pen(BLACK)
    display.clear()
    display.update()

while True:
    if wlan.isconnected():
        clear()
        led.value(False)
        print("Dotazuji se serveru na data: {}".format(url))
        data = GetData(url)
        print("Data:", data)
        #
        print("Teplota:", data['teplota'], "°C")
        print("Vlhkost:", data['vlhkost'], "%")
        print("Atm. tlak:", data['atm_tlak'], "hPa")
        print("Nadm. výška:", data['nadm_vyska'], "m")
        # zobrazeni dat na TFT
        display.set_pen(WHITE)
        display.text("Teplomer 05 - data", 20, 20, 300, 3)
        display.text("Teplota: "+str(data['teplota'])+" °C", 50, 60, 300, 2)
        display.text("Vlhkost: "+str(data['vlhkost'])+" %", 50, 90, 300, 2)
        display.text("Atm. tlak: "+str(data['atm_tlak'])+" hPa", 50, 120, 300, 2)
        display.text("Nadm. vyska: "+str(data['nadm_vyska'])+" m", 50, 150, 300, 2)
        # ...
        display.update()
        #
        time.sleep(delay)

