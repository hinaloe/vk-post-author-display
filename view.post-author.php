<?php
function pad_get_author_box(){
	/*-------------------------------------------*/
	/*	Profile
	/*-------------------------------------------*/
	$author_box_title = get_pad_options('author_box_title');
	
	global $post;
	$user_id = $post->post_author;
	$user = get_userdata( $user_id );
	
	// author caption
	if (get_the_author_meta( 'pad_caption' )){
		$caption = '<span id="pad_caption">'.get_the_author_meta( 'pad_caption' ).'</span>';
	}

	// url
	$url = isset( $user->data->user_url ) ? $user->data->user_url : '';
	if ( $url ){
		$sns_icons = '<li class="pad_url"><a href="'.esc_url( $url ).'" target
		="_blank" class="web"><i class="fa fa-globe web" aria-hidden="true"></i></a></li>';
	}

	$sns_array = pad_sns_array();

	foreach ($sns_array as $key => $value) {
		$field = 'pad_'.$key;
		$sns_url = get_the_author_meta( $field );

		// 旧バージョンの人はアカウントだけで保存されているので、その前のURLを追加
		if ( $key == 'twitter' ){
			$subject = $sns_url;
			$pattern = '/https:\/\/twitter.com\//';
			preg_match( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );
			if ( !$matches )
				$sns_url = 'https://twitter.com/'.$sns_url;
		} // if ( $key == 'twitter' ){

		if ( $sns_url ){
			$sns_icons .= '<li class="pad_'.$key.'"><a href="'.esc_url( $sns_url ).'" target
			="_blank" class="'.$key.'"><i class="fa '.$value['icon'].'" aria-hidden="true"></i></a></li>';
		}
	}


	$profileUnit =
		'<h4>'.$author_box_title.'</h4>'.
		'<div id="avatar">'.get_avatar( get_the_author_meta('email'), 100 ).'</div>'.
		'<dl id="profileTxtSet">'.
		'<dt>'.'<span id="authorName">'.esc_html ( get_the_author_meta( 'display_name' ) ).'</span>';
	if(isset($caption)):
		$profileUnit .= $caption;
	endif;

	$profileUnit .= '</dt><dd>'.nl2br(get_the_author_meta( 'description' ));
	if ( $sns_icons ){
		$profileUnit .= '<ul class="sns_icons">';
		$profileUnit .= $sns_icons;
		$profileUnit .= '</ul>';
	}
	$profileUnit .= '</dd></dl>';



	/*-------------------------------------------*/
	/*	entryUnit (Latest entries)
	/*-------------------------------------------*/
	$list_box_title  = get_pad_options('list_box_title');
	$thumbnail       = get_pad_options('show_thumbnail');
	$author_link     = get_pad_options('author_archive_link');
	$author_link_txt = get_pad_options('author_archive_link_txt');

	// author entries
	global $post;
	$autorID = $post->post_author;
	$loop = new WP_Query( array( 'post_type' => 'post', 'posts_per_page'=> 4, 'author' => $autorID ) );
	$entryUnit = '<div id="latestEntries">'."\n";
	$entryUnit .= '<h5>'.$list_box_title.'</h5>'."\n";
	if ($author_link == 'display'){
			$entryUnit .= '<p class="authorLink"><a href="'. esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) .'" rel="author"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i> '.$author_link_txt.'</a></p>'."\n";
		}
	$entryUnit .= '<ul class="entryList">'."\n";
	while ( $loop->have_posts() ) : $loop->the_post();
		$term = '';
		// foreach((get_the_category()) as $cat) {
		// 	$cat_id = $cat->cat_ID ;
		// 	break ;
		// }
		// $category_link = get_category_link( $cat_id );
		// $categories = '<a href="'.$category_link.'" title="'.$cat->cat_name.'" class="padCate cate-'.$cat->slug.'">'.$cat->cat_name.'</a>';
			
			$taxonomies = get_the_taxonomies();
			if ($taxonomies):
				// get $taxonomy name
				$taxonomy = key( $taxonomies );
				$terms  = get_the_terms( get_the_ID(),$taxonomy );
				$term_name	= esc_html($terms[0]->name);
				$term_color = Vk_term_color::get_term_color( $terms[0]->term_id );
				$term_color = ( $term_color ) ? ' style="background-color:'.$term_color.'"': '';
				$term_link = esc_url( get_term_link( $terms[0]->term_id, $taxonomy ) );
				$term = '<a class="padCate"'.$term_color.' href="'.$term_link.'">'.$term_name.'</a>';
			endif;

		if ($thumbnail == 'hide'){
			/* list only */
			$entryUnit .= '<li class="textList"><span class="padDate">'.get_the_date('Y.m.d').'</span>'.$term.'<a href="'.get_permalink($post->ID).'" class="padTitle">'.get_the_title().'</a></li>'."\n";
		} else {
			/* Show thumbnail box */
			$entryUnit .= '<li class="thumbnailBox"><span class="postImage"><a href="'.get_permalink().'">';
			if ( has_post_thumbnail()) {
				//allows display of pad_thumb only if selected in pad options
				$sizes_available = get_intermediate_image_sizes();
				$pad_thumb = get_the_post_thumbnail( get_the_ID(), 'pad_thumb' );
				if ( in_array( 'pad_thumb', $sizes_available) && !empty($pad_thumb) )
					$entryUnit .= $pad_thumb;
				else
					$entryUnit .=  get_the_post_thumbnail();
			} else {
				$entryUnit .= '<img src="'.plugins_url().'/vk-post-author-display/images/thumbnailDummy.jpg" alt="'.get_the_title().'" />';
			}
			$entryUnit .= $term.'</a></span><span class="padDate">'.get_the_date('Y.m.d').'</span><a href="'.get_permalink($post->ID).'" class="padTitle">'.get_the_title().'</a></li>'."\n";
		}
		endwhile;
	$entryUnit .= '</ul>'."\n";
	$entryUnit .= '</div>'."\n";
	wp_reset_query(); // important!!
	//  get_author_posts_url()
	/*-------------------------------------------*/
	/*	Unit display
	/*-------------------------------------------*/
	$author_unit = '<div id="padSection">';
	$author_unit .= $profileUnit;
	$author_unit .= $entryUnit;
	$author_unit .= '</div>';
	return $author_unit;
}