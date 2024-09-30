jQuery(document).ready(function($) {
    var playlist = bapData.tracks || [];
    var rot = 0;
    var duration;
    var playPercent;
    var rotate_timer;
    var armrot = -45;
    var bufferPercent;
    var currentSong = 0;
    var arm_rotate_timer;
    var arm = document.getElementById("arm");
    var next = document.getElementById("next");
    var song = document.getElementById("song");
    var timer = document.getElementById("timer");
    var music = document.getElementById("music");
    var album = document.getElementById("album");
    var artist = document.getElementById("artist");
    var volume = document.getElementById("volume");
    var playButton = document.getElementById("play");
    var timeline = document.getElementById("slider");
    var playhead = document.getElementById("elapsed");
    var previous = document.getElementById("previous");
    var pauseButton = document.getElementById("pause");
    var bufferhead = document.getElementById("buffered");
    var artwork = document.getElementsByClassName("artwork")[0];
    var timelineWidth = timeline ? timeline.offsetWidth - playhead.offsetWidth : 0;
    var visablevolume = document.getElementsByClassName("volume")[0];

    // Остальной код остается без изменений
    // ...

    function Rotate(){
        if(rot == 361){
            artwork.style.transform = 'rotate(0deg)';
            rot = 0;
        } else {
            artwork.style.transform = 'rotate('+rot+'deg)';
            rot++;
        }
    }

    function RotateArm(){
        if(armrot > -12){
            arm.style.transform = 'rotate(-38deg)';
            armrot = -45;
        } else {
            arm.style.transform = 'rotate('+armrot+'deg)';
            armrot = armrot + (26 / duration);
        }
    }

    function play() {
        if(music.paused){
            music.play();
            console.log('Playing, adding spinning class');
            $('#cover').addClass('spinning'); // Изменено с artwork на #cover
            playButton.style.visibility = "hidden";
            pauseButton.style.visibility = "visible";
            rotate_timer = setInterval(Rotate, 10);
            if(armrot != -45){
                arm.setAttribute("style", "transition: transform 800ms;");
                arm.style.transform = 'rotate('+armrot+'deg)';
            }
            arm_rotate_timer = setInterval(RotateArm, 1000);
        }
    }

    function pause() {
        if(!music.paused){
            music.pause();
            console.log('Paused, removing spinning class');
            $('#cover').removeClass('spinning'); // Изменено с artwork на #cover
            playButton.style.visibility = "visible";
            pauseButton.style.visibility = "hidden";
            clearInterval(rotate_timer);
            clearInterval(arm_rotate_timer);
        }
    }

    // ... остальной код остается без изменений ...

    function loadTrack(index) {
        var track = tracks[index];
        $('#artist').text(track.artist);
        $('#song').text(track.title);
        $('#cover').attr('src', track.cover);
        $('#music').attr('src', track.audio);
        
        // Сброс положения тонарма при загрузке нового трека
        armrot = -45;
        arm.style.transform = 'rotate(-45deg)';
    }

    // Добавьте обработчики событий для кнопок
    $('#play').on('click', play);
    $('#pause').on('click', pause);
    $('#previous').on('click', function() {
        currentSong = (currentSong - 1 + tracks.length) % tracks.length;
        loadTrack(currentSong);
        play();
    });
    $('#next').on('click', function() {
        currentSong = (currentSong + 1) % tracks.length;
        loadTrack(currentSong);
        play();
    });

    // Инициализация первого трека
    if (tracks.length > 0) {
        loadTrack(0);
    }
});