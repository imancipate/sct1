<?php
// most of the functions here are rewrite of json-api functions
class JSON_API_progress_Controller {

		
	  public function get_posts($query = false, $wp_posts = false) {
		global $post;
		//print_r($query);exit;
		$this->set_posts_query($query);
		$output = array();
		while (have_posts()) {
		  the_post();
		  if ($wp_posts) {
			$output[] = $post;
		  } else {
			$output[] = new JSON_API_Post($post);
		  }
		}
		return $output;
	  }
		protected function set_posts_query($query = false) {
			global $json_api, $wp_query;

			if (!$query) {
				$query = array();
			}

	
			//echo '<pre>query: '.print_r($query,true).'</pre>';
			//echo '<pre>json-query: '.print_r($json_api->query->post_type,true).'</pre>';
			
			$query = array_merge($query, $wp_query->query);

			if ($json_api->query->page) {
				$query['paged'] = $json_api->query->page;
			}

			if ($json_api->query->count) {
				$query['posts_per_page'] = $json_api->query->count;
			}
			
			if ($json_api->query->post_type) {
				$query['post_type'] = $json_api->query->post_type;
			}

			if(1==1){  // date vars are set
			
			//filter here
			
			}
			if (!empty($query)) {

				add_filter('posts_where', 'filter_by_date');
				query_posts($query);
				remove_filter('posts_where', 'filter_by_date');
			}
		}

	  public function get_author_posts() {
		global $json_api,$current_user;
		$author = $json_api->introspector->get_current_author();
		
		extract($json_api->query->get(array('author_id', 'post_type', 'post_status', 'orderby', 'order' )));
		if(!$post_status)
			$post_status = 'publish';
		if(!$orderby)
			$orderby = 'menu_order';
		if(!$order)
			$order = 'ASC';
		if(!$post_type)
			$post_type = 'tracked_ritual';
		

		if (!$author || ($current_user->id!=$author_id && $author->id != 1 && $post_status == 'publish')) {
		  $json_api->error("No author specified or lacking viewing permissions");
		}

		if ($author->id == 1 && $post_type != 'scr_template') {
		  $json_api->error("No author specified or lacking viewing permissions");
		}

				
		
		$get_posts_array = array(
		  'author' => $author->id,
		  'post_status' => $post_status,
		  'post_type' => $post_type,
		  'orderby' => $orderby,
		  'order' => $order
		);
		//echo '<pre>'.print_r($get_posts_array, true).'</pre>'; //exit;
		$posts = $this->get_posts($get_posts_array);
		foreach ($posts as $jpost) {
			$this->add_taxonomies( $jpost );
			$this->filter_private( $jpost );
		}
		return $this->posts_object_result($posts, $author);
	  }
  
    public function get_taxonomy_posts() {
        global $json_api;
        $taxonomy = $this->get_current_taxonomy();
        if (!$taxonomy) {
            $json_api->error("Not found.");
        }
        $term = $this->get_current_term( $taxonomy );
        $posts = $json_api->introspector->get_posts(array(
                    'taxonomy' => $taxonomy,
                    'term' => $term->slug
                ));
        foreach ($posts as $jpost) {
            $this->add_taxonomies( $jpost );
        }
        return $this->posts_object_result($posts, $taxonomy, $term);
    }

	
    public function get_recent_posts() {
        global $json_api;

        $posts = $json_api->introspector->get_posts();
        foreach ($posts as $jpost) {
            $this->add_taxonomies( $jpost );
        }
        return $this->posts_result($posts);
    }

    protected function posts_result($posts) {
        global $wp_query;
        return array(
            'count' => count($posts),
            'count_total' => (int) $wp_query->found_posts,
            'pages' => $wp_query->max_num_pages,
            'posts' => $posts
        );
    }

    protected function add_taxonomies( $post ) {
        $taxonomies = get_object_taxonomies( $post->type );
        foreach ($taxonomies as $tax) {
            $post->$tax = array();
            $terms = wp_get_object_terms( $post->id, $tax );
            foreach ( $terms as $term ) {
				$post->{$tax}[] = $term->name;
            }
        }
        return true;
    }
	
    protected function filter_private( $post ) {
        if($post->status=='publish'){
			return $post;
		} else {
			unset($post);
			//print_r($post);
		}
    }

    protected function get_current_taxonomy() {
        global $json_api;
        $taxonomy  = $json_api->query->get('taxonomy');
        if ( $taxonomy ) {
            return $taxonomy;
        } else {
            $json_api->error("Include 'taxonomy' var in your request.");
        }
        return null;
    }

    protected function get_current_term( $taxonomy=null ) {
        global $json_api;
        extract($json_api->query->get(array('id', 'slug', 'term_id', 'term_slug')));
        if ($id || $term_id) {
            if (!$id) {
                $id = $term_id;
            }
            return $this->get_term_by_id($id, $taxonomy);
        } else if ($slug || $term_slug) {
            if (!$slug) {
                $slug = $term_slug;
            }
            return $this->get_term_by_slug($slug, $taxonomy);
        } else {
            $json_api->error("Include 'id' or 'slug' var for specifying term in your request.");
        }
        return null;
    }

    protected function get_term_by_id($term_id, $taxonomy) {
        $term = get_term_by('id', $term_id, $taxonomy);
        if ( !$term ) return null;
        return new JSON_API_Term( $term );
    }

    protected function get_term_by_slug($term_slug, $taxonomy) {
        $term = get_term_by('slug', $term_slug, $taxonomy);
        if ( !$term ) return null;
        return new JSON_API_Term( $term );
    }

    protected function posts_object_result($posts, $taxonomy) {
        global $wp_query;
        return array(
          'count' => count($posts),
          'pages' => (int) $wp_query->max_num_pages,
          'taxonomy' => $taxonomy,
          'posts' => $posts
        );
    } 

}

function filter_by_date($where = '') {
			//posts in the last 30 days
			//$where .= " AND post_date > '" . date('Y-m-d', strtotime('-30 days')) . "'";
			//posts  30 to 60 days old
			//$where .= " AND post_date >= '" . date('Y-m-d', strtotime('-60 days')) . "'" . " AND post_date <= '" . date('Y-m-d', strtotime('-30 days')) . "'";
			//posts for March 1 to March 15, 2009
			
			if(!$_GET['start_date'] || !$_GET['end_date']){ die('You must specify start_date and end_date (YYYYMMDD) parameters');}
			
			$start_date = date('Y-m-d', strtotime($_GET['start_date']));
			$end_date = date('Y-m-d', strtotime($_GET['end_date']));
			$where .= " AND DATE(post_date) >= '{$start_date}' AND DATE(post_date) <= '{$end_date}'";
			
			//$where .= " AND post_title = 'test template 1'";
			
			//echo $where; 
			
			return $where;
}

?>