(function () {
  'use strict';

  function getDeviceKey() {
    // Match Elementor's standard responsive ranges, including tablet landscape.
    // Elementor's default tablet breakpoint is 1024px and tablet landscape may
    // extend to 1200px. Keep mobile as the narrowest range.
    var width = window.innerWidth || document.documentElement.clientWidth;
    if (width <= 767) return 'mobile';
    if (width <= 1200) return 'tablet';
    return 'desktop';
  }

  var stickyfxRuntimeInitialized = false;

  function initStickyFX() {
    if (stickyfxRuntimeInitialized) return;
    stickyfxRuntimeInitialized = true;
    var headers = Array.prototype.slice.call(document.querySelectorAll('.stickyfx-enabled'));
    if (!headers.length) return;

    headers.forEach(function (header) {
      if (header.dataset.stickyfxInitialized === '1') return;
      header.dataset.stickyfxInitialized = '1';
      var spacer = document.createElement('div');
      spacer.className = 'stickyfx-spacer';
      spacer.style.height = header.getBoundingClientRect().height + 'px';
      spacer.style.display = 'none';
      header.parentNode.insertBefore(spacer, header);
      header._stickyfxSpacer = spacer;
    });

    var lastScrollY = window.scrollY;

    function updateHeaders() {
      var currentScrollY = window.scrollY;
      var scrollingDown = currentScrollY > lastScrollY;
      var scrollingUp = currentScrollY < lastScrollY;
      var device = getDeviceKey();

      headers.forEach(function (header) {
        var enabled = header.getAttribute('data-stickyfx-' + device + '-enabled') !== 'no';
        var threshold = parseInt(header.getAttribute('data-stickyfx-' + device + '-threshold') || header.getAttribute('data-stickyfx-threshold') || '50', 10);
        var offset = parseInt(header.getAttribute('data-stickyfx-' + device + '-offset') || '0', 10);
        var background = header.getAttribute('data-stickyfx-background') || '#ffffff';
        var hideOnScroll = header.getAttribute('data-stickyfx-' + device + '-hide-on-scroll');
        if (hideOnScroll !== 'yes' && hideOnScroll !== 'no') {
          hideOnScroll = header.getAttribute('data-stickyfx-hide-on-scroll') === 'yes' ? 'yes' : 'no';
        }
        if (isNaN(threshold) || threshold < 0) threshold = 50;
        if (isNaN(offset) || offset < 0) offset = 0;
        header.style.setProperty('--stickyfx-bg-color', background);

        var shouldStick = enabled && currentScrollY > threshold;
        var spacer = header._stickyfxSpacer;

        if (shouldStick && !header.classList.contains('stickyfx-fixed')) {
          var rect = header.getBoundingClientRect();
          if (spacer) {
            spacer.style.height = rect.height + 'px';
            spacer.style.display = 'block';
          }
          header.style.left = rect.left + 'px';
          header.style.width = rect.width + 'px';
          header.classList.add('stickyfx-fixed');
        } else if (!shouldStick && header.classList.contains('stickyfx-fixed')) {
          header.classList.remove('stickyfx-fixed', 'stickyfx-hidden');
          header.style.removeProperty('left');
          header.style.removeProperty('width');
          header.style.removeProperty('top');
          if (spacer) spacer.style.display = 'none';
        }

        if (shouldStick) {
          header.style.top = offset + 'px';
        }

        if (shouldStick && hideOnScroll === 'yes') {
          if (scrollingDown && currentScrollY > threshold + 5) {
            header.classList.add('stickyfx-hidden');
          } else if (scrollingUp || currentScrollY <= threshold + 5) {
            header.classList.remove('stickyfx-hidden');
          }
        } else {
          header.classList.remove('stickyfx-hidden');
        }

        header.classList.toggle('stickyfx-scrolled', shouldStick);
      });
      lastScrollY = currentScrollY;
    }

    window.addEventListener('scroll', updateHeaders, { passive: true });
    window.addEventListener('resize', function () {
      headers.forEach(function (header) {
        if (header.classList.contains('stickyfx-fixed')) {
          var rect = header.getBoundingClientRect();
          header.style.left = rect.left + 'px';
          header.style.width = rect.width + 'px';
        } else if (header._stickyfxSpacer) {
          header._stickyfxSpacer.style.height = header.getBoundingClientRect().height + 'px';
        }
      });
      updateHeaders();
    });

    updateHeaders();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStickyFX);
  } else {
    initStickyFX();
  }
  window.addEventListener('elementor/frontend/init', initStickyFX);
})();
