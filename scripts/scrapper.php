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

        try {
            $this->processor = new LoaderProcessor($this->config);
        } catch (Exception $ex) {
            logger("FATAL", $ex);
            exit;
        }
    }

    /*
     * Download xml file
     * $url url to resource
     * $zipFile destination file
     */

    function get_file($url, $zipFile) {
        $free = (int) disk_free_space($this->config['BASE_DOWNLOAD']);
        if ($free < $this->config['MINIMUM_FREE_SPACE']) {
            return false;
        }

        $fp = fopen($zipFile, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return (is_file($zipFile) && filesize($zipFile) > 0);
    }

    /*
     * Download xml zip file
     */

    function get_zip($url, $zipFile) {
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
        $pathxml = $this->config['BASE_DOWNLOAD'] . "/$folder";
        $zipFile = "{$pathxml}.zip";

        if (!$this->get_zip($url, $zipFile)) {
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
                    try {
                        $this->processor->process("$pathxml/$entry");
                    } catch (Exception $ex) {
                        logger('ERROR', 'EXCEPTION:');
                        logger('ERROR', $ex->getMessage());
                    }
                }
            }
        }

        return true;
    }

    function run($params) {
        logger('INFO', "==================================================");
        logger('INFO', "Start import process");
        logger('INFO', "==================================================");

        // Clean previously downloaded files and init folders
        if (is_dir($this->config['BASE_SCRIPT'])) {
            exec("rm -rf " . $this->config['BASE_SCRIPT']);
        }
        if (!mkdir($this->config['BASE_SCRIPT'])) {
            logger('ERROR', "1folder for files could not be created at: " . $this->config['BASE_SCRIPT']);
            exit;
        }
        if (!mkdir($this->config['BASE_DOWNLOAD'])) {
            logger('ERROR', "2folder for files could not be created at: " . $this->config['BASE_DOWNLOAD']);
            exit;
        }
        if (!mkdir($this->config['PARTIALS_INDEX'])) {
            logger('ERROR', "3folder for files could not be created at: " . $this->config['PARTIALS_INDEX']);
            exit;
        }

        // get data from last imported
        $lastImported = (int) $this->ini['last_imported'];
        $first = $lastImported + 1;
        $defaultCounter = (int) $this->config['IMPORT_COUNTER'];
        $last = $first + $defaultCounter;
        $counter = 0;
        $noExistingCounter = 0;

        // Start batch import
        logger('INFO', "start import batch from [$first to " . ($last - 1) . "]");
        for ($i = $first; $i < $last; $i++) {
            logger('INFO', "importing $i");
            $url = sprintf($this->config['BASE_URL'], $i);

            if ($this->common_handle($url)) {
                $counter++;
                $lastImported = $i;
                $noExistingCounter = 0;
            } else {
                $noExistingCounter++;
                if ($noExistingCounter > $this->config['NO_EXISTING_THRESHOLD']) {
                    logger('WARN', "too many empty consecutive files (stop)");
                    break;
                }
            }
        }

        try {
            $this->processor->publishIndexNode();
        } catch (Exception $ex) {
            logger('ERROR', 'EXCEPTION: failed to publish index node');
        }

        // Log and finish
        logger('INFO', "imported counter: $counter");
        logger('INFO', "last imported session: $lastImported");
        file_put_contents($this->config['IMPORT_FILE'], "last_imported = $lastImported");
    }

}

$main = new Scrapper();
$main->run($argv);
