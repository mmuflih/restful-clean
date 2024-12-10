<?php

/**
 * @author M. Muflih Kholidin <mmuflic@gmail.com>
 * Date: 04.07.2017
 */

namespace App\Context;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasSQLPaginate
{
    protected $request;
    protected $sqlData;
    protected $sqlCount;

    /** @return LengthAwarePaginator */
    public function paginate()
    {
        $pageNo = 1;
        $pageSize = 1000;
        if ($this->request && $this->request->has('page')) {
            $pageNo = $this->request->get("page");
        }
        if ($this->request && $this->request->has('size')) {
            $pageSize = $this->request->get("size");
        }
        $offset = ($pageNo - 1) * $pageSize;
        $items = DB::select($this->sqlData . " LIMIT $pageSize OFFSET $offset");
        $count = DB::select($this->sqlCount);
        return new PagedList($items, $count, $pageNo, $pageSize);
    }
}
