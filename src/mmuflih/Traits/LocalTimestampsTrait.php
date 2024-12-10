<?php

/**
 * Created by Muhammad Muflih Kholidin
 * https://github.com/mmuflih
 * muflic.24@gmail.com
 **/

namespace MMuflih\Traits;

trait LocalTimestampsTrait
{
    public function getCreatedAtAttribute($value)
    {
        $format = 'Y-m-d H:i:s';
        $timezone = env('APP_TIMEZONE');
        $date = $this->asDateTime($value)->setTimezone($timezone);
        return $date->format($format);
    }

    public function getUpdatedAtAttribute($value)
    {
        $format = 'Y-m-d H:i:s';
        $timezone = env('APP_TIMEZONE');
        $date = $this->asDateTime($value)->setTimezone($timezone);
        return $date->format($format);
    }
}
