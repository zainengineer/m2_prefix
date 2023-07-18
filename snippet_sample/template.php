<?php
$initialClasses = get_declared_classes();
/**
 * @var $magentoInc MagentoInc
 * @see \MagentoInc::setAdminHtml()
 */

$magentoInc->setAdminHtml();

class ClassName
{
    public function __construct()
    {
    }

    public function test()
    {
        return 1;
    }
}

\ZActionDetect::showOutput(end($initialClasses), $magentoInc);
