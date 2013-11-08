#!/usr/bin/python
# -*- coding: utf-8 -*-
import MySQLdb
from PIL import Image, ImageDraw, ImageFont
from random import randint

def rectangle( startx, starty, endx, endy, f ):
	draw.polygon((startx, starty, startx, endy, endx, endy, endx, starty), fill=f)

def hbar(index, value, mx, monitor = 0, orientation = 0, h = 3, i = 0,bkg=(217, 83, 79),color=(52, 52, 52),text="",autototal=0,title=""):

	if(rnd==True):
		bkg=list(bkg)
		bkg[0]=bkg[0]+randint(0, 5)-randint(0, 5)
		bkg[1]=bkg[1]+randint(0, 5)-randint(0, 5)
		bkg[2]=bkg[2]+randint(0, 5)-randint(0, 5)
		bkg=tuple(bkg)

	tt=""
	if(title!=""):
		tt = title

	offsetx = 1920 * monitor + 20
	width = 1920
	height = 1080
	offsety = 20

	if (monitor == 2):
		width = 1440
		height = 900
		offsety = 140

	realw = (width - 40)
	realh = (height - 40)
	total = 31
	if(autototal!=0):
		total = autototal
	size = realh / total

	if ( orientation == 0):
		startx = offsetx
		endx = offsetx + realw * value / mx
		starty = offsety + (index * size)
		endy = starty + size
		fontx = startx+10
		fonty = starty+(size/4)
	else:
		startx = offsetx + realw
		endx = startx - realw * value / mx
		starty = offsety + (index * size)
		endy = starty + size
		fontx = startx-size+8
		if((index+1)>9):
			fontx = startx+10-size
		fonty = starty+(size/3)

	t = str(index+1)
	if(text!=""):
		t = text
	fontPath = "F:/2012/wamp/www/test/github/Q/wallpaper/helvneue.ttf"
	helv  =  ImageFont.truetype ( fontPath, size / 3 )
	fx,fy = helv.getsize(t)


	rectangle(startx, starty, endx, endy,bkg)
	if ( orientation != 0):
		fontx = fontx + 60 - fx

	#draw.text ( (fontx-1,fonty-1), t, font=helv, fill=color )
	#draw.text ( (fontx+1,fonty-1), t, font=helv, fill=color )
	#draw.text ( (fontx+1,fonty+1), t, font=helv, fill=color )
	#draw.text ( (fontx-1,fonty+1), t, font=helv, fill=color )
	draw.text ( (fontx,fonty), t, font=helv, fill=(60,60,60))#bkg )

	if(tt!=""):
		
		fx,fy = helv.getsize(tt)
		fonty = offsety + realh/2
		if(orientation==0):
			fontx = startx + (startx+realw/2) - (fx/2)
		else:
			fontx = startx - realw + (realw/2) - (fx/2)
		draw.text ( (fontx,fonty), tt, font=helv, fill=(60,60,60) )

def vbar(index, value, mx, monitor = 0, orientation = 0, h = 3, i = 0,bkg=(217, 83, 79),color=(52, 52, 52),text="",autototal=0,title=""):

	if(rnd==True):
		bkg=list(bkg)
		bkg[0]=bkg[0]+randint(0, 5)-randint(0, 5)
		bkg[1]=bkg[1]+randint(0, 5)-randint(0, 5)
		bkg[2]=bkg[2]+randint(0, 5)-randint(0, 5)
		bkg=tuple(bkg)

	tt=""
	if(title!=""):
		tt = title
	offsetx = 1920 * monitor
	width = 1920
	height = 1080
	offsety = 20 + 10 * i
	if (monitor == 2):
		width = 1440
		height = 900
		offsety = 140
	realh = (height-(30+h*10))/h
	realw = (width - 40)
	total = 31
	if(autototal!=0):
		total = autototal
	size = realw / total
	sectory = offsety + (realh * (i + 1)) #+ size/2 * i
	if ( orientation == 0):
		startx = offsetx + 20 + (index * size)
		endx = startx + size
		starty = sectory
		endy = starty - (realh * value / mx)
		fontx = startx+10
		if((index+1)>9):
			fontx = startx+2
		fonty = starty - size
	else:
		startx = offsetx + 20 + (index * size)
		endx = startx + size
		starty = sectory - realh
		endy = starty + (realh * value / mx)
		fontx = startx+10
		if((index+1)>9):
			fontx = startx+2
		fonty = starty - size

	t = str(index+1)
	if(text!=""):
		t = text
		size = size/3
	fontPath = "F:/2012/wamp/www/test/github/Q/wallpaper/helvneue.ttf"
	helv  =  ImageFont.truetype ( fontPath, size - 8 )
	fx,fy = helv.getsize(t)

	rectangle(startx, starty, endx, endy,bkg)

	if(text!=""):
		img_txt = Image.new('RGBA', (fx,fy*2))
		draw_txt = ImageDraw.Draw(img_txt)
		draw_txt.text((1,1), t, font=helv, fill=color)
		draw_txt.text((-1,-1), t, font=helv, fill=color)
		draw_txt.text((1,-1), t, font=helv, fill=color)
		draw_txt.text((-1,1), t, font=helv, fill=color)
		draw_txt.text((0,0), t, font=helv, fill=bkg)
		w = img_txt.rotate(90, expand=1)
		sx, sy = img_txt.size
		if ( orientation == 0):
			im.paste(w, (fontx+(size/2)+(fy/2),starty-fx-10), w)
		else:
			im.paste(w, (fontx+(size/2)+(fy/2),starty+10), w)

	if(tt!=""):
		
		fx,fy = helv.getsize(tt)
		fontx = offsetx + (realw/2) - (fx/2)
		fonty = sectory - (realh/2) - (fy/2)
		draw.text ( (fontx,fonty), tt, font=helv, fill=(60,60,60))

def creategraph(results,monitor=1,orientation=0,horiz=1,h=3,idd=0,automax=0,title="",autototal=0,bkg=(217, 83, 79),color=(52, 52, 52),m=False):
	i = 0
	tt=""
	if(title!=""):
		tt = title
	if(automax==0):
		mx = 16 * 60
	else:
		mx=0
		for row in results :
			if(row[1]>mx):
				mx=row[1]
	total = 0
	if(autototal!=0):
		for row in results :
			total = total+1

	for row in results :
		t = ""
		if(isinstance(row[0], str)):
			t=row[0]

		if(isinstance(row[0], str)!=True):
			i=row[0]-1

		if(horiz==1):
			hbar( i, row[1], mx,monitor,orientation,h,idd,text=t,autototal=total,title=tt,color=color,bkg=bkg)
		else:
			v=row[1]
			if(m==True):
				v=6-row[1]
				mx=5*row[1]
			vbar( i, v, mx,monitor,orientation,h,idd,text=t,autototal=total,title=tt,color=color,bkg=bkg)

		if(isinstance(row[0], str)):
			i=i+1

def plot(query,monitor=1,orientation=0,horiz=1,h=3,idd=0,automax=0,autototal=0,title="",bkg=(217, 83, 79),color=(52, 52, 52),m=False):
	cur.execute(query)
	creategraph(cur.fetchall(),monitor,orientation,horiz,h,idd,automax,autototal=autototal,title=title,color=color,bkg=bkg,m=m)




db = MySQLdb.connect(host="localhost", # your host, usually localhost
                     user="", # your username
                      passwd="", # your password
                      db="") # name of the data base
cur = db.cursor() 


bkg=(217, 83, 79)
rndC=True
if(rndC==True):
	red = randint(20,205)#randint(20,255)
	green = randint(20,205)#randint(20,255)
	blue = randint(20,205)#randint(20,255)
	bkg=(red,green,blue)
color=(52, 52, 52)
rnd=False
im = Image.new('RGB', (5280, 1172), color) 

logo = Image.open("F:/2012/wamp/www/test/github/Q/wallpaper/logo.png")
x, y = logo.size#im.width/2, im.height/2

sx = 1920 / 2 - x / 2
sy = 1080 / 2 - y / 2 - 137

draw = ImageDraw.Draw(im) 

im.paste(logo, (sx,sy,x+sx,y+sy))



#################################################################################
#
# CURRENT MONTH
#
# right monitor, first row
query="SELECT DAY(datetime) AS monthday, SUM(duration) AS duration_total FROM logs WHERE YEAR(datetime) = YEAR(CURDATE()) AND MONTH(datetime) = MONTH(CURDATE()) GROUP BY YEAR(datetime), MONTH(datetime), DAY(datetime)"
plot(query,monitor=1,orientation=0,horiz=0,idd=0,h=3,title="Este mes",bkg=bkg,color=color)


'''
#################################################################################
#
# PREVIOUS MONTH
#
# right monitor, second row
query="SELECT DAY(datetime) AS day, duration FROM logs WHERE MONTH(datetime) = MONTH(CURDATE())-1 GROUP BY day ORDER BY datetime DESC"
plot(query,monitor=1,orientation=1,horiz=0,idd=1,h=3)
'''


#################################################################################
#
# ALL TIMES
#
# right monitor, third row
query="SELECT DAY(datetime) AS day, AVG(duration) FROM logs GROUP BY day ORDER BY datetime DESC"
plot(query,monitor=1,orientation=1,horiz=0,idd=1,h=3,title="Actividad media",bkg=bkg,color=color)

#################################################################################
#
# ALL TIMES
#
# right monitor, third row
query="SELECT DAY(datetime) AS day, mood FROM logs GROUP BY day ORDER BY datetime DESC"
plot(query,monitor=1,orientation=0,horiz=0,idd=2,h=3,automax=1,title="Estado de animo",bkg=bkg,color=color,m=True)


'''
# left monitor, first row
query="SELECT tags.tag AS tag, COUNT(tag_id) AS tag_total FROM tags INNER JOIN log_tags ON tags.ID = log_tags.tag_id GROUP BY tag ORDER BY tag_total DESC LIMIT 15"
#plot(query,monitor=2,orientation=1,horiz=0,idd=0,automax=1,autototal=1,h=1)
plot(query,monitor=1,orientation=0,horiz=0,idd=2,automax=1,autototal=1,h=3)
'''

'''
# left monitor, second row
query="SELECT tags.tag AS tag, COUNT(tag_id) AS tag_total FROM tags INNER JOIN log_tags ON tags.ID = log_tags.tag_id GROUP BY tag ORDER BY tag_total DESC LIMIT 15"
plot(query,monitor=2,orientation=0,horiz=0,idd=1,automax=1,autototal=1,h=3)
'''
# left monitor, all
query="SELECT tags.tag AS tag, COUNT(tag_id) AS tag_total FROM tags INNER JOIN log_tags ON tags.ID = log_tags.tag_id GROUP BY tag ORDER BY tag_total DESC, tag ASC LIMIT 10"
plot(query,monitor=2,orientation=1,horiz=1,automax=1,autototal=1,h=1,idd=0,title="Tags mas usados",bkg=bkg,color=color)




im.save('F:/2012/wamp/www/test/github/Q/wallpaper/py_wall.jpg', quality=95)


# SET WALLPAPER
import ctypes
SPI_SETDESKWALLPAPER = 20 
ctypes.windll.user32.SystemParametersInfoA(SPI_SETDESKWALLPAPER, 0, "F:/2012/wamp/www/test/github/Q/wallpaper/py_wall.jpg" , 0)

'''

The last parameter, fWinIni, "specifies whether the user profile is to be updated". The flags are SPIF_UPDATEINIFILE == 1 and SPIF_SENDCHANGE == 2. The latter broadcasts a WM_SETTINGCHANGE message. Try using fWinIni == 3

'''

#uncomment to debug
#raw_input("Press Enter to continue...")