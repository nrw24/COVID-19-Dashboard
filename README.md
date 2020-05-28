# COVID-19-Dashboard
This website was created using Python, XML DOM API, HTML, CSS, JS, PHP, MYSQL and Bash scripting. During the corona pandemic, I found it useful to try to create a comprehensive dashboard on national and state statistics. I also included Michigan County data. The site includes some homepage customization options such as selecting a highlight state and/or a set of favorite states to display. The unique identifer for each user is their IP address. In addition, tables are sortable based on the column values and the site is updated every thirty minutes for accuracy.

The flow of this applicaiton goes like this:
1. Bash script:
   - Download all necessary html files for parsing using wget
   - Convert all .html files to .xhtml using TagSoup.
   - Call the python parser script to extract all the necessary data from the site
   - Delete all .html, .xhtml and temporary files upon completion.
2. Python script:
   - Parse each .xhtml file using the XML DOM API (xml.dom.minidom).
   - Store the information in a list/dict
   - Create database/tables if necessary.
   - Insert/update the information in the MYSQL database.
   - Update the yesterday table each day before 11:30pm.
3. PHP:
   - Extract the information from the MYSQL database.
   - Create HTML file using the extracted information.
   
   
NOTE: SOMETIMES SITE WON'T WORK DUE TO HTML FILES FROM THE SOURCES BEING CHANGED IN SOME WAY. SIMPLY LOOK THROUGH THE HTML OF THE SITES TO SEE IF THERE HAS BEEN ANY UPDATED. USUALLY IT'S A SIMPLE 5-10 MINUTE FIX WHERE THEY ADDED AN EXTRA TAG OR FORMATTING CHANGE.
   
