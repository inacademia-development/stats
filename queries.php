<?php
$MYSQL_HOST = getenv('MYSQL_HOST');
$MYSQL_USER = getenv('MYSQL_USER');
$MYSQL_PWD  = getenv('MYSQL_PWD');
$MYSQL_DB   = getenv('MYSQL_DB');

$MYSQL_HOST="172.172.172.220";
$MYSQL_USER="audit";
$MYSQL_PWD="pw4audit";
$MYSQL_DB="audit_db";

$mysqli = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PWD, $MYSQL_DB);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
} else {
//     echo $mysqli->host_info . "\n";
}

function mkEntityFileter($sp="", $idp="",$fed="") {
    $entityfilter = "";
    if ($idp) {
        $entityfilter .= " and log_idp = '$idp' ";
    }
    if ($sp) {
        $entityfilter .= " and log_sp = '$sp' ";
    }
    if ($fed) {
        // not yet implemented
    }
    return $entityfilter;
}

function get_data($query) {
    
    //echo "This query: <pre>$query</pre>";
    
    global $mysqli;
    $res = $mysqli->query($query);
    if (!$res) {
        return $mysqli->error;
    }
    $res->data_seek(0);
    return $res;
}

function getDisplayname($entity, $entityType) {
     switch($entityType) {
        case 'sp':
            $query = "SELECT client_displayname AS displayname FROM audit_db.clients WHERE client_name = '$entity'";
            break;
        case 'idp':
            $query = "SELECT idp_displayname AS displayname FROM audit_db.idps WHERE idp_entityid = '$entity'";
            break;
        case 'fed':
            $query = "SELECT 'Federation' as displayname";
            break;            
        default:
            $query = "SELECT 'Unknown Entity' as displayname";
    }
    
    return get_data($query);
}

function getIdPListForSelect() {
    $query = "SELECT concat('<option value=', idp_entityid, '>', ' (', idp_country, ') ', concat(IF(LENGTH(idp_displayname)<100, idp_displayname, CONCAT(LEFT(idp_displayname, 97), '...'))), '</option>') FROM audit_db.idps order by  idp_displayname, idp_country";
    return get_data($query);
}

function getMerchantListForSelect() {
    $query = "SELECT concat('<option value=', client_name, '>', client_displayname, '</option>') FROM audit_db.clients order by client_displayname";
    return get_data($query);
}

function get_sessions($s, $e, $p, $f="", $sp="", $idp="") {
    
    switch($p) {
        case 'day':
            // present result per hour
            $preset = "HOUR(log_timestamp) as Hour,";
            $groupby = "GROUP BY HOUR(log_timestamp)";
            $orderby = "ORDER BY Hour";
             break;
        case 'week':
            // present result per day
            $preset = "DAY(log_timestamp) as Day,";
            $groupby = "GROUP BY DAY(log_timestamp)";
            $orderby = "ORDER BY Day";
            break;
        case 'month':
            // present result per day
            $preset = "DAY(log_timestamp) as Day,";
            $groupby = "GROUP BY DAY(log_timestamp)";
            $orderby = "ORDER BY Day";
            break;
        case 'year':
            // present result per month
            $preset = "MONTH(log_timestamp) as Month,";
            $groupby = "GROUP BY MONTH(log_timestamp)";
            $orderby = "ORDER BY Month";
            break;
        default:
            $preset = "";
            $groupby = ";";
            $orderby = "";
    }   

    $query  = "select ".$preset." count(distinct(l.log_sessionid)) c ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e'";
    $query .= mkEntityFileter($sp, $idp);
    $query .= $groupby;
    $query .= " ".$orderby;
    return get_data($query);
}

function get_idpsessions($s, $e, $p, $f="", $sp="", $idp="") {
    $query  = "select c, idp_displayname as Displayname, idp_country as Country, idp_entityid as EntityID, idp_ra as RA from ";
    $query .= "idps idp, ";
    $query .= "(select count(l.log_sessionid) c, l.log_idp ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_idp like '%$f%' or i.idp_displayname like '%$f%') ";
    }
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by l.log_idp) log ";
    $query .= "where log.log_idp = idp.idp_entityid ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_spsessions($s, $e, $p, $f="", $sp="", $idp="") {
    $query  = "select c, client_displayname as Displayname, client_name as client_id from ";
    $query .= "clients cl, ";
    $query .= "(select count(l.log_sessionid) c, l.log_sp ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_sp like '%$f%') ";
    }
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by l.log_sp) log ";
    $query .= "where log.log_sp = cl.client_name ";   
    $query .= "order by c desc;";
    return get_data($query);
}

function get_domains($s, $e, $p, $f, $sp="", $idp="") {
    $query  = "select count(l.log_sessionid) c, l.log_domain ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_domain like '%$f%') ";
    }
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by l.log_domain ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_countries($s, $e, $p, $f, $sp="", $idp="") {
    $query  = "select count(l.log_sessionid) c, i.idp_country ";
    $query .= "from logs l left join idps i on l.log_idp=i.idp_entityid ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (i.idp_country like '%$f%') ";
    }
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by i.idp_country ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_affiliations($s, $e, $p, $f, $sp="", $idp="") {
    $query  = "select count(log_affiliate) c, 'affiliate' from logs l where log_affiliate=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by log_affiliate ";

    $query .= "union select count(log_employee) c, 'employee' from logs l where log_employee=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by log_employee ";

    $query .= "union select count(log_member) c, 'member' from logs l where log_member=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by log_member ";

    $query .= "union select count(log_faculty) c, 'faculty' from logs l where log_faculty=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by log_faculty ";

    $query .= "union select count(log_staff) c, 'staff' from logs l where log_staff=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by log_staff ";

    $query .= "union select count(log_student) c, 'student' from logs l where log_student=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= "group by log_student ";

    $query .= "order by c desc;";
    return get_data($query);
}

function get_idps($s, $e, $p, $f, $sp="", $idp="") {
    $query = "SELECT idp_country as Country, idp_displayname as Displayname, idp_entityid as EntityID, idp_ra as RA FROM idps WHERE idp_entityid IN (";
    $query .= "SELECT DISTINCT(log_idp) FROM audit_db.logs l where 1=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= ") ";
    $query .= " ORDER BY idp_country, idp_displayname";
    return get_data($query);
}

function get_clients($s, $e, $p, $f, $sp="", $idp="") {
   
    $query = "SELECT client_displayname as Displayname, client_name as Client_ID FROM clients WHERE client_name IN (";
    $query .= "SELECT DISTINCT(log_sp) FROM audit_db.logs l where 1=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);
    $query .= ") ";
    $query .= " ORDER BY Displayname";
    return get_data($query);
 
}

function get_logs($s, $e, $p, $f, $sp="", $idp="") {
    $query = "select count(*) as c from logs l ";
    $query .= "where 1=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);    
    return get_data($query);
}

function get_summary($s, $e, $p, $f, $sp="", $idp="") {
    $query  = "select count(log_timestamp) c, year(log_timestamp) y, month(log_timestamp) m, day(log_timestamp) d from logs ";
    $query .= "where log_timestamp between '$s' and '$e' ";
    $query .= mkEntityFileter($sp, $idp);        
    $query .= "group by d,m,y;";
    return get_data($query);
}
