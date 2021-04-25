<?php


namespace Golly\Simulator\Traits;

use Golly\Simulator\Browser;
use Golly\Simulator\Exceptions\JavascriptException;
use Throwable;

/**
 * Trait WithJavascript
 * @package Golly\Simulator\Traits
 * @mixin Browser
 */
trait WithJavascript
{
    /**
     * Execute JavaScript within the browser.
     *
     * @param array $scripts
     * @param false $jquery
     * @param false $throw
     * @return array
     * @throws JavascriptException
     */
    public function script(array $scripts, $jquery = false, $throw = false)
    {
        if ($jquery) {
            $this->jQueryIsAvailable();
        }
        $result = [];
        foreach ($scripts as $script) {
            try {
                $result[] = $this->driver->executeScript($script);
            } catch (Throwable $e) {
                if ($throw) {
                    throw new JavascriptException($e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Click the link with the given text.
     *
     * @param string $link
     * @param string $element
     * @return $this
     * @throws JavascriptException
     */
    public function clickLink(string $link, $element = 'a')
    {
        $this->jQueryIsAvailable();

        $selector = addslashes(trim($this->resolver->format("{$element}:contains({$link}):visible")));
        try {
            $this->driver->executeScript("jQuery.find(\"{$selector}\")[0].click();");
        } catch (Throwable $e) {
            throw new JavascriptException($e->getMessage());
        }


        return $this;
    }

    /**
     * Scroll element into view at the given selector.
     *
     * @param string $selector
     * @return $this
     * @throws JavascriptException
     */
    public function scrollIntoView(string $selector)
    {
        $selector = addslashes($this->resolver->format($selector));
        try {
            $this->driver->executeScript("document.querySelector(\"$selector\").scrollIntoView();");
        } catch (Throwable $e) {
            throw new JavascriptException($e->getMessage());
        }


        return $this;
    }

    /**
     * Scroll screen to element at the given selector.
     *
     * @param string $selector
     * @return $this
     * @throws JavascriptException
     */
    public function scrollTo(string $selector)
    {
        $this->jQueryIsAvailable();

        $selector = addslashes($this->resolver->format($selector));
        try {
            $this->driver->executeScript("jQuery(\"html, body\").animate({scrollTop: jQuery(\"$selector\").offset().top}, 0);");
        } catch (Throwable $e) {
            throw new JavascriptException($e->getMessage());
        }


        return $this;
    }
}
