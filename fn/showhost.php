<?php
function showhost($hostid){
        $query = "select  host.name as linkhostname, host.id as linkhostid, q4.name as linkinterface, q4.intname, q4.intid  as intid, q4.l2aid from host right outer join\n";
        $query .=               "(select interface.id as i2id, interface.name, interface.host as i2host, q3.* from interface right outer join\n";
        $query .=                       "(select physicallink.id as l2bid, physicallink.link as l2blink, physicallink.interface as l2bint, q2.* from physicallink right outer join\n";
        $query .=                       "       (select physicallink.id as l2aid, physicallink.link as l2alink, physicallink.interface as l2aint, q1.intid, q1.intname from physicallink right outer join\n";
        $query .=                       "               (select interface.id as intid ,interface.name as intname from interface where interface.host=".$hostid.") as q1\n";
        $query .=                       "       on physicallink.interface = q1.intid) as q2\n";
        $query .=               "       on physicallink.link = q2.l2alink and physicallink.id != q2.l2aid) as q3\n";
        $query .=               "on interface.id = q3.l2bint) as q4\n";
        $query .=       "on host.id = q4.i2host;\n";
#       echo $query."\n";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());
        echo "<table>\n";
        echo "<tr><td>interface</td><td>linked host</td><td>linked interface</td></tr>\n";
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
                echo "\t<tr>\n";
                echo "<td><a href=cmd.php?action=showint&intid=".$line['intid']."&hostid=".$hostid.">".$line['intname']."</a></td><td><a href=cmd.php?action=showhost&hostid=".$line['linkhostid'].">".$line['linkhostname']."</a></td><td>".$line['linkinterface']."</td><td><a href=cmd.php?action=dellink&linkid=".$line['l2aid']."&hostid=".$hostid.">delete link</a></td><td><a href=cmd.php?action=delint&intid=".$line['intid']."&hostid=".$hostid.">delete interface</a></td><td><a href=cmd.php?action=addlink&i1=".$line['intid'].">newlink</a></td>\n";
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
?>
