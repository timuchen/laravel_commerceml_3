<?php

namespace Timuchen\LaravelCommerceml3\Http\Controllers;

use Auth;
use Exception;
use File;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Log;
use Session;

use Timuchen\LaravelCommerceml3\Http\Controllers\Traits\ImportCML;
use Timuchen\LaravelCommerceml3\Model\FileName;


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

    use ImportCML;

    public function __construct(Request $request){
        $this->request = $request;
    }

    /**
     *
     * @param $type
     * @param $mode
     */
    protected function logRequestData($type, $mode)
    {
        if (config('configExchange1C.logCommandsOf1C', false)) {
            Log::debug('Command from 1C type: '.$type.'; mode: '.$mode);
        }

        if (config('configExchange1C.logCommandsHeaders', false)) {
            Log::debug('Headers:');
            Log::debug($this->request->header());
        }

        if (config('configExchange1C.logCommandsFullUrl', false)) {
            Log::debug('Request: '.$this->request->fullUrl());
        }
    }

    public function catalogIn()
    {
        $type = $this->request->get('type');
        $mode = $this->request->get('mode');

        $this->logRequestData($type, $mode);

        if ($type != 'catalog' && $type != 'sale') {
            return $this->checkAuth('');
        }

        if (!$this->checkCSRF($mode)) {
            return $this->failure('CSRF token mismatch');
        }

        if (!$this->userLogin()) {
            return $this->failure('wrong username or password');
        } else {
            $cookie = $this->request->header('cookie');
            $sessionName = config('session.cookie');
            if ($cookie
                && preg_match("/$sessionName=([^;\s]+)/", $cookie, $matches)) {
                $id = $matches[1];
                session()->setId($id);
            }
        }

        switch ($mode) {
            case $this->stepCheckAuth:
                return $this->checkAuth($type);
                break;

            case $this->stepInit:
                return $this->init($type);
                break;

            case $this->stepFile:
                return $this->getFile();
                break;

            case $this->stepImport:
                try {
                    return $this->import();
                } catch (Exception $e) {
                    return $this->failure($e->getMessage());
                }
                break;

            case $this->stepDeactivate:
                $startTime = $this->getStartTime();

                return $startTime !== null
                    ? $this->importDeactivate($startTime)
                    : $this->failure('Cannot get start time of session, url: '.$this->request->fullUrl()."\nRegexp: (\d{4}-\d\d-\d\d)_(\d\d:\d\d:\d\d)");
                break;

            case $this->stepComplete:
                return $this->importComplete();
                break;

            case $this->stepQuery:
                return $this->processQuery();
                break;

            case $this->stepSuccess:
                if($type === 'sale') {
                    return $this->saleSuccess();
                }

                return '';
        }

        return $this->failure();
    }

    protected function getStartTime()
    {
        foreach (array_keys($this->request->all()) as $item) {
            if(preg_match("/(\d{4}-\d\d-\d\d)_(\d\d:\d\d:\d\d)/", $item, $matches)) {
                return "$matches[1] $matches[2]";
            }
        }

        return null;
    }

    protected function checkCSRF($mode)
    {
        if (!config('configExchange1C.isBitrixOn1C', false)
            || $mode === $this->stepCheckAuth) {
            return true;
        }

        foreach ($this->request->all() as $key => $item) {
            if ($key === Session::token()) {
                return true;
            }
        }

        return false;
    }

    protected function failure($details = '')
    {
        $return = "failure".(empty($details) ? '' : "\n$details");

        return $this->answer($return);
    }

    protected function answer($answer)
    {
        return iconv('UTF-8', 'windows-1251', $answer);
    }

    /**
     * @return bool
     */
    protected function userLogin()
    {
        if (Auth::getUser() === null) {
            $user = \Request::getUser();
            $pass = \Request::getPassword();

            $attempt = Auth::attempt(['email' => $user, 'password' => $pass]);

            if (! $attempt) {
                return false;
            }

            $gates = config('configExchange1C.gates', []);
            if (! is_array($gates)) {
                $gates = [$gates];
            }

            foreach ($gates as $gate) {
                if (Gate::has($gate) && Gate::denies($gate, Auth::user())) {
                    Auth::logout();

                    return false;
                }
            }

            return true;
        }

        return true;
    }

    /**
     *
     * @param string $type sale или catalog
     *
     * @return string
     */
    protected function checkAuth($type)
    {
        $cookieName = config('session.cookie');

        if (! empty(config('configExchange1C.sessionID'))) {
            $cookieID = config('configExchange1C.sessionID');
            Session::setId($cookieID);
            Session::flush();
            Session::regenerateToken();
        } else {
            $cookieID = Session::getId();
        }

        $answer = "success\n$cookieName\n$cookieID";

        if (config('configExchange1C.isBitrixOn1C', false)) {
            if ($type === 'catalog') {
                $answer .= "\n".csrf_token()."\n".date('Y-m-d_H:i:s');
            } elseif ($type === 'sale') {
                $answer .= "\n".csrf_token();
            }
        }

        return $this->answer($answer);
    }

    /**
     *
     * @param string $type
     *
     * @return string
     */
    protected function init($type)
    {
        $zip = "zip=".($this->canUseZip() ? 'yes' : 'no');
        $limit = "file_limit=".config('configExchange1C.maxFileSize');
        $answer = "$zip\n$limit";

        if (config('configExchange1C.isBitrixOn1C', false)) {
            if ($type === 'catalog' || $type === 'sale') {
                $answer .=
                    "\nsessid=".Session::getId().
                    "\nversion=".config('configExchange1C.catalogXmlVersion');
            }
        }

        return $this->answer($answer);
    }

    /**
     * @return bool
     */
    protected function canUseZip()
    {
        if (class_exists('ZipArchive') && config('configExchange1C.use_zip')){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Получение файла(ов)
     * @return string
     */
    protected function getFile()
    {
        $modelFileName = new FileName($this->request->input('filename'));
        $fileName = $modelFileName->getFileName();

        if (empty($fileName)) {
            return $this->failure('Mode: '.$this->stepFile
                .', parameter filename is empty');
        }

        $fullPath = $this->getFullPathToFile($fileName, true);

        $fData = $this->getFileGetData();

        if (empty($fData)) {
            return $this->failure('Mode: '.$this->stepFile
                .', input data is empty.');
        }

        if ($file = fopen($fullPath, 'ab')) {
            $dataLen = mb_strlen($fData, 'latin1');
            $result = fwrite($file, $fData);

            if ($result === $dataLen) {
                // файлы, требующие распаковки
                $files = [];

                if ($this->canUseZip()) {
                    $files = session('inputZipped', []);
                    $files[$fileName] = $fullPath;
                }

                session(['inputZipped' => $files]);

                return $this->success();
            }

            $this->failure('Mode: '.$this->stepFile
                .', can`t wrote data to file: '.$fullPath);
        } else {
            return $this->failure('Mode: '.$this->stepFile.', cant open file: '
                .$fullPath.' to write.');
        }

        return $this->failure('Mode: '.$this->stepFile.', unexpected error.');
    }

    /**
     * Формирование полного пути к файлу
     *
     * @param string $fileName
     *
     * @param bool   $clearOld
     *
     * @return string
     */
    protected function getFullPathToFile($fileName, $clearOld = false)
    {
        $workDirName = $this->checkInputPath();

        if ($clearOld) {
            $this->clearInputPath($workDirName);
        }

        $path = config('configExchange1C.inputPath');

        return $path.'/'.$workDirName.'/'.$fileName;
    }

    /**
     * Формирование имени папки, куда будут сохранятся принимаемые файлы
     * @return string
     */
    protected function checkInputPath()
    {
        $folderName = session('inputFolderName');

        if (empty($folderName)) {
            $folderName = date('Y-m-d_H-i-s').'_'.md5(time());

            $fullPath =
                config('configExchange1C.inputPath').DIRECTORY_SEPARATOR
                .$folderName;

            if (! File::isDirectory($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }

            session(['inputFolderName' => $folderName]);
        }

        return $folderName;
    }

    /**
     * Очистка папки, где хранятся входящие файлы от предыдущих принятых файлов
     *
     * @param $currentFolder
     */
    protected function clearInputPath($currentFolder)
    {
//        $storePath = config('configExchange1C.inputPath');
//
//        foreach (File::directories($storePath) as $path) {
//            if (File::basename($path) != $currentFolder) {
//                File::deleteDirectory($path);
//            }
//        }
    }

    /**
     *
     * @return string
     */
    protected function getFileGetData()
    {
        return \Request::getContent();
    }

    /**
     * @return string
     */
    protected function success()
    {
        return $this->answer('success');
    }

}

