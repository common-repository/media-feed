import './media-feed-admin.scss';

import apiFetch from '@wordpress/api-fetch';

import { ToggleControl, TextControl, TextareaControl, RangeControl } from '@wordpress/components';

import {
	render,
	useState,
	useEffect
} from '@wordpress/element';

import Credit from './components/credit';

const MediaFeedAdmin = () => {

	const mediafeed_options = JSON.parse( mediafeedadmin_data.options );
	const mediafeed_links = JSON.parse( mediafeedadmin_data.links );

	const mediafeed_options_apply = mediafeed_options.apply;
	const [ checkedItems, setCheckedItems ] = useState( mediafeed_options_apply );

	const mediafeed_options_text = mediafeed_options.text;
	const [ textItems, setTextItems ] = useState( mediafeed_options_text );

	const mediafeed_options_per_rss = mediafeed_options.per_rss;
	const [ perrssItems, setPerrssItems ] = useState( mediafeed_options_per_rss );

	const [ excludeidItem, setExcludeidItem ] = useState( mediafeed_options.exclude_id );
	const [ termfilterItem, setTermfilterItem ] = useState( mediafeed_options.term_filter );

	useEffect( () => {
		apiFetch( {
			path: 'rf/mediafeed_api/token',
			method: 'POST',
			data: {
				apply: checkedItems,
				text: textItems,
				per_rss: perrssItems,
				exclude_id: excludeidItem,
				term_filter: termfilterItem,
			}
		} ).then( ( response ) => {
			//console.log( response );
		} );
	}, [ checkedItems, textItems, perrssItems, excludeidItem, termfilterItem ] );

	const items_apply = [];
	Object.keys( mediafeed_options_apply ).map(
		( key ) => {
			if( mediafeed_options_apply.hasOwnProperty( key ) ) {
				items_apply.push(
					<td>
					<ToggleControl
						label={ key }
						checked={ checkedItems[ key ] }
						onChange={ ( value ) =>
							{
								checkedItems[ key ] = value;
								let data = Object.assign( {}, checkedItems );
								setCheckedItems( data );
							}
						}
					/>
					</td>
				);
			}
		}
	);
	//console.log( checkedItems );

	const items_text = [];
	Object.keys( mediafeed_options_text ).map(
		( key ) => {
			if( mediafeed_options_text.hasOwnProperty( key ) ) {
				items_text.push(
					<td>
					<TextControl
						value={ textItems[ key ] }
						onChange={ ( value ) =>
							{
								textItems[ key ] = value;
								let data = Object.assign( {}, textItems );
								setTextItems( data );
							}
						}
					/>
					</td>
				);
			}
		}
	);
	//console.log( textItems );

	const items_per_rss = [];
	Object.keys( mediafeed_options_per_rss ).map(
		( key ) => {
			if( mediafeed_options_per_rss.hasOwnProperty( key ) ) {
				items_per_rss.push(
					<td>
					<RangeControl
						max = { 100 }
						min = { 1 }
						value={ perrssItems[ key ] }
						onChange={ ( value ) =>
							{
								perrssItems[ key ] = value;
								let data = Object.assign( {}, perrssItems );
								setPerrssItems( data );
							}
						}
					/>
					</td>
				);
			}
		}
	);
	//console.log( perrssItems );

	const items = [];
	for ( let i = 0; i < 5; i++ ) {
		items.push(
			<tr>
			{ items_apply[ i ] }
			<td align="center">
			<a className="aStyle" href={ mediafeed_links[ i ] } target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-external"></span></a>
			</td>
			{ items_text[ i ] }
			{ items_per_rss[ i ] }
			</tr>
		);
	}

	return (
		<div className="wrap">
		<h2>Media Feed</h2>
			<Credit />
			<div className="wrap">
				<h2>{ mediafeedadmin_text.settings }</h2>
				<details className="detailsStyle" open>
				<summary className="summaryStyle">{ mediafeedadmin_text.apply } & { mediafeedadmin_text.feedname } & { mediafeedadmin_text.numfeeds }</summary>
				<div className="detailsdivStyle">
				<table border="1" cellspacing="0" cellpadding="5" bordercolor="#000000" className="tableStyle">
				<tr>
				<td align="center">{ mediafeedadmin_text.apply }</td>
				<td align="center">{ mediafeedadmin_text.feed }</td>
				<td align="center">{ mediafeedadmin_text.feedname }</td>
				<td align="center">{ mediafeedadmin_text.feedrecent }</td>
				</tr>
				{ items }
				</table>
				<div><a className="aStyle" href={ mediafeedadmin_text.permlink }>{ mediafeedadmin_text.permlink_description }</a></div>
				</div>
				</details>
				<details className="detailsStyle">
				<summary className="summaryStyle">{ mediafeedadmin_text.exclude }</summary>
				<div className="detailsdivStyle">
				<div>{ mediafeedadmin_text.exclude_description1 }</div>
				<TextareaControl
					value={ excludeidItem }
					onChange={ ( value ) => setExcludeidItem( value ) }
				/>
				<div><a className="aStyle" href={ mediafeedadmin_text.medialibrary }>{ mediafeedadmin_text.exclude_description2 }</a></div>
				</div>
				</details>
				<details className="detailsStyle">
				<summary className="summaryStyle">{ mediafeedadmin_text.termlist }</summary>
				<div className="detailsdivStyle">
				<div>{ mediafeedadmin_text.termlist_description }</div>
				<TextareaControl
					value={ termfilterItem }
					onChange={ ( value ) => setTermfilterItem( value ) }
				/>
				</div>
				</details>
				<details className="detailsStyle">
				<summary className="summaryStyle">{ mediafeedadmin_text.shortcode }</summary>
				<div className="detailsdivStyle">
				<div>{ mediafeedadmin_text.shortcode_description1 }</div>
				<h4>{ mediafeedadmin_text.shortcode_description2 }</h4>
				<div><code>[mediafeedlist slug="video"]</code></div>
				<div><code>slug</code> { mediafeedadmin_text.shortcode_description3 }</div>
				<div><code>media</code> { mediafeedadmin_text.media }</div>
				<div><code>image</code> { mediafeedadmin_text.image }</div>
				<div><code>audio</code> { mediafeedadmin_text.audio }</div>
				<div><code>video</code> { mediafeedadmin_text.video }</div>
				<div><code>misc</code> { mediafeedadmin_text.misc }</div>
				</div>
				</details>
			</div>
		</div>
	);

};

render(
	<MediaFeedAdmin />,
	document.getElementById( 'mediafeedadmin' )
);

