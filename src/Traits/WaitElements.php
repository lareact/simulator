<?php


namespace Golly\Simulator\Traits;


use Closure;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Golly\Simulator\Browser;
use Illuminate\Support\Str;

/**
 * Class WaitElements
 * @package Golly\Simulator\Traits
 * @mixin Browser
 */
trait WaitElements
{

    /**
     * Global implicitly wait
     *
     * @param int $seconds
     * @return $this
     */
    public function implicitlyWait(int $seconds)
    {
        $this->driver->manage()
            ->timeouts()
            ->implicitlyWait($seconds);

        return $this;
    }

    /**
     * Execute the given callback in a scoped browser once the selector is available.
     *
     * @param string $css
     * @param Closure $callback
     * @param int $seconds
     * @return WaitElements
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function whenAvailable(string $css, Closure $callback, int $seconds = 30)
    {
        $callback($this->waitUntil($css, $seconds));

        return $this;
    }

    /**
     * Wait until the given script returns true.
     *
     * @param string $css
     * @param int $seconds
     * @param string|null $message
     * @return $this
     * @throws TimeoutException
     * @throws NoSuchElementException
     */
    public function waitUntil(string $css, int $seconds = 30, string $message = null)
    {
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated(
            WebDriverBy::cssSelector($css)
        );
        $this->driver->wait($seconds)->until($condition, $message);

        return $this;
    }

    /**
     * Wait for the given selector to be removed.
     *
     * @param string $css
     * @param int $seconds
     * @return $this
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitUntilMissing(string $css, int $seconds = 30)
    {
        $message = $this->formatTimeOutMessage('Waited %s seconds for removal of selector', $css);

        $condition = WebDriverExpectedCondition::invisibilityOfElementLocated(
            WebDriverBy::cssSelector($css)
        );
        $this->driver->wait($seconds)->until($condition, $message);

        return $this;
    }

    /**
     * Wait for the given link to be visible.
     *
     * @param string $link
     * @param int $seconds
     * @return WaitElements
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForLink(string $link, int $seconds = 30)
    {
        $message = $this->formatTimeOutMessage('Waited %s seconds for link', $link);

        return $this->wait(function () use ($link) {
            return $this->seeLink($link);
        }, $seconds, 100, $message);
    }

    /**
     * Wait for the current page to reload.
     *
     * @param Closure|null $callback
     * @param int $seconds
     * @return WaitElements
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForReload(Closure $callback = null, $seconds = 30)
    {
        $token = Str::random();

        $this->driver->executeScript("window['{$token}'] = {};");

        if ($callback) {
            $callback($this);
        }

        return $this->wait(function () use ($token) {
            return $this->driver->executeScript("return typeof window['{$token}'] === 'undefined';");
        }, $seconds, 100, 'Waited %s seconds for page reload.');
    }


    /**
     * Wait for the given callback to be true.
     *
     * @param Closure $callback
     * @param int $seconds
     * @param int $interval
     * @param string|null $message
     * @return $this
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function wait(Closure $callback, int $seconds = 30, int $interval = 100, string $message = null)
    {
        $end = microtime(true) + $seconds;
        $exception = null;
        while ($end > microtime(true)) {
            try {
                $bool = call_user_func($callback, $this->driver);
                if ($bool) {
                    return $this;
                }
            } catch (NoSuchElementException $e) {
                $exception = $e;
            }
            usleep($interval * 1000);
        }

        if ($exception) {
            throw $exception;
        }

        throw new TimeoutException($message);
    }

    /**
     * Prepare custom TimeOutException message for sprintf().
     *
     * @param string $message
     * @param string $expected
     * @return string
     */
    protected function formatTimeOutMessage(string $message, string $expected)
    {
        return $message . ' [' . str_replace('%', '%%', $expected) . '].';
    }
}
