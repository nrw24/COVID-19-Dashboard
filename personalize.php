<?php

//Get user IP address
function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//Connect to database
$cnx = new mysqli('localhost', 'root', '', 'covid19');

if ($cnx->connect_error)
    die('Connection failed: ' . $cnx->connect_error);

//Create users table if doesn't exist
$query = "CREATE TABLE IF NOT EXISTS users
(ip_address VARCHAR(255),
favorite_state VARCHAR(255) DEFAULT NULL,
highlighted_state VARCHAR(255) DEFAULT NULL, UNIQUE(highlighted_state))";
$result = $cnx->query($query);
echo 'USERS DATABASE CREATED.<br>';
//Get favorite states on form submission


if(isset($_POST['favorite_state'])) {
    $fav_states = $_POST['favorite_state'];
    foreach($fav_states as $state){
        $statement = 'INSERT INTO users (ip_address, favorite_state) ';
        $statement = $statement . "VALUES ( '" . getUserIpAddr() . "', '" . $state ."' )";
        if($cnx->query($statement) === TRUE){
            echo "New record created successfully.<br>";
        }
        else {
            echo "Error: " . $statement . "<br>" . $cnx->error;
        }
        
    }
    
}
//Get highlighted state on form submission
if(isset($_POST['highlight_state'])){
    $highlight_state = $_POST['highlight_state'];
    $statement = 'INSERT INTO users ( ip_address, highlighted_state ) ';
    $statement = $statement . "VALUES ( '" . getUserIpAddr() . "', '" . $highlight_state ."' )";
    if($cnx->query($statement) === TRUE){
        echo "New record created successfully.<br>";
    }
    else {
        $sql = "UPDATE users SET highlighted_state='" . $highlight_state . "' WHERE ip_address='" . getUserIpAddr() .  "'";
    $res = $cnx->query($sql);
    echo $sql;
        if ($res = $cnx->query($sql) === TRUE) {
            echo "Update successful.<br>";
        }  
         
    
        
    
    else {
        echo "Error: " . $statement . "<br>" . $cnx->error;
        }
    }
   
    
}
    
else{
    echo 'Please select AT LEAST one option.';
}
header("Location: index.php");

?>