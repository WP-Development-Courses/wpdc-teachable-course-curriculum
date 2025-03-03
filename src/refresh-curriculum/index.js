import apiFetch from '@wordpress/api-fetch';
import { Button, TextControl, Spinner } from '@wordpress/components';
import { useSelect, useDispatch, select, dispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useState } from '@wordpress/element';
import { registerPlugin } from '@wordpress/plugins';

const TeachableCourseMetaBox = () => {
	const metaKey = 'wpdc_teachable_course_id';
	const { editPost } = useDispatch( 'core/editor' );
	const [ isFetching, setIsFetching ] = useState( false );

	const metaValue = useSelect(
		( select ) =>
			select( 'core/editor' ).getEditedPostAttribute( 'meta' )[
				metaKey
			] || '',
		[ metaKey ]
	);

	const handleRefreshCurriculum = async () => {
		setIsFetching( true );

		try {
			const postId = select( 'core/editor' ).getCurrentPostId();

			apiFetch.use(
				apiFetch.createNonceMiddleware(
					window.teachableIntegration.nonce
				)
			);
			const response = await apiFetch( {
				path: `/wpdc-teachable/v1/refresh-curriculum/${ postId }`,
				method: 'POST',
			} );

			if ( ! response || ! response.success ) {
				dispatch( 'core/notices' ).createNotice(
					'error',
					'Issue refreshing curriculum: ' + response?.message,
					{
						isDismissible: true,
					}
				);
			} else {
				dispatch( 'core/notices' ).createNotice(
					'success',
					'Curriculum refreshed successfully.',
					{
						isDismissible: true,
					}
				);

				dispatch( 'core' ).invalidateResolution( 'getEntityRecord', [
					'postType',
					select( 'core/editor' ).getCurrentPostType(),
					postId,
				] );
			}
		} catch ( err ) {
			dispatch( 'core/notices' ).createNotice(
				'error',
				'Issue refreshing curriculum: ' + err.message,
				{
					isDismissible: true,
				}
			);
		} finally {
			setIsFetching( false );
		}
	};

	return (
		<PluginDocumentSettingPanel
			name="wpdc-teachable-integration"
			title="Teachable Integration"
			className="wpdc-teachable-integration"
		>
			<TextControl
				label={ 'Enter Teachable Course ID:' }
				value={ metaValue !== 0 ? metaValue : '' }
				type="number"
				onChange={ ( value ) =>
					editPost( {
						meta: {
							[ metaKey ]: value ? parseInt( value, 10 ) : 0,
						},
					} )
				}
			/>

			<Button
				isPrimary
				disabled={ ! metaValue || isFetching }
				onClick={ handleRefreshCurriculum }
			>
				{ isFetching ? <Spinner /> : 'Refresh curriculum' }
			</Button>
		</PluginDocumentSettingPanel>
	);
};
registerPlugin( 'wpdc-teachable-integration-metabox', {
	render: TeachableCourseMetaBox,
} );
