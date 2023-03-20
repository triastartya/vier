<?php

namespace Viershaka\Vier;

use Viershaka\Vier\Exceptions\ModelClassIsRequiredException;
use Illuminate\Database\Eloquent\Model;
use Viershaka\Vier\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

class VierRepository
{
    protected $relatedModels = [];
    protected $fields = ['*'];
    protected $filters = [];
    protected $orders = [];
    protected $keyword;
    protected $whereHas = [];
    protected $keywordFields = [];
    protected $model = null;
    protected $modelCls = null;
    public function __construct($modelCls)
    {
        if (!isset($modelCls)) {
            throw new ModelClassIsRequiredException("Model Class is required");
        }

        $this->modelCls = $modelCls;

        $this->model = new $modelCls();

        if (!$this->model instanceof Model) {
            throw new ModelClassIsRequiredException("Model Class must extend Model");
        }
    }

    public function getModel()
    {
        return $this->model;
    }

    public function get($page = 15)
    {
        if ($page <= 0) {
            $page = $this->query()->count();
        }

        return $this->query()->paginate($page);
    }

    public function find($id, $strict = true)
    {
        return $strict ? $this->query()->findOrFail($id) : $this->query()->find($id);
    }
    
    public function first()
    {
        return $this->query()->first();
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function setRelatedModels(array $models)
    {
        $this->relatedModels = $models;

        return $this;
    }

    public function addRelatedModel(string $related)
    {
        array_push($this->relatedModels, $related);

        return $this;
    }

    public function setFilter(array $filters)
    {
        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $column = $filter['column'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'];

            $this->addFilter($column, $operator, $value);
        }

        return $this;
    }

    /**
     * Append to filter;
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * @return void
     */
    public function addFilter(string $column, string $operator, string $value)
    {
        array_push($this->filters, [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ]);

        return $this;
    }

    public function setOrder(array $orders)
    {
        $this->orders = $orders;

        return $this;
    }

    public function setKeyword($keyword = '')
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function setKeywordFields($fields = [])
    {
        $this->keywordFields = $fields;

        return $this;
    }

    protected function query()
    {
        return $this->model->applySelect($this->fields)
            ->applyFilter($this->filters)
            ->applyOrder($this->orders)
            ->applyKeywordFilter($this->keyword, $this->keywordFields)
            ->applyWhereHas($this->whereHas)
            ->with($this->relatedModels);
    }

    public function datatables()
    {
        return datatables()->of($this->query());
    }

    public function table($request)
    {
        $orders = [];

        foreach ($request->columnFilters ?? [] as $key => $value) {
            if (!empty($value) && !empty($key)) {
                $this->addFilter($key, '=', $value);
            }
        }

        foreach ($request->sort ?? [] as $order) {
            if (in_array($order['type'], ['asc', 'desc'])) {
                $orders[$order['field']] = $order['type'];
            }
        }

        return $this->setFilter($this->filters)
            ->setKeyword($request->searchAll)
            ->setOrder($orders)
            ->query()
            ->paginate($request->perPage ?? 15);
    }

    public function setQueryForLoggedInUser($key, $modelKey = 'id')
    {
        $this->addFilter($key, '=', auth()->user()->$modelKey);

        return $this;
    }

    public function setWhereHas(string $key, callable $closure)
    {
        $this->whereHas[$key] = $closure;

        return $this;
    }
    
    protected function validate(array $data, $except = null)
    {
        $rules = ($this->model)->rules($except);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException('Validation error', $validator);
        }

        return $validator->validated();
    }

    public function validateModelIn($model)
    {
        if (!isset($model)) {
            throw new ModelClassIsRequiredException("Model is required");
        }

        $modelClass = get_class($this->model);

        if (get_class($model) !== $modelClass) {
            throw new ModelClassIsRequiredException("Model class must be " . $modelClass);
        }
    }

    public function store(array $data)
    {
        $validated = $this->validate($data);
        $this->storeValidation($data);
        return $this->model->create($validated);
    }

    public function update($model, array $data)
    {
        $this->validateModelIn($model);
        $validated = $this->validate($data, $model->getKey());
        $this->updateValidation($model, $data);
        return $model->update($validated);
    }

    public function delete($model)
    {
        $this->validateModelIn($model);
        $this->deleteValidation($model);
        return $model->delete();
    }
    /**
     * Custom delete validation.. 
     * Throw exception if delete is not allowed
     */
    public function deleteValidation($model) {
    }
    /**
     * Custom store validation.. 
     * Throw exception if store is not allowed
     */
    public function storeValidation($data) {
    }
    /**
     * Custom update validation.. 
     * Throw exception if update is not allowed
     */
    public function updateValidation($model, $data) {
    }
}
