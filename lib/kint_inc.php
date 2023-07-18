<?php
if (version_compare(phpversion(), '8', '>=')) {
    $rootPath = dirname(__DIR__, 3);
    //need to do it because having strange phar rapper errors
    require_once $rootPath . "/vendor/autoload.php";
    if (!class_exists('\Kint')) {
        require_once __DIR__ . '/kint.phar';
    }
    \Kint::$depth_limit = 5;

} else {
    require_once __DIR__ . '/kint.php';
    \Kint::$max_depth = 5;
}

class KintHelper
{
    static function isM24() : bool
    {
        return version_compare(phpversion(), '8', '>=');
    }
    static function magentoInc() : \MagentoInc
    {
        return $GLOBALS['magentoInc'];
    }
    static function setMaxDepth(int $limit)
    {
        if (empty(\Kint::$max_depth)) {
            \Kint::$depth_limit = $limit;
        } else {
            \Kint::$max_depth = 5;
        }
    }
}

\Kint::$file_link_format = 'http://localhost:8091/?message=%f:%l';
/**x
 *
 *
 * to put it raw in any place
 *
 * require_once '/var/www/magento/pub/zain_custom/lib/kint_inc.php';
 * \Kint::$max_depth =3;
 * !d($saveOrder);
 * die;
 */

//Kint::dump('test');
