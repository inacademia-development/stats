<?php
include('queries.php');
//session_start();
/*
$t = isset($_GET['t'])?$_GET['t']:time();
$p = isset($_GET['p'])?$_GET['p']:"month";
$tab = isset($_GET['tab'])?$_GET['tab']:"SPSessions";

if (isset($_POST['action']) and $_POST['action'] == 'clear') {
    $filter = '';
} else {
    $filter = isset($_POST['f'])?$_POST['f']:(isset($_SESSION['f'])?$_SESSION['f']:'');
}
$_SESSION['f'] = $filter;
*/

function csv_table($res) {
    $res->data_seek(0);

    $ret = "";
    $header = false;
    while ($row = $res->fetch_assoc()) {
        if (!$header) {
            $ret = implode(",", array_keys($row)) . "\n";
            $header = true;
        }
        foreach($row as $column) {
            $ret .= $column . ",";
        }
        $ret .= "\n";
    }
    return $ret;
}
/*
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
        $explain = "$y-$m-${d}_InAcademia_" . date("l", $s);
        break;
    case 'week':
        $s = strtotime("-$wd days", $s);
        $e = strtotime("+1 week", $s);
        $explain = "$y-$m-${d}_InAcademia_w" . date("W", $s);
        break;
    case 'month':
        $s = strtotime("1-$m-$y", $s);
        $e = strtotime("+1 month", $s);
        $explain = "$y-${m}_InAcademia_" . date("F", $s);
        break;
    case 'year':
        $s = strtotime("1-1-$y", $s);
        $e = strtotime("+1 year", $s);
        $explain = "${y}_InAcademia_" . date("Y", $s);
        break;
}
// Day ends at 23:59:59
$e -= 1;

$start = date('Y-m-d H:i:s', $s);
$end = date('Y-m-d H:i:s', $e);
*/

$t = isset($_GET['t'])?$_GET['t']:"";
$et = isset($_GET['et'])?$_GET['et']:"";
$p = isset($_GET['p'])?$_GET['p']:"";
$filter = isset($_GET['f'])?$_GET['f']:"";
$tab = isset($_GET['tab'])?$_GET['tab']:"";
$sp = isset($_GET['sp'])?$_GET['sp']:"";
$idp = isset($_GET['idp'])?$_GET['idp']:"";
$explain = isset($_GET['expl'])?urldecode($_GET['expl']):"";

// Calculate start and end datetime and headers we need to show
$date = getdate($t);
$d = $date['mday'];
$m = $date['mon'];
$y = $date['year'];
$wd = ($date['wday']+6)%7; // week starts on monday

// All days start at 00:00:00
$s = strtotime("$d-$m-$y");

switch($p) {
    case 'day':
        $e = strtotime("+1 day", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $csv_filename = "$y-$m-${d}_InAcademia_". date("l", $s);
        break;
    case 'week':
        $s = strtotime("-$wd days", $s);
        $e = strtotime("+1 week", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $csv_filename = "$y-$m-${d}_InAcademia_w" . date("W", $s);
        break;
    case 'month':
        $s = strtotime("1-$m-$y", $s);
        $e = strtotime("+1 month", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $csv_filename = "$y-${m}_InAcademia_" . date("F", $s);
        break;
    case 'year':
        $s = strtotime("1-1-$y", $s);
        $e = strtotime("+1 year", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $csv_filename = "${y}_InAcademia_" . date("Y", $s);
        break;
}

// Day ends at 23:59:59
$e -= 1;

/*
print("<li>t=".$t);
print("<li>et=".$et);
print("<li>p=".$p);
print("<li>filter=".$filter);
print("<li>tab=".$tab);
print("<li>sp=".$sp);
print("<li>idp=".$idp);
print("<li>explain=".$explain);
exit();
*/

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$csv_filename.csv");
header("Pragma: no-cache");
header("Expires: 0");

echo "$explain\n\n";
switch($tab) {
	case 'UniqueSessions': 
    echo csv_table(get_sessions($start, $end, $p, $filter, $sp, $idp));
	break;
	
	case 'IdPSessions';
    echo csv_table(get_idpsessions($start, $end, $p, $filter, $sp, $idp));
	break;
	
	case 'SPSessions';
    echo csv_table(get_spsessions($start, $end, $p, $filter, $sp, $idp));
	break;

	case 'Domains';
    echo csv_table(get_domains($start, $end, $p, $filter, $sp, $idp));
	break;

	case 'Country';
    echo csv_table(get_countries($start, $end, $p, $filter, $sp, $idp));
	break;
	
	case 'Affiliaton';
    echo csv_table(get_affiliations($start, $end, $p, $filter, $sp, $idp));
	break;
	
	case 'Institutions';
    echo csv_table(get_idps($start, $end, $p, $filter, $sp, $idp));
	break;
	
	case 'Services';
    echo csv_table(get_clients($start, $end, $p, $filter, $sp, $idp));
	break;
	
	case 'Logs';
    echo csv_table(get_logs($start, $end, $p, $filter, $sp, $idp));
	break;
}
