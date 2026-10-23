(function () {
  'use strict';

  function deviceKey() {
    var width = window.innerWidth || document.documentElement.clientWidth;
    if (width <= 767) return 'mobile';
    if (width <= 1200) return 'tablet';
    return 'desktop';
  }

  function initProEffects() {
    var elements = document.querySelectorAll('.stickyfx-enabled[data-stickyfx-pro="yes"]');
    if (!elements.length) return;

    elements.forEach(function (element) {
      if (element.dataset.stickyfxProInitialized === '1') return;
      element.dataset.stickyfxProInitialized = '1';

      var selector = element.getAttribute('data-stickyfx-pro-logo-selector') || '.elementor-widget-theme-site-logo img, .elementor-widget-image img';
      var images = [];
      try {
        images = Array.prototype.slice.call(element.querySelectorAll(selector));
      } catch (e) {
        images = [];
      }

      images.forEach(function (img) {
        if (!img.dataset.stickyfxOriginalSrc) {
          img.dataset.stickyfxOriginalSrc = img.getAttribute('src') || '';
          img.dataset.stickyfxOriginalSrcset = img.getAttribute('srcset') || '';
        }
      });

      function applyEffects() {
        var isScrolled = element.classList.contains('stickyfx-scrolled');
        var isFixed = element.classList.contains('stickyfx-fixed');
        var device = deviceKey();
        var enabled = element.getAttribute('data-stickyfx-' + device + '-enabled') !== 'no';
        var scale = parseInt(element.getAttribute('data-stickyfx-pro-shrink-scale') || '85', 10);
        var blur = parseInt(element.getAttribute('data-stickyfx-pro-blur') || '0', 10);
        var opacity = parseInt(element.getAttribute('data-stickyfx-pro-opacity') || '100', 10);
        var duration = parseInt(element.getAttribute('data-stickyfx-pro-transition') || '250', 10);
        var animation = element.getAttribute('data-stickyfx-pro-animation') || 'fade';
        var mobileLogo = element.getAttribute('data-stickyfx-pro-mobile-logo') || '';
        var scrolledLogo = element.getAttribute('data-stickyfx-pro-scrolled-logo') || '';

        if (isNaN(scale)) scale = 85;
        if (isNaN(blur)) blur = 0;
        if (isNaN(opacity)) opacity = 100;
        if (isNaN(duration)) duration = 250;

        var shrinkActive = enabled && isFixed && isScrolled && element.getAttribute('data-stickyfx-pro-shrink') === 'yes';
        element.style.setProperty('--stickyfx-pro-scale', shrinkActive ? String(Math.max(0.01, Math.min(1, scale / 100))) : '1');
        element.style.setProperty('--stickyfx-pro-blur', (isFixed && isScrolled && enabled ? Math.min(30, blur) : 0) + 'px');
        element.style.setProperty('--stickyfx-pro-opacity', isFixed && isScrolled && enabled ? String(Math.max(0.1, Math.min(1, opacity / 100))) : '1');
        element.style.setProperty('--stickyfx-pro-duration', Math.max(0, Math.min(2000, duration)) + 'ms');
        element.setAttribute('data-stickyfx-pro-animation-active', isFixed && isScrolled && enabled ? animation : 'none');

        var desiredLogo = '';
        if (enabled && device === 'mobile' && mobileLogo) {
          desiredLogo = mobileLogo;
        } else if (enabled && isScrolled && scrolledLogo) {
          desiredLogo = scrolledLogo;
        }

        images.forEach(function (img) {
          var original = img.dataset.stickyfxOriginalSrc || '';
          var originalSrcset = img.dataset.stickyfxOriginalSrcset || '';
          var current = desiredLogo || original;
          if (current && img.getAttribute('src') !== current) {
            img.setAttribute('src', current);
            if (desiredLogo) {
              img.removeAttribute('srcset');
            } else if (originalSrcset) {
              img.setAttribute('srcset', originalSrcset);
            }
          }
        });
      }

      element._stickyfxProApplyEffects = applyEffects;
      applyEffects();
    });
  }

  function refresh() {
    document.querySelectorAll('.stickyfx-enabled[data-stickyfx-pro="yes"]').forEach(function (element) {
      if (typeof element._stickyfxProApplyEffects === 'function') {
        element._stickyfxProApplyEffects();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProEffects);
  } else {
    initProEffects();
  }
  window.addEventListener('elementor/frontend/init', initProEffects);
  window.addEventListener('scroll', refresh, { passive: true });
  window.addEventListener('resize', refresh);
})();
