<!DOCTYPE HTML>
<?php
$cnx = new mysqli('localhost', 'root', '', 'covid19');

if ($cnx->connect_error) die('Connection failed: ' . $cnx->connect_error);

//Get user IP address
function getUserIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
    {
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
//Get entire table
function getEntireTable($cnx, $table, $id)
{
    $table_data = '';
    $query = 'SELECT * FROM ' . $table . ' ORDER BY total_cases DESC';
    $cursor = $cnx->query($query);
    while ($row = $cursor->fetch_assoc())
    {
        $newC = 0;
        $newD = 0;
        $select_query = "SELECT total_cases, total_deaths FROM " . $table . " WHERE " . $id . "='" . $row[$id] . "'";
        $result1 = $cnx->query($select_query);
        $select_query = "SELECT total_cases, total_deaths FROM yesterday WHERE ID='" . $row[$id] . "'";
        $result2 = $cnx->query($select_query);
        $select_query = "SELECT DISTINCT highlighted_state FROM users WHERE ip_address='" . getUserIpAddr() . "' AND highlighted_state IS NOT NULL";
        $result3 = $cnx->query($select_query);
        $highlight = '';
        if ($result3->num_rows > 0)
        {
            $highlight = $result3->fetch_assoc() ['highlighted_state'];
        }

        while ($row1 = $result1->fetch_assoc() and $row2 = $result2->fetch_assoc())
        {
            $newC = $row1['total_cases'] - $row2['total_cases'];
            $newD = $row1['total_deaths'] - $row2['total_deaths'];
            if ($row[$id] == "Michigan")
            {
                if ($row['state'] == $highlight)
                {
                    $table_row = '<tr class="highlight" onclick="openTab(event, ' . "'Counties')" . '">';
                }
                else
                {
                    $table_row = '<tr class="table_row" onclick="openTab(event, ' . "'Counties')" . '">';
                }
            }
            else
            {
                if ($row[$id] == $highlight)
                {
                    $table_row = '<tr class="highlight"';
                }
                else
                {
                    $table_row = "<tr class='table_row'>";
                }
            }
            $table_row = $table_row . '<td>' . $row[$id] . '</td><td>' . $row['total_cases'] . '</td><td>' . $newC . '</td><td>' . $row['total_deaths'] . '</td><td>' . $newD . '</td>';
            $table_row = $table_row . '</tr>';
            $table_data = $table_data . $table_row;
        }
    }
    return $table_data;
}

//Get new cases and deaths for each row
function getNewData($cnx, $table, $id)
{

    $table_data = "";
    if ($table != 'states')
    {
        return getEntireTable($cnx, $table, $id);
    }
    $sql = "SELECT DISTINCT * FROM users WHERE ip_address='" . getUserIpAddr() . "' AND favorite_state IS NOT NULL";
    if ($res = $cnx->query($sql))
    {
        /* Check the number of rows that match the SELECT statement */
        if ($res->num_rows > 0)
        {
            /* Issue the real SELECT statement and work with the results */
            $sql = "SELECT DISTINCT favorite_state FROM users WHERE ip_address='" . getUserIpAddr() . "'";
            foreach ($cnx->query($sql) as $row)
            {

                /* Get only the favorite states information*/
                $query = 'SELECT * FROM ' . $table . ' WHERE ' . $id . "='" . $row['favorite_state'] . "' ORDER BY total_cases DESC";
                $cursor = $cnx->query($query);

                /*Calculate favorite state data*/
                while ($row = $cursor->fetch_assoc())
                {
                    if (!($row[$id] === ''))
                    {
                        $newC = 0;
                        $newD = 0;

                        $select_query = "SELECT total_cases, total_deaths FROM " . $table . " WHERE " . $id . "='" . $row[$id] . "'";
                        $result1 = $cnx->query($select_query);

                        $select_query = "SELECT total_cases, total_deaths FROM yesterday WHERE ID='" . $row[$id] . "'";
                        $result2 = $cnx->query($select_query);

                        $select_query = "SELECT DISTINCT highlighted_state FROM users WHERE ip_address='" . getUserIpAddr() . "' AND highlighted_state IS NOT NULL";
                        $result3 = $cnx->query($select_query);
                        if ($result3->num_rows > 0)
                        {
                            $highlight = $result3->fetch_assoc() ['highlighted_state'];
                        }
                        while ($row1 = $result1->fetch_assoc() and $row2 = $result2->fetch_assoc())
                        {

                            $newC = $row1['total_cases'] - $row2['total_cases'];
                            $newD = $row1['total_deaths'] - $row2['total_deaths'];
                            if ($row['state'] == "Michigan")
                            {
                                if ($row['state'] == $highlight)
                                {
                                    $table_row = '<tr class="highlight" onclick="openTab(event, ' . "'Counties')" . '">';
                                }
                                else
                                {
                                    $table_row = '<tr class="table_row" onclick="openTab(event, ' . "'Counties')" . '">';
                                }
                            }
                            else
                            {
                                if ($row['state'] == $highlight)
                                {

                                    $table_row = '<tr class="highlight">';
                                }
                                else
                                {
                                    $table_row = "<tr class='table_row'>";
                                }
                            }
                            $table_row = $table_row . '<td>' . $row[$id] . '</td><td>' . $row['total_cases'] . '</td><td>' . $newC . '</td><td>' . $row['total_deaths'] . '</td><td>' . $newD . '</td>';
                            $table_row = $table_row . '</tr>';
                            $table_data = $table_data . $table_row;
                        }
                    }
                }
            }
        }

        /* No rows matched -- print whole table */
        else
        {
            $table_data = getEntireTable($cnx, $table, $id);
        }
    }
    return $table_data;
}

?>
<HTML lang='en'>

<head>
<meta charset="utf-8">


<title>COVID-19 Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!--own stylesheet-->
<link rel="stylesheet" type="text/css" href="dashboard.css">
</head>

<body>
  <nav>
  <ul id="navigation">
  <li class="nav"><a href="index.php">Home</a></li>
  <li class="nav"><a href="https://coronavirus.jhu.edu/us-map">John Hopkins Dashboard</a></li>
  <li class="nav"><a href="sources.php">Sources</a></li>
  <li class="nav"><a href="about.php">About</a></li>
  <li class="nav"><a href="personalization.php">Personalize the Dashboard</a></li>
</ul>

</nav>

<p class="welcome"><strong><em>Welcome to Navado's COVID-19 Website
    <br>
    Tables are updated every thirty minutes using SOURCES.
</em></strong></p>

<p class="welcome"><strong><em>To personalize your page, please use the personalization section of the site (found in the navigation bar above) 
</em></strong></p>


<!-- Tab links -->
<div class="tab">
  <button class="tablinks" id="defaultTab" onclick="openTab(event, 'US')">US</button>
  <button class="tablinks" onclick="openTab(event, 'State')">State</button>
  <button class="tablinks" onclick="openTab(event, 'Counties')">Counties</button>

</div>

<!-- Tab content -->

 <!--US Table Tab-->
<div id="US" class="tabcontent">
  <h3>US</h3>
  <div class="wpb_wrapper">
			   <!-- STARTSECTION - Top Level Box HTML -->
                        <section class="cases-header">
                            <div class="cases-callouts">
                                <div class="callouts-container">
                                    <div class="callout">
                                        Total Cases
                                        <span class="count">
                                        
    <?php
$query = 'SELECT * FROM nation';

$select_query = "SELECT total_cases, total_deaths FROM yesterday WHERE ID='us'";
$result1 = $cnx->query($select_query);
$cursor = $cnx->query($query);
$row = $cursor->fetch_assoc();
$row1 = $result1->fetch_assoc();

echo $row['total_cases'];

$newC = $row['total_cases'] - $row1['total_cases'];
$newD = $row['total_deaths'] - $row1['total_deaths'];

?> 
</span>
                                        <span class="new-cases"><?php echo $newC; ?> New Cases*</span>
                                    </div>
                                    <div class="callout">
                                        Total Deaths
                                        <span class="count"><?php
echo $row['total_deaths'];

?>
</span>
                                        <span class="new-cases"><?php echo $newD; ?> New Deaths*</span>
                                    </div>
                                </div>
                                

                            </div>
  </div>
  </div>

 <!--State Table Tab-->
 
<div id="State" class="tabcontent">
<div class="usa_table_countries_div">
<table id="usa_table_countries_today" class="table table-bordered table-hover table-responsive usa_table_countries" style="width:100%">
<thead>
  <h3>State</h3>
  <!--Display personalized information if it exists-->
 
<table class="states"> 
		<caption class="table_caption">COVID CASES by State</caption>
    
	<tr class="table_header">
	<th onclick="sortTable('states',0)">State</a></th>
  <th onclick="sortTable('states',1)">Confirmed Cases</a></th>
  <th onclick="sortTable('states',2)">New Cases</th>
  <th onclick="sortTable('states',3)">Confirmed Deaths</a></th>
  <th onclick="sortTable('states',4)">New Deaths</a></th>
</tr>

<?php
//Populate table
echo getNewData($cnx, 'states', 'state');

?>
</table>

</div>
  </div>


<!--Michigan County Table Tab-->
<div id="Counties" class="tabcontent">
  <h3>Counties</h3>

<table class="michigan_county"> 
		<caption class="table_caption">COVID CASES by County in Michigan</caption>
<thead>
	<tr class="table_header">
	<th onclick="sortTable('michigan_county',0)">County</th>
  <th onclick="sortTable('michigan_county',1)">Confirmed Cases</th>
  <th onclick="sortTable('michigan_county',2)">New Cases</th>
  <th onclick="sortTable('michigan_county',3)">Confirmed Deaths</th>
	<th onclick="sortTable('michigan_county',4)">New Deaths</th>
</tr>
</thead>
<tbody>
<?php
//Populate table
echo getNewData($cnx, 'michigan_county', 'county');
$cnx->close();
?>
</tbody>
</table>
</div>
</body>

<script>

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultTab").click();

function redirectToCounty() {
  window.location.replace("county.php");
}

/*Sort tables by column value*/
function sortTable(tableClass, n) {
var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;

table = document.getElementsByClassName(tableClass)[0];
switching = true;
dir = "asc";
while (switching) {
    switching = false;
    rows = table.getElementsByTagName("TR");
    for (i = 1; i < (rows.length - 1); i++) {
        shouldSwitch = false;
        x = rows[i].getElementsByTagName("TD")[n];
        y = rows[i + 1].getElementsByTagName("TD")[n];
                var cmpX=isNaN(parseInt(x.innerHTML))?x.innerHTML.toLowerCase():parseInt(x.innerHTML);
                var cmpY=isNaN(parseInt(y.innerHTML))?y.innerHTML.toLowerCase():parseInt(y.innerHTML);
cmpX=(cmpX=='-')?0:cmpX;
cmpY=(cmpY=='-')?0:cmpY;
        if (dir == "asc") {
            if (cmpX > cmpY) {
                shouldSwitch= true;
                break;
            }
        } else if (dir == "desc") {
            if (cmpX < cmpY) {
                shouldSwitch= true;
                break;
            }
        }
    }
    if (shouldSwitch) {
        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
        switching = true;
        switchcount ++;      
    } else {
        if (switchcount == 0 && dir == "asc") {
            dir = "desc";
            switching = true;
        }
    }
}
}


/*Switch tables*/
function openTab(e, tableName) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tableName).style.display = "block";
  e.currentTarget.className += " active";
}
</script>

</html>
