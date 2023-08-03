<?php

namespace App\DataClasses;

interface BaseDataClass
{
    public static function get(?int $item = null): mixed;
}
