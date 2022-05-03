<?php
namespace ddn\api;

class _Functions_Placeholder {
    /**
     * This function makes that all the static functions of this object are available in the global scope; it is useful if they are being used
     *   very often and avoids needing to use the object name in the global scope (e.g. for pug templates).
     * 
     * (*) NOTE: This function is not called automatically.
     */
    static function define_functions() {
        $functions = [];
        foreach (get_class_methods(get_called_class()) as $method_name) {
            if ($method_name !== "define_functions") {
                if (!function_exists($method_name))
                    array_push($functions, "function $method_name(...\$args) { return " . get_called_class() . "::$method_name(...\$args); }");
            }
        };
        eval(implode("\n", $functions));
    }    
}

class Helpers extends _Functions_Placeholder {
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
     * Function that obtains a random string of a given length, using a standard alphanumeric characters set (a-z, A-Z, 0-9), or an extended set (a-z, A-Z, 0-9, !\"#$%&'()*+,-.:;<=>?[]^_{|}~, etc.)
     * @param $length the length of the string to generate (default: 8)
     * @param $extended if true, the extended set of characters is used (default: false)
     * @return the random string
     */
    static function get_random_string($length = 8, $extended = false) {
        // 218340105584896 combinations (> 2*10^14) vs UUID (> 5*10^36)
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        if ($extended) {
            $codeAlphabet.= "!\"#$%&'()*+,-.:;<=>?[]^_{|}~";
        }
        $max = strlen($codeAlphabet);
    
       for ($i=0; $i < $length; $i++) {
           $token .= $codeAlphabet[random_int(0, $max-1)];
       }
    
       return $token;
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
    private static function _p_debug($type, $force, $skip = 1, $msg, ...$args) {
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
            echo "<pre class=\"$type\">";
            echo "<small>$line</small>";
            // We include a margin o 10 extra null just in case the msg contains a % sign and we want to print it
            $args = array_merge($args, array_fill(0, 10, null));
            echo sprintf($msg, ...$args);
            echo "</pre>";
        }
    }    
}

namespace ddn\api\Helpers;

class Web extends \ddn\api\_Functions_Placeholder {
    static $CSS_FILES = [];
    static $JS_FILES = [];
    static function add_css_file($fname) {
        if (!in_array($fname, self::$CSS_FILES))
            self::$CSS_FILES[] = $fname;
    }

    static function str_start_with($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    static $ROOT_URL = "";
    static $SERVER_NAME = "http://localhost";

    static function set_root_url($url) {
        self::$ROOT_URL = rtrim($url, "/");
    }

    static function set_servername($name) {
        self::$SERVER_NAME = $name;
    }

    static function redirect_to(...$url) {
        header("Location: " . get_url(...$url));
        die();            
    }    

    /**
     * This function makes that all the static functions of this object are available in the global scope; it is useful if they are being used
     *   very often and avoids needing to use the object name in the global scope (e.g. for pug templates).
     * 
     * (*) NOTE: This function is not called automatically.
     */
    // static function define_functions() {
    //     function add_js_file(...$args) { return \ddn\api\Helpers\Web::add_js_file($args); }
    //     $functions = [];
    //     foreach (get_class_methods(__CLASS__) as $method_name) {
    //         if ($method_name !== "define_functions") {
    //             if (!function_exists($method_name))
    //                 array_push($functions, "function $method_name(...\$args) { return " . __CLASS__ . "::$method_name(...\$args); }");
    //         }
    //     };
    //     eval(implode("\n", $functions));
    // }

    static function dump_css_files() {
        foreach (self::$CSS_FILES as $fname) {
            echo "<link rel=\"stylesheet\" href=\"$fname\">\n";
        }
    }

    static function add_js_file($fname) {
        if (!in_array($fname, self::$JS_FILES))
            self::$JS_FILES[] = $fname;
    }

    static function dump_js_files() {
        foreach (self::$JS_FILES as $fname) {
            echo "<script src=\"$fname\"></script>\n";
        }
    }

    /** Retrieves the current accessed URI */
    static function get_current_url() {
        return $_SERVER['REQUEST_URI'];
    }

    /** Retrieves the configured root url */
    static function get_root_url() {
        return self::$ROOT_URL . '/';
    }

    /** 
     * Gets a relative URL
     * - get_url() - returns the relative URL of the current page
     * - get_url($url) - returns the relative URL of the given url
     * - get_url([var=>value, ...], $url) - returns the relative URL of the given url with the given variables in the query string
     * - get_url([var=>value, ...]) - returns the relative URL of the current page with the given variables in the query string
     */
    static function get_url(...$arguments) {
        if (count($arguments) === 0) {
            $arguments = [ $_SERVER['REQUEST_URI'] ]; 
        }
        if (is_array($arguments[0])) {
            return add_query_var(...$arguments);
        }
        if (self::str_start_with($arguments[0], "http://") || self::str_start_with($arguments[0], "https://")) {
            return $arguments[0];
        }
        return self::$ROOT_URL . '/' . ltrim($arguments[0], '/');
    }

    /**
     * Gets a full URL, including the server name (see get_abs_url())
     */
    static function get_url_abs(...$arguments) {
        return self::$SERVER_NAME . self::get_rel_url(...$arguments);
    }

    /*
    static function add_query_var_rel($values, $uri = null) {
        if ($uri !== null) {
            $uri = get_rel_url($uri);
        }
        return add_query_var($values, $uri);
    }
    */

    /**
     * Adds a query string to the given url (or to the current URI if no url is given)
     */
    static function add_query_var($values, $uri = null) {
        if ($uri === null) 
            $uri = $_SERVER['REQUEST_URI'];

        if ($uri[0] === '/')
            $uri = '/' . ltrim($uri, '/');

        $uri_parts = parse_url($uri);
        if (!isset($uri_parts['query'])) 
            $uri_parts['query'] = "";

        parse_str($uri_parts['query'], $query_vars);

        // $values has precedence over $query_vars
        $uri_parts['query'] = http_build_query($values + $query_vars);

        $result = '';
        if (isset($uri_parts['scheme'])) $result .= $uri_parts['scheme']. "://";
        if (isset($uri_parts['user'])) $result .= $uri_parts['user'];
        if (isset($uri_parts['pass'])) $result .= ':' . $uri_parts['pass'];
        if (isset($uri_parts['user']) || isset($uri_parts['pass'])) $result .= '@';
        if (isset($uri_parts['host'])) $result .= $uri_parts['host'];
        if (isset($uri_parts['port'])) $result .= ':' . $uri_parts['port'];
        if (isset($uri_parts['path'])) $result .= $uri_parts['path'];
        if (isset($uri_parts['fragment'])) $result .= '#' . $uri_parts['fragment'];
        if (isset($uri_parts['query'])) $result .= '?' . $uri_parts['query'];

        return $result;
    }
}