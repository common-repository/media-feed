import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { ToggleControl, RadioControl, PanelBody } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	return (
		<div { ...blockProps }>
			<ServerSideRender
				block = 'media-feed/media-feed-icon-block'
				attributes = { attributes }
			/>

			<InspectorControls>
				<PanelBody title = { __( 'Type', 'media-feed' ) } initialOpen = { false }>
					<ToggleControl
						label = { __( 'Media', 'media-feed' ) }
						checked = { attributes.media }
						onChange = { ( value ) => setAttributes( { media: value } ) }
					/>
					<ToggleControl
						label = { __( 'Image', 'media-feed' ) }
						checked = { attributes.image }
						onChange = { ( value ) => setAttributes( { image: value } ) }
					/>
					<ToggleControl
						label = { __( 'Audio', 'media-feed' ) }
						checked = { attributes.audio }
						onChange = { ( value ) => setAttributes( { audio: value } ) }
					/>
					<ToggleControl
						label = { __( 'Video', 'media-feed' ) }
						checked = { attributes.video }
						onChange = { ( value ) => setAttributes( { video: value } ) }
					/>
					<ToggleControl
						label = { __( 'Misc', 'media-feed' ) }
						checked = { attributes.misc }
						onChange = { ( value ) => setAttributes( { misc: value } ) }
					/>
				</PanelBody>
				<PanelBody title = { __( 'View', 'media-feed' ) } initialOpen = { false }>
					<RadioControl
						label = { __( 'Align', 'media-feed' ) }
						selected = { attributes.align }
						onChange = { ( value ) => setAttributes( { align: value } ) }
						options = { [
						{ label: __( 'Align left', 'media-feed' ), value: 'left' },
						{ label: __( 'Align center', 'media-feed' ), value: 'center' },
						{ label: __( 'Align right', 'media-feed' ), value: 'right' },
						] }
					/>
				</PanelBody>
			</InspectorControls>
		</div>
	);
}
