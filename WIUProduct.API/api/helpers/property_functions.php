<?php

function hasProperty($mixed, $property)
{
    return isset($mixed[$property]) && !empty($mixed[$property]);
}