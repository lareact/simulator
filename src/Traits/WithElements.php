<?php


namespace Golly\Simulator\Traits;


use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Golly\Simulator\Browser;
use Throwable;

/**
 * Trait WithElements
 * @package Golly\Simulator\Traits
 * @mixin Browser
 */
trait WithElements
{

    /**
     * Attempt to find the selector by ID.
     *
     * @param string $selector
     * @return RemoteWebElement|null
     */
    public function findById(string $selector)
    {
        if (preg_match('/^#[\w\-:]+$/', $selector)) {
            return $this->driver->findElement(WebDriverBy::id(substr($selector, 1)));
        }

        return null;
    }


    /**
     * Find an element by the given selector or throw an exception.
     *
     * @param string $css
     * @return RemoteWebElement|null
     */
    public function find(string $css)
    {
        try {
            return $this->driver->findElement(WebDriverBy::cssSelector($css));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Find the elements by the given selector or return an empty array.
     *
     * @param string $css
     * @return RemoteWebElement[]
     */
    public function findAll(string $css)
    {
        try {
            return $this->driver->findElements(WebDriverBy::cssSelector($css));
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @param string $name
     * @return RemoteWebElement|null
     */
    public function findByName(string $name)
    {
        try {
            return $this->driver->findElement(WebDriverBy::name($name));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $name
     * @return RemoteWebElement[]
     */
    public function findAllByName(string $name)
    {
        try {
            return $this->driver->findElements(WebDriverBy::name($name));
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @param string $link
     * @return RemoteWebElement|null
     */
    public function findByLinkText(string $link)
    {
        try {
            return $this->driver->findElement(WebDriverBy::linkText($link));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $link
     * @return RemoteWebElement[]
     */
    public function findAllByLinkText(string $link)
    {
        try {
            return $this->driver->findElements(WebDriverBy::linkText($link));
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @param string $xpath
     * @return RemoteWebElement
     */
    public function findByXPath(string $xpath)
    {
        try {
            return $this->driver->findElement(WebDriverBy::xpath($xpath));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $xpath
     * @return RemoteWebElement[]
     */
    public function findAllByXPath(string $xpath)
    {
        try {
            return $this->driver->findElements(WebDriverBy::xpath($xpath));
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @param array $selectors
     * @return RemoteWebElement|null
     */
    public function search(array $selectors)
    {
        foreach ($selectors as $selector) {
            try {
                return $this->find($selector);
            } catch (Throwable $e) {
                //
            }
        }

        return null;
    }

    /**
     * @param string $field
     * @return RemoteWebElement|null
     */
    public function findInput(string $field)
    {
        if (!is_null($element = $this->findById($field))) {
            return $element;
        }

        return $this->search([
            "input[name='{$field}']",
            "textarea[name='{$field}']",
            $field,
        ]);
    }


    /**
     * @param string $button
     * @return RemoteWebElement|null
     */
    public function findButton(string $button)
    {
        return $this->find($button);
    }


    /**
     * @param string $field
     * @return RemoteWebElement|null
     */
    public function findSelect(string $field)
    {
        if (!is_null($element = $this->findById($field))) {
            return $element;
        }

        return $this->find("select[name='{$field}']");
    }

    /**
     * @param string $field
     * @param array $values
     * @return RemoteWebElement[]
     */
    public function findSelectOptions(string $field, array $values)
    {
        try {
            $options = $this->findSelect($field)->findElements(
                WebDriverBy::tagName('option')
            );
            if (empty($options)) {
                return [];
            }

            return array_filter($options, function ($option) use ($values) {
                return in_array($option->getAttribute('value'), $values);
            });
        } catch (Throwable $e) {
            return [];
        }
    }


    /**
     * @param string $field
     * @param string|null $value
     * @return RemoteWebElement|null
     */
    public function findRadio(string $field, string $value = null)
    {
        if (!is_null($element = $this->findById($field))) {
            return $element;
        }

        return $this->search(["input[type=radio][name='{$field}'][value='{$value}']", $field]);
    }

    /**
     * @param string $field
     * @param null $value
     * @return RemoteWebElement|null
     */
    public function findCheckbox(string $field, $value = null)
    {
        if (!is_null($element = $this->findById($field))) {
            return $element;
        }
        $selectors[] = 'input[type=checkbox]';

        if (!is_null($field)) {
            $selectors[] = "[name='{$field}']";
        }

        if (!is_null($value)) {
            $selectors[] = "[value='{$value}']";
        }
        $selector = implode('', $selectors);

        return $this->search([$selector, $field]);
    }

    /**
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function fillInput(string $field, string $value)
    {
        $this->findInput($field)->sendKeys($value);

        return $this;
    }

    /**
     * Type the given value in the given field slowly without clearing it.
     *
     * @param string $field
     * @param string $value
     * @param int $pause
     * @return $this
     */
    public function fillInputSlowly(string $field, string $value, int $pause = 100)
    {
        foreach (str_split($value) as $char) {
            $this->fillInput($field, $char)->usleep($pause);
        }

        return $this;
    }

    /**
     * Clear the given field.
     *
     * @param string $field
     * @return $this
     */
    public function clearInput(string $field)
    {
        $this->findInput($field)->clear();

        return $this;
    }

    /**
     * Send the given keys to the element matching the given selector.
     *
     * @param string $css
     * @param mixed $keys
     * @return $this
     */
    public function sendKeys(string $css, ...$keys)
    {
        $this->find($css)->sendKeys($keys);

        return $this;
    }

    /**
     * Directly get or set the value attribute of an input field.
     *
     * @param string $css
     * @param null $value
     * @return string|$this
     */
    public function value(string $css, $value = null)
    {
        if (is_null($value)) {
            return $this->find($css)->getAttribute('value');
        }

        $this->driver->executeScript(
            'document.querySelector(' . json_encode($css) . ').value = ' . json_encode($value) . ';'
        );

        return $this;
    }

    /**
     * Get the text of the element matching the given selector.
     *
     * @param string $css
     * @param string $default
     * @return string
     */
    public function text(string $css, string $default = '')
    {
        try {
            return $this->find($css)->getText();
        } catch (Throwable $e) {

        }

        return $default;
    }

    /**
     * Get the html content of the element matching the given selector.
     *
     * @param string $css
     * @param string $default
     * @return string
     */
    public function html(string $css, string $default = '')
    {
        try {
            return $this->attribute($css, 'innerHTML');
        } catch (Throwable $e) {

        }

        return $default;
    }

    /**
     * Get the given attribute from the element matching the given selector.
     *
     * @param string $css
     * @param string $attribute
     * @return string
     */
    public function attribute(string $css, string $attribute)
    {
        return $this->find($css)->getAttribute($attribute);
    }

}
