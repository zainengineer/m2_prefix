<?php
/**
 * insert code as following
 * TODO: create a more easy to plugin code
 * @see \Magento\MediaStorage\Service\ImageResize::resizeFromImageName()
 * after lines
 * if (!$this->mediaDirectory->isFile($originalImagePath)) {
 * require_once "/Volumes/..full_path/pub/zain_custom/media_include.php";
 * \MagePrefix\ZInclude\ZMediaInc::addImageToList($originalImageName,$originalImagePath);
 *
 * @see \Magento\Framework\View\Asset\File::getSourceFile()
 *
 *
 *
 * if (null === $this->resolvedFile) {
 * try {
 * $this->resolvedFile = $this->source->getFile($this);
 * }catch (\Exception $e){
 * require_once "/Volumes/fullPath../pub/zain_custom/media_include.php";
 * \MagePrefix\ZInclude\ZMediaInc::downloadStatic($this->getPath(),$this->getUrl());
 * try{
 * $this->resolvedFile = $this->source->getFile($this);
 * }catch (\Exception $e){
 * throw new File\NotFoundException("Unable to resolve the source file for '{$this->getPath()}'");
 * }
 * }
 *
 * if (false === $this->resolvedFile) {
 * require_once "/Volumes/fullPath../pub/zain_custom/media_include.php";
 * \MagePrefix\ZInclude\ZMediaInc::downloadStatic($this->getPath(),$this->getUrl());
 * throw new File\NotFoundException("Unable to resolve the source file for '{$this->getPath()}'");
 * }
 * }
 */

namespace MagePrefix\ZInclude;
if (!defined('PHP_VERSION_ID') || !(PHP_VERSION_ID === 70002 || PHP_VERSION_ID === 70004 || PHP_VERSION_ID >= 70006)) {
    if (PHP_SAPI == 'cli') {
        echo 'Magento supports 7.0.2, 7.0.4, and 7.0.6 or later. ' .
            'Please read http://devdocs.magento.com/guides/v2.2/install-gde/system-requirements.html';
    } else {
        echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <p>Magento supports PHP 7.0.2, 7.0.4, and 7.0.6 or later. Please read
    <a target="_blank" href="http://devdocs.magento.com/guides/v2.2/install-gde/system-requirements.html">
    Magento System Requirements</a>.
</div>
HTML;
    }
    exit(1);
}

class ZMediaInc
{
    public static function getBaseRemoteUrl()
    {
        static $basicUrlContent;
        if (!isset($basicUrlContent)){
            $basicUrlContent = file_get_contents(__DIR__ .'/base_production_url.txt');
            if (!$basicUrlContent){
                xdebug_break();
                throw new \Exception("not set basic Url Content");
            }
        }
        $baseUrl = $basicUrlContent;
//        $baseUrl = 'https://wwww.example.com.au/';
        $baseUrl = rtrim($baseUrl, '/');
        return $baseUrl;
    }

    static function aria2C(string $strImage,
                           string $originalImagePath)
    {
        static $existingList;
        $filePath = __DIR__ . '/write/image_list.txt';
        if (is_null($existingList)
            && file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $existingList = explode("\n", $content);
            $existingList = array_filter($existingList);
            $existingList = array_combine($existingList, $existingList);
        }
        if (!isset($existingList[$strImage])) {
            $existingList[$strImage] = $strImage;
            file_put_contents($filePath, $strImage . "\n", FILE_APPEND);
            ///.../pub/zain_custom/write
            $rootPath = dirname(__DIR__);
            $baseUrl = self::getBaseRemoteUrl();
//            $imageUrl = str_replace( $rootPath,$baseUrl,$strImage);
            $imageUrl = "$baseUrl/$strImage";
//            $out = "out=$rootPath/$strImage";
            $relativePath = str_replace($rootPath, "pub", $originalImagePath);
//            $out = "out=$relativePath";
            $out = "out=pub/$strImage";
            //pub/media/wysiwyg/some_image_pathD.jpg
            file_put_contents(__DIR__ . '/write/url_list.txt', "$imageUrl\n  $out \n", FILE_APPEND);
        }
    }
    public static function guessPathAndDownloadStatic()
    {
        $url = $_SERVER['REQUEST_URI'];
        $relativePath = explode('/', $url);
    }

    /**
     * @param string $strImage
     * @param string $originalImagePath
     * @return void
     * @throws \Exception
     *
     * @see \Magento\Framework\View\Asset\File::getSourceFile()
     *
     */
    public static function downloadStatic(string $staticPath, string $url)
    {
//xdebug_break();
        $rootPath = dirname(__DIR__);
        $staticPath = ltrim($staticPath, '/');
        if ((strpos($staticPath, 'static/')) !== 0) {
            $staticPath = "static/$staticPath";
        }
        $targetPath = "$rootPath/$staticPath";
        if (file_exists($targetPath)) {
            throw new \Exception("$targetPath already exists");
        }

        $remoteBaseUrl = self::getBaseRemoteUrl();
        $urlPath = parse_url($url, PHP_URL_PATH);
        $remoteFullUrl = "{$remoteBaseUrl}$urlPath";
        $contents = file_get_contents($remoteFullUrl);
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        file_put_contents($targetPath, $contents);
    }
//if (!$this->mediaDirectory->isFile($originalImagePath)) {
//require_once "/Volumes/....fullpath./pub/zain_custom/media_include.php";
//\MagePrefix\ZInclude\ZMediaInc::addImageToList($originalImageName,$originalImagePath);
//throw new NotFoundException(__('Cannot resize image "%1" - original image not found', $originalImagePath));
//}
    public static function addImageToList(
        string $strImage,
        string $originalImagePath)
    {
        xdebug_break();
        $strImage = ltrim($_SERVER['REQUEST_URI'], '/');
        $cached = false;
        if (strpos($strImage, 'media/catalog/product/cache/') === 0) {
            $cached = true;
            $parts = explode('/', $strImage);
            unset($parts[3]);
            unset($parts[4]);
            $strImage = implode('/', $parts);
        }
        $relativePath = ltrim($strImage, '/');
        $baseUrl = self::getBaseRemoteUrl();
        $rootPath = dirname(__DIR__);
        $imageUrl = "$baseUrl/$strImage";
        $absPath = "$rootPath/$relativePath";
        if (file_exists($absPath)) {
            if ($cached) {
                return;
            }
            throw new \Exception("unexpected $absPath already exists");
        }
        $remoteContent = file_get_contents($imageUrl);
        $dirName = dirname($absPath);
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }
        file_put_contents($absPath, $remoteContent);
    }
}
