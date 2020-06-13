<?php

namespace Bhutanio\RuTorrent;

use Bhutanio\RuTorrent\Models\Scrape;
use Bhutanio\RuTorrent\Models\Torrent;
use Exception;
use Illuminate\Http\Client;
use Illuminate\Support\Arr;

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

    /**
     * RuTorrent constructor.
     *
     * @param $url
     * @param $auth
     */
    public function __construct($url, $auth)
    {
        $this->url = rtrim($url, '/');
        $this->auth = $auth;

        $this->client = $this->initClient();
    }

    public function getConfig()
    {
        return $this->parseConfig($this->postAction('stg'));
    }

    public function getCpuLoad()
    {
        return $this->getAction('/plugins/cpuload/action.php?_=' . time())['load'];
    }

    public function getDiskSpace()
    {
        return $this->getAction('/plugins/diskspace/action.php?_=' . time());
    }

    public function getTorrents()
    {
        $response = $this->postAction('list');

        return $this->torrentModels($response);
    }

    public function getScrapeAll()
    {
        return $this->parseScrapeAll($this->postAction('trkall'));
    }

    public function startTorrent($info_hash)
    {
        return $this->postAction('start', $info_hash);
    }

    public function stopTorrent($info_hash)
    {
        return $this->postAction('stop', $info_hash);
    }

    public function pauseTorrent($info_hash)
    {
        return $this->postAction('pause', $info_hash);
    }

    public function unpauseTorrent($info_hash)
    {
        return $this->postAction('unpause', $info_hash);
    }

    public function deleteTorrent($info_hash)
    {
        return $this->postAction('remove', $info_hash);
    }

    public function getAction($url)
    {
        $response = $this->client->get($this->url . $url);
        if ($response->status() >= 400) {
            return $response->throw();
        }

        return $response->json();
    }

    public function postAction($action, $info_hash = null, $url = '/plugins/httprpc/action.php')
    {
        if (!in_array($action, ['list', 'recheck', 'start', 'stop', 'pause', 'unpause', 'remove', 'trk', 'trkstate', 'trkall', 'ttl', 'stg'])) {
            throw new Exception('Unknown Action');
        }

        $parameters['mode'] = $action;

        if ($info_hash) {
            $parameters['hash'] = strtoupper($info_hash);
        }

        $response = $this->client->asForm()->post($this->url . $url, $parameters);
        if ($response->status() >= 400) {
            return $response->throw();
        }

        return $this->parseResponse($response->body());
    }

    public function addTorrent($file, $path = '', $file_name = null)
    {
        $path_append = $path ? '&dir_edit=' . urlencode($path) : '';

        $response = $this->client
            ->attach('torrent_file', fopen($file, 'r'), $file_name)
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

        $client = (new Client\PendingRequest())->withHeaders($headers)->withOptions(['verify' => false]);

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

//        if (is_array($data) && count($data) == 34) {
//            return $this->torrentModels(['t' => $data]);
//        }

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

    private function parseConfig($data)
    {
        $keys = [
            "dht",
            "get_check_hash",
            "get_bind",
            "get_dht_port",
            "get_directory",
            "get_download_rate",
            "get_hash_interval",
            "get_hash_max_tries",
            "get_hash_read_ahead",
            "get_http_cacert",
            "get_http_capath",
            "get_http_proxy",
            "get_ip",
            "get_max_downloads_div",
            "get_max_downloads_global",
            "get_max_file_size",
            "get_max_memory_usage",
            "get_max_open_files",
            "get_max_open_http",
            "get_max_peers",
            "get_max_peers_seed",
            "get_max_uploads",
            "get_max_uploads_global",
            "get_min_peers_seed",
            "get_min_peers",
            "get_peer_exchange",
            "get_port_open",
            "get_upload_rate",
            "get_port_random",
            "get_port_range",
            "get_preload_min_size",
            "get_preload_required_rate",
            "get_preload_type",
            "get_proxy_address",
            "get_receive_buffer_size",
            "get_safe_sync",
            "get_scgi_dont_route",
            "get_send_buffer_size",
            "get_session",
            "get_session_lock",
            "get_session_on_completion",
            "get_split_file_size",
            "get_split_suffix",
            "get_timeout_safe_sync",
            "get_timeout_sync",
            "get_tracker_numwant",
            "get_use_udp_trackers",
            "get_max_uploads_div",
            "get_max_open_sockets",
        ];

        return array_combine($keys, $data);
    }

    private function parseScrapeAll($response)
    {
        $collection = collect();
        if (!empty($response)) {
            foreach ($response as $info_hash => $data) {
                $collection->add(new Scrape($info_hash, Arr::first($data)));
            }
        }

        return $collection;
    }
}