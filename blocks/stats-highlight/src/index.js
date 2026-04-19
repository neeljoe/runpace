/**
 * RunPace – Stats Highlight Block
 * Editor script (index.js) — Full stat item editor with repeater UI.
 */

import { registerBlockType }   from '@wordpress/blocks';
import { useBlockProps, InspectorControls, BlockControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl,
	Button,
	ToolbarGroup,
	ToolbarButton,
	__experimentalDivider as Divider,
} from '@wordpress/components';
import { __ }  from '@wordpress/i18n';
import { plus, trash } from '@wordpress/icons';
import metadata from '../block.json';

const STAT_LIMIT = 8;

function StatItem( { stat, index, onChange, onRemove, isLast } ) {
	return (
		<div className="runpace-stats-editor__item">
			<div className="runpace-stats-editor__row">
				<TextControl
					label={ `#${ index + 1 } ${ __( 'Icon (emoji)', 'runpace' ) }` }
					value={ stat.icon }
					placeholder="🏃"
					onChange={ ( icon ) => onChange( { ...stat, icon } ) }
					style={ { maxWidth: '80px' } }
				/>
				<TextControl
					label={ __( 'Value', 'runpace' ) }
					value={ stat.value }
					placeholder="42KM"
					onChange={ ( value ) => onChange( { ...stat, value } ) }
				/>
				<TextControl
					label={ __( 'Label', 'runpace' ) }
					value={ stat.label }
					placeholder={ __( 'Full marathon', 'runpace' ) }
					onChange={ ( label ) => onChange( { ...stat, label } ) }
				/>
				<Button
					icon={ trash }
					label={ __( 'Remove stat', 'runpace' ) }
					isDestructive
					onClick={ onRemove }
					variant="tertiary"
					style={ { alignSelf: 'flex-end', marginBottom: '2px' } }
				/>
			</div>
			{ ! isLast && <Divider margin="2" /> }
		</div>
	);
}

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const {
			stats,
			layout,
			theme,
			textAlign,
			showDividers,
		} = attributes;

		const blockProps = useBlockProps( {
			className: `runpace-stats-highlight runpace-stats-highlight--${ theme } runpace-stats-highlight--editor`,
		} );

		function updateStat( index, updated ) {
			const next = stats.map( ( s, i ) => ( i === index ? updated : s ) );
			setAttributes( { stats: next } );
		}

		function removeStat( index ) {
			setAttributes( { stats: stats.filter( ( _, i ) => i !== index ) } );
		}

		function addStat() {
			if ( stats.length >= STAT_LIMIT ) return;
			setAttributes( {
				stats: [ ...stats, { value: '', label: '', icon: '' } ],
			} );
		}

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Stats', 'runpace' ) } initialOpen={ true }>
						{ stats.map( ( stat, index ) => (
							<StatItem
								key={ index }
								stat={ stat }
								index={ index }
								onChange={ ( updated ) => updateStat( index, updated ) }
								onRemove={ () => removeStat( index ) }
								isLast={ index === stats.length - 1 }
							/>
						) ) }

						{ stats.length < STAT_LIMIT && (
							<Button
								icon={ plus }
								variant="secondary"
								onClick={ addStat }
								style={ { marginTop: '12px', width: '100%', justifyContent: 'center' } }
							>
								{ __( 'Add stat', 'runpace' ) }
							</Button>
						) }
					</PanelBody>

					<PanelBody title={ __( 'Layout & Theme', 'runpace' ) } initialOpen={ true }>
						<SelectControl
							label={ __( 'Layout', 'runpace' ) }
							value={ layout }
							options={ [
								{ value: 'grid',    label: __( 'Grid (auto)', 'runpace' ) },
								{ value: 'row',     label: __( 'Single row', 'runpace' ) },
								{ value: 'stacked', label: __( '2-column',   'runpace' ) },
							] }
							onChange={ ( v ) => setAttributes( { layout: v } ) }
						/>
						<SelectControl
							label={ __( 'Theme', 'runpace' ) }
							value={ theme }
							options={ [
								{ value: 'dark',        label: __( 'Dark',        'runpace' ) },
								{ value: 'light',       label: __( 'Light',       'runpace' ) },
								{ value: 'brand',       label: __( 'Brand green', 'runpace' ) },
								{ value: 'transparent', label: __( 'Transparent', 'runpace' ) },
							] }
							onChange={ ( v ) => setAttributes( { theme: v } ) }
						/>
						<SelectControl
							label={ __( 'Text align', 'runpace' ) }
							value={ textAlign }
							options={ [
								{ value: 'left',   label: __( 'Left',   'runpace' ) },
								{ value: 'center', label: __( 'Center', 'runpace' ) },
								{ value: 'right',  label: __( 'Right',  'runpace' ) },
							] }
							onChange={ ( v ) => setAttributes( { textAlign: v } ) }
						/>
						<ToggleControl
							label={ __( 'Show dividers between stats', 'runpace' ) }
							checked={ showDividers }
							onChange={ ( v ) => setAttributes( { showDividers: v } ) }
						/>
					</PanelBody>
				</InspectorControls>

				{ /* Live preview in editor */ }
				<div { ...blockProps }>
					<ul
						className="runpace-stats-highlight__list"
						style={ { '--runpace-stat-count': stats.length } }
						role="list"
					>
						{ stats.map( ( stat, i ) => (
							<li key={ i } className="runpace-stats-highlight__item is-visible">
								{ stat.icon && (
									<span className="runpace-stats-highlight__icon" aria-hidden="true">
										{ stat.icon }
									</span>
								) }
								<strong className="runpace-stats-highlight__value">
									{ stat.value || '—' }
								</strong>
								{ stat.label && (
									<span className="runpace-stats-highlight__label">
										{ stat.label }
									</span>
								) }
							</li>
						) ) }
					</ul>
				</div>
			</>
		);
	},

	save: () => null,
} );