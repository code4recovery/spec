import requests
import json

def datatojson():
	#This script is run as a simple Flask app on a free Heroku plan to allow meeting data in a Google Sheet to connect to the Meeting Guide app
	#For full Flask app see https://github.com/pugetsoundaa/jsonfeed

	#Google Sheet for Puget Sound AA CSO: https://docs.google.com/spreadsheets/d/1fLxXxKFIiuPJOuTTNzAn1S0rmgjRQhFxqDNZabACIcI/edit?usp=sharing
	#Google Sheet ID from publicly shared link above
	SPREADSHEET_ID = '1fLxXxKFIiuPJOuTTNzAn1S0rmgjRQhFxqDNZabACIcI'
	#Google Sheet JSON Feed URL - must have published the spreadsheet to the web (different than sharing, File->Publish to the web...)
	SPREADSHEET_FEED_URL = "https://spreadsheets.google.com/feeds/list/"+SPREADSHEET_ID+"/1/public/values?alt=json"

	#Requests JSON and then parses it into a Python object
	json_request = requests.get(SPREADSHEET_FEED_URL)
	json_string = json_request.text
	parsed_json = json.loads(json_string)

	#Preprocessing before Meeting Guide format loop
	meetings = parsed_json["feed"]["entry"]
	meetings_num = len(meetings)
	output = []
	output.append('[')

	#Loop to create JSON string in Meeting Guide format
	for x in range (0, meetings_num):
		output.append('{"name":"')
		#Have to add \ before each forward slash
		output.append(meetings[x]["gsx$name"]["$t"].replace("/","\/"))
		output.append('","slug":"')
		output.append(meetings[x]["gsx$slug"]["$t"])
		output.append('","day":[')
		output.append(dayArray(meetings[x]))
		output.append('],"time":"')
		output.append(timeFormatted(meetings[x]))
		output.append('","location":"')
		#Have to add \ before each forward slash
		output.append(meetings[x]["gsx$location"]["$t"].replace("/","\/"))
		output.append('","notes":"')
		#Have to add \ before each forward slash
		output.append(meetings[x]["gsx$websitenotes"]["$t"].replace("/","\/"))
		output.append('","updated":"')
		output.append(updatedFormatted(meetings[x]))
		output.append('","url":"https:\/\/apps.pugetsoundaa.org\/meetinglist\/index.html?slug=')
		output.append(meetings[x]["gsx$slug"]["$t"])
		output.append('","types":[')
		output.append(typesArray(meetings[x]))
		output.append('],"address":"')
		#Have to add \ before each forward slash
		output.append(meetings[x]["gsx$address"]["$t"].replace("/","\/"))
		output.append('","city":"')
		output.append(meetings[x]["gsx$city"]["$t"])
		output.append('","state":"WA","postal_code":"')
		output.append(meetings[x]["gsx$zipcode"]["$t"])
		output.append('","country":"US"}')
		if (x != meetings_num -1):
			output.append(',')

	#Postprocessing after Meeting Guide format loop
	output.append(']')
	output_string = ''.join(output)
	
	return output_string

#properly formats updated into YYYY-MM-DD HH:MM:SS
def updatedFormatted(meeting):
	lupdateString = meeting["gsx$updated"]["$t"]
	updatedoutput = []
	
	#checks to see if the month AND day are single digits
	if(len(lupdateString) == 8):
		#insert 0 in front of month
		lupdateString = "0" + lupdateString
	#checks to see if the month OR day is a single digit
	if(len(lupdateString) == 9):
		#checks to see if month is double digit and if yes inserts 0 in front of day
		if(lupdateString[2] == "/"):
			lupdateString = lupdateString[:3] + "0" + lupdateString[3:]
		else:
			#insert 0 in front of month
			lupdateString = "0" + lupdateString

	updatedoutput.append(lupdateString[6:11])
	updatedoutput.append("-")
	updatedoutput.append(lupdateString[0:2])
	updatedoutput.append("-")
	updatedoutput.append(lupdateString[3:5])
	updatedoutput.append(" 00:00:00")
	
	updatedFormattedString = ''.join(updatedoutput)
	return updatedFormattedString

#checks on each type and adds corresponding code to the day array if true
def typesArray(meeting):
	typesoutput = []

	if(meeting["gsx$open"]["$t"]=="1"):
		typesoutput.append('"O"')
	else:
		typesoutput.append('"C"')
	if(meeting["gsx$mens"]["$t"]=="1"):
		typesoutput.append(', "M"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$womens"]["$t"]=="1"):
		typesoutput.append(', "W"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$handi"]["$t"]=="1"):
		typesoutput.append(', "X"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$lgbtq"]["$t"]=="1"):
		typesoutput.append(', "LGBTQ"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$spanish"]["$t"]=="1"):
		typesoutput.append(', "S"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$kid"]["$t"]=="1"):
		typesoutput.append(', "CF"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$si"]["$t"]=="1"):
		typesoutput.append(', "ASL"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$alanon"]["$t"]=="1"):
		typesoutput.append(', "AL-AN"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$young"]["$t"]=="1"):
		typesoutput.append(', "Y"')
	typesArrayString = ''.join(typesoutput)
	if(meeting["gsx$speaker"]["$t"]=="1"):
		typesoutput.append(', "SP"')

	typesArrayString = ''.join(typesoutput)
	return typesArrayString

#properly formats the time into HH:MM from from stime_num integer
def timeFormatted(meeting):
	timeoutput = []

	if(int(meeting["gsx$time"]["$t"])<1000):
		timeoutput.append('0')
		timeoutput.append(meeting["gsx$time"]["$t"][:1])
	else:
		timeoutput.append(meeting["gsx$time"]["$t"][:2])
	timeoutput.append(':')
	timeoutput.append(meeting["gsx$time"]["$t"][-2:])

	timeFormattedString = ''.join(timeoutput)
	return timeFormattedString

#checks on each day and adds corresponding integer to the day array if true
def dayArray(meeting):
	dayoutput = []
	y = 0 #helper variable to determine if preceding comma is necessary

	if(meeting["gsx$sunday"]["$t"]=="1"):
		dayoutput.append('0')
		y+=1
	if(meeting["gsx$monday"]["$t"]=="1"):
		if(y>0):
			dayoutput.append(',')
		dayoutput.append('1')
		y+=1
	if(meeting["gsx$tuesday"]["$t"]=="1"):
		if(y>0):
			dayoutput.append(',')
		dayoutput.append('2')
		y+=1
	if(meeting["gsx$wednesday"]["$t"]=="1"):
		if(y>0):
			dayoutput.append(',')
		dayoutput.append('3')
		y+=1
	if(meeting["gsx$thursday"]["$t"]=="1"):
		if(y>0):
			dayoutput.append(',')
		dayoutput.append('4')
		y+=1
	if(meeting["gsx$friday"]["$t"]=="1"):
		if(y>0):
			dayoutput.append(',')
		dayoutput.append('5')
		y+=1
	if(meeting["gsx$saturday"]["$t"]=="1"):
		if(y>0):
			dayoutput.append(',')
		dayoutput.append('6')
		y+=1

	dayArrayString = ''.join(dayoutput)
	return dayArrayString
