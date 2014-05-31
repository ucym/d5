<?php
require 'vendor/Roose/Autoloader.php';

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('ROOSE_COREPATH', dirname(__FILE__) . DS . "vendor" . DS);

//-- オートローダを初期化
Roose_Autoloader::setBasePath(ROOSE_COREPATH);

// クラスの別名を設定
Roose_Autoloader::classAlias(array(
    'Roose_Arr'     => 'Arr',
    'Roose_File'    => 'File',
    'Roose_Input'   => 'Input',
    'Roose_Cookie'  => 'Cookie',
    'Roose_Session' => 'Session',
    'Roose_Security'=> 'Security'
));

// オートローダを登録
Roose_Autoloader::regist();

// ライブラリ初期化処理
Roose_Roose::init();
