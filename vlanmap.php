<?php

$dbconn = pg_connect("dbname=ip2")
        or die('Could not connect: ' . pg_last_error());

header("Content-type: image/svg+xml");
print ('<?xml version="1.0" standalone="no"?>'."\n")
?>

<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="20cm" height="16cm" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink">
<desc>First attempt at a diagram</desc>

<?

$query = "select * from vlanmaphost vm1, host h1 where vm1.id = h1.id;";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
while ( $arr = pg_fetch_array($result, NULL , PGSQL_ASSOC) ) {
	print ('<g transform="translate('."$arr[xpos] $arr[ypos]".') rotate(0) ">');
	print ('<a xlink:href="cmd.php?action=showhost&amp;hostid='.$arr['id'].'">'."\n".'<circle cx="'."0".'" cy="'."0".'" r="'."0.5".'" fill="blue" stroke="blue" stroke-width="0.010"  /></a>');
	print ('</g>'."\n");
	print ('<text  font-size="1" x="'.$arr[xpos].'" y="'.$arr[ypos].'">'.$arr[name].'</text>');
}

$query = "select * from vlanmapvlan vm1, vlan v1 where vm1.id = v1.id;";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
while ( $arr = pg_fetch_array($result, NULL , PGSQL_ASSOC) ) {
	print ('<g transform="translate('."$arr[xpos] $arr[ypos]".') rotate(0) ">');
	print ('<a xlink:href="cmd.php?action=showint&amp;intid='.$arr['id']."&amp;hostid=".$arr['host'].'">'."\n".'<circle cx="'."0".'" cy="'."0".'" r="'."0.5".'" fill="green" stroke="green" stroke-width="0.010"  /></a>');
	print ('</g>'."\n");
	print ('<text  font-size="1" x="'.$arr[xpos].'" y="'.$arr[ypos].'">'.$arr[name].'</text>');
}


$query = "SELECT vm1.xpos as xpos1, vm1.ypos as ypos1, vh1.xpos as xpos2, vh1.ypos as ypos2 from vlanmapvlan vm1, subint s1, interface i1, vlanmaphost vh1 where vm1.id = s1.vlan and s1.interface = i1.id and i1.host = vh1.id;";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

while ( $arr = pg_fetch_array($result, NULL , PGSQL_ASSOC) ) {
	print ("<line x1=\"".$arr['xpos1']."\" y1=\"".$arr['ypos1']."\" x2=\"".$arr['xpos2']."\" y2=\"".$arr['ypos2']."\" stroke=\"red\" stroke-width=\"0.020\"/>\n");
}


?>
</svg>
