<?php
$dbconn = pg_connect("dbname=ip2") 
	or die('Could not connect: ' . pg_last_error());

if ( $_GET['action'] == 'showallhosts' ) {
	$query = 'SELECT * FROM host';
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());

	// Printing results in HTML
	echo "<table>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "\t<tr>\n";
		echo "<td>".$line['id']."</td> <td><a href=cmd.php?action=showhost&hostid=".$line['id'].">".$line['name']."</a></td><td><a href=cmd.php?action=delhost&hostid=".$line['id'].">delete</a>\n";
		echo "\t</tr>\n";
	}
	echo "</table>\n";
	echo '<form name="new host" action="cmd.php" method="get">';
	echo 'Hostname: <input type="text" name="hostname" /> <br/>';
	echo ' <input type="submit" value="Submit" />';
	echo ' <input type="hidden" name="action" value="addhost" />';
	echo "</form>";
} else if ( $_GET['action'] == 'delhost' ) {
	echo "about to delete host by id\n";
	//XXXX check for hostid being set and not stupid
        $query = 'DELETE FROM host WHERE id='.$_GET['hostid'].";";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
} else if  ( $_GET['action'] == 'addhost' ) {
	echo "about to add host\n";
        $query = "INSERT INTO host VALUES (DEFAULT,"."'".$_GET['hostname']."'".") returning *";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
} else if ( $_GET['action'] == 'showhost' ) {
	$query = "select * from interface where host=".$_GET['hostid'];
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo "<table>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "\t<tr>\n";
		echo "<td>".$line['id']."</td> <td><a href=cmd.php?id=".$line['id'].">".$line['name']."</a></td>\n";
		echo "\t</tr>\n";
	}
	echo "</table>";
	echo '<form name="new host" action="cmd.php" method="get">';
	echo 'Interface name: <input type="text" name="intname" /> <br/>';
	echo ' <input type="submit" value="Submit" />';
	echo ' <input type="hidden" name="action" value="addinterface" />';
	echo ' <input type="hidden" name="hostid" value="'.$_GET['hostid'].'" />';
	echo "</form>";

} else if ( $_GET['action'] == 'addinterface' ) {


	echo "about to add interface\n";
        $query = "INSERT INTO interface VALUES (DEFAULT,"."'".$_GET['intname']."',".$_GET['hostid'].") returning *";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);


}
?>
