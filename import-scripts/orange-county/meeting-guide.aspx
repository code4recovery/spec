<%@ Page Title="" Language="C#" AutoEventWireup="true" CodeFile="meeting-guide.aspx.cs" Inherits="JoshMdb2Json" %>
<asp:AccessDataSource ID="dsJson" runat="server" DataFile = "<%$ appSettings:Mdb2Json_SourceDB %>"     
     SelectCommand="SELECT [DK] AS mtgday, city, time, [address] AS mtgaddress, [MEETING NAME] AS mtgname,  [TYPE] AS typecodes, ZIP AS PostalCode, [LAST CHANGE] AS mtgDou, [Hndcpd Eqpd] AS mtgADA FROM JAN99_981213">
</asp:AccessDataSource>