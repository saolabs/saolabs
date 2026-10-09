<?php

namespace App\Http\Controllers;

use Saola\Core\Http\Controllers\BaseWebController;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

abstract class WebController extends BaseWebController
{
    protected $context = 'web';
    protected $module = null;

    public function responseModule(array $data = [], ?string $bladePath = null, array $options = []): View|JsonResponse
    {
        return $this->response($data, $bladePath ? '@module.' . $bladePath : null, $options);
    }
}
