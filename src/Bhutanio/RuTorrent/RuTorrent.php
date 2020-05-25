<?php

namespace Bhutanio\RuTorrent;

use Bhutanio\RuTorrent\Models\Torrent;
use Exception;
use Illuminate\Http\Client;

class RuTorrent
{
    /**
     * @var \Illuminate\Http\Client\PendingRequest
     */
    protected $client;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string|array
     */
    private $auth;

    public function __construct($url, $auth)
    {
        $this->url = $url;
        $this->auth = $auth;

        $this->client = $this->initClient();
    }

    public function startTorrent($info_hash)
    {
        return $this->actionTorrent($info_hash, 'start');
    }

    public function stopTorrent($info_hash)
    {
        return $this->actionTorrent($info_hash, 'stop');
    }

    public function deleteTorrent($info_hash)
    {
        return $this->actionTorrent($info_hash, 'remove');
    }

    public function actionTorrent($info_hash, $action)
    {
        if (!in_array($action, ['recheck', 'start', 'stop', 'pause', 'unpause', 'remove', 'trk', 'trkstate', 'trkall', 'ttl', 'stg'])) {
            throw new Exception('Unknown Action');
        }

        $info_hash = strtoupper($info_hash);
        $response = $this->client->asForm()->post($this->url . '/plugins/httprpc/action.php', [
            'mode' => $action,
            'hash' => $info_hash,
        ]);

        return $this->parseResponse($response->body());
    }

    public function torrentList()
    {
        $response = $this->client->asForm()->post($this->url . '/plugins/httprpc/action.php', [
            'mode' => 'list',
        ]);

        return $this->torrentModels($response->json());
    }

    public function addTorrent($file, $path = '')
    {
        $path_append = $path ? '&dir_edit=' . urlencode($path) : '';

        $response = $this->client
            ->attach('torrent_file', fopen($file, 'r'))
            ->post($this->url . '/php/addtorrent.php?json=1' . $path_append);

        return $this->parseResponse($response->body());
    }

    private function initClient()
    {
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
        ];

        if (!empty($this->auth) && is_string($this->auth)) {
            $headers['Authorization'] = $this->auth;
        }

        $client = (new Client\PendingRequest())->withHeaders($headers);

        if (is_array($this->auth) && count($this->auth) == 2) {
            $client->withBasicAuth($this->auth[0], $this->auth[1]);
        }

        $this->testConnection($client);

        return $client;
    }

    private function parseResponse($response)
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response;
        }

        if (count($data) == 34) {
            return $this->torrentModels(['t' => $data]);
        }

        return $data;
    }

    private function torrentModels($response)
    {
        $collection = collect();
        if (!empty($response['t'])) {
            foreach ($response['t'] as $info_hash => $data) {
                $collection->add(new Torrent($info_hash, $data));
            }
        }

        return $collection;
    }

    private function testConnection(Client\PendingRequest $client)
    {
        $response = $client->get($this->url . '/plugins/cpuload/action.php?_=' . time());
        if ($response->status() >= 400) {
            return $response->throw();
        }
    }
}