<?php

class JSON_API_terms_Controller {
	
	
	public function get_terms_index() {
		global $json_api;
        $taxonomy_slug = $json_api->query->tax;
		$terms = $this->get_terms($taxonomy_slug);
		return array(
		  'count' => count($terms),
		  'taxonomy' => $taxonomy_slug,
		  'terms' => $terms
		);
	}
	
	
	public function get_terms($taxonomy_slug) {
	
		$args = array(
		'hide_empty' => false,
		
		);
		
		$wp_terms = get_terms( $taxonomy_slug, $args );
		$terms = array();
		foreach ($wp_terms as $wp_term) {

		  $terms[] = $this->get_term_object($wp_term);
		}
		return $terms;
	  }
  
 
	protected function get_term_object($wp_term) {
		if (!$wp_term) {
		  return null;
		}
		return new JSON_API_Category($wp_term);
	}
}

?>