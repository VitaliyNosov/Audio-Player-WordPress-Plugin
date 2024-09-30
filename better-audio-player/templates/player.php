<?php
$tracks = get_posts(array(
    'post_type' => 'bap_track',
    'numberposts' => -1,
));

$tracks_data = array_map(function($track) {
    return array(
        'title' => $track->post_title,
        'artist' => get_post_meta($track->ID, 'bap_artist', true),
        'audio' => get_post_meta($track->ID, 'bap_audio_url', true),
        'cover' => get_post_meta($track->ID, 'bap_cover_url', true),
    );
}, $tracks);

$appearance_settings = get_option('better_audio_player_appearance_settings');
$background_color = isset($appearance_settings['background_color']) ? $appearance_settings['background_color'] : '#1e2125';
$text_color = isset($appearance_settings['text_color']) ? $appearance_settings['text_color'] : '#ffffff';
$accent_color = isset($appearance_settings['accent_color']) ? $appearance_settings['accent_color'] : '#ef6dbc';

// Добавьте эти строки в начало файла для подключения стилей
function enqueue_better_audio_player_styles() {
    wp_enqueue_style('better-audio-player-styles', plugins_url('css/player-styles.css', dirname(__FILE__)));
}
add_action('wp_enqueue_scripts', 'enqueue_better_audio_player_styles');

// Если треков нет, используем демо-трек
if (empty($tracks_data)) {
    $tracks_data = array(
        array(
            'title' => 'Demo Song',
            'artist' => 'Demo Artist',
            'audio' => plugins_url('assets/demo-song.mp3', dirname(__FILE__)),
            'cover' => plugins_url('assets/demo-cover.jpg', dirname(__FILE__)),
        )
    );
}
?>

<div class="player" style="background-color: <?php echo esc_attr($background_color); ?>;">
    <div id="arm"></div>
    <ul>
        <li class="artwork">
            <img id="cover" src="default-cover.jpg" alt="Album cover">
        </li>
        <li class="info">
            <h1 id="artist" style="color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($artist_label); ?></h1>
            <h4 id="album" style="color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($album_label); ?></h4>
            <h2 id="song" style="color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($song_label); ?></h2>
            <div class="button-items">
                <audio id="music" preload="auto">
                    <source src="" type="audio/mp3">
                    Your browser does not support the audio element.
                </audio>
                <div id="slider">
                    <div id="elapsed"></div>
                    <div id="buffered"></div>
                </div>
                <p id="timer">0:00</p>
                <div class="controls">
                    <span class="expend">
                        <svg id="previous" class="step-backward" viewBox="0 0 25 25" xml:space="preserve">
                            <g>
                                <polygon points="4.9,4.3 9,4.3 9,11.6 21.4,4.3 21.4,20.7 9,13.4 9,20.7 4.9,20.7"/>
                            </g>
                        </svg>
                    </span>
                    <svg id="play" class="control-button" viewBox="0 0 25 25" xml:space="preserve">
                        <defs>
                            <rect x="-49.5" y="-132.9" width="446.4" height="366.4"/>
                        </defs>
                        <g>
                            <circle fill="none" cx="12.5" cy="12.5" r="10.8"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.7,6.9V18c0,0,0.2,1.4,1.8,0l8.1-4.8c0,0,1.2-1.1-1-2L9.8,6.5 C9.8,6.5,9.1,6,8.7,6.9z"/>
                        </g>
                    </svg>
                    <svg id="pause" class="control-button" viewBox="0 0 25 25" xml:space="preserve">
                        <g>
                            <rect x="6" y="4.6" width="3.8" height="15.7"/>
                            <rect x="14" y="4.6" width="3.9" height="15.7"/>
                        </g>
                    </svg>
                    <span class="expend">
                        <svg id="next" class="step-foreward" viewBox="0 0 25 25" xml:space="preserve">
                            <g>
                                <polygon points="20.7,4.3 16.6,4.3 16.6,11.6 4.3,4.3 4.3,20.7 16.7,13.4 16.6,20.7 20.7,20.7"/>
                            </g>
                        </svg>
                    </span>
                    <div class="slider">
                        <div class="volume"></div>
                        <input type="range" id="volume" min="0" max="1" step="0.01" value="1" />
                    </div>
                </div>
            </div>
        </li>
    </ul>
</div>

<script>
var tracks = <?php echo json_encode($tracks_data); ?>;
var currentTrack = 0;

function loadTrack(index) {
    var track = tracks[index];
    document.getElementById('artist').textContent = track.artist;
    document.getElementById('song').textContent = track.title;
    document.getElementById('cover').src = track.cover;
    document.getElementById('music').src = track.audio;
}

document.addEventListener('DOMContentLoaded', function() {
    if (tracks.length > 0) {
        loadTrack(0);
    }
});
</script>

<style>
    .player h1, .player h2, .player h4 {
        color: <?php echo esc_attr($text_color); ?>;
    }
    #elapsed, .volume, #play:hover g, #pause:hover rect, .expend svg:hover g polygon, input[type="range"]::-webkit-slider-thumb:hover {
        background-color: <?php echo esc_attr($accent_color); ?>;
    }
    .control-button {
        cursor: pointer;
    }
    #pause {
        display: none;
    }
</style>