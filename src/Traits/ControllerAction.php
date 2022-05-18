<?php

namespace Att\Workit\Traits;

use Illuminate\Http\Request;

trait ControllerAction
{
    public function beforeIndex()
    {
        //
    }

    public function afterIndex($data)
    {
        //
    }


    /**
     * This method will be called immediately after calling controller's show method
     *
     * @return void
     */
    public function beforeShow()
    {
        //
    }

    /**
     * This method will be called before returning the data in controller's show method
     *
     * @return void
     */
    public function afterShow($data)
    {
        return $data;
    }

    protected function beforeStore(Request $request)
    {
        return $request->all();
    }

    protected function afterStore($data)
    {
        return $data;
    }

    protected function beforeUpdate(Request $request)
    {
        return $request->all();
    }

    protected function afterUpdate($data)
    {
        return $data;
    }

    protected function beforeDestroy($data)
    {
        //
    }

    public function afterDestroy($data)
    {
        //
    }
}