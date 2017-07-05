<?php

/*

Slideshow
The Andrew Version
7/22/09

2009-08-07
----------
* introduced hideNonXHTMLElements which will do just that for wc3 compliance
* ssReindexDelay - provides for delay of fade when slide show is reindexed

*/

//required to be declared
#$ssFolder='featured';

//optional
#$boxMode=3;

//controls and control show method
if(!isset($ssShowControls))$ssShowControls=true;
if(!isset($startButtonImgURL))$startButtonImgURL='/images/i/slide/button-play-1.jpg';
if(!isset($stopButtonImgURL))$stopButtonImgURL='/images/i/slide/button-pause-1.jpg';
if(!isset($nextButtonImgURL))$nextButtonImgURL='/images/i/slide/arrow-right-white-1.png';
if(!isset($prevButtonImgURL))$prevButtonImgURL='/images/i/slide/arrow-left-white-1.png';
if(!isset($galleryPreviousImgURL))$galleryPreviousImgURL='/images/i/slide/doublearrow-left-white-1.png';
if(!isset($galleryNextImgURL))$galleryNextImgURL='/images/i/slide/doublearrow-right-white-1.png';

//box mode
if(!isset($boxMode))$boxMode=1; //1=expand box to fix, 2=contract images to fit box (images will not be overstretched), 3=make box overflow:hidden;
if(!isset($boxWidth))$boxWidth=500;
if(!isset($boxHeight))$boxHeight=500;
if(!$oversizeImageNoticeEmail)$oversizeImageNoticeEmail=$developerEmail;

//display of title and/or descriptions and/or links for each picture
if(!isset($ssIntegrateText))$ssIntegrateText=false;
//these only apply if ssIntegrateText=true
if(!isset($ssTitleField))$ssTitleField='Title';
if(!isset($ssDescriptionField))$ssDescriptionField='Description';
if(!isset($ssLinkField))$ssLinkField='Link';
if(!$ssTextArrayQuery)$ssTextArrayQuery='';
if(!isset($ssCnx))$ssCnx=$qx['defCnxMethod'];
if(!isset($ssExcludeNonTextPictures))$ssExcludeNonTextPictures=false;
if(!isset($ssMergeText))$ssMergeText=false;


if(!isset($ssReindexDelay))$ssReindexDelay=3000;

if(!isset($ssRewrite))$ssRewrite=false;
if(!isset($ssUseNativeCSS))$ssUseNativeCSS=true;
if(!isset($ssRandImages))$ssRandImages=false; //if true images will have a querystring and escape caching (for testing)
if(!isset($ssUseSlideshow))$ssUseSlideshow=true;
if(!isset($ssUseDynamicTitles))$ssUseDynamicTitles=false;
if(!isset($ssUseDynamicDescriptions))$ssUseDynamicDescriptions=false;
if(!isset($ssUseEditor))$ssUseEditor=false;
if(!isset($ssDeclareCSS))$ssDeclareCSS=true;
if(!isset($ssControlsOrder))$ssControlsOrder='play,previous,next';



if(!function_exists('get_contents')){
	function get_contents(){
		/* 2008-06-30 - for handling output buffering 
		this function can either return output or start the next buffer
		*/
		$cmds=array('striptabs','beginnextbuffer');
		global $gcontents;
		unset($gcontents);
		if($a=func_get_args()){
			foreach($a as $v){
				if(in_array(strtolower($v),$cmds)){
					$v=strtolower($v);
					$$v=true;
				}
			}
		}
		$gcontents['out']=ob_get_contents();
		ob_end_clean();
		if($striptabs)$gcontents['out']=str_replace("\t",'',$gcontents['out']);
		if($beginnextbuffer){
			ob_start();
		}else{
			return $gcontents['out'];
		}
	}
}
if(!$browser){
	if(preg_match('/^Mozilla\/4/i',$_SERVER['HTTP_USER_AGENT'])){
		$browser='IE';
	}else if(preg_match('/^Mozilla\/5/i',$_SERVER['HTTP_USER_AGENT'])){
		$browser='Moz';
	}else if(!stristr($_SERVER['HTTP_USER_AGENT'],'Gigabot') && !stristr($_SERVER['HTTP_USER_AGENT'],'msnbot')){
		#mail($technicalEmail,'Unknown browser type',$_SERVER['HTTP_USER_AGENT'],$fromHdrBugs);
		$browser='Moz'; #assume
	}
}



//--------- coding before HML output here ----------------


/*

get file assets from a folder
we could get file assets from multiple folders - but that would mean modifying the global js + more
the dynamic js is what is used to run the ss
so if we filter we need to filter before this
if we add descriptions we need to add before this
we need a way to default to the file name vs. description

*/


if(!$ssPictures){
	$ssFolder=rtrim($ssFolder,'/');
	//get pictures; note that the array can be pre-declared; lowercase-keyed on the filename [could ultimately be path/filename optionally]
	$ssPictures=get_file_assets($ssFolder);
	//remove non-images
	if(count($ssPictures))
	foreach($ssPictures as $n=>$v){
		if(!@getimagesize($ssFolder.'/'.$v['name']))unset($ssPictures[$n]);
	}
}

//integrate title, description and links; exclude pictures with Active=0; exclude no-text-present pictures if requested
if($ssMergeText){
	if(!$ssTextArray && $ssTextArrayQuery){
		$ssTextArray=q($ssTextArrayQuery, O_ARRAY_ASSOC, $ssCnx);
	}
	if($ssTextArray)
	foreach($ssPictures as $n=>$v){
		if($ssTextArray[$n]){
			//text exclusion field value would typically be Active=0
			if($ssTextExclusionField && !$ssTextArray[$n][$ssTextExclusionField]){
				unset($ssPictures[$n]);
				continue;
			}
			foreach($ssTextArray[$n] as $o=>$w){
				//integrate all fields in the array
				$ssPictures[$n][$o]=$w;
			}
		}else if($ssExcludeNonTextPictures){
			unset($ssPictures[$n]);
		}
	}
}

/*
--------------------			- - - - - - - - - - - - - - - - - new
					   \	   /
					   	 \	 /
					       /\
					   	  /    \
 - - - - - - - - - - - /         \-------------------------------- old
*/
if(count($ssPictures)){
	?>
	<script language="javascript" src="<?php echo $slideshowGlobalJSURL ? $slideshowGlobalJSURL : 'http://www.relatebase-rfm.com/Library/js/global_slideshow_v130.js' ;?>" type="text/javascript"></script>
	<script language="javascript" type="text/javascript">
	// ---------------- #1 User Controlled parameters -------------
	//valley point (opacity) at which existing image is faded out enough, so that new image begins to fade in.  
	var ithreshold=<?php echo $ithreshold ? $ithreshold : 75;?>; 			
	var diff1=<?php echo $diff1 ? $diff1 : 4;?>; 						//percent opacities to fade in and out by; 1 is a slow transition, 
	var diff2=<?php echo $diff2 ? $diff2 : 4;?>;						//5 is about average, 10 is pretty fast, 20 is about the highest you want
	var inc=<?php echo $inc ? $inc : 20;?>; 							//milliseconds delay, typical is 20
	var duration=<?php echo ($ssDuration ? $ssDuration : 4)*1000?>; 	//period of time between slides
	var inDuration=<?php echo $inDuration ? $inDuration : 'false';?>; 	//true when fifo is called for duration, set false any other time
	var minstarttime=<?php echo $minstarttime ? $minstarttime : 3;?>;	//minimum wait for show to start (make sure all pictures are loaded)
	var maxstarttime=<?php echo $maxstarttime ? $maxstarttime : 20?>;	//maximum wait time (may cause missing pictures for large shows, but sometime download calcs are not reliable)
	// ------------------ #2 declare dynamic image information -----------------
	var editing=false;
	var sscalled=false;
	var totalloaded=0;
	
	//dynamically generated vars for the slideshow
	<?php
	$rand=rand(100, 1000000);
	//get all of the pictures that EXIST IN THE FILE SYSTEM, get properties and sort the array
	if($ssPictures){
		echo '//------ begin image declaration by PHP -----------'."\n";
		$i=0;
		foreach($ssPictures as $n=>$v){
			$i++;
			$pictureIdx[$i]=strtolower($v['name']);
			echo 'imgs['.$i.']= new Image(); ';
			echo 'srcs['.$i.']= "'.$ssFolder.'/'.$v['name'].($ssRandImages && isset($rand)? '?r=' . $rand : '').'";'."\n";
	
			$a=getimagesize($ssFolder.'/'.$v['name']);
			if($boxMode==1 || $boxMode==3){
				//expand box to fit images
				echo 'fwidth['.$i.']='.($a[0] ? $a[0] : 0).";\n";
				echo 'fheight['.$i.']='.($a[1] ? $a[1] : 0).";\n";
				if($boxMode==1){
					if($a[0]>$slideMaxWidth)$slideMaxWidth=$a[0];
					if($a[1]>$slideMaxHeight)$slideMaxHeight=$a[1];
				}
			}else if($boxMode==2){
				//constrain images to fit box
				$a=image_dims($a,$boxWidth,$boxHeight);
				preg_match('/width="([0-9]+)" height="([0-9]+)"/i', $a, $m);
				echo 'fwidth['.$i.']='.($m[1] ? $m[1] : 0).";\n";
				echo 'fheight['.$i.']='.($m[2] ? $m[2] : 0).";\n";
				if($m[1]>$slideMaxWidth)$slideMaxWidth=$m[1];
				if($m[2]>$slideMaxHeight)$slideMaxHeight=$m[2];
			}
			echo 'fsize['.$i.']='.($x=floor($v['size']*1024)).";\n";
			$showsize+=$x;
		}
		echo '//------ end ------'."\n";
	}
	if($boxMode==3){
		$slideMaxWidth=$boxWidth;
		$slideMaxHeight=$boxHeight;
	}
	$ssRandomIdx ? $idx=rand(1,count($ssPictures)) : (is_numeric($idx) && $idx<=count($ssPictures)? '' : $idx=1);
	$nextIdx=($idx+1>count($ssPictures) ? 1 : $idx+1);
	?>
	//----------- dynamically declared by php ------------
	var imgcount=<?php echo count($ssPictures) ? count($ssPictures) : 0?>;
	var showsize=<?php echo $showsize ? $showsize : 0?>;
	var boxWidth=<?php echo $boxWidth=($boxMode==1 ? $slideMaxWidth : $boxWidth);?>;
	var boxHeight=<?php echo $boxHeight=($boxMode==1 ? $slideMaxHeight : $boxHeight);?>;
	var ssLinkMethod=<?php echo $ssLinkMethod ? $ssLinkMethod : 0;?>;
	var ssLinkBaseURL='<?php echo $ssLinkBaseURL ? $ssLinkBaseURL : '';?>';
	var ssLinkFieldName='<?php echo $ssLinkFieldName ? $ssLinkFieldName : '';?>';
	var ssLinkPopupDims='<?php echo $ssLinkPopupDims ? $ssLinkPopupDims : '';?>';
	var ssLinkTarget='<?php echo $ssLinkTarget ? $ssLinkTarget : '';?>';
	
	var startButtonImgURL='<?php echo $startButtonImgURL ?>';
	var stopButtonImgURL='<?php echo $stopButtonImgURL ?>';
	</script>
	
	<?php //=========================================================================================== ?>
	
	<?php 
	ob_start();
	?>
	<?php if($ssUseNativeCSS){ ?>
	<style type="text/css">
	/* ------------- static styles -------------- */
	#ssComponent{position:relative;	}
	#ly2{	z-index:2; 				}
	#ly1{	z-index:1; 				}
	#ly2, #ly1{	position:absolute; 	}
	#ly2 img, #ly1 img{	border:0px;	}
	#pictureTexts{	display:none;	}
	</style>
	<?php } ?>
	<style type="text/css">
	/* ----------- dynamic styles --------------- */
	#ssWrap{
		/* overall "box" the slideshow (and controls) fit in - you can float it but it must be block, not inline; */
		width:<?php echo $boxWidth?>px;
		}
	#ssComponent{
		/* wraps ly1 and ly2 (layer 1 and 2) which are used to juggle the transparencies. You can pad this if you want */
		width:<?php echo $boxWidth?>px;
		height:<?php echo $boxHeight?>px;
		}
	#ly2, #ly1{
		width:<?php echo $boxWidth?>px;
		height:<?php echo $boxHeight?>px;
		<?php if($boxMode==3){ ?>overflow:hidden;<?php }?>
		text-align:center;
		}
	#ssControls{
		display:<?php echo $ssShowControls?'block':'none';?>;
		}
	#ssControls img{
		cursor:pointer;
		}
	</style><?php
	echo $ssCSS=get_contents();
	?>
	<?php if($ssRewrite)ob_start(); ?>
	<div id="ssWrap">
		<?php 
		//dynamic titles
		if($ssUseDynamicTitles){ 
			ob_start();
			?><h2 id="ssTitle"><?php echo $ssPictures[$pictureIdx[$idx]][$ssTitleField]?>&nbsp;</h2><?php
			echo $ssDynamicTitle=get_contents();
		}
		//slideshow itself - you should always show this
		if($ssUseSlideshow){
			ob_start();
			?><div id="ssComponent">
				<?php ob_start();?>
				<span id="topTextWait">
					please wait while the slideshow loads..
				</span>
				<?php echo $ssWait=get_contents();?>
				<div id="ly2" <?php echo 'style="'. ($browser=='IE'? 'filter:alpha(opacity=0);' : '-moz-opacity:0.0;') . '"';?>>
					<img id="img2" src="<?php echo $ssFolder.'/'.$ssPictures[$pictureIdx[$nextIdx]]['name']?>" alt="slideshow frame" />
				</div>
				<div id="ly1" <?php echo 'style="'. ($browser=='IE'? 'filter:alpha(opacity=100);' : '-moz-opacity:1.0;') . '"';?>>
					<img id="img1" src="<?php echo $ssFolder.'/'.$ssPictures[$pictureIdx[$idx]]['name']?>" alt="slideshow frame" />
				</div>
			</div><?php
			echo $ssSlideshow=get_contents();
		}
		if($ssUseDynamicDescriptions){
			ob_start();
			$attrib=($ssAllowEdit ? 'ondblclick="if(running)startStop();editing=true;toggle_description()"' : '');
			?><div id="ssDescription" <?php echo $attrib;?>><?php
			//text here
			echo $ssPictures[$pictureIdx[$idx]][$ssDescriptionField];
			?></div><?php
			echo $ssDynamicDescription=get_contents();
		}
		if($ssShowControls){ 
			ob_start();
			?><div id="ssControls">
				<div id="ctrlBg">&nbsp;</div>
				<div id="ctrlFg">
				<?php ob_start();?><span><img src="<?php echo $prevButtonImgURL?>" onclick="reindexDelay=<?php echo $ssReindexDelay?>;previousNext(-1);" title="Previous Picture"></span><?php $previousCtrl=get_contents();?>
				<?php ob_start();?><span><img src="<?php echo $nextButtonImgURL?>" onclick="previousNext(1);" title="Next Picture"></span><?php $nextCtrl=get_contents();?>
				<?php ob_start();?><span><img id="bStartStop" src="<?php echo $stopButtonImgURL?>" onclick="startStop();" title="Stop slideshow"></span><?php $playCtrl=get_contents();?>
				<?php
				$controlsOrder=array('next'=>$nextCtrl, 'previous'=>$previousCtrl, 'play'=>$playCtrl);
				foreach(explode(',',$ssControlsOrder) as $v)echo $controlsOrder[$v]
				?>
				</div>
			</div><?php
			echo $ssControls=get_contents();
		}
		//dynamic descriptions
		if($ssIntegrateText){
			ob_start();
			?><div id="pictureTexts"><?php
			//this is the titles and descriptions
			$i=0;
			foreach($ssPictures as $v){
				$i++;
				?><div id="title_<?php echo $i?>"><?php echo trim($v[$ssTitleField]) ? htmlentities($v[$ssTitleField]) : ''?></div><?php
				?><div id="description_<?php echo $i?>" filename="<?php echo $v['name']?>" <?php
				/* ---------------------------
				//handle href
				if($ssLinkMethod==1){
					?>href="<?php echo htmlentities($v['href']);?>"<?php
				}else if($ssLinkMethod==2){
					?>calc_href="<?php echo htmlentities($v['calc_href']);?>"<?php
				}
				---------------------------- */
				?>><?php 
				//handle output of description, factors are:
				#1 length and cutoff point
				#2 html vs. text
				#3 strip tags
				#4 any annotations like hyperlinking or obscuring emails
				$out= $v[$ssDescriptionField];
				$out=preg_replace('/^<p[^>]*>/i','',trim($out));
				$out=preg_replace('/<\/p>$/i','',trim($out));
				$out=trim(strip_tags($out));
				echo $out;
				?></div><?php
			}
			?></div>
			<input id="Description" name="Description" value="<?php echo h($ssPictures[$pictureIdx[$idx]][$ssDescriptionField]);?>" type="hidden" />
			<?php
			echo $ssTexts=get_contents();
		}
		?>
		<script language="javascript" type="text/javascript">
		//set first image in ly1, next image in ly2 at zero visibility
		var idx=<?php echo $idx?>;
		srcs.length-1==idx?idxNext=1:idxNext=idx+1;
		//begin loading images
		var ssUseDynamicTitles=<?php echo $ssUseDynamicTitles ? 'true':'false'?>;
		var ssUseDynamicDescriptions=<?php echo $ssUseDynamicDescriptions ? 'true':'false'?>;
		var ssUseEditor=<?php echo $ssUseEditor ? 'true':'false'?>;
		var starttime=parseFloat( new Date()/1000 );
	
		loadImagesStats(idx);
		</script>
		<?php if(false){ ?>
		<div id="pictureTextText">
			implement this later - contains hidden fields and text area to edit
		</div>
		<?php } ?>
		<input name="Idx" id="Idx" type="hidden" />
	</div>
	<?php
	if($ssShowGallery){

		//settings
		if(!isset($ssGalleryWidth))$ssGalleryWidth=65;
		if(!isset($ssGalleryHeight))$ssGalleryHeight=65;
		if(!$ssGalleryFolder)$ssGalleryFolder=$ssFolder.'/'.'gallery_thumbs_'.$ssGalleryWidth.'_'.$ssGalleryHeight;
		if(!isset($ssShowGallery))$ssShowGallery=false;
		if(!isset($ssGalleryFrameSize))$ssGalleryFrameSize=8;
		if(!isset($ssGalleryTitle))$ssGalleryTitle='Side Gallery';
		if(!isset($ssGalleryUseNativeCSS))$ssGalleryUseNativeCSS=true;
		
		//handle creation of thumbnail gallery if called for
		if($ssGalleryCreateThumbs){
			if(is_dir($ssGalleryFolder)){
				foreach($ssPictures as $v){
					if(!file_exists($ssGalleryFolder.'/'.$v['name'])){
						//attempt to create the thumbnails
						if(!function_exists('create_thumbnail'))require($FUNCTION_ROOT.'/function_create_thumbnail_v200.php');
						#code block 219419
						if(!($dims=create_thumbnail(
							$ssFolder.'/'.$v['name'], 
							$ssGalleryWidth.','.$ssGalleryHeight, 
							'', 
							$ssGalleryFolder.'/'.$v['name']
						))){
							mail($developerEmail, 'unable to create gallery thumb: error file '.__FILE__.' line '.__LINE__, get_globals(), $fromHdrBugs);
							continue;
						}
					}else{
						//well, we could see if it's OK and resize but I'd like to go home for thanksgiving
					}
				}
			}else{
				//directory doesn't exist, make it
				if(!mkdir($ssGalleryFolder)){
					//if we can't make the directory there is a problem.
					mail($developerEmail,'unable to make gallery folder, error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				}else{
					//we know we must create thumbs b/c they didn't exist
					if(!function_exists('create_thumbnail'))require($FUNCTION_ROOT.'/function_create_thumbnail_v200.php');
					$i=0;
					foreach($ssPictures as $v){
						#code block 219419
						if(!($dims=create_thumbnail(
							$ssFolder.'/'.$v['name'], 
							$ssGalleryWidth.','.$ssGalleryHeight, 
							'', 
							$ssGalleryFolder.'/'.$v['name']
						))){
							mail($developerEmail, 'unable to create gallery thumb: error file '.__FILE__.' line '.__LINE__, get_globals(), $fromHdrBugs);
							continue;
						}
					}
				}
			}
		}
		if($ssGalleryThumbs=get_file_assets($ssGalleryFolder,'normal')){
			ob_start();
			?>
			<?php if($ssGalleryUseNativeCSS){ ob_start();?>
			<style type="text/css">
			#ssGallery{
				padding:2px;
				}
			#galleryFrameGroup{
				position:relative;
				}
			.galleryFrame{
				background-color:#000;
				position:absolute;
				top:0px;
				left:0px;
				width:100%;
				}
			.galleryThumb{
				float:left;
				margin:3px;
				}
			#ssGallery .on{
				margin:2px;
				border:1px solid yellow;
				}
			#ssGallery span{
				cursor:pointer;
				}
			#ssGallery .imgctrl{
				cursor:pointer;
				}
			#ssGallery .pointer{
				cursor:auto;
				-moz-opacity:.5;
				opacity:.5;
				}
			#galleryFrameLocation{
				font-size:smaller;
				}
			</style>
			<?php echo $ssGalleryCSS=get_contents(); } ?>
			<div id="ssGallery">
				<h2><?php echo $ssGalleryTitle;?></h2>
				<?php ob_start();?>
				<div id="galleryControls">
					<img class="imgctrl" title="click to view the previous batch of images" src="<?php echo $galleryPreviousImgURL;?>" alt="prev" onclick="gallery_framenav(this,-1);" />
					<img class="imgctrl" title="click to view the next batch of images" src="<?php echo $galleryNextImgURL;?>" alt="next" onclick="gallery_framenav(this,1);" />&nbsp;&nbsp;<span id="galleryFrameLocation"><?php echo ceil($idx/$ssGalleryFrameSize)?> of <?php echo ceil(count($ssPictures)/$ssGalleryFrameSize)?></span>
				</div>
				<?php echo $ssGalleryControls=get_contents();?>
				<div id="galleryFrameGroup">
				<?php
				$i=$g=0;
				foreach($ssPictures as $n=>$v){
					if(!$ssGalleryThumbs[$n] || !file_exists($ssGalleryFolder . '/'.$v['name'])){
						//handle no-thumb-present - this will work for now
						if($mailedMissingThumbs){
							mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
							$mailedMissingThumbs=true;
						}
						$img='/images/i/spacer.gif';
					}else{
						$img=$ssGalleryFolder.'/'.$v['name'];
					}
					$i++;
					//open the group
					if(!fmod($i-1,$ssGalleryFrameSize)){
						$g++;
						?><div id="galleryframe_<?php echo $g?>" class="galleryFrame" style="display:<?php echo $g==ceil($idx/$ssGalleryFrameSize)?'block':'none';?>"><?php
					}
					?><div id="gallerythumb_<?php echo $i; ?>" class="galleryThumb<?php echo $i==$idx?' on':''?>" <?php if(!$hideNonXHTMLElements){ ?>idx="<?php echo $i?>"<?php } ?>><span title="Click here to move slideshow to this location" onclick="gallery_highlight(<?php echo $i?>);reindexDelay=<?php echo $ssReindexDelay?>; return previousNext(<?php echo $i?>,1);"><img src="<?php echo $img; ?>" alt="gallery thumbnail" /></span></div><?php
					//close the group
					if(!fmod($i,$ssGalleryFrameSize)){
						$close=$i;
						?></div><?php
					}
				}
				//close uneven group
				if($close!==$i){
					//todo: handle fillers if called for
					echo '</div>';
				}
			?></div></div><script language="javascript" type="text/javascript">
			//declare parameters of the gallery
			var galleryCount=<?php echo $i?>;
			var galleryFrameCount=<?php echo $g?>;
			var galleryFrameSize=<?php echo $ssGalleryFrameSize?>;
			</script><?php	
			echo $ssGallery=get_contents();
		}
	}
	
	
	//------------ final output ---------
	if($ssRewrite){
		$ssOutput=ob_get_contents();
		ob_end_clean();
	}
}else{
	?>
	<style type="text/css">
	#ssEmpty{
		padding:15px;
		border:1px dotted #333;
		background-color:aliceblue;
		font-size:119%;
		}
	</style>
	<div id="ssEmpty">
	This slideshow (<?php echo $ssFolder?>) has no pictures in it.<br />
	<a href="/admin/file_explorer/?uid=uploadforslide&createFolder=1&folder=<?php echo preg_replace('/^\/*images\//i','',$ssFolder);?>" onclick="window.open(this.href,'l1_fex','width=700,height-700,menubar,resizable,scrollbars'); return false;">UPLOAD PICTURES NOW</a>	</div>
	<?php
}
?>