var H5PIntegration = H5PIntegration || {};
var H5P = H5P || {};

// PS: domready is Mootools. jQuery.ready() does not work properly in
// iFrames
window.addEvent('domready', function() {
  H5P.loadedJs = [];
  H5P.loadedCss = [];

  // This is a hack to prevent Joomla mooTools stepping on jQuery UI
  // sliders used in Interactive Video, H5P.Audio etc.
  H5P.jQuery('.ui-slider').each(function (idx, el) {
    el.slide = null;
  });

  // Joomla stylesheets interferes massively with our CSS due to its use
  // of scoping everything in #main.  Therefore, move the contents of
  // our little iFrame to top of body.
  H5P.jQuery('.h5p-content')
    .prependTo('body')
    .siblings().hide(); // Hide siblings, to make only H5P visible in iframe.

  // We only add this if we're being rendered in an iframe.
  if (window.parent && window.parent.h5pIframeResizer) {
    H5P.jQuery('body').resize(function (event) {
      // If we're in an iframe
      window.parent.h5pIframeResizer(H5P.jQuery('.h5p-content').data('content-id'), H5P.jQuery(event.target).outerHeight());
    });
  }
  if (window.parent && window.parent.h5pToggleFullScreen) {
    H5P.oldFullScreen = H5P.fullScreen;
    H5P.fullScreen = function ($el, obj, exit) {
      H5P.jQuery('html').addClass('h5p-fullscreen');
      H5P.$body.addClass('h5p-fullscreen');
      $el.addClass('h5p-fullscreen');

      H5P.jQuery(window.document).on('keyup', function h5pKeyup(event) {
        if (event.keyCode == 27) {
          window.parent.H5P.exitFullScreen();
          H5P.jQuery(window.document).off('keyup', h5pKeyup);
        }
      });

      window.parent.h5pToggleFullScreen($el.data('content-id'), obj, function exitCallback() {
        H5P.jQuery('html').removeClass('h5p-fullscreen');
        H5P.$body.removeClass('h5p-fullscreen');
        $el.removeClass('h5p-fullscreen');
        if (exit) {
          exit();
        }
      });
    };
  }
});

H5PIntegration.getJsonContent = function (contentId) {
  return H5PIntegration.content[contentId].json;
};

H5PIntegration.getContentPath = function (contentId) {
  return H5PIntegration.jsonContentPath + contentId + '/';
};

/**
 * Get the path to the library
 *
 * TODO: Make this use machineName instead of machineName-majorVersion-minorVersion
 *
 * @param {string} library
 *  The library identifier as string, for instance 'downloadify-1.0'
 * @returns {string} The full path to the library
 */
H5PIntegration.getLibraryPath = function (library) {
  return H5PIntegration.libraryPath + library;
};

H5PIntegration.getFullscreen = function (contentId) {
  return H5PIntegration.content[contentId].fullscreen === "1";
};

H5PIntegration.fullscreenText = 'Fullscreen';
