<?php
namespace ddn\api\Helpers;

class Functions_Placeholder {
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
