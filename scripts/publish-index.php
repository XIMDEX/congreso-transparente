<?php

include './config.php';

function execute_call($service = "", $data = array()) {
    global $SERVER_URL_XIMDEX_INSTANCE;
    $ch = curl_init($SERVER_URL_XIMDEX_INSTANCE."/api/$service");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($ch);
}

function publishIndexXML($ximtoken = "") {
    global $INDEX_ID;
    $id_index = $INDEX_ID;
    $data_publish = array(
        'ximtoken' => $ximtoken,
        'nodeid' => $id_index,
    );
    $result = execute_call("node/publish", $data_publish);
    $result_json = json_decode($result);
    echo "publish index result:\n"; print_r($result_json); echo "\n";
}

$data_login = array(
    'user' => $XIMDEX_USER,
    'pass' => $XIMDEX_PASS,
);
$result = execute_call("login", $data_login);
$result_json = json_decode($result);
if ($result_json->error == 0) {
    $ximtoken = $result_json->data->ximtoken;
    publishIndexXML($ximtoken);
}

?>
