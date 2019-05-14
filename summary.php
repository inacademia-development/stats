<html>
<head>
<style>
table {
    border-collapse: collapse;
    border: 0;
/*     width: 80%; */
    box-shadow: 1px 2px 3px #ccc;
}
td, th {
    border: 1px solid #666;
    font-size: 75%;
    vertical-align: baseline;
    padding: 4px 5px;
}
h1 {
    margin-bottom: 0px;
}
a {
    text-decoration: none;
}
pre {
    font-size: 1.4em;
    font-weight: bold;
}
</style>
<head>
<body>
<a name=top><h1>Summary</h1></a>
<?php
include('queries.php');
$t = isset($_GET['t'])?$_GET['t']:time();
$p = isset($_GET['p'])?$_GET['p']:"month";

echo "<a href=\"?t=" . strtotime('-1 ' . $p, $t) . "&p=$p\">previous</a> | \n";
echo "<a href=\"/summary/\">now</a> | \n";
echo "<a href=\"?t=" . strtotime('+1 ' . $p, $t) . "&p=$p\">next</a>\n";
echo "<br>\n";
echo "<a href=\"?t=$t&p=month\">month</a> | \n";
echo "<a href=\"?t=$t&p=year\">year</a> | \n";
echo "<a href=\"/scsv?t=$t&p=$p\">csv</a>\n";
echo "</br></br>\n";

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
        $explain = date("F", $s);
        break;
    case 'year':
        $s = strtotime("1-1-$y", $s);
        $e = strtotime("+1 year", $s);
        $explain = date("Y", $s);
        break;
}
// Day ends at 23:59:59
$e -= 1;

$start = date('Y-m-d H:i:s', $s);
$end = date('Y-m-d H:i:s', $e);

echo "<pre>\n";
echo "p: $p ($explain)\n";
echo "d: " . date('Y-m-d', $t) . "\n";
echo "s: $start\n";
echo "e: $end\n";
echo "</pre>\n";
?>

<a href="#logs"># logs</a><br>

<?php
function summary_table($res) {
    $res->data_seek(0);

    $ret = "<table>\n";
    $header = false;
    while ($row = $res->fetch_assoc()) {
        if (!$header) {
            $ret .= "<tr>\n";
            foreach(array_keys($row) as $name) {
                $ret .= "<td><b>" . $name . "</b></td>\n";
            }
            $ret .= "</tr>\n";
            $header = true;
        }
        $c = $row['c'];
        $y = $row['y'];
        $m = $row['m'];
        $d = $row['d'];

        $ret .= "<tr>\n";

        $ret .= "<td style='border-left: 1px solid; padding: 0px 4px; margin: 0px;'>$c</td>";

        $ret .= "<td style='border-left: 1px solid; padding: 0px 4px; margin: 0px;'>";
        $ret .= "<a href=\"/audit?p=year&t=" . strtotime("1-1-$y") . "\">$y</a></td>";

        $ret .= "<td style='border-left: 1px solid; padding: 0px 4px; margin: 0px;'>";
        $ret .= "<a href=\"/audit?p=month&t=" . strtotime("1-$m-$y") . "\">";
        $ret .= date("F", strtotime("$d-$m-$y")) . "</a></td>";

        $ret .= "<td style='border-left: 1px solid; padding: 0px 4px; margin: 0px;'>";
        $ret .= "<a href=\"/audit?p=day&t=" . strtotime("$d-$m-$y") . "\">$d</a></td>";

        $ret .= "</tr>\n";
    }
    $ret .= "</table>\n";
    return $ret;
}

echo "<h1># logs <a href=#top name=logs>^</a></h1>\n";
echo summary_table(get_summary($start, $end));
