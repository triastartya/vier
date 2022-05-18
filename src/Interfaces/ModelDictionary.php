<?php

namespace Att\Workit\Interfaces;

interface ModelDictionary
{
    const COLUMN_TYPE_STRING = 'string';
    const COLUMN_TYPE_INTEGER = 'integer';
    const COLUMN_TYPE_BOOLEAN = 'boolean';
    const COLUMN_TYPE_DATE = 'date';

    const RELATION_TYPE_MANY = 'many';
    const RELATION_TYPE_ONE = 'one';
}