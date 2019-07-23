<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use Illuminate\Support\Str;

class MethodNameFormatter
{
    public static function toLabel($method_name)
    {
        $title = Str::title($method_name);
        
        return str_replace(['-', '_'], ' ', $title);
    }
}