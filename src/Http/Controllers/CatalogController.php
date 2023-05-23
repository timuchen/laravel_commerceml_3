<?php

namespace Timuchen\LaravelCommerceml3;

use Auth;
use Exception;
use File;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Log;
use Session;

class CatalogController extends BaseController
{
    /** @var  Request */
    protected $request;
    protected $stepCheckAuth = 'checkauth';
    protected $stepInit = 'init';
    protected $stepFile = 'file';
    protected $stepImport = 'import';
    protected $stepDeactivate = 'deactivate';
    protected $stepComplete = 'complete';
    protected $stepQuery = 'query';
    protected $stepSuccess = 'success';

    /**
     * Запись в лог данных запроса, если это необходимо
     *
     * @param $type
     * @param $mode
     */


}
