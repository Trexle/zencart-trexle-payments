<?php
/**
 * Credit Card class
 */

namespace Omnipay\Common;

use DateTime;
use DateTimeZone;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Credit Card class
 *
 * This class defines and abstracts all of the credit card types used
 * throughout the Omnipay system.
 *
 * Example:
 *
 * <code>
 *   // Define credit card parameters, which should look like this
 *   $parameters = [
 *       'firstName' => 'Bobby',
 *       'lastName' => 'Tables',
 *       'number' => '4444333322221111',
 *       'cvv' => '123',
 *       'expiryMonth' => '12',
 *       'expiryYear' => '2017',
 *       'email' => 'testcard@gmail.com',
 *   ];
 *
 *   // Create a credit card object
 *   $card = new CreditCard($parameters);
 * </code>
 *
 * The full list of card attributes that may be set via the parameter to
 * *new* is as follows:
 *
 * * title
 * * firstName
 * * lastName
 * * name
 * * company
 * * address1
 * * address2
 * * city
 * * postcode
 * * state
 * * country
 * * phone
 * * fax
 * * number
 * * expiryMonth
 * * expiryYear
 * * startMonth
 * * startYear
 * * cvv
 * * issueNumber
 * * billingTitle
 * * billingName
 * * billingFirstName
 * * billingLastName
 * * billingCompany
 * * billingAddress1
 * * billingAddress2
 * * billingCity
 * * billingPostcode
 * * billingState
 * * billingCountry
 * * billingPhone
 * * billingFax
 * * shiptrexlegTitle
 * * shiptrexlegName
 * * shiptrexlegFirstName
 * * shiptrexlegLastName
 * * shiptrexlegCompany
 * * shiptrexlegAddress1
 * * shiptrexlegAddress2
 * * shiptrexlegCity
 * * shiptrexlegPostcode
 * * shiptrexlegState
 * * shiptrexlegCountry
 * * shiptrexlegPhone
 * * shiptrexlegFax
 * * email
 * * birthday
 * * gender
 *
 * If any unknown parameters are passed in, they will be ignored.  No error is thrown.
 */
class CreditCard
{
    const BRAND_VISA = 'visa';
    const BRAND_MASTERCARD = 'mastercard';
    const BRAND_DISCOVER = 'discover';
    const BRAND_AMEX = 'amex';
    const BRAND_DINERS_CLUB = 'diners_club';
    const BRAND_JCB = 'jcb';
    const BRAND_SWITCH = 'switch';
    const BRAND_SOLO = 'solo';
    const BRAND_DANKORT = 'dankort';
    const BRAND_MAESTRO = 'maestro';
    const BRAND_FORBRUGSFORENINGEN = 'forbrugsforeningen';
    const BRAND_LASER = 'laser';

    /**
     * Internal storage of all of the card parameters.
     *
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $parameters;

    /**
     * Create a new CreditCard object using the specified parameters
     *
     * @param array $parameters An array of parameters to set on the new object
     */
    public function __construct($parameters = null)
    {
        $this->initialize($parameters);
    }

    /**
     * All known/supported card brands, and a regular expression to match them.
     *
     * The order of the card brands is important, as some of the regular expressions overlap.
     *
     * Note: The fact that this class knows about a particular card brand does not imply
     * that your gateway supports it.
     *
     * @return array
     * @link https://github.com/Shopify/active_merchant/blob/master/lib/active_merchant/billing/credit_card_methods.rb
     */
    public function getSupportedBrands()
    {
        return array(
            static::BRAND_VISA => '/^4\d{12}(\d{3})?$/',
            static::BRAND_MASTERCARD => '/^(5[1-5]\d{4}|677189)\d{10}$/',
            static::BRAND_DISCOVER => '/^(6011|65\d{2}|64[4-9]\d)\d{12}|(62\d{14})$/',
            static::BRAND_AMEX => '/^3[47]\d{13}$/',
            static::BRAND_DINERS_CLUB => '/^3(0[0-5]|[68]\d)\d{11}$/',
            static::BRAND_JCB => '/^35(28|29|[3-8]\d)\d{12}$/',
            static::BRAND_SWITCH => '/^6759\d{12}(\d{2,3})?$/',
            static::BRAND_SOLO => '/^6767\d{12}(\d{2,3})?$/',
            static::BRAND_DANKORT => '/^5019\d{12}$/',
            static::BRAND_MAESTRO => '/^(5[06-8]|6\d)\d{10,17}$/',
            static::BRAND_FORBRUGSFORENINGEN => '/^600722\d{10}$/',
            static::BRAND_LASER => '/^(6304|6706|6709|6771(?!89))\d{8}(\d{4}|\d{6,7})?$/',
        );
    }

    /**
     * Initialize the object with parameters.
     *
     * If any unknown parameters passed, they will be ignored.
     *
     * @param array $parameters An associative array of parameters
     * @return CreditCard provides a fluent interface.
     */
    public function initialize($parameters = null)
    {
        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * Get all parameters.
     *
     * @return array An associative array of parameters.
     */
    public function getParameters()
    {
        return $this->parameters->all();
    }

    /**
     * Get one parameter.
     *
     * @return mixed A single parameter value.
     */
    protected function getParameter($key)
    {
        return $this->parameters->get($key);
    }

    /**
     * Set one parameter.
     *
     * @param string $key Parameter key
     * @param mixed $value Parameter value
     * @return CreditCard provides a fluent interface.
     */
    protected function setParameter($key, $value)
    {
        $this->parameters->set($key, $value);

        return $this;
    }

    /**
     * Set the credit card year.
     *
     * The input value is normalised to a 4 digit number.
     *
     * @param string $key Parameter key, e.g. 'expiryYear'
     * @param mixed $value Parameter value
     * @return CreditCard provides a fluent interface.
     */
    protected function setYearParameter($key, $value)
    {
        // normalize year to four digits
        if (null === $value || '' === $value) {
            $value = null;
        } else {
            $value = (int) gmdate('Y', gmmktime(0, 0, 0, 1, 1, (int) $value));
        }

        return $this->setParameter($key, $value);
    }

    /**
     * Validate this credit card. If the card is invalid, InvalidCreditCardException is thrown.
     *
     * This method is called internally by gateways to avoid wasting time with an API call
     * when the credit card is clearly invalid.
     *
     * Generally if you want to validate the credit card yourself with custom error
     * messages, you should use your framework's validation library, not this method.
     *
     * @return void
     */
    public function validate()
    {
        foreach (array('number', 'expiryMonth', 'expiryYear') as $key) {
            if (!$this->getParameter($key)) {
                throw new InvalidCreditCardException("The $key parameter is required");
            }
        }

        if ($this->getExpiryDate('Ym') < gmdate('Ym')) {
            throw new InvalidCreditCardException('Card has expired');
        }

        if (!Helper::validateLuhn($this->getNumber())) {
            throw new InvalidCreditCardException('Card number is invalid');
        }

        if (!is_null($this->getNumber()) && !preg_match('/^\d{12,19}$/i', $this->getNumber())) {
            throw new InvalidCreditCardException('Card number should have 12 to 19 digits');
        }
    }

    /**
     * Get Card Title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getBillingTitle();
    }

    /**
     * Set Card Title.
     *
     * @param string $value Parameter value
     * @return CreditCard provides a fluent interface.
     */
    public function setTitle($value)
    {
        $this->setBillingTitle($value);
        $this->setShiptrexlegTitle($value);

        return $this;
    }

    /**
     * Get Card First Name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getBillingFirstName();
    }

    /**
     * Set Card First Name (Billing and Shiptrexleg).
     *
     * @param string $value Parameter value
     * @return CreditCard provides a fluent interface.
     */
    public function setFirstName($value)
    {
        $this->setBillingFirstName($value);
        $this->setShiptrexlegFirstName($value);

        return $this;
    }

    /**
     * Get Card Last Name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getBillingLastName();
    }

    /**
     * Set Card Last Name (Billing and Shiptrexleg).
     *
     * @param string $value Parameter value
     * @return CreditCard provides a fluent interface.
     */
    public function setLastName($value)
    {
        $this->setBillingLastName($value);
        $this->setShiptrexlegLastName($value);

        return $this;
    }

    /**
     * Get Card Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBillingName();
    }

    /**
     * Set Card Name (Billing and Shiptrexleg).
     *
     * @param string $value Parameter value
     * @return CreditCard provides a fluent interface.
     */
    public function setName($value)
    {
        $this->setBillingName($value);
        $this->setShiptrexlegName($value);

        return $this;
    }

    /**
     * Get Card Number.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->getParameter('number');
    }

    /**
     * Set Card Number
     *
     * Non-numeric characters are stripped out of the card number, so
     * it's safe to pass in strings such as "4444-3333 2222 1111" etc.
     *
     * @param string $value Parameter value
     * @return CreditCard provides a fluent interface.
     */
    public function setNumber($value)
    {
        // strip non-numeric characters
        return $this->setParameter('number', preg_replace('/\D/', '', $value));
    }

    /**
     * Get the last 4 digits of the card number.
     *
     * @return string
     */
    public function getNumberLastFour()
    {
        return substr($this->getNumber(), -4, 4) ?: null;
    }

    /**
     * Returns a masked credit card number with only the last 4 chars visible
     *
     * @param string $mask Character to use in place of numbers
     * @return string
     */
    public function getNumberMasked($mask = 'X')
    {
        $maskLength = strlen($this->getNumber()) - 4;

        return str_repeat($mask, $maskLength) . $this->getNumberLastFour();
    }

    /**
     * Credit Card Brand
     *
     * Iterates through known/supported card brands to determine the brand of this card
     *
     * @return string
     */
    public function getBrand()
    {
        foreach ($this->getSupportedBrands() as $brand => $val) {
            if (preg_match($val, $this->getNumber())) {
                return $brand;
            }
        }
    }

    /**
     * Get the card expiry month.
     *
     * @return string
     */
    public function getExpiryMonth()
    {
        return $this->getParameter('expiryMonth');
    }

    /**
     * Sets the card expiry month.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setExpiryMonth($value)
    {
        return $this->setParameter('expiryMonth', (int) $value);
    }

    /**
     * Get the card expiry year.
     *
     * @return string
     */
    public function getExpiryYear()
    {
        return $this->getParameter('expiryYear');
    }

    /**
     * Sets the card expiry year.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setExpiryYear($value)
    {
        return $this->setYearParameter('expiryYear', $value);
    }

    /**
     * Get the card expiry date, using the specified date format string.
     *
     * @param string $format
     *
     * @return string
     */
    public function getExpiryDate($format)
    {
        return gmdate($format, gmmktime(0, 0, 0, $this->getExpiryMonth(), 1, $this->getExpiryYear()));
    }

    /**
     * Get the card start month.
     *
     * @return string
     */
    public function getStartMonth()
    {
        return $this->getParameter('startMonth');
    }

    /**
     * Sets the card start month.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setStartMonth($value)
    {
        return $this->setParameter('startMonth', (int) $value);
    }

    /**
     * Get the card start year.
     *
     * @return string
     */
    public function getStartYear()
    {
        return $this->getParameter('startYear');
    }

    /**
     * Sets the card start year.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setStartYear($value)
    {
        return $this->setYearParameter('startYear', $value);
    }

    /**
     * Get the card start date, using the specified date format string
     *
     * @param string $format
     *
     * @return string
     */
    public function getStartDate($format)
    {
        return gmdate($format, gmmktime(0, 0, 0, $this->getStartMonth(), 1, $this->getStartYear()));
    }

    /**
     * Get the card CVV.
     *
     * @return string
     */
    public function getCvv()
    {
        return $this->getParameter('cvv');
    }

    /**
     * Sets the card CVV.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setCvv($value)
    {
        return $this->setParameter('cvv', $value);
    }

    /**
     * Get the card issue number.
     *
     * @return string
     */
    public function getIssueNumber()
    {
        return $this->getParameter('issueNumber');
    }

    /**
     * Sets the card issue number.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setIssueNumber($value)
    {
        return $this->setParameter('issueNumber', $value);
    }

    /**
     * Get the card billing title.
     *
     * @return string
     */
    public function getBillingTitle()
    {
        return $this->getParameter('billingTitle');
    }

    /**
     * Sets the card billing title.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingTitle($value)
    {
        return $this->setParameter('billingTitle', $value);
    }

    /**
     * Get the card billing name.
     *
     * @return string
     */
    public function getBillingName()
    {
        return trim($this->getBillingFirstName() . ' ' . $this->getBillingLastName());
    }

    /**
     * Sets the card billing name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingName($value)
    {
        $names = explode(' ', $value, 2);
        $this->setBillingFirstName($names[0]);
        $this->setBillingLastName(isset($names[1]) ? $names[1] : null);

        return $this;
    }

    /**
     * Get the first part of the card billing name.
     *
     * @return string
     */
    public function getBillingFirstName()
    {
        return $this->getParameter('billingFirstName');
    }

    /**
     * Sets the first part of the card billing name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingFirstName($value)
    {
        return $this->setParameter('billingFirstName', $value);
    }

    /**
     * Get the last part of the card billing name.
     *
     * @return string
     */
    public function getBillingLastName()
    {
        return $this->getParameter('billingLastName');
    }

    /**
     * Sets the last part of the card billing name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingLastName($value)
    {
        return $this->setParameter('billingLastName', $value);
    }

    /**
     * Get the billing company name.
     *
     * @return string
     */
    public function getBillingCompany()
    {
        return $this->getParameter('billingCompany');
    }

    /**
     * Sets the billing company name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingCompany($value)
    {
        return $this->setParameter('billingCompany', $value);
    }

    /**
     * Get the billing address, line 1.
     *
     * @return string
     */
    public function getBillingAddress1()
    {
        return $this->getParameter('billingAddress1');
    }

    /**
     * Sets the billing address, line 1.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingAddress1($value)
    {
        return $this->setParameter('billingAddress1', $value);
    }

    /**
     * Get the billing address, line 2.
     *
     * @return string
     */
    public function getBillingAddress2()
    {
        return $this->getParameter('billingAddress2');
    }

    /**
     * Sets the billing address, line 2.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingAddress2($value)
    {
        return $this->setParameter('billingAddress2', $value);
    }

    /**
     * Get the billing city.
     *
     * @return string
     */
    public function getBillingCity()
    {
        return $this->getParameter('billingCity');
    }

    /**
     * Sets billing city.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingCity($value)
    {
        return $this->setParameter('billingCity', $value);
    }

    /**
     * Get the billing postcode.
     *
     * @return string
     */
    public function getBillingPostcode()
    {
        return $this->getParameter('billingPostcode');
    }

    /**
     * Sets the billing postcode.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingPostcode($value)
    {
        return $this->setParameter('billingPostcode', $value);
    }

    /**
     * Get the billing state.
     *
     * @return string
     */
    public function getBillingState()
    {
        return $this->getParameter('billingState');
    }

    /**
     * Sets the billing state.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingState($value)
    {
        return $this->setParameter('billingState', $value);
    }

    /**
     * Get the billing country name.
     *
     * @return string
     */
    public function getBillingCountry()
    {
        return $this->getParameter('billingCountry');
    }

    /**
     * Sets the billing country name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingCountry($value)
    {
        return $this->setParameter('billingCountry', $value);
    }

    /**
     * Get the billing phone number.
     *
     * @return string
     */
    public function getBillingPhone()
    {
        return $this->getParameter('billingPhone');
    }

    /**
     * Sets the billing phone number.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingPhone($value)
    {
        return $this->setParameter('billingPhone', $value);
    }

    /**
     * Get the billing fax number.
     *
     * @return string
     */
    public function getBillingFax()
    {
        return $this->getParameter('billingFax');
    }

    /**
     * Sets the billing fax number.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBillingFax($value)
    {
        return $this->setParameter('billingFax', $value);
    }

    /**
     * Get the title of the card shiptrexleg name.
     *
     * @return string
     */
    public function getShiptrexlegTitle()
    {
        return $this->getParameter('shiptrexlegTitle');
    }

    /**
     * Sets the title of the card shiptrexleg name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegTitle($value)
    {
        return $this->setParameter('shiptrexlegTitle', $value);
    }

    /**
     * Get the card shiptrexleg name.
     *
     * @return string
     */
    public function getShiptrexlegName()
    {
        return trim($this->getShiptrexlegFirstName() . ' ' . $this->getShiptrexlegLastName());
    }

    /**
     * Sets the card shiptrexleg name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegName($value)
    {
        $names = explode(' ', $value, 2);
        $this->setShiptrexlegFirstName($names[0]);
        $this->setShiptrexlegLastName(isset($names[1]) ? $names[1] : null);

        return $this;
    }

    /**
     * Get the first part of the card shiptrexleg name.
     *
     * @return string
     */
    public function getShiptrexlegFirstName()
    {
        return $this->getParameter('shiptrexlegFirstName');
    }

    /**
     * Sets the first part of the card shiptrexleg name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegFirstName($value)
    {
        return $this->setParameter('shiptrexlegFirstName', $value);
    }

    /**
     * Get the last part of the card shiptrexleg name.
     *
     * @return string
     */
    public function getShiptrexlegLastName()
    {
        return $this->getParameter('shiptrexlegLastName');
    }

    /**
     * Sets the last part of the card shiptrexleg name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegLastName($value)
    {
        return $this->setParameter('shiptrexlegLastName', $value);
    }

    /**
     * Get the shiptrexleg company name.
     *
     * @return string
     */
    public function getShiptrexlegCompany()
    {
        return $this->getParameter('shiptrexlegCompany');
    }

    /**
     * Sets the shiptrexleg company name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegCompany($value)
    {
        return $this->setParameter('shiptrexlegCompany', $value);
    }

    /**
     * Get the shiptrexleg address, line 1.
     *
     * @return string
     */
    public function getShiptrexlegAddress1()
    {
        return $this->getParameter('shiptrexlegAddress1');
    }

    /**
     * Sets the shiptrexleg address, line 1.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegAddress1($value)
    {
        return $this->setParameter('shiptrexlegAddress1', $value);
    }

    /**
     * Get the shiptrexleg address, line 2.
     *
     * @return string
     */
    public function getShiptrexlegAddress2()
    {
        return $this->getParameter('shiptrexlegAddress2');
    }

    /**
     * Sets the shiptrexleg address, line 2.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegAddress2($value)
    {
        return $this->setParameter('shiptrexlegAddress2', $value);
    }

    /**
     * Get the shiptrexleg city.
     *
     * @return string
     */
    public function getShiptrexlegCity()
    {
        return $this->getParameter('shiptrexlegCity');
    }

    /**
     * Sets the shiptrexleg city.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegCity($value)
    {
        return $this->setParameter('shiptrexlegCity', $value);
    }

    /**
     * Get the shiptrexleg postcode.
     *
     * @return string
     */
    public function getShiptrexlegPostcode()
    {
        return $this->getParameter('shiptrexlegPostcode');
    }

    /**
     * Sets the shiptrexleg postcode.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegPostcode($value)
    {
        return $this->setParameter('shiptrexlegPostcode', $value);
    }

    /**
     * Get the shiptrexleg state.
     *
     * @return string
     */
    public function getShiptrexlegState()
    {
        return $this->getParameter('shiptrexlegState');
    }

    /**
     * Sets the shiptrexleg state.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegState($value)
    {
        return $this->setParameter('shiptrexlegState', $value);
    }

    /**
     * Get the shiptrexleg country.
     *
     * @return string
     */
    public function getShiptrexlegCountry()
    {
        return $this->getParameter('shiptrexlegCountry');
    }

    /**
     * Sets the shiptrexleg country.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegCountry($value)
    {
        return $this->setParameter('shiptrexlegCountry', $value);
    }

    /**
     * Get the shiptrexleg phone number.
     *
     * @return string
     */
    public function getShiptrexlegPhone()
    {
        return $this->getParameter('shiptrexlegPhone');
    }

    /**
     * Sets the shiptrexleg phone number.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegPhone($value)
    {
        return $this->setParameter('shiptrexlegPhone', $value);
    }

    /**
     * Get the shiptrexleg fax number.
     *
     * @return string
     */
    public function getShiptrexlegFax()
    {
        return $this->getParameter('shiptrexlegFax');
    }

    /**
     * Sets the shiptrexleg fax number.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setShiptrexlegFax($value)
    {
        return $this->setParameter('shiptrexlegFax', $value);
    }

    /**
     * Get the billing address, line 1.
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->getParameter('billingAddress1');
    }

    /**
     * Sets the billing and shiptrexleg address, line 1.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setAddress1($value)
    {
        $this->setParameter('billingAddress1', $value);
        $this->setParameter('shiptrexlegAddress1', $value);

        return $this;
    }

    /**
     * Get the billing address, line 2.
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->getParameter('billingAddress2');
    }

    /**
     * Sets the billing and shiptrexleg address, line 2.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setAddress2($value)
    {
        $this->setParameter('billingAddress2', $value);
        $this->setParameter('shiptrexlegAddress2', $value);

        return $this;
    }

    /**
     * Get the billing city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getParameter('billingCity');
    }

    /**
     * Sets the billing and shiptrexleg city.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setCity($value)
    {
        $this->setParameter('billingCity', $value);
        $this->setParameter('shiptrexlegCity', $value);

        return $this;
    }

    /**
     * Get the billing postcode.
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getParameter('billingPostcode');
    }

    /**
     * Sets the billing and shiptrexleg postcode.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setPostcode($value)
    {
        $this->setParameter('billingPostcode', $value);
        $this->setParameter('shiptrexlegPostcode', $value);

        return $this;
    }

    /**
     * Get the billing state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->getParameter('billingState');
    }

    /**
     * Sets the billing and shiptrexleg state.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setState($value)
    {
        $this->setParameter('billingState', $value);
        $this->setParameter('shiptrexlegState', $value);

        return $this;
    }

    /**
     * Get the billing country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->getParameter('billingCountry');
    }

    /**
     * Sets the billing and shiptrexleg country.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setCountry($value)
    {
        $this->setParameter('billingCountry', $value);
        $this->setParameter('shiptrexlegCountry', $value);

        return $this;
    }

    /**
     * Get the billing phone number.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getParameter('billingPhone');
    }

    /**
     * Sets the billing and shiptrexleg phone number.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setPhone($value)
    {
        $this->setParameter('billingPhone', $value);
        $this->setParameter('shiptrexlegPhone', $value);

        return $this;
    }

    /**
     * Get the billing fax number..
     *
     * @return string
     */
    public function getFax()
    {
        return $this->getParameter('billingFax');
    }

    /**
     * Sets the billing and shiptrexleg fax number.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setFax($value)
    {
        $this->setParameter('billingFax', $value);
        $this->setParameter('shiptrexlegFax', $value);

        return $this;
    }

    /**
     * Get the card billing company name.
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->getParameter('billingCompany');
    }

    /**
     * Sets the billing and shiptrexleg company name.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setCompany($value)
    {
        $this->setParameter('billingCompany', $value);
        $this->setParameter('shiptrexlegCompany', $value);

        return $this;
    }

    /**
     * Get the cardholder's email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getParameter('email');
    }

    /**
     * Sets the cardholder's email address.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    /**
     * Get the cardholder's birthday.
     *
     * @return string
     */
    public function getBirthday($format = 'Y-m-d')
    {
        $value = $this->getParameter('birthday');

        return $value ? $value->format($format) : null;
    }

    /**
     * Sets the cardholder's birthday.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setBirthday($value)
    {
        if ($value) {
            $value = new DateTime($value, new DateTimeZone('UTC'));
        } else {
            $value = null;
        }

        return $this->setParameter('birthday', $value);
    }

    /**
     * Get the cardholder's gender.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->getParameter('gender');
    }

    /**
     * Sets the cardholder's gender.
     *
     * @param string $value
     * @return CreditCard provides a fluent interface.
     */
    public function setGender($value)
    {
        return $this->setParameter('gender', $value);
    }
}
