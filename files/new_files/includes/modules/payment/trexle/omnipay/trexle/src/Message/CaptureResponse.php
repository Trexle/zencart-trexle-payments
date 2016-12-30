<?php
/**
 * Trexle Capture Response
 */

namespace Omnipay\Trexle\Message;

/**
 * Trexle Capture Response
 *
 * This is the response class for Trexle Capture REST requests.
 *
 * @see \Omnipay\Trexle\Gateway
 */
class CaptureResponse extends Response
{
    /**
     * Get Captured value
     *
     * This is used after an attempt to capture the charge is made.
     * If the capture was successful then it will return true.
     *
     * @return string
     */
    public function getCaptured()
    {
        if (isset($this->data['response']['captured'])) {
            return $this->data['response']['captured'];
        }
    }
}
