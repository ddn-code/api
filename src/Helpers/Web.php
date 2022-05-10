<?php
namespace ddn\api\Helpers;
use ddn\api\Helpers\Functions_Placeholder;

class Web extends Functions_Placeholder {
    static $CSS_FILES = [];
    static $JS_FILES = [];
    static $JS_INLINE = [];
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

    static function set_server_name($name) {
        self::$SERVER_NAME = rtrim($name, "/");
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

    static function add_js_inline($js) {
        self::$JS_INLINE[] = $js;
    }

    static function dump_js_inline() {
        echo "<script>\n";
        foreach (self::$JS_INLINE as $js) {
            echo $js;
        }
        echo "</script>\n";
    }

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
        return self::$SERVER_NAME . self::get_url(...$arguments);
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

    public static function get_accessed_url() {
        $s = &$_SERVER;
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        $uri = $protocol . '://' . $host . $s['REQUEST_URI'];
        $segments = explode('?', $uri, 2);
        $url = $segments[0];
        return $url;
    }
}