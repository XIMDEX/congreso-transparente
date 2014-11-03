<?php

date_default_timezone_set('Europe/Madrid');

function logger($level, $msg) {
    $d = getdate();
    echo sprintf("%d-%02d-%02dT%02d:%02d:%02dZ $level $msg\n", $d["year"], $d["mon"], $d["mday"], $d["hours"], $d["minutes"], $d["seconds"]);
}