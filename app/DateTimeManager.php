<?php

namespace App;

class DateTimeManager {

    public static function getReservationEndDate($start, $nights) {
        $startMilli = strtotime($start);
        $endArr = getDate($startMilli + (60 * 60 * 24 * $nights));
        return $endArr['year'] . '-' . $endArr['mon'] . '-' . $endArr['mday'];
    }
}