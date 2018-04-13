<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Praxigento\Dcp\Api\Helper;


interface Map
{
    /**
     * Convert rank's code from DB to UI representation.
     *
     * @param string $code
     * @return string
     */
    public function rankCodeToUi(string $code);
}