<?php

// "Congreso transparente" parameters configuration 
return array(
    'URL_XIMDEX' => "http://localhost/ximdex/api",
    'INDEX_ID' => 12026,                    // index-idex node id
    'RNG_NODE_ID' => 10755,                 // relax-ng node id
    'DOCUMENT_FOLDER_ID' => 12262,          // document folder node id
    'LANG_SUFFIX' => 'es',                  // could be en, es...
    'DOC_SUFFIX' => '-ides',                // could be -ides, -iden... related with previous
    'PUBLISH_CHANNELS_IDS' => '10001',// channels id as in table Channels (separated with comma) 
    'XIMDEX_USER' => "ximdex",              
    'XIMDEX_PASS' => "ximdex",
    'IMPORT_COUNTER' => 6,                  // 50 is a good value for a monthly execution
    'IMPORT_FILE' => '/var/www/congreso-transparente/last-import/last.ini',
    'BASE_URL' => 'http://www.congreso.es/votaciones/OpenData?sesion=%d&completa=1&legislatura=10',
    'BASE_SCRIPT' => '/tmp/congreso',       // ensure this folder can be created or it has write permissions.
    'BASE_DOWNLOAD' => '/tmp/congreso/download',
    'NO_EXISTING_THRESHOLD' => 10,          // stop after 10 failed downloads.
    'MINIMUM_FREE_SPACE' => 100000000,      // about 95 Mb to ensure files can be downloaded and unzipped.
    'GET_FILE_CONNECTION_TIMEOUT' => 10,    // connection timeout in seconds.
    'GET_FILE_DOWNLOAD_TIMEOUT' => 90       // download timeout in seconds. Change these two for slow connections.
);
