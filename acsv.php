<?php
include('queries.php');
session_start();

$t = isset($_GET['t'])?$_GET['t']:time();
$p = isset($_GET['p'])?$_GET['p']:"month";
$tab = isset($_POST['tab'])?$_POST['tab']:(isset($_GET['tab'])?$_GET['tab']:"IdPSessionsPerSP");
$sp = isset($_POST['sp'])?$_POST['sp']:(isset($_GET['sp'])?$_GET['sp']:"");

if (isset($_POST['action']) and $_POST['action'] == 'clear') {
    $filter = '';
} else {
    $filter = isset($_POST['f'])?$_POST['f']:(isset($_SESSION['f'])?$_SESSION['f']:'');
}
$_SESSION['f'] = $filter;

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

if ($sp) {
  $explain .= "_$sp";
}

if ($filter) {
  $explain .= "_" . str_replace(" ", "_", $filter);
}


// Day ends at 23:59:59
$e -= 1;

$start = date('Y-m-d H:i:s', $s);
$end = date('Y-m-d H:i:s', $e);

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$explain.csv");
header("Pragma: no-cache");
header("Expires: 0");

/**echo "Audit\n";
echo "p,$p ($explain)\n";
echo "d," . date('Y-m-d', $t) . "\n";
echo "s,$start\n";
echo "e,$end\n";
echo "f,$filter\n";
echo "t,$tab\n";
**/

switch($tab) {
/*
  case 'UniqueSessions':
  echo "Unique sessions\n";
  echo csv_table(get_sessions($start, $end));
  break;

  case 'IdPSessions';
  echo "Sessions per IdP\n";
  echo csv_table(get_idpsessions($start, $end, $filter));
  break;
*/
  case 'IdPSessionsPerSP';
  echo "Sessions per Service\n";
  echo csv_table(get_idpsessionspersp($start, $end, $filter, $sp));
  break;

  case 'SPSessions';
  echo "Sessions per Service\n";
  echo csv_table(get_spsessions($start, $end, $filter, $sp));
  break;

/**
  case 'ServicesPerIdP';
  echo "# SP's per IdP\n";
  echo csv_table(get_spperidp($start, $end, $filter));
  break;

  case ;
  echo "# IdP's per SP\n";
  echo csv_table(get_idppersp($start, $end, $filter));
  break;
**/

  case 'SessionsPerCountry';
  echo "Sessions per Service\n";
  echo csv_table(get_sessionspercountry($start, $end, $filter, $sp));
  break;

  case 'UniqueIdPs';
  echo "Unique IdP's\n";
  echo csv_table(get_logidps($start, $end, $filter));
  break;

  case 'Domains';
  echo "Domains\n";
  echo csv_table(get_domains($start, $end, $filter));
  break;

  case 'Country';
  echo "Countries\n";
  echo csv_table(get_countries($start, $end, $filter));
  break;

  case 'affiliaton';
  echo "Affiliatons\n";
  echo csv_table(get_affiliations($start, $end));
  break;

  case 'institutions';
  echo "Institutions\n";
  echo csv_table(get_idps());
  break;

  case 'services';
  echo "Services\n";
  echo csv_table(get_clients());
  break;

/*
  case 'logs';
  echo "Logs\n";
  echo csv_table(get_logs());
  break;
*/
}
