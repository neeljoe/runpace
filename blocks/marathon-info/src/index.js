/**
 * Marathon Info Block – Editor
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { layout, showCountdown, showRegisterButton, registerLabel } = attributes;
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Layout', 'runpace' ) }>
						<SelectControl
							label={ __( 'Layout style', 'runpace' ) }
							value={ layout }
							options={ [
								{ label: __( 'Grid', 'runpace' ),    value: 'grid' },
								{ label: __( 'Compact', 'runpace' ), value: 'compact' },
								{ label: __( 'Hero', 'runpace' ),    value: 'hero' },
							] }
							onChange={ ( val ) => setAttributes( { layout: val } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Options', 'runpace' ) }>
						<ToggleControl label={ __( 'Show countdown', 'runpace' ) }    checked={ showCountdown }       onChange={ ( v ) => setAttributes( { showCountdown: v } ) } />
						<ToggleControl label={ __( 'Show register CTA', 'runpace' ) } checked={ showRegisterButton } onChange={ ( v ) => setAttributes( { showRegisterButton: v } ) } />
						{ showRegisterButton && (
							<TextControl label={ __( 'Register button label', 'runpace' ) } value={ registerLabel } onChange={ ( v ) => setAttributes( { registerLabel: v } ) } />
						) }
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					<div style={ { padding: 20, background: '#f8f7f4', border: '2px dashed #e2e1dc', borderRadius: 10, textAlign: 'center' } }>
						<p style={ { margin: 0, fontWeight: 600, color: '#0bda7a' } }>📋 Marathon Info Block</p>
						<p style={ { margin: '6px 0 0', color: '#7a7973', fontSize: 13 } }>{ __( 'Displays marathon meta. Works inside Query Loop.', 'runpace' ) }</p>
					</div>
				</div>
			</>
		);
	},
	save() { return null; },
} );