<?php
/**
 * クラスオートローダ
 * 
 * @link http://www.infiniteloop.co.jp/docs/psr/psr-1-basic-coding-standard.html PSR-1の日本語訳（非公式）
 * @author うちやま
 * @since PHP 5.2
 */
class Roose_Autoloader
{
    const DS = DIRECTORY_SEPARATOR;
    
    private static $basePath = null;
    private static $loadPath = array();
    private static $classes = array();
    private static $aliases = array();
    
    /**
     * クラスファイルが保存されているディレクトリへのパスを設定します。
     * パスはかならず"/"（windowsの場合は"¥"）で終わっている必要があります。
     * 
     *      このメソッドはaddBasePathのエイリアスとなっており、非推奨です。
     * 
     * @deprecated
     * @param string $path
     */
    public static function setBasePath($path)
    {
        self::addBasePath($path);
    }
    
    /**
     * クラスファイルが保存されているディレクトリへのパスを登録します。
     * @param string $path
     */
    public static function addBasePath($path)
    {
        if (in_array($path, self::$loadPath) === false) {
            self::$loadPath[] = $path;
        }
    }
    
    /**
     * クラス名と対応するパスを登録します。
     * @param mixed $class 登録するクラス名。
     *    連想配列が渡されたとき、インデックスを$class、値を$pathとしてクラスを登録します。
     * @param string|null $path 読み込み先
     */
    public static function addClass($class, $path = null)
    {
        if (is_array($class)) {
            foreach ($class as $cls => $path) {
                self::$classes[$cls] = $path;
            }
            return;
        } elseif ($path === null) {
            throw new Exception('クラス名に対応するパスが指定されていません');
        }
            
        self::$classes[$class] = $path;
    }
    
    /**
     * クラスの別名を作成します。
     * @param mixed $original オリジナルのクラス名。
     * 配列が渡された場合、インデックスを$original、値を$aliasとして繰り返し実行します。
     * @param string $alias クラスの別名
     */
    public static function classAlias($original, $alias = null)
    {
        // このメソッド内では、別名クラスの生成は行いません。
        // すべての別名クラスを生成してしまうと、そのクラスが利用されなかった場合に
        // クラスの生成コストが無駄になってしまうためです。
        // そのため、該当のクラスが参照された時に、オートローダ内で生成します。
        
        // $originalが配列の時
        if (is_array($original)) {
            foreach ($original as $o => $a) {
                self::$aliases[$a] = $o;
            }
            return;
        }
        
        self::$aliases[$alias] = $original;
    }
    
    /**
     * オートローダをPHPのオートローダスタックへ追加します。
     */
    public static function regist()
    {
        // 第三引数は PHP 5.3.0以上で有効
        spl_autoload_register(array('Roose_Autoloader', 'load'), true);//, true);
    }
    
    /**
     * クラスの読み込みを行います。
     * クラス名のアンダースコアはDIRECTORY_SEPERATORに置き換えられます。
     * @param string $class
     */
    public static function load($class)
    {
        
        // 要求されたクラスがクラスの別名として登録されていれば
        // 別名クラスを生成
        if (isset(self::$aliases[$class])) {
            $original = self::$aliases[$class];
            eval('class ' . $class . ' extends ' . $original . ' {}');
            return;
        }
        
        // クラスファイルへのパスが指定されていれば
        // そのパスから読み込み
        if (isset(self::$classes[$class]))
        {
            include self::$classes[$class];
            return;
        }
        
        // "!="は意図的にこうしてます
        foreach (self::$loadPath as $path) {
            $path .= self::DS;
            
            // クラス名を小文字に変換
            $clazz = strtolower($class);
            
            // クラス名のアンダースコアを'/'に置き換え
            $path .= str_replace('_', self::DS, $clazz);

            if (@ include($path . '.php') !== false and class_exists($class)) {
                // ファイルとクラスが読み込まれたら探索を止める
                break;
            }
        }
    
    }
}