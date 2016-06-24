<?php

function blueprint_data_option_test(array $param = null, $sort = false)
{
    if ($sort) {
        asort($param);
    }
    return $param ?: ['yes' => 'Yes', 'no' => 'No'];
}