<?php
namespace ddn\api;
use ddn\api\Helpers\Functions_Placeholder;
use ddn\api\Helpers\Web;

class Debug extends Functions_Placeholder {
    static $_js_mode = true;

    /**
     * Enables debugging in javascript mode; if disabled, the debugging is made in php mode: using "<pre>" tags 
     */
    static function js_mode() {
        self::$_js_mode = true;
    }

    /**
     * Enables debugging in php mode; if enabled, the debugging is made in php mode: using "<pre>" tags 
     */
    static function php_mode() {
        self::$_js_mode = false;
    }

    /**
     * Function to output the contents of a list variables as a debug message, in case of DEBUG mode
     * @param ...$vars the variables to output
     */
    static function p_var_dump(...$vars) {
        self::_p_debug("debug", false, 1, self::var_dump_to_str(...$vars));
    }
    /**
     * Outputs a debug message using sprintf, in case of DEBUG mode
     * @param $msg the message to output
     * @param ...$args the message is printed using sprintf, so this is included in case the output needs arguments (i.e. for %s, %d, etc.)
     */
    static function p_debug($msg, ...$args) {
        self::_p_debug("debug", false, 1, $msg, ...$args);
    }

    /**
     * Outputs a warning message using sprintf
     * @param $msg the message to output
     * @param ...$args the message is printed using sprintf, so this is included in case the output needs arguments (i.e. for %s, %d, etc.)
     */
    static function p_warning($msg, ...$args) {
        self::_p_debug("warning", true, 1, $msg, ...$args);
    }

    /**
     * Outputs an error message using sprintf
     * @param $msg the message to output
     * @param ...$args the message is printed using sprintf, so this is included in case the output needs arguments (i.e. for %s, %d, etc.)
     */
    static function p_error($msg, ...$args) {
        self::_p_debug("error", true, 1, $msg, ...$args);
    }

    /** 
     * This is a function equivalent to p_debug, but to be used inside helpers, so it skips the 0 level of the debug_backtrace.
     */
    static function p_debug_h($msg, ...$args) {
        self::_p_debug("debug", false, 2, $msg, ...$args);
    }
    /**
     * Function to output the contents of a list variables in a readable format (using var_dump), to a string
     * @param ...$vars the variables to output
     * @return the string with the variables
     */
    static function var_dump_to_str(...$vars) {
        ob_start();
        var_dump(...$vars);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }    
    /**
     * Generic function to output a debug message
     * @param $type the type of message (e.g. "error", "warning", "info", "debug")
     * @param $force whether to force or not the output (if not forced, will be output in case of debug mode. i.e. DEBUG set to true)
     * @param $skip the amount of debug depth to skip; this is useful in case of using this function inside helper functions (defaults to 1, because this function
     *          is inteded to be called from p_debug, p_error, etc. functions)
     * @param $message the message to output
     * @param $args the message is printed using sprintf, so this is included in case the output needs arguments (i.e. for %s, %d, etc.)
     */
    private static function _p_debug($type, $force, $skip, $msg, ...$args) {
        if ($force || (defined('DEBUG') && DEBUG)) {
            $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $skip + 2);
            while ($skip > 0) {
                array_shift($debug);
                $skip--;
            }
            if (str_ends_with($debug[0]["file"], "eval()'d code")) {
                $debug = $debug[1];
            } else {
                $debug = $debug[0];
            }
            $line = basename($debug['file']) . ":" . $debug['line'] . ": ";
            // echo "<script language=\"javascript\">debug(\"$type\", \"" . htmlspecialchars($line) . "\", \"" . htmlspecialchars(str_replace("\n", "\\n", sprintf($msg, ...$args))) . "\");</script>";

            if (self::$_js_mode) {
                self::_init_js_debug();
                Web::add_js_inline("debug(\"$type\", \"" . htmlspecialchars($line) . "\", \"" . htmlspecialchars(str_replace("\n", "\\n", sprintf($msg, ...$args))) . "\");");
            } else {
                echo "<pre class=\"$type\">";
                echo "<small>$line</small>";
                // We include a margin o 10 extra null just in case the msg contains a % sign and we want to print it
                $args = array_merge($args, array_fill(0, 10, null));
                echo sprintf($msg, ...$args);
                echo "</pre>";
            }
        }
    }

    private static function _init_js_debug() {
        static $initd = false;
        if (!$initd) {
            $initd = true;
            Web::add_js_inline("function debug(type, line, txt) {
                var dbg = document.getElementById('debug');
                if (!dbg) {
                    return;
                }
                var el = document.createElement('div');
                el.className = type;
                var ln = document.createElement('div');
                ln.className = 'line';
                ln.innerHTML = line;
                el.appendChild(ln);
                var msg = document.createElement('div');
                msg.className = 'message';
                msg.innerHTML = txt;
                el.appendChild(msg);
                dbg.appendChild(el);
            }");
        }
    }
}
