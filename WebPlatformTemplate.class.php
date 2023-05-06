<?php
/**
 * WebPlatform - A skin featuring a stylized table of contents,
 * built-in breadcrumbs, and a vibrant accent palette.
 *
 * @file
 * @ingroup Skins
 */

use MediaWiki\MediaWikiServices;

/**
 * QuickTemplate class for WebPlatform skin
 * @ingroup Skins
 */
class WebPlatformTemplate extends BaseTemplate {
	/* Functions */
	/**
	 * Outputs the entire contents of the (X)HTML page
	 */
	public function execute() {
		global $wgVectorUseIconWatch;

		$skin = $this->getSkin();
		$config = $skin->getConfig();
		$title = $skin->getTitle();

		// Build additional attributes for navigation urls
		$nav = $this->data['content_navigation'];

		if ( $wgVectorUseIconWatch ) {
			$user = $skin->getUser();

			$mode = MediaWikiServices::getInstance()->getWatchlistManager()
				->isWatched( $user, $title ) ? 'unwatch' : 'watch';

			if ( isset( $nav['actions'][$mode] ) ) {
				$nav['views'][$mode] = $nav['actions'][$mode];
				$nav['views'][$mode]['class'] = rtrim( 'icon ' . $nav['views'][$mode]['class'], ' ' );
				$nav['views'][$mode]['primary'] = true;
				unset( $nav['actions'][$mode] );
			}
		}

		$xmlID = '';
		foreach ( $nav as $section => $links ) {
			foreach ( $links as $key => $link ) {
				if ( $section == 'views' && !( isset( $link['primary'] ) && $link['primary'] ) ) {
					// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset
					$link['class'] = rtrim( 'collapsible ' . $link['class'], ' ' );
				}
				$xmlID = $link['id'] ?? 'ca-' . $xmlID;
				$nav[$section][$key]['attributes'] =
					' id="' . Sanitizer::escapeIdForAttribute( $xmlID ) . '"';
				// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset
				if ( $link['class'] ) {
					$nav[$section][$key]['attributes'] .=
						// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset
						' class="' . htmlspecialchars( $link['class'] ) . '"';
					unset( $nav[$section][$key]['class'] );
				}
				if ( isset( $link['tooltiponly'] ) && $link['tooltiponly'] ) {
					$nav[$section][$key]['key'] =
						Linker::tooltip( $xmlID );
				} else {
					$nav[$section][$key]['key'] =
						Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( $xmlID ) );
				}
			}
		}
		$this->data['namespace_urls'] = $nav['namespaces'];
		$this->data['view_urls'] = $nav['views'];
		$this->data['action_urls'] = $nav['actions'];
		$this->data['variant_urls'] = $nav['variants'];
		// Reverse horizontally rendered navigation elements
		if ( $this->data['rtl'] ) {
			$this->data['view_urls'] =
				array_reverse( $this->data['view_urls'] );
			$this->data['namespace_urls'] =
				array_reverse( $this->data['namespace_urls'] );
			$this->data['personal_urls'] =
				array_reverse( $this->data['personal_urls'] );
		}

		// Output HTML Page
		?>
		<div id="mw-page-base" class="noprint"></div>
		<div id="mw-head-base" class="noprint"></div>
		<!-- header -->
		<header id="mw-head" class="noprint">
			<div class="container">
				<!-- logo -->
				<div id="p-logo">
				<a class="mw-wiki-logo"
					href="<?php
						// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
						echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>" <?php
						// @phan-suppress-next-line SecurityCheck-XSS Per FlaggedRevs, this looks like a false positive
						echo Xml::expandAttributes( Linker::tooltipAndAccesskeyAttribs( 'p-logo' ) ) ?>></a>
				</div>
				<!-- /logo -->

				<?php $this->renderHeaderMenu(); ?>
				<?php $this->renderSearch(); ?>
			</div>
		</header>
		<!-- /header -->

		<nav id="sitenav">
			<div class="container">
				<ul class="links">
				<?php
				'@phan-var SkinWebPlatform $skin';
				$siteNavLinks = $skin->buildSiteNavigation();
				foreach ( $siteNavLinks as $link ) {
					$class = '';
					if ( !empty( $link['class'] ) && $link['class'] && $link['class'] !== '' ) {
						// Currently (February 2023) $link['class'] can only ever
						// be either empty or 'active', so this is fine.
						$class .= ' class="' . $link['class'] . '"';
					}

					$text = '';
					if ( isset( $link['text'] ) && $link['text'] ) {
						$text = htmlspecialchars( $link['text'], ENT_QUOTES );
					}

					// @phan-suppress-next-line SecurityCheck-XSS Yes, it looks scary but based on testing, it's fine
					echo "<li><a href=\"{$link['href']}\"{$class}>{$text}</a></li>";
				}
				?>
				</ul>
			</div>
		</nav>

		<!-- content -->
		<div id="content-container" class="mw-body">
			<div class="container">
				<a id="top"></a>
				<div class="tool-area">
					<div id="hierarchy-menu">
						<ol id="breadcrumb-info" class="breadcrumbs">
							<li><a href="<?php
								// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
								echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>"><?php echo $config->get( 'Sitename' ) ?></a></li>
							<?php
								// Only if enabled in config
								if ( $config->get( 'WebPlatformEnableBreadcrumbs' ) ) {
									echo $this->buildBreadcrumbMenu( $title );
								}
							?>
						</ol>
					</div>

					<div class="toolbar">
						<?php $this->renderEditButton(); ?>
						<?php $this->renderWatchButton(); ?>
						<?php $this->renderToolMenu(); ?>
					</div>
				</div>
				<div id="page">
					<div id="content">
						<div id="main-content">
							<?php if ( $this->data['sitenotice'] ): ?>
							<!-- sitenotice -->
							<div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div>
							<!-- /sitenotice -->
							<?php endif; ?>
							<?php echo $this->getIndicators(); ?>
							<!-- firstHeading -->
							<?php
							// Loose comparison with '!=' is intentional, to catch null and false too, but not '0'
							if ( $this->data['title'] != '' ) {
								echo Html::rawElement( 'h1',
									[
										'id' => 'firstHeading',
										'class' => 'firstHeading',
										'lang' => $this->get( 'pageLanguage' ),
									],
									// Raw HTML
									$this->get( 'title' )
								);
							}
							?>
							<!-- /firstHeading -->

							<!-- subtitle -->
							<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php $this->html( 'subtitle' ) ?></div>
							<!-- /subtitle -->

							<!-- bodyContent -->
							<div id="bodyContent">

								<?php if ( $this->data['undelete'] ): ?>
									<!-- undelete -->
									<div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
									<!-- /undelete -->
								<?php endif; ?>

								<?php if ( $this->data['newtalk'] ): ?>
									<!-- newtalk -->
									<div class="usermessage"><?php $this->html( 'newtalk' )	?></div>
									<!-- /newtalk -->
								<?php endif; ?>

								<!-- jumpto -->
								<div id="jump-to-nav"></div>
								<a class="mw-jump-link" href="#mw-head"><?php $this->msg( 'webplatform-jump-to-navigation' ) ?></a>
								<a class="mw-jump-link" href="#p-search"><?php $this->msg( 'webplatform-jump-to-search' ) ?></a>
								<!-- /jumpto -->

								<!-- bodycontent -->
								<?php $this->html( 'bodycontent' ) ?>
								<!-- /bodycontent -->

								<?php if ( $this->data['printfooter'] ): ?>
									<!-- printfooter -->
									<div class="printfooter">
										<?php $this->html( 'printfooter' ); ?>
									</div>
									<!-- /printfooter -->
								<?php endif; ?>

								<?php
								if ( $this->data['catlinks'] ) {
									$this->html( 'catlinks' );
								}

								if ( $this->data['dataAfterContent'] ) {
									$this->html( 'dataAfterContent' );
								}
								?>

								<?php
								// Bottom navigation footer, if enabled
								'@phan-var SkinWebPlatform $skin';
								$links = $skin->getBottomFooterLinks();
								if ( count( $links ) > 0 ) {
								?>
								<div class="topics-nav">
									<ul>
									<?php
									foreach ( $links as $key => $val ) {
										echo $this->makeListItem( $key, $val );
									}
									?>
									</ul>
								</div>
								<?php
								}
								?>

								<div class="visualClear"></div>

						<!-- debughtml -->
						<?php $this->html( 'debughtml' ); ?>
						<!-- /debughtml -->
							</div>
					<!-- /bodyContent -->
						</div>
				<!-- /main content -->
					<div class="clear"></div>
					</div>
				<!-- /page content -->
				</div>
			<!-- /page -->
			</div>
			<!-- /container -->
		</div>
		<!-- /content -->

		<!-- footer -->
		<footer id="mw-footer"<?php $this->html( 'userlangattributes' ) ?>>
			<div class="container">
				<div id="footer-wordmark">
					<?php
						$icons = $this->get( 'footericons' );
						$icon = $icons['copyright'][0] ?? null;
						if ( $icon ) {
							echo $this->getSkin()->makeFooterIcon( $icon );
						}
					?>
					<a href="http://webplatform.org/">
						<span id="footer-title">WebPlatform
							<span id="footer-title-light">.org</span>
						</span>
					</a>
				</div>

				<ul class="stewards">
					<li class="steward-w3c"><a href="http://webplatform.org/stewards/w3c">W3C</a></li>
					<li class="steward-adobe"><a href="http://webplatform.org/stewards/adobe">Adobe</a></li>
					<li class="steward-facebook"><a href="http://webplatform.org/stewards/facebook">facebook</a></li>
					<li class="steward-google"><a href="http://webplatform.org/stewards/google">Google</a></li>
					<li class="steward-hp"><a href="http://webplatform.org/stewards/hp">HP</a></li>
					<li class="steward-intel"><a href="http://webplatform.org/stewards/intel">Intel</a></li>
					<li class="steward-microsoft"><a href="http://webplatform.org/stewards/microsoft">Microsoft</a></li>
					<li class="steward-mozilla"><a href="http://webplatform.org/stewards/mozilla">Mozilla</a></li>
					<li class="steward-nokia"><a href="http://webplatform.org/stewards/nokia">Nokia</a></li>
					<li class="steward-opera"><a href="http://webplatform.org/stewards/opera">Opera</a></li>
				</ul>
			</div>
		</footer>
		<!-- /footer -->
	<?php
	}

	/**
	 * Create breadcrumbs based on subpage hierarchy, with dropdown menus for child pages at each level
	 *
	 * This is literally the BreadcrumbMenu extension (which was always skin-specific
	 * to begin with) cleaned up very slightly and renamed.
	 *
	 * The BreadcrumbMenu extension is loosely based on the SubPageList3 extension by James McCormack;
	 *
	 * See: https://www.mediawiki.org/wiki/Extension:SubPageList3
	 *
	 * @author Doug Schepers (http://schepers.cc)
	 * @date 20 February 2023
	 * @see https://github.com/webplatform/mediawiki/blob/master/extensions/BreadcrumbMenu.php
	 *
	 * @param Title $title
	 * @return string HTML suitable for output
	 */
	private function buildBreadcrumbMenu( $title ) {
		global $wgArticlePath;

		$menuHTML = '';
		$pageTitleWithNS = $title->getPrefixedText();
		$page_path = explode( '/', $pageTitleWithNS );

		for ( $pp = 1; count( $page_path ) >= $pp; $pp++ ) {
			$path_part = implode( '/', array_slice( $page_path, 0, $pp ) );

			// Don't render an empty <li> (no text for the <a> elem) for when a page
			// ends in a slash
			$displayText = str_replace( '_', ' ', $page_path[$pp - 1] );
			if ( $displayText !== '' ) {
				$menuHTML .= '<li><a href="' .
					// @note Redlink state isn't known because we don't have a Title we're working
					// with here, we're just munging regular ol' HTML
					str_replace( '$1', str_replace( '%20', '_', rawurlencode( $path_part ) ), $wgArticlePath )
				. '">' . $displayText . '</a></li>';
			}
		}

		return $menuHTML;
	}

	private function renderHeaderMenu() {
	?>
		<div id="p-personal" class="<?php if ( count( $this->data['personal_urls'] ) == 0 ) echo ' emptyPortlet'; ?>">
		<h5><?php $this->msg( 'personaltools' ) ?></h5>
		<ul<?php $this->html( 'userlangattributes' ) ?> class="links">
		<?php
			foreach( $this->getPersonalTools() as $key => $item ) {
				if ( $key == 'userpage' || $key == 'login' ) {
					echo $this->makeListItem( $key, $item );
				}
			}
			?>
			<ul class="user-dropdown">
				<?php
				foreach ( $this->getPersonalTools() as $key => $item ) {
					if ( $key !== 'userpage' && $key !== 'login' ) {
						echo $this->makeListItem( $key, $item );
					}
				}
				?>
			</ul>
		</ul>
	</div>
	<?php
	}

	/**
	 * @phan-suppress PhanTypeMismatchArgumentNullableInternal
	 * I have no clue why phan has issues with the $link variable below, but it's all good,
	 * despite what phan claims.
	 */
	private function renderWatchButton() {
		if ( isset( $this->data['action_urls']['watch'] ) ) {
			$link = $this->data['action_urls']['watch'];
		} elseif ( isset( $this->data['action_urls']['unwatch'] ) ) {
			$link = $this->data['action_urls']['unwatch'];
		} else {
			return;
		}

		$pt = $this->data['personal_urls'];
	?>
		<div class="dropdown">
			<a href="<?php echo htmlspecialchars( $link['href'] ) ?>"
				<?php $this->html( 'userlangattributes' ) ?>
				<?php echo $link['key'] ?>
				<?php
					if ( strpos( $link['attributes'], 'class=' ) > 0 ) {
						echo str_replace( 'class="', 'class="watch button ', $link['attributes'] );
					} else {
						echo 'class="watch button"';
					}
				?>>
				<?php echo htmlspecialchars( $link['text'] ) ?>
			</a>
			<ul>
				<?php
					if ( isset( $pt['watchlist']['href'] ) ) {
						echo $this->makeListItem( 'href', $pt['watchlist'] );
					}
				?>
			</ul>
		</div>
	<?php
	}

	private function renderEditButton() {
		if ( $this->getSkin()->getTitle()->getNamespace() < 0 /* = NS_MAIN */ ) {
			// Don't render the "edit" button at all for Special: pages, they're not
			// editable
			return;
		}
		$cn = $this->data['content_navigation'];
		$sb = $this->getSidebar();
		if ( isset( $this->data['view_urls']['edit'] ) ) {
			$link = $this->data['view_urls']['edit'];

			if ( isset( $this->data['view_urls']['ve-edit'] ) ) {
				$link = $this->data['view_urls']['ve-edit'];
			}

			if ( isset( $this->data['view_urls']['form_edit'] ) ) {
				$link = $this->data['view_urls']['form_edit'];
			}
		} else {
			$link = [
				'href' => Skin::makeSpecialUrl( 'Userlogin' ),
				'id' => 'ca-edit',
				'text' => $this->getSkin()->msg( 'edit' )->text()
			];
		}
		?>
		<div class="dropdown">
			<a href="<?php echo $link['href'] ?>" id="<?php echo $link['id'] ?>" class="highlighted edit button">
				<?php echo $link['text'] ?>
			</a>
			<ul>
				<?php
				if ( isset( $cn['views']['form_edit'] ) ) {
					echo $this->makeListItem( 'form_edit', $cn['views']['form_edit'] );
				}

				if ( isset( $cn['views']['edit'] ) ) {
					echo $this->makeListItem( 'edit', $cn['views']['edit'] );
				}

				if ( isset( $sb['TOOLBOX']['content']['upload'] ) ) {
					echo $this->makeListItem( 'upload', $sb['TOOLBOX']['content']['upload'] );
				}

				if ( isset( $cn['views']['history'] ) ) {
					echo $this->makeListItem( 'history', $cn['views']['history'] );
				}

				if ( isset( $cn['views']['view'] ) ) {
					echo $this->makeListItem( 'view', $cn['views']['view'] );
				}

				if ( isset( $cn['actions']['move'] ) ) {
					echo $this->makeListItem( 'move', $cn['actions']['move'] );
				}

				if ( isset( $cn['actions']['protect'] ) ) {
					echo $this->makeListItem( 'protect', $cn['actions']['protect'] );
				}

				if ( isset( $cn['actions']['unprotect'] ) ) {
					echo $this->makeListItem( 'unprotect', $cn['actions']['unprotect'] );
				}

				if ( isset( $cn['actions']['delete'] ) ) {
					echo $this->makeListItem( 'delete', $cn['actions']['delete'] );
				}

				if ( isset( $cn['actions']['purge'] ) ) {
					echo $this->makeListItem( 'purge', $cn['actions']['purge'] );
				}
				?>
			</ul>
		</div>

	<?php
	}

	private function renderToolMenu() {
		$cn = $this->data['content_navigation'];
		$sb = $this->getSidebar();
	?>
		<div class="dropdown">
			<a class="tools button">
				<?php echo $this->getSkin()->msg( 'toolbox' )->escaped() ?>
			</a>
			<ul>
				<?php
				if ( isset( $sb['TOOLBOX']['content']['whatlinkshere'] ) ) {
					echo $this->makeListItem( 'whatlinkshere', $sb['TOOLBOX']['content']['whatlinkshere'] );
				}

				if ( isset( $sb['TOOLBOXEND']['content'] ) ) {
					echo '<li>' . preg_replace( '#^<ul.+?>|</ul>#', '', $sb['TOOLBOXEND']['content'] );
				}

				if ( isset( $sb['TOOLBOX']['content']['recentchangeslinked'] ) ) {
					echo $this->makeListItem( 'recentchangeslinked', $sb['TOOLBOX']['content']['recentchangeslinked'] );
				}

				if ( isset( $sb['navigation']['content'][3] ) ) {
					echo $this->makeListItem( '3', $sb['navigation']['content'][3] );
				}

				if ( isset( $sb['TOOLBOX']['content']['specialpages'] ) ) {
					echo $this->makeListItem( 'specialpages', $sb['TOOLBOX']['content']['specialpages'] );
				}

				if ( isset( $cn['namespaces']['talk'] ) ) {
					echo $this->makeListItem( 'talk', $cn['namespaces']['talk'] );
				}

				if ( isset( $sb['navigation']['content'][5] ) ) {
					echo $this->makeListItem( '5', $sb['navigation']['content'][5] );
				}
				?>
			</ul>
		</div>
	<?php
	}

	/**
	 * Render the search portlet
	 */
	private function renderSearch() {
	?>
		<div id="p-search">
			<h5<?php $this->html( 'userlangattributes' ) ?>>
				<label for="searchInput">
					<?php $this->msg( 'search' ) ?>
				</label>
			</h5>
			<form action="<?php $this->text( 'wgScript' ) ?>" id="searchform">
				<div id="search">
					<?php
						echo $this->makeSearchInput( [ 'id' => 'searchInput', 'type' => 'input' ] );
						echo $this->makeSearchButton( 'fulltext', [ 'id' => 'mw-searchButton', 'class' => 'searchButton', 'value' => ' ' ] );
					?>
					<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
				</div>
			</form>
		</div>
		<?php
		echo "\n<!-- /search -->\n";
	}
}
