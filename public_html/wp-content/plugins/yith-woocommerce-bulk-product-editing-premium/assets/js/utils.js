window.yithBulk = window.yithBulk || {};

yithBulk.block = element => {
	const blockArgs = {
		message   : '',
		overlayCSS: { backgroundColor: '#ffffff', opacity: 0.8, cursor: 'wait' }
	};
	element.block( blockArgs );
};

yithBulk.unblock = element => element.unblock();

yithBulk.inArray = function ( needle, haystack ) {
	return Array.isArray( haystack ) && haystack.indexOf( needle ) !== -1;
};

yithBulk.formatPrice    = function ( price ) {
	return price.toString().replace( '.', yithWcbep.wcDecimalSeparator ?? '.' );
};
yithBulk.isJsonString   = function ( str ) {
	let isJson = true;
	if ( 'string' === typeof str && str.indexOf( '{' ) === 0 ) {
		try {
			JSON.parse( str );
		} catch ( e ) {
			isJson = false;
		}
	} else {
		isJson = false;
	}

	return isJson;
};
yithBulk.maybeParseJSON = function ( value, defaultValue ) {
	defaultValue = undefined === defaultValue ? value : defaultValue;
	return yithBulk.isJsonString( value ) ? JSON.parse( value ) : defaultValue;
};

yithBulk.wpEditorDefaultOptions = {
	tinymce     : {
		wpautop      : true,
		plugins      : 'charmap colorpicker hr lists paste tabfocus textcolor wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
		toolbar1     : 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,wp_adv,listbuttons',
		toolbar2     : 'styleselect,strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
		textarea_rows: 20
	},
	quicktags   : { buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close' },
	mediaButtons: false
};
