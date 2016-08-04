<cfsetting enablecfoutputonly = "True"> <!---this setting limits output to values within <cfoutput> tags--->

<!---create meeting designation structure --->
 <cfquery name="a" datasource="#session.a.dsnname#">
	select * from zlk_event_attributes;
</cfquery>
<cfset filename = expandPath("meetingguide\aasf.json")>
<cfset aStruct = {}>
<cfloop query="a">
	<cfscript>
		structInsert(aStruct,code,display_short);	
	</cfscript>
</cfloop>
 
<!---get active meetings (end_date is null)--->
<cfquery name="m" datasource="#session.a.dsnname#">
	select  
	coalesce(schedMeetingName,event_name) as name
	, meet_id as slug, 
	schedNote as notes, 
	convert(char(19),meeting_update,120) as updated, 
	'http://aasf.org/vm.cfm?s=' + cast(meet_id as char) as url,
	timesort as time, 
	day_id - 1 as day, 
	null as types, 
	m_bus_address1 as address, 
	m_bus_city as city, 
	m_bus_state as state, 
	m_bus_zip as postal_code,
	'US' as country, 
	latitude,
	longitude, 
	'American/Los Angeles' as timezone,
	bus_name as location,
	'http://aasf.org/viewsite.cfm?site_id=' + cast(bus_id as char) as location_url,
	bus_id as location_slug, 
	sitenote as location_notes,
	convert(char(19),
	bus_modified_date,120) as location_updated,
	discipline_areas,
	neighborhood as region
	from qry_meetings
	WHERE     (End_Date IS NULL) OR
                      (CONVERT(char(8), End_Date, 112) >= CONVERT(char(8), GETDATE(), 112));
</cfquery>
<cfset myCnt = 0> 

<!---build JSON string starting with left square bracket--->
<!---value of output saved into variable str--->
<cfsavecontent variable="str">
<cfoutput>[</cfoutput> 
<cfloop query="m">
 
	<!---loop to allow for multiple discipline (type) codes--->
	<cfset myD = "">
	<cfloop list="#discipline_areas#" index="i" delimiters=" ">
		<cfif myD IS NOT ""><cfset myD &= ","></cfif>
		<cfset myV = aStruct[i]> 
		<cfset myD &= '"#myV#"'>
	</cfloop>
	
 	<!---output json string with labels and values--->
	<cfif myCnt GT 0><cfoutput>,</cfoutput></cfif> 
	<cfoutput>{"name":"#name#","slug": "#slug#","notes":"#notes#","updated":"#updated#","url":"#trim(url)#","time":"#time#","day":"#day#","types":[#myD#],"address":"#address#","city":"#city#","state":"#state#","postal_code":"#postal_code#","country":"#country#","latitude":"#trim(latitude)#","longitude":"#trim(longitude)#","timezone":"#timezone#","location":"#location#","location_slug":"#location_slug#","location_notes":"#location_notes#","region":"#region#"}</cfoutput> 
	<cfset myCnt += 1> </cfloop>
<cfoutput>]</cfoutput>
</cfsavecontent>
</cfsetting> 
<!---write to file--->
 <cffile action="Write" file="#filename#" output="#str#"> 
   <cfcontent type="application/json" file="#filename#" deletefile="no"> 
<!---   <cfoutput>#str#</cfoutput> ---> 