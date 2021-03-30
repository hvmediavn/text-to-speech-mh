<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://dominhhai.com/
 * @since      1.0.0
 *
 * @package    Text_To_Speech_Mh
 * @subpackage Text_To_Speech_Mh/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

  <!--
    WARNING! Make sure that you match all Quasar related
    tags to the same version! (Below it's "@1.15.7")
  -->
  	<style>
  		.q-loading-bar {
  			z-index: 999999 !important;
  		}
  		body{
  			background: #fff;
  		}
  		.fixed, .fixed-bottom, .fixed-bottom-left, .fixed-bottom-right, .fixed-center, .fixed-full, .fixed-left, .fixed-right, .fixed-top, .fixed-top-left, .fixed-top-right, .fullscreen {
  		    z-index: 99999999 !important;
  		}
  	</style>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/quasar@1.15.7/dist/quasar.min.css" rel="stylesheet" type="text/css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous"></script>

    <!-- Add the following at the end of your body tag -->
    <div id="q-app">
    	<div class="q-pa-lg">
    		<div class="row q-col-gutter-md q-mb-md">
    			<div class="col-3">
    				<q-select v-model="file.channel" :options="channelOptions" label="Channel"  dense/>
    			</div>
    			<div class="col-3">
    				<q-select
				        dense
				        v-model="file.voice"
				        :options="voiceOptions.viettel"
				        label="Voice"
				        emit-value
				        map-options
				      />
    			</div>
    			<div class="col-3">
    				<q-select
				        dense
				        v-model="file.speed"
				        :options="speedOptions"
				        label="Speed"
				        emit-value
				       
				      />
    			</div>
    			<div class="col-2">
	    			<q-btn color="primary" icon-right="send" label="Generate MP3 Files" @click="generateFile"></q-btn>
	    			
    			</div>
    			<div class="col-1 text-right" >
	    			<q-btn round color="primary" icon="settings" size="sm" @click="icon = true"></q-btn>
    				
    			</div>
    		</div>
    		<div class="wrap-content" v-if="posts.length > 0">
	    		<div class="q-markup-table q-table__container q-table__card q-table--horizontal-separator q-table--no-wrap">
			    	<table class="q-table">
			    		<thead>
			    			<th width="50px">
			    				<q-btn round color="primary" icon="playlist_add_check" size="sm" @click="toggleSelection"></q-btn>
			    			</th>
			    			<th class="text-left" width="50px">ID</th>
			    			<th class="text-left" width="70px">Ảnh đại diện</th>
			    			<th class="text-left">Tiêu đề</th>
			    			<th class="text-center">Audio</th>
			    			<th class="text-left">Trạng thái</th>
			    		</thead>
			    		<tbody>
			    			<tr v-for="post in posts" :key="post.id">
			    				<td>
			    					<q-checkbox v-model="selection" :val="post.id"/>
			    				</td>
			    				<td>{{post.id}}</td>
			    				<td>
			    					<q-img
			    						  v-if="post._embedded.hasOwnProperty('wp:featuredmedia')"
			    					      :src="post._embedded['wp:featuredmedia'][0].source_url"
			    					      spinner-color="white"
			    					      style="height: 50px; max-width: 50px"
			    					></q-img>

			    				</td>
			    				<td>{{post.title.rendered}}</td>
			    				<td class="text-center">
			    					<q-btn v-if="post.tts_file && !post.is_running" round color="green" icon="audiotrack" size="sm" @click="openURL(post.tts_file)"></q-btn>
			    					<q-spinner-bars color="purple" size="2em" v-if="post.is_running"></q-spinner-bars>
			    				</td>
			    				<td>
			    					<q-btn round color="primary" icon="launch" size="sm" @click="openURL(BASE_URL + `/wp-admin/post.php?post=${post.id}&action=edit`)"></q-btn>
			    					<q-btn round color="purple" icon="person" size="sm" @click="openURL(post.link)"></q-btn>
			    				</td>
			    			</tr>
			    		</tbody>
			    	</table>
			    	
		    	</div>
		    	<div class="flex flex-center q-mt-lg">
		    			Số trang
			    		<q-pagination

			    		      v-model="pagination.page"
			    		      :max="pagination.max"
			    		      direction-links
			    		      boundary-links
			    		      
			    		      icon-first="skip_previous"
			    		      icon-last="skip_next"
			    		      icon-prev="fast_rewind"
			    		      icon-next="fast_forward"
			    		      :disabled="isLoading"
			    		></q-pagination>

			    		| 

			    		Số bài viết trên trang 
			    		<q-btn-dropdown color="primary" :label="pagination.per_page" class="q-ml-xs">
			    		      <q-list>
			    		      	<q-item clickable v-close-popup>
			    		      	  <q-item-section @click="pagination.per_page = 10">
			    		      	    <q-item-label>10</q-item-label>
			    		      	  </q-item-section>
			    		      	</q-item>
			    		        <q-item clickable v-close-popup>
			    		          <q-item-section @click="pagination.per_page = 20">
			    		            <q-item-label>20</q-item-label>
			    		          </q-item-section>
			    		        </q-item>

			    		        <q-item clickable v-close-popup>
			    		          <q-item-section @click="pagination.per_page = 50">
			    		            <q-item-label>50</q-item-label>
			    		          </q-item-section>
			    		        </q-item>

			    		        <q-item clickable v-close-popup>
			    		          <q-item-section @click="pagination.per_page = 100">
			    		            <q-item-label>100</q-item-label>
			    		          </q-item-section>
			    		        </q-item>

			    	

			    		      </q-list>
			    		    </q-btn-dropdown>
			    		    				
		    	</div>
		    	<q-dialog v-model="icon">
		    	      <q-card style="min-width: 400px">
		    	        <q-card-section class="row items-center q-pb-none">
		    	          <div class="text-h6">Settings</div>
		    	          <q-space />
		    	          <q-btn icon="close" flat round dense v-close-popup />
		    	        </q-card-section>

		    	        <q-card-section>
		    	          <q-toggle
					        v-model="settings.active_tts"
					        label="Bật audio trong bài viết"
					      />
		    	        </q-card-section>
		    	        <q-card-section>
		    	        	<q-btn color="primary" icon="save" label="Save settings" @click="generateFile"></q-btn>
		    	        </q-card-section>
		    	      </q-card>
	    	    </q-dialog>
	    	</div>

    	</div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/vue@^2.0.0/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quasar@1.15.7/dist/quasar.umd.min.js"></script>

    <script>
      /*
        Example kicking off the UI. Obviously, adapt this to your specific needs.
        Assumes you have a <div id="q-app"></div> in your <body> above
       */
       window.quasarConfig = {
	      brand: { // this will NOT work on IE 11
	        primary: '#e46262',
	        // ... or all other brand colors
	      },
	    
	    }
      const AJAX_URL  = "<?php echo admin_url('admin-ajax.php'); ?>";
      const BASE_URL  = "<?php echo get_site_url(); ?>";
      const viettelVoices = 
      	[
	        {
	          label: 'Nữ miền Bắc',
	          value: 'doanngocle',
	        },
	        {
	          label: 'Nữ miền Bắc 2',
	          value: 'hn-quynhanh',
	        },
	        {
	          label: 'Nam miền Bắc',
	          value: 'phamtienquan',
	        },
	        {
	          label: 'Nữ miền Nam',
	          value: 'lethiyen',
	        },
	        {
	          label: 'Nữ miền Nam 2',
	          value: 'hcm-diemmy',
	        },
	        {
	          label: 'Nam miền Nam',
	          value: 'hcm-minhquan',
	        },
	        {
	          label: 'Nữ miền Trung',
	          value: 'hue-maingoc',
	        },
	        {
	          label: 'Nam miền Trung',
	          value: 'hue-baoquoc',
	        }
	        
        ];
     
      new Vue({
        el: '#q-app',
        data: function () {
          return {
          	icon: false,
          	isLoading: false,
          	posts: [
          		
          	],
          	ttsFiles: [

          	],
          	pagination: {
	          	page: 1,
	          	max: 1,
	          	per_page: 10
	        },
	        selection: [],
          	file: {
          		channel: 'Viettel',
          		voice: 'doanngocle',
          		speed: 1
          	},
          	channelOptions: [
          	    'Zalo', 'Viettel'
  	        ],
  	        speedOptions: [
          	    0.7, 0.8, 0.9, 1.0, 1.1, 1.2, 1.3
  	        ],
  	        voiceOptions: {
  	        	viettel: viettelVoices,	
  	        },
  	        BASE_URL,
  	  		settings: {
  	  			active_tts: true
  	  		}
          }
        },
        methods: {
        	openURL(url){
        		Quasar.utils.openURL(url)
        	},
        	notify(msg, type){
        		this.$q.notify({message: msg, color: type ? 'green' : 'negative', position: 'top'})
        	},
        	buildFormData(formData, data, parentKey) {
        	  if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
        	    Object.keys(data).forEach(key => {
        	      this.buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
        	    });
        	  } else {
        	    const value = data == null ? '' : data;

        	    formData.append(parentKey, value);
        	  }
        	},
        	jsonToFormData(data) {
        	  const formData = new FormData();

        	  this.buildFormData(formData, data);

        	  return formData;
        	},
        	toggleSelection(){
        		if(this.selection.length > 0)
        			this.selection = []
        		else
        			this.selection = this.posts.map(el => el.id)
        	},
        	getPostFiles()
        	{
        		axios.post(AJAX_URL, this.jsonToFormData({action: 'ttp_get_post_files'})).then(res => {
        			this.ttsFiles = res.data.tts_files
        			this.getPosts()
        			// console.log(this.ttsFiles)
        		})
        	},
        	getPosts(){
        		const { pagination } = this
        		
        		this.isLoading = true
        		axios(`${BASE_URL}/wp-json/wp/v2/posts?_embed&per_page=${pagination.per_page == 'Tất cả' ? -1 : pagination.per_page}&page=${pagination.page}`).then(res => {

        			res.data.forEach(el => {
        				el.is_running = false
        				tts_file_exist = this.ttsFiles.find(tts => tts.id == el.id)
        				if(tts_file_exist)
	        				el.tts_file = tts_file_exist.file
	        			else
	        				el.tts_file = ''

        			})
        			this.posts = res.data
        			

	        		this.pagination.total = res.headers['x-wp-total']
	        		this.pagination.max = res.headers['x-wp-totalpages']
	        		this.isLoading = false
        		})
        	},
        	async generateFile(){
        		if(this.selection.length == 0){
        			this.notify('Lựa chọn bài viết trước khi thực hiện thao tác này.', 0)
        			return;
        		}
        		this.selection.forEach(el => {
        			const index = this.posts.findIndex(post => post.id == el)
        			this.posts[index].is_running = true
        		})
        		for (let i = 0; i < this.selection.length; i++) {
	        		const r = await axios.post(AJAX_URL, this.jsonToFormData({
	        			action: 'tts_generate_file',
	        			id: this.selection[i],
	        			file: this.file
	        		}))
	        		if(r.data)
	        		{
	        			const index = this.posts.findIndex(post => post.id == r.data.id)
	        				this.posts[index].is_running = false
	        				this.posts[index].tts_file = r.data.file
	        			this.notify(r.data.msg, r.data.code)	
	        		}
        		}

        	}
        },
        watch:{
        	'pagination.page': function(){
        		this.getPosts()
        	},
        	'pagination.per_page': function(){
        		this.pagination.page = 1
        		this.getPosts()
        	}
        },
        created(){
        	this.getPostFiles()
        	
        }
        // ...etc
      })
    </script>
