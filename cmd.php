<?php
function footer(){
	echo '<a href="cmd.php?Action=showallhosts"> show all hosts </a>';
}
require("fn/showhostname.php");
require("fn/showintname.php");
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
	echo "<a href=cmd.php?action=showallhosts>showallhosts</a>";

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
	echo '<h1>';
	showhostname($_GET['hostid']);
	echo " ";
	showintname($_GET['intid']);
	echo '</h1>';
	#$query = " select i1.name as iname, b.name as aname, b.aid as aid, b.address as address, b.subnet as subnet, b.vlan,b.subintid as subintid, v.name as vname  from vlan v, interface i1 left join (SELECT s1.id as subintid, a1.id as aid, * from address a1 right outer join subint s1 on a1.subint = s1.id ) as b on i1.id = b.interface where v.id = b.vlan and i1.id=".$_GET['intid'].";";
	$query = "select c.*, i1.* from interface i1 left join ( select b.*, v.name as vname from vlan v right join	( SELECT s1.id as subintid, s1.interface, a1.id as aid, a1.name as aname, a1.address,  s1.vlan as vid from address a1 right outer join subint s1 on a1.subint = s1.id ) as b on v.id = b.vid ) as c on c.interface = i1.id where i1.id=".$_GET['intid'].";";
	#echo $query;
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$query2 = "select * from address where subint is NULL;";
	$result2 = pg_query($query2) or die('Query failed: ' . pg_last_error());
	$intchoice='<select name="addressid">';
	while ($line = pg_fetch_array($result2, null, PGSQL_ASSOC)) {
		$intchoice.='<option value="'.$line['id'].'"> '.$line['name']." ".$line['address'].' </option>';
	}
	$intchoice.='</select>';
	echo '<table border="1">';
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo  "<tr><td>".$line['subintid']."</td><td>".$line['iname']."</td><td>".$line['vname']."</td><td>".$line['address']."</td><td><a href=cmd.php?action=changevlan&subint=".$line['subintid'].">change vlan</a> </td><td><a href=cmd.php?action=delsubint&subint=".$line['subintid'].">delete subint</a></td><td>";
		if ($line['aid']) echo "<a href=cmd.php?action=unlinkaddress&addressid=".$line['aid'].">unlink address</a>";
		###choose address####
		echo '</td><td><form name="add address" action="cmd.php" method="get">';
		echo '<input type="submit" value="submit"/>';
		echo '<input type="hidden" name="action" value="linkaddress" />';
		echo '<input type="hidden" name="subint" value="'.$line['subintid'].'" />';
		echo $intchoice;
		echo '</form>';
		echo "</td></tr>\n";
	}
	echo '</table>';
	echo  '<a href="cmd.php?action=addsubint&intid='.$_GET['intid'].'"> Add subint </a>';
	echo '<form name="changeint" action="cmd.php" method="get">';
	echo 'change interface name: <input type="text" name="intname" /> ';
	echo '<input type="submit" value="Submit" />';
	echo '<input type="hidden" name="action" value="changeint" />';
	echo '<input type="hidden" name="intid" value="'.$_GET['intid'].'" />';
	echo "</form>";
	echo '<form name="addaddress" action="cmd.php" method="get">';
	echo 'New address: name: <input type="text" name="name" />';
	echo 'address: <input type="text" name="address" />';
	echo '<input type="hidden" name="action" value="addaddress" \>';
	echo '<input type="submit" value="Submit" />';
	echo '</form>';
	echo "<a href=cmd.php?action=showhost&hostid=".$_GET['hostid']."> back to host </a>";
} else if ( $_GET['action'] == 'unlinkaddress' ) {
	if (isset($_GET['addressid'])) {
		echo "about to unlink address \n";
		$query="update address set subint=NULL where id=".$_GET['addressid'].";";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	}
} else if ( $_GET['action'] == 'linkaddress' ) {
	if (isset($_GET['addressid'])) {
		echo "about to link address \n";
		$query="update address set subint=".$_GET['subint']." where id=".$_GET['addressid'].";";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	}
} else if ( $_GET['action'] == 'addaddress' ) {
	if (isset($_GET['address'])) {
		echo "add address\n";
		$query="insert into address values (default,'".$_GET['name']."',null,null,'".$_GET['address']."');";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	}
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
		echo "<a href=cmd.php?action=showallhosts>showallhosts</a>";
	}
} else if ( $_GET['action'] == 'dellink' ) {
	if (isset($_GET['linkid'])) {
		$query = "delete from physicallink where id=".$_GET['linkid'].";";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		echo "<h1> deleted link </h1>";
		showhost($_GET['hostid']);
		echo "<a href=cmd.php?action=showallhosts>showallhosts</a>";
	}
} else if ( $_GET['action'] == 'addsubint' ) {
	##XXX do vlan and and intid check
	echo "about to add subint\n";
	$query = "insert into subint values ( default,".$_GET['intid'].",default );";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo "added subint\n";
} else if ( $_GET['action'] == 'delsubint' ) {
	echo "about to delete subint\n";
	$query = "delete from subint where id=".$_GET['subint'].";"; 
	echo $query;
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	echo "deleted subint\n";
} else if ( $_GET['action'] == 'changevlan' ) {
	if (isset($_GET['vlan'],$_GET['subint'])) {
		$query = "update subint set vlan=".$_GET['vlan']." where  id=".$_GET['subint'].";";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	} else {	
		$query = "select * from vlan;";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		echo '<form name="changevlan" action="cmd.php" method="get">';
		echo '<input type="submit" value="Submit" />';
		echo '<input type="hidden" name="action" value="changevlan" />';
		echo '<input type="hidden" name="subint" value="'.$_GET['subint'].'" />';
		echo ' <select name="vlan"> ';
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			echo ' <option value="'.$line['id'].'"> '.$line['name'].":".$line['number'].' </option>';
		}
		echo "</form>";
	}
}
?>
