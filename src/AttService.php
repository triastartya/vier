<?php

namespace Att\Workit;

use Att\Workit\Exceptions\ModelClassIsRequiredException;
use Att\Workit\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class AttService 
{
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