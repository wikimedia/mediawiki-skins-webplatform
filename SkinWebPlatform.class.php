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
		$template = 'WebPlatformTemplate';

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

	/**
	 * Parses MediaWiki:Webplatform-bottom-footer
	 * @return array
	 */
	public function getBottomFooterLinks() {
		$nodes = [];
		$lines = $this->getMessageAsArray( 'Webplatform-bottom-footer' );
		if ( !$lines ) {
			return $nodes;
		}

		foreach ( $lines as $line ) {
			$depth = strrpos( $line, '*' );
			if ( $depth === 0 ) {
				$nodes[] = $this->parseItem( $line );
			}
		}

		return $nodes;
	}

	/**
	 * Parse one line from MediaWiki message to array with indexes 'text', 'href',
	 * 'org' and 'desc'
	 *
	 * @param string $line Name of a MediaWiki: message
	 * @return array
	 */
	public function parseItem( $line ) {
		$href = false;

		// trim spaces and asterisks from line and then split it to maximum three chunks
		$line_temp = explode( '|', trim( $line, '* ' ), 3 );

		// trim [ and ] from line to have just http://en.wikipedia.org instead
		// of [http://en.wikipedia.org] for external links
		$line_temp[0] = trim( $line_temp[0], '[]' );

		// $line_temp now contains page name or URL as the 0th array element
		// and the link description as the 1st array element
		if ( count( $line_temp ) >= 2 && $line_temp[1] != '' ) {
			$msgObj = $this->msg( $line_temp[0] );
			$link = ( $msgObj->isDisabled() ? $line_temp[0] : trim( $msgObj->inContentLanguage()->text() ) );
			$textObj = $this->msg( trim( $line_temp[1] ) );
			$line = ( !$textObj->isDisabled() ? $textObj->text() : trim( $line_temp[1] ) );
		} else {
			$line = $link = trim( $line_temp[0] );
		}

		$descText = null;
		if ( count( $line_temp ) > 2 && $line_temp[2] != '' ) {
			$desc = $line_temp[2];
			$descObj = $this->msg( $desc );
			if ( $descObj->isDisabled() ) {
				$descText = $desc;
			} else {
				$descText = $descObj->text();
			}
		}

		$lineObj = $this->msg( $line );
		if ( $lineObj->isDisabled() ) {
			$text = $line;
		} else {
			$text = $lineObj->text();
		}

		if ( $link != null ) {
			if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
				$href = $link;
			} else {
				$title = Title::newFromText( $link );
				if ( $title ) {
					$title = $title->fixSpecialName();
					$href = $title->getLocalURL();
				} else {
					$href = '#';
				}
			}
		}

		return [
			'text' => $text,
			'href' => $href,
			'org' => $line_temp[0],
			'desc' => $descText
		];
	}

	/**
	 * @param string $messageKey Name of a MediaWiki: message
	 * @return array|null Array if $messageKey has been given, otherwise null
	 */
	public function getMessageAsArray( $messageKey ) {
		$messageObj = $this->msg( $messageKey )->inContentLanguage();
		if ( !$messageObj->isDisabled() ) {
			$lines = explode( "\n", $messageObj->text() );
			if ( count( $lines ) > 0 ) {
				return $lines;
			}
		}
		return null;
	}
}
