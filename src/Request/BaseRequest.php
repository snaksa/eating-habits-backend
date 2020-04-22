<?php

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class BaseRequest
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->map();
    }

    public function getRequest() {
        return $this->request;
    }

    public function map() {
        $all = $this->request->request->all();

        foreach ($all as $key => $value) {
            $this->$key = $value;
        }
    }
}
