<?php
class qa_html_theme extends qa_html_theme_base {
	public function body_footer() {
		if ( isset( $this->content['body_footer'] ) ) {
			$this->output_raw( $this->content['body_footer'] );
		}

		$this->output( '<link rel="preconnect" href="https://fonts.googleapis.com">' );
		$this->output( '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' );
		$this->output( '<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">' );
		$this->king_js_codes();
	}

	public function king_js() {
		$this->output( '<script src="' . qa_html( $this->rooturl ) . 'js/main.js"></script>' );
		$this->output( '<script src="' . qa_html( $this->rooturl ) . 'js/bootstrap.min.js"></script>' );

		if ( 'home' == $this->template || 'hot' == $this->template || 'search' == $this->template || 'updates' == $this->template || 'user-questions' == $this->template || 'favorites' == $this->template || 'qa' == $this->template || 'tag' == $this->template || 'type' == $this->template || 'reactions' == $this->template ) {
			$this->output( '<script src="' . qa_html( $this->rooturl ) . 'js/jquery-ias.min.js"></script>' );
		}
	}

	public function body_content() {
		$this->body_prefix();
		$this->notices();
		$this->body_header();
		$this->header();
		$this->output( '<DIV id="king-body" class="king-body">' );
		$this->h_title();

		if ( isset( $this->content['profile'] ) ) {
			$this->profile_page();
		}

		$this->leftmenu();

		if ( qa_using_categories() && 'home' == $this->template ) {
			$this->output( '<div class="king-cat">' );
			$this->nav( 'cat', 4 );
			$this->output( '</div>' );
		}

		$this->main_up();
		$this->output( '<DIV id="king-body-wrapper" class="king-body-in">' );
		$this->widgets( 'full', 'top' );
		$this->widgets( 'full', 'high' );
		$this->featured();
		$this->widgets( 'full', 'low' );

		$this->nav( 'sub' );
		$this->nav( 'kingsub' );
		$this->main();
		$this->output( '</DIV>' );
		$this->footer();
		$this->output( '</DIV>' );
		$this->body_suffix();
	}

	public function body_header() {
		if ( isset( $this->content['body_header'] ) ) {
			$this->output( '<DIV class="ads">' );
			$this->output_raw( $this->content['body_header'] );
			$this->output( '</DIV>' );
		}
	}

	public function main() {
		$content = $this->content;
		$hidden  = isset( $content['hidden'] ) ? ' king-main-hidden' : '';
		$class   = isset( $content['class'] ) ? $content['class'] : ' one-page';
		$this->widgets( 'main', 'top' );
		$this->output( '<DIV CLASS="king-main' . qa_html( $class ) . qa_html( $hidden ) . '">' );
		$this->output( '<DIV CLASS="king-main-in">' );
		$this->widgets( 'main', 'high' );
		$this->main_parts( $content );
		$this->page_links();
		$this->widgets( 'main', 'low' );
		$this->output( '</div> <!-- king-main-in -->' );

		if ( isset( $content['sside'] ) ) {
			$this->sidepanel();
		}

		$this->output( '</DIV> <!-- king-main -->' );

		$this->suggest_next();
		$this->widgets( 'main', 'bottom' );
	}

	public function main_up() {
		$content = $this->content;
		$q_view  = isset( $content['q_view'] ) ? $content['q_view'] : '';
		$thumb   = isset( $q_view['raw']['content'] ) ? king_get_uploads( $q_view['raw']['content'] ) : '';

		if ( $thumb ) {
			$bc = qa_html( $thumb['furl'] );
		} else {
			$bc = '';
		}

		if ( $q_view ) {
			$text2 = $q_view['raw']['postformat'];
			$nsfw  = $q_view['raw']['nsfw'];

			if ( null !== $nsfw && ! qa_is_logged_in() ) {
				$this->output( '<DIV CLASS="king-video">' );
				$this->output( '<span class="king-nsfw-post"><p><i class="fas fa-mask fa-2x"></i></p>' . qa_lang_html( 'misc/nsfw_post' ) . '</span>' );
				$this->output( '</DIV>' );
			} elseif ( 'V' == $text2 ) {
				$this->output( '<DIV CLASS="king-video-in" style="background-image: url(' . qa_html( $bc ) . ');">' );
				$this->output( '<DIV CLASS="king-video" >' );
				$this->q_view_extra( $q_view );
				$this->output( '</DIV>' );
				$this->output( '</DIV>' );
			} elseif ( 'music' == $text2 ) {
				$this->output( '<DIV CLASS="king-video-in" style="background-image: url(' . qa_html( $bc ) . ');">' );
				$this->output( '<DIV CLASS="king-video" >' );
				$this->music_view( $q_view );
				$this->output( '</DIV>' );
				$this->output( '</DIV>' );
			} elseif ( 'I' == $text2 ) {
				$this->output( '<DIV CLASS="king-video-in" style="background-image: url(' . qa_html( $bc ) . ');">' );
				$this->output( '<DIV CLASS="king-video" >' );
				$this->q_view_extra( $q_view );
				$this->output( '</DIV>' );
				$this->output( '</DIV>' );
			} else {
				if ( isset( $thumb ) ) {
					$this->output( '<div class="king-back" style="background-image: url(' . qa_html( $bc ) . ');"></div>' );
				}
			}
		}
	}

	/**
	 * @param $q_view
	 */
	public function q_view( $q_view ) {
		$pid   = $q_view['raw']['postid'];
		$text2 = $q_view['raw']['postformat'];
		$nsfw  = $q_view['raw']['nsfw'];

		if ( ! empty( $q_view ) ) {
			if ( null == $nsfw || qa_is_logged_in() ) {
				$this->output( '<DIV CLASS="king-q-view' . ( isset( $q_view['hidden'] ) ? ' king-q-view-hidden' : '' ) . rtrim( ' ' . $q_view['classes'] ) . '"' . rtrim( ' ' . $q_view['tags'] ) . '>' );

				$this->output( '<DIV CLASS="rightview">' );
				$this->post_tags( $q_view, 'king-q-view' );
				$this->page_title_error();
				$this->viewtop();
				$blockwordspreg = qa_get_block_words_preg();
				$this->output( '<div class="post-content">' . qa_block_words_replace( $q_view['raw']['pcontent'], $blockwordspreg ) . '</div>' );

				if ( 'poll' == $text2 ) {
					$this->get_poll( $pid );
				} elseif ( 'list' == $text2 ) {
					$this->get_list( $pid );
				} elseif ( 'trivia' == $text2 ) {
					$this->get_trivia( $pid );
				}

				$this->view_count( $q_view );
				$this->post_meta_when( $q_view, 'meta' );
				$this->output( '<div class="prev-next">' );
				$this->get_next_q();
				$this->get_prev_q();
				$this->output( '</div>' );
				$this->output( '</DIV>' );

				if ( qa_opt( 'show_ad_post_below' ) ) {
					$this->output( '<div class="ad-below">' );
					$this->output( '' . qa_opt( 'ad_post_below' ) . '' );
					$this->output( '</div>' );
				}

				$this->output( '</DIV> <!-- END king-q-view -->', '' );
			}

			$this->socialshare( $q_view );
			$this->pboxes( $q_view );
			$this->maincom( $q_view );
		}
	}

	public function header() {
		$this->output( '<header CLASS="king-headerf">' );

		if ( 'question' == $this->template ) {
			$class = ' darkheader';
			$this->output( '<div id="progress-bar"></div>' );
		} else {
			$class = '';
		}

		$this->output( '<DIV CLASS="king-header' . qa_html( $class ) . '">' );
		$this->header_left();
		$this->header_middle();
		$this->header_right();
		$this->output( '</DIV>' );
		$this->output( '</header>' );

		if ( isset( $this->content['error'] ) ) {
			$this->error( $this->content['error'] );
		}
	}

	public function h_title() {
		if ( isset( $this->content['header'] ) ) {
			$this->output( '<div class="head-title">' );
			$this->output( $this->content['header'] );
			$this->output( '</div>' );
		}
	}

	public function header_left() {
		$this->output( '<div class="header-left">' );
		$this->output( '<label class="king-left-toggle">
			<input class="hide" type="checkbox" id="king-side" name="king_side"><span class="left-toggle-line"></span>
			</label>' );

		$this->logo();

		if ( isset( $this->content['navigation']['headmenu'] ) ) {
			$this->output( '<div class="menutoggle" data-toggle="dropdown" data-target=".king-mega-menu" aria-expanded="false"><i class="fas fa-angle-down"></i></div>' );
			$this->output( '<div class="king-mega-menu">' );
			$this->nav( 'headmenu' );
			$this->output( '</div>' );
		}

		$this->output( '</div>' );
	}

	public function header_middle() {
		$this->output( '<div class="header-middle">' );
		$this->nav_user_search();
		$this->output( '</div>' );
	}

	public function search() {
		$search = $this->content['search'];

		$this->output( '<div class="king-search">' );
		$this->output( '<form ' . qa_sanitize_html( $search['form_tags'] ) . '>' );
		$this->output( qa_sanitize_html( $search['form_extra'] ) );
		$this->search_field( $search );
		$this->search_button( $search );
		$this->output( '</form>' );
		$this->output( '<div class="king-search-in">' );
		$populartags = qa_db_single_select( qa_db_popular_tags_selectspec( 0, 5 ) );
		$this->output( '<div id="king_live_results" class="liveresults">' );
		$this->output( '<h3>' . qa_lang_html( 'misc/discover' ) . '</h3>' );

		foreach ( $populartags as $tag => $count ) {
			$this->output( '<a class="sresults" href="' . qa_path_html( 'tag/' . $tag ) . '" >' . qa_html( $tag ) . '</a>' );
		}

		$this->output( '</div>' );
		$this->output( '</div>' );
		$this->output( '</div>' );
	}

	/**
	 * @param $search
	 */
	public function search_field( $search ) {
		$this->output( '<input type="text" name="q" value="' . ( isset( $search['value'] ) ? qa_html( $search['value'] ) : '' ) . '" class="king-search-field" placeholder="' . qa_lang_html( 'misc/search' ) . '" onkeyup="showResult(this.value)" autocomplete="off" data-toggle="dropdown" data-target=".king-search" aria-expanded="false"/>' );
	}

	public function header_right() {
		$this->output( '<DIV CLASS="header-right">' );
		$this->output( '<ul>' );

		if ( ! qa_is_logged_in() ) {
			$this->output( '<li>' );
			$this->output( '<div class="reglink" data-toggle="modal" data-target="#loginmodal" role="button" title="' . qa_lang_html( 'main/nav_login' ) . '"><i class="fa-solid fa-user"></i></div>' );
			$this->output( '</li>' );
		} else {
			$this->userpanel();
		}

		if (  ( qa_user_maximum_permit_error( 'permit_post_q' ) != 'level' ) ) {
			$this->kingsubmit();
		}

		$this->output( '</ul>' );
		$this->output( '</DIV>' );
	}

	public function leftmenu() {
		$this->output( '<div class="leftmenu kingscroll">' );
		$search = $this->content['search'];
		$this->output( '<div class="king-search-left">' );
		$this->output( '<form ' . qa_sanitize_html( $search['form_tags'] ) . '>' );
		$this->output( qa_sanitize_html( $search['form_extra'] ) );
		$this->search_field( $search );
		$this->search_button( $search );
		$this->output( '</form>' );
		$this->output( '</div>' );
		$this->nav( 'head' );
		$this->nav_main_sub();

		if ( qa_is_logged_in() ) {
			$this->subs();
		}

		$this->output( '<div class="left-night">' );
		$this->output( '<input type="checkbox" id="king-night" class="hide" /><label for="king-night" class="king-nightb"><i class="fa-solid fa-sun"></i><i class="fa-solid fa-moon"></i></label>' );
		$this->output( qa_lang_html( 'misc/night_mode' ) );
		$this->output( '</div>' );
		$this->output( '</div>' );
	}

	public function subs() {
		$userid = qa_get_logged_in_userid();
		$users  = qa_db_select_with_pending(
			qa_db_user_favorite_users_selectspec( $userid, '8' )
		);

		if ( count( $users ) ) {
			$this->output( '<div class="left-discover">' );
			$this->output( '<div class="left-discover-title">' . qa_lang_html( 'misc/nav_discover' ) . '</div>' );

			foreach ( $users as $user ) {
				$this->output( '<a href="' . qa_path_html( 'user/' . $user['handle'] ) . '" >' );
				$this->output( get_avatar( $user['avatarblobid'], 34 ) );
				$this->output( qa_html( $user['handle'] ) . '</a>' );
			}

			$this->output( '</div>' );
		}
	}

	public function kingsubmit() {
		if ( ! qa_opt( 'disable_image' ) || ! qa_opt( 'disable_video' ) || ! qa_opt( 'disable_news' ) || ! qa_opt( 'disable_poll' ) || ! qa_opt( 'disable_list' ) ) {
			$this->output( '<li>' );
			$this->output( '<span class="kingadd" data-toggle="modal" data-target=".king-submit" aria-expanded="false" role="button"><i class="fa-solid fa-circle-plus"></i></span>' );
			$this->output( '</li>' );
			$this->output( '<div class="king-submit king-modal-login">' );

			$this->output( '<div class="king-dropdown2 king-modal-content">' );

			if ( ! qa_opt( 'disable_news' ) ) {
				$this->output( '<a href="' . qa_path_html( 'news' ) . '" class="kingaddnews"><i class="fas fa-newspaper"></i> ' . qa_lang_html( 'main/home_news' ) . '</a>' );
			}

			if ( ! qa_opt( 'disable_image' ) ) {
				$this->output( '<a href="' . qa_path_html( 'ask' ) . '" class="kingaddimg"><i class="fas fa-image"></i> ' . qa_lang_html( 'main/home_image' ) . '</a>' );
			}

			if ( ! qa_opt( 'disable_video' ) ) {
				$this->output( '<a href="' . qa_path_html( 'video' ) . '" class="kingaddvideo"><i class="fas fa-video"></i> ' . qa_lang_html( 'main/home_video' ) . '</a>' );
			}

			if ( ! qa_opt( 'disable_poll' ) ) {
				$this->output( '<a href="' . qa_path_html( 'poll' ) . '" class="kingaddpoll"><i class="fas fa-align-left"></i> ' . qa_lang_html( 'misc/king_poll' ) . '</a>' );
			}

			if ( ! qa_opt( 'disable_list' ) ) {
				$this->output( '<a href="' . qa_path_html( 'list' ) . '" class="kingaddlist"><i class="fas fa-bars"></i> ' . qa_lang_html( 'misc/king_list' ) . '</a>' );
			}

			if ( ! qa_opt( 'disable_trivia' ) ) {
				$this->output( '<a href="' . qa_path_html( 'trivia' ) . '" class="kingaddtrivia"><i class="fas fa-times"></i> ' . qa_lang_html( 'misc/king_trivia' ) . '</a>' );
			}

			if ( ! qa_opt( 'disable_music' ) ) {
				$this->output( '<a href="' . qa_path_html( 'music' ) . '" class="kingaddmusic"><i class="fas fa-headphones-alt"></i> ' . qa_lang_html( 'misc/king_music' ) . '</a>' );
			}

			$this->output( '</div>' );
			$this->output( '</div>' );
		}
	}

	public function nav_main_sub() {
		$this->output( '<DIV CLASS="king-nav-main">' );
		$this->nav( 'main' );
		$this->output( '</DIV>' );
	}

	public function profile_page() {
		$handle = qa_request_part( 1 );

		if ( ! strlen( (string)$handle ) ) {
			$handle = qa_get_logged_in_handle();
		}

		$user = qa_db_select_with_pending(
			qa_db_user_account_selectspec( $handle, false )
		);

		$this->output( get_user_html( $user, '1200', 'king-profile', '140' ) );
	}

	

	/**
	 * @param $q_items
	 */
	public function q_list_items( $q_items ) {
		$this->output( '<div class="container">' );

		foreach ( $q_items as $q_item ) {
			$this->q_list_item( $q_item );
		}

		$this->output( '</div>' );
	}

	/**
	 * @param $q_item
	 */
	public function q_list_item( $q_item ) {
		$format     = $q_item['raw']['postformat'];
		$postformat = '';
		$postc      = '';
		$shomag     = true;

		if ( 'V' == $format ) {
			$postformat = '<a class="king-post-format" href="' . qa_path_html( 'type' ) . '" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'main/home_video' ) . '"><i class="fas fa-video"></i></a>';
			$postc      = ' king-class-video';
		} elseif ( 'I' == $format ) {
			$postformat = '<a class="king-post-format" href="' . qa_path_html( 'type', array( 'by' => 'images' ) ) . '" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'main/home_image' ) . '"><i class="fas fa-image"></i></a>';
			$postc      = ' king-class-image';
		} elseif ( 'N' == $format ) {
			$postformat = '<a class="king-post-format" href="' . qa_path_html( 'type', array( 'by' => 'news' ) ) . '" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'main/home_news' ) . '"><i class="fas fa-newspaper"></i></a>';
			$postc      = ' king-class-news';
		} elseif ( 'poll' == $format ) {
			$postformat = '<a class="king-post-format" href="' . qa_path_html( 'type', array( 'by' => 'poll' ) ) . '" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'misc/king_poll' ) . '"><i class="fas fa-align-left"></i></a>';
			$postc      = ' king-class-poll';
		} elseif ( 'list' == $format ) {
			$postformat = '<a class="king-post-format" href="' . qa_path_html( 'type', array( 'by' => 'list' ) ) . '" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'misc/king_list' ) . '"><i class="fas fa-bars"></i></a>';
			$postc      = ' king-class-list';
		} elseif ( 'trivia' == $format ) {
			$postformat = '<a class="king-post-format" href="' . qa_path_html( 'type', array( 'by' => 'trivia' ) ) . '" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'misc/king_list' ) . '"><i class="fas fa-times"></i></a>';
			$postc      = ' king-class-trivia';
		} elseif ( 'music' == $format ) {
			$postformat = '<a class="king-post-format" href="' . qa_path_html( 'type', array( 'by' => 'music' ) ) . '" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'misc/king_music' ) . '"><i class="fas fa-headphones-alt"></i></a>';
			$shomag     = false;
			$postc      = ' king-class-music';

			if ( $q_item['ext'] ) {
				$shomag = true;
			}
		}

		$this->output( '<div class="box king-q-list-item' . rtrim( ' ' . $q_item['classes'] ) . '' . qa_html( $postc ) . '" ' . ( isset( $q_item['tags'] ) ? $q_item['tags'] : '' ) . '>' );
		$this->output( '<div class="king-post-upbtn">' );
		$this->output( $postformat );

		if ( $shomag ) {
			$this->output( '<a href="' . qa_html( $q_item['url'] ) . '" class="ajax-popup-link magnefic-button mgbutton" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'misc/king_qview' ) . '"><i class="fa-solid fa-play"></i></a>' );
		} else {
			$this->output( '<a href="' . qa_html( $q_item['url'] ) . '" class="king-listen magnefic-button mgbutton" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'misc/king_listen' ) . '"><i class="fa-solid fa-headphones"></i></a>' );
		}
		if (qa_opt('enable_bookmark')) {
			$this->output( post_bookmark( $q_item['raw']['postid'] ) );
		}
		$this->output( '<a href="' . qa_html( $q_item['url'] ) . '" class="ajax-popup-share magnefic-button" data-toggle="tooltip" data-placement="top" title="' . qa_lang_html( 'misc/king_share' ) . '"><i class="fas fa-share-alt"></i></a>' );
		$this->output( '</div>' );
		$this->q_item_main( $q_item );
		$this->output( '</div>' );
	}

	/**
	 * @param $q_item
	 */
	public function q_item_main( $q_item ) {
		$this->output( '<div class="king-q-item-main">' );
		$this->q_item_content( $q_item );
		$this->output( '<DIV CLASS="king-post-content">' );
		$this->q_item_title( $q_item );
		$this->post_metas( $q_item );
		$this->q_item_buttons( $q_item );
		$this->output( '</DIV>' );
		$this->output( '</div>' );
	}

	/**
	 * @param $q_item
	 */
	public function post_metas( $q_item ) {
		$this->output( '<div class="post-meta">' );

		if ( isset( $q_item['avatar'] ) ) {
			$this->output( '<div class="king-p-who">' );
			$this->output( '' . get_avatar( $q_item['raw']['avatarblobid'], '40' ) . $q_item['who']['data'] . '' );
			$this->output( '</div>' );
		}

		$this->output( '<div>' );
		$this->output( '<span><i class="fa fa-comment" aria-hidden="true"></i> ' . qa_html( $q_item['raw']['acount'] ) . '</span>' );
		$this->output( '<span><i class="fa fa-eye" aria-hidden="true"></i> ' . qa_html( $q_item['raw']['views'] ) . '</span>' );
		$this->output( '<span><i class="fas fa-chevron-up"></i> ' . qa_html( $q_item['raw']['netvotes'] ) . '</span>' );
		$this->output( '</div>' );
		$this->output( '</div>' );
	}

	/**
	 * @param $q_item
	 */
	public function q_item_content( $q_item ) {
		$text = $q_item['raw']['content'];
		$nsfw = $q_item['raw']['nsfw'];

		if ( null !== $nsfw && ! qa_is_logged_in() ) {
			$this->output( '<a href="' . qa_html( $q_item['url'] ) . '" class="item-a"><span class="king-nsfw-post"><p><i class="fas fa-mask fa-2x"></i></p>' . qa_lang_html( 'misc/nsfw_post' ) . '</span></a>' );
		} elseif ( ! empty( $text ) ) {
			$text2 = king_get_uploads( $text );
			$this->output( '<A class="item-a" HREF="' . qa_html( $q_item['url'] ) . '">' );

			if ( $text2 ) {
				$this->output_raw( '<span class="post-featured-img"><img class="item-img king-lazy" width="' . qa_html( $text2['width'] ) . '" height="' . qa_html( $text2['height'] ) . '" data-king-img-src="' . qa_html( $text2['furl'] ) . '" alt=""/></span>' );
			} else {
				$this->output_raw( '<span class="post-featured-img"><img class="item-img" data-king-img-src="' . qa_html( $text ) . '" alt=""/></span>' );
			}

			$this->output( '</A>' );
		} else {
			$this->output( '<a href="' . qa_html( $q_item['url'] ) . '" class="king-nothumb"></a>' );
		}
	}

	/**
	 * @param $q_item
	 */
	public function q_item_title( $q_item ) {
		$this->output( '<DIV CLASS="king-q-item-title">' );
		$this->output( '<A HREF="' . qa_html( $q_item['url'] ) . '"><h2>' . qa_html( $q_item['title'] ) . '</h2></A>' );
		$this->output( '</DIV>' );
	}

	public function page_title_error() {
		$this->output( '<DIV CLASS="pheader">' );
		$this->output( '<H1>' );
		$this->title();
		$this->output( '</H1>' );
		$this->output( '</DIV>' );
	}

	public function get_prev_q() {
		$myurl       = $this->request;
		$myurlpieces = explode( "/", $myurl );
		$myurl       = $myurlpieces[0];

		$query_p = "SELECT *
		FROM ^posts
		WHERE postid < $myurl
		AND type='Q'
		ORDER BY postid DESC
		LIMIT 1";

		$prev_q = qa_db_query_sub( $query_p );

		while ( $prev_link = qa_db_read_one_assoc( $prev_q, true ) ) {
			$title = $prev_link['title'];
			$pid   = $prev_link['postid'];

			$this->output( '<A HREF="' . qa_q_path_html( $pid, $title ) . '" CLASS="king-prev-q">' . qa_html( $title ) . ' <i class="fas fa-angle-right"></i></A>' );
		}
	}

	public function get_next_q() {
		$myurl       = $this->request;
		$myurlpieces = explode( "/", $myurl );
		$myurl       = $myurlpieces[0];

		$query_n = "SELECT *
		FROM ^posts
		WHERE postid > $myurl
		AND type='Q'
		ORDER BY postid ASC
		LIMIT 1";

		$next_q = qa_db_query_sub( $query_n );

		while ( $next_link = qa_db_read_one_assoc( $next_q, true ) ) {
			$title = $next_link['title'];
			$pid   = $next_link['postid'];

			$this->output( '<A HREF="' . qa_q_path_html( $pid, $title ) . '" CLASS="king-next-q"><i class="fas fa-angle-left"></i> ' . qa_html( $title ) . '</A>' );
		}
	}
}
