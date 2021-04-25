<?php


namespace Golly\Simulator;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * Trait WebDriver
 * @package Golly\Simulator
 */
class WebDriver
{

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * https://chromedriver.storage.googleapis.com/index.html
     * http://localhost:9515
     *
     * http://selenium:4444/wd/hub
     *
     * @var string
     */
    protected $seleniumServerUrl = 'http://localhost:4443';

    /**
     * proxy setting
     *
     * @var string
     */
    protected $proxy = '';

    /**
     * ignore the image request
     *
     * @var bool
     */
    protected $ignoreImages = true;

    /**
     * Request timeout, default 5 minutes
     *
     * @var int
     */
    protected $timeout = 300000;

    /**
     * @return RemoteWebDriver
     */
    public static function init()
    {
        return (new static())->createWebDriver();
    }

    /**
     * Create the remote web driver instance.
     *
     * @return RemoteWebDriver
     */
    public function createWebDriver()
    {
        return RemoteWebDriver::create(
            $this->seleniumServerUrl,
            $this->initCapabilities(),
            $this->timeout,
            $this->timeout
        );
    }


    /**
     * init capabilities
     *
     * @return DesiredCapabilities
     */
    protected function initCapabilities()
    {
        $caps = DesiredCapabilities::chrome();
        $options = new ChromeOptions();
        if ($this->proxy) {
            $options->addArguments(['--proxy-server=' . $this->proxy]);
        }
        if ($this->ignoreImages) {
            $value = ['profile.managed_default_content_settings.images' => 2];
            $options->setExperimentalOption('prefs', $value);
        }
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        return $caps;
    }
}
