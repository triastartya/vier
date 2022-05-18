<?php

namespace Att\Workit;

use Att\Workit\Interfaces\ModelDictionary;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait AttModel
{
    protected $selectedFields;
    protected $separator = '.';

    /**
     * Get model database fields from $modelFields property
     * Property format: ['fieldName1' => 'type', 'fieldName2' => 'type']
     *
     * @return array
     */
    public function getModelFields() : array
    {
        return $this->modelFields ?? [];
    }

    /**
     * Get model relations from $modelRelations property
     * Property format: ['relation1', 'relation2']
     *
     * @return array
     */
    public function getModelRelations() : array
    {
        return $this->modelRelations ?? [];
    }

    public function scopeApplySelect($query, array $fields)
    {
        $relationFields = [];
        $relationFieldKeys = [];
        $selectAllFields = false;

        foreach ($fields as $field) {
            if ($field == '*') {
                $selectAllFields = true;
            } else if ($this->validFieldName($field)) {
                $query->addSelect($field);
            } else if (Str::contains($field, $this->separator)) {
                $relationKeys = $this->getRelationKeys($field);
                $relationName = $relationKeys[0];
                $fieldName = Str::after($field, $this->separator);

                if ($this->validRelationName($relationName) && $fieldName) {
                    $modelKeyName = $this->getModelKeyName($relationName);
                    $relatedKeyName = $this->getRelatedKeyName($relationName);

                    if (!in_array($modelKeyName, $relationFieldKeys)) {
                        array_push($relationFieldKeys, $modelKeyName);
                    }

                    if (
                        !array_key_exists($relationName, $relationFields) ||
                        (array_key_exists($relationName, $relationFields) && !in_array($relatedKeyName, $relationFields[$relationName]))
                    ) {
                        $relationFields[$relationName][] = $relatedKeyName;
                    }

                    $relationFields[$relationName][] = $fieldName;
                }
            }
        }

        if ($selectAllFields) {
            $query->select('*');
        }

        $query->addSelect($relationFieldKeys);
        $query->applyRelation($relationFields);
    }

    public function scopeApplyFilter($query, array $filters)
    {
        $query->where(function ($query) use ($filters) {
            foreach ($filters as $filter) {
                $column = $filter['column'];
                $operator = $filter['operator'];
                $value = $filter['value'];

                if (empty($column) || empty($operator) || empty($value)) {
                    continue;
                }

                if (Str::contains($column, '.')) {
                    $relationKeys = $this->getRelationKeys($column);
                    $relationName = $relationKeys[0];
                    $columnName = Str::after($column, '.');

                    $query->applyWhereHasRelation(
                        $relationName,
                        $columnName,
                        $operator,
                        $value
                    );
                } else if ($this->validFieldName($column)) {
                    $query->where($column, $operator, $value);
                }
            }
        });
    }

    public function scopeApplyWhereHasRelation($query, $relationName, $fieldName, $operator, $value)
    {
        $hasOtherRelation = count(explode('.', $fieldName)) > 1;
        $relations = $relationName;
        $searchField = Str::afterLast($fieldName, '.');

        if ($hasOtherRelation) {
            $relations .= '.' . Str::beforeLast($fieldName, '.');
        }

        // TODO: Validate relation & field name
        $query->whereHas($relations, function ($query) use ($searchField, $operator, $value) {
            $query->where($searchField, $operator, $value);
        });
    }

    public function scopeApplyKeywordFilter($query, string $keyword = null, $fields = [])
    {
        $query->when($keyword, function ($query) use ($keyword, $fields) {
            $query->where(function ($query) use ($keyword, $fields) {
                $modelFields = $this->getModelFields();

                if (empty($fields)) {
                    $fields = $modelFields;
                } else {
                    $fields = array_filter($modelFields, function ($modelField, $index) use ($fields) {
                        return in_array($modelField['name'], $fields);
                    }, ARRAY_FILTER_USE_BOTH);
                }

                foreach ($fields as $field) {
                    // TODO: Check for every column type??????
                    if ($field['type'] == ModelDictionary::COLUMN_TYPE_STRING) {
                        $query->orWhere($field['name'], 'like', "%${keyword}%");
                    }
                }
            });
        });
    }

    public function scopeApplyOrder($query, array $orders)
    {
        foreach ($orders as $key => $type) {
            if ($this->validFieldName($key) && in_array($type, ['asc', 'desc'])) {
                $query->orderBy($key, $type);
            }
        }
    }

    public function scopeApplyWhereHas($query, array $filters)
    {
        foreach ($filters as $filter) {
            $column = $filter['column'];
            $condition = $filter['condition'];

            if (is_callable($condition) && $this->validRelationName($column)) {
                $query->whereHas($column, $condition);
            } else if ($this->validRelationName($column) && is_array($condition)) {
                $query->whereHas($column, function ($query) use ($condition) {
                    $query->applyFilter([$condition]);
                });
            } else if ($this->validRelationName($condition)) {
                $query->whereHas($condition);
            }
        }
    }

    public function scopeApplyRelation($query, array $relations)
    {
        foreach ($relations as $name => $fields) {
            $query->with($name, function ($query) use ($fields) {
                $query->applySelect($fields);
            });
        }
    }

    public function validFieldName(string $fieldName)
    {
        return in_array(
            $fieldName,
            collect($this->getModelFields())->pluck('name')->all()
        );
    }

    public function getRelationKeys(string $key)
    {
        return explode('.', $key);
    }

    public function validRelationName($name)
    {
        return in_array($name, $this->getModelRelations());
    }

    public function validRelationAndFieldName(string $relationName, string $fieldName)
    {
        return $this->validRelationName($relationName) && $this->$relationName()->getModel()->validFieldName($fieldName);    
    }

    public function getModelKeyName($relationName)
    {
        $relation = $this->$relationName();

        if ($relation instanceof BelongsTo) {
            return $relation->getForeignKeyName();
        } else if ($relation instanceof HasMany) {
            return $relation->getParentKey() ?? 'id';
        }
    }

    public function getRelatedKeyName($relationName)
    {
        $relation = $this->$relationName();

        if ($relation instanceof BelongsTo) {
            return $relation->getOwnerKeyName();
        } else if ($relation instanceof HasMany) {
            return $relation->getForeignKeyName();
        }
    }

    /**
     * Get model dictionary
     *
     * @return array
     */
    public static function getModelDictionary() :   array
    {
        $model = new static;
        $fields = $model->getModelFields();
        $relations = [];

        foreach ($model->getModelRelations() as $relation) {
            $relationObj = $model->$relation();

            array_push($relations, [
                'model' => get_class($relationObj->getRelated()->getModel()),
                'name' => $relation,
                'return' => ($relationObj instanceof HasMany) ? ModelDictionary::RELATION_TYPE_MANY : ModelDictionary::RELATION_TYPE_ONE
            ]);
        }

        return [
            'model' => get_class($model),
            'fields' => $fields,
            'relations' => $relations
        ];
    }

    public function getLovFields()
    {
        return $this->lovFields ?? [];
    }
}