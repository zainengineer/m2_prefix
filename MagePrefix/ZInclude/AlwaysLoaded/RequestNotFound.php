<?php

namespace MagePrefix\ZInclude\AlwaysLoaded;

use MagePrefix\ZInclude\Traits\SingletonTrait;

class RequestNotFound
{
    use SingletonTrait;
    private function sendResourceNotFound()
    {
        $vRequest = $_SERVER['REQUEST_URI'];
        header("HTTP/1.0 404 Not Found");
        echo "PHP continues $vRequest .\n";
        die();
    }

    private function ignoreThisRequest()
    {
        $vRequest = $_SERVER['REQUEST_URI'] ?? '';
        if (in_array($vRequest, ['/favicon.ico'])) {
            return true;
        }
        $vExtension = strtolower(pathinfo($vRequest, PATHINFO_EXTENSION));
        if (in_array($vExtension, [
            'jpeg', 'jpg', 'gif', 'png', 'pdf',
        ])) {
            return true;
        }
    }

    public function process()
    {
        if ($this->ignoreThisRequest()) {
            $this->sendResourceNotFound();
        }
    }
}
