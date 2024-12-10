<?php

/**
 * Created by Muhammad Muflih Kholidin
 * https://github.com/mmuflih
 * muflic.24@gmail.com
 **/

namespace MMuflih\Context;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;

class PagedList implements Arrayable
{
    private $data;
    private $totalRow;
    private $currentPage;
    private $itemsPerPage;

    public function __construct(array $data, $total, $currentPage, $perPage)
    {
        $this->data = collect($data);
        $this->totalRow = intval($total);
        $this->currentPage = intval($currentPage);
        $this->itemsPerPage = intval($perPage);
    }

    public static function fromPaginator(array $data, LengthAwarePaginator $paginator)
    {
        return new static(
            $data,
            $paginator->total(),
            $paginator->currentPage(),
            $paginator->perPage()
        );
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = collect($data);
    }

    public function getTotalRow()
    {
        return $this->totalRow;
    }

    public function setTotalRow($totalRow)
    {
        $this->totalRow = $totalRow;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function toArray()
    {
        $arrayData = $this->data->map(function ($item) {
            if (is_array($item)) {
                return $item;
            } else if ($item instanceof Arrayable) {
                return $item->toArray();
            } else {
                return (array)$item;
            }
        });
        return [
            'data' => $arrayData,
            'total' => $this->totalRow,
            'current_page' => $this->currentPage,
            'per_page' => $this->itemsPerPage,
        ];
    }
}
