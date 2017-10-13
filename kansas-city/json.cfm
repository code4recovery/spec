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
		
	<cfif isDefined('url.type')>
		<cfif url.type eq 'gay'>
			WHERE(people = 'lgbt')
				OR(attended='lgbt')
		<cfelseif url.type eq 'men'>
			WHERE(people = 'm')
				OR(attended='men')
		<cfelseif url.type eq 'women'>
			WHERE(people = 'w')
				OR(attended='women')
		</cfif>
	</cfif>
			
</cfquery>

<cfset meetings = [] />

<cfoutput query="get_meetings">
	<cfset meeting = {
		"name"="#groupname#", 
		"slug"="#meetingid#",
		"day"="#INT(NUMBERFORMAT(day)-1)#",
		"time"="#TimeFormat(time1,"HH:mm")#",
		"location"="#groupdirections#",
		"notes"="#groupnote1#",
		"types"="[#open#]",
		"updated"="#DateTimeFormat(updated, "yyyy-mm-dd HH:nn:ss")#",
		"address"="#groupaddress#",
		"city"="#groupcity#",
		"postal_code"="#groupzip#",
		"state"="#groupstate#",
		"country"="USA",
		"longitude"="#longitude#",
		"latitude"="#latitude#"
	} />
	<cfset arrayAppend(meetings, meeting) />
</cfoutput>


<cfprocessingdirective suppresswhitespace="Yes">
	<cfheader name="Content-Type" value="application/json">
	<cfoutput>#serializeJSON(meetings)#</cfoutput>
</cfprocessingdirective>