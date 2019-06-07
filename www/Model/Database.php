<?php


class Database
{

    function test(){
        $host='database';
        $db = 'test';
        $username = 'postgres';
        $password = 'this_is_a_strong_p@ssw0rd';
        $dsn = "pgsql:host=$host;dbname=$username";

        try{
            // create a PostgreSQL database connection
            $conn = new PDO($dsn,$username,$password);

            // display a message if connected to the PostgreSQL successfully
            if($conn){
                echo "Connected to the <strong>$db</strong> database successfully!";
            }

            $url = $_GET["url"];
            echo "<BR/>You want to access $url";

        }catch (PDOException $e){
            // report error message
            echo $e->getMessage();
        }
    }

}