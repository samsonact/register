<?php
function showintname($intid){
	$query = "select name from interface where id=".$intid.";";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$line = pg_fetch_array($result,0,PGSQL_ASSOC);
	echo $line['name'];
	return $line['name'];
}
?>
