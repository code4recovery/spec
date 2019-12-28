<%@ Page Title="" Language="C#" AutoEventWireup="true" CodeFile="JoshMdb2Json.aspx.cs" Inherits="JoshMdb2Json" %>
<html>
<head id="Head1" runat="server">
<title>JSON Export</title>
</head>
<body>
  <asp:AccessDataSource ID="dsJson" runat="server" 
    DataFile = "<%$ appSettings:Mdb2Json_SourceDB %>"     
    SelectCommand="SELECT [DK] AS mtgday, city, time, [address] AS mtgaddress, [MEETING NAME] AS mtgname,  
    [TYPE] AS typecodes, ZIP AS PostalCode, [LAST CHANGE] AS mtgDou, [Hndcpd Eqpd] AS mtgADA FROM JAN99_981213">
  </asp:AccessDataSource>
  <asp:Label ID="lblBlurb" runat="server"></asp:Label>
</body>
</html>