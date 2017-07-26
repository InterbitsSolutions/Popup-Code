<?php
session_start();
/**
 * The Header for our theme.
 */

/*
    GIUSEPPE LIDONNICI CODE TO GEO LOCALIZATION AND REDIRECTION STARTS
*/
function url_origin( $s, $use_forwarded_host = false )
{
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $s['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
    $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url( $s, $use_forwarded_host = false )
{
    return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}
$absolute_url = full_url( $_SERVER );

$urlnew="";
// Enable full error reporting
error_reporting(0);
// Include the class
include(prohost_get_file_dir("ipInfo.inc.php"));
// Get the API key, you could set this anywhere..

// Create a new instance
$ipInfo = new ipInfo ("6f2b58a79b3c7eef0dae726e7efd55302d11af21186e7238661accbce00d0c0d");
$userIP = $ipInfo->getIPAddress();
//var_dump($ipInfo->getCity($userIP));
$dataip=$ipInfo->getCountry($userIP);
$realip=explode(";",$dataip);
if(!isset($realip[3])){
	$realip[3]="FR";	
}
$country= strtolower($realip[3]);
$activeL="";
$url=parse_url($absolute_url );

foreach($url as $part){
   $parts[]=$part;
}
$urlpath=explode("/",$parts[2]);
$_SESSION["frac"]=false;
$_SESSION["ukac"]=false;
$_SESSION["usac"]=false;
$_SESSION["esac"]=false;
$_SESSION["mxac"]=false;
$_SESSION["dnmc"]=false;
if($_SESSION['start']=="")
{
	$_SESSION['start']=false;
}

if(isset($_SESSION["llan"])){
	$country=$_SESSION["llan"];
}
if(preg_match('/x1/',$absolute_url)){
	$_SESSION["llan"]="fr";
	$_SESSION["frac"]=true;
	 header("Location: https://easytech247.com/fr/");
}
elseif(preg_match('/x2/',$absolute_url)){
	$_SESSION["llan"]="us";
	$_SESSION["usac"]=true;
	 header("Location: https://easytech247.com/");	
}
elseif(preg_match('/x3/',$absolute_url)){
	$_SESSION["llan"]="gb";
	$_SESSION["ukac"]=true;
	header("Location: https://easytech247.com/");
}

elseif(preg_match('/x4/',$absolute_url)){
	$_SESSION["llan"]="es";
	$_SESSION["esac"]=true;	
	header("Location: https://easytech247.com/es/");
}
elseif(preg_match('/x5/',$absolute_url)){
	 $_SESSION["llan"]="mx";
	 $_SESSION["mxac"]=true;
	 header("Location: https://easytech247.com/es/");
}
elseif(preg_match('/x6/',$absolute_url)){
	 $_SESSION["llan"]="da";
	 $_SESSION["dnmc"]=true;
	 header("Location: https://easytech247.com/da/");
}

elseif($country=="mx"){
   $_SESSION["mxac"]=true;

  /* if($absolute_url=="https://easytech247.com/services/free-data-backups/"){
         header("Location: https://easytech247.com/es/");
   }
   else if (preg_match('/es/',$absolute_url)){
       
   }
else if (preg_match('/services/',$absolute_url)){
       
   }
   elseif(preg_match("/es/",$absolute_url)){
       $urlnew=str_replace("/es/",'/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/fr/',$absolute_url)){
       $urlnew=str_replace('/fr/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/es/fr/',$absolute_url)){
       $urlnew=str_replace('/es/fr/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/en/',$absolute_url)){
       $urlnew=str_replace('/en/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }


   else{ */
	   if($_SESSION["start"]=="")
		{
			$_SESSION["start"]=true;
		   $urlnew=str_replace('easytech247.com/','easytech247.com/es/',$absolute_url);
		   header("Location: ".$urlnew);
		}
		
  /* }*/
   
}

elseif($country=="da"){
   $_SESSION["dnmc"]=true;

  /* if($absolute_url=="https://easytech247.com/services/free-data-backups/"){
         header("Location: https://easytech247.com/es/");
   }
   else if (preg_match('/es/',$absolute_url)){
       
   }
else if (preg_match('/services/',$absolute_url)){
       
   }
   elseif(preg_match("/es/",$absolute_url)){
       $urlnew=str_replace("/es/",'/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/fr/',$absolute_url)){
       $urlnew=str_replace('/fr/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/es/fr/',$absolute_url)){
       $urlnew=str_replace('/es/fr/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/en/',$absolute_url)){
       $urlnew=str_replace('/en/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }


   else{ */
	   if($_SESSION["start"]=="")
		{
			$_SESSION["start"]=true;
		   $urlnew=str_replace('easytech247.com/','easytech247.com/da/',$absolute_url);
		   header("Location: ".$urlnew);
		}
		
  /* }*/
   
}





elseif($country=="gb"){
  	$_SESSION["ukac"]=true;
	
	/*
   if($absolute_url=="https://easytech247.com/services/free-data-backups/"){
         header("Location: https://easytech247.com/fr/");
   }
   else if (preg_match('/uk/',$absolute_url)){
       
   }
else if (preg_match('/services/',$absolute_url)){
       
   }
   elseif(preg_match("/es/",$absolute_url)){
       $urlnew=str_replace("/es/",'/uk/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/en/fr/',$absolute_url)){
       $urlnew=str_replace('/en/fr/','/uk/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/fr/',$absolute_url)){
       $urlnew=str_replace('/fr/','/uk/',$absolute_url);
       header("Location: ".$urlnew);
   }
   
   elseif(preg_match('/en/',$absolute_url)){
       $urlnew=str_replace('/en/','/uk/',$absolute_url);
       header("Location: ".$urlnew);
   }
*/
	 
	 /*
	 if (preg_match('/fr/',$absolute_url)){
		   $urlnew=str_replace('/fr/','/',$absolute_url);
		   
		   header("Location: ".$urlnew);
	   }
	else if (preg_match('/services/',$absolute_url)){
		   
	   }
	 elseif (preg_match('/es/',$absolute_url)){
		   $urlnew=str_replace('/es/','/',$absolute_url);
		   
		   header("Location: ".$urlnew);
	   }
	   elseif(preg_match('/uk/',$absolute_url)){
		   $urlnew=str_replace('/uk/','/',$absolute_url);
		   header("Location: ".$urlnew);
	   }
	   elseif(preg_match('/mx/',$absolute_url)){
		   
		   
		   $urlnew=str_replace('/mx/','/',$absolute_url);
		   header("Location: ".$urlnew);
	   }

   else{ */	
		if($_SESSION["start"]=="")
		{	
			$_SESSION["start"]=true;
			$urlnew=str_replace('easytech247.com/','easytech247.com/',$absolute_url);
			header("Location: ".$urlnew);
		 }
  /* } */
   
}
elseif($country=="fr"){
 $_SESSION["frac"]=true;
 /*
  if(preg_match('/uk/',$absolute_url)){
       $urlnew=str_replace('/uk/','/fr/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/en/',$absolute_url)){
       $urlnew=str_replace('/en/','/fr/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/es/',$absolute_url)){
       $urlnew=str_replace('/es/','/fr/',$absolute_url);
       header("Location: ".$urlnew);
   }   
   else{	  */  
		if($_SESSION["start"]=="")
		{
		  $_SESSION["start"]=true;
		   $urlnew=str_replace('easytech247.com/','easytech247.com/fr/',$absolute_url);
		   header("Location: ".$urlnew);
		  
		}
  /* } */
}
elseif($country=="es"){
    $_SESSION["esac"]=true;

	/*
   if (preg_match('/es/',$absolute_url)){
       
   }
else if (preg_match('/services/',$absolute_url)){
       
   }
   elseif(preg_match('/uk/',$absolute_url)){
       $urlnew=str_replace('/uk/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/en/',$absolute_url)){
       $urlnew=str_replace('/en/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/fr/',$absolute_url)){
       $urlnew=str_replace('/fr/','/es/',$absolute_url);
       header("Location: ".$urlnew);
   }
   else{  */
	    if($_SESSION["start"]=="")
		{		
		   $_SESSION["start"]=true;
		   $urlnew=str_replace('easytech247.com/','easytech247.com/es/',$absolute_url);
		   header("Location: ".$urlnew);
		 }
  /* } */


}
else{
   $_SESSION["usac"]=true;
   $activeL="US";  
  /* if (preg_match('/fr/',$absolute_url)){
       $urlnew=str_replace('/fr/','/',$absolute_url);
	   
       header("Location: ".$urlnew);
   }
else if (preg_match('/services/',$absolute_url)){
       
   }
 elseif (preg_match('/es/',$absolute_url)){
       $urlnew=str_replace('/es/','/',$absolute_url);
	   
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/uk/',$absolute_url)){
       $urlnew=str_replace('/uk/','/',$absolute_url);
       header("Location: ".$urlnew);
   }
   elseif(preg_match('/mx/',$absolute_url)){
	   
	   
       $urlnew=str_replace('/mx/','/',$absolute_url);
       header("Location: ".$urlnew);
   } */
}


/*
    GIUSEPPE LIDONNICI CODE TO GEO LOCALIZATION AND REDIRECTION ENDS
*/


// Theme init - don't remove next functions! Load custom options
prohost_theme_init();
$theme_init =  prohost_theme_options();
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php echo 'scheme_' . esc_attr($theme_init['body_scheme']); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1<?php if (prohost_get_theme_option('responsive_layouts')=='yes') echo ', maximum-scale=1'; ?>">
	<meta name="format-detection" content="telephone=no">

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php
    // Page preloader options
    prohost_page_preloader_style_css();

	if ( !function_exists('has_site_icon') || !has_site_icon() ) {
		$favicon = prohost_get_custom_option('favicon');
		if (!$favicon) {
			if ( file_exists(prohost_get_file_dir('skins/'.($theme_skin).'/images/favicon.ico')) )
				$favicon = prohost_get_file_url('skins/'.($theme_skin).'/images/favicon.ico');
			if ( !$favicon && file_exists(prohost_get_file_dir('favicon.ico')) )
				$favicon = prohost_get_file_url('favicon.ico');
		}
		if ($favicon) {
			?><link rel="icon" type="image/x-icon" href="<?php echo esc_url($favicon); ?>" /><?php
		}
	}

	wp_head();
	?>
<meta name="google-site-verification" content="9mwXPcB0DrOKcJQrI8T3IaRTGWN9JoiGjLB8Wlvnc98" />
</head>

<body <?php body_class(); ?>>
	<?php
	prohost_profiler_add_point(esc_html__('BODY start', 'prohost'));
	
	echo force_balance_tags(prohost_get_custom_option('gtm_code'));

	// Page preloader
    if (($preloader = prohost_get_theme_option('page_preloader')) != '') { ?>
        <div id="page_preloader"></div><?php
    }

	do_action( 'before' );

	// Add TOC items 'Home' and "To top"
    prohost_add_toc();
	?>

	<?php if ( !prohost_param_is_off(prohost_get_custom_option('show_sidebar_outer')) ) { ?>
	<div class="outer_wrap">
	<?php } ?>

	<?php get_template_part(prohost_get_file_slug('sidebar_outer.php')); ?>

	<?php
		$class = $style = '';
		if ($theme_init['body_style']=='boxed' || prohost_get_custom_option('bg_image_load')=='always') {
			if (($img = (int) prohost_get_custom_option('bg_image', 0)) > 0)
				$class = 'bg_image_'.($img);
			else if (($img = (int) prohost_get_custom_option('bg_pattern', 0)) > 0)
				$class = 'bg_pattern_'.($img);
			else if (($img = prohost_get_custom_option('bg_color', '')) != '')
				$style = 'background-color: '.($img).';';
			else if (prohost_get_custom_option('bg_custom')=='yes') {
				if (($img = prohost_get_custom_option('bg_image_custom')) != '')
					$style = 'background: url('.esc_url($img).') ' . str_replace('_', ' ', prohost_get_custom_option('bg_image_custom_position')) . ' no-repeat fixed;';
				else if (($img = prohost_get_custom_option('bg_pattern_custom')) != '')
					$style = 'background: url('.esc_url($img).') 0 0 repeat fixed;';
				else if (($img = prohost_get_custom_option('bg_image')) > 0)
					$class = 'bg_image_'.($img);
				else if (($img = prohost_get_custom_option('bg_pattern')) > 0)
					$class = 'bg_pattern_'.($img);
				if (($img = prohost_get_custom_option('bg_color')) != '')
					$style .= 'background-color: '.($img).';';
			}
		}
	?>

	<div class="body_wrap<?php echo !empty($class) ? ' '.esc_attr($class) : ''; ?>"<?php echo !empty($style) ? ' style="'.esc_attr($style).'"' : ''; ?>>

		<?php
		if ($theme_init['video_bg_show']) {
			$youtube = prohost_get_custom_option('video_bg_youtube_code');
			$video   = prohost_get_custom_option('video_bg_url');
			$overlay = prohost_get_custom_option('video_bg_overlay')=='yes';
			if (!empty($youtube)) {
				?>
				<div class="video_bg<?php echo !empty($overlay) ? ' video_bg_overlay' : ''; ?>" data-youtube-code="<?php echo esc_attr($youtube); ?>"></div>
				<?php
			} else if (!empty($video)) {
				$info = pathinfo($video);
				$ext = !empty($info['extension']) ? $info['extension'] : 'src';
				?>
				<div class="video_bg<?php echo !empty($overlay) ? ' video_bg_overlay' : ''; ?>"><video class="video_bg_tag" width="1280" height="720" data-width="1280" data-height="720" data-ratio="16:9" preload="metadata" autoplay loop src="<?php echo esc_url($video); ?>"><source src="<?php echo esc_url($video); ?>" type="video/<?php echo esc_attr($ext); ?>"></source></video></div>
				<?php
			}
		}
		?>

		<div class="page_wrap">

			<?php
			prohost_profiler_add_point(esc_html__('Before Page Header', 'prohost'));
			// Top panel 'Above' or 'Over'
			if (in_array($theme_init['top_panel_position'], array('above', 'over'))) {
				prohost_show_post_layout(array(
					'layout' => $theme_init['top_panel_style'],
					'position' => $theme_init['top_panel_position'],
					'scheme' => $theme_init['top_panel_scheme']
					), false);
				prohost_profiler_add_point(esc_html__('After show menu', 'prohost'));
			}
			// Slider
			get_template_part(prohost_get_file_slug('templates/headers/_parts/slider.php'));
			// Top panel 'Below'
			if ($theme_init['top_panel_position'] == 'below') {
				prohost_show_post_layout(array(
					'layout' => $theme_init['top_panel_style'],
					'position' => $theme_init['top_panel_position'],
					'scheme' => $theme_init['top_panel_scheme']
					), false);
				prohost_profiler_add_point(esc_html__('After show menu', 'prohost'));
			}

			// Top of page section: page title and breadcrumbs
			$show_title = prohost_get_custom_option('show_page_title')=='yes';
			$show_navi = $show_title && is_single() && prohost_is_woocommerce_page();
			$show_breadcrumbs = prohost_get_custom_option('show_breadcrumbs')=='yes';
			if ($show_title || $show_breadcrumbs) {
				?>
				<div class="top_panel_title top_panel_style_<?php echo esc_attr(str_replace('header_', '', $theme_init['top_panel_style'])); ?> <?php echo (!empty($show_title) ? ' title_present'.  ($show_navi ? ' navi_present' : '') : '') . (!empty($show_breadcrumbs) ? ' breadcrumbs_present' : ''); ?> scheme_<?php echo esc_attr($theme_init['top_panel_scheme']); ?>">
					<div class="top_panel_title_inner top_panel_inner_style_<?php echo esc_attr(str_replace('header_', '', $theme_init['top_panel_style'])); ?> <?php echo (!empty($show_title) ? ' title_present_inner' : '') . (!empty($show_breadcrumbs) ? ' breadcrumbs_present_inner' : ''); ?>">
						<div class="content_wrap">
							<?php
							if ($show_title) {
								if ($show_navi) {
									?><div class="post_navi"><?php 
										previous_post_link( '<span class="post_navi_item post_navi_prev">%link</span>', '%title', true, '', 'product_cat' );
										echo '<span class="post_navi_delimiter"></span>';
										next_post_link( '<span class="post_navi_item post_navi_next">%link</span>', '%title', true, '', 'product_cat' );
									?></div><?php
								} else {
									?><h1 class="page_title"><?php echo strip_tags(prohost_get_blog_title()); ?></h1><?php
								}
							}
							if ($show_breadcrumbs) {
								?><div class="breadcrumbs"><?php if (!is_404()) prohost_show_breadcrumbs(); ?></div><?php
							}
							?>
						</div>
					</div>
				</div>
				<?php
			}
			?>

			<div class="page_content_wrap page_paddings_<?php echo esc_attr(prohost_get_custom_option('body_paddings')); ?>">

				<?php
				prohost_profiler_add_point(esc_html__('Before Page content', 'prohost'));
				// Content and sidebar wrapper
				if ($theme_init['body_style']!='fullscreen') prohost_open_wrapper('<div class="content_wrap">');

                //*+ Woo content
                if (function_exists('is_shop')) {
                    if (file_exists(prohost_get_file_dir('templates/_parts/woo-content.php')) && is_shop()) {
                        get_template_part(prohost_get_file_slug('templates/_parts/woo-content.php'));
                    }
                }

				// Main content wrapper
				prohost_open_wrapper('<div class="content">');

				?>