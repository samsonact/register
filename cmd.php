<?php
function footer(){
echo '<a href="cmd.php?Action=showallhosts"> show all hosts </a>';
}

function showallhosts(){
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
}

function showhost($hostid){
	$query = "select * from interface where host=".$hostid;
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo "<table>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "\t<tr>\n";
		echo "<td>".$line['id']."</td> <td><a href=cmd.php?id=".$line['id'].">".$line['name']."</a></td>\n";
		echo "\t</tr>\n";
	}
	echo "</table>";
	echo '<form name="new host" action="cmd.php" method="get">';
	echo 'Add interface name: <input type="text" name="intname" />';
	echo ' <input type="submit" value="Submit" />';
	echo ' <input type="hidden" name="action" value="addinterface" />';
	echo ' <input type="hidden" name="hostid" value="'.$hostid.'" />';
	echo "</form>";
}

$dbconn = pg_connect("dbname=ip2") 
	or die('Could not connect: ' . pg_last_error());

if ( $_GET['action'] == 'showallhosts' ) {
	showallhosts();
} else if ( $_GET['action'] == 'delhost' ) {
	echo "about to delete host by id\n";
	//XXXX check for hostid being set and not stupid
        $query = 'DELETE FROM host WHERE id='.$_GET['hostid'].";";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
	showallhosts();
} else if  ( $_GET['action'] == 'addhost' ) {
	echo "about to add host\n";
        $query = "INSERT INTO host VALUES (DEFAULT,"."'".$_GET['hostname']."'".") returning *";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
	showallhosts();
} else if ( $_GET['action'] == 'showhost' ) {
	showhost($_GET['hostid']);
} else if ( $_GET['action'] == 'addinterface' ) {
	echo "about to add interface\n";
        $query = "INSERT INTO interface VALUES (DEFAULT,"."'".$_GET['intname']."',".$_GET['hostid'].") returning *";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
	showhost($_GET['hostid']);

} else if ( ( $_GET['action'] == 'addl2link' ) && isset($_GET['h1'],$_GET['h2']) ) {
	echo 'pick interfaces';
	$query = "select * from interface where host=".$_GET['h1'].";";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo '<form name="new link" action="cmd.php" method="get">';
        echo ' <input type="submit" value="Submit" />';
        echo ' <input type="hidden" name="action" value="addl2link" />';
	echo ' <select name="i1"> ';
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo ' <option value="'.$line['id'].'"> '.$line['name'].' </option>';
	}
	echo ' </select> ';
	$query = "select * from interface where host=".$_GET['h2'].";";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo ' <select name="i2"> ';
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo ' <option value="'.$line['id'].'"> '.$line['name'].' </option>';
	}
	echo ' </select> ';
	echo ' </form> ';
	
} else if ( ( $_GET['action'] == 'addl2link' ) && isset($_GET['i1'],$_GET['i2']) ) {
	$query = "insert into link values ( default, '".$_GET['link_name']."') returning id;";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$line = pg_fetch_array($result,null, PGSQL_ASSOC);

	$query = "insert into l2 values ( default, '".$line['id']."' , '".$_GET['i1']."' ) ;  insert into l2 values ( default, '".$line['id']."' , '".$_GET['i2']."' ) ; ";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        $line = pg_fetch_array($result,null, PGSQL_ASSOC);	

} else if ( $_GET['action'] == 'addl2link' ) {
	$query = "select * from host;";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo '<form name="new link" action="cmd.php" metho="get">';
	echo 'New Link Name: <input type="text" name="link_name" />';
        echo ' <input type="submit" value="Submit" />';
        echo ' <input type="hidden" name="action" value="addl2link" />';
	echo ' <select name="h1"> ';
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo ' <option value="'.$line['id'].'"> '.$line['name'].' </option>';
	}
        echo "</select>";
	$query = "select * from host;";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo ' <select name="h2"> ';
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo ' <option value="'.$line['id'].'"> '.$line['name'].' </option>';
	}
        echo "</select>";
        echo "</form>";
}
?>
