<?php

define("DBHOST","localhost");
define("DBUSER","root");
define("DBPASS","");
define("DBNAME","testbot");

if (mysqli_connect(DBHOST,DBUSER,DBPASS,
DBNAME)){
    echo "ulandi";
}else{
    echo "Xato".mysqli_connect_error();
}
