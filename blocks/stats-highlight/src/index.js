/**
 * RunPace – Stats Highlight block editor script.
 *
 * Provides the block edit UI with live-editable stat items.
 */

import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InspectorControls,
	PanelColorSettings,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Button,
	RangeControl,
	ToggleControl,
	__experimentalItemGroup as ItemGroup,
	__experimentalItem as Item,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { plus, trash } from '@wordpress/icons';
import metadata from './block.json';

function Edit( { attributes, setAttributes } ) {
	const { stats, animationEnabled, columns } = attributes;

	const blockProps = useBlockProps( {
		className: 'runpace-stats runpace-stats--editor',
		'data-columns': String( columns ),
	} );

	function updateStat( index, key, value ) {
		const updated = stats.map( ( s, i ) =>
			i === index ? { ...s, [ key ]: value } : s
		);
		setAttributes( { stats: updated } );
	}

	function addStat() {
		setAttributes( {
			stats: [ ...stats, { value: '0', suffix: '', label: 'New stat' } ],
		} );
	}

	function removeStat( index ) {
		setAttributes( { stats: stats.filter( ( _, i ) => i !== index ) } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'runpace' ) } initialOpen>
					<RangeControl
						label={ __( 'Columns', 'runpace' ) }
						value={ columns }
						onChange={ ( v ) => setAttributes( { columns: v } ) }
						min={ 1 }
						max={ 6 }
					/>
					<ToggleControl
						label={ __( 'Scroll-reveal animation', 'runpace' ) }
						help={ animationEnabled
							? __( 'Stats count up when scrolled into view.', 'runpace' )
							: __( 'Stats display immediately.', 'runpace' ) }
						checked={ animationEnabled }
						onChange={ ( v ) => setAttributes( { animationEnabled: v } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Stats', 'runpace' ) } initialOpen>
					<ItemGroup>
						{ stats.map( ( stat, i ) => (
							<Item key={ i }>
								<TextControl
									label={ __( 'Value', 'runpace' ) }
									value={ stat.value }
									onChange={ ( v ) => updateStat( i, 'value', v ) }
								/>
								<TextControl
									label={ __( 'Suffix', 'runpace' ) }
									value={ stat.suffix }
									placeholder="+  KM  %"
									onChange={ ( v ) => updateStat( i, 'suffix', v ) }
								/>
								<TextControl
									label={ __( 'Label', 'runpace' ) }
									value={ stat.label }
									onChange={ ( v ) => updateStat( i, 'label', v ) }
								/>
								<Button
									isDestructive
									variant="tertiary"
									icon={ trash }
									onClick={ () => removeStat( i ) }
									disabled={ stats.length <= 1 }
								>
									{ __( 'Remove', 'runpace' ) }
								</Button>
							</Item>
						) ) }
					</ItemGroup>

					<Button
						variant="secondary"
						icon={ plus }
						onClick={ addStat }
						disabled={ stats.length >= 6 }
					>
						{ __( 'Add stat', 'runpace' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<ul
					className="runpace-stats__grid"
					style={ { '--stats-columns': columns } }
				>
					{ stats.map( ( stat, i ) => (
						<li key={ i } className="runpace-stats__item">
							<span className="runpace-stats__number">
								<span className="runpace-stats__value">{ stat.value }</span>
								{ stat.suffix && (
									<span className="runpace-stats__suffix">
										{ stat.suffix }
									</span>
								) }
							</span>
							{ stat.label && (
								<span className="runpace-stats__label">
									{ stat.label }
								</span>
							) }
						</li>
					) ) }
				</ul>
			</div>
		</>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null, // Dynamic block — server rendered.
} );