#!/bin/bash

filename=sources.txt

function update () {
echo "Getting Source files with WGET..."
lines=`cat $filename`

for line in $lines;
do
  if [[ $line = https* ]] 
  then
    wget $line
  fi
done
echo "File(s) downloaded"

echo "Converting file(s) to XHTML with TagSoup"
java -jar tagsoup-1.2.1.jar --files *.html
echo "HTML file(s) converted to XHTML"

echo "Parsing file(s)..."
python parser.py
echo "File(s) Parsed" 

echo "Deleting .html and .xhtml file(s)...."
rm *.html
rm *.xhtml
echo "File(s) deleted."

}

while true
do
  update
  sleep 1800
done