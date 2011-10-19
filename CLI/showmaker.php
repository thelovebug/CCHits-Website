<pre>
<?php
/**
 * TODO: Write DOCBLOCK for ShowMaker.php
 */
require_once 'config_default.php'; // Adjust config_local to set your own local variables.
require_once 'library.php';

$data = curl_get(Configuration::getAPI() . '/runshows/20110731', 0);
if ($data != false and isset($data[0]) and strlen($data[0]) > 0) {
    $json_data = mkarray(json_decode($data[0]));

    $pre_sable = '<?xml version="1.0"?>
<!DOCTYPE SABLE PUBLIC "-//SABLE//DTD SABLE speech mark up//EN" "Sable.v0_2.dtd" []>
<SABLE>
<SPEAKER NAME="cmu_us_clb_arctic_clunits">';
    $post_sable = '</SPEAKER>
</SABLE>';
    $track_nsfw = ' a track which may not be considered work or family safe <BREAK LEVEL=\"MEDIUM\" /> It is';
    $show_nsfw = ' the show for to day contains tracks which may not be considered work or family safe <BREAK LEVEL="MEDIUM" />';

    if (isset($json_data['daily_show'])) {
        $show_data = $json_data['daily_show'];
        $intro = "$pre_sable\r\nHello and welcome to {$show_data['strShowNameSpoken']} <BREAK LEVEL=\"MEDIUM\" /> To dayz show features";
        if ($show_data['isNSFW'] != 0) {
            $intro .= $track_nsfw;
        }
        $intro .= " {$show_data['arrTracks'][1]['strTrackNameSounds']} by {$show_data['arrTracks'][1]['strArtistNameSounds']} <BREAK LEVEL=\"MEDIUM\" />  If you like to dayz daily exposure track <BREAK LEVEL=\"SMALL\" /> please vote for it at <BREAK LEVEL=\"SMALL\" /> {$show_data['strShowUrlSpoken']}\r\n$post_sable";
        $out = fopen(Configuration::getWorkingDir() . '/intro.sable', 'w');
        fwrite($out, $intro);
        fclose($out);
        $outro = "$pre_sable\r\nThat was <BREAK LEVEL=\"SMALL\" /> {$show_data['arrTracks'][1]['strTrackNameSounds']} <BREAK LEVEL=\"SMALL\" /> by <BREAK LEVEL=\"SMALL\" /> {$show_data['arrTracks'][1]['strArtistNameSounds']} <BREAK LEVEL=\"MEDIUM\" /> It was a {$show_data['arrTracks'][1]['pronouncable_enumTrackLicense']} licensed track. Remember, you can vote for this track by visiting {$show_data['strShowUrlSpoken']} <BREAK LEVEL=\"MEDIUM\" /> Your vote will decide whether it makes it into the best-of-the-week <BREAK LEVEL=\"SMALL\" /> weekly show which is available from cee-cee-hits dot net slash weekly <BREAK LEVEL=\"MEDIUM\" />The theme is an exerpt from Gee Em Zed By Scott Alt-him <BREAK LEVEL=\"SMALL\" />for details, please visit Cee-Cee-Hits dot net slash theme$post_sable";
        $out = fopen(Configuration::getWorkingDir() . '/outro.sable', 'w');
        fwrite($out, $outro);
        fclose($out);

        make_silence(7, Configuration::getWorkingDir() . '/pre-show-silence.wav');
        make_sable(Configuration::getWorkingDir() . '/intro.sable', Configuration::getWorkingDir() . '/intro.wav');
        track_concatenate(Configuration::getWorkingDir() . '/pre-show-silence.wav', Configuration::getWorkingDir() . '/intro.wav', Configuration::getWorkingDir() . '/showstart.wav');
        copy(Configuration::getStaticDir() . '/intro.wav', Configuration::getWorkingDir() . '/intro.wav');
        track_merge(Configuration::getWorkingDir() . '/showstart.wav', Configuration::getWorkingDir() . '/intro.wav', Configuration::getWorkingDir() . '/run.wav', false);
        $running_order = json_add('', track_length(Configuration::getWorkingDir() . '/run.wav', 'intro'));

        $track = download_file($show_data['arrTracks'][1]['localSource']);
        copy($track, Configuration::getWorkingDir() . '/' . $show_data['attTracks'][1]['fileSource']);
        unlink($track);

        track_concatenate(Configuration::getWorkingDir() . '/run.wav', $track, Configuration::getWorkingDir() . '/runplustrack.wav');

        make_sable(Configuration::getWorkingDir() . '/outro.sable', Configuration::getWorkingDir() . '/outro.wav');
        make_silence(34, Configuration::getWorkingDir() . '/post-show-silence.wav');
        track_concatenate(Configuration::getWorkingDir() . '/outro.wav', Configuration::getWorkingDir() . '/post-show-silence.wav', Configuration::getWorkingDir() . '/showend.wav');
        track_reverse(Configuration::getWorkingDir() . '/showend.wav', Configuration::getWorkingDir() . '/showend_rev.wav');
        track_reverse(Configuration::getStaticDir() . '/outro.wav', Configuration::getWorkingDir() . '/outro_rev.wav', false);

        track_merge(Configuration::getWorkingDir() . '/showend_rev.wav', Configuration::getWorkingDir() . '/outro_rev.wav', Configuration::getWorkingDir() . '/run_rev.wav');
        track_reverse(Configuration::getWorkingDir() . '/run_rev.wav', Configuration::getWorkingDir() . '/run.wav');

        track_concatenate(Configuration::getWorkingDir() . '/runplustrack.wav', Configuration::getWorkingDir() . '/run.wav', Configuration::getWorkingDir() . '/show.wav');

/*        make_output(Configuration::getWorkingDir() . '/show.wav', Configuration::media_base . '/daily/' . $show_data['intShowID'] . '.mp3', Configuration::media_base . '/daily/' . $show_data['intShowID'] . '.oga', array(
            'Title' => $show_data['strShowName'],
            'Artist' => 'CCHits.net',
            'AlbumArt' => curl_get($show_data['qrcode'])
        ));*/
    }
}
?></pre>