<html>
<head> <title> Iterator </title>
<meta http-equiv="refresh" content="1">
 </head>
<body>
<table>
<?php

$TENSION = 10;
$INTTENSION = 10;
$STICKLENGTH = 20;
$INTLENGTH = 2;
$REPULSION = 20;
$INTREPULSION = 0.1;
$MAXX = 100;
$MAXY = 100;
$MINX = 0;
$MINY = 0;
$dbconn = pg_connect("dbname=ip2") 
        or die('Could not connect: ' . pg_last_error());

#force on hosts
#all hosts repel
$result1 = pg_query ($dbconn, "SELECT * FROM vlanmaphost where peg='f' order by id;");
if (!$result1)
  {
    echo "An error occured.\n";
    exit;
  }
while ($arr = pg_fetch_array ($result1, NULL, PGSQL_ASSOC))
  {
    $FX = 0;
    $FY = 0;
//calculate forces between other hosts
    $result3 = pg_query ($dbconn, "SELECT * from vlanmaphost WHERE id <> ".$arr['id']);
        if (!$result3)
          {
            ##echo "An error occured.\n";
            exit;
          }
    while ($arr2 = pg_fetch_array ($result3, NULL, PGSQL_ASSOC))
      {
        $DX = ($arr['xpos'] - $arr2['xpos']);
        $DY = ($arr['ypos'] - $arr2['ypos']);
	#echo "<tr><td>".$DX."</td><td>".$DY."</td></tr>\n";
        $FX = $FX + (1 / ($DX * $DX + $DY * $DY)) * ($DX / sqrt ($DX * $DX + $DY * $DY)) * $REPULSION;
        $FY = $FY + (1 / ($DX * $DX + $DY * $DY)) * ($DY / sqrt ($DX * $DX + $DY * $DY)) * $REPULSION;
      }
$query = "SELECT vm1.xpos, vm1.ypos from vlanmapvlan vm1, subint s1, interface i1 where vm1.id = s1.vlan and s1.interface = i1.id and i1.host = ".$arr['id'].";";

	##echo $query;
	$result4 = pg_query($query) or die('Query failed: ' . pg_last_error());
	while ($arr2 = pg_fetch_array ($result4, NULL, PGSQL_ASSOC))
	{
		$DX = ($arr['xpos'] - $arr2['xpos']);
		$DY = ($arr['ypos'] - $arr2['ypos']);
		$LN = sqrt(($DX*$DX)+($DY*$DY));
		$FX = $FX - $TENSION * ($LN - $STICKLENGTH) * $DX/$LN;
		$FY = $FY - $TENSION * ($LN - $STICKLENGTH) * $DY/$LN;
		##echo "T".$arr2['id']." ".$FX." ".$FY." ";
	} 
    echo "<tr><td>ID ".$arr['id']."</td><td> FX $FX </td><td>FY $FY </td><td>xpos".$arr['xpos']."</td><td> ypos ".$arr['ypos']."</td></tr> \n";
    $NEWX = $arr['xpos'] + 0.01 * $FX;
    $NEWY = $arr['ypos'] + 0.01 * $FY;
    if ($NEWX > $MAXX) $NEWX=$MAXX;
    if ($NEWY > $MAXY) $NEWY=$MAXY;
    if ($NEWX < $MINX) $NEWX=$MINX;
    if ($NEWY < $MINY) $NEWY=$MINY;
    $result6 = pg_query ($dbconn, "update vlanmaphost set xpos = $NEWX, ypos = $NEWY where id = ".$arr['id']." ; ");
    if (!result6)
      {
        echo "Error saving results\n";
      }
}
#force on vlan
$result1 = pg_query ($dbconn, "SELECT * FROM vlanmapvlan where peg='f' order by id;");
if (!$result1) {
    echo "An error occured.\n";
    exit;
}
while ($arr = pg_fetch_array ($result1, NULL, PGSQL_ASSOC)) {
    $FX = 0;
    $FY = 0;
	#force via link
	$result2 = pg_query ($dbconn, "SELECT vh1.xpos, vh1.ypos from subint s1, interface i1, vlanmaphost vh1 where ".$arr['id']." = s1.vlan and s1.interface = i1.id and i1.host = vh1.id;" );
	while ($arr2 = pg_fetch_array ($result2, NULL, PGSQL_ASSOC)) {
		$DX = ($arr['xpos'] - $arr2['xpos']);
		$DY = ($arr['ypos'] - $arr2['ypos']);
                $LN = sqrt(($DX*$DX)+($DY*$DY));
                $FX = $FX - $TENSION * ($LN - $STICKLENGTH ) * $DX/$LN;
                $FY = $FY - $TENSION * ($LN - $STICKLENGTH ) * $DY/$LN;
	}

	echo "<tr><td>ID ".$arr['id']."</td><td> FX $FX </td><td>FY $FY </td><td>xpos".$arr['xpos']."</td><td> ypos ".$arr['ypos']."</td></tr> \n";
    $NEWX = $arr['xpos'] + 0.01 * $FX;
    $NEWY = $arr['ypos'] + 0.01 * $FY;
    if ($NEWX > $MAXX) $NEWX=$MAXX;
    if ($NEWY > $MAXY) $NEWY=$MAXY;
    if ($NEWX < $MINX) $NEWX=$MINX;
    if ($NEWY < $MINY) $NEWY=$MINY;
    $result6 = pg_query ($dbconn, "update vlanmapvlan set xpos = $NEWX, ypos = $NEWY where id = ".$arr['id']." ; ");
    if (!result6)
      {
        echo "Error saving results\n";
      }
}
?></table></body > </html >
