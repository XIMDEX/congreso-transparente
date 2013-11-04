<?php

/*
 * Este fichero acepta como parámetro un xml a procesar.
*/

function execute_call($service = "", $data = array()) {
	// TODO Cambiar la url de XImdex
    $ch = curl_init("http://ximdex_url/api/$service");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array());
    return curl_exec($ch);
}

function parse_xml_filename($filename = "") {
    $slots = explode("/", $filename);
    $name = explode(".", $slots[sizeof($slots)-1]);
    $matches = array();
    $data = array();
    $data['full'] = $name[0];
    preg_match('/sesion([0-9]+)/', $name[0], $matches);
    $data['sesion'] = $matches[1];
    preg_match('/votacion([0-9]+)/', $name[0], $matches);
    $data['votacion'] = $matches[1];
    return $data;
}

function read_and_delete_first_line($filename = "") {
    $file = file($filename);    
    $output = $file[0];
    if (strpos($output,'xml') !== false) {
        unset($file[0]);
        file_put_contents($filename, $file);
        echo "$output\n";
        return $output;
    }
}

function insertIntoIndexXML($ximtoken = "", $id_element = "", $sesion = "", $votacion = "") {
    $id_index = "10109";
    $data_request = array(
        'ximtoken' => $ximtoken,
        'nodeid' => $id_index,
    );
    $result = execute_call("node/getcontent", $data_request);
    $result_json = json_decode($result);
    if ($result_json->error == 0) {
        $domDoc = new DOMDocument();
        $domDoc->preserveWhiteSpace = false;
        $domDoc->validateOnParse = true;
        $domDoc->formatOutput = true;
        if ($domDoc->loadXML($result_json->data)) {
            $xpathObj = new DOMXPath($domDoc);
            $nodeList0 = $xpathObj->query("/Sess/Ses[Numero=$sesion]");
            var_dump($nodeList0);
            if ($nodeList0->length > 0) {
                //Existe Sesion. Comprobar la votacion
                echo "Existe sesion", PHP_EOL;
                foreach ($nodeList0 as $value) {
                    echo "-", PHP_EOL;
                    $votes = $xpathObj->query("Vots/Vot[Numero=$votacion]", $value);
                    if ($votes->length > 0) {
                        //La votacion ya existe
                        echo "Existe votacion", PHP_EOL;
                        // foreach ($votes as $value_) {
                        //     var_dump($value_);
                        // }
                    }
                    else {
                        $votes_ = $xpathObj->query("Vots", $value)->item(0);
                        $vot_num_ = $domDoc->createElement('Numero', $votacion);
                        $vot_id_ = $domDoc->createElement('Id', $id_element);
                        $vot_ = $domDoc->createElement('Vot');
                        $vot_->appendChild($vot_num_);
                        $vot_->appendChild($vot_id_);
                        $votes_->appendChild($vot_);
                    }
                }
            }
            else {
                //No existe sesion. Insertar sesion y votacion
                echo "No existe sesion", PHP_EOL;
                $sessions_ = $domDoc->getElementsByTagName('Sess')->item(0);
                $vot_num_ = $domDoc->createElement('Numero', $votacion);
                $vot_id_ = $domDoc->createElement('Id', $id_element);
                $vot_ = $domDoc->createElement('Vot');
                $votes_ = $domDoc->createElement('Vots');
                $vot_->appendChild($vot_num_);
                $vot_->appendChild($vot_id_);
                $votes_->appendChild($vot_);
                $ses_num_ = $domDoc->createElement('Numero', $sesion);
                $ses_ = $domDoc->createElement('Ses');
                $ses_->appendChild($ses_num_);
                $ses_->appendChild($votes_);
                $sessions_->appendChild($ses_);
            }
        }
        $dd = $domDoc->saveXML();
        $delete = array('<?xml version="1.0"?>');
        $dd_final = str_replace($delete, '', $dd);
        $myFile = "index.tmp";
        $fh = fopen($myFile, 'w') or die("can't open file");
        fwrite($fh, $dd_final);
        fclose($fh);
        $fn = realpath("index.tmp");
        $result = exec("curl -X POST --data-urlencode content@$fn --data \"ximtoken=$ximtoken&nodeid=$id_index\" http://192.168.100.233/ximdexcongreso/api/node/contentxml");
        // $data_publish = array(
        //     'ximtoken' => $ximtoken,
        //     'nodeid' => $id_index,
        // );
        // $result = execute_call("node/publish", $data_publish);
        // $result_json = json_decode($result);
        // var_dump($result_json);
    }
    else {
        //
    }
}

echo "FILENAME: $argv[0]\n";
echo "PARAMETER: $argv[1]\n";
read_and_delete_first_line($argv[1]);
$voteData = parse_xml_filename($argv[1]);
$workingname = $voteData['full'];
$sesion = $voteData['sesion'];
$votacion = $voteData['votacion'];

// TODO Insertar el usuario y la password de Ximdex con permisos de administración
$user = "";
$pass = "";
$data_login = array(
    'user' => $user,
    'pass' => $pass,
);
$result = execute_call("login", $data_login);
$result_json = json_decode($result);
if ($result_json->error == 0) {
    $ximtoken = $result_json->data->ximtoken;
    // echo "XIMTOKEN: $ximtoken\n";
	// TODO Añadir ID de tipo de nodo XML
    $xml_node_type = "";
	// TODO Añadir ID de la carpeta 'documents' del servidor virtual
    $document_folder_id = "";
	// TODO Añadir ID de nodo de la RNG de los resultados de las votaciones
    $rng_node = "";
	// TODO Añadir ID del canal a publicar (HTML = 10001)
    $channels = "";
	// TODO Añadir nombre corto del lenguaje a publicar ('es' para español)
    $languages = "";
    $data_request_1 = array(
        'ximtoken' => $ximtoken,
        'nodeid' => $document_folder_id,
        'name' => $workingname,
        'id_schema' => $rng_node,
        'channels' => $channels,
        'languages' => $languages,
    );
    $result = execute_call("node/createxml", $data_request_1);
    $result_json = json_decode($result);
    var_dump($result_json);
    if ($result_json->error == 0) {
        $nodeid = $result_json->data->container_langs->es->nodeid;
        $rp_filename = realpath($argv[1]);
        $curldatafile = new CurlFile($rp_filename, 'text/xml', 'content');
        $data_request_2 = array(
            'ximtoken' => $ximtoken,
            'nodeid' => $nodeid,
            'file' => $curldatafile,
        );
        // $result = execute_call("node/contentxml", $data_request_2);
        $result = exec("curl -X POST --data-urlencode content@$rp_filename --data \"ximtoken=$ximtoken&nodeid=$nodeid\" http://192.168.100.233/ximdexcongreso/api/node/contentxml");
        $result_json = json_decode($result);
        var_dump($result_json);
        if ($result_json->error == 0) {
            $data_publish = array(
                'ximtoken' => $ximtoken,
                'nodeid' => $nodeid,
            );
            $result = execute_call("node/publish", $data_publish);
            $result_json = json_decode($result);
            // var_dump($result_json);
            insertIntoIndexXML($ximtoken,$nodeid,$sesion,$votacion);
            echo "_______________OK!\n";
        }
        else {
            echo "An error occurred (3)\n";
        }
    }
    else {
        echo "An error occurred (2)\n";
    }
}
else {
    echo "An error occurred (1)\n";
}

?>
