<?php
/**
 * Trexle Gateway
 */

namespace Omnipay\Trexle;

use Omnipay\Common\AbstractGateway;

/**
 * Trexle Gateway
 *
 * Trexle Payments is an Australian all-in-one payment system, allowing you
 * to accept multi-currency credit card payments without a security
 * deposit or a merchant account.
 *
 * ### Example
 *
 * <code>
 * // Create a gateway for the Trexle REST Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('pingateway');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'secretKey' => 'TEST',
 *     'testMode'  => true, // Or false when you are ready for live transactions
 * ));
 *
 * // Create a credit card object
 * // This card can be used for testing.
 * // See https://docs.trexle.com/test-cards for a list of card
 * // numbers that can be used for testing.
 * $card = new d(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4200000000000000',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 *             'email'        => 'customer@example.com',
 *             'billingAddress1'       => '1 Scrubby Creek Road',
 *             'billingCountry'        => 'AU',
 *             'billingCity'           => 'Scrubby Creek',
 *             'billingPostcode'       => '4999',
 *             'billingState'          => 'QLD',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'description'              => 'Your order for widgets',
 *     'amount'                   => '10.00',
 *     'currency'                 => 'AUD',
 *     'clientIp'                 => $_SERVER['REMOTE_ADDR'],
 *     'card'                     => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * ### Test modes
 *
 * To enable test/sandbox/development mode you need to use the Trexle Sandbox Gateway in your dashboard at trexle.com
 * against the same API end point https://core.trexle.com/api/v1
 *
 * ### Authentication
 *
 * Calls to the Trexle Payments API must be authenticated using HTTP
 * basic authentication, with your API key as the username, and
 * a blank string as the password.
 *
 * #### Keys
 *
 * Your account has two types of keys:
 *
 * * public
 * * secret
 *
 * You can find your keys on the account settings page of the dashboard
 * after you have created an account at trexle.com and logged in.
 *
 * Your secret key can be used with all of the API, and must be kept
 * secure and secret at all times. You use your secret key from your
 * server to create charges and refunds.
 *
 * Your publishable key can be used from insecure locations (such as
 * browsers or mobile apps) to create cards with the cards API. This
 * is the key you use with Trexle.js to create secure payment forms in
 * the browser.
 *
 * @see \Omnipay\Common\AbstractGateway
 * @link https://docs.trerxle.com/
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Trexle';
    }

    public function getDefaultParameters()
    {
        return array(
            'secretKey' => '',
            'testMode' => false,
        );
    }

    /**
     * Get secret key
     *
     * Calls to the Trexle Payments API must be authenticated using HTTP
     * basic authentication, with your API key as the username, and
     * a blank string as the password.
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    /**
     * Set secret key
     *
     * Calls to the Trexle Payments API must be authenticated using HTTP
     * basic authentication, with your API key as the username, and
     * a blank string as the password.
     *
     * @param string $value
     * @return Gateway implements a fluent interface
     */
    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * Create a purchase request
     *
     * @param array $parameters
     * @return \Omnipay\Trexle\Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Trexle\Message\PurchaseRequest', $parameters);
    }

    /**
     * Create an authorize request
     *
     * @param array $parameters
     * @return \Omnipay\Trexle\Message\AuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Trexle\Message\AuthorizeRequest', $parameters);
    }

    /**
     * Create a capture request
     *
     * @param array $parameters
     * @return \Omnipay\Trexle\Message\CaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Trexle\Message\CaptureRequest', $parameters);
    }

    /**
     * Create a refund request
     *
     * @param array $parameters
     * @return \Omnipay\Trexle\Message\RefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Trexle\Message\RefundRequest', $parameters);
    }

    /**
     * Create a createCustomer request
     *
     * @param array $parameters
     * @return \Omnipay\Trexle\Message\CreateCustomerRequest
     */
    public function createCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Trexle\Message\CreateCustomerRequest', $parameters);
    }

    /**
     * Create a createCard request
     *
     * @param array $parameters
     * @return \Omnipay\Trexle\Message\CreateCardRequest
     */
    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Trexle\Message\CreateCardRequest', $parameters);
    }
}
