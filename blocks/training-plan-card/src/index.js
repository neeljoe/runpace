/**
 * RunPace – Training Plan Card Block
 * Editor script (index.js)
 */

import { registerBlockType }   from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
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
			showDuration,
			showLevel,
			showGoal,
			showSessions,
			showPeakKm,
			showFreeBadge,
			showDownload,
			ctaLabel,
			downloadLabel,
			colorScheme,
		} = attributes;

		const blockProps = useBlockProps( {
			className: `runpace-training-plan-card runpace-training-plan-card--${ colorScheme } runpace-training-plan-card--editor`,
		} );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Visible fields', 'runpace' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'Duration (weeks)', 'runpace' ) }
							checked={ showDuration }
							onChange={ ( v ) => setAttributes( { showDuration: v } ) }
						/>
						<ToggleControl
							label={ __( 'Level badge', 'runpace' ) }
							checked={ showLevel }
							onChange={ ( v ) => setAttributes( { showLevel: v } ) }
						/>
						<ToggleControl
							label={ __( 'Goal', 'runpace' ) }
							checked={ showGoal }
							onChange={ ( v ) => setAttributes( { showGoal: v } ) }
						/>
						<ToggleControl
							label={ __( 'Sessions per week', 'runpace' ) }
							checked={ showSessions }
							onChange={ ( v ) => setAttributes( { showSessions: v } ) }
						/>
						<ToggleControl
							label={ __( 'Peak km/week', 'runpace' ) }
							checked={ showPeakKm }
							onChange={ ( v ) => setAttributes( { showPeakKm: v } ) }
						/>
						<ToggleControl
							label={ __( 'Free / Premium badge', 'runpace' ) }
							checked={ showFreeBadge }
							onChange={ ( v ) => setAttributes( { showFreeBadge: v } ) }
						/>
						<ToggleControl
							label={ __( 'Download button', 'runpace' ) }
							checked={ showDownload }
							onChange={ ( v ) => setAttributes( { showDownload: v } ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Appearance', 'runpace' ) } initialOpen={ false }>
						<SelectControl
							label={ __( 'Colour scheme', 'runpace' ) }
							value={ colorScheme }
							options={ [
								{ value: 'auto',   label: __( 'Auto (follows theme)', 'runpace' ) },
								{ value: 'light',  label: __( 'Light',   'runpace' ) },
								{ value: 'dark',   label: __( 'Dark',    'runpace' ) },
								{ value: 'accent', label: __( 'Accent',  'runpace' ) },
							] }
							onChange={ ( v ) => setAttributes( { colorScheme: v } ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Labels', 'runpace' ) } initialOpen={ false }>
						<TextControl
							label={ __( 'CTA button label', 'runpace' ) }
							value={ ctaLabel }
							onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
						/>
						{ showDownload && (
							<TextControl
								label={ __( 'Download button label', 'runpace' ) }
								value={ downloadLabel }
								onChange={ ( v ) => setAttributes( { downloadLabel: v } ) }
							/>
						) }
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<Placeholder
						icon="clipboard"
						label={ __( 'Training Plan Card', 'runpace' ) }
						instructions={ __( 'Displays training plan metadata from the current post. Use inside a Query Loop targeting the training-plan post type.', 'runpace' ) }
					>
						<div style={ { display: 'flex', flexWrap: 'wrap', gap: '8px' } }>
							{ showLevel    && <span className="runpace-meta-chip">Level badge</span> }
							{ showDuration && <span className="runpace-meta-chip">Duration</span> }
							{ showSessions && <span className="runpace-meta-chip">Sessions/wk</span> }
							{ showPeakKm   && <span className="runpace-meta-chip">Peak km</span> }
							{ showGoal     && <span className="runpace-meta-chip">Goal</span> }
							{ showDownload && <span className="runpace-meta-chip">PDF download</span> }
						</div>
					</Placeholder>
				</div>
			</>
		);
	},

	save: () => null,
} );