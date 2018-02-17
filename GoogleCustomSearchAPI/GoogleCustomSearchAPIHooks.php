<?php 

class GoogleCustomSearchAPIHooks {
    public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
        $dir = $GLOBALS['wgExtensionDirectory'] . DIRECTORY_SEPARATOR . 'GoogleCustomSearchAPI' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        $updater->addExtensionTable( 'google_custome_search_api', $dir . 'GoogleCustomSearchAPI.sql',true ); 
	    return true;
    }
}