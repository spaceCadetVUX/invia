<section id="wishes-section" class="wishes-section"
         data-event-slug="{{ $event->slug }}"
         data-wishes-enabled="{{ $event->wishes_enabled ? 'true' : 'false' }}">

    <h2 style="text-align:center; margin-bottom:1.5rem; font-size:1.25rem; color:#374151;">
        {{ __('wishes.section_title') }}
    </h2>

    @if ($event->wishes_enabled)
    <form id="wish-form" class="wish-form" style="max-width:480px; margin:0 auto 2rem;">
        @csrf
        @if (!$guest)
            <input type="text" name="name" placeholder="{{ __('wishes.your_name') }}" maxlength="100" required
                   style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; margin-bottom:0.5rem; box-sizing:border-box;">
        @else
            <input type="hidden" name="name" value="{{ $guest->name }}">
            <p style="font-size:0.875rem; color:#6b7280; margin-bottom:0.5rem;">
                {{ __('wishes.greeting', ['name' => $guest->name]) }}
            </p>
        @endif
        <div style="position:relative;">
            <textarea name="message" placeholder="{{ __('wishes.placeholder') }}" maxlength="500" required
                      style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; resize:vertical; min-height:80px; box-sizing:border-box; padding-bottom:1.5rem;"></textarea>
            <span class="wish-counter" style="position:absolute; bottom:0.375rem; right:0.75rem; font-size:0.7rem; color:#9ca3af;">0/500</span>
        </div>
        <button type="submit" style="
            width:100%; margin-top:0.5rem; padding:0.625rem;
            background:#4f46e5; color:#fff; border:none; border-radius:0.5rem;
            font-size:0.875rem; font-weight:600; cursor:pointer;
        ">{{ __('wishes.send') }}</button>
    </form>
    @endif

    <div id="wishes-list" class="wishes-list" style="max-width:560px; margin:0 auto;">
        <p class="wishes-loading" style="text-align:center; color:#9ca3af; font-size:0.875rem;">
            {{ __('wishes.loading') }}
        </p>
    </div>
</section>

<script>
(function () {
    var section = document.querySelector('#wishes-section');
    var slug    = section.dataset.eventSlug;
    var enabled = section.dataset.wishesEnabled === 'true';
    var list    = document.getElementById('wishes-list');
    var form    = document.getElementById('wish-form');
    var lastId  = 0;

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
        });
    }

    function renderWish(w) {
        var el = document.createElement('div');
        el.className = 'wish-item' + (w.is_pinned ? ' wish-pinned' : '');
        el.dataset.id = w.id;
        el.style.cssText = 'border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.75rem 1rem; margin-bottom:0.75rem; background:#fff;';
        if (w.is_pinned) el.style.borderColor = '#c7d2fe';
        el.innerHTML =
            '<div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">' +
            (w.is_pinned ? '<span style="font-size:0.7rem;color:#4f46e5;font-weight:600;">📌 Ghim</span>' : '') +
            '<strong style="font-size:0.875rem;color:#111827;">' + escapeHtml(w.name) + '</strong>' +
            '<time style="font-size:0.75rem;color:#9ca3af;margin-left:auto;">' + escapeHtml(w.created_at) + '</time>' +
            '</div>' +
            '<p style="font-size:0.875rem;color:#374151;margin:0;white-space:pre-wrap;">' + escapeHtml(w.message) + '</p>';
        return el;
    }

    function renderEmpty() {
        list.innerHTML = '<p style="text-align:center;color:#9ca3af;font-size:0.875rem;">{{ __("wishes.empty") }}</p>';
    }

    function loadWishes() {
        return fetch('/thiep/' + slug + '/wishes', { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                list.innerHTML = '';
                if (!data.length) { renderEmpty(); return; }
                data.forEach(function(w) {
                    list.appendChild(renderWish(w));
                    if (w.id > lastId) lastId = w.id;
                });
            })
            .catch(function() {});
    }

    function pollWishes() {
        if (!lastId || document.hidden) return;
        fetch('/thiep/' + slug + '/wishes?after=' + lastId, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                data.forEach(function(w) {
                    if (!document.querySelector('[data-id="' + w.id + '"]')) {
                        var empty = list.querySelector('p');
                        if (empty) list.innerHTML = '';
                        list.insertBefore(renderWish(w), list.firstChild);
                        if (w.id > lastId) lastId = w.id;
                    }
                });
            })
            .catch(function() {});
    }

    if (enabled && form) {
        var textarea = form.querySelector('textarea[name="message"]');
        var counter  = form.querySelector('.wish-counter');
        textarea.addEventListener('input', function() {
            counter.textContent = textarea.value.length + '/500';
        });

        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var tParam    = new URLSearchParams(location.search).get('t');
        var actionUrl = '/thiep/' + slug + '/wishes' + (tParam ? '?t=' + tParam : '');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            var body = new FormData(form);
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json',
                },
                body: body,
            })
            .then(function(r) {
                if (!r.ok) throw r;
                return r.json();
            })
            .then(function(wish) {
                var empty = list.querySelector('p');
                if (empty) list.innerHTML = '';
                list.insertBefore(renderWish(wish), list.firstChild);
                lastId = Math.max(lastId, wish.id);
                form.reset();
                if (counter) counter.textContent = '0/500';
            })
            .catch(function() {})
            .finally(function() { btn.disabled = false; });
        });
    }

    loadWishes().then(function() {
        setInterval(pollWishes, 15000);
    });
})();
</script>
