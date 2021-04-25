<?php


namespace Golly\Simulator;

use Closure;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;
use Throwable;

/**
 * Class Browser
 * @package Golly\Simulator
 */
class Browser
{
    use Traits\WithCookie,
        Traits\WaitElements,
        Traits\WithElements,
        Traits\WithMouseAndKeyboard,
        Traits\WithJavascript,
        Traits\MakeAssertion;

    /**
     * The base URL for all URLs.
     *
     * @var string
     */
    public $baseUrl;

    /**
     * The directory that will contain any screenshots.
     *
     * @var string
     */
    public $screenshotPath = 'storage/simulator/images';

    /**
     * The browsers that support retrieving logs.
     *
     * @var array
     */
    public $supportsRemoteLogs = [
        WebDriverBrowserType::CHROME,
        WebDriverBrowserType::PHANTOMJS,
    ];

    /**
     * The page object currently being viewed.
     *
     * @var mixed
     */
    public $page;

    /**
     * @var bool
     */
    public $fitOnFailure = true;

    /**
     * @var RemoteWebDriver
     */
    public $driver;

    /**
     * Browser constructor.
     * @param RemoteWebDriver|null $driver
     */
    public function __construct(RemoteWebDriver $driver = null)
    {
        if (is_null($driver)) {
            $driver = WebDriver::init();
        }
        $this->driver = $driver;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->quit();
    }

    /**
     * @param string $url
     * @param Closure $callback
     * @return $this
     */
    public function visit(string $url, Closure $callback)
    {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = $this->baseUrl . '/' . ltrim($url, '/');
        }
        $this->driver->navigate()->to($url);
        try {
            $callback($this);
        } catch (Throwable $e) {
            $this->screenshot();
        }

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function get(string $url)
    {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = $this->baseUrl . '/' . ltrim($url, '/');
        }
        $this->driver->get($url);
        $this->switch2LastWindow();

        return $this;
    }

    /**
     * Switch to the last window, because some page are opened by new tabs
     *
     * @return $this
     */
    public function switch2LastWindow()
    {
        $currentWindow = $this->driver->getWindowHandle();
        $this->driver->switchTo()->window($currentWindow);

        return $this;
    }

    /**
     * Browse to the "about:blank" page.
     *
     * @return $this
     */
    public function blank()
    {
        $this->driver->navigate()->to('about:blank');

        return $this;
    }

    /**
     * Refresh the page.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->driver->navigate()->refresh();

        return $this;
    }

    /**
     * Navigate to the previous page.
     *
     * @return $this
     */
    public function back()
    {
        $this->driver->navigate()->back();

        return $this;
    }

    /**
     * Navigate to the next page.
     *
     * @return $this
     */
    public function forward()
    {
        $this->driver->navigate()->forward();

        return $this;
    }

    /**
     * Maximize the browser window.
     *
     * @return $this
     */
    public function maximize()
    {
        $this->driver->manage()->window()->maximize();

        return $this;
    }

    /**
     * Resize the browser window.
     *
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize(int $width, int $height)
    {
        $this->driver->manage()->window()->setSize(
            new WebDriverDimension($width, $height)
        );

        return $this;
    }

    /**
     * Make the browser window as large as the content.
     *
     * @return $this
     */
    public function fitContent()
    {
        $this->driver->switchTo()->defaultContent();

        $html = $this->driver->findElement(WebDriverBy::tagName('html'));

        if (!empty($html) && $html->getSize()->getWidth() >= 0 && $html->getSize()->getHeight() >= 0) {
            $this->resize($html->getSize()->getWidth(), $html->getSize()->getHeight());
        }

        return $this;
    }


    /**
     * Move the browser window.
     *
     * @param int $x
     * @param int $y
     * @return $this
     */
    public function move(int $x, int $y)
    {
        $this->driver->manage()->window()->setPosition(
            new WebDriverPoint($x, $y)
        );

        return $this;
    }


    /**
     * Take a screenshot and store it with the given name.
     *
     * @return $this
     */
    public function screenshot()
    {
        $filePath = sprintf(
            '%s/%s.png',
            rtrim($this->screenshotPath, '/'),
            trim($this->driver->getTitle(), '/')
        );
        $dirPath = dirname($filePath);
        $this->mkdir($dirPath);
        $this->driver->takeScreenshot($filePath);

        return $this;
    }

    /**
     * Switch to a specified frame in the browser and execute the given callback.
     *
     * @param string $selector
     * @param Closure $callback
     * @return $this
     */
    public function withinFrame(string $selector, Closure $callback)
    {
        $this->driver->switchTo()->frame($this->resolver->find($selector));

        $callback($this);

        $this->driver->switchTo()->defaultContent();

        return $this;
    }


    /**
     * sleep for the given amount of seconds.
     *
     * @param int $seconds
     * @return $this
     */
    public function sleep(int $seconds)
    {
        sleep($seconds);

        return $this;
    }

    /**
     * sleep for the given amount of milliseconds.
     *
     * @param int $milliseconds
     * @return $this
     */
    public function usleep(int $milliseconds)
    {
        usleep($milliseconds * 1000);

        return $this;
    }

    /**
     * Sleep and page down
     *
     * @return $this
     */
    public function imitator()
    {
        $seconds = rand(2, 5);
        $this->sleep($seconds);
        $this->pageDown($seconds);

        return $this;
    }

    /**
     * Close the browser.
     *
     * @return void
     */
    public function quit()
    {
        $this->driver && $this->driver->quit();
    }

    /**
     * Tap the browser into a callback.
     *
     * @param Closure $callback
     * @return $this
     */
    public function tap(Closure $callback)
    {
        $callback($this);

        return $this;
    }

    /**
     * Ensure that jQuery is available on the page.
     *
     * @return void
     */
    public function jQueryIsAvailable()
    {
        if ($this->driver->executeScript('return window.jQuery == null')) {
            $this->driver->executeScript(file_get_contents(__DIR__ . '/../bin/jquery.js'));
        }
    }

    /**
     * dd the content from the last response.
     *
     * @return void
     */
    public function dd()
    {
        dd($this->driver->getPageSource());
    }

    /**
     * Dump the content from the last response.
     *
     * @return void
     */
    public function dump()
    {
        dump($this->driver->getPageSource());
    }

    /**
     * @param string $dirPath
     * @return void
     */
    protected function mkdir(string $dirPath)
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
    }

}
