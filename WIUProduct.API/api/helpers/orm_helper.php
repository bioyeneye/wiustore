<?php

namespace helpers;

class OrmHelper
{
    public static function hasRows($result)
    {
        return mysqli_num_rows($result) > 0;
    }

    public static function getRows($result)
    {
        return mysqli_fetch_assoc($result);
    }
}