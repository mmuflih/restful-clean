<?php

/**
 * @author M. Muflih Kholidin <mmuflic@gmail.com>
 * Date: 04.07.2017
 */

namespace MMuflih\Context;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;

trait HasPaginate
{
    protected $request;
    protected $data;
    protected $dateField;
    protected $qFields = [];

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
        Paginator::currentPageResolver(function () use ($pageNo) {
            return $pageNo;
        });
        return $this->data
            ->paginate($pageSize);
    }

    private function filterDate($data)
    {
        $request = $this->request;
        if (!$this->dateField) {
            $this->dateField = "created_at";
        }
        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $data->whereBETWEEN($this->dateField, [
                $startDate->format('Y-m-d') . " 00:00:00",
                $endDate->format('Y-m-d') . " 23:59:59"
            ]);
        }
        return $data;
    }

    private function filterQ($data)
    {
        $request = $this->request;
        if (empty($this->qFields)) {
            $this->qFields = ["name"];
        }
        if ($request->qu) {
            $q = $request->qu;
            $this->data->where(function ($que) use ($q) {
                foreach ($this->qFields as $key => $field) {
                    if ($key == 0) {
                        $que->where($field, 'like', "%$q%");
                        continue;
                    }
                    $que->orWhere($field, 'like', "%$q%");
                }
            });
        }
        return $data;
    }
}
