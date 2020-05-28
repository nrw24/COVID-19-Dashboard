# -*- coding: utf-8 -*-
"""
Created on Mon Apr 13 06:06:56 2020

@author: navad
"""

'''Parse xhtml files and extract COVID-19 statistics 
and insert the information into a MySQL database'''

import xml.dom.minidom as xml
import mysql.connector as mysql
import datetime


#Insert record into table 
def insert(cursor, table, table_values, data):
    
    try:
        if 'nation' == table:
            insert_us(cursor, table, table_values, data)
        query = 'INSERT INTO %s ' % table
        query += "(%s) " % table_values 
        query += "VALUES ( '%s', %s, %s)" % data
        cursor.execute(query)
        
    except Exception:
        update(cursor, table, data)
        
#Insert record into nation table
def insert_us(cursor, table, table_values, data):
    
    try:
        query = 'INSERT INTO %s ' % table
        query += "(%s) " % table_values 
        query += "VALUES ('%s',%s,%s)" % data
        cursor.execute(query)
        
    except Exception:
        update(cursor, table, data)

#Update yesterday table
def update_yesterday(cursor):
     tables = ['nation', 'states', 'michigan_county']
     columns = "ID, total_cases, total_deaths"
     for table in tables:
        cursor.execute("SELECT * FROM %s; " % table)
        rows = cursor.fetchall()
        for row in rows:
            row_data = (row[0], row[1], row[2])
            insert(cursor, 'yesterday', columns, row_data)
            
#Update table
def update(cursor, table, data):
    query = 'UPDATE %s' % table + ' SET '
        
    if 'michigan_county' == table:
        query += 'county="%s", total_cases=%s, total_deaths=%s ' % (data[0], data[1], data[2])
        query += " WHERE county='%s'" % data[0]
        cursor.execute(query)
        
    if 'nation' == table:
        query += 'country="%s", total_cases=%s, total_deaths=%s ' % (data[0], data[1], data[2])
        query += " WHERE country='%s'" % data[0]
        cursor.execute(query)
        
        
    if 'states' == table:
        query += 'state="%s", total_cases=%s, total_deaths=%s ' % (data[0], data[1], data[2])
        query += " WHERE state='%s'" % data[0]
        cursor.execute(query)
        

    if 'yesterday' == table:
        query += 'ID="%s", total_cases=%s, total_deaths=%s ' % (data[0], data[1], data[2])
        query += " WHERE ID='%s'" % data[0]
        cursor.execute(query)
        
        
#Convert string to a number
def stringtoNum(num):
    number = ""
    for char in num:
        if char.isnumeric():
            number += char
        else:
            continue
    if len(number) > 0:
      number = int(number)
    else:
        number = 0
    return number
    

#Files
    
files = ["cases-in-us.xhtml", "cases-in-us.html", "0,9753,7-406-98163_98173---,00.xhtml", "0,9753,7-406-98163_98173---,00.html", "index.xhtml", "index.html" ]
#Nationwide data
document = xml.parse("cases-in-us.xhtml")

#Get total Cases in US from CDC
totals = []
spans = document.documentElement.getElementsByTagName("span")
for span in spans:
    if span.hasAttribute("class"):
        clas = span.getAttribute("class")
        if clas == "count":
          totals.append(span.childNodes)

totalCases = stringtoNum(totals[0].item(0).nodeValue)
totalDeaths = stringtoNum(totals[1].item(0).nodeValue)
us_data = ("us", totalCases, totalDeaths)

#Parse Worldometers
document = xml.parse("index.xhtml")

#State data dictionary
state_data = {}
#Get each table row to extract data
tables = document.documentElement.getElementsByTagName("table")

#Find today table
for table in tables:
    
    #Filter by table ID
    if table.hasAttribute("id"):
        tableId = table.getAttribute("id")
        if tableId == "usa_table_countries_today":
            
            #Get data from table rows
            rows = table.getElementsByTagName("tr")
            for row in rows:
                if row.hasAttribute("style"):
                    if row.hasChildNodes():
                        children = row.childNodes
                        
                        #Get state
                        if len(children[0].childNodes) > 0:
                            for child in children[0].childNodes:
                                #print(child)
                                if child.nodeType == 1:
                                    state = child.childNodes[0].nodeValue.strip()
                                    
                                else:
                                    state = children[0].childNodes[0].nodeValue.strip()
                                   
                            #Get total cases
                                if len(children[1].childNodes) > 0:
                                    cases = children[1].childNodes[0].nodeValue
                                    total_cases = stringtoNum(cases)
                                    
                                #Get total deaths
                                if len(children[3].childNodes) > 0:
                                    deaths = children[3].childNodes[0].nodeValue.strip("\n")
                                    total_deaths = stringtoNum(deaths)
                                    
                                if state != '':
                                    state_data[state] = (state, total_cases, total_deaths)
                            


# Get Michigan county 
county_data = {}
document = xml.parse("0,9753,7-406-98163_98173---,00.xhtml")

tables = document.documentElement.getElementsByTagName("table")

#Find country table
try:
    for table in tables:
        table_captions = table.getElementsByTagName("caption")
        
        #Filter by caption
        for caption in table_captions:
            caption = caption.childNodes[0]
            if "Jurisdiction" in caption.nodeValue:
                
                #Get data from table rows
                rows = table.getElementsByTagName("tr")
                for row in rows:
                    if row.hasAttribute("height"):
                        children = row.childNodes
                       
                        #Get county, confirmed cases and reported deaths
                        
                        county = children[0].childNodes[0].nodeValue.strip()
                        confirmed_county_cases = children[1].childNodes[0].nodeValue
                        reported_county_deaths = children[2].childNodes[0].nodeValue.replace(u'\xa0', '0')
                        
                        #Populate county data
                        if '\n' in county:
                            continue
                        
                        data = (county, int(confirmed_county_cases), int(reported_county_deaths))
                        county_data[county] = data
except Exception:
    pass
#Form conncetion to database server
connection = mysql.connect(host="localhost", user="root", passwd="")
cursor = connection.cursor()

#Create covid19 database if it doesn't exist
statement = "CREATE DATABASE IF NOT EXISTS covid19"
cursor.execute(statement)
cursor.close()

#Form connection to database
connection = mysql.connect(host="localhost", user="root", passwd="", database="covid19")

#Get cursor
cursor = connection.cursor()

#Create Tables if it doesn't exist

statement = '''CREATE TABLE IF NOT EXISTS states
 (state VARCHAR(255) PRIMARY KEY,
 total_cases INT,
 total_deaths INT)'''
cursor.execute(statement)

statement = '''CREATE TABLE IF NOT EXISTS yesterday
 (ID VARCHAR(255) PRIMARY KEY,
 total_cases INT,
 total_deaths INT, UNIQUE(ID))'''
cursor.execute(statement)


statement = '''CREATE TABLE IF NOT EXISTS nation
 (country VARCHAR(255) PRIMARY KEY,
 total_cases INT,
 total_deaths INT)'''
cursor.execute(statement)

statement = '''CREATE TABLE IF NOT EXISTS michigan_county
 (county VARCHAR(255) PRIMARY KEY,   
 total_cases INT, 
 total_deaths INT)'''
cursor.execute(statement)

statement = '''CREATE TABLE IF NOT EXISTS users
(ip_address VARCHAR(255),
favorite_state VARCHAR(255) DEFAULT NULL,
highlighted_state VARCHAR(255) DEFAULT NULL, UNIQUE(highlighted_state))'''
cursor.execute(statement)


#Insert County data into database
columns = "county, total_cases, total_deaths"
for data in county_data.values():
    insert(cursor, 'michigan_county', columns, data)
connection.commit()

#Insert State data into database
columns = "state, total_cases, total_deaths"
for data in state_data.values():
    insert(cursor, 'states', columns, data)
connection.commit()


#Insert National Data data into database
columns = "country, total_cases, total_deaths"
insert_us(cursor, 'nation', columns, us_data)
connection.commit()

#UPDATE YESTERDAY TABLE AT 00:00:00 each day

#Get current time in time object
currentTime = datetime.datetime.now()
currentTime = datetime.time(currentTime.hour, currentTime.minute, currentTime.second, currentTime.microsecond)

#Set update time
updateTime = datetime.time(23, 29, 55, 0)
if currentTime >= updateTime:
   update_yesterday(cursor)
   connection.commit()


#CLose connection 
cursor.close()


