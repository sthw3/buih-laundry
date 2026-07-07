<?php

$conn = mysqli_connect(
"localhost",
"root",
"",
"buih_laundry"
);

if(!$conn)
{
    die("Connection Failed: " . mysqli_connect_error());
}

?>