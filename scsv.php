<?php
include('queries.php');
$t = isset($_GET['t'])?$_GET['t']:time();
$p = isset($_GET['p'])?$_GET['p']:"month";

function csv_table($res) {
    $res->data_seek(0);

    $ret = "";
    $header = false;
    while ($row = $res->fetch_assoc()) {
        if (!$header) {
            $ret = implode(",", array_keys($row)) . "\n";
            $header = true;
        }

        $c = $row['c'];
        $y = $row['y'];
        $m = $row['m'];
        $d = $row['d'];

        $ret .= "$c, $y, " . date("F", strtotime("$d-$m-$y")) . ",$d\n";
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
    case 'month':
        $s = strtotime("1-$m-$y", $s);
        $e = strtotime("+1 month", $s);
        $explain = "$y-${m}_Summary_" .  date("F", $s);
        break;
    case 'year':
        $s = strtotime("1-1-$y", $s);
        $e = strtotime("+1 year", $s);
        $explain = "${y}_Summary_" . date("Y", $s);
        break;
}
// Day ends at 23:59:59
$e -= 1;

$start = date('Y-m-d H:i:s', $s);
$end = date('Y-m-d H:i:s', $e);

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$explain.csv");
header("Pragma: no-cache");
header("Expires: 0");

echo "Summary\n";
echo "p,$p ($explain)\n";
echo "d," . date('Y-m-d', $t) . "\n";
echo "s,$start\n";
echo "e,$end\n";

echo "# logs\n";
echo csv_table(get_summary($start, $end));
