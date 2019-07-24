<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use Illuminate\Support\Str;

class MethodNameFormatter
{
    /**
     * Formats the method name into a friendly format.
     * 
     * @param  string $method_name 
     * @return string             
     */
    public static function toLabel($method_name)
    {
        $title = Str::title($method_name);
        
        return str_replace(['-', '_'], ' ', $title);
    }
}