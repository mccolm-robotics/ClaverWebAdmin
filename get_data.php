<?php

include 'connect.php';

$sql = $connection->query("select * from rootTable");

$result = array();

while($row=$sql->fetch_assoc())
{
    $result[] = $row;
}

echo json_encode($result);

?>