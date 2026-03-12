<?php
$c = new mysqli("127.0.0.1", "root", "", "edu");
$res = $c->query("SHOW TABLES");
while($r = $res->fetch_array()) echo $r[0] . "\n";
