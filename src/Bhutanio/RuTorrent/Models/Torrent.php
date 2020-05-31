<?php

namespace Bhutanio\RuTorrent\Models;

use Carbon\Carbon;

class Torrent
{
    /**
     * @var string
     */
    public $info_hash, $created_at, $name, $path;

    /**
     * @var int
     */
    public $size, $downloaded, $uploaded, $left;

    /**
     * @var float
     */
    public $ratio;

    /**
     * @var bool
     */
    public $is_active, $is_complete, $is_folder, $is_private;

    public function __construct($info_hash, $data)
    {
        $raw = array_combine($this->keys(), $data);

        $this->info_hash = $info_hash;

        $this->name = $raw['get_name'];

        $this->created_at = (new Carbon(intval($raw['get_creation_date'])))->toDateTimeString();

        $this->path = $raw['get_base_path'];

        $this->size = (int)$raw['get_size_bytes'];

        $this->downloaded = (int)$raw['get_bytes_done'];

        $this->uploaded = (int)$raw['get_up_total'];

        $this->left = (int)$raw['get_left_bytes'];

        $this->ratio = number_format(($this->uploaded ? $this->downloaded / $this->uploaded : 0), 2);

        $this->is_active = (bool)$raw['is_active'];

        $this->is_complete = $this->left == 0;

        $this->is_folder = (bool)$raw['is_multi_file'];

        $this->is_private = (bool)$raw['is_private'];
    }

    public function keys()
    {
        return [
            0 => "is_open",
            1 => "is_hash_checking",
            2 => "is_hash_checked",
            3 => "get_state", // 1 = active 0 = inactive
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

    private function getStatus($data)
    {

    }
}