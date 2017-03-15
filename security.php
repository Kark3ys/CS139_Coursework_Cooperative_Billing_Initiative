<?php


//'h' for htmlspecialchars
function h($string) {

	$temp = htmlspecialchars($string, ENT_QUOTES, 'utf-8');
	return $temp;
}


?>