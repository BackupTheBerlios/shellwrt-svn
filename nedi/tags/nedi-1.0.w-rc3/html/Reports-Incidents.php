<?
/*
#============================================================================
# Program: Reports-Incidents.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 27/07/06	initial version.
*/

error_reporting(E_ALL ^ E_NOTICE);

$bg1	= "eeccaa";
$bg2	= "ffddaa";
$btag	= "";
$nocache= 0;
$calendar= 0;
$refresh = 0;

include_once ("inc/header.php");
include_once ('inc/libdev.php');
include_once ('inc/libmon.php');

$_GET = sanitize($_GET);
$rep = isset($_GET['rep']) ? $_GET['rep'] : array();
$lim = isset($_GET['lim']) ? $_GET['lim'] : 10;
$ord = isset($_GET['ord']) ? "checked" : "";

$cpos = strpos($locformat, "c");
$bpos = strpos($locformat, "b");
?>
<h1>Incident Reports</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table bgcolor=#000000 <?=$tabtag?> >
<tr bgcolor=#<?=$bg1?>><th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src=img/32/dbmb.png border=0 title="Statistics based on device monitoring">
</a></th>
<th>Select Report(s)</th>
<th>
<select multiple name="rep[]" size=4>
<OPTION value="dev" <? if(in_array("dev",$rep)){echo "selected";} ?> >Devices
<OPTION value="cat" <? if(in_array("cat",$rep)){echo "selected";} ?> >Categories
<OPTION value="cal" <? if(in_array("cal",$rep)){echo "selected";} ?> >Calendar
</SELECT></th>
</th>
<th>Limit
<SELECT size=1 name="lim">
<? selectbox("limit",$lim);?>
</SELECT>
</th>
<th>
<INPUT type="checkbox" name="ord" <?=$ord?> > alternative order
</th>
</SELECT></th>

<th width=80><input type="submit" name="shw" value="Show"></th>
</tr></table></form><p>
<?
if($rep){
$now = getdate();
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('incidents');
$res	= @DbQuery($query,$link);
if($res){
	$tinc = 0;
	while( ($i = @DbFetchRow($res)) ){
		$numdv[$i[2]]++;
		$numcat[$i[8]]++;
		$indev[$i[0]] = $i[2];
		$insta[$i[0]] = $i[4];
		$incat[$i[0]] = $i[8];
		if($i[5]){
			$inend[$i[0]] = $i[5];
		}else{
			$inend[$i[0]] = $now[0];
		}
		$tinc++;
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
	die;
}
$query	= GenQuery('devices');
$res	= @DbQuery($query,$link);
if($res){
	$tdev = 0;
	while( ($d = @DbFetchRow($res)) ){
		$dip[$d[0]]  = long2ip($d[1]);
		$dtyp[$d[0]] = $d[3];
		$dos[$d[0]]  = $d[8];
		$dcon[$d[0]] = $d[11];
		$dico[$d[0]] = $d[18];
		$tdev++;
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
	die;
}

if ( in_array("dev",$rep) ){
?>
<h3>Devices</h3><p>
<table bgcolor=#666666 <?=$tabtag?> ><tr bgcolor=#<?=$bg2?>>
<th colspan=3><img src=img/32/dev.png><br>Device</th>
<th><img src=img/32/umgr.png><br>Contact</th>
<th width=50%><img src=img/32/bomb.png><br>Incidents</th>
</tr>
<?
	if($ord){
		asort($numdv);
	}else{
		arsort($numdv);
	}
	$row = 0;
	foreach($numdv as $dv => $ndi){
		if ($row % 2){$bg = $bga; $bi = $bia; }else{$bg = $bgb; $bi = $bib;}
		$row++;
		$img = $dico[$dv];
		$ud  = rawurlencode($dv);
		$ibar = Bar($ndi,3);
		echo "<tr bgcolor=#$bg>";
		echo "<th bgcolor=#$bi><a href=Devices-Status.php?dev=$ud><img src=img/dev/$img.png border=0 title=\"$dtyp[$dv]\"><p></a>$dv</th>\n";
		echo "<td><a href=telnet://$dip[$dv]>$dip[$dv]</td><td>$dos[$dv]</td><td>$dcon[$dv]</td><td>$ibar $ndi</td></tr>\n";
		if($row == $lim){break;}
	}
	echo "</table><table bgcolor=#666666 $tabtag >\n";
	echo "<tr bgcolor=#$bg2><td>$row of $tdev devices with incidents shown</td></tr></table>\n";
}

if ( in_array("cat",$rep) ){
?>
<h3>Categories</h3><p>
<table bgcolor=#666666 <?=$tabtag?> ><tr bgcolor=#<?=$bg2?>>
<th colspan=2><img src=img/32/find.png><br>Category</th>
<th><img src=img/32/form.png><br>Occurrence</th>
</tr>
<?
	if($ord){
		ksort($numcat);
	}else{
		arsort($numcat);
	}
	$row = 0;
	foreach($numcat as $c => $nc){
		if ($row % 2){$bg = $bga; $bi = $bia; }else{$bg = $bgb; $bi = $bib;}
		$row++;
		$cimg = Cimg($c);
		$cbar = Bar($nc,3);
		echo "<tr bgcolor=#$bg><th bgcolor=$bi width=80><img src=img/16/$cimg.png></th>";
		echo "<td><a href=Monitoring-Incidents.php?cat=$c>$icat[$c]</a></td><td>$cbar $nc times</td></tr>\n";
		if($row == $lim){break;}
	}
	echo "</table><table bgcolor=#666666 $tabtag >\n";
	echo "<tr bgcolor=#$bg2><td>$row categories shown</td></tr></table>\n";
}

if ( in_array("cal",$rep) ){

?>
<h3>Incident Calendar <?=$now['year']?></h3><p>
<table bgcolor=#666666 <?=$tabtag?> ><tr bgcolor=#<?=$bg2?>><td></td>
<?
	for($d=1;$d < 32;$d++){
		echo "<th>$d</th>";
	}
	$row = 0;
	$prevm = "";
	for($t = strtotime(date('1/1/Y'));$t < $now[0];$t += 86400){
		$then = getdate($t);
		if($prevm != $then['month']){
			if ($row % 2){$bg = $bga; $bi = $bia; }else{$bg = $bgb; $bi = $bib;}
			$row++;
			echo "</tr>\n<tr bgcolor=#$bg><th bgcolor=#$bi width=80>$then[month]</th>";
		}
		asort($insta);
		foreach($insta as $id => $st){
			if($st < ($t + 86400) ){
				if($inend[$id] < $t){
					unset($insta[$id]);
					unset($inend[$id]);
				}else{
					$curi[$t][] = $id;
				}
			}
		}
		if($then['wday'] == 0 or $then['wday'] == 6){
			$cl = "class=red";
		}else{
			$cl = "class=blu";
		}
		echo "<td $cl align=center>";
		if(isset($curi[$t]) ){
			if($ord){
				foreach($curi[$t] as $id){
					$cimg = Cimg($incat[$id]);
					$tit  = $indev[$id] . " had " .$icat[$incat[$id]] . " on $then[weekday]";
					echo "<a href=Monitoring-Incidents.php?id=$id>";
					echo "<img src=img/16/$cimg.png border=0 title=\"$tit\"></a>";
				}
			}else{
				$ninc = count($curi[$t]);
				$tit  = "$ninc on $then[weekday]";
				if($ninc == 1){
					echo "<img src=img/16/bomb.png border=0 title=\"$tit\"></a>";
				}elseif($ninc < 10){
					echo "<img src=img/16/impt.png border=0 title=\"$tit\"></a>";
				}else{
					echo "<img src=img/16/bstp.png border=0 title=\"$tit\"></a>";
				}
			}
		}else{
			echo substr($then['weekday'],0,1);
		}
		echo "</td>";
		$prevm = $then['month'];
	}
	echo "</table>";
}

}
include_once ("inc/footer.php");
?>
