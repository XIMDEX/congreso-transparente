<?php

function execute_call($service = "", $data = array()) {
    $ch = curl_init("http://192.168.100.212/ximdexcongreso/api/$service");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array());
    return curl_exec($ch);
}

function publishIndexXML($ximtoken = "") {
    $id_index = "10109";
    $data_publish = array(
        'ximtoken' => $ximtoken,
        'nodeid' => $id_index,
    );
    $result = execute_call("node/publish", $data_publish);
    $result_json = json_decode($result);
    // var_dump($result_json);
}

$user = "ximdex";
$pass = "ximdex";
$data_login = array(
    'user' => $user,
    'pass' => $pass,
);
$result = execute_call("login", $data_login);
$result_json = json_decode($result);
if ($result_json->error == 0) {
    $ximtoken = $result_json->data->ximtoken;
    publishIndexXML($ximtoken);
}

?>
