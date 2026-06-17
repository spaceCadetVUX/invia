(function () {
    var root = document.querySelector('.tmpl-classic');
    if (!root) return;

    var tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

    tl.to(root.querySelector('.hero'),      { opacity: 1, duration: 0.8 })
      .to(root.querySelector('.bride-name'), { opacity: 1, y: 0, duration: 1 },   '-=0.3')
      .to(root.querySelector('.connector'), { opacity: 1, duration: 0.5 },       '-=0.5')
      .to(root.querySelector('.groom-name'), { opacity: 1, y: 0, duration: 1 },   '-=0.3')
      .to(root.querySelector('.divider'),   { opacity: 1, scaleX: 1, duration: 0.6 }, '-=0.4')
      .to(root.querySelector('.event-date'), { opacity: 1, duration: 0.6 },       '-=0.2')
      .to(root.querySelector('.event-venue'), { opacity: 1, duration: 0.6 },      '-=0.3')
      .to(root.querySelector('.actions'),   { opacity: 1, duration: 0.6 },        '-=0.2');
})();
