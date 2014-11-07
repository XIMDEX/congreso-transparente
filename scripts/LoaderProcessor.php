<?php

require_once __DIR__ . '/logger.php';

class LoaderProcessor {

    private $config = array();
    private $ximtoken = '';
    private $indexSess = null;

    public function __construct($config) {
        $this->config = $config;
        // do login (get api token)
        $this->doLogin();

        // load index node DOM
        $this->indexSess = $this->loadIndexData();
    }

    /*
     * add Content to ximdex node
     * $fileContentStr content of file as string
     * $nodeid node id
     */

    private function addContentString($fileContentStr, $nodeid) {
        $data = "ximtoken={$this->ximtoken}&nodeid=$nodeid&content=" . urlencode($fileContentStr);
        $ch = curl_init($this->config['URL_XIMDEX'] . '/node/contentxml');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return curl_exec($ch);
    }

    /*
     * add Content to ximdex node
     * $fn absolute path to file
     * $nodeid node id
     */

    private function addContentFile($fn, $nodeid) {
        $fileContentStr = file_get_contents($fn);
        return $this->addContentString($fileContentStr, $nodeid);
    }

    /*
     * generic call for several api services
     * $service service name
     * $data array with data needed for service
     */

    private function execute_call($service = "", $data = array()) {
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

    private function read_and_delete_first_line($filename = "") {
        $fileString = file_get_contents($filename);
        $filteredString = preg_replace('/<\?xml([^<]+)>/', '', $fileString);
        file_put_contents($filename, $filteredString);
    }

    /*
     * Extract values for 'sesion' and 'votacion'
     * $filename absolute path to file
     */

    private function parse_xml_filename($filename = "") {
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

    private function analizeResult($res) {
        $result_json = json_decode($res, true);
        if (isset($result_json['error'])) {
            return $result_json;
        } else {
            if ($result_json === null) {
                $err = 'bad server response:';
            } else if (!isset($result_json['error'])) {
                $err = 'missing error field:';
            }

            $err .= "\n" . print_r($res, true);
            $err .= "\n" . print_r($result_json, true);
            throw new Exception($err);
        }
    }

    private function addToIndex($data) {
        $partialXml = $this->createPartialIndex($data);
        $this->insertIntoIndexXML($partialXml);
        // save
        $fileContentString = preg_replace('/<\?xml([^<]+)>/', '', $this->indexSess->asXML());
        $this->addContentString($fileContentString, $this->config['INDEX_ID']);
    }

    /*
     * Request index node content and extract data.
     * $ximtoken login token
     */

    private function loadIndexData() {
        $data_request = array(
            'ximtoken' => $this->ximtoken,
            'nodeid' => $this->config['INDEX_ID'],
        );
        $result = $this->execute_call("node/getcontent", $data_request);
        $result_json = $this->analizeResult($result);

        try {
            $domDoc = new SimpleXMLElement($result_json['data']);
            if ($domDoc === false) {
                $err .= "could not parse index content as xml:";
                $err .= "\n" . print_r($result, true);
                $err .= "\n" . print_r($result_json, true);
                throw new Exception($err);
            }
        } catch (Exception $ex) {
            $err = "SimpleXMLElement constructor failed: " . $ex->getMessage();
            $err .= "\n" . print_r($result, true);
            $err .= "\n" . print_r($result_json, true);
            throw new Exception($err);
        }


        return $domDoc;
    }

    private function createPartialIndex($data) {
        $partial = new SimpleXMLElement('<partial></partial>');
        $Ses = $partial->addChild('Ses');
        $Ses->Numero = $data['sesion'];
        $Vots = $Ses->addChild("Vots");
        $Vot = $Vots->addChild('Vot');
        $Vot->Numero = $data['votacion'];
        $Vot->Id = $data['id'];
        $Vot->Fecha = $data['date'];
        $Vot->Titulo = $data['title'];
        return $partial;
    }

    private function writePartial($partialXml, $Vot) {
        $Vot->Numero = $partialXml->Ses->Vots->Vot->Numero;
        $Vot->Id = $partialXml->Ses->Vots->Vot->Id;
        $Vot->Fecha = $partialXml->Ses->Vots->Vot->Fecha;
        $Vot->Titulo = $partialXml->Ses->Vots->Vot->Titulo;
    }

    private function addNewPartial($partialXml, $Ses) {
        $Ses->Numero = $partialXml->Ses->Numero;
        $Vots = $Ses->addChild("Vots");
        $Vot = $Vots->addChild('Vot');
        $this->writePartial($partialXml, $Vot);
    }

    /*
     * Dump SimpleXMLElement content into index node
     * $Sess SimpleXMLElement object
     * $data array with fields
     */

    private function insertIntoIndexXML($partialXml) {
        $partialSesionNumero = $partialXml->Ses->Numero;
        $partialSesionVotsVot = $partialXml->Ses->Vots->Vot->Numero;
        $Ses = $this->indexSess->xpath("/Sess/Ses[Numero=$partialSesionNumero]");

        if (empty($Ses)) {
            $Ses = $this->indexSess->addChild("Ses");
            $this->addNewPartial($partialXml, $Ses);
        } else {
            $Ses = $Ses[0];
            $Vot = $Ses->xpath("Vots/Vot[Numero=$partialSesionVotsVot]");
            if (empty($Vot)) {
                $this->addNewPartial($partialXml, $Ses);
            } else {
                $Vot = $Vot[0];
                $this->writePartial($partialXml, $Vot);
            }
        }
    }

    /*
     * Extract title and date info from xml file
     * $filename Absolute path to file
     */

    private function returnTitleAndDate($filename = "") {
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

    private function doLogin() {
        $credentials = array('user' => $this->config['XIMDEX_USER'], 'pass' => $this->config['XIMDEX_PASS']);
        $result = $this->execute_call("login", $credentials);
        $result_json = $this->analizeResult($result);
        if (!isset($result_json['data']['ximtoken'])) {
            logger('FATAL', 'token could not be retrieved!');
            exit;
        }
        $this->ximtoken = $result_json['data']['ximtoken'];
        logger('INFO', "TOKEN: {$this->ximtoken}");
    }

    private function publishNode($nodeid) {
        $data_publish = array(
            'ximtoken' => $this->ximtoken,
            'nodeid' => $nodeid,
        );
        return $this->execute_call('node/publish', $data_publish);
    }

    public function publishIndexNode() {
        $data_publish = array(
            'ximtoken' => $this->ximtoken,
            'nodeid' => $this->config['INDEX_ID'],
        );
        return $this->execute_call('node/publish', $data_publish);
    }

    /*
     * Process downloaded xml file
     * $path Absolute path to file
     */

    public function process($path) {
        logger('INFO', "path: $path");
        $this->read_and_delete_first_line($path);
        $voteData = $this->parse_xml_filename($path);
        $workingname = $voteData['full'];
        $sesion = $voteData['sesion'];
        $votacion = $voteData['votacion'];
        logger('INFO', "import: sesion=$sesion :: votacion=$votacion");

        // create node
        $lang = $this->config['LANG_SUFFIX'];
        $data_request_1 = array(
            'ximtoken' => $this->ximtoken,
            'nodeid' => $this->config['DOCUMENT_FOLDER_ID'],
            'name' => $workingname,
            'id_schema' => $this->config['RNG_NODE_ID'],
            'channels' => $this->config['PUBLISH_CHANNELS_IDS'],
            'languages' => $lang,
        );
        $result = $this->execute_call('node/createxml', $data_request_1);
        $result_json = $this->analizeResult($result);

        // fill node content, overwrite if already exists
        if (!isset($result_json['data']['container_langs'][$lang]['nodeid'])) {
            logger('WARN', "failed to create node: $workingname");
            if (isset($result_json['message'])) {
                logger('WARN', $result_json['message']);
            }
            $search = array(
                'ximtoken' => $this->ximtoken,
                'name' => $workingname . $this->config['DOC_SUFFIX']
            );
            $result = $this->execute_call("search", $search);
            $result_json = $this->analizeResult($result);
            if ($result_json['error'] === 0 && isset($result_json['data']) && count($result_json['data']) == 1) {
                $nodeid = $result_json['data'][0]['IdNode'];
                logger('INFO', "node $workingname was found with id $nodeid (overwrite)");
            } else {
                $err = "'$workingname' could not be unambiguously resolved";
                $err .= "\n" . print_r($result_json, true);
                throw new Exception($err);
            }
        } else {
            $nodeid = $result_json['data']['container_langs'][$lang]['nodeid'];
            logger('INFO', "node $workingname will be created (new): $nodeid");
        }
        $this->analizeResult($this->addContentFile($path, $nodeid));

        // publish node
        $this->analizeResult($this->publishNode($nodeid));

        // add info to index node
        $res = $this->returnTitleAndDate($path);
        $data = array(
            'sesion' => $sesion,
            'votacion' => $votacion,
            'id' => $this->config['INDEX_ID'],
            'date' => $res['date'],
            'title' => $res['title'],
        );

        $this->addToIndex($data);
    }

}
