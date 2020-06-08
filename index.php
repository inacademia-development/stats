<?php
include('queries.php');
include('auth.php');

function table($res) {
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

function showTableHeader($t, $p, $tab, $filter, $sp) {

  $dspname="";

  switch($tab) {
    case 'UniqueSessions':
      $dspname="Unique sessions\n";
      break;
    case 'UniqueIdPs':
      $dspname="Unique IdP's\n";
      break;
    case 'IdPSessions':
      $dspname="Sessions per IdP\n";
      break;
    case 'IdPSessionsPerSP':
      $dspname="IdP sessions per SP\n";
      break;
    case 'SessionsPerCountry':
      $dspname="Sessions per Country\n";
      break;
    case 'SPSessions':
      $dspname="Sessions per Service\n";
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
    case 'Services':
      $dspname="Services\n";
      break;
    case 'Logs':
      $dspname="Logs\n";
      break;
  }
  return "<table><tr><td align='left' class='thHeader'><h2>$dspname</h2></td><td align='right'  class='thHeader'>[<a target='_blank' href='acsv.php?t=$t&p=$p&tab=$tab&filter=$filter&sp=$sp'>export CSV</a>]</td></tr></table>";
}
?>


<html>
<head>
<link rel="stylesheet" type="text/css" href="inacademia_stats.css">
<script src="inacademia_stats.js"></script>
</head>

<?php

$t = isset($_GET['t'])?$_GET['t']:time();
$p = isset($_GET['p'])?$_GET['p']:"month";
$filter = isset($_GET['f'])?$_GET['f']:"";
$tab = isset($_POST['tab'])?$_POST['tab']:(isset($_GET['tab'])?$_GET['tab']:"IdPSessionsPerSP");
$sp = isset($_POST['sp'])?$_POST['sp']:(isset($_GET['sp'])?$_GET['sp']:"");

echo "<body onLoad='openTab(event, &quot;".$tab."&quot;)'>";
echo "<div><table><tr><td>";
echo "<form name='state_form' id='state_form'>";
echo "<input type='hidden' name='time' value='".$t."'>";
echo "<input type='hidden' name='tab' value='".$tab."'>";
echo "<input type='hidden' name='filter' value='".$filter."'>";
echo "<input type='hidden' name='interval' value='".$p."'>";
echo "<input type='hidden' name='sp' value='".$sp."'>";
echo "</form>";

echo "<a name=top><h1>InAcademia Stats</h1></a>";

echo "<div class='menutab'>";
echo "<div class='menutab_element'>";
echo "<button class='tablinks'><b>Interval:</b>   </button>";
echo "</div>";
echo "<div class='menutab_element'>";
echo "<button class='tablinks' onclick=\"openURL(null,null,null,'day','$sp')\">Day</button>";
echo "<button class='tablinks' onclick=\"openURL(null,null,null,'week','$sp')\">Week</button>";
echo "<button class='tablinks' onclick=\"openURL(null,null,null,'month','$sp')\">Month</button>";
echo "<button class='tablinks' onclick=\"openURL(null,null,null,'year','$sp')\">Year</button>";
echo "<button class='tablinks' onclick=\"openURL(" . strtotime('-1 ' . $p, $t) . ",null,null,null,'$sp')\">&lt;&lt;</button>";
echo "<button class='tablinks' onclick=\"openURL(".strtotime("now").",null,null,null,'$sp')\">Today</button>";
echo "<button class='tablinks' onclick=\"openURL(" . strtotime('+1 ' . $p, $t) . ",null,null,null,'$sp')\">&gt;&gt;</button>";
echo "</div>";
/**
echo "<div class='menutab_element'>";
echo "<button class='tablinks'>       <b>Filter:</b>   </button>";
echo "</div>";
echo "<div class='menutab_element'>";
echo "<form method=post id=filter_form>";
echo "<input type=text name=f value='$filter'>";
echo "<button class='tablinks' onclick='openURL(null,null,document.getElementById(&quot;filter_form&quot;).elements[&quot;f&quot;].value,null)'>Apply</button>";
echo "<button class='tablinks' onclick='openURL(null,null,&quot;&quot;,null)'>Clear</button>";
echo "</form>";
echo "</div>";
**/
echo "</div>";

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
        break;
    case 'week':
        $s = strtotime("-$wd days", $s);
        $e = strtotime("+1 week", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $explain = "Week " .date("W", $s) . " (" . date("Y", $s) . ")";
        break;
    case 'month':
        $s = strtotime("1-$m-$y", $s);
        $e = strtotime("+1 month", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $explain = date("F", $s) . " (" . date("Y", $s) . ")";
        break;
    case 'year':
        $s = strtotime("1-1-$y", $s);
        $e = strtotime("+1 year", $s);
        $start = date('Y-m-d H:i:s', $s);
        $end = date('Y-m-d H:i:s', $e);
        $explain = date("Y", $s);
        break;
}

// Day ends at 23:59:59
$e -= 1;

//

echo "<div class='filter'>";
echo "<button class='tablinks'>Current parameters: </button>";
echo "<button class='tablinks'><b>" . $explain ."</b></button>";
echo "<button class='tablinks'>";
echo "<form method='post' name='sp_form' id='sp_form' style='margin: 0px'>";
echo "<input type=hidden name=tab id=tab>";
echo "<select name=sp onchange='submitForm(this.form)'>";
echo "<option value=''>&lt;Select Merchant&gt;</option>";
$res = get_spsessions($start, $end, $filter, NULL);
while ($row = $res->fetch_assoc()) {
  $id = $row['log_sp'];
  echo "<option value='$id'" . ($id==$sp?" selected":"") . ">$id</option>";
}
echo "</select>";
echo "</form>";
echo "</button>";
echo "<button class='tablinks'>Keyword: <b>" . $filter ."</b></button>";

echo "</div>";
echo "<div class='filter'>";
echo "</div>";

// echo "<div class='menutab'>";
// echo "<button class='tablinks'>Navigate:</button>";
// echo "<div class='menutab_element'>";
// echo "<button class='tablinks' onclick='openURL(" . strtotime('-1 ' . $p, $t) . ",null,null,null)'>&lt;&lt;</button>";
// echo "<button class='tablinks' onclick='openURL(".strtotime("now").",null,null,null)'>Today</button>";
// echo "<button class='tablinks' onclick='openURL(" . strtotime('+1 ' . $p, $t) . ",null,null,null)'>&gt;&gt;</button>";
// echo "</div>";
// echo "</div>";
?>


<!-- Tab links -->
<div class="tab">
  <div style='background: #e0e0ff'>
<!--   <button class="tablinks" onclick="openTab(event, 'UniqueSessions')" id='UniqueSessions_tab'>Unique Sessions</button> -->
<!--   <button class="tablinks" onclick="openTab(event, 'IdPSessions')" id='IdPSessions_tab'>Sessions per IdP</button> -->
  <button class="tablinks" onclick="openTab(event, 'IdPSessionsPerSP')" id='IdPSessionsPerSP_tab'>IdP Sessions per Merchant</button>
  <button class="tablinks" onclick="openTab(event, 'SPSessions')" id='SPSessions_tab'>Session per Merchant</button>
<!--   <button class="tablinks" onclick="openTab(event, 'ServicesPerIdP') id='ServicesPerIdP_tab'">Services per IdP</button> -->
<!--   <button class="tablinks" onclick="openTab(event, 'IdPsPerService')" id='IdPsPerService_tab'>IdPs per Service</button> -->
  </div>
  <div style='background: #e0ffe0'>
  <button class="tablinks" onclick="openTab(event, 'SessionsPerCountry')" id='SessionsPerCountry_tab'>Sessions per Country</button>
  <button class="tablinks" onclick="openTab(event, 'UniqueIdPs')" id='UniqueIdPs_tab'>Unique IdP's</button>
  <button class="tablinks" onclick="openTab(event, 'Domains')" id='Domains_tab'>Domains</button>
  <button class="tablinks" onclick="openTab(event, 'Country')" id='Country_tab'>Country</button>
  <button class="tablinks" onclick="openTab(event, 'Affiliaton')" id='Affiliaton_tab'>Affiliation</button>
  <button class="tablinks" onclick="openTab(event, 'Institutions')" id='Institutions_tab'>Institutions</button>
  <button class="tablinks" onclick="openTab(event, 'Services')" id='Services_tab'>Merchants</button>
  </div>
</div>

<?php
// echo "<div id='UniqueSessions' class='tabcontent'>";
// echo showTableHeader($t, $p, 'UniqueSessions', $filter);
// echo table(get_sessions($start, $end));
// echo "</div>";

// echo "<div id='IdPSessions' class='tabcontent'>";
// echo showTableHeader($t, $p, 'IdPSessions', $filter);
// echo table(get_idpsessions($start, $end, $filter));
// echo "</div>";

echo "<div id='IdPSessionsPerSP' class='tabcontent'>";
echo showTableHeader($t, $p, 'IdPSessionsPerSP', $filter, $sp);
echo table(get_idpsessionspersp($start, $end, $filter, $sp));
echo "</div>";

echo "<div id='SPSessions' class='tabcontent'>";
echo showTableHeader($t, $p, 'SPSessions', $filter, $sp);
echo table(get_spsessions($start, $end, $filter, $sp));
echo "</div>";

// echo "<div id='ServicesPerIdP' class='tabcontent'>";
// echo showTableHeader($t, $p, 'ServicesPerIdP', $filter);
// echo table(get_spperidp($start, $end, $filter));
// echo "</div>";

// echo "<div id='IdPsPerService' class='tabcontent'>";
// echo showTableHeader($t, $p, 'IdPs_per_Service', $filter);
// echo table(get_idppersp($start, $end, $filter, $sp));
// echo "</div>";
echo "<div id='SessionsPerCountry' class='tabcontent'>";
echo showTableHeader($t, $p, 'SessionsPerCountry', $filter, $sp);
echo table(get_sessionspercountry($start, $end, $filter));
echo "</div>";

echo "<div id='UniqueIdPs' class='tabcontent'>";
echo showTableHeader($t, $p, 'UniqueIdPs', $filter, $sp);
echo table(get_logidps($start, $end));
echo "</div>";

echo "<div id='Domains' class='tabcontent'>";
echo showTableHeader($t, $p, 'Domains', $filter, $sp);
echo table(get_domains($start, $end, $filter));
echo "</div>";

echo "<div id='Country' class='tabcontent'>";
echo showTableHeader($t, $p, 'Country', $filter, $sp);
echo table(get_countries($start, $end, $filter));
echo "</div>";

echo "<div id='Affiliaton' class='tabcontent'>";
echo showTableHeader($t, $p, 'Affiliaton', $filter, $sp);
echo table(get_affiliations($start, $end));
echo "</div>";

echo "<div id='Institutions' class='tabcontent'>";
echo showTableHeader($t, $p, 'Institutions', $filter, $sp);
echo table(get_idps());
echo "</div>";

echo "<div id='Services' class='tabcontent'>";
echo showTableHeader($t, $p, 'Services', $filter, $sp);
echo table(get_clients());
echo "</div>";

echo "<div><span style='float: right'>Authenticated as: ". get_displayname($attributes) . "</span></div>";
echo "</td></tr></table></div>";
// echo "</form></td></tr></table></div>";
?>
</body>
</html>
