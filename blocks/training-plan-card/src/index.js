/**
 * Training Plan Card Block – Editor
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { colorScheme, showDownload, showStats } = attributes;
		const blockProps = useBlockProps();

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Appearance', 'runpace' ) }>
						<SelectControl
							label={ __( 'Colour scheme', 'runpace' ) }
							value={ colorScheme }
							options={ [
								{ label: __( 'Default', 'runpace' ), value: 'default' },
								{ label: __( 'Green',   'runpace' ), value: 'green' },
								{ label: __( 'Orange',  'runpace' ), value: 'orange' },
								{ label: __( 'Dark',    'runpace' ), value: 'dark' },
							] }
							onChange={ ( v ) => setAttributes( { colorScheme: v } ) }
						/>
						<ToggleControl label={ __( 'Show stats grid', 'runpace' ) }    checked={ showStats }    onChange={ ( v ) => setAttributes( { showStats: v } ) } />
						<ToggleControl label={ __( 'Show download button', 'runpace' ) } checked={ showDownload } onChange={ ( v ) => setAttributes( { showDownload: v } ) } />
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					<div style={ { padding: 20, background: '#f8f7f4', border: '2px dashed #e2e1dc', borderRadius: 10, textAlign: 'center' } }>
						<p style={ { margin: 0, fontWeight: 600, color: '#ff5c1a' } }>📋 Training Plan Card</p>
						<p style={ { margin: '6px 0 0', color: '#7a7973', fontSize: 13 } }>{ __( 'Displays training plan stats and download CTA.', 'runpace' ) }</p>
					</div>
				</div>
			</>
		);
	},
	save() { return null; },
} );