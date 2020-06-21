<?php
    // From https://stackoverflow.com/a/36040388
    function getMedian($arr) {
        sort($arr);
        $count = count($arr);
        $middleval = floor(($count-1)/2);
        if ($count % 2) {
            $median = $arr[$middleval];
        } else {
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }
?>