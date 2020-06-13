<?php

namespace Bhutanio\RuTorrent\Models;

use Carbon\Carbon;

class Scrape
{
    /**
     * @var string
     */
    public $info_hash, $url, $group;

    /**
     * @var int
     */
    public $seed, $peer, $downloaded;

    /**
     * @var bool
     */
    public $is_enabled;

    public function __construct($info_hash, $data)
    {
        $raw = array_combine($this->keys(), $data);

        $this->info_hash = $info_hash;

        $this->url = $raw['get_url'];
        $this->group = $raw['get_group'];
        $this->seed = (int)$raw['get_scrape_complete'];
        $this->peer = (int)$raw['get_scrape_incomplete'];
        $this->downloaded = (int)$raw['get_scrape_downloaded'];
        $this->is_enabled = (bool)$raw['is_enabled'];
    }

    public function keys()
    {
        return [
            0 => "get_url",
            1 => "get_type",
            2 => "is_enabled",
            3 => "get_group",
            4 => "get_scrape_complete",
            5 => "get_scrape_incomplete",
            6 => "get_scrape_downloaded",
        ];
    }
}