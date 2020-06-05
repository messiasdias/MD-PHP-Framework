<?php
namespace  MessiasDias\PHPLibrary\Log;
use Psr\Log\AbstractLogger;

/**
 * Class messiasdias\php_library\Log\Log
 * 
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification
 */

class Log extends AbstractLogger 
{

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $message = $this->interpolate( $message, $context);
    }


    /**
    * Interpolates context values into the message placeholders.
    */
    function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

}