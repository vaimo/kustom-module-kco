<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Test\Unit;

/**
 * Handles all HTML validation
 *
 */
trait TestsHtml
{
    /**
     * Checks that the provided message is valid HTML
     *
     * @param string $actualMessage
     * @return bool
     */
    public function validateHtml(string $actualMessage): bool
    {
        // xml requires one root node
        $string = "<div>{$actualMessage}</div>";

        libxml_use_internal_errors(true);
        libxml_clear_errors();
        return (simplexml_load_string($string) !== false);
    }
}
