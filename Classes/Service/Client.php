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

use Guzzle\Http\Client as GuzzleClient;

/**
 * File Previews Client Service
 *
 * @api
 */
class Client
{

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config = [])
    {
        $this->client = new GuzzleClient($config['api_url']);
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getDefaultHeaders()
    {
        $client_ua = [
            'lang' => 'php',
            'publisher' => 'filepreviews',
            'bindings_version' => $this->config['version'],
            'lang_version' => phpversion(),
            'platform' => PHP_OS,
            'uname' => php_uname(),
        ];
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-FilePreviews-Client-User-Agent' => json_encode($client_ua),
            'User-Agent' => 'FilePreviews/v2 PhpBindings/' . $this->config['version']
        ];
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function get($path)
    {
        $request = $this->client->get($path, $this->getDefaultHeaders());
        $request->setAuth($this->config['api_key'], $this->config['api_secret']);
        $response = $request->send();
        return json_decode($response->getBody(true));
    }

    /**
     * @param string $path
     * @param string $data
     * @return mixed
     */
    public function post($path, $data)
    {
        $request = $this->client->post($path, $this->getDefaultHeaders(), $data);
        $request->setAuth($this->config['api_key'], $this->config['api_secret']);
        $response = $request->send();
        return json_decode($response->getBody(true));
    }

}
