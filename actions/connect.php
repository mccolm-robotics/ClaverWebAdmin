<?php

$connection = new mysqli('localhost', 'root', '', 'claver');
if ($connection->connect_errno) {
    echo "Failed to connect to MySQL";
}

?>