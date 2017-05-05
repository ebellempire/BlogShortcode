<?php
class BlogShortcodePlugin extends Omeka_Plugin_AbstractPlugin
{

	protected $_hooks = array(
		'initialize',
	);
	
	public function hookInitialize()
	{
	    add_shortcode('blog', array($this, 'bs_postlist'));
	}
	
	public function bs_postlist($args)
	{
	    return '<div id="bs-widget-container">'.bs_display_postlist($args).'</div>';
	}	     
	
}	

function bs_sortByDateDesc( $a, $b ) {
    return strtotime($a["inserted"]) < strtotime($b["inserted"]);
}

function bs_display_postlist($args){
	$current=get_current_record('SimplePagesPage',false);
	$blogParentId=isset($args['parent']) && ($bp=get_record('SimplePagesPage',array('slug'=>$args['parent']))) ? 
		$bp->id : $current->id;
	if($blogParentId){			
		$pages = get_db()->getTable('SimplePagesPage')->findAll();
		$total=isset($args['number']) ? filter_var($args['number'],FILTER_VALIDATE_INT)  : 10;
		$excerpt_length=isset($args['length']) ? filter_var($args['length'],FILTER_VALIDATE_INT) : 500; 
		$show_author=isset($args['author']) ? filter_var($args['author'],FILTER_VALIDATE_BOOLEAN) : true;
		$show_date=isset($args['date']) ? filter_var($args['date'],FILTER_VALIDATE_BOOLEAN) : true;
		$posts=array();
		$i=0;
		usort($pages, "bs_sortByDateDesc");
		foreach($pages as $page){
			if($i==$total) break;
			$class=(($i+1) % 2 == 0) ? 'even' : 'odd';
			$html=null;
			if($page['parent_id']==$blogParentId && $page['is_published']){
				$date=date('d M Y',strtotime($page['inserted']));
				$url=WEB_ROOT.'/'.$page['slug'];
				$userid=$page['created_by_user_id']; 
				$username=get_record_by_id('user',$userid)->name ? 
					get_record_by_id('user',$userid)->name : 
					get_record_by_id('user',$userid)->username;
				$html.='<article class="post post-position-'.($i+1).' '.$class.'">';
				$html.='<h3><a href="'.$url.'">'.$page['title'].'</a></h3>';
				$html.='<div class="byline">'.($show_date ? __('Posted on %s',$date) : null).' '.($show_author ? __('by %s',$username) : null).'</div>';
				$html.='<p>'.snippet($page['text'],0,$excerpt_length,
					'&nbsp;&hellip; <a href="'.$url.'">'.__('Read More').'</a>').'</p>';
				$html.='</article>';
				
				$posts[]=$html;
				$i++;
			}
		}
	return count($posts)>0 ? '<div class="posts">'.implode('',$posts) .'</div>' : '<p><em>No posts found!</em></p>';
	}else{
		return '<p><em>No posts found!</em></p>';
	}
}
