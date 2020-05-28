<!DOCTYPE HTML>

<HTML lang='en'>

<head>
<meta charset="utf-8">


<title>Personalization</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!--own stylesheet-->
<link rel="stylesheet" type="text/css" href="sources.css">
<?php
$cnx = new mysqli('localhost', 'root', '', 'covid19');

if ($cnx->connect_error) die('Connection failed: ' . $cnx->connect_error);
?>
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
    This page displays all the sources use in the dashboard on the homepage.
</em></strong></p>

<label for="personalization">Choose your favorite states:</label>
<form method="post" action="personalize.php">  
    <fieldset>  
    <legend>Choose your preferred states</legend>  
  <?php
$query = 'SELECT * FROM states';
$cursor = $cnx->query($query);
while ($row = $cursor->fetch_assoc())
{
    echo "<input type='checkbox' name='favorite_state[]' value='" . $row['state'] . "'>" . $row['state'] . "<br>";
}
?>
        <br>    
    </fieldset>  

<label for="highlighting">Choose your highlighted states:</label>  
    <fieldset>  
    <legend>Choose your highlighted states</legend>  
  <?php
$query = 'SELECT * FROM states';
$cursor = $cnx->query($query);
while ($row = $cursor->fetch_assoc())
{
    echo "<input type='radio' name='highlight_state' value='" . $row['state'] . "'>" . $row['state'] . "<br>";
}
?>
        <br> 
    <input type="submit" value="Submit now">  
    </fieldset>  
</form> 

</body>

</html>
