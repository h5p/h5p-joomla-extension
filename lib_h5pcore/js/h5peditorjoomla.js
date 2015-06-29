var H5PEditor = H5PEditor || {};
var ns = H5PEditor;

ns.init = function () {
	ns.$ = H5P.jQuery;

	var h5peditor;
	var $editor = ns.$('.h5p-editor');
	var $type = ns.$('input[name="h5p_type"]');
	var library = ns.$('#edit-h5p-library').val();

	ns.contentId = h5peditordata.contentId;
	ns.basePath = h5peditordata.basePath;
	ns.fileIcon = h5peditordata.fileIcon;
	ns.ajaxPath = h5peditordata.ajaxPath;

	h5peditor = new ns.Editor(library, JSON.parse(ns.$("#edit-h5p-params").val()));
	h5peditor.replace($editor);

	ns.$('#h5peditor-form').submit(function () {
		window.parent.H5PRefuseClose = false;
		var params = h5peditor.getParams();

		if (params === false) {
			/*
			 * TODO: Give good feedback when validation fails. Currently it seems save and delete buttons
			 * aren't working, but the user doesn't get any indication of why they aren't working.
			 */
			// TODO: Stop submitting, and point to errors.
		}

		if (params !== undefined) {
			ns.$('#edit-h5p-library').val(h5peditor.getLibrary());
			ns.$('#edit-h5p-params').val(JSON.stringify(params));
		}
	});
	window.addEventListener("beforeunload", function (e) {
		if (!window.parent.H5PRefuseClose) {
			return;
		}
		var confirmationMessage = "You are about to leave the page. If you have unsaved changes your work will be lost. Press the save button to save your work.";

		(e || window.event).returnValue = confirmationMessage;     //Gecko + IE
		return confirmationMessage;                                //Webkit, Safari, Chrome etc.
	});
};

H5P.jQuery(document).ready(ns.init);
