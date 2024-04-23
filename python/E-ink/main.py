import machine, time, network, json, socket
from machine import Pin
import EPD2in9 as EPD

led = Pin("LED", Pin.OUT)
epd = EPD.EPD_2in9_Landscape()

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

while True:
    if wlan.isconnected():
        led.value(False)
        print("Dotazuji se serveru na data: {}".format(url))
        data = GetData(url)
        print("Data:", data)
        #
        print("Teplota:", data['teplota'], "°C")
        print("Vlhkost:", data['vlhkost'], "%")
        print("Atm. tlak:", data['atm_tlak'], "hPa")
        print("Nadm. výška:", data['nadm_vyska'], "m")
        # zobrazeni dat na e-ink
        epd.init()
        epd.Clear(0xff)
        epd.delay_ms(200)
        epd.fill(0x00)
        epd.text("Teplomer 01 - data", 20, 20, 0xff)
        # ...
        epd.display(epd.buffer)
        epd.delay_ms(200)
        epd.sleep()
        #
        time.sleep(delay)
