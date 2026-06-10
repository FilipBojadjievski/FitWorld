<?php

session_start();


$_SESSION = [];


session_destroy();


header("Location: ../index.php");
exit; // Always exit to stop the script from running anything else 
?>