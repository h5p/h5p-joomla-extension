var h5pIsFullScreen = false,
	h5pOldHeight;

function h5pIdClean(id) {
	return id.replace('.', '_');
}

// Set size of H5P iframe when content resizes.
function h5pIframeResizer(id, height) {
	var height = (h5pIsFullScreen) ? '100%' : height + 'px';
	if (height != h5pOldHeight) {
		h5pOldHeight = height;
		document.getElementById('iframe-' + h5pIdClean(id)).style.height = height;
	}
}

// Toggle H5P full screen.
function h5pToggleFullScreen(id, obj, exit) {
	id = h5pIdClean(id);
	h5pIsFullScreen = true;
	var $el = H5P.jQuery('#iframe-wrapper-' + id);
	H5P.fullScreen($el, obj, function () {
		h5pIsFullScreen = false;
		exit();
	});
	return;
}
