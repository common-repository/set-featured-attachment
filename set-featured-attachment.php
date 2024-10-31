<?php
/*************************************************************************************************
Plugin Name: Set Featured Attachment
Plugin URI: http://wordpress.org/plugins/set-featured-attachment
Author URI: http://arkapravamajumder.com
Author: Arkaprava majumder
Version:1.0
Description: This plugin will create "set featured attachment" like "set featured image on post and page".
License: GPLV2
***************************************************************************************************/

class SFA { 
	function sfa() {
		// for set featured attachment metabox
		add_action('add_meta_boxes',array(&$this,'sfa_meta_box' )); 
		// save set featured attachment metabox
		add_action('save_post', array(&$this,'save_sfa_attachment'));
		// add enctype into the form
		add_action('post_edit_form_tag', array(&$this,'update_sfa_form'));
		// Add featured attachment to the content
		add_filter( 'the_content',array(&$this,'sfa_alter_the_content'));
		// Add shortcode of featured attachment
		add_shortcode('the_post_attachment',array(&$this,'sfa_the_post_attachment'));
		
	}
	function sfa_meta_box() {
		// Define  set featured attachment for posts
    		add_meta_box('sfa_attachment','Featured Attachment','sfa_attachment', 'post','side');
		// Define  set featured attachment for pages
    		add_meta_box('sfa_attachment','Featured Attachment','sfa_attachment', 'page','side');
		function sfa_attachment( $post) { // Function set featured attachment for posts
			wp_nonce_field(plugin_basename(__FILE__), 'sfa_attachment_nonce');
       			$value = get_post_meta( $post->ID, 'sfa_attachment', true ); 
	    		$html = '<p class="description">';
			if(isset($value['url']) && !empty($value['url'])) {
				$html.= '<a target="_blank" href="'.$value['url'].'" >';
				$html.=basename($value['url']);
				$html.= '</a>';
 
			}
			else {
				$html.= 'Upload your Attachment here.';
			}
	    		$html.= '</p>';
	    		$html.= '<input id="sfa_attachment" name="sfa_attachment" value="" size="25" type="file">';
	     		echo $html;
	 
		} 
	}
	function save_sfa_attachment( $id ) {
		if(!wp_verify_nonce($_POST['sfa_attachment_nonce'], plugin_basename(__FILE__))) {
      			return $id;
    		}
           	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      			return $id;
    		} 
           	if('page' == $_POST['post_type']) {
      			if(!current_user_can('edit_page', $id)) {
        			return $id;
      			} 
    		} else {
        		if(!current_user_can('edit_page', $id)) {
            			return $id;
        		} 
    		}
		if(!empty($_FILES['sfa_attachment']['name'])) {
	        $arr_file_type = wp_check_filetype(basename($_FILES['sfa_attachment']['name']));
	        $uploaded_type = $arr_file_type['type'];
	        $upload = wp_upload_bits($_FILES['sfa_attachment']['name'], null, file_get_contents($_FILES['sfa_attachment']['tmp_name']));
                	if(isset($upload['error']) && $upload['error'] != 0) {
                		wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            		} else {
				$value = get_post_meta( $post->ID, 'sfa_attachment', true ); 
				if(!empty($value)) {
					unlink($value['url']);				
				}
                		add_post_meta($id, 'sfa_attachment', $upload);
                		update_post_meta($id, 'sfa_attachment', $upload);    
            		} 	
		}
	}
	function update_sfa_form() {
		echo ' enctype="multipart/form-data"';
	}
	function sfa_alter_the_content( $content ) {
		$post_id=$GLOBALS['post']->ID;
		$sfa_attachment = get_post_meta( $post_id, 'sfa_attachment', true ); 
		$post_attachment.='<a target="_blank" href="'.$sfa_attachment['url'].'" >';
		$post_attachment.=basename($sfa_attachment['url']);
		$post_attachment.='</a>';
		$content =$content."<br/>".$post_attachment;
		return $content;
	}
	function sfa_the_post_attachment( ) {
		$post_id = get_the_ID();
		if(!empty( $post_id )) {
			$sfa_attachment = get_post_meta( $post_id, 'sfa_attachment', true ); 
			$post_attachment.='<a target="_blank" href="'.$sfa_attachment['url'].'" >';
			$post_attachment.=basename($sfa_attachment['url']);
			$post_attachment.='</a>';
			echo $post_attachment;
		}
		
	}
}
$sfa=new SFA;
