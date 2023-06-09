<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;


class DUPX_REST
{
    /**
     *
     */
    const DUPLICATOR_NAMESPACE = 'duplicator/v1/';

    /**
     *
     * @var string
     */
    private $nonce = false;

    /**
     *
     * @var string
     */
    private $basicAuthUser = "";

    /**
     *
     * @var string
     */
    private $basicAuthPassword = "";

    /**
     *
     * @var string
     */
    private $url = false;

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $paramsManager = PrmMng::getInstance();
        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        if (is_array($overwriteData)) {
            if (
                isset($overwriteData['restUrl']) &&
                strlen($overwriteData['restUrl']) > 0 &&
                isset($overwriteData['restNonce']) &&
                strlen($overwriteData['restNonce']) > 0
            ) {
                $this->url   = SnapIO::untrailingslashit($overwriteData['restUrl']);
                $this->nonce = $overwriteData['restNonce'];
            }

            if (strlen($overwriteData['restAuthUser']) > 0) {
                $this->basicAuthUser     = $overwriteData['restAuthUser'];
                $this->basicAuthPassword = $overwriteData['restAuthPassword'];
            }
        }
    }

    public function checkRest($reset = false, &$errorMessage = "")
    {
        static $success = null;
        if (is_null($success) || $reset) {
            try{
                $success = true;
                if ($this->nonce === false) {
                    throw new Exception("Nonce is not set.");
                }

                if (strlen($testUrl  = $this->getRestUrl('versions')) === 0) {
                    throw new Exception("Couldn't get REST API backed URL to do tests. REST API URL was empty.");
                }

                $response = Requests::get($testUrl, array(), $this->requestAuthOptions());
                if ($response->success == false) {
                    Log::info(Log::v2str($response));
                    throw new Exception("REST API request on $testUrl failed");
                }

                if (($result = json_decode($response->body, true)) === null) {
                    throw new Exception("Can't decode json.");
                }

                if (!isset($result["dup"])) {
                    throw new Exception("Did not receive the expected result.");
                }
            } catch (Exception $ex) {
                $success = false;
                $errorMessage = $ex->getMessage();
                Log::info("FAILED REST API CHECK. MESSAGE: " . $ex->getMessage());
            }
        }
        return $success;
    }

    public function getVersions()
    {
        $response = Requests::get($this->getRestUrl('versions'), array(), $this->requestAuthOptions());
        if (!$response->success) {
            return false;
        }

        if (($result = json_decode($response->body)) === null) {
            return false;
        }

        return $result;
    }

    /**
     * Create new subsites
     *
     * @param string $data
     * @param int    $numSubisites
     * @param string $errorMessage
     * 
     * @return array
     */
    public function subsiteActions($data, $numSubisites, &$errorMessage = '') {
        if (Log::isLevel(Log::LV_DETAILED)) {
            Log::info('SUBSITE ACTION CALL NUM SUBISTES ' . $numSubisites . ' DATA: ' . $data);
        }

        $options = $this->requestAuthOptions();

        // ten seconds foreach subsite
        $options['timeout'] = 10 * max(1, $numSubisites);

        $response = Requests::post(
            $this->getRestUrl('multisite/subsite/actions'),
            array(),
            array(
                'data'   => $data
            ),
            $options
        );

        if (!$response->success) {
            Log::info('REST CALL FAIL REPONSE: ' . Log::v2str($response));
            return false;
        } elseif (Log::isLevel(Log::LV_DEBUG)) {
            Log::info('REST CALL REPONSE: ' . Log::v2str($response));
        }

        if (($result = json_decode($response->body, true)) === null) {
            Log::info('REST CALL: can\'t decode json ' . $response->body);
            return false;
        }

        if (!$result['success']) {
            $errorMessage = $result['message'];
            return false;
        }

        return $result['subsitesInfo'];
    }

    private function requestAuthOptions()
    {
        return array(
            'auth'   => new DUPX_REST_AUTH($this->nonce, $this->basicAuthUser, $this->basicAuthPassword),
            //'verify' => DUPX_INIT . '/lib/certificates/cacert.pem'
            'verify' => false,
            'verifyname' => false
        );
    }

    private function getRestUrl($subPath = '')
    {
        return $this->url ? $this->url . '/' . self::DUPLICATOR_NAMESPACE . $subPath : '';
    }
}
