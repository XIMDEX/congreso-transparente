<?php

function cierre() {
    $error = error_get_last();
    echo print_r($error, true), PHP_EOL;
}

register_shutdown_function('cierre');

require_once __DIR__ . '/LoaderProcessor.php';
require_once __DIR__ . '/logger.php';

/*
 * Downloads zip files, uncompress them and send to process.
 */

class Scrapper {

    private $config;
    private $ini;
    private $processor;

    function __construct() {
        // Load config file
        $this->config = require_once __DIR__ . '/config.php';
        if (empty($this->config)) {
            logger('ERROR', 'failed to read config file!');
            exit;
        }
        // Load data from last import
        $this->ini = parse_ini_file($this->config['IMPORT_FILE']);
        if (empty($this->ini) || !isset($this->ini['last_imported'])) {
            logger('ERROR', 'failed to read ' . $this->config['IMPORT_FILE'] . ' file!');
            exit;
        }

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
                logger('ERROR', "mkdir failed: $pathzips");
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
        logger('INFO', "common_handle: $url");
        preg_match_all("|\d+|", $url, $match, PREG_SET_ORDER);
        $folder = '';
        foreach ($match as $num) {
            $folder .= $num[0];
        }
        $pathzipsFolder = $this->config['MEDIA_ROOT'] . "/zips";
        $pathxml = "$pathzipsFolder/$folder";
        $zipFile = "{$pathxml}.zip";

        if (!$this->get_zip($url, $pathzipsFolder, $zipFile)) {
            logger('WARN', "file not found: $url");
            return false;
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

        return true;
    }

    function run($params) {
        logger('INFO', "==================================================");
        logger('INFO', "Start import process");
        logger('INFO', "==================================================");

        // Clean previously downloaded files
        $baseZips = $this->config['MEDIA_ROOT'] . "/zips";
        if (is_dir($baseZips)) {
            exec("rm -rf $baseZips");
        }
        if (!mkdir($baseZips)) {
            logger('ERROR', "folder for files could not be created at: $baseZips");
            exit;
        }

        $lastImported = (int) $this->ini['last_imported'];
        $first = $lastImported + 1;
        $defaultCounter = (int) $this->config['IMPORT_COUNTER'];
        $last = $first + $defaultCounter;
        $counter = 0;

        // Start batch import
        logger('INFO', "start import batch from [$first to $last]");
        for ($i = $first; $i < $last; $i++) {
            logger('INFO', "importing $i");
            $url = sprintf($this->config['BASE_URL'], $i);

            if ($this->common_handle($url)) {
                $counter++;
                $lastImported = $i;
            }
        }

        $this->processor->publishNode($this->config['INDEX_ID']);

        // Log and finish
        logger('INFO', "imported counter: $counter");
        logger('INFO', "last imported session: $lastImported");
        file_put_contents($this->config['IMPORT_FILE'], "last_imported = $lastImported");
    }

}

$main = new Scrapper();
$main->run($argv);
