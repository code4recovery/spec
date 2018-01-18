<cfset today = #dayofweek(now())#>
<cfquery name="getday" datasource="#attributes.dsn#">
	SELECT number
    FROM days
    WHERE day = '#today#'
</cfquery>
<cfset today = #today#-1>
    
    <cfquery name="get_meetings" datasource="#attributes.dsn#">
	SELECT
		groupname,
		meetingid,
		day,
		time1,
		groupdirections,
		groupnote1,
		open,
		updated,
		groupaddress,
		groupcity,
		groupzip,
		groupstate,
		longitude,
		latitude
	FROM 
		allmeetlist
		
	WHERE time2 BETWEEN 0.2500001 AND 0.4999999
</cfquery>

<cfoutput><cfloop query="get_meetings">#get_meetings.groupname# #get_meetings.day# #get_meetings.time1#<br></cfloop></cfoutput>