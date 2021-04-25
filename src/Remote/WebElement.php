<?php


namespace Golly\Simulator\Remote;


use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Throwable;

/**
 * Class WebElement
 * @package Golly\Simulator\Remote
 */
class WebElement
{
    /**
     * @var RemoteWebElement
     */
    protected $remoteWebElement;

    /**
     * @param RemoteWebElement $remoteWebElement
     * @return WebElement
     */
    public static function init(RemoteWebElement $remoteWebElement)
    {
        return (new static())->setRemoteWebElement($remoteWebElement);
    }

    /**
     * @param string $css
     * @return WebElement
     */
    public function find(string $css)
    {
        $element = $this->remoteWebElement->findElement(WebDriverBy::cssSelector($css));

        return static::init($element);
    }

    /**
     * @param string|null $css
     * @param string $default
     * @return string
     */
    public function text(string $css = null, string $default = '')
    {
        try {
            if (is_null($css)) {
                return $this->remoteWebElement->getText();
            }
            return $this->remoteWebElement->findElement(
                WebDriverBy::cssSelector($css)
            )->getText();
        } catch (Throwable $e) {
            return $default;
        }
    }

    /**
     * @param string|null $css
     * @param string $default
     * @return string|null
     */
    public function html(string $css = null, string $default = '')
    {
        return $this->attribute('innerHTML', $css, $default);
    }

    /**
     * @param string|null $css
     * @param string $default
     * @return string|null
     */
    public function href(string $css = null, string $default = '')
    {
        return $this->attribute('href', $css, $default);
    }

    /**
     * @param string $name
     * @param string|null $css
     * @param string $default
     * @return string|null
     */
    public function attribute(string $name, string $css = null, string $default = '')
    {
        try {
            if (is_null($css)) {
                return $this->remoteWebElement->getAttribute($name);
            }
            return $this->remoteWebElement->findElement(
                WebDriverBy::cssSelector($css)
            )->getAttribute($name);
        } catch (Throwable $e) {

        }

        return $default;
    }

    /**
     * @param RemoteWebElement $remoteWebElement
     * @return $this
     */
    public function setRemoteWebElement(RemoteWebElement $remoteWebElement)
    {
        $this->remoteWebElement = $remoteWebElement;

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->remoteWebElement, $name)) {
            return $this->remoteWebElement->{$name}($arguments);
        }

    }
}
