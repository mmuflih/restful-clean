<?php

/**
 * Created by Muhammad Muflih Kholidin
 * https://github.com/mmuflih
 * muflic.24@gmail.com
 **/

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait CodeGenerator
{
    public function genCode($field, $prefix, $seqLen)
    {
        $date = Carbon::now(env('APP_TIMEZONE'))->format('ymd');
        $codes = DB::select("SELECT (count(`$field`)+1) as seq FROM $this->table WHERE `$field` like '$prefix$date-%'");
        foreach ($codes as $code) {
            return "$prefix$date-" . self::seq($code->seq, $seqLen);
        }
        return "$prefix$date-0001";
    }

    public static function seq($seq, $count)
    {
        $code = "";
        for ($i = strlen((string)$seq); $i < $count; $i++) {
            $code .= "0";
        }
        return $code . $seq;
    }
}
