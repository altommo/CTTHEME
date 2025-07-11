wp.domReady( () => {
  const { addFilter } = wp.hooks;
  const { createHigherOrderComponent } = wp.compose;
  const { BlockPreview } = wp.blockEditor;

  const withEmbedError = createHigherOrderComponent( ( BlockListBlock ) => {
    return ( props ) => {
      if ( props.name === 'core/embed' && props.attributes.providerNameSlug === 'youtube' ) {
        return (
          <BlockPreview
            { ...props }
            fetchPreviewSuccess={ false }
            fetchPreviewError={ () =>
              <div className="embed-error">
                <p><strong>Embed failed.</strong> Use a clean YouTube URL.</p>
              </div>
            }
          />
        );
      }
      return <BlockListBlock { ...props } />;
    };
  }, 'withEmbedError' );

  addFilter(
    'editor.BlockListBlock',
    'customtube/embed-error',
    withEmbedError
  );
} );
