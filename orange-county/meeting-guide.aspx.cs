using System;
using System.Linq;
using System.IO;
using System.Data;
using System.Configuration;
using System.Collections;
using System.Collections.Generic;
using System.Globalization;
using System.Web;
using System.Text;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using System.Web.Security;
using System.Security.Cryptography; 

public partial class JoshMdb2Json : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {      
        GetJsonFromMdb();    // do the work
    }

    public static string GetMd5Hash(MD5 md5Hash, string input)
    {
        // Convert the input string to a byte array and compute the hash.
        byte[] data = md5Hash.ComputeHash(Encoding.UTF8.GetBytes(input));
        // Create Stringbuilder var to collect bytes and create a hash string.
        StringBuilder sBuilder = new StringBuilder();
        // Loop through each byte of hashed data, format each as hex string.
        for (int i = 0; i < data.Length; i++)
        {
            sBuilder.Append(data[i].ToString("x2"));
        }
        return sBuilder.ToString();         // Return hex string.
    }

    protected void GetJsonFromMdb()
    {
        TextInfo ti = CultureInfo.CurrentCulture.TextInfo;
        string mtgDay;
        string mtgTime;
        string mtgCity;
        string mtgName;
        string mtgAddr;
        string mtgZip;
        string mtgDOU;
        string mtgADA;
        string mtgTypes;
        string mtgNotes;
        string hashSrc;
        string hashOut;
        MD5 md5Hash = MD5.Create();
        dsJson.DataBind();
        DataView dV = (DataView)dsJson.Select(DataSourceSelectArguments.Empty);
        DataTable dT;
        dT = dV.ToTable();
        StringBuilder jsonString = new StringBuilder();
        jsonString.Append("[");
        bool flgError = false;
        int errCount = 0;
        for (int i = 0; i < dT.Rows.Count; i++)
        {
            flgError = false;
            int mtgDayN = 0;
            mtgDay = dT.Rows[i]["mtgday"].ToString();
            if (int.TryParse(mtgDay, out mtgDayN))
            {
                if (mtgDayN == 0)
                {
                    flgError = true;
                }
                else
                {
                    mtgDayN = mtgDayN - 1;
                    mtgDay = mtgDayN.ToString();
                }
            }
            else
            {
                flgError = true;
            }
            mtgTime = dT.Rows[i]["Time"].ToString().Substring(11, 5);
            if (mtgTime.Substring(mtgTime.Length - 1) == ":")
            { // kludge to fix time data error
                mtgTime = mtgTime.Substring(0, mtgTime.Length - 1);
            }
            string strHH = mtgTime.Substring(0, mtgTime.IndexOf(":"));
            string strMM = mtgTime.Substring(mtgTime.IndexOf(":") + 1);
            int mtgHH = 0;
            int mtgMM = 0;
            if (int.TryParse(strHH, out mtgHH))
            {
                if (mtgHH > 23)
                {
                    flgError = true;
                }
            }
            else
            {
                flgError = true;
            }
            if (int.TryParse(strMM, out mtgMM))
            {
                if (mtgMM > 59)
                {
                    flgError = true;
                }
            }
            else
            {
                flgError = true;
            }
            mtgCity = dT.Rows[i]["City"].ToString();
            mtgName = dT.Rows[i]["mtgname"].ToString().Replace("\"", "\\\"");
            mtgName = mtgName.Replace("'s", "'S");
            mtgAddr = dT.Rows[i]["mtgaddress"].ToString();
            mtgNotes = "";
            if (mtgAddr.ToLower().IndexOf("suite") > -1)  // Per Josh, massage address
            {  // strip suite+ from address after adding suite+ data to notes field
                mtgNotes = mtgAddr.Substring(mtgAddr.ToLower().IndexOf("suite"));
                mtgAddr = mtgAddr.Substring(0, mtgAddr.ToLower().IndexOf("suite"));
            }
            hashSrc = mtgDay + mtgTime + mtgCity + mtgName + mtgAddr;
            hashOut = GetMd5Hash(md5Hash, hashSrc);
            mtgZip = dT.Rows[i]["PostalCode"].ToString();
            mtgDOU = dT.Rows[i]["mtgDou"].ToString();
            mtgADA = dT.Rows[i]["mtgADA"].ToString();
            mtgTypes = dT.Rows[i]["typecodes"].ToString();
            if (mtgTypes.Substring(0, 1) != "(" || mtgTypes.Substring(mtgTypes.Length - 1, 1) != ")")
            {
                flgError = true;
            }
            else
            {
                mtgTypes = "[" + mtgTypes + "]";
                mtgTypes = mtgTypes.Replace("(", "|");
                mtgTypes = mtgTypes.Replace(")", "|");
                mtgTypes = mtgTypes.Replace("~", "S");
                mtgTypes = mtgTypes.Replace("GA", "G");
                mtgTypes = mtgTypes.Replace(",", "|,|");
                mtgTypes = mtgTypes.Replace("|", "\"");
            }
            if (flgError == false) // build a JSON feed entry
            {
                jsonString.Append("{\"slug\":\"" + hashOut + "\",");
                jsonString.Append("\"day\":\"" + mtgDay + "\",");
                jsonString.Append("\"name\":\"" + ti.ToTitleCase(mtgName).Replace("'s", "'S") + "\",");
                //           jsonString.Append("\"slug\":\"" + dT.Rows[i]["slug"].ToString() + "\",");
                //               if (dT.Rows[i]["grp"].ToString().Length < 1)
                //               {
                //            jsonString.Append("\"group\":\"" + mtgName + "\",");
                //                }
                //                else
                //                {
                //                    jsonString.Append("\"group\":\"" + dT.Rows[i]["grp"].ToString() + "\",");
                //                }
                jsonString.Append("\"" + "time" + "\":\"" + mtgTime + "\",");
                //                if (Utilities.IsValidDate(dT.Rows[i]["end_time"].ToString()) == true)
                //                {
                //                    jsonString.Append("\"" + "end_time" + "\":\"" + dT.Rows[i]["end_time"].ToString() + "\",");
                //                }
                //                else
                //                {
                //                }
                //                if (dT.Rows[i]["location"].ToString().Length < 1)
                //                {
                //                    jsonString.Append("\"" + "location" + "\":\"\",");
                //                }
                //                else
                //                {
                //                    jsonString.Append("\"" + "location" + "\":\"" + dT.Rows[i]["location"].ToString() + "\",");
                //                }
                jsonString.Append("\"" + "address" + "\":\"" + mtgAddr);
                // append zip code to address, if one exists
                if (mtgZip.Length > 1)
                {
                    jsonString.Append(" " + mtgZip);
                }
                jsonString.Append("\",");                
                jsonString.Append("\"" + "types" + "\":" + mtgTypes + ",");
                
                //              if (dT.Rows[i]["website"].ToString().IndexOf(".") > 0)
                //              {
                //                  if (dT.Rows[i]["website"].ToString().IndexOf("http:") < 0)
                //                  {
                //                      jsonString.Append("\"website\":\"http:\\/\\/" + dT.Rows[i]["website"].ToString().Replace("/", "\\/") + "\",");
                //                  }
                //                  else
                //                  {
                //                      jsonString.Append("\"website\":\"" + dT.Rows[i]["website"].ToString().Replace("/", "\\/") + "\",");
                //                  }
                //              }
                //              else
                //              {
                //                  jsonString.Append("\"website\":\"\",");
                //              }
                //              if (dT.Rows[i]["url"].ToString().IndexOf(".") > 0)
                //              {
                //                  if (dT.Rows[i]["url"].ToString().IndexOf("http:") < 0)
                //                  {
                //                      jsonString.Append("\"url\":\"http:\\/\\/" + dT.Rows[i]["url"].ToString().Replace("/", "\\/") + "\",");
                //                  }
                //                  else
                //                  {
                //                      jsonString.Append("\"url\":\"" + dT.Rows[i]["url"].ToString().Replace("/", "\\/") + "\",");
                //                  }
                //              }
                //              else
                //              {
                //                  string qsStr = "http://" + "tbs";
                //                  jsonString.Append("\"url\":\"" + qsStr + "\",");
                //              }
                //              if (dT.Rows[i]["MeetingImage"].ToString().IndexOf(".") > 0)
                //              {
                //                  if (dT.Rows[i]["MeetingImage"].ToString().IndexOf("http:") < 0)
                //                  {
                //                      jsonString.Append("\"image\":\"http:\\/\\/" + AAGroupSiteConfiguration.DomainURL + "\\/" + dT.Rows[i]["MeetingImage"].ToString().Replace("/", "\\/") + "\",");
                //                  }
                //                  else
                //                  {
                //                      jsonString.Append("\"image\":\"" + dT.Rows[i]["MeetingImage"].ToString().Replace("/", "\\/") + "\",");
                //                  }
                //              }
                //              else
                //                {
                //                    jsonString.Append("\"image\":\"\",");
                //                }
                jsonString.Append("\"notes\":\"" + mtgNotes.Replace("\"", "") + "\",");
                // LAST JSON ELEMENT -- OMIT TRAILING COMMA from StringBuilder str
                jsonString.Append("\"updated\":\"" + mtgDOU.Replace("\"", "") + "\"");
                jsonString.Append("},");
            }
            else
            {
                errCount = errCount + 1;
            }
        }
        jsonString.Remove(jsonString.Length - 1, 1); // remove last trailing comma
        jsonString.Append("]"); // enclose json with closing bracket
        lblBlurb.Text = jsonString.ToString(); // output to html page
        // set page title to show mdb records in, json out
        //Page.Title = "mdB in: " + dT.Rows.Count.ToString() + ", Json out: " + (dT.Rows.Count - errCount).ToString();
    }
}
