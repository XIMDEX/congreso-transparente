<?php

//Configuración parámetros de Congreso transparente
return array(
    'URL_XIMDEX' => "http://lab13.ximdex.net/ximdexct/api",
    'INDEX_ID' => 11192, //index-idex node id
    'RNG_NODE_ID' => 10175,
    'DOCUMENT_FOLDER_ID' => 11190,
    'XIMDEX_USER' => "ximdex",
    'XIMDEX_PASS' => "ximdexct",
    'IMPORT_COUNTER' => 50,  // 50 is a good value for a monthly execution
    'IMPORT_FILE' => 'last-import/last.ini',
    'BASE_URL' => 'http://www.congreso.es/votaciones/OpenData?sesion=%d&completa=1&legislatura=10',
    'BASE_SCRIPT' => '/tmp/congreso',
    'BASE_DOWNLOAD' => '/tmp/congreso/download',
    'PARTIALS_INDEX' => '/tmp/congreso/partialsIndex',
    'LANG_SUFFIX' => '-ides',
    'NO_EXISTING_THRESHOLD' => 10,
    'MINIMUM_FREE_SPACE' => 100000000,
    'GET_FILE_CONNECTION_TIMEOUT' => 10,
    'GET_FILE_DOWNLOAD_TIMEOUT' => 90
);
