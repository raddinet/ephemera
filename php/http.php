<?php
class http {
    private static $accept = array ();
    private static $charset = array ();
    private static $language = array ();

    public static
    function initialize () {
        if (isset ($_SERVER ['HTTP_ACCEPT']))
            http::$accept = http::prepare ($_SERVER ['HTTP_ACCEPT']);
        if (isset ($_SERVER ['HTTP_ACCEPT_CHARSET']))
            http::$charset = http::prepare ($_SERVER ['HTTP_ACCEPT_CHARSET']);
        if (isset ($_SERVER ['HTTP_ACCEPT_LANGUAGE']))
            http::$language = http::prepare ($_SERVER ['HTTP_ACCEPT_LANGUAGE']);
        return;
    }
    
    private static
    function prepare ($string) {
        $result = array ();
        $list = explode (',', $string);
        
        while (list ($k, $v) = each ($list)) {
            $q = 1.0;
            $item = explode (';', trim ($v));
            
            while (list ($kk, $vv) = each ($item)) {
                $qualifier = explode ('=', trim ($vv));
                if ($qualifier[0] === 'q')
                    $q = floatval ($qualifier[1]);
            };
            
            $result [trim ($item [0])] = $q;
        };
        
        arsort ($result, SORT_NUMERIC);
        return $result;
    }

    public static
    function accept ($mime)
        { return array_key_exists ($mime, http::$accept); }
    public static
    function charset ($mime)
        { return array_key_exists ($mime, http::$charset); }
    public static
    function language ($mime)
        { return array_key_exists ($mime, http::$language); }
    
    public static
    function preview () {
        return isset ($_SERVER['HTTP_X_PURPOSE'])
                   && $_SERVER['HTTP_X_PURPOSE'] == 'preview';
    }

    public static
    function debug () {
        echo "HTTP Accept: ";
        while (list ($k, $v) = each (http::$accept)) { echo "$k : $v; "; }
        echo "<br />HTTP Charset: ";
        while (list ($k, $v) = each (http::$charset)) { echo "$k : $v; "; }
        echo "<br />HTTP Language: ";
        while (list ($k, $v) = each (http::$language)) { echo "$k : $v; "; }
        echo "<br />";
    }
};

http::initialize ();
?>
