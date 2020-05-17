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

    public function getRequest()
    {
        return $this->request;
    }

    public function map()
    {
        $data = json_decode($this->request->getContent(), true);

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
