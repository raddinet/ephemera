<?php
class xhtml {
    private static $supported = false;
    
    public  static $location;
    public  static $home;
    public  static $baseref;
    
    public static
    function initialize () {
        xhtml::$supported = http::accept ('application/xhtml+xml');
        
        xhtml::$location = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        xhtml::$home = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
        xhtml::$home = substr (xhtml::$home, 0, strrpos (xhtml::$home, '/') + 1); 

        xhtml::$baseref = '';
        $n = substr_count ($_SERVER ['REQUEST_URI'], '/')
           - substr_count ($_SERVER ['SCRIPT_NAME'], '/');
        
        for ($i = 0; $i < $n; ++$i)
            xhtml::$baseref .= '../';
        
        return;
    }
    
    public static
    function supported () {
        return xhtml::$supported;
    }
    
    public static
    function content_type () {
        if (xhtml::$supported)
            return 'application/xhtml+xml';
        else
            return 'text/html';
    }

    public static
    function png () {
        return xhtml::$supported
            || http::accept ('image/png')
            || strstr ($_SERVER ['HTTP_USER_AGENT'], 'MSIE 6.0') !== false
            || strstr ($_SERVER ['HTTP_USER_AGENT'], 'MSIE 7.0') !== false
            || strstr ($_SERVER ['HTTP_USER_AGENT'], 'MSIE 8.0') !== false;
    }
    
    public static
    function escape ($string) {
        return htmlspecialchars ($string, ENT_COMPAT, 'UTF-8');
    }
    
    public static
    function out ($string) {
        echo xhtml::escape ($string);
        return;
    }
};

xhtml::initialize ();
?>
