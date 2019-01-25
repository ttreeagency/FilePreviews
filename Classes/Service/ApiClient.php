<?php
namespace Ttree\FilePreviews\Service;

/*
 * This file is part of the Ttree.FilePreviews package.
 *
 * (c) ttree ltd - www.ttree.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Media\Exception;

/**
 * File Previews Service
 *
 * @api
 */
class ApiClient
{
    const VERSION = '1.0.1';
    const API_URL = 'https://api.filepreviews.io/v2/';
    const API_KEY_ENV_NAME = 'FILEPREVIEWS_API_KEY';
    const API_SECRET_ENV_NAME = 'FILEPREVIEWS_API_SECRET';

    /**
     * @var Client
     */
    protected $client;

    /**
     * FilePreviewsService constructor
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'api_key' => getenv(static::API_KEY_ENV_NAME),
            'api_secret' => getenv(static::API_SECRET_ENV_NAME),
            'api_url' => static::API_URL,
            'version' => static::VERSION
        ], $config);

        if (!$config['api_key']) {
            throw new \Exception('Required "api_key" key not supplied.');
        }

        if (!$config['api_secret']) {
            throw new \Exception('Required "api_secret" key not supplied.');
        }

        $this->client = new Client($config);
    }

    /**
     * Call filepreviews.io API to generate a new preview
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function generate($url, array $params = [])
    {
        $params = array_merge([
            'url' => $url,
            'metadata' => []
        ], $params);

        $metadata = array_unique($params['metadata']);

        if (empty($metadata)) {
            unset($params['metadata']);
        }

        if (isset($params['size'])) {
            $size = $params['size'];
            unset($params['size']);

            $geometry = '';
            $size = array_merge([
                'height' => null,
                'width' => null
            ], $size);

            if ($size['width'] !== null) {
                $geometry = "{$size['width']}";
            }

            if ($size['height'] !== null) {
                $geometry = "{$geometry}x{$size['height']}";
            }

            $params['sizes'] = [$geometry];
        }

        return $this->client->post('previews/', json_encode($params));
    }

    /**
     * Call filepreviews.io API to retrieve details about a preview
     *
     * @param string $preview_id
     * @return mixed
     */
    public function retrieve($preview_id)
    {
        return $this->client->get("previews/$preview_id/");
    }
}
