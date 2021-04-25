<?php


namespace Golly\Simulator\Traits;


use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverKeys;
use Golly\Simulator\Browser;

/**
 * Trait WithMouseAndKeyboard
 * @package Golly\Simulator\Traits
 * @mixin Browser
 */
trait WithMouseAndKeyboard
{
    use WithElements;

    /**
     * Click the element at the given selector.
     *
     * @param string|null $selector
     * @return $this
     */
    public function click($selector = null)
    {
        if (is_null($selector)) {
            (new WebDriverActions($this->driver))->click()->perform();
        } else {
            $this->find($selector)->click();
        }

        return $this;
    }

    /**
     * Right click the element at the given selector.
     *
     * @param string|null $selector
     * @return $this
     */
    public function rightClick($selector = null)
    {
        if (is_null($selector)) {
            (new WebDriverActions($this->driver))->contextClick()->perform();
        } else {
            (new WebDriverActions($this->driver))->contextClick(
                $this->find($selector)
            )->perform();
        }

        return $this;
    }

    /**
     * Perform a double click at the current mouse position.
     *
     * @param string|null $selector
     * @return $this
     */
    public function doubleClick(string $selector = null)
    {
        if (is_null($selector)) {
            (new WebDriverActions($this->driver))->doubleClick()->perform();
        } else {
            (new WebDriverActions($this->driver))->doubleClick(
                $this->find($selector)
            )->perform();
        }

        return $this;
    }

    /**
     * Move the mouse by offset X and Y.
     *
     * @param int $xOffset
     * @param int $yOffset
     * @return $this
     */
    public function moveMouse(int $xOffset, int $yOffset)
    {
        (new WebDriverActions($this->driver))->moveByOffset(
            $xOffset, $yOffset
        )->perform();

        return $this;
    }

    /**
     * @param string $css
     * @return $this
     */
    public function overMouse(string $css)
    {
        $element = $this->find($css);
        if ($element) {
            $this->driver->getMouse()->mouseMove($element->getCoordinates());
        }

        return $this;
    }

    /**
     * press and release key
     *
     * @param string $key
     * @param int $seconds
     * @return $this
     */
    public function pressAndReleaseKey(string $key, int $seconds = 1)
    {
        $keyboard = $this->driver->getKeyboard();
        $keyboard->pressKey($key);
        $this->sleep($seconds);
        $keyboard->releaseKey($key);

        return $this;
    }

    /**
     * @param int $seconds
     * @return WithMouseAndKeyboard
     */
    public function pageDown(int $seconds = 1)
    {
        return $this->pressAndReleaseKey(WebDriverKeys::PAGE_DOWN, $seconds);
    }

    /**
     * @param int $seconds
     * @return WithMouseAndKeyboard
     */
    public function pageUp(int $seconds = 1)
    {
        return $this->pressAndReleaseKey(WebDriverKeys::PAGE_DOWN, $seconds);
    }
}
