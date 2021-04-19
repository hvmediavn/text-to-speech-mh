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
include_once "vendor/phpmp3.php";
class Text_To_Speech_Mh_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	private $settings;
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
		$settings = get_option('tts_settings', false);
		if($settings)
			$this->settings = json_decode($settings, true);
		else{
			$settings = add_option('tts_settings', '{"active":"true","autoplay":"false","zalo_tokens":""}');
		}

		add_action('admin_menu', [$this, 'add_admin_pages']);
		add_action('wp_ajax_tts_generate_file', [$this, 'tts_generate_file']);
		add_action('wp_ajax_tts_get_post_files', [$this, 'tts_get_post_files']);
		add_action('wp_ajax_tts_remove_post_files', [$this, 'tts_remove_post_files']);
		add_action('wp_ajax_tts_remove_all', [$this, 'tts_remove_all']);
		add_action('wp_ajax_tts_options', [$this, 'tts_options']);
	}
	
	public function add_admin_pages()
	{
			add_menu_page(
		        __( 'Text to Speech', 'textdomain' ),
		        'Text to Speech',
		        'manage_options',
		        'tts_mh',
		        [$this, 'admin_template'],
		        'dashicons-controls-volumeon',
		        110
		    );
	}

	public function admin_template()
	{
		require_once plugin_dir_path( __FILE__ ) . 'partials/' .$this->plugin_name . '-admin-display.php';
	}

	public function tts_remove_all(){
		global $wpdb;
		$upload_path = wp_upload_dir();
		$files = glob($upload_path['basedir']. '/tts_uploads/*'); // get all file names
		// print_r($files);
		foreach($files as $file){ // iterate files
		  if(is_file($file)) {
		    unlink($file); // delete file
		  }
		}
		$rows = $wpdb->get_results( "DELETE FROM " . $wpdb->base_prefix . "postmeta WHERE meta_key LIKE 'tts_audio_'");
		echo json_encode(['success' => 1, 'msg' => 'Thao thác thành công!']);
		die();
	}

	public function tts_options()
	{
		$act = isset($_POST['act']) ? $_POST['act'] : null;
		$key = isset($_POST['key']) ? $_POST['key'] : null;
		if($act && $key)
		{
			if($act == 'get')
			{
				$data = get_option($key, false);
				echo json_encode(['success' => 1, 'data' => $data ? json_decode($data) : null]);
			}
			if($act == 'set')
			{
				$data = isset($_POST['data']) ? $_POST['data'] : null;
				if(!$data){
					echo json_encode(['success' => 0, 'msg' => 'Có lỗi xảy ra!', 'data' => json_decode($data)]);
					die();
				}
				$option = get_option($key, false);
				if( $option ) {
				   update_option($key, json_encode($data));
				}else {
				
				   add_option( $key, json_encode($data));
				}
				$data = get_option($key, false);
				echo json_encode(['success' => 1, 'msg' => 'Lưu dữ liệu thành công!', 'data' => json_decode($data)]);
			}

		}
		die();
	}
	public function tts_get_post_files(){ 
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

	public function tts_remove_post_files(){ 
		$ids =  isset($_POST['ids']) ? $_POST['ids'] : null;
		
		if(!$ids){
			echo json_encode(['code' => 0, 'msg' => 'Có lỗi xảy ra']);
			die();
		}
		$upload_path = wp_upload_dir();

		foreach ($ids as $key => $id) {
			$file = $upload_path['basedir']. '/tts_uploads/audio_'.$id.'.mp3';
			
			if (file_exists($upload_path['basedir']. '/tts_uploads')) 
				unlink($file);
			delete_post_meta($id, 'tts_audio_');
		}
		echo json_encode(['code' => 1, 'msg' => 'Thao tác xóa hoàn tất']); 
		die();
		
	}

	public function tts_generate_file(){ 
		$id =  isset($_POST['id']) ? $_POST['id'] : null;
		$file = isset($_POST['file']) ? $_POST['file'] : null;

		if(!$id || !$file){
			echo json_encode(['code' => 0, 'msg' => 'Có lỗi xảy ra']);
			die();
		}
	
		$post = get_post($id);
		if($post)
		{
			$content = $post->post_content;
			$content = preg_replace("/<img[^>]+\>/i", " ", $content);   
			$content = '..........' . wp_strip_all_tags($content) . '..........';   
			$this->curlAudio($content, $file, $id);
		}
			
		die();
	}

	public function curlAudio($content, $file, $post_id)
	{
		// e
		$upload_path = wp_upload_dir();
		// die();
		
		
		if($file['channel'] == 'Viettel'){
			$params = [
				'text' => $content,
				'voice' => $file['voice'],
				'id' => 1,
				'without_filter' => false,
				'speed' => $file['speed'],
				'tts_return_option' => '3',
				'timeout' => 60000
			];
			$response = $this->viettel($params);
		}

		if($file['channel'] == 'Zalo'){
			$params = [
				'input' => $content,
				'speaker_id' => $file['voice'],
				'speed' => $file['speed'],
				
			];
			
			$arrays = $this->zalo($params);
			// print_r($arrays);
			// die();
			sleep(2);
			$b = null;
			foreach ($arrays as $key => $d) {
					$b  .= file_get_contents($d);
			}
			$response = $b;
		}
		
	
		if (!file_exists($upload_path['basedir']. '/tts_uploads')) {
		    mkdir($upload_path['basedir']. '/tts_uploads');
		}
		$file_dir = $upload_path['basedir']. '/tts_uploads/audio_'.$post_id.'.mp3';



		file_put_contents($file_dir, $response);
		
		if($file['channel'] == 'Viettel'){
			$audio = new PHPMP3($file_dir);
			$audio->setFileInfoExact();
			$time = $audio->time;
			$mp3 = $audio->extract(5, $time - 10);
			$mp3->save($file_dir);
		}


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


	protected function viettel($params)
	{
		$curl = curl_init();
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
		return $response;
	}


	public function zalo($params)
	{

		$params['input'] = preg_replace( "/\r|\n/", "", $params['input'] );
		$urls = [];
		$x = 1990;
		$lines = explode("\n", wordwrap($params['input'], $x));
		
		foreach ($lines as $key => $line) {
			$param_new = [
				'input' => $line,
				'speaker_id' => $params['speaker_id'],
				'speed' => $params['speed'],
				'encode_type' => 1
				
			];

			$tokens = preg_split('/\r\n|[\r\n]/', $this->settings['zalo_tokens']);
			foreach ($tokens as $key => $token) {
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => 'https://api.zalo.ai/v1/tts/synthesize',
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'POST',
				  CURLOPT_POSTFIELDS => http_build_query($param_new),
				  CURLOPT_HTTPHEADER => array(
				    'apikey: ' . trim($token),
				    'Content-Type: application/x-www-form-urlencoded'
				  ),
				));

				$response = curl_exec($curl);

				curl_close($curl);
				$response = json_decode($response, true);
				// print_r($response);
				if(!$response['error_code']){
					$urls[] = $response['data']['url'];
					break;
				}
			}

			
		}

		// print_r($url);
		// die();
		
		
		return $urls;
		
	}

	public function generateRandomString($length = 10) {
	    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}


	public function curlGetFile($url)
	{
		$url = $url;
		$process = curl_init($url); 
		curl_setopt($process, CURLOPT_HEADER, 0); 
		curl_setopt($process, CURLOPT_POST, 1); 
		curl_setopt($process, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($process,CURLOPT_CONNECTTIMEOUT,1);
		$response = curl_exec($process); 
		curl_close($process); 

		return $response;
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
