/**
 * Marathon Filter Block – Editor (index.js)
 *
 * The filter UI is almost entirely server-rendered. The editor
 * shows a static placeholder so authors can insert and configure it.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const {
			postsPerPage,
			showDistanceFilter,
			showLocationFilter,
			showDateFilter,
			showViewToggle,
			defaultView,
		} = attributes;

		const blockProps = useBlockProps( { className: 'runpace-marathon-filter-editor-preview' } );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Filter Settings', 'runpace' ) }>
						<RangeControl
							label={ __( 'Races per page', 'runpace' ) }
							value={ postsPerPage }
							onChange={ ( val ) => setAttributes( { postsPerPage: val } ) }
							min={ 3 }
							max={ 24 }
						/>
						<SelectControl
							label={ __( 'Default view', 'runpace' ) }
							value={ defaultView }
							options={ [
								{ label: __( 'Grid', 'runpace' ), value: 'grid' },
								{ label: __( 'List', 'runpace' ), value: 'list' },
							] }
							onChange={ ( val ) => setAttributes( { defaultView: val } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Visible Filters', 'runpace' ) } initialOpen={ false }>
						<ToggleControl
							label={ __( 'Distance filter', 'runpace' ) }
							checked={ showDistanceFilter }
							onChange={ ( val ) => setAttributes( { showDistanceFilter: val } ) }
						/>
						<ToggleControl
							label={ __( 'Location filter', 'runpace' ) }
							checked={ showLocationFilter }
							onChange={ ( val ) => setAttributes( { showLocationFilter: val } ) }
						/>
						<ToggleControl
							label={ __( 'Date filter', 'runpace' ) }
							checked={ showDateFilter }
							onChange={ ( val ) => setAttributes( { showDateFilter: val } ) }
						/>
						<ToggleControl
							label={ __( 'View toggle (grid/list)', 'runpace' ) }
							checked={ showViewToggle }
							onChange={ ( val ) => setAttributes( { showViewToggle: val } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<div style={ {
						padding: '24px',
						background: '#f8f7f4',
						borderRadius: '10px',
						border: '2px dashed #e2e1dc',
						textAlign: 'center',
					} }>
						<p style={ { margin: 0, fontWeight: 600, color: '#0bda7a' } }>
							🏃 Marathon Filter Block
						</p>
						<p style={ { margin: '8px 0 0', color: '#7a7973', fontSize: '13px' } }>
							{ __( 'Interactive marathon listing with filters. Configure in the sidebar.', 'runpace' ) }
						</p>
						<p style={ { margin: '4px 0 0', color: '#7a7973', fontSize: '12px' } }>
							{ postsPerPage } { __( 'per page', 'runpace' ) } · { defaultView } { __( 'view', 'runpace' ) }
						</p>
					</div>
				</div>
			</>
		);
	},

	// Dynamic block — save returns null (all rendering is in render.php).
	save() { return null; },
} );