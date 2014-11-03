<?php

class LoaderProcessor {

    private $config = array();
    private $ximtoken = '';

    function __construct($config) {
        $this->config = $config;
    }

    /*
     * add Content to ximdex node
     * $fn absolute path to file
     * $ximtoken token received with login
     * $id_index index node id
     */

    function addContent($fn, $id_index) {
        $result = exec("curl -X POST --data-urlencode content@$fn --data \"ximtoken={$this->ximtoken}&nodeid=$id_index\" " . $this->config['URL_XIMDEX'] . "/node/contentxml");
        return $result;
    }

    /*
     * generic call for several api services
     * $service service name
     * $data array with data needed for service
     */

    function execute_call($service = "", $data = array()) {
        $ch = curl_init($this->config['URL_XIMDEX'] . "/$service");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($ch);
    }

    /*
     * add Content to ximdex node
     * $fn absolute path to file
     * $ximtoken token received with login
     * $id_index index node id
     */

    function read_and_delete_first_line($filename = "") {
        $file = file($filename);
        $output = $file[0];
        if (strpos($output, 'xml') !== false) {
            unset($file[0]);
            file_put_contents($filename, $file);
            return $output;
        }
    }

    /*
     * Extract values for 'sesion' and 'votacion'
     * $filename absolute path to file
     */

    function parse_xml_filename($filename = "") {
        $slots = explode("/", $filename);
        $name = explode(".", $slots[sizeof($slots) - 1]);
        $matches = array();
        $data = array();
        $data['full'] = $name[0];
        preg_match('/sesion([0-9]+)/', $name[0], $matches);
        $data['sesion'] = $matches[1];
        preg_match('/votacion([0-9]+)/', $name[0], $matches);
        $data['votacion'] = $matches[1];
        return $data;
    }

    /*
     * Parse server response, exit if error
     * $res response received from server
     */

    function analizeResult($res) {
        $result_json = json_decode($res, true);
        if (isset($result_json['error'])) {
            return $result_json;
        } else {
            if ($result_json === null) {
                echo "ERROR bad server response:\n";
            } else if (!isset($result_json['error'])) {
                echo "ERROR missing error field:\n";
            }

            echo print_r($res, true), "\n";
            echo print_r($result_json, true), "\n";
            exit;
        }
    }

    /*
     * Request index node content and extract data.
     * $ximtoken login token
     */

    function loadIndexData() {
        $data_request = array(
            'ximtoken' => $this->ximtoken,
            'nodeid' => $this->config['INDEX_ID'],
        );
        $result = $this->execute_call("node/getcontent", $data_request);
        $result_json = $this->analizeResult($result);

        $domDoc = new SimpleXMLElement($result_json['data']);
        if ($domDoc === false) {
            echo "ERROR could not parse index content as xml:\n";
            print_r($result_json);
            echo "\n";
            exit;
        }

        return $domDoc;
    }

    /*
     * Dump SimpleXMLElement content into index node
     * $Sess SimpleXMLElement object
     * $data array with fields
     */

    function insertIntoIndexXML($Sess, $data) {
        $Ses = $Sess->xpath("/Sess/Ses[Numero=" . $data['sesion'] . "]");
        // check existing session
        if ($Ses === false || (is_array($Ses) && count($Ses) == 0)) {
            $Ses = $Sess->addChild("Ses");
        } else {
            $Ses = $Ses[0];
        }
        $Ses->Numero = $data['sesion'];

        // check votes
        $Vots = $Ses->xpath("Vots");
        if ($Vots === false || (is_array($Vots) && count($Vots) == 0)) {
            $Vots = $Ses->addChild("Vots");
        } else {
            $Vots = $Vots[0];
        }

        // check this vote
        $Vot = $Ses->xpath("Vots/Vot[Numero=" . $data['votacion'] . "]");
        if ($Vot === false || (is_array($Vot) && count($Vot) == 0)) {
            $Vot = $Vots->addChild('Vot');
        } else {
            $Vot = $Vot[0];
        }

        // fill data (overwrite existing)
        $Vot->Numero = $data['votacion'];
        $Vot->Id = $data['id'];
        $Vot->Fecha = $data['date'];
        $Vot->Titulo = $data['title'];
    }

    /*
     * Extract title and date info from xml file
     * $filename Absolute path to file
     */

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

    function publishNode($nodeid) {
        $data_publish = array(
            'ximtoken' => $this->ximtoken,
            'nodeid' => $nodeid,
        );
        return $this->execute_call("node/publish", $data_publish);
    }

    function doLogin() {
        $credentials = array('user' => $this->config['XIMDEX_USER'], 'pass' => $this->config['XIMDEX_PASS']);
        $result = $this->execute_call("login", $credentials);
        $result_json = $this->analizeResult($result);
        $this->ximtoken = $result_json['data']['ximtoken'];
    }

    /*
     * Process downloaded xml file
     * $path Absolute path to file
     */

    function process($path) {
        echo "path: $path\n";
        $this->read_and_delete_first_line($path);
        $voteData = $this->parse_xml_filename($path);
        $workingname = $voteData['full'];
        $sesion = $voteData['sesion'];
        $votacion = $voteData['votacion'];
        echo "import: sesion=$sesion :: votacion=$votacion \n";

        // do login (get api token)
        $this->doLogin();

        // load index node DOM
        $sess = $this->loadIndexData();

        // create node
        $data_request_1 = array(
            'ximtoken' => $this->ximtoken,
            'nodeid' => $this->config['DOCUMENT_FOLDER_ID'],
            'name' => $workingname,
            'id_schema' => $this->config['RNG_NODE_ID'],
            'channels' => "10001",
            'languages' => "es",
        );
        $result = $this->execute_call("node/createxml", $data_request_1);
        $result_json = $this->analizeResult($result);

        // fill node content
        $nodeid = $result_json['data']['container_langs']['es']['nodeid'];
        $result = $this->addContent($path, $nodeid);
        $result_json = $this->analizeResult($result);

        // publish node
        $result = $this->publishNode($nodeid);
        $result_json = $this->analizeResult($result);

        // add info to index node
        $res = $this->returnTitleAndDate($path);
        $data = array(
            'sesion' => $sesion,
            'votacion' => $votacion,
            'id' => $this->config['INDEX_ID'],
            'date' => $res['date'],
            'title' => $res['title'],
        );
        $this->insertIntoIndexXML($sess, $data);

        //publish index
        $tmpfname = tempnam("/tmp", 'ximdex');
        $xmlText = str_replace('<?xml version="1.0"?>', '', $sess->asXML());
        file_put_contents($tmpfname, $xmlText);
        $result = $this->addContent($tmpfname, $this->config['INDEX_ID']);
        $this->analizeResult($result);
        unlink($tmpfname);
    }

}

?>
