#!/usr/bin/python
# coding: utf-8

# Example using a character LCD plate.
import math
import time

import Adafruit_CharLCD as LCD


# Initialize the LCD using the pins
lcd = LCD.Adafruit_CharLCDPlate()

log_file = '/home/pi/temperatur/temperatur/temp.log'
content_variable = open(log_file, 'r')
file_lines = content_variable.readlines()
content_variable.close()
last_line = file_lines[len(file_lines)-1]

temperatur = last_line.split(',', 1)
display = temperatur[0] + "\n" + temperatur[1] + '\ºC'

print last_line
lcd.message(display)
