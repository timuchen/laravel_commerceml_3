<?php

namespace Timuchen\LaravelCommerceml3\Http\Controllers\Traits;

use App;
use Exception;
use File;
use ZipArchive;

use Timuchen\LaravelCommerceml3\Interfaces\Import;
use Timuchen\LaravelCommerceml3\Interfaces\ImportBitrix;
use Timuchen\LaravelCommerceml3\Model\FileName;
use Timuchen\LaravelCommerceml3\CommerceML;

trait ImportCML{

    protected function getImportModel()
    {
        $modelCLass = config('protocolExchange1C.catalogWorkModel');
        // проверка модели
        if (empty($modelCLass)) {
            return $this->failure('Mode: '.$this->stepImport
                .', please set model to import data in catalogWorkModel key.');
        }

        /** @var Import $model */
        $model = App::make($modelCLass);
        if (! $model instanceof Import) {
            return $this->failure('Mode: '.$this->stepImport.' model '
                .$modelCLass
                .' must implement \Mavsan\LaProtocol\Interfaces\Import');
        }

        return $model;
    }

    /**
     * Импорт данных
     * @return string
     * @throws Exception
     */
    protected function import()
    {
        $unZip = $this->unzipIfNeed();

        if ($unZip == 'more') {
            return $this->answer('progress');
        } elseif (! empty($unZip)) {
            return $this->failure('Mode: '.$this->stepImport.' '.$unZip);
        }

        // проверка валидности имени файла
        $fileName = $this->importGetFileName($this->request->get('filename'));
        if (empty($fileName)) {
            return $this->failure('Mode: '.$this->stepImport
                .' wrong file name: '
                .$this->request->get('filename'));
        }

//        $fullPath = $this->getFullPathToFile($fileName);
//        $parseCML = new CommerceML();
//        $parseCML->addXmls($fileName, $fullPath);
//        $category = $parseCML->getCollection('category');
//        return dd($category);

//        $fullPath = $this->getFullPathToFile($fileName);
//        $parseCML = new CommerceML();
//        $parseCML->addXmls($fileName, $fullPath);
//        $product = $parseCML->getCollection('product');
//        return dd($product);
//
//        $fullPath = $this->getFullPathToFile($fileName);
//        $parseCML = new CommerceML();
//        $parseCML->addXmls($fileName, $fullPath);
//        $product = $parseCML->getCollection('priceType');
//        return dd($product);

//        $fullPath = $this->getFullPathToFile($fileName);
//        $parseCML = new CommerceML();
//        $parseCML->addXmls($fileName, $fullPath);
//        $product = $parseCML->getCollection('properties_offers');
//        return dd($product);

        $fullPath = $this->getFullPathToFile($fileName);
        $parseCML = new CommerceML();
        $parseCML->addXmls($fileName, $fullPath);
        $product = $parseCML->getCollection('prices');
        return dd($product);

        /** @var Import $model */
        $model = $this->getImportModel();
        if (! $model instanceof Import) {
            return $model;
        }

        try {
            $fullPath = $this->getFullPathToFile($fileName);

            if (! File::isFile($fullPath)) {
                return $this->failure('Mode: '.$this->stepImport.', file '
                    .$fullPath
                    .' not exists');
            }

            $ret = $model->import($fullPath);

            return $this->importAnalyzeModelAnswer($ret, $model);

        } catch (Exception $e) {

            return $this->failure('Mode: '.$this->stepImport
                .", exception: {$e->getMessage()}\n"
                ."{$e->getFile()}, {$e->getLine()}\n"
                ."{$e->getTraceAsString()}");

        }
    }

    /**
     * Анализ ответа от модели обработки каталога товаров
     *
     * @param string                               $result
     * @param \Mavsan\LaProtocol\Interfaces\Import $model
     *
     * @return string
     */
    protected function importAnalyzeModelAnswer($result, Import $model)
    {
        $retData = explode("\n", $result);
        $valid = [
            Import::answerSuccess,
            Import::answerProgress,
            Import::answerFailure,
        ];

        if (! in_array($retData[0], $valid)) {
            return $this->failure('Mode: '.$this->stepImport.' model '
                .class_basename($model)
                .' model return wrong answer');
        }

        $log = $model->getAnswerDetail();

        return $this->answer($result."\n".$log);
    }

    /**
     * Запрос на деактивацию данных
     *
     * @param $dateTime
     *
     * @return string
     */
    protected function importDeactivate($dateTime)
    {
        try {
            $model = $this->getImportModel();
            if ($model instanceof ImportBitrix) {
                $ret = $model->modeDeactivate($dateTime);

                return $this->importAnalyzeModelAnswer($ret, $model);
            }

            return $model::answerSuccess;
        } catch (Exception $e) {
            return $this->failure('Mode: '.$this->stepImport
                .", exception: {$e->getMessage()}\n"
                ."{$e->getFile()}, {$e->getLine()}\n"
                ."{$e->getTraceAsString()}");
        }
    }

    /**
     * Последний этап обмена от 1СБитрикс
     * @return string
     */
    protected function importComplete()
    {
        try {
            $model = $this->getImportModel();

            if ($model instanceof ImportBitrix) {
                $ret = $model->modeComplete();

                return $this->importAnalyzeModelAnswer($ret, $model);
            }

            return $model::answerSuccess;
        } catch (Exception $e) {
            return $this->failure('Mode: '.$this->stepImport
                .", exception: {$e->getMessage()}\n"
                ."{$e->getFile()}, {$e->getLine()}\n"
                ."{$e->getTraceAsString()}");
        }
    }

    /**
     * Распаковка файлов, если требуется
     *
     * @return string
     * @throws Exception
     */
    protected function unzipIfNeed()
    {
        $files = session('inputZipped', []);

        if (empty($files)) {
            return '';
        }

        $file = array_shift($files);

        session(['inputZipped' => $files]);

        try {
            $zip = new ZipArchive();

            if ($zip->open($file) !== true) {
                return 'Error opening zipped: '.$file;
            }
        } catch (Exception $e) {
            return 'Error opening zipped: '.$e->getMessage();
        }

        $path =
            config('protocolExchange1C.inputPath').'/'.$this->checkInputPath();

        $zip->extractTo($path);
        $zip->close();

        File::delete($file);

        return 'more';
    }

    /**
     * Получение и очистка имени файла. Все, что тут сделано - взято из 1С
     * Битрикс
     *
     * В случае, если имя переданное файла не проходит фильтры - будет
     * возвращена пустая строка
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function importGetFileName($fileName)
    {
        $modeFileName = new FileName($fileName);
        if ($modeFileName->hasScriptExtension()
            || $modeFileName->isFileUnsafe()
            || ! $modeFileName->validatePathString("/$fileName")
        ) {
            return '';
        }

        return $modeFileName->getFileName();
    }

}
