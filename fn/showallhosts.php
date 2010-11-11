<?php
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
	echo "<a href=physmap.php>Physical Map</a>";
	echo "<a href=iterator.php>Physical Map Iterator</a>";
}
?>
