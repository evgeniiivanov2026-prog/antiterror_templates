( function ( wp ) {
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var serverSideRender = wp.serverSideRender;
	var ServerSideRender = serverSideRender && serverSideRender.default ? serverSideRender.default : serverSideRender;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var __ = wp.i18n.__;

	registerBlockType( 'rubezh/canvas', {
		apiVersion: 3,
		title: __( 'Полотно страницы', 'rubezh' ),
		description: __( 'Основная область, в которой секции можно менять местами.', 'rubezh' ),
		icon: 'layout',
		category: 'rubezh',
		supports: { html: false },
		edit: function () {
			return el(
				'main',
				{ id: 'content', className: 'rz-main' },
				el( InnerBlocks, {
					renderAppender: InnerBlocks.ButtonBlockAppender
				} )
			);
		},
		save: function () {
			return el(
				'main',
				{ id: 'content', className: 'rz-main' },
				el( InnerBlocks.Content, null )
			);
		}
	} );

	var dynamic = [
		[ 'rubezh/site-header', __( 'Шапка', 'rubezh' ), 'admin-home' ],
		[ 'rubezh/site-footer', __( 'Подвал', 'rubezh' ), 'admin-site-alt3' ],
		[ 'rubezh/masthead', __( 'Обложка выпуска', 'rubezh' ), 'cover-image' ],
		[ 'rubezh/journal-grid', __( 'Свежая полоса', 'rubezh' ), 'excerpt-view', { count: { type: 'number', default: 5 } } ],
		[ 'rubezh/memorial', __( 'Память героев', 'rubezh' ), 'awards', { count: { type: 'number', default: 4 }, archive: { type: 'boolean', default: false } } ],
		[ 'rubezh/ethics', __( 'Принципы полосы', 'rubezh' ), 'editor-alignleft' ],
		[ 'rubezh/initiatives', __( 'Фонд инициатив', 'rubezh' ), 'megaphone', { count: { type: 'number', default: 4 }, archive: { type: 'boolean', default: false } } ],
		[ 'rubezh/signal', __( 'Полоса 112', 'rubezh' ), 'warning' ],
		[ 'rubezh/article', __( 'Материал', 'rubezh' ), 'text-page' ],
		[ 'rubezh/hero-story', __( 'Профиль памяти', 'rubezh' ), 'id' ],
		[ 'rubezh/initiative-story', __( 'Страница инициативы', 'rubezh' ), 'yes' ],
		[ 'rubezh/page-shell', __( 'Страница', 'rubezh' ), 'admin-page' ],
		[ 'rubezh/journal-archive', __( 'Архив журнала', 'rubezh' ), 'list-view' ],
		[ 'rubezh/search-results', __( 'Результаты поиска', 'rubezh' ), 'search' ],
		[ 'rubezh/not-found', __( 'Страница 404', 'rubezh' ), 'dismiss' ],
		[ 'rubezh/route', __( 'Маршрут шаблона', 'rubezh' ), 'randomize' ]
	];

	dynamic.forEach( function ( item ) {
		var attributes = item[ 3 ] || {};
		registerBlockType( item[ 0 ], {
			apiVersion: 3,
			title: item[ 1 ],
			icon: item[ 2 ],
			category: 'rubezh',
			attributes: attributes,
			supports: { html: false },
			edit: function ( props ) {
				var controls = null;
				if ( attributes.count ) {
					controls = el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: __( 'Полоса', 'rubezh' ), initialOpen: true },
							el( RangeControl, {
								label: __( 'Сколько записей', 'rubezh' ),
								min: 1,
								max: 12,
								value: props.attributes.count,
								onChange: function ( value ) {
									props.setAttributes( { count: value } );
								}
							} ),
							attributes.archive
								? el( ToggleControl, {
									label: __( 'Режим архива с страницами', 'rubezh' ),
									checked: !! props.attributes.archive,
									onChange: function ( value ) {
										props.setAttributes( { archive: value } );
									}
								} )
								: null
						)
					);
				}
				var preview = ServerSideRender
					? el( ServerSideRender, { block: item[ 0 ], attributes: props.attributes } )
					: el( 'p', null, item[ 1 ] );
				return el( 'div', { className: 'rubezh-block-preview' }, controls, preview );
			},
			save: function () {
				return null;
			}
		} );
	} );
}( window.wp ) );
