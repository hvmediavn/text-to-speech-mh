<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://dominhhai.com/
 * @since      1.0.0
 *
 * @package    Text_To_Speech_Mh
 * @subpackage Text_To_Speech_Mh/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Text_To_Speech_Mh
 * @subpackage Text_To_Speech_Mh/public
 * @author     Minh Hai <minhhai27121994@gmail.com>
 */
class Text_To_Speech_Mh_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_filter( 'the_content', [$this, 'filter_the_content_in_the_main_loop'], 1 );
		 
		
	}

	public function  filter_the_content_in_the_main_loop( $content ) {
	 
	    // Check if we're inside the main loop in a single Post.

	    if ( is_singular() ) {
		    	$id = get_the_ID();
	    		$tts_file = get_post_meta($id , 'tts_audio_', true);
	    		if($tts_file):
	    		$thumbnail = get_the_post_thumbnail_url($id);
	    		if(!$thumbnail)
	    			$thumbnail = 'https://imgv3.fotor.com/images/side/sideimage-one-tap-enhance.jpg';
	    		ob_start();
	        ?>
	        	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aplayer@1.10.0/dist/APlayer.min.css">
	        	<script src="https://cdn.jsdelivr.net/npm/aplayer@1.10.0/dist/APlayer.min.js"></script>
	        	<div id="aplayer"></div>
	        	<script>
	        		const ap = new APlayer({
	        		    container: document.getElementById('aplayer'),
	        		    audio: [{
	        		        name: '<?php echo get_the_title($id); ?>',
	        		        artist: 'Audio',
	        		        url: '<?php echo $tts_file ?>',
	        		        cover: '<?php echo $thumbnail ?>'
	        		    }]
	        		});
	        	</script>

	        	<?php echo $content ?>
	        <?php
	        $content = ob_get_clean();
		    endif;
	    }
	 
	    return $content;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Text_To_Speech_Mh_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Text_To_Speech_Mh_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/text-to-speech-mh-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Text_To_Speech_Mh_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Text_To_Speech_Mh_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/text-to-speech-mh-public.js', array( 'jquery' ), $this->version, false );

	}

}
