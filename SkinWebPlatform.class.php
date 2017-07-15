<?php
/**
 * WebPlatform - A skin featuring a stylized table of contents,
 * built-in breadcrumbs, and a vibrant accent palette.
 *
 * @file
 * @ingroup Skins
 */

/**
 * SkinTemplate class for WebPlatform skin
 * @ingroup Skins
 */
class SkinWebPlatform extends SkinTemplate {
	public $skinname = 'webplatform',
		$stylename = 'webplatform',
		$template = 'WebPlatformTemplate',
		$useHeadElement = true;

	/**
	 * Initializes output page and sets up skin-specific parameters
	 * @param $out OutputPage object to initialize
	 */
	public function initPage( OutputPage $out ) {
		global $wgLocalStylePath;

		parent::initPage( $out );

		$out->addHeadItem( 'ie compatibility', '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">' );
		$out->addHeadItem( 'viewport', '<meta name="viewport" content="width=device-width">' );

		/**
		 * These need to be inline stylesheets in the <head>
		 * to load before they're used for proper IE11 support.
		 * Look at the below ticket for the Refreshed skin which
		 * experienced the same problem in the past.
		 *
		 * @see https://phabricator.wikimedia.org/T134653
		 */
		$out->addHeadItem( 'webfontloader-bitter',
			Html::element( 'link', [
				'href' => $wgLocalStylePath . '/webplatform/font-bitter.css',
				'rel' => 'stylesheet'
			] )
		);

		$out->addHeadItem( 'webfontloader-gudea',
			Html::element( 'link', [
				'href' => $wgLocalStylePath . '/webplatform/font-gudea.css',
				'rel' => 'stylesheet'
			] )
		);

		$out->addHeadItem( 'webfontloader-wpsymbols',
			Html::element( 'link', [
				'href' => $wgLocalStylePath . '/webplatform/font-wpsymbols.css',
				'rel' => 'stylesheet'
			] )
		);

		$out->addModuleScripts( 'skins.webplatform.js' );
	}

	/**
	 * Load skin and user CSS files in the correct order
	 * fixes bug 22916
	 * @param $out OutputPage object
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
		$out->addModuleStyles( 'skins.webplatform.css' );
	}
}
