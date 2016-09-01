#!/usr/bin/python
import RPi.GPIO as GPIO
import time
import socket
import sys

time.sleep(40)

AIN1 = 6
AIN2 = 13
BIN1 = 19
BIN2 = 26

# GPIO SETUP
GPIO.setmode(GPIO.BCM)
GPIO.setup(AIN1,GPIO.OUT)
GPIO.setup(AIN2,GPIO.OUT)
GPIO.setup(BIN1,GPIO.OUT)
GPIO.setup(BIN2,GPIO.OUT)
GPIO.setup(21,GPIO.OUT)

# Assign PWM pins
g1 = GPIO.PWM(AIN2,80)
g2 = GPIO.PWM(BIN2,80)

# Start PWM pins low
GPIO.output(21,True)
g1.start(0)
g2.start(0)

# Create a socket
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# Bind the socket to the address given on the command line
#server_name = sys.argv[1]
#server_port = sys.argv[2]
server_address = ("192.168.42.1", 10000)
print >>sys.stderr, 'starting up on %s port %s' % server_address
sock.bind(server_address)
sock.listen(1)

while True:
#	GPIO.output(AIN1,True)
#	GPIO.output(BIN1,True)
	print >>sys.stderr, 'waiting for a connection'
	connection, client_address = sock.accept()
	try:
        	print >>sys.stderr, 'client connected:', client_address
		GPIO.output(AIN1,0)
		GPIO.output(BIN1,0)
		while 1:
			time.sleep(0.2)
			data = connection.recv(1)
			print >>sys.stderr, 'received "%s"' % data
			if data == '3':
#				GPIO.output(AIN2,False)
#				g1.ChangeDutyCycle(80)
#				GPIO.output(BIN2,True)
#				g2.ChangeDutyCycle(100)
				g1.start(30)
				g2.start(100)
				count = 0
				print "right"
#				time.sleep(1)
#				g1.start(100)
			elif data == '4':
#				GPIO.output(BIN2,False)
#				g2.ChangeDutyCycle(80)
#				GPIO.output(AIN2,True)
#				g2.ChangeDutyCycle(100)
				g1.start(100)
				g2.start(30)
				count = 0
				print "left"
#				time.sleep(1)
#				g2.start(100)
			elif data == '0':
				g1.start(0)
				g2.start(0)
#				GPIO.output(AIN1,0)
#				GPIO.output(BIN1,0)
				count = 0
				print "stop"
			elif data == '1':
				g1.start(100)
				g2.start(100)
				count = 0
				print "go"
			else:
				count += 1
				print >>sys.stderr, 'count "%s"' % count
			if count > 10:
				connection.close()
				break
	except KeyboardInterrupt:
		print "int!"
		connection.close()
		g1.stop()
		g2.stop()
		GPIO.cleanup()
		break
