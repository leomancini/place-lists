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

    function getImportantTimestamps($places_data) {
        $places_metadata_saved_timestamps = [];

        foreach($places_data as $place_data) {
            if($place_data["saved_timestamp"] !== "") {
                array_push($places_metadata_saved_timestamps, date("Y-m", $place_data["saved_timestamp"]));
            }
        }

        $places_metadata_saved_timestamp_counts = array_count_values($places_metadata_saved_timestamps);
        
        $saved_timestamp_date_count_pairs = [];

        foreach ($places_metadata_saved_timestamp_counts as $date => $count) {
            array_push(
                $saved_timestamp_date_count_pairs,
                [
                    "date" => $date,
                    "count" => $count
                ]
            );
        }

        $saved_timestamp_median_count = getMedian($places_metadata_saved_timestamp_counts);

        $greater_than_median_factor = 2;
        
        $places_metadata_saved_timestamps_greater_than_median = [];
        
        foreach ($saved_timestamp_date_count_pairs as $saved_timestamp_date_count_pair) {
            if ($saved_timestamp_date_count_pair["count"] > ($saved_timestamp_median_count * $greater_than_median_factor)) {
                $saved_timestamp_date_count_pair["reason"] = "greater_than_".$greater_than_median_factor."x_median";
                array_push($places_metadata_saved_timestamps_greater_than_median, $saved_timestamp_date_count_pair);
            }
        }

        return $places_metadata_saved_timestamps_greater_than_median;
    }
?>