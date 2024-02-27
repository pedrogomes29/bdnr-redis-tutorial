<?php

require __DIR__ . '/vendor/autoload.php';

Predis\Autoloader::register();

try {
    // Connect to the localhost Redis server.
    $url = isset($_POST['url']) ? $_POST['url'] : null;
    $tags = isset($_POST['tags']) ? explode(' ', $_POST['tags']) : array();

    $redis = new Predis\Client();

    $next_bookmark_id = $redis->incr('next_bookmark_id');
    $redis->zadd('bookmarks', $next_bookmark_id, $url);
    $redis->hset('bookmark:' . $next_bookmark_id, "url", $url);
    foreach ($tags as $tag) {
        $redis->sadd('tag:' . $tag, $next_bookmark_id);
        $redis->sadd('bookmark:' . $next_bookmark_id . ':tags', $tag);
    }
    header("Location: /index.php");
    die();
} catch (Exception $e) {
    print $e->getMessage();
}

?>
