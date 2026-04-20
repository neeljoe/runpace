/**
 * Stats Highlight Block – Editor
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, Button, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

const THEMES = [
	{ label: __( 'Dark', 'runpace' ),    value: 'dark' },
	{ label: __( 'Light', 'runpace' ),   value: 'light' },
	{ label: __( 'Primary', 'runpace' ), value: 'primary' },
	{ label: __( 'Split', 'runpace' ),   value: 'split' },
];

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const { stats, theme, animateOnScroll } = attributes;
		const blockProps = useBlockProps( { className: `runpace-stats runpace-stats--${ theme } is-revealed` } );

		const updateStat = ( index, key, value ) => {
			const next = stats.map( ( s, i ) => i === index ? { ...s, [ key ]: value } : s );
			setAttributes( { stats: next } );
		};

		const addStat = () => setAttributes( { stats: [ ...stats, { value: '', label: '', icon: '' } ] } );
		const removeStat = ( index ) => setAttributes( { stats: stats.filter( ( _, i ) => i !== index ) } );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Appearance', 'runpace' ) }>
						<SelectControl
							label={ __( 'Theme', 'runpace' ) }
							value={ theme }
							options={ THEMES }
							onChange={ ( val ) => setAttributes( { theme: val } ) }
						/>
						<ToggleControl
							label={ __( 'Animate on scroll', 'runpace' ) }
							checked={ animateOnScroll }
							onChange={ ( val ) => setAttributes( { animateOnScroll: val } ) }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Stats', 'runpace' ) }>
						{ stats.map( ( stat, i ) => (
							<div key={ i } style={ { marginBottom: 16, padding: '12px', background: '#f0f0f0', borderRadius: 4 } }>
								<TextControl label={ __( 'Value', 'runpace' ) } value={ stat.value } onChange={ ( v ) => updateStat( i, 'value', v ) } />
								<TextControl label={ __( 'Label', 'runpace' ) } value={ stat.label } onChange={ ( v ) => updateStat( i, 'label', v ) } />
								<TextControl label={ __( 'Icon (emoji)', 'runpace' ) } value={ stat.icon }  onChange={ ( v ) => updateStat( i, 'icon',  v ) } />
								<Button isDestructive isSmall onClick={ () => removeStat( i ) }>
									{ __( 'Remove', 'runpace' ) }
								</Button>
							</div>
						) ) }
						<Button isPrimary onClick={ addStat }>{ __( '+ Add stat', 'runpace' ) }</Button>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					<ul className="runpace-stats__list" role="list">
						{ stats.map( ( stat, i ) => (
							<li key={ i } className="runpace-stats__item" style={ { '--item-index': i } }>
								{ stat.icon && <span className="runpace-stats__icon" aria-hidden="true">{ stat.icon }</span> }
								<span className="runpace-stats__value">{ stat.value || '—' }</span>
								<span className="runpace-stats__label">{ stat.label || __( 'Label', 'runpace' ) }</span>
							</li>
						) ) }
					</ul>
				</div>
			</>
		);
	},
	save() { return null; },
} );