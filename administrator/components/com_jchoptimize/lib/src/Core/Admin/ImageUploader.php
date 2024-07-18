<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin;

use _JchOptimizeVendor\GuzzleHttp\Client;
use _JchOptimizeVendor\GuzzleHttp\Exception\GuzzleException;
use _JchOptimizeVendor\GuzzleHttp\Psr7\Response;
use _JchOptimizeVendor\GuzzleHttp\Psr7\Utils;
use _JchOptimizeVendor\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\Psr\Http\Client\ClientInterface;
use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\Exception;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class ImageUploader
{
    protected array $auth = [];
    protected array $files = [];

    /**
     * @var null|(Client&ClientInterface)
     */
    private $http;

    /**
     * @param mixed $http
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Registry $params, $http)
    {
        if (\is_null($http)) {
            throw new Exception\InvalidArgumentException('No http client transporter found', 500);
        }
        $this->http = $http;
        $this->auth = ['auth' => ['dlid' => $params->get('pro_downloadid', ''), 'secret' => $params->get('hidden_api_secret', '')]];
    }

    /**
     * @param ((int[]|string)[]|bool|mixed|string)[] $opts
     *
     * @throws \Exception
     *
     * @psalm-param array{files?: list{0?: string,...}|mixed, lossy?: bool, save_metadata?: bool, resize?: array<array<mixed|string>|string, array{width: int, height?: int}>, resize_mode?: 'auto'|'manual', webp?: mixed, url?: ''|mixed} $opts
     */
    public function upload(array $opts = [])
    {
        if (empty($opts['files'][0])) {
            throw new Exception\InvalidArgumentException('File parameter was not provided', 500);
        }
        $files = [];
        foreach ($opts['files'] as $i => $file) {
            $files[] = ['name' => 'files['.$i.']', 'contents' => Utils::tryFopen($file, 'r'), 'filename' => self::getPostedFileName($file)];
        }
        $this->files = $opts['files'];
        $body = ['name' => 'data', 'contents' => \json_encode(\array_merge($this->auth, $opts))];
        $data = \array_merge($files, [$body]);

        return self::request($data);
    }

    /**
     * @param mixed $file
     *
     * @return false|string
     */
    public static function getMimeType($file)
    {
        return \extension_loaded('fileinfo') ? \mime_content_type($file) : 'image/'.\preg_replace(['#\\.jpg#', '#^.*?\\.(jpeg|png|gif)(?:[?\\#]|$)#i'], ['.jpeg', '\\1'], \strtolower($file));
    }

    /**
     * @psalm-return array<mixed|string>|string
     *
     * @param mixed $file
     *
     * @return (mixed|string)[]|string
     */
    public static function getPostedFileName($file)
    {
        return AdminHelper::contractFileNameLegacy($file);
    }

    /**
     * @param (false|mixed|resource|string)[][] $data
     *
     * @psalm-param list{array{name: 'data', contents: false|string},...} $data
     */
    private function request(array $data)
    {
        \ini_set('upload_max_filesize', '50M');
        \ini_set('post_max_size', '50M');
        \ini_set('max_input_time', '600');
        \ini_set('max_execution_time', '600');

        try {
            /** @var Response $response */
            $response = $this->http->post('https://api2.jch-optimize.net/', [RequestOptions::MULTIPART => $data]);
        } catch (GuzzleException $e) {
            return new \JchOptimize\Core\Admin\Json(new \Exception('Exception trying to access API with message: '.$e->getMessage().' Files: '.\print_r($this->files, \true)));
        }
        if (200 !== $response->getStatusCode()) {
            return new \JchOptimize\Core\Admin\Json(new \Exception('Response returned with status code: '.$response->getStatusCode().' Files: '.\print_r($this->files, \true), 500));
        }
        $body = $response->getBody();
        $body->rewind();
        $contents = \json_decode($body->getContents());
        if (\is_null($contents)) {
            return new \JchOptimize\Core\Admin\Json(new \Exception('Improper formatted response: '.$body->getContents()));
        }

        return $contents;
    }
}
