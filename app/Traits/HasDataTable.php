<?php

namespace App\Traits;

trait HasDataTable
{

    /**
     * Método para búsqueda dinámica en columnas específicas.
     */
    public function scopeSearch($query, $search, $columns = [])
    {
        $columns = $columns ?: $query->getModel()::$searchColumns ?? [];

        $query->where(function ($query) use ($search, $columns) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%$search%");
            }
        });

        return $query;
    }

    /**
     * Método para ordenar.
     */
    public function scopeSort($query, $sorts)
    {
        //reemplazar el ordenamiento por defecto
        $query->getQuery()->orders = null;
        foreach ($sorts as $sort) {
            $query->orderBy($sort['key'], $sort['order']);
        }
        return $query;
    }

    /**
     * Método para filtros dinámicos.
     */
    public function scopeFilter($query, $filters)
    {
        foreach ($filters as $filter => $value) {
            if (!is_null($value)) {
                $query->where(function ($query) use ($filter, $value) {
                    $query->where($filter, $value);
                });
            }
        }
        return $query;
    }

    /**
     * Método para obtener resultados en formato DataTable.
     */
    public static function scopeDataTable($query, $request, $searchColumns = [])
    {


        $itemsPerPage = $request->has('itemsPerPage') ? $request->itemsPerPage : 10;

        // Filtros dinámicos
        if ($request->has('filters') && is_array($request->filters)) {
            $query->filter($request->filters);
        }

        // Búsqueda dinámica en columnas especificadas
        if ($request->has('search')) {
            $searchColumns = $searchColumns ?: $query->getModel()->searchColumns;
            $query->search($request->search, $searchColumns);
        }

        // Ordenamiento dinámico
        if ($request->has('sortBy')) {
            $query->sort($request->sortBy);
        }

        return $query->paginate($itemsPerPage);
    }
}
