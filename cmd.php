<?php
function footer(){
echo '<a href="cmd.php?Action=showallhosts"> show all hosts </a>';
}

function showhostname($hostid){
$query = "select name from host where id=".$hostid.";";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
$line = pg_fetch_array($result,0,PGSQL_ASSOC);
echo $line['name'];
return $line['name'];
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
	$query = "select  host.name as linkhostname, host.id as linkhostid, q4.name as linkinterface, q4.intname, q4.intid  as intid, q4.l2aid from host right outer join\n";
	$query .=		"(select interface.id as i2id, interface.name, interface.host as i2host, q3.* from interface right outer join\n";
	$query .=			"(select physicallink.id as l2bid, physicallink.link as l2blink, physicallink.interface as l2bint, q2.* from physicallink right outer join\n";
	$query .=			"	(select physicallink.id as l2aid, physicallink.link as l2alink, physicallink.interface as l2aint, q1.intid, q1.intname from physicallink right outer join\n";
	$query .=			"		(select interface.id as intid ,interface.name as intname from interface where interface.host=".$hostid.") as q1\n";
	$query .=			"	on physicallink.interface = q1.intid) as q2\n";
	$query .=		"	on physicallink.link = q2.l2alink and physicallink.id != q2.l2aid) as q3\n";
	$query .=		"on interface.id = q3.l2bint) as q4\n";
	$query .=	"on host.id = q4.i2host;\n";
#	echo $query."\n";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo "<table>\n";
	echo "<tr><td>interface</td><td>linked host</td><td>linked interface</td></tr>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "\t<tr>\n";
		echo "<td><a href=cmd.php?action=showint&intid=".$line['intid']."&hostid=".$hostid.">".$line['intname']."</a></td><td><a href=cmd.php?action=showhost&hostid=".$line['linkhostid'].">".$line['linkhostname']."</a></td><td>".$line['linkinterface']."</td><td><a href=cmd.php?action=dellink&linkid=".$line['l2aid']."&hostid=".$hostid.">delete link</a></td><td><a href=cmd.php?action=delint&intid=".$line['intid']."&hostid=".$hostid.">delete interface</a></td></td>\n";
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
	echo "<a href=cmd.php?action=showallhosts> show all hosts </a>";
} else if  ( $_GET['action'] == 'addhost' ) {
	echo "about to add host\n";
        $query = "INSERT INTO host VALUES (DEFAULT,"."'".$_GET['hostname']."'".") returning *";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
	showallhosts();
} else if ( $_GET['action'] == 'showhost' ) {
	echo "<h1>\n";
	showhostname($_GET['hostid']);
	echo "</h1>\n";
	showhost($_GET['hostid']);
	echo "<a href=cmd.php?action=showallhosts>showallhosts</a>";
} else if ( $_GET['action'] == 'addinterface' ) {
	echo "about to add interface\n";
        $query = "INSERT INTO interface VALUES (DEFAULT,"."'".$_GET['intname']."',".$_GET['hostid'].") returning *";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
	showhost($_GET['hostid']);

} else if ( ( $_GET['action'] == 'addlink' ) && isset($_GET['h1'],$_GET['h2']) ) {
	echo 'pick interfaces';
	$query = "select * from interface where host=".$_GET['h1'].";";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo '<form name="new link" action="cmd.php" method="get">';
        echo ' <input type="submit" value="Submit" />';
        echo ' <input type="hidden" name="action" value="addlink" />';
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
	
} else if ( ( $_GET['action'] == 'addlink' ) && isset($_GET['i1'],$_GET['i2']) ) {
	$query = "insert into link values ( default, '".$_GET['link_name']."') returning id;";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$line = pg_fetch_array($result,null, PGSQL_ASSOC);

	$query = "insert into physicallink values ( default, '".$line['id']."' , '".$_GET['i1']."' ) ;  insert into physicallink values ( default, '".$line['id']."' , '".$_GET['i2']."' ) ; ";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        $line = pg_fetch_array($result,null, PGSQL_ASSOC);	

} else if ( $_GET['action'] == 'addlink' ) {
	$query = "select * from host;";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo '<form name="new link" action="cmd.php" metho="get">';
	echo 'New Link Name: <input type="text" name="link_name" />';
        echo ' <input type="submit" value="Submit" />';
        echo ' <input type="hidden" name="action" value="addlink" />';
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
} else if ( $_GET['action'] == 'showint' ) {
	$query = "select * from interface where id=".$_GET['intid'].";";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo  $line['name'];
	}
	echo '<form name="changeint" action="cmd.php" method="get">';
	echo 'change interface name: <input type="text" name="intname" /> ';
	echo '<input type="submit" value="Submit" />';
	echo '<input type="hidden" name="action" value="changeint" />';
	echo '<input type="hidden" name="intid" value="'.$_GET['intid'].'" />';
	echo "</form>";
	echo "<a href=cmd.php?action=showhost&hostid=".$_GET['hostid']."> back to host </a>";
} else if ( $_GET['action'] == 'changeint' ) {
	if (isset($_GET['intid'],$_GET['intname'])) {
		echo "about to update interface\n";
		$query = "update interface SET name='".$_GET['intname']."' where id=".$_GET['intid'].";";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	}
} else if ( $_GET['action'] == 'delint' ) {
	if (isset($_GET['intid'])) {
		$query = "delete from interface where id=".$_GET['intid'].";";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		echo "<h1> deleted interface </h1>";
		showhost($_GET['hostid']);
	}
} else if ( $_GET['action'] == 'dellink' ) {
	if (isset($_GET['linkid'])) {
		$query = "delete from physicallink where id=".$_GET['linkid'].";";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		echo "<h1> deleted link </h1>";
		showhost($_GET['hostid']);
	}
}
?>
