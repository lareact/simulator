<?php


namespace Golly\Simulator\Traits;


use DateTimeInterface;
use Facebook\WebDriver\Exception\NoSuchCookieException;
use Golly\Simulator\Browser;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Support\Facades\Crypt;

/**
 * Trait WithCookie
 * @package Golly\Simulator\Traits
 * @mixin Browser
 */
trait WithCookie
{

    /**
     * @param string $name
     * @return mixed|string|null
     */
    public function getCookie(string $name)
    {
        try {
            $cookie = $this->driver->manage()->getCookieNamed($name);
        } catch (NoSuchCookieException $e) {
            $cookie = null;
        }

        if ($cookie) {
            $decryptedValue = decrypt(rawurldecode($cookie['value']), false);

            $hasValuePrefix = strpos($decryptedValue, CookieValuePrefix::create($name, Crypt::getKey())) === 0;

            return $hasValuePrefix ? CookieValuePrefix::remove($decryptedValue) : $decryptedValue;
        }

        return null;
    }

    /**
     * @param string $name
     * @param string $value
     * @param null $expiry
     * @param array $options
     * @param bool $encrypt
     * @return $this
     */
    public function addCookie(string $name, string $value, $expiry = null, array $options = [], $encrypt = true)
    {
        if ($encrypt) {
            $value = encrypt($value, false);
        }

        if ($expiry instanceof DateTimeInterface) {
            $expiry = $expiry->getTimestamp();
        }

        $this->driver->manage()->addCookie(
            array_merge($options, compact('expiry', 'name', 'value'))
        );

        return $this;
    }

    public function getPlainCookie()
    {

    }

    public function plainCookie()
    {

    }

    /**
     * @param string $name
     * @return $this
     */
    public function deleteCookie(string $name)
    {
        $this->driver->manage()->deleteCookieNamed($name);

        return $this;
    }
}
