/**
 * Shows a live icon preview in ACF "Icon" and "Social network" select fields.
 * Fields are marked with the wrapper classes aqua-icon-select / aqua-social-select.
 * Icon fields show the theme's liquid icons (window.aquaIcons, see inc/admin.php; styles: liquid-icon.css).
 */
( function () {
	'use strict';

	if ( typeof acf === 'undefined' ) {
		return;
	}

	var socialIcons = {
		facebook: 'fab fa-facebook-f',
		instagram: 'fab fa-instagram',
		x: 'fab fa-twitter',
		linkedin: 'fab fa-linkedin-in',
		youtube: 'fab fa-youtube',
		tiktok: 'fab fa-tiktok',
		telegram: 'fab fa-telegram-plane',
		whatsapp: 'fab fa-whatsapp'
	};

	var svgIcons = window.aquaIcons || {};

	function render( iconClass, text, svg ) {
		var wrap = document.createElement( 'span' );
		wrap.className = 'aqua-icon-option';
		if ( svg ) {
			wrap.innerHTML = svg; // Trusted markup from the theme (aqua_icon()).
		} else {
			var icon = document.createElement( 'i' );
			icon.className = iconClass + ' aqua-icon-option__icon';
			wrap.appendChild( icon );
		}
		wrap.appendChild( document.createTextNode( ' ' + text ) );
		return window.jQuery( wrap );
	}

	acf.add_filter( 'select2_args', function ( args, $select ) {
		// Not every select2 passes an ACF field object, so detect the field from the <select>.
		var $field = $select && $select.closest ? $select.closest( '.acf-field' ) : null;
		if ( ! $field || ! $field.length ) {
			return args;
		}

		var isIcon = $field.hasClass( 'aqua-icon-select' );
		var isSocial = $field.hasClass( 'aqua-social-select' );

		if ( ! isIcon && ! isSocial ) {
			return args;
		}

		var template = function ( state ) {
			if ( ! state.id ) {
				return state.text;
			}
			var iconClass = isIcon ? state.id : socialIcons[ state.id ];
			return iconClass ? render( iconClass, state.text, isIcon ? svgIcons[ state.id ] : '' ) : state.text;
		};

		args.templateResult = template;
		args.templateSelection = template;
		return args;
	} );
} )();
