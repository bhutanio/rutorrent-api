<?php

use Bhutanio\RuTorrent\RuTorrent;

require __DIR__ . '/../vendor/autoload.php';

$client = new RuTorrent('http://192.168.0.1:8080', ['username', 'password']);

$data = $client->addTorrent('/path/file.torrent');
dump($data);

dump($client->torrentList());