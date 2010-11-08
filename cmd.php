<?php
function footer(){
	echo '<a href="cmd.php?Action=showallhosts"> show all hosts </a>';
}
require("fn/showhostname.php");
require("fn/showallhosts.php");
require("fn/showhost.php");
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
#DS working on this one
} else if ( ( $_GET['action'] == 'addlink' ) && isset($_GET['i1']) ) {

	$query = "SELECT i1.id as intid, i1.name as intname, h1.name as hostname from interface i1, host h1 where h1.id = i1.host and i1.id not in (select interface from physicallink );";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());

        echo '<form name="new link" action="cmd.php" method="get">';
        echo ' <input type="submit" value="Submit" />';
        echo ' <input type="hidden" name="action" value="addlink" />';
        echo ' <input type="hidden" name="i1" value="'.$_GET['i1'].'" />';

        echo ' <select name="i2"> ';
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
                echo ' <option value="'.$line['intid'].'"> '.$line['hostname'].":".$line['intname'].' </option>';
        }
        echo ' </select> ';
        echo ' </form> ';

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
