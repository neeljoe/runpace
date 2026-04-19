/**
 * RunPace – Marathon Info Block
 * Editor (index.js) — Inspector controls, edit preview placeholder.
 *
 * Build target: blocks/marathon-info/build/index.js
 * (In a real project, run: npx wp-scripts build blocks/marathon-info/src/index.js)
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	TextControl,
	Placeholder,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const {
			showDate,
			showLocation,
			showDistance,
			showPrice,
			showElevation,
			showDifficulty,
			showRegisterBtn,
			registerBtnLabel,
			layout,
		} = attributes;

		const blockProps = useBlockProps( {
			className: `runpace-marathon-info runpace-marathon-info--${ layout } runpace-marathon-info--editor`,
		} );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Layout', 'runpace' ) } initialOpen={ true }>
						<SelectControl
							label={ __( 'Display layout', 'runpace' ) }
							value={ layout }
							options={ [
								{ value: 'card',    label: __( 'Card (default)', 'runpace' ) },
								{ value: 'inline',  label: __( 'Inline row', 'runpace' ) },
								{ value: 'compact', label: __( 'Compact', 'runpace' ) },
							] }
							onChange={ ( val ) => setAttributes( { layout: val } ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Visible fields', 'runpace' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'Race date', 'runpace' ) }
							checked={ showDate }
							onChange={ ( val ) => setAttributes( { showDate: val } ) }
						/>
						<ToggleControl
							label={ __( 'Location', 'runpace' ) }
							checked={ showLocation }
							onChange={ ( val ) => setAttributes( { showLocation: val } ) }
						/>
						<ToggleControl
							label={ __( 'Distance', 'runpace' ) }
							checked={ showDistance }
							onChange={ ( val ) => setAttributes( { showDistance: val } ) }
						/>
						<ToggleControl
							label={ __( 'Entry fee / price', 'runpace' ) }
							checked={ showPrice }
							onChange={ ( val ) => setAttributes( { showPrice: val } ) }
						/>
						<ToggleControl
							label={ __( 'Elevation gain', 'runpace' ) }
							checked={ showElevation }
							onChange={ ( val ) => setAttributes( { showElevation: val } ) }
						/>
						<ToggleControl
							label={ __( 'Difficulty rating', 'runpace' ) }
							checked={ showDifficulty }
							onChange={ ( val ) => setAttributes( { showDifficulty: val } ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Register button', 'runpace' ) } initialOpen={ false }>
						<ToggleControl
							label={ __( 'Show register button', 'runpace' ) }
							checked={ showRegisterBtn }
							onChange={ ( val ) => setAttributes( { showRegisterBtn: val } ) }
						/>
						{ showRegisterBtn && (
							<TextControl
								label={ __( 'Button label', 'runpace' ) }
								value={ registerBtnLabel }
								onChange={ ( val ) => setAttributes( { registerBtnLabel: val } ) }
							/>
						) }
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<Placeholder
						icon="location-alt"
						label={ __( 'Marathon Info', 'runpace' ) }
						instructions={ __(
							'This block displays race metadata (date, location, distance, price, elevation, difficulty) pulled automatically from the current marathon post.',
							'runpace'
						) }
					>
						<div className="runpace-marathon-info__editor-preview">
							{ showDate      && <span className="runpace-meta-chip">📅 Race date</span> }
							{ showLocation  && <span className="runpace-meta-chip">📍 Location</span> }
							{ showDistance  && <span className="runpace-meta-chip">🏃 Distance</span> }
							{ showPrice     && <span className="runpace-meta-chip">💳 Entry fee</span> }
							{ showElevation && <span className="runpace-meta-chip">⛰️ Elevation</span> }
							{ showDifficulty&& <span className="runpace-meta-chip">⚡ Difficulty</span> }
						</div>
					</Placeholder>
				</div>
			</>
		);
	},

	// No save — fully server-rendered.
	save: () => null,
} );