/**
 * Featured Marathon Block – Editor
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { showCountdown, primaryLabel, secondaryLabel, overlayOpacity } = attributes;
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Options', 'runpace' ) }>
						<ToggleControl label={ __( 'Show countdown', 'runpace' ) } checked={ showCountdown } onChange={ ( v ) => setAttributes( { showCountdown: v } ) } />
						<TextControl label={ __( 'Primary button label', 'runpace' ) }   value={ primaryLabel }   onChange={ ( v ) => setAttributes( { primaryLabel: v } ) } />
						<TextControl label={ __( 'Secondary button label', 'runpace' ) } value={ secondaryLabel } onChange={ ( v ) => setAttributes( { secondaryLabel: v } ) } />
						<RangeControl label={ __( 'Overlay opacity', 'runpace' ) } value={ overlayOpacity } onChange={ ( v ) => setAttributes( { overlayOpacity: v } ) } min={ 0.2 } max={ 0.9 } step={ 0.05 } />
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					<div style={ { padding: 20, background: '#111210', border: '2px dashed #2c302d', borderRadius: 16, textAlign: 'center', minHeight: 120, display: 'flex', alignItems: 'center', justifyContent: 'center', flexDirection: 'column', gap: 8 } }>
						<p style={ { margin: 0, fontWeight: 600, color: '#0bda7a', fontSize: 15 } }>⭐ Featured Marathon Block</p>
						<p style={ { margin: 0, color: '#7a7973', fontSize: 13 } }>{ __( 'Hero card with backdrop image. Works inside Query Loop.', 'runpace' ) }</p>
					</div>
				</div>
			</>
		);
	},
	save() { return null; },
} );