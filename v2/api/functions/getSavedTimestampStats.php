<?php
    function getSavedTimestampStats($places_data) {
        $places_metadata_saved_timestamps = [];

        foreach($places_data as $place_data) {
            if($place_data["saved_timestamp"] !== "") {
                array_push($places_metadata_saved_timestamps, date("Y-m", $place_data["saved_timestamp"]));
            }
        }

        $places_metadata_saved_timestamp_date_count_pairs = array_count_values($places_metadata_saved_timestamps);
        ksort($places_metadata_saved_timestamp_date_count_pairs);
        
        $saved_timestamp_date_count_pairs = [];

        foreach ($places_metadata_saved_timestamp_date_count_pairs as $date => $count) {
            array_push(
                $saved_timestamp_date_count_pairs,
                [
                    "date" => $date,
                    "count" => $count
                ]
            );
        }

        $all_dates = [];
        $start_year = 2015;
        
        for ($year = $start_year; $year <= date("Y"); $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $month_with_leading_zeros = str_pad($month, 2, '0', STR_PAD_LEFT);

                if ($year < date("Y")) {
                    array_push($all_dates, [
                        "date" => $year."-".$month_with_leading_zeros,
                        "count" => 0
                    ]);
                } else if ($year == date("Y")) {
                    if ($month_with_leading_zeros <= date("m")) {
                        array_push($all_dates, [
                            "date" => $year."-".$month_with_leading_zeros,
                            "count" => 0
                        ]);
                    }
                }
            }
        }

        $saved_timestamp_date_count_pairs_with_zeros = [];

        foreach ($all_dates as $key => $blank_date_count_pair) {
            $saved_timestamp_date_count_pairs_with_zeros[$key] = $blank_date_count_pair;

            foreach ($saved_timestamp_date_count_pairs as $saved_timestamp_date_count_pair) {
                if ($blank_date_count_pair["date"] === $saved_timestamp_date_count_pair["date"]) {
                    $saved_timestamp_date_count_pairs_with_zeros[$key] = $saved_timestamp_date_count_pair;
                }
            }
        }
                
        usort($saved_timestamp_date_count_pairs_with_zeros, function($a, $b) {
            return $b["date"] < $a["date"];
        });

        return $saved_timestamp_date_count_pairs_with_zeros;
    }
?>