<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    /**
     * Scope a query to only include enabled items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsEnabled(Builder $query)
    {
        $tableNames = $query->getModel()->getTable();
        return $query->where($tableNames . '.status', true);
    }

    /**
     * Scope a query to only include disabled items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsDisabled(Builder $query)
    {
        $tableNames = $query->getModel()->getTable();
        return $query->where($tableNames . '.status', false);
    }

    /**
     * Enable the model.
     *
     * @return bool
     */
    public function enable()
    {
        $this->status = true;
        return $this->save();
    }

    /**
     * Disable the model.
     *
     * @return bool
     */
    public function disable()
    {
        $this->status = false;
        return $this->save();
    }

    /**
     * Toggle the status of the model.
     *
     * @return bool
     */
    public function toggleStatus()
    {
        $this->status = !$this->status;
        return $this->save();
    }
}
