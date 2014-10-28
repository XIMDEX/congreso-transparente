<?php

include './config.php';

/*
 * Este fichero acepta como parámetro un xml a procesar.
*/

function addContent($fn, $ximtoken, $id_index) {
   global $SERVER_URL_XIMDEX_INSTANCE;

   $result = exec("curl -X POST --data-urlencode content@$fn --data \"ximtoken=$ximtoken&nodeid=$id_index\" ".$SERVER_URL_XIMDEX_INSTANCE."/api/node/contentxml");
   return $result;
}

function execute_call($service = "", $data = array()) {
    global $SERVER_URL_XIMDEX_INSTANCE;
    $ch = curl_init($SERVER_URL_XIMDEX_INSTANCE."/api/$service");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
        return $output;
    }
}

function returnTitleAndDate($filename = "") {
    $data_response = array(
        'date' => '',
        'title' => '',
    );
    $domDoc = new DOMDocument();
    $domDoc->preserveWhiteSpace = false;
    $domDoc->validateOnParse = true;
    $domDoc->formatOutput = true;
    if ($domDoc->loadXML(mb_convert_encoding(file_get_contents($filename), "UTF-8"))) {
        $xpathObj = new DOMXPath($domDoc);
        $data_response['date'] = $xpathObj->query("/Resultado/Informacion/Fecha")->item(0)->nodeValue;
        $data_response['title'] = $xpathObj->query("/Resultado/Informacion/Titulo")->item(0)->nodeValue;
    }
    return $data_response;
}

function insertIntoIndexXML($ximtoken = "", $id_element = "", $sesion = "", $votacion = "", $filename = "") {
    global $INDEX_ID,$SERVER_URL_XIMDEX_INSTANCE;
    $res = returnTitleAndDate($filename);
    $id_index = $INDEX_ID;
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
            if ($nodeList0->length > 0) {
                //Existe Sesion. Comprobar la votacion
                echo "Existe sesion", PHP_EOL;
                foreach ($nodeList0 as $value) {
                    echo "-", PHP_EOL;
                    $votes = $xpathObj->query("Vots/Vot[Numero=$votacion]", $value);
                    if ($votes->length > 0) {
                        //La votacion ya existe
                        echo "Existe votacion", PHP_EOL;
                    }
                    else {
                        $votes_ = $xpathObj->query("Vots", $value)->item(0);
                        $vot_num_ = $domDoc->createElement('Numero', $votacion);
                        $vot_id_ = $domDoc->createElement('Id', $id_element);
                        $vot_date = $domDoc->createElement('Fecha', $res['date']);
                        $vot_title = $domDoc->createElement('Titulo', $res['title']);
                        $vot_ = $domDoc->createElement('Vot');
                        $vot_->appendChild($vot_num_);
                        $vot_->appendChild($vot_id_);
                        $vot_->appendChild($vot_date);
                        $vot_->appendChild($vot_title);
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
                $vot_date = $domDoc->createElement('Fecha', $res['date']);
                $vot_title = $domDoc->createElement('Titulo', $res['title']);
                $vot_ = $domDoc->createElement('Vot');
                $votes_ = $domDoc->createElement('Vots');
                $vot_->appendChild($vot_num_);
                $vot_->appendChild($vot_id_);
                $vot_->appendChild($vot_date);
                $vot_->appendChild($vot_title);
                $votes_->appendChild($vot_);
                $ses_num_ = $domDoc->createElement('Numero', $sesion);
                $ses_ = $domDoc->createElement('Ses');
                $ses_->appendChild($ses_num_);
                $ses_->appendChild($votes_);
                $sessions_->appendChild($ses_);
            }
        }
        $dd = $domDoc->saveXML($domDoc->documentElement);
        $delete = array('<?xml version="1.0"?>');
        $dd_final = str_replace($delete, '', $dd);
        $myFile = "index.tmp";
        $fh = fopen($myFile, 'w') or die("can't open file");
        fwrite($fh, $dd_final);
        fclose($fh);
        $fn = realpath("index.tmp");
        addContent($fn, $ximtoken, $id_index);
    }
}

echo "FILENAME: $argv[0]\n";
echo "PARAMETER: $argv[1]\n";
read_and_delete_first_line($argv[1]);
$voteData = parse_xml_filename($argv[1]);
$workingname = $voteData['full'];
$sesion = $voteData['sesion'];
$votacion = $voteData['votacion'];

$data_login = array(
    'user' => $XIMDEX_USER,
    'pass' => $XIMDEX_PASS,
);
$result = execute_call("login", $data_login);
$result_json = json_decode($result);

if ($result_json->error == 0) {
    $ximtoken = $result_json->data->ximtoken;

    $xml_node_type = "5032";
    $document_folder_id = $DOCUMENT_FOLDER_ID;
    $rng_node = $RNG_NODE_ID;
    $channels = "10001";
    $languages = "es";
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

    if ($result_json->error == 0) {
        $nodeid = $result_json->data->container_langs->es->nodeid;
        $rp_filename = realpath($argv[1]);
        $result = addContent($rp_filename, $ximtoken, $nodeid);
        $result_json = json_decode($result);

        if (gettype($result_json) !== 'NULL' && $result_json->error == 0) {
            $data_publish = array(
                'ximtoken' => $ximtoken,
                'nodeid' => $nodeid,
            );
            $result = execute_call("node/publish", $data_publish);
            $result_json = json_decode($result);
//echo "publish result:\n"; print_r($result_json); echo "\n";
//echo "insertIntoIndexXML: $nodeid, $sesion, $votacion, $rp_filename \n";
            insertIntoIndexXML($ximtoken, $nodeid, $sesion, $votacion, $rp_filename);
            echo "_______________OK!\n";
        }
        else {
            echo "An error occurred (3): 'addContent' missing data or empty response\n";
            if(gettype($result_json) === 'NULL') {echo "IS NULL!!\n";}
            exit;
        }
    }
    else {
        echo "An error occurred (2): 'createxml' failed\n";
        exit;
    }
}
else {
    echo "An error occurred (1): login failed\n";
    print_r ($result_json);
    exit;
}

?>
