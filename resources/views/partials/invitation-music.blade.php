@if ($music['type'] === 'library' || $music['type'] === 'upload')
    <audio id="bgMusic" src="{{ $music['url'] }}" loop preload="auto" style="display:none"></audio>
    <button id="musicToggle" aria-label="Bật/tắt nhạc" class="music-toggle" style="
        position:fixed; bottom:1.5rem; right:1.5rem; z-index:100;
        width:44px; height:44px; border-radius:50%; border:none;
        background:rgba(255,255,255,0.9); box-shadow:0 2px 8px rgba(0,0,0,0.2);
        cursor:pointer; font-size:1.2rem; display:flex; align-items:center; justify-content:center;
    ">♪</button>
    <script>
    (function () {
        var audio  = document.getElementById('bgMusic');
        var toggle = document.getElementById('musicToggle');
        document.addEventListener('click', function startMusic() {
            audio.play().catch(function(){});
            document.removeEventListener('click', startMusic);
        }, { once: true });
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            audio.paused ? audio.play() : audio.pause();
            toggle.textContent = audio.paused ? '♪' : '♬';
        });
    })();
    </script>

@elseif ($music['type'] === 'soundcloud')
    <div style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:100;">
        <iframe
            src="https://w.soundcloud.com/player/?url={{ urlencode($music['url']) }}&auto_play=true&hide_related=true&show_comments=false&show_user=false&show_reposts=false"
            width="100%" height="166" frameborder="0" allow="autoplay">
        </iframe>
    </div>
@endif
