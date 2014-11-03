<?php

/*
 * Downloads zip files, uncompress them and send to process.
 */

class Scrapper {

    private $config;
    private $processor;

    function __construct() {
        $this->config = require_once __DIR__ . '/config.php';
        require_once __DIR__ . '/LoaderProcessor.php';
        $this->processor = new LoaderProcessor($this->config);
    }

    /*
     * Download xml file
     * $url url to resource
     * $zipFile destination file
     */

    function get_file($url, $zipFile) {
        $fp = fopen($zipFile, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return (filesize($zipFile) > 0);
    }

    /*
     * Download xml zip file
     */

    function get_zip($url, $pathzips, $zipFile) {
        if (!is_dir($pathzips)) {
            if (!mkdir($pathzips)) {
                echo "mkdir failed: $pathzips \n";
                exit;
            }
        }
        if (!is_file($zipFile)) {
            return $this->get_file($url, $zipFile);
        }
        return true;
    }

    function extract_files($pathzip, $pathunzip) {
        $zip = new ZipArchive();
        if ($zip->open($pathzip)) {
            $zip->extractTo($pathunzip);
            $zip->close();
        } else {
            unlink($pathzip);
        }
    }

    function common_handle($url) {
        echo "INFO common_handle: $url \n";
        preg_match_all("|\d+|", $url, $match, PREG_SET_ORDER);
        $folder = '';
        foreach ($match as $num) {
            $folder .= $num[0];
        }
        $pathzipsFolder = $this->config['MEDIA_ROOT'] . "/zips";
        $pathxml = "$pathzipsFolder/$folder";
        $zipFile = "{$pathxml}.zip";

        if (!$this->get_zip($url, $pathzipsFolder, $zipFile)) {
            echo "WARN file not found: $url \n";
            return;
        }

        if (!is_dir($pathxml)) {
            $this->extract_files($zipFile, $pathxml);
        }
        if ($handle = opendir($pathxml)) {
            $this->extract_files($zipFile, $pathxml);

            while (false !== ($entry = readdir($handle))) {
                if (is_file(realpath("$pathxml/$entry"))) {
                    $this->processor->process("$pathxml/$entry");
                }
            }
        }
    }

    function getParam($params, $index) {
        if (!isset($params[$index])) {
            echo "ERROR $index param must be present\n";
            exit;
        }
        return $params[$index];
    }

    function run($params) {
        if (empty($this->config)) {
            echo "ERROR failed to read config file!\n";
            exit;
        }

        exec("rm -rf " . $this->config['MEDIA_ROOT'] . "/zips/*");
        $first = (int) $this->getParam($params, 1);
        $last = (int) $this->getParam($params, 2);
        if ($last <= $first) {
            echo "ERROR incorrect parameters: 'last' must be greater than 'first'\n";
        }

        echo "$first - $last\n";
        for ($i = $first; $i < $last; $i++) {
            echo "INFO import $i\n";
            $url = sprintf($this->config['BASE_URL'], $i);
            $this->common_handle($url);
        }

        $this->processor->publishNode($this->config['INDEX_ID']);
    }

}

$main = new Scrapper();
$main->run($argv);
?>
