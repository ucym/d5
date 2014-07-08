<?php
/**
 * データベースコネクションラッパークラス
 * 
 * @todo コメント書く
 * @todo 実装チェック
 * @todo MySQL関数依存からの脱却
 * 
 * @package Roose\DB
 * @author うちやま
 * @since PHP 5.2.17
 * @version 1.0.0
 */
class Roose_DB_Connection
{
    /**
     * コネクション名
     */
    private $_con_name = null;
    
    /**
     * データベースコネクション
     */
    private $_con = null;
    
    /**
     * @param string $host ホスト名
     * @param string $user ユーザー名
     * @param string|null $password パスワード
     * @param boolean|null $newConnection (optional) 新規コネクションを生成するか
     */
    public function __construct($host, $user, $password = null, $newConnection = false)
    {
        $this->_con =
            @mysql_connect($host, $user, $password, $newConnection)
                or die('Connection failed. (' . mysql_error() . ')');
    }
    
    /**
     * @ignore
     */ 
    public function __destruct()
    {
        $this->disconnect();
    }
    
    /**
     * このコネクションを切断します。
     */
    public function disconnect()
    {
        $this->_con_name !== null
            and DB::_disconnected($this->_con_name);
        
        $this->_con !== null
            and mysql_close($this->_con);
    }
    
    /**
     * 使用するデータベースを指定します。
     *
     * @param string $db_name 使用するデータベース名
     */
    public function useDb($dbname)
    {
        return mysql_select_db($dbname, $this->_con);
//            or die('Select db failed. (' . mysql_error() . ')');
    }
    
    /**
     * クエリーを実行します。
     *
     * @todo 動作確認
     * @param string $sql クエリ。"?"、":name"を埋め込み、パラメータを後から指定することが可能です。
     * @param array|null $params クエリに埋め込むパラメータ
     * @return Roose_DB_Resultset|bool
     */
    public function query($sql, $params = null)
    {
        if (is_array($params)) {
            //-- パラメータが設定されていれば埋め込む
            foreach ($params as $key => $value) {
                
                if (is_int($key)) {
                    $ph_pos = strpos($sql, '?');
                    
                    if ($ph_pos !== false) {
                        $value = sprintf('\'%s\'', mysql_real_escape_string($value, $this->_con));
                        $sql = substr_replace($sql, $value, $ph_pos, 1);
                    }
                    
                    continue;
                }
                
                if (is_string($key)) {
                    $ph_pos = strpos($sql, $key);
                    
                    if ($ph_pos !== false) {
                        $value = sprintf('\'%s\'', mysql_real_escape_string($value, $this->_con));
                        $sql = str_replace($key, $value, $sql);
                    }
                    
                    continue;
                }
            }
        }
        
        $result = mysql_query($sql, $this->_con);
        
        if (is_bool($result)) {
            return $result;
        } else {
            return new Roose_DB_Resultset($result);
        }
    }
    
    /**
     * 最近発生したエラーの内容を取得します。
     * @param string|null $connection 接続名。指定されない場合、defaultコネクションを利用します。
     * @return string
     */
    public function error()
    {
        return mysql_error($this->_con);
    }
}