<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dominhhai.com/
 * @since      1.0.0
 *
 * @package    Text_To_Speech_Mh
 * @subpackage Text_To_Speech_Mh/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Text_To_Speech_Mh
 * @subpackage Text_To_Speech_Mh/admin
 * @author     Minh Hai <minhhai27121994@gmail.com>
 */
class Text_To_Speech_Mh_Admin {

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

	private $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->loader = new Text_To_Speech_Mh_Loader();


		add_action('admin_menu', [$this, 'add_admin_pages']);
		add_action('wp_ajax_tts_generate_file', [$this, 'tts_generate_file']);
		add_action('wp_ajax_ttp_get_post_files', [$this, 'ttp_get_post_files']);
	}
	
	public function add_admin_pages()
	{
			add_menu_page(
		        __( 'Text to Speech', 'textdomain' ),
		        'Text to Speech',
		        'manage_options',
		        'decoration_frames_images',
		        [$this, 'admin_template'],
		        'dashicons-format-gallery',
		        110
		    );
	}

	public function admin_template()
	{
		require_once plugin_dir_path( __FILE__ ) . 'partials/' .$this->plugin_name . '-admin-display.php';
	}

	public function ttp_get_post_files(){ 
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->base_prefix . "postmeta WHERE meta_key LIKE 'tts_audio_'");
		$data = [];
		if($rows)
			foreach ($rows as $key => $row) {
				$data[] = [
					'id' => $row->post_id,
					'file' => $row->meta_value,
				];
			}
		echo json_encode(['tts_files' => $data]);
		die();
	}
	public function tts_generate_file(){ 
		$id =  isset($_POST['id']) ? $_POST['id'] : null;
		$file = isset($_POST['file']) ? $_POST['file'] : null;

		if(!$id || !$file){
			echo json_encode(['code' => 0, 'msg' => 'Có lỗi xảy ra']);
			die();
		}
		if($file['channel'] == 'Viettel')
		{
			$post = get_post($id);
			if($post)
			{
				$content = $post->post_content;
				$content = preg_replace("/<img[^>]+\>/i", " ", $content);   
				$content = wp_strip_all_tags($content);   
				$this->curlViettel($content, $file, $id);
			}
			
		}
		die();
	}

	public function curlViettel($content, $file, $post_id)
	{
		// e
		$upload_path = wp_upload_dir();
		// die();
		$curl = curl_init();
		$params = [
			'text' => $content,
			'voice' => $file['voice'],
			'id' => 1,
			'without_filter' => false,
			'speed' => $file['speed'],
			'tts_return_option' => '3',
			'timeout' => 60000
		];

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://viettelgroup.ai/voice/api/tts/v1/rest/syn',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => json_encode($params),
		  CURLOPT_HTTPHEADER => array(
		    'Connection: keep-alive',
		    'Pragma: no-cache',
		    'Cache-Control: no-cache',
		    'sec-ch-ua: "Google Chrome";v="89", "Chromium";v="89", ";Not A Brand";v="99"',
		    'sec-ch-ua-mobile: ?0',
		    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36',
		    'token: ',
		    'Content-Type: application/json',
		    'Accept: */*',
		    'Origin: https://viettelgroup.ai',
		    'Sec-Fetch-Site: same-origin',
		    'Sec-Fetch-Mode: cors',
		    'Sec-Fetch-Dest: empty',
		    'Referer: https://viettelgroup.ai/en/service/tts',
		    'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7,fr-FR;q=0.6,fr;q=0.5',
		    'Cookie: WEBSVR=1; XSRF-TOKEN=eyJpdiI6Im5Lc0lVcFErekZ5dHJHOVRaaFVteFE9PSIsInZhbHVlIjoiRGxYVVU1VzBZYVdtWkdkR1BvcGRcL3ZYbjFualZIaWFONlwvXC9kY3pBT013R2RmbkpWZnhxeEQxVG1EZk04UitSSSIsIm1hYyI6ImUxMmFmMmFhYjQxM2JiZDcwNzFhNGVlZjBhZjExNTk3MjQ5YTg5ZDliM2Y1OTdkMmExMjFiMGE4MjNjMTI1ZTIifQ%3D%3D; viettel_ai_session=eyJpdiI6IjhOWE1aaVF5QVlOVkxXejMySU1vdWc9PSIsInZhbHVlIjoibkFPa1paSWh6dkF1VmdwQW9CZ2V6U1V0YkI2c2cxNE0ra2RYd29ERkk2VWxobHQxWWg4dzYwd28xUTRrMllEZiIsIm1hYyI6IjU5NmM2OGZkOGIyMDE1ZWNlYTUxODc2NDA1OWQ0MTZlYjBlOWE4ZmQ0ZDRlN2EyY2I2OGEzNmEwMzA1OTNkZDkifQ%3D%3D; _ga=GA1.2.244846069.1616662045; _gid=GA1.2.964423529.1616662045; _gat_gtag_UA_127323561_1=1; _ga_2P9BYTJLB5=GS1.1.1616662044.1.1.1616662064.40'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);

		if (!file_exists($upload_path['basedir']. '/tts_uploads')) {
		    mkdir($upload_path['basedir']. '/tts_uploads');
		}


		file_put_contents($upload_path['basedir']. '/tts_uploads/audio_'.$post_id.'.mp3', $response);
		$meta_key = 'tts_audio_'; 
		$file = $upload_path['baseurl'] . '/tts_uploads/audio_'.$post_id.'.mp3';
		$existing_pms = get_post_meta( $post_id, $meta_key, true );
		if($existing_pms)
			update_post_meta( $post_id, $meta_key, $file);
		else
			add_post_meta( $post_id, $meta_key, $file);


		echo json_encode(['code' => 1, 'file' => $file, 'id' => $post_id, 'msg' => 'Tạo tệp mp3 thành công cho bài viết: ' . $post_id ]);

		die();
	}


	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/text-to-speech-mh-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/text-to-speech-mh-admin.js', array( 'jquery' ), $this->version, false );

	}

}
