<html>
<head> <title> Iterator </title>
<meta http-equiv="refresh" content="1">
 </head>
<body>
<table>
<?php

$TENSION = 50;
$INTTENSION = 10;
$STICKLENGTH = 20;
$INTLENGTH = 2;
$REPULSION = 20;
$INTREPULSION = 1;
$MAXX = 100;
$MAXY = 100;
$MINX = 0;
$MINY = 0;
$dbconn = pg_connect("dbname=ip2") 
        or die('Could not connect: ' . pg_last_error());

#force on hosts
#all hosts repel
$result1 = pg_query ($dbconn, "SELECT * FROM physmaphost where peg='f' order by id;");
if (!$result1)
  {
    echo "An error occured.\n";
    exit;
  }
while ($arr = pg_fetch_array ($result1, NULL, PGSQL_ASSOC))
  {
    $FX = 0;
    $FY = 0;
##	echo "<br>id: ".$arr['id'];
//calculate forces between other hosts
    $result3 = pg_query ($dbconn, "SELECT * from physmaphost WHERE id <> ".$arr['id']);
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
	##echo "R".$arr2['id']." ".$FX." ".$FY."    ";
      }

	$query = "select  physmaphost.id as id, physmaphost.xpos as xpos, physmaphost.ypos as ypos from physmaphost right outer join\n";
        $query .=               "(select interface.id as i2id, interface.name, interface.host as i2host, q3.* from interface right outer join\n";
        $query .=                       "(select physicallink.id as l2bid, physicallink.link as l2blink, physicallink.interface as l2bint, q2.* from physicallink right outer join\n";
        $query .=                       "       (select physicallink.id as l2aid, physicallink.link as l2alink, physicallink.interface as l2aint, q1.intid, q1.intname from physicallink join\n";
        $query .=                       "               (select interface.id as intid ,interface.name as intname from interface where interface.host=".$arr['id'].") as q1\n";
        $query .=                       "       on physicallink.interface = q1.intid) as q2\n";
        $query .=               "       on physicallink.link = q2.l2alink and physicallink.id != q2.l2aid) as q3\n";
        $query .=               "on interface.id = q3.l2bint) as q4\n";
        $query .=       "on physmaphost.id = q4.i2host;\n";
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
    $result6 = pg_query ($dbconn, "update physmaphost set xpos = $NEWX, ypos = $NEWY where id = ".$arr['id']." ; ");
    if (!result6)
      {
        echo "Error saving results\n";
      }
}
#force on interfaces
$result1 = pg_query ($dbconn, "SELECT * FROM physmapinterface where peg='f' order by id;");
if (!$result1) {
    echo "An error occured.\n";
    exit;
}
while ($arr = pg_fetch_array ($result1, NULL, PGSQL_ASSOC)) {
    $FX = 0;
    $FY = 0;
	#force via link
	$result2 = pg_query ($dbconn, "SELECT pmi1.xpos, pmi1.ypos from physmapinterface pmi1, physicallink l1, physicallink l2 where l1.link = l2.link and l1.id <> l2.id and l1.interface = ".$arr['id']." and l2.interface = pmi1.id;");
	while ($arr2 = pg_fetch_array ($result2, NULL, PGSQL_ASSOC)) {
		$DX = ($arr['xpos'] - $arr2['xpos']);
		$DY = ($arr['ypos'] - $arr2['ypos']);
                $LN = sqrt(($DX*$DX)+($DY*$DY));
                $FX = $FX - $INTTENSION * ($LN - $STICKLENGTH + 2* $INTLENGTH) * $DX/$LN;
                $FY = $FY - $INTTENSION * ($LN - $STICKLENGTH + 2* $INTLENGTH) * $DY/$LN;
	}
#force to host
	$result3 = pg_query ($dbconn, "SELECT pmh1.xpos , pmh1.ypos from interface i1, physmaphost pmh1 where pmh1.id = i1.host and i1.id = ".$arr['id'].";");
	while ($arr3 = pg_fetch_array ($result3, NULL, PGSQL_ASSOC)) {
		$DX = ($arr['xpos'] - $arr3['xpos']);
		$DY = ($arr['ypos'] - $arr3['ypos']);
                $LN = sqrt(($DX*$DX)+($DY*$DY));
                $FX = $FX - $INTTENSION * ($LN - $INTLENGTH) * $DX/$LN;
                $FY = $FY - $INTTENSION * ($LN - $INTLENGTH) * $DY/$LN;
	}
#force to other interfaces. Note: only do other interfaces on this host

	$result4 = pg_query ($dbconn, "SELECT pmi1.xpos, pmi1.ypos from interface i1, interface i2, physmapinterface pmi1 where i1.id = pmi1.id and i1.host = i2.host and i2.id = ".$arr['id']." and i1.id<> i2.id;" );
	while ($arr4 = pg_fetch_array ($result4, NULL, PGSQL_ASSOC)) {
		$DX = ($arr['xpos'] - $arr4['xpos']);
		$DY = ($arr['ypos'] - $arr4['ypos']);
		$FX = $FX + (1 / ($DX * $DX + $DY * $DY)) * ($DX / sqrt ($DX * $DX + $DY * $DY)) * $INTREPULSION;
		$FY = $FY + (1 / ($DX * $DX + $DY * $DY)) * ($DY / sqrt ($DX * $DX + $DY * $DY)) * $INTREPULSION;
	}

	echo "<tr><td>ID ".$arr['id']."</td><td> FX $FX </td><td>FY $FY </td><td>xpos".$arr['xpos']."</td><td> ypos ".$arr['ypos']."</td></tr> \n";
    $NEWX = $arr['xpos'] + 0.01 * $FX;
    $NEWY = $arr['ypos'] + 0.01 * $FY;
    if ($NEWX > $MAXX) $NEWX=$MAXX;
    if ($NEWY > $MAXY) $NEWY=$MAXY;
    if ($NEWX < $MINX) $NEWX=$MINX;
    if ($NEWY < $MINY) $NEWY=$MINY;
    $result6 = pg_query ($dbconn, "update physmapinterface set xpos = $NEWX, ypos = $NEWY where id = ".$arr['id']." ; ");
    if (!result6)
      {
        echo "Error saving results\n";
      }
}
?></table></body > </html >
