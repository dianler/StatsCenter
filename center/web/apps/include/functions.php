<?php

function array_rebuild($array, $key, $value = '')
{
    $r = array();

    foreach ($array as $k => $v)
    {
        $r[$v[$key]] = $value ? $v[$value] : $v;
    }

    return $r;
}

function get_post($key)
{
    if (isset($_POST[$key]))
    {
        return $_POST[$key];
    }
    elseif (isset($_GET[$key]))
    {
        return $_GET[$key];
    }
    else
    {
        return false;
    }
}

function is_valid_url($url)
{
    // return (bool) preg_match('@^(https?|ftp)://[^\s/$.?#].[^\s]*$@iS', $url);
    // TODO: 拆分两个方法判断
    return (bool) preg_match('@^(https?|ftp|chelun|autopaiwz|chelunwelfare|chelunkjz|drivingcoach)://[^\s/$.?#].[^\s]*$@iS', $url);
}

function array_get($array, $key, $default = '')
{
    return isset($array[$key]) ? $array[$key] : $default;
}

function filter_value($value, $trim = false, $escape = true)
{
    if ($trim)
    {
        $value = trim($value);
    }
    if ($escape)
    {
        $value = htmlspecialchars($value);
    }
    return $value;
}
