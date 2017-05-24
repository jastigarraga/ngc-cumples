<?php
if(!function_exists("add_action")){
	die("No se puede ejecutar el plugin sin wordpress");
}
function ngc_main(){ ?>
<script> var site_url = "<?php echo site_url(); ?>"; </script>	
<?php 
	readfile(plugin_dir_path(__FILE__)."templates/index.html");
}
function ngc_add_scripts() {
  wp_register_script( 'angularjs', plugins_url('/js/angular.min.js',__FILE__));
  wp_register_script( 'ngAnimate', plugins_url('/js/angular-animate.min.js',__FILE__),["angularjs"]);
  wp_register_script( 'ngRoute', plugins_url('/js/angular-route.min.js',__FILE__),["angularjs"]);
  wp_register_script( 'ngcApp', plugins_url('/js/ngcApp.js',__FILE__),["angularjs"]);
  wp_enqueue_script("angularjs");
  wp_enqueue_script("ngAnimate");
  wp_enqueue_script("ngRoute");
  wp_enqueue_script("ngcApp");
}
add_action("admin_init","ngc_admin_enqueue_styles");
add_action( 'admin_enqueue_scripts', 'ngc_add_scripts' );

function ngc_admin_enqueue_styles(){
	wp_enqueue_style("ngcSite",plugins_url('/css/site.css',__FILE__));
}

add_menu_page("NaiGallego Cumples", "Nai Cumples", "manage_options", "NaiGallegoCumples","ngc_main","dashicons-email-alt");