<!---
	<cfset today = #dayofweek(now())#>
	<cfset todaytime = "1899-12-30 #timeformat(now(), "HH:mm:ss.0")#">

	<cfquery name="getgroup" datasource="#attributes.dsn#">
		SELECT *
		FROM Groupinfo
		ORDER BY groupstate, groupcity
	</cfquery>

		
	<cfset zz = 1>
		<cfoutput>
		<cfloop query="getgroup">

		<cfquery name="getmeeting" datasource="#attributes.dsn#">
			SELECT *
			FROM Meetinfo
			WHERE groupid = #getgroup.groupid#
				AND day = #today#
			ORDER BY time1
		</cfquery>
			
       	<cfset dd=0>
  		<cfloop query="getmeeting">
	       	<cfif #getmeeting.day# eq #today#>
		        <cfif #getmeeting.time1# gte #todaytime#>
   					<cfset dd = dd + 1>
		        </cfif>
	        </cfif>
	    </cfloop>
		<cfif dd gt 0>
--->



<cfset today = #dayofweek(now())#>
<cfquery name="getday" datasource="#attributes.dsn#">
	SELECT number
    FROM days
    WHERE day = '#today#'
</cfquery>
<cfset today = #today#-1>
<cfset todaytime = "1899-12-30 #timeformat(now(), "HH:mm:ss.0")#">

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
    WHERE day = #today#
    AND time2 > #numberformat(timeformat(now(), "HH:mm:ss.0"),0.0000000)#
        
</cfquery>
<table>
	<cfoutput query="get_meetings">
	<tr>
    	<td>	
        	#groupname#  
        </td>
        <td>
        	#time1#
        </td>
        <td>
        	#todaytime#
        </td>
        <td>
        	#today#
        </td>
    </tr>
	</cfoutput>
</table>