<?php
error_reporting(E_ALL);
ini_set(“display_errors”, 1);


//'h' for htmlspecialchars
function h($string) {

	$temp = htmlspecialchars($string, ENT_QUOTES, 'utf-8');
	return $temp;
}


?>