<?php
/**
Plugin Name: New Import Plugin
* NEWIMPLG
* 
* 
*/
define( 'NEWIMPLG_DIR',plugin_dir_path( __FILE__ ));
add_action('admin_menu', 'NEWIMPLG_admin_menu');

function NEWIMPLG_admin_menu(){
	add_submenu_page( 'tools.php', 'New Import Plugin', 'New Import Plugin', 'manage_options', 'new-import-plugin-setting', 'NEWIMPLG_setting_page_callback' );
}

function NEWIMPLG_setting_page_callback(){

	
	$uploaded_items = 0;
	if(isset($_POST) and !empty($_POST)){
		
		 
		$file = $_FILES['file_upload_input'];
		if($file['error'] == 0 ){
			$destination = NEWIMPLG_DIR.'uploads';
			move_uploaded_file($file['tmp_name'],$destination.'/file.zip');
			WP_Filesystem();
			$unzipfile = unzip_file( $destination.'/file.zip', $destination);
   			
			if( $unzipfile ){
				unlink($destination.'/file.zip');    
			} 
			$nowtime = time();
			foreach(glob("$destination/*.txt") as $filename){
				$path_parts = pathinfo($filename);
				
				$post_title 	=	str_replace('-',' ', $path_parts['filename']);
				$post_content 	= 	nl2br( file_get_contents($filename) );
				$matches =array();
				if (preg_match('/^\n*(.*?)\n+/',$post_content,$matches))
				{
					 $post_title = strip_tags($matches[1]);
				} 	
				$post_content 	= 	nl2br( $post_content );
		 
				// Create post object
				$post = array(
					'post_title'    => $post_title,
					'post_content'  => $post_content,
					'post_status'   => 'publish', 
					'post_category' => array($_POST['cates']),
					'tags_input'	=> $_POST['tags'],
					'post_date'		=> date('Y-m-d H:i:s',$nowtime)
				);

				// Insert the post into the database
				wp_insert_post( $post );
				unlink($filename); 
				
			
				$nowtime+=86400;
				$uploaded_items++;
			}
		}
		 
	}
	?>
	<form enctype="multipart/form-data" method="post" action=""> 
		<div>
			<h2>Import Posts</h2>
			<?php 
			if (!empty($uploaded_items))
			{
				?>
				<p>Successfully imported <?php echo$uploaded_items; ?> posts</p>
				<?php
			}
			?>
			<input  type="file" name="file_upload_input"  />
 	
			<br />
			<div style="overflow: hidden;margin: 10px;padding: 10px;background-color: #DFE7EF;">
				<h3 style="
    margin: auto;
    margin-bottom: 10px;
">Category</h3>
				<p><i>category can be added more in <a href='edit-tags.php?taxonomy=category'>here</a></i></p>
				<?php 
				$cates = get_categories();
 	 
				foreach($cates as $cate){
					?>
					<div style="width: 150px;float:left;">
						<input  type="checkbox" name="cates[]" value="<?php echo $cate->term_id; ?>" />&nbsp;<?php echo $cate->name; ?>
					</div>
					<?php
				}
				?>
			 		
			</div>
 	 
			<h3>Tags:</h3>
			<input type="text" name="tags" placeholder="Separate tags with commas" width="250px" style="
			width: 500px;
			">
			<br />
			<br />
			<input type="submit" class="button" value="Start Importing" />
		</div>
	</form>
	<?php

}