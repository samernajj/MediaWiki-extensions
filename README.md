# MediaWiki-extensions
MediaWiki extensions

## Getting Started
These instructions will get you a copy of the extensions on your local machine for development and testing purposes.

1- go to the extensions directory
```
cd extensions
```

2- clone the repository 
```
git clone https://github.com/samernajj/MediaWiki-extensions.git
```

3- Add the following code at the bottom of your **LocalSettings.php** and replace (API_KEY,CX)  
```
wfLoadExtension( 'GoogleCustomSearchAPI' );
wfLoadExtension( 'SavedResult' );
$wgGoogleSearchEndPoint='https://www.googleapis.com/customsearch/v1';
$wgGoogleSearchKey= API_KEY;
$wgGoogleSearchCX= CX;
```
4- Run the **[update script](https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Update.php)** which will automatically create the necessary database tables that this extension needs.

5- **Done** - Navigate to **Special:Version** on your wiki to verify that the extension is successfully installed.

## Author

* **Samer Najjar** - [samernajj](https://github.com/samernajj)
