<?php
$db_connection = mysqli_connect("localhost", "nghp", "Rhksflwk1!") or die("DB Connection Error");
mysqli_query($db_connection, "set names utf8_unicode_ci");
mysqli_select_db($db_connection, "nghp");
?>