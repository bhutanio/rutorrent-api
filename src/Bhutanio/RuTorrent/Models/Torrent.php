<?php

namespace Bhutanio\RuTorrent\Models;

use Carbon\Carbon;

class Torrent
{
    /**
     * @var string
     */
    public $info_hash;

    /**
     * @var string
     */
    public $name;

    /**
     * @var \Carbon\Carbon
     */
    public $created_at;

    /**
     * @var string
     */
    public $base_path;

    public function __construct($info_hash, $data)
    {
        $raw = array_combine($this->keys(), $data);

        $this->info_hash = $info_hash;

        $this->name = $raw['get_name'];

        $this->created_at = (new Carbon(intval($raw['get_creation_date'])))->toDateTimeString();

        $this->base_path = $raw['get_base_path'];
    }

    public function keys()
    {
        return [
            0 => "is_open",
            1 => "is_hash_checking",
            2 => "is_hash_checked",
            3 => "get_state",
            4 => "get_name",
            5 => "get_size_bytes",
            6 => "get_completed_chunks",
            7 => "get_size_chunks",
            8 => "get_bytes_done",
            9 => "get_up_total",
            10 => "get_ratio",
            11 => "get_up_rate",
            12 => "get_down_rate",
            13 => "get_chunk_size",
            14 => "get_custom1",
            15 => "get_peers_accounted",
            16 => "get_peers_not_connected",
            17 => "get_peers_connected",
            18 => "get_peers_complete",
            19 => "get_left_bytes",
            20 => "get_priority",
            21 => "get_state_changed",
            22 => "get_skip_total",
            23 => "get_hashing",
            24 => "get_chunks_hashed",
            25 => "get_base_path",
            26 => "get_creation_date",
            27 => "get_tracker_focus",
            28 => "is_active",
            29 => "get_message",
            30 => "get_custom2",
            31 => "get_free_diskspace",
            32 => "is_private",
            33 => "is_multi_file",
        ];
    }
}