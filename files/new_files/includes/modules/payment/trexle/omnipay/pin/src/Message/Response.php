<?php
/**
 * Trexle Response
 */

namespace Omnipay\Trexle\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Trexle Response
 *
 * This is the response class for all Trexle REST requests.
 *
 * @see \Omnipay\Trexle\Gateway
 */
class Response extends AbstractResponse
{
    public function isSuccessful()
    {
        return !isset($this->data['error']);
    }

    public function getTransactionReference()
    {
        if (isset($this->data['response']['token'])) {
            return $this->data['response']['token'];
        }
    }

    /**
     * Get Card Reference
     *
     * This is used after createCard to get the credit card token to be
     * used in future transactions.
     *
     * @return string
     */
    public function getCardReference()
    {
        if (isset($this->data['response']['token'])) {
            return $this->data['response']['token'];
        }
    }

    /**
     * @deprecated
     */
    public function getCardToken()
    {
        return $this->getCardReference();
    }

    /**
     * Get Customer Reference
     *
     * This is used after createCustomer to get the customer token to be
     * used in future transactions.
     *
     * @return string
     */
    public function getCustomerReference()
    {
        if (isset($this->data['response']['token'])) {
            return $this->data['response']['token'];
        }
    }

    /**
     * @deprecated
     */
    public function getCustomerToken()
    {
        return $this->getCustomerReference();
    }

    public function getMessage()
    {
        if ($this->isSuccessful()) {
            if (isset($this->data['response']['status_message'])) {
                return $this->data['response']['status_message'];
            } else {
                return true;
            }
        } else {
            if (is_array($this->data['messages']) && count($this->data['messages']) > 0)
            {
                return $this->data['messages'][0]['message'];
            }
            return $this->data['error_description'];
        }
    }

    public function getCode()
    {
        if (isset($this->data['error'])) {
            return $this->data['error'];
        }
    }
    // added by vinawebdev@gmail.com
    public function getScheme()
    {
        if (isset($this->data['response']['card']['scheme'])) {
            return $this->data['response']['card']['scheme'];
        }
        