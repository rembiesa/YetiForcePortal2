
var app = {
	/**
	 * Function to get the module name. This function will get the value from element which has id module
	 * @return : string - module name
	 */
	getModuleName: function () {
		return this.getUrlParam('module');
	},
	/**
	 * Function returns the current view name
	 */
	getViewName: function () {
		return this.getUrlParam('view');
	},
	/**
	 * Function returns the javascript controller based on the current view
	 */
	getPageController: function () {
		var moduleName = app.getModuleName();
		var view = app.getViewName()

		var moduleClassName = moduleName + "_" + view + "_Js";
		var extendModules = jQuery('#extendModules').val();
		if (typeof window[moduleClassName] == 'undefined' && extendModules != undefined) {
			moduleClassName = extendModules + "_" + view + "_Js";
		}
		if (typeof window[moduleClassName] == 'undefined') {
			moduleClassName = "Base_" + view + "_Js";
		}
		if (typeof window[moduleClassName] != 'undefined') {
			return new window[moduleClassName]();
		}
	},
	showSelectElement: function (parent, view, params) {
		var thisInstance = this;
		var selectElement = jQuery();
		if (typeof parent == 'undefined') {
			parent = jQuery('body');
		}
		if (typeof params == 'undefined') {
			params = {};
		}
		selectElement = jQuery('.chzn-select', parent);
		// generate random ID
		selectElement.each(function () {
			if ($(this).prop("id").length == 0) {
				$(this).attr('id', "sel" + thisInstance.generateRandomChar() + thisInstance.generateRandomChar() + thisInstance.generateRandomChar());
			}
		});

		selectElement.chosen(params);
	},
	getUrlParam: function (name) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
				sURLVariables = sPageURL.split('&'),
				sParameterName,
				i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === name) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
	},
	generateRandomChar: function () {
		var chars, newchar, rand;
		chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ";
		rand = Math.floor(Math.random() * chars.length);
		return newchar = chars.substring(rand, rand + 1);
	},
	registerSideLoading: function (body) {
		$(document).pjax('a[href]:not(.loadPage)', 'div.bodyContent');
		$(document).on('pjax:complete', function () {
			var pageController = app.getPageController();
			if (pageController)
				pageController.registerEvents();
		})
	},
	headerBtn: function(){
		$('.rightHeaderBtnMenu').click(function(){
			$('.mobileLeftPanel').toggleClass('showMainMenu');
		});
	},
}

jQuery(document).ready(function () {
	app.showSelectElement(jQuery('body'));
	app.registerSideLoading(jQuery('body'));
	app.headerBtn();
	// Instantiate Page Controller
	var pageController = app.getPageController();
	if (pageController)
		pageController.registerEvents();
});
