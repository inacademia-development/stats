<?php
//$MYSQL_HOST = getenv('MYSQL_HOST');
//$MYSQL_USER = getenv('MYSQL_USER');
//$MYSQL_PWD  = getenv('MYSQL_PWD');
//$MYSQL_DB   = getenv('MYSQL_DB');

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

function get_sessions($s, $e) {
    $query  = "select count(distinct(l.log_sessionid)) c ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e';";
    return get_data($query);
}

function get_idpsessions($s, $e, $f) {
    $query  = "select c, idp_displayname as Displayname, idp_entityid as EntityID from ";
    $query .= "idps idp, ";
    $query .= "(select count(l.log_sessionid) c, l.log_idp ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_idp like '%$f%' or i.idp_displayname like '%$f%') ";
    }
    $query .= "group by l.log_idp) log ";
    $query .= "where log.log_idp = idp.idp_entityid ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_spsessions($s, $e, $f) {
    $query  = "select c, client_displayname as Displayname, client_name as client_id from ";
    $query .= "clients cl, ";
    $query .= "(select count(l.log_sessionid) c, l.log_sp ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_sp like '%$f%') ";
    }
    $query .= "group by l.log_sp) log ";
    $query .= "where log.log_sp = cl.client_name ";   
    $query .= "order by c desc;";
    return get_data($query);
}

function get_spperidp($s, $e, $f) {
    $query  = "select c, idp_displayname as Displayname, idp_entityid as EntityID from ";
    $query .= "idps idp, ";
    $query .= "(select count(l.log_sp) c, l.log_idp, ANY_VALUE(i.idp_displayname) displayname ";
    $query .= "from logs l left join idps i on l.log_idp=i.idp_entityid ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_idp like '%$f%' or i.idp_displayname like '%$f%') ";
    }
    $query .= "group by l.log_idp) log ";
    $query .= "where log.log_idp = idp.idp_entityid ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_idppersp($s, $e, $f) {
    $query  = "select count(l.log_idp) c, l.log_sp ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_sp like '%$f%') ";
    }
    $query .= "group by l.log_sp ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_domains($s, $e, $f) {
    $query  = "select count(l.log_sessionid) c, l.log_domain ";
    $query .= "from logs l ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (l.log_domain like '%$f%') ";
    }
    $query .= "group by l.log_domain ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_countries($s, $e, $f) {
    $query  = "select count(l.log_sessionid) c, i.idp_country ";
    $query .= "from logs l left join idps i on l.log_idp=i.idp_entityid ";
    $query .= "where l.log_timestamp between '$s' and '$e' ";
    if ($f) {
        $query .= "and (i.idp_country like '%$f%') ";
    }
    $query .= "group by i.idp_country ";
    $query .= "order by c desc;";
    return get_data($query);
}

function get_affiliations($s, $e) {
    $query  = "select count(log_affiliate) c, 'affiliate' from logs l where log_affiliate=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= "group by log_affiliate ";

    $query .= "union select count(log_employee) c, 'employee' from logs l where log_employee=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= "group by log_employee ";

    $query .= "union select count(log_member) c, 'member' from logs l where log_member=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= "group by log_member ";

    $query .= "union select count(log_faculty) c, 'faculty' from logs l where log_faculty=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= "group by log_faculty ";

    $query .= "union select count(log_staff) c, 'staff' from logs l where log_staff=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= "group by log_staff ";

    $query .= "union select count(log_student) c, 'student' from logs l where log_student=1 ";
    $query .= "and l.log_timestamp between '$s' and '$e' ";
    $query .= "group by log_student ";

    $query .= "order by c desc;";
    return get_data($query);
}

function get_idps() {
    $query = "select count(*) c from idps ";
    return get_data($query);
}

function get_clients() {
    $query = "select count(*) c from clients;";
    return get_data($query);
}

function get_logs() {
    $query = "select count(*) c from logs;";
    return get_data($query);
}

function get_summary($s, $e) {
    $query  = "select count(log_timestamp) c, year(log_timestamp) y, month(log_timestamp) m, day(log_timestamp) d from logs ";
    $query .= "where log_timestamp between '$s' and '$e' ";
    $query .= "group by d,m,y;";
    return get_data($query);
}
