<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookit</title>
</head>
<body>
    <h1>Bookit</h1>
    <?php
    require __DIR__ . '/vendor/autoload.php';

    // Retrieve tags from URL parameters
    $tags = isset($_GET['tags']) ? explode(',', $_GET['tags']) : array();
    $redis = new Predis\Client();

    function displayBookmarks($redis, $bookmarks)
    {
        foreach ($bookmarks as $bookmark => $id) {
            echo '<a href="' . $bookmark . '">' . $bookmark . '</a><br>';
            $bookmarkTags = $redis->smembers('bookmark:' . $id . ':tags');
            echo '<h3> Tags </h3>';
            foreach ($bookmarkTags as $tag)
                echo '<a href="index.php?tags=' . $tag . '">' . $tag . '</a><br>';

            echo '<br>';
            echo '<br>';
        }
    }


    if ($tags) {
        echo '<h2> Bookmarks with matching tags </h2>';

        $matchingBookmarkIds = $redis->smembers('tag:' . $tags[0]);

        // Intersect the sets of remaining keys
        for ($i = 1; $i < count($tags); $i++) {
            $matchingBookmarkIds = $redis->sinter($matchingBookmarkIds, 'tag:' . $tags[$i]);
        }

        $bookmarkUrlToIdMap = [];
        foreach ($matchingBookmarkIds as $matchingBookmarkId) {
            $matchingBookmarkUrl = $redis->hget('bookmark:' . $matchingBookmarkId, "url");
            $bookmarkUrlToIdMap[$matchingBookmarkUrl] = $matchingBookmarkId;
        }

        displayBookmarks($redis, $bookmarkUrlToIdMap);

    } else {
        echo '<h2> Latest Bookmarks </h2>';
        $bookmarks = $redis->zrange("bookmarks", -15, -1, "withscores");
        displayBookmarks($redis, $bookmarks);
    }
    ?>
    <a href="add.html">Add Bookmark</a>
    <br>
    <a href="index.php">Home</a>

</body>
</html>
