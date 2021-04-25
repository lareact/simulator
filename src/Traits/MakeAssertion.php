<?php


namespace Golly\Simulator\Traits;

use Golly\Simulator\Browser;

/**
 * Trait MakeAssertion
 * @package Golly\Simulator\Traits
 * @mixin Browser
 */
trait MakeAssertion
{

    /**
     * Determine if the given link is visible.
     *
     * @param string $link
     * @return bool
     */
    public function seeLink(string $link)
    {
        $this->jQueryIsAvailable();

        $selector = addslashes(trim($this->resolver->format("a:contains('{$link}')")));

        $script = <<<JS
            var link = jQuery.find("{$selector}");
            return link.length > 0 && jQuery(link).is(':visible');
JS;

        return $this->driver->executeScript($script);
    }
}
