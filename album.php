<?php
namespace PhotosLeaves;
require_once('init.inc.php');

$uri = explode('?', $_SERVER['REQUEST_URI']);
$album_path = urldecode($uri[0]);
if (strpos($album_path, '/album') === 0) {
    $album_path = substr($album_path, 6);
}
$album_path = trim($album_path, '/');

if (empty($album_path)) {
    // List all root albums
    include('templates/header.php');
    echo "<ul>";
    foreach (glob(Config::get('ALBUMS_DIR') . "/*") as $folder) {
        $album_folder = preg_replace('@^' . Config::get('ALBUMS_DIR') . '/@', '', $folder);
        if (file_exists("$folder/album.json")) {
            echo "<li><a href='/album/" . urlencode($album_folder) . "/'>" . he(basename($album_folder)) . "</a></li>";
        }
    }
    echo "</ul>";
} else {
    $json_file = Config::get('ALBUMS_DIR') . "/$album_path/album.json";
    if (!file_exists($json_file)) {

        if (file_exists(Config::get('ALBUMS_DIR') . "/$album_path")) {
            // Photo

            $original_file = Config::get('ALBUMS_DIR') . "/$album_path";
            if (@$_REQUEST['thumb'] == 'y') {
                $thumb_file = substr($original_file, 0, strlen($original_file)-4) . ".thumb." . substr($original_file, -3);
                if (!file_exists($thumb_file)) {
                    resizeImage($original_file, $thumb_file, 256, 256, 90);
                }
                $file = $thumb_file;
            } else {
                $file = $original_file;
            }

            header('Content-type: image/jpeg');
            readfile($file);
            exit(0);
        }

        die("Album not found");
    }

    $album = new Album($json_file);

    include('templates/header.php');

    echo "<h2>" . he($album->name) . "</h2>";

    echo "<ul>";
    chdir(Config::get('ALBUMS_DIR') . "/$album_path");
    foreach (glob("*") as $file) {
        if ($file == 'album.json') continue;
        if (string_contains($file, '.thumb.')) continue;
        if (file_exists("$file/album.json")) {
            $album_folder = preg_replace('@^' . Config::get('ALBUMS_DIR') . "/@", '', Config::get('ALBUMS_DIR') . "/$album_path/$file");
            echo "<li><a href='/album/" . urlencode($album_folder) . "/'>" . he(basename($album_folder)) . "</a></li>";
        } else {
            echo "<li><a href='" . urlencode(basename($file)) . "'><img class='thumb' src='" . urlencode(basename($file)) . "?thumb=y' /></a></li>";
        }
    }
    echo "</ul>";
}
?>
</body>
</html>
