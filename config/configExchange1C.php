<?php

declare(strict_types=1);

return [

    '1cRouteNameCatalog' => 'api/1cexc',
    'inputPath'          => storage_path('app/1c_exchange'),
    'imagesPath'         => storage_path('app/public/images'),
    'imagesFormat'       => '.jpg',

    'use_zip'            => true,
    'maxFileSize'        => 10 * 1000 * 1024,

    'filesToSendTest'    => [],
    'filesToWorkTest'    => [],

    'userEmailToTest'    => env('1C_EXCHANGE_LOGIN','admin'),
    'userPasswordToTest' => env('1C_EXCHANGE_PASSWORD','admin'),

    'catalogWorkModel'   => '',
    'saleShareModel'     => '',

    'encodeToWindows1251' => true,

    'saleShareToXML'     => '',

    'logCommandsOf1C'    => true,
    'logCommandsHeaders' => true,
    'logCommandsFullUrl' => true,

    'sessionID'          => 'EQ0uHUXToBjnfaHt6imt36uiXnHOPMeu28n5cboU',
    'gates'              => [],
    'middleware'         => [
        \Illuminate\Session\Middleware\StartSession::class,
    ],

    'isBitrixOn1C'       => true,
    'saleXmlVersion'     => '2.03',
    'catalogXmlVersion'  => '3.1',
];
