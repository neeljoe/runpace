/**
 * RunPace – Featured Marathon Block
 * Editor script (index.js)
 */

import { registerBlockType }   from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	RangeControl,
	TextControl,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { useSelect }  from '@wordpress/data';
import { __ }         from '@wordpress/i18n';
import metadata       from '../block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const {
			postId,
			showCountdown,
			showDistanceBadge,
			showExcerpt,
			showMeta,
			ctaLabel,
			learnMoreLabel,
			overlayOpacity,
		} = attributes;

		const blockProps = useBlockProps( {
			className: 'runpace-featured-marathon runpace-featured-marathon--editor',
		} );

		// Load post data for the preview label.
		const post = useSelect(
			( select ) => {
				if ( ! postId ) return null;
				return select( 'core' ).getEntityRecord( 'postType', 'marathon', postId );
			},
			[ postId ]
		);

		const isLoading = postId && ! post;

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Content', 'runpace' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'Show countdown', 'runpace' ) }
							checked={ showCountdown }
							onChange={ ( val ) => setAttributes( { showCountdown: val } ) }
						/>
						<ToggleControl
							label={ __( 'Show distance badge', 'runpace' ) }
							checked={ showDistanceBadge }
							onChange={ ( val ) => setAttributes( { showDistanceBadge: val } ) }
						/>
						<ToggleControl
							label={ __( 'Show excerpt', 'runpace' ) }
							checked={ showExcerpt }
							onChange={ ( val ) => setAttributes( { showExcerpt: val } ) }
						/>
						<ToggleControl
							label={ __( 'Show meta (date / price)', 'runpace' ) }
							checked={ showMeta }
							onChange={ ( val ) => setAttributes( { showMeta: val } ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Labels', 'runpace' ) } initialOpen={ false }>
						<TextControl
							label={ __( 'Register button label', 'runpace' ) }
							value={ ctaLabel }
							onChange={ ( val ) => setAttributes( { ctaLabel: val } ) }
						/>
						<TextControl
							label={ __( '"Learn more" link label', 'runpace' ) }
							value={ learnMoreLabel }
							onChange={ ( val ) => setAttributes( { learnMoreLabel: val } ) }
						/>
					</PanelBody>

					<PanelBody title={ __( 'Image overlay', 'runpace' ) } initialOpen={ false }>
						<RangeControl
							label={ __( 'Overlay opacity (%)', 'runpace' ) }
							value={ overlayOpacity }
							min={ 0 }
							max={ 90 }
							step={ 5 }
							onChange={ ( val ) => setAttributes( { overlayOpacity: val } ) }
						/>
					</PanelBody>
				</InspectorControls>

				<div { ...blockProps }>
					{ isLoading ? (
						<Placeholder icon="awards" label={ __( 'Featured Marathon', 'runpace' ) }>
							<Spinner />
						</Placeholder>
					) : (
						<Placeholder
							icon="awards"
							label={ __( 'Featured Marathon', 'runpace' ) }
							instructions={ post
								? sprintf( __( 'Showing: %s', 'runpace' ), post.title?.rendered ?? post.slug )
								: __( 'Displays the latest marathon with "Featured" enabled, or pick one from the sidebar Post Picker.', 'runpace' )
							}
						>
							<div style={ { display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '8px' } }>
								{ showDistanceBadge && <span className="runpace-meta-chip">Distance badge</span> }
								{ showCountdown    && <span className="runpace-meta-chip">Countdown</span> }
								{ showMeta         && <span className="runpace-meta-chip">Date · Price</span> }
								{ showExcerpt      && <span className="runpace-meta-chip">Excerpt</span> }
							</div>
						</Placeholder>
					) }
				</div>
			</>
		);
	},

	save: () => null,
} );