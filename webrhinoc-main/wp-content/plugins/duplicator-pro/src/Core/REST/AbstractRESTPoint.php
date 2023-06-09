<?php

/**
 * Controller interface
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Core\REST;

use DUP_PRO_Log;
use Duplicator\Libs\Snap\SnapLog;

abstract class AbstractRESTPoint
{
    const REST_NAMESPACE = 'duplicator/v1';

    protected $args     = array();
    protected $override = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->args['methods']             = $this->getMethods();
        $this->args['callback']            = array($this, 'callback');
        $this->args['permission_callback'] = array($this, 'permission');
        $this->args['args']                = $this->getArgs();
    }

    /**
     * Get current endpoint route
     *
     * @return string
     */
    abstract protected function getRoute();

    /**
     * Rest api permission callback
     *
     * @param \WP_REST_Request $request REST request
     *
     * @return boolean
     */
    abstract public function permission(\WP_REST_Request $request);

    /**
     * Return methods of current rest point
     *
     * @return string|array
     */
    protected function getMethods()
    {
        return 'GET';
    }

    /**
     * Return arga of current rest point
     *
     * @return array
     */
    protected function getArgs()
    {
        return array();
    }

    /**
     * Return true if current rest point is enable
     *
     * @return boolean
     */
    public function isEnable()
    {
        return true;
    }

    /**
     * Registers REST API route.
     *
     * @return bool True on success, false on error.
     */
    public function register()
    {
        if (!$this->isEnable()) {
            return true;
        }

        return register_rest_route(self::REST_NAMESPACE, $this->getRoute(), $this->args, $this->override);
    }

    /**
     * REST callback logic
     *
     * @param \WP_REST_Request $request REST request
     *
     * @return \WP_REST_Response
     */
    public function callback(\WP_REST_Request $request)
    {
        $invalidOutput = '';
        $exception = null;
        $responseBase = array(
            'success'     => false,
            'message'     => ''
        );
        ob_start();

        try {
            $result = call_user_func(array($this, 'respond'), $request, $responseBase);
        } catch (\Exception $e) {
            $exception = $e;
        } catch (\Error $e) {
            $exception = $e;
        }

        if (!is_null($exception)) {
            $response['success'] = false;
            $response['message'] = SnapLog::getTextException($exception);
            $result = new \WP_REST_Response($response, 200);
        }

        while (ob_get_level() > 0) {
            $invalidOutput .= ob_get_clean();
        }

        if (strlen($invalidOutput) > 0) {
            DUP_PRO_Log::trace('REST CALL INVALID OUTPUT: ' . $invalidOutput);
        }

        return $result;
    }

    /**
     * REST endpoint logic
     *
     * @param WP_REST_Request $request      REST request
     * @param array           $responseBase response base data
     *
     * @return \WP_REST_Response
     */
    abstract protected function respond(\WP_REST_Request $request, $responseBase);
}
