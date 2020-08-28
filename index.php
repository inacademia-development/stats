<?php

$start_time = floatval(explode(" ", microtime())[1])+floatval(explode(" ", microtime())[0]);

include('queries.php');
include('auth.php');

$qry_debug = [];
$showdebug = false;

function asTable($res) {
    $res->data_seek(0);

	$row_cnt = $res->num_rows;
    
	if (($res->num_rows) == 0) { 
		$ret = "Result set has ".$row_cnt." rows.\n";
	}
	else
	{
		$ret = "<table>";
		$header = false;
		while ($row = $res->fetch_assoc()) {
			if (!$header) {
				$ret .= "<tr>";
				foreach(array_keys($row) as $name) {
					$ret .= "<td><b>" . $name . "</b></td>";
				}
				$ret .= "</tr>";
				$header = true;
			}
			$ret .= "<tr>";
			foreach($row as $column) {
				$ret .= "<td>" . $column . "</td>";
			}
			$ret .= "</tr>";
		}
		$ret .= "</table>";
	}
    return $ret;
}

function asString($res) {
    $res->data_seek(0);

	$row_cnt = $res->num_rows;
    
    $ret = "";
	if (($res->num_rows) == 0) { 
		$ret .= "Unknown\n";
	}
	else
	{
		while ($row = $res->fetch_assoc()) {
            foreach($row as $column) {
				$ret = $column;
			}
		}
	}
    return $ret;
}

function asSelect($res, $type, $selected="") {
    $res->data_seek(0);

	$row_cnt = $res->num_rows;
    
    if ($type == 'idp') {
        $ret = "<select name='$type' onchange='openURL(null, null, null, null, null, this.value);' style='width: 250px;'>";
    } else {
        $ret = "<select name='$type' onchange='openURL(null, null, null, null, this.value, null);' style='width: 250px;'>";
    }
    // Add default
	$ret .= "<option value=>Use all ...</option>";
    
    while ($row = $res->fetch_assoc()) {
        foreach($row as $column) {
			if ( ($selected != "") && (strpos($column, $selected)) ) {
                $ret .= str_replace("option value=" , "option selected value=", $column);
            } else {
                $ret .= $column;
            }
            
		}
	}
    $ret .= "</select>";
    return $ret;
}

function showTableHeader($t, $p, $tab, $filter, $explain="", $sp="", $idp="") {

	$dspname="";
    if (strlen($explain) > 0) {
        $explain = " for " .$explain;
    }

    $entityName = "";
    if (strlen($idp) > 0) {
        $entityName .= "institution " . asString(getDisplayname($idp, 'idp'));
    }
    if ((strlen($idp) > 0) && (strlen($sp) > 0)) {
        $entityName .= " and ";
    }
    if (strlen($sp) > 0) {
        $entityName .= "merchant " . asString(getDisplayname($sp, 'sp'));
    }

    if (strlen($entityName) > 0) {
        $entityName = " for " .$entityName;
    }

	switch($tab) {
		case 'UniqueSessions': 
			$dspname="Unique sessions\n"; 
			break;
		case 'IdPSessions':
			$dspname="Sessions per IdP\n";
			break;
		case 'SPSessions':
			$dspname="Sessions per Merchant\n";
			break;
		case 'Domains':
			$dspname="Domains\n";
			break;
		case 'Country':
			$dspname="Countries\n";
			break;
		case 'Affiliaton':
			$dspname="Affiliatons\n";
			break;
		case 'Institutions':
			$dspname="Institutions\n";
			break;
		case 'Merchants':
			$dspname="Merchants\n";
			break;
		case 'Logs':
			$dspname="Total transactions\n";
			break;
	}
    
    $explain_csv = urlencode($dspname." " .$explain." " .$entityName);
    
    $tableHeader = "<table>";
    $tableHeader .= "<tr><td align='left' class='thHeader'>";
    $tableHeader .= "<h2>$dspname $explain $entityName</h2>";
    $tableHeader .= "</td><td align='right'  class='thHeader'>";
    $tableHeader .= "[<a target='_blank' href='acsv.php?expl=".$explain_csv."&t=".$t."&p=".$p ."&tab=".$tab."&f=".$filter."&sp=".$sp."&idp=".$idp."&et=".$et."'>export CSV</a>]";
    $tableHeader .= "</td></tr></table>";
    
    return $tableHeader;
}

// Make sure we pick up incoming vars and always have proper defaults

$t = isset($_GET['t'])?$_GET['t']:time();
$et = isset($_GET['et'])?$_GET['et']:"";
$p = isset($_GET['p'])?$_GET['p']:"month";
$filter = isset($_GET['f'])?$_GET['f']:"";
$tab = isset($_GET['tab'])?$_GET['tab']:"UniqueSessions";
$sp = isset($_GET['sp'])?$_GET['sp']:"";
$idp = isset($_GET['idp'])?$_GET['idp']:"";


// Calculate start and end datetime and headers we need to show
$date = getdate($t);
$d = $date['mday'];
$m = $date['mon'];
$y = $date['year'];
$wd = ($date['wday']+6)%7; // week starts on monday

// All days start at 00:00:00
$s = strtotime("$d-$m-$y");

$explain = "";
switch($p) {
    case 'day':
        $e = strtotime("+1 day", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $explain = date("D M j, Y", $s);
        $explain_csv = "$y-$m-${d}_InAcademia_" . date("l", $s);
        break;
    case 'week':
        $s = strtotime("-$wd days", $s);
        $e = strtotime("+1 week", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $explain = "Week " .date("W", $s) . " (" . date("Y", $s) . ")";
        $explain_csv = "$y-$m-${d}_InAcademia_w" . date("W", $s);
        break;
    case 'month':
        $s = strtotime("1-$m-$y", $s);
        $e = strtotime("+1 month", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $explain = date("F", $s) . " (" . date("Y", $s) . ")";
        $explain_csv = "$y-${m}_InAcademia_" . date("F", $s);
        break;
    case 'year':
        $s = strtotime("1-1-$y", $s);
        $e = strtotime("+1 year", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $explain = date("Y", $s);
        $explain_csv = "${y}_InAcademia_" . date("Y", $s);
        break;
}

// Day ends at 23:59:59
$e -= 1;


?>


<html>
<head>
<link rel="stylesheet" type="text/css" href="inacademia_stats.css">
<script src="inacademia_stats.js"></script> 
</head>

<?php
echo "<body onLoad='openTab(event, &quot;".$tab."&quot;)'>";
echo "<div><table><tr><td>";
echo "<form method='post' name='state_form' id='state_form'>";
echo "<input type='hidden' name='time' value='".$t."'>";
echo "<input type='hidden' name='endtime' value='".$et."'>";
echo "<input type='hidden' name='tab' value='".$tab."'>";
echo "<input type='hidden' name='filter' value='".$filter."'>";
echo "<input type='hidden' name='interval' value='".$p."'>";
echo "<input type='hidden' name='idp' value='".$idp."'>";
echo "<input type='hidden' name='sp' value='".$sp."'>";
echo "<input type='hidden' name='q' value='$query'>";
echo "</form>";

echo "<a name=top><h1>InAcademia Stats</h1></a>";

echo "<div class='menutab'>";
  echo "<div class='menutab_element'>";
  echo "<button class='tablinks'><b>Interval:</b>   </button>";
  echo "</div>";

  echo "<div class='menutab_element' style='width:300px'>";
  echo "<button class='tablinks' onclick='openURL(null,null,null,&quot;day&quot;,&quot;$sp&quot;,&quot;$idp&quot;)'> Day </button>";
  echo "<button class='tablinks' onclick='openURL(null,null,null,&quot;week&quot;,&quot;$sp&quot;,&quot;$idp&quot;)'>Week</button>";
  echo "<button class='tablinks' onclick='openURL(null,null,null,&quot;month&quot;,&quot;$sp&quot;,&quot;$idp&quot;)'>Month</button>";
  echo "<button class='tablinks' onclick='openURL(null,null,null,&quot;year&quot;,&quot;$sp&quot;,&quot;$idp&quot;)'>Year</button>";
  echo "</div>";
  echo "<div class='menutab_element' style='width:300px'>";
  echo "<button class='tablinks' onclick='openURL(" . strtotime('-1 ' . $p, $t) . ",null,null,null)'>&lt;&lt;</button>";
  echo "<button class='tablinks' onclick='openURL(".strtotime("now").",null,null,null)'>Today</button>";
  echo "<button class='tablinks' onclick='openURL(" . strtotime('+1 ' . $p, $t) . ",null,null,null)'>&gt;&gt;</button>";
  echo "</div>";
/*  
  echo "<div class='menutab_element' style='width:300px'>";
  echo "<form id=time_form>";
  echo "<input type=text name=st value='$d-$m-$y'>";
  echo "<input type=text name=et value=''>";  
  echo "<button class='tablinks' onclick='openURL(null,null,null, document.getElementById(&quot;time_form&quot;).elements[&quot;st&quot;].value, document.getElementById(&quot;time_form&quot;).elements[&quot;et&quot;].value);'>Apply</button>";
  echo "<button class='tablinks' onclick='openURL(null,null,&quot;&quot;,null)'>Clear</button>";
  echo "</form>";
  echo "</div>";  
*/
  echo "<div class='menutab_element'>";
  echo "<button class='tablinks'><b>Institution:</b>   </button>";
  echo "</div>";
  
  echo "<div class='menutab_element' style='width:300px'>";
  echo "<form method=post id=idp_form>";
  echo asSelect(getIdPListForSelect(),'idp', $idp);
  echo "</form>";
  echo "<button class='tablinks' onclick='openURL(null, null, null, null, null, &quot;&quot;);'>Clear</button>";
  echo "</div>";

  echo "<div class='menutab_element'>";
  echo "<button class='tablinks'><b>Merchant:</b>   </button>";
  echo "</div>";
  
  echo "<div class='menutab_element' style='width:300px'>";
  echo "<form method=post id=sp_form>";
  echo asSelect(getMerchantListForSelect(),'sp', $sp);
  echo "</form>";
  echo "<button class='tablinks' onclick='openURL(null, null, null, null, &quot;&quot;, null);'>Clear</button>";
  echo "</div>";
/*
  echo "<div class='menutab_element'>";
  echo "<button class='tablinks'><b>Filter:</b>   </button>";
  echo "</div>";
  
  echo "<div class='menutab_element' style='width:300px'>";
  echo "<form id=filter_form>";
  echo "<input type=text name=f value='$filter'>";
  echo "<button class='tablinks' onclick='openURL(null,null,document.getElementById(&quot;filter_form&quot;).elements[&quot;f&quot;].value, null, null, null);'>Apply</button>";
  echo "<button class='tablinks' onclick='openURL(null,null,&quot;&quot;,null, null, null)'>Clear</button>";
  echo "</form>";
  echo "</div>";
*/
echo "</div>";
//
?>

<!-- Tab links -->
<div class="tab">
  <button class="tablinks" onclick="openURL(null,'UniqueSessions',null,null)">Unique Sessions</button>
  <button class="tablinks" onclick="openURL(null,'IdPSessions',null,null)">Session per IdP</button>
  <button class="tablinks" onclick="openURL(null,'SPSessions',null,null)">Session per Merchant</button>
  <button class="tablinks" onclick="openURL(null,'Domains',null,null)">Domains</button>
  <button class="tablinks" onclick="openURL(null,'Country',null,null)">Country</button>
  <button class="tablinks" onclick="openURL(null,'Affiliaton',null,null)">Affiliation</button>
  <button class="tablinks" onclick="openURL(null,'Institutions',null,null)">Institutions</button>
  <button class="tablinks" onclick="openURL(null,'Merchants',null,null)">Merchants</button>
  <button class="tablinks" onclick="openURL(null,'Logs',null,null)">Logs</button>
</div>

<?php

echo "<div id='UniqueSessions' class='tabcontent'>";
if ($tab == 'UniqueSessions')
{
    echo showTableHeader($t, $p, 'UniqueSessions', $filter, $explain, $sp, $idp); 
    echo asTable(get_sessions($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='IdPSessions' class='tabcontent'>";
if ($tab == 'IdPSessions')
{
    echo showTableHeader($t, $p, 'IdPSessions', $filter, $explain, $sp, $idp); 
    echo asTable(get_idpsessions($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='SPSessions' class='tabcontent'>";
if ($tab == 'SPSessions')
{
   echo showTableHeader($t, $p, 'SPSessions',  $filter, $explain, $sp, $idp);
    echo asTable(get_spsessions($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='Domains' class='tabcontent'>";
if ($tab == 'Domains')
{
    echo showTableHeader($t, $p, 'Domains',  $filter, $explain, $sp, $idp); 
    echo asTable(get_domains($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='Country' class='tabcontent'>";
if ($tab == 'Country')
{
    echo showTableHeader($t, $p, 'Country',  $filter, $explain, $sp, $idp);
    echo asTable(get_countries($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='Affiliaton' class='tabcontent'>";
if ($tab == 'Affiliaton')
{
    echo showTableHeader($t, $p, 'Affiliaton',  $filter, $explain, $sp, $idp); 
    echo asTable(get_affiliations($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='Institutions' class='tabcontent'>";
if ($tab == 'Institutions')
{
    echo showTableHeader($t, $p, 'Institutions',  $filter, $explain, $sp, $idp); 
    echo asTable(get_idps($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='Merchants' class='tabcontent'>";
if ($tab == 'Merchants')
{    
    echo showTableHeader($t, $p, 'Merchants',  $filter, $explain, $sp, $idp);
    echo asTable(get_clients($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";

echo "<div id='Logs' class='tabcontent'>";
if ($tab == 'Logs')
{
    echo showTableHeader($t, $p, 'Logs',  $filter, $explain, $sp, $idp);
    echo asTable(get_logs($start, $end, $p, $filter, $sp, $idp));
}
echo "</div>";




// Some background data
$attributes = $as->getAttributes();   

$finish_time = floatval(explode(" ", microtime())[1])+floatval(explode(" ", microtime())[0]);
$total_time = round(($finish_time - $start_time), 3);

print_r("<div align='right'>Authenticated as: ". get_displayname($attributes)."</br>Page created in " .$total_time." s. </div>");

if ($showdebug) {
    echo "<pre>Page input:\n";
    print("<li>t=".$t);
    print("<li>et=".$et);
    print("<li>p=".$p);
    print("<li>filter=".$filter);
    print("<li>tab=".$tab);
    print("<li>sp=".$sp);
    print("<li>idp=".$idp);
    echo "</pre>";
    echo "<pre>Queries:\n";
    foreach ($qry_debug as &$qry) {
        print("<li>".$qry);
    }
    echo "</pre>";
}

echo "</td></tr></table></div>";
?>

</body>
</html>
