<?php

namespace App\Inspections;

class Spam
{
    protected array $inspections = [
        InvalidKeywords::class,
        KeyHeldDown::class,
    ];

    public function detect($body)
    {
        foreach ($this->inspections as $inspection) {
//            (new $inspection)->detect($body);
            app($inspection)->detect($body);
        }

        return false;
    }
}
