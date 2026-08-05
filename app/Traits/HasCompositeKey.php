<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCompositeKey
{
    /**
     * Get the composite keys for the model.
     */
    public function getCompositeKeys()
    {
        return $this->compositeKeys ?? [$this->getKeyName()];
    }

    /**
     * Set the keys for a save update query.
     */
    protected function setKeysForSaveQuery($query)
    {
        if (isset($this->compositeKeys)) {
            foreach ($this->compositeKeys as $key) {
                $query->where($key, '=', $this->getAttribute($key));
            }

            return $query;
        }

        return parent::setKeysForSaveQuery($query);
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey()
    {
        if (isset($this->compositeKeys)) {
            $keys = [];
            foreach ($this->compositeKeys as $key) {
                $keys[] = $this->getAttribute($key);
            }

            return implode('_', $keys);
        }

        return parent::getKey();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return $this->getKey();
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (isset($this->compositeKeys)) {
            $parts = explode('_', (string) $value);
            if (count($parts) === count($this->compositeKeys)) {
                $query = $this->newQuery();
                foreach ($this->compositeKeys as $index => $key) {
                    $query->where($this->getTable() . '.' . $key, $parts[$index]);
                }

                return $query->first();
            }
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (isset($this->compositeKeys)) {
            $parts = explode('_', (string) $value);
            if (count($parts) === count($this->compositeKeys)) {
                foreach ($this->compositeKeys as $index => $key) {
                    $query->where($this->getTable() . '.' . $key, $parts[$index]);
                }
                return $query;
            }
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query)
    {
        return new class($query) extends Builder
        {
            public function whereKey($id)
            {
                $model = $this->getModel();
                if (method_exists($model, 'getCompositeKeys') && property_exists($model, 'compositeKeys')) {
                    $keys = $model->getCompositeKeys();

                    if (is_array($id)) {
                        $this->where(function ($q) use ($id, $keys) {
                            foreach ($id as $composite) {
                                $parts = explode('_', $composite);
                                if (count($parts) === count($keys)) {
                                    $q->orWhere(function ($subQ) use ($parts, $keys) {
                                        foreach ($keys as $index => $key) {
                                            $subQ->where($key, $parts[$index]);
                                        }
                                    });
                                }
                            }
                        });

                        return $this;
                    }

                    $parts = explode('_', $id);
                    if (count($parts) === count($keys)) {
                        foreach ($keys as $index => $key) {
                            $this->where($key, $parts[$index]);
                        }

                        return $this;
                    }
                }

                return parent::whereKey($id);
            }

            public function whereKeyNot($id)
            {
                $model = $this->getModel();
                if (method_exists($model, 'getCompositeKeys') && property_exists($model, 'compositeKeys')) {
                    $keys = $model->getCompositeKeys();

                    if (is_array($id)) {
                        foreach ($id as $composite) {
                            $parts = explode('_', $composite);
                            if (count($parts) === count($keys)) {
                                $this->where(function ($q) use ($parts, $keys) {
                                    foreach ($keys as $index => $key) {
                                        $q->where($key, '!=', $parts[$index]);
                                    }
                                });
                            }
                        }

                        return $this;
                    }

                    $parts = explode('_', $id);
                    if (count($parts) === count($keys)) {
                        $this->where(function ($q) use ($parts, $keys) {
                            foreach ($keys as $index => $key) {
                                $q->where($key, '!=', $parts[$index]);
                            }
                        });

                        return $this;
                    }
                }

                return parent::whereKeyNot($id);
            }
        };
    }
}
