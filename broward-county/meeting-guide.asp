<%
'open connection to database
Set Conn = Server.CreateObject("ADODB.Connection")
Set rs = Server.CreateObject("ADODB.Recordset")
connStr = "DRIVER={Microsoft Access Driver (*.mdb)};DBQ=" & Server.MapPath("fpdb/aa.mdb")
Conn.Open connStr

'sql = "SELECT * FROM meeting_data;"
'meeting_data example record
'meeting_id=2, place_id=2, time=20:00, dow=3, last_updated=, meeting_type=1, mname_id=2, 
'Alanon=False, notes=Handicap Access, meeting_code=BG , meeting_id=2, 1=1, 2=, 3=, 4=, 5=, 6=, 7=, 
'8=, 9=, 10=, 11=, 12=, 13=, 14=, 15=, 16=, 17=, 18=, 19=, 20=, 21=, 22=, 23=, 24=, 
'place_id=2, Name=CALVARY PRESBYTERIAN CHURCH, Add1=3950 Coconut Creek Parkway, Add2=, 
'City=Coconut Creek, State=FL, Zip=33066, Lat=26.244538 , Lon=-80.177598 , Phone=, Special=, 
'area_id=1, color_code=, wheelChair=True, smoking=False, latlon=False, beach=False, area_name=Coconut Creek, 
'meeting_name=Open, mname=Coconut Creek Group, area_color=66FF66, meeting_name.place_id=2, 

'select meetings
sql = "SELECT" & _
"	meetings.meeting_id AS slug," & _
"	time," & _
"	dow AS day," & _
"	meeting_type," & _
"	notes," & _
"	meeting_code," & _
"	Name AS location," & _
"	Add1 AS address," & _
"	city," & _
"	state," & _
"	Zip AS postal_code," & _
"	wheelChair," & _
"	smoking," & _
"	area_name AS region," & _
"	mname AS name " & _
"FROM meeting_data;"

rs.open sql, Conn

Do Until rs.EOF
	
	'subtract 1 from day

	'types logic: should be an array of codes
	'meeting type 1 add an O
	'meeting type 2 add a C
	'wheelChair = True then add an X
	'if smoking = True then add SM
	'meeting_code comes space-separated
	'if meeting_code contains AB then add LIT
	'if meeting_code contains BB then add B
	'if meeting_code contains BG then add BE
	'if meeting_code contains CB then add LIT
	'if meeting_code contains D then add D
	'if meeting_code contains DR then add LIT
	'if meeting_code contains FR then add F
	'if meeting_code contains g then add G
	'if meeting_code contains GV then add GR
	'if meeting_code contains LS then add LIT
	'if meeting_code contains LT then add LIT
	'if meeting_code contains M then add MED
	'if meeting_code contains m then add M
	'if meeting_code contains PG then add PG
	'if meeting_code contains SH then add S
	'if meeting_code contains SP then add SP
	'if meeting_code contains SPD then add SP and D
	'if meeting_code contains SS then add ST
	'if meeting_code contains ST then add ST
	'if meeting_code contains STR then add ST and TR
	'if meeting_code contains TR then add TR
	'if meeting_code contains w then add W
	'if meeting_code contains YP then add Y

    for each x in rs.fields
        response.write(x.name)
        response.write("=")
        response.write(x.value)
        response.write(", ")
    next
    
    response.write("<br>")
    
    rs.MoveNext
Loop

rs.close

'Output JSON
'Response.ContentType = "application/json"
'Response.Write("{ ""query"":""Li"", ""suggestions"":[""Liberia"",""Libyan Arab Jamahiriya"",""Liechtenstein"",""Lithuania""], ""data"":[""LR"",""LY"",""LI"",""LT""] }")

'response.write(json)
%>