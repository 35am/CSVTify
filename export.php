<?php

define("JSON_TRACKS", "tracks");
define("JSON_TRACK_ID", "id");
define("JSON_LINKED_FROM", "linked_from");
define("JSON_LINKED_FROM_ID", "id");
define("JSON_TRACK_NAME", "name");
define("JSON_TRACK_ARTISTS", "artists");
define("JSON_TRACK_ARTIST_NAME", "name");
define("JSON_TRACK_ALBUM", "album");
define("JSON_TRACK_ALBUM_NAME", "name");
define("PARAM_SPOTIFY_URL", "SPOTIFY_URL");

// Check parameter
if (isset($_POST['songs']) && strlen($_POST['songs']) >= 1) {

    // Get songs infos
    $songsCSV = getSongsData($_POST['songs']);

    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Name', 'Artist', 'Album', 'Spotify_URL'));
    foreach ($songsCSV as $song) {
        fputcsv($output, $song);
    }
} else {
    // Wrong parameters
    echo "A wrong decision is better than indecision.";
}

/**
 * Return an array of the songs with data from Spotify
 */
function getSongsData($paramSongs) {

    $retour = array(array());

    $nb_song = 0;

    $inSongs = trim($paramSongs);
    $songs = array_filter(explode("\n", $inSongs), 'trim');

    foreach ($songs as $song) {
        // Reading songs line by line
        // Check correct URL
        if (preg_match('#^https?://open.spotify.com/track/#i', $song) === 1) {

            // Getting JSON for song
            $json = json_decode(file_get_contents('https://api.spotify.com/v1/tracks/?ids=' . preg_replace('#^https?://open.spotify.com/track/#i', '', preg_replace('/\s+/', '', $song))), true);

            // Check result
            if (isset($json[JSON_TRACKS])) {
                $track = $json[JSON_TRACKS][0];
                // Check result
                if (isset($track[JSON_TRACK_NAME]) && isset($track[JSON_TRACK_ARTISTS]) && isset($track[JSON_TRACK_ALBUM]) && isset($track[JSON_TRACK_ALBUM][JSON_TRACK_ALBUM_NAME])) {
                    // Add song to final result
                    $retour[$nb_song][JSON_TRACK_NAME] = $track[JSON_TRACK_NAME];
                    $retour[$nb_song][JSON_TRACK_ARTISTS] = implode(', ', array_map(function ($entry) {
                                        return $entry[JSON_TRACK_ARTIST_NAME];
                                    }, $track[JSON_TRACK_ARTISTS]));
                    $retour[$nb_song][JSON_TRACK_ALBUM] = $track[JSON_TRACK_ALBUM][JSON_TRACK_ALBUM_NAME];
                    $retour[$nb_song][PARAM_SPOTIFY_URL] = preg_replace('/\s+/', '', $song);
                    $nb_song++;
                }
            }
        }
    }

    return $retour;
}

?>
