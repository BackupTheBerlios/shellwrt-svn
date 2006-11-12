<?

/*
#============================================================================
# Program: Devices-Write.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 29/04/05	initial version.
# 17/03/06	new SQL query support
*/

$bg1	= "BB6666";
$bg2	= "CC7777";
$btag	= "";
$nocache= 0;
$calendar= 1;
$refresh = 0;

include_once ("inc/header.php");
include_once ('inc/libdev.php');

$_POST = sanitize($_POST);
$sta = isset( $_POST['sta']) ? $_POST['sta'] : "";
$stb = isset( $_POST['stb']) ? $_POST['stb'] : "";
$ina = isset( $_POST['ina']) ? $_POST['ina'] : "";
$inb = isset( $_POST['inb']) ? $_POST['inb'] : "";
$opa = isset( $_POST['opa']) ? $_POST['opa'] : "";
$opb = isset( $_POST['opb']) ? $_POST['opb'] : "";
$cop = isset( $_POST['cop']) ? $_POST['cop'] : "";
$cmd = isset( $_POST['cmd']) ? $_POST['cmd'] : "";
$int = isset( $_POST['int']) ? $_POST['int'] : "";
$sim = isset( $_POST['sim']) ? $_POST['sim'] : "";
$scm = isset( $_POST['scm']) ? $_POST['scm'] : "";
$con = isset( $_POST['con']) ? $_POST['con'] : "";
$pwd = isset( $_POST['pwd']) ? $_POST['pwd'] : "";
$sint = isset( $_POST['sint']) ? $_POST['sint'] : "";
$eint = isset( $_POST['eint']) ? $_POST['eint'] : "";
$emod = isset( $_POST['emod']) ? $_POST['emod'] : "";
$smod = isset( $_POST['smod']) ? $_POST['smod'] : "";
$icfg = isset( $_POST['icfg']) ? $_POST['icfg'] : "";
?>
<h1>Device Write</h1>

<form method="post" name="list" action="<?=$_SERVER['PHP_SELF']?>" name="cfg">
<table bgcolor=#000000 <?=$tabtag?> >
<tr bgcolor=#<?=$bg1?>><th width=80 rowspan=3><a href=<?=$_SERVER['PHP_SELF'] ?>><img src=img/32/wrte.png border=0 title="sends commands or configures devices. Warning: Use simulate first!"></a></th>
<th valign=top>Condition A<p>
<SELECT size=1 name="ina">
<? selectbox("devices",$ina);?>
</SELECT>
<SELECT size=1 name="opa">
<? selectbox("oper",$opa);?>
</SELECT>
<p><a href="javascript:show_calendar('list.sta');"><img src="img/cal.png" border=0 hspace=8></a>
<input type="text" name="sta" value="<?=$sta?>" size="25">
</th>
<th valign=top>Combination<p>
<SELECT size=1 name="cop">
<? selectbox("comop",$cop);?>
</SELECT>
</th>
<th valign=top>Condition B<p>
<SELECT size=1 name="inb">
<? selectbox("devices",$inb);?>
</SELECT>
<SELECT size=1 name="opb">
<? selectbox("oper",$opb);?>
</SELECT>
<p><a href="javascript:show_calendar('list.stb');"><img src="img/cal.png" border=0 hspace=8></a>
<input type="text" name="stb" value="<?=$stb?>" size="25">
</th>

</tr><tr bgcolor=#<?=$bg2?>>

<th valign=top colspan=2>Commands / Configuration<p>
<textarea rows="6" name="cmd" cols="60"><?=$cmd?></textarea>
</th>

<th valign=top>Interface Configuration<p>
<select size=1 name="int">
	<option value="">----------------
	<option value="Et" <? if($int == "Et"){echo "selected";} ?>>Ethernet
	<option value="Fa" <? if($int == "Fa"){echo "selected";} ?>>Fast Ethernet
	<option value="Gi" <? if($int == "Gi"){echo "selected";} ?>>Gigabit Ethernet
</select>
 from <input type="text" size="2"name="smod" value=<?=($smod)? $smod:'0'?> name="smod" OnFocus=select();>
 /    <input type="text" size="2" name="sint" value=<?=($sint)? $sint:'1'?> OnFocus=select();>
 to   <input type="text" size="2" name="emod" value=<?=($emod)? $emod:'0'?> OnFocus=select();>
 /    <input type="text" size="2" name="eint" value=<?=($eint)? $eint:'1'?> OnFocus=select();>
<br>
<textarea rows="4" name="icfg" cols="44"><?=$icfg?></textarea>
</th>

</tr><tr bgcolor=#<?=$bg1?>>

<th valign=top colspan=3>
<input type="submit" value="Simulate" name="sim">
<?
	if(preg_match("/adm/",$_SESSION['group']) ){
		?>
		<input type="submit" value="Send Commands" name="scm">
		<input type="submit" value="Configure" name="con">
		<?
		if ( stristr('i',$guiauth) ){
			?>
			<input type="password" value="<?=$pwd?>" name="pwd">
			<?
		}
	}
?>
</th>

</tr></table>
</form>
<p>

<?

if($ina){

	$nres = 0;

	$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
	$query	= GenQuery('devices','s','*','','',array($ina,$inb),array($opa,$opb),array($sta,$stb),array($cop) );
	$res	= @DbQuery($query,$link);
	if($res){
		$prevos = "";
		$oserr = 0;
		while( ($d = @DbFetchRow($res)) ){
			if($d[17]){
				$devip[$d[0]] = long2ip($d[1]);
				if ($prevos and $prevos != $d[8]){$oserr = 1;}
				$prevos = $d[8];
				$devos[$d[0]] = $d[8];
				$devbi[$d[0]] = $d[9];
				$devpo[$d[0]] = $d[16];
				$devlo[$d[0]] = $d[17];
				$nres++;
			}else{
				echo "<h4>No login for $d[0]!</h4>\n";
			}
		}
		$cf = "log/cmd_$_SESSION[user]";
		if ($oserr){echo "<h4>OS $n1rmsg</h4>";die;}
	}else{
		print @DbError($link);
	}
	if(!isset($devip) ){echo $resmsg;die;}
	if($sim){
		Buildcmd();

		echo "<h2>Devices</h2>\n";
		echo "<table bgcolor=#666666 $tabtag>\n";
		echo "<tr bgcolor=#$bg2><th colspan=2>Device</th><th>OS</th><th>Bootimage</th><th>Login</th><th>IP Address</th><th>Port</th></tr>\n";
		$row = 0;
		foreach ($devip as $dv => $ip){
			if ($row % 2){$bg = $bga; $bi = $bia; }else{$bg = $bgb; $bi = $bib;}
			$row++;
			echo "<tr bgcolor=#$bg><td align=right>$row</td><th>$dv</th><td>$devos[$dv]</td><td>$devbi[$dv]</td><td>$devlo[$dv]</td><td><a href=telnet://$ip>$ip</a></td><td align=center>$devpo[$dv]</td></tr>\n";
		}
		echo "</table><table bgcolor=#666666 $tabtag >\n";
		echo "<tr bgcolor=#$bg2><td>$nres results using $query</td></tr></table>\n";
	}else{
		if(preg_match("/adm/",$_SESSION['group']) ){
			$fd =  @fopen("log/cmd_$_SESSION[user]","w") or die ("can't create log/cmd_$_SESSION[user]");
			fwrite($fd,Buildcmd($con) );
			fclose($fd);

			echo "<h2>Devices</h2></center><p><ul><ul><ol>\n";
			foreach ($devip as $dv => $ip){
				flush();
				if($devpo[$dv] == 22){
					echo "<li><b>$dv</b> <a href=ssh://$ip>$ip</a> SSH ignored...";
				}else{
					echo "<li><b>$dv</b> <a href=telnet://$ip>$ip</a> ";
					$cred = ( stristr('i',$guiauth) )?"$_SESSION[user] $pwd":"$devlo[$dv] dummy";
					$log = `perl inc/Devsend.pl $ip $devpo[$dv] $cred $devos[$dv] log/cmd_$_SESSION[user]`;
					echo $log;
					echo " <a href=\"$cf-$ip.log\" target=window><img src=img/16/book.png border=0 title='view output'></a>";
				}
			}
			echo "</ol></ul></ul>";
		}else{
			echo $nokmsg;
		}
	}
}

function Buildcmd($cfgandwrite=0){

	global $cmd, $sint, $eint, $smod, $emod, $int, $icfg;

	$config = "";
	if($cfgandwrite){$config .= "conf t\n";}
	$config .= "$cmd\n";
	if($int){
		for($m = $smod;$m <= $emod;$m++){
			for($i = $sint;$i <= $eint;$i++){
				$config .= "int $int $m/$i\n";
				$config .= "$icfg\n";
			}
		}
	}
	if($cfgandwrite){$config .= "end\nwrite mem\n";}

	echo "<center><h2>Commands</h2>\n";
	echo "<table bgcolor=#000000 bgcolor=#000000 cellspacing=1 cellpadding=8 border=0 width=80%><tr bgcolor=#EEEEEE><td align=left><pre>\n";
	echo $config;
	echo "</pre></td></tr></table>\n";

	return $config;
}

include_once ("inc/footer.php");
?>
