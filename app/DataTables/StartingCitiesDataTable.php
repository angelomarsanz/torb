<?php

namespace App\DataTables;

use App\Models\StartingCities;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Http\JsonResponse;

class StartingCitiesDataTable extends DataTable
{
    protected array|string $exportColumns = ['name', 'image'];

    public function ajax(): JsonResponse
    {
        $startingCities = $this->query();

        return datatables()
            ->of($startingCities)
            ->addColumn('image', function ($startingCities) {
                return '<img src="' . $startingCities->image_url . '" width="200" height="100">';
            })
            ->addColumn('action', function ($startingCities) {
                return '<a href="' . url('admin/settings/edit-starting-cities/' . $startingCities->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;<a href="' . url('admin/settings/delete-starting-cities/' . $startingCities->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>';
            })
            ->addColumn('name', function ($startingCities) {
                return '<a href="' . url('admin/settings/edit-starting-cities/' . $startingCities->id) . '">' . $startingCities->name . '</a>';
            })
            ->rawColumns(['action','image','name'])
            ->toJson();
    }

    public function query()
    {
        $startingCities = StartingCities::select();
        return $this->applyScopes($startingCities);
    }

    public function html()
    {
        return $this->builder()
        ->columns([
            'name',
            'image',
            'status'
        ])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters(dataTableOptions());
    }

    protected function filename(): string
    {
        return 'spacetypedatatables_' . time();
    }
}
