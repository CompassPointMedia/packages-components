<?php
/*
2009-07-13
----------
* added ssRandomIdx - idx ranges from 1-count of images if true

2009-06-23
----------
* added ssControls with ability to position absolutely over the view area
* if no pictures in folder, slide doesn't show - div with link to upload pictures does

2009-06-17
----------
* began more sound css structure and ob functionality like other components; as of 12:40 pm I have only the slide component working, not the titles or description or controls/editor/indices.  However we're off to a solid start

2009-06-15
----------
integrated from 3 separate files,
* moved js file to symlink in Library/js - path is now absolute to www.relatebase-rfm.com like google map api
* removed required functions (must be present esp. if using database)

Slideshow 1.20
------------------------------------------------------
This requires you point to a folder with images in it.
* you must have these functions available:
	xml_read_tags() - version 1.35
	get_file_assets() - version 1.0
	image_dims() - version 1.0
* the slideshow will get an array $ssPictures using get_picture_assets($ssFolder);
* this only works as one slideshow per page







------------------------------------------- */


//required to be declared
#$ssFolder='featured';




//optional
#$boxMode=3;

//controls and control show method
if(!isset($ssShowControls))$ssShowControls=true;
if(!isset($startButtonImgURL))$startButtonImgURL='/images/i/start.gif';
if(!isset($stopButtonImgURL))$stopButtonImgURL='/images/i/stop.gif';
if(!isset($nextButtonImgURL))$nextButtonImgURL='/images/i/rt.gif';
if(!isset($prevButtonImgURL))$prevButtonImgURL='/images/i/lft.gif';

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



if(!isset($ssRewrite))$ssRewrite=false;
if(!isset($ssUseNativeCSS))$ssUseNativeCSS=true;
if(!isset($ssRandImages))$ssRandImages=false; //if true images will have a querystring and escape caching (for testing)
if(!isset($ssUseSlideshow))$ssUseSlideshow=true;
if(!isset($ssUseDynamicTitles))$ssUseDynamicTitles=false;
if(!isset($ssUseDynamicDescriptions))$ssUseDynamicDescriptions=false;
if(!isset($ssUseEditor))$ssUseEditor=false;
if(!isset($ssDeclareCSS))$ssDeclareCSS=true;

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



$ssFolder=rtrim($ssFolder,'/');
//get pictures; note that the array can be pre-declared; lowercase-keyed on the filename [could ultimately be path/filename optionally]
if(!isset($ssPictures))$ssPictures=get_file_assets($ssFolder);

//remove non-images
if(count($ssPictures))
foreach($ssPictures as $n=>$v){
	if(!@getimagesize($ssFolder.'/'.$v['name']))unset($ssPictures[$n]);
}

//integrate title, description and links; exclude pictures with Active=0; exclude no-text-present pictures if requested
if($ssIntegrateText && !$ssTextArray && $ssTextArrayQuery){
	$ssTextArray=q($ssTextArrayQuery, O_ARRAY_ASSOC, $ssCnx);
}
if($ssIntegrateText && count($ssTextArray) && count($ssPictures)){
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

--------------------			 		- - - - - - - - - - - - - - - - - new
							 \		   /
						   	 \	 /
						       /\
					   	  /    \
 - - - - - - - - - - - /         \-------------------------------- old



*/
if(count($ssPictures)){
	?>
	<script language="javascript" src="<?php echo $slideshowGlobalJSURL ? $slideshowGlobalJSURL : 'http://www.relatebase-rfm.com/Library/js/global_slideshow_v120.js' ;?>" type="text/javascript"></script>
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
	#pictureTitles{	display:none;	}
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
			?><h2 id="ssDynamicTitle"><?php echo $ssPictures[$pictureIdx[$idx]][$ssTitleField]?>&nbsp;</h2><?php
			echo $ssDynamicTitle=get_contents();
		}
		//slideshow itself - you should always show this
		if($ssUseSlideshow){
			ob_start();
			?><div id="ssComponent">
				<span id="topTextWait">
					please wait while the slideshow loads..
				</span>
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
			?><div id="pictureTextHTML" <?php echo $attrib;?>><?php
			//text here
			echo $ssPictures[$pictureIdx[$idx]][$ssDescriptionField];
			?></div><?php
			echo $ssDynamicDescription=get_contents();
		}
		if($ssShowControls){ 
			ob_start();
			?><div id="ssControls">
				<img src="<?php echo $prevButtonImgURL?>" onclick="previousNext(-1);" title="Previous Picture">
				<img src="<?php echo $nextButtonImgURL?>" onclick="previousNext(1);" title="Next Picture">
				<img id="bStartStop" src="<?php echo $stopButtonImgURL?>" onclick="startStop();" title="Stop slideshow">
			</div><?php
			echo $ssControls=get_contents();
		}
		//dynamic descriptions
		if($ssIntegrateText){
			ob_start();
			?><div id="pictureTitles"><?php
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
			<input id="Description" name="Description" value="<?php echo $pictures[$pictureIdx[$idx]][$ssDescriptionField];?>" type="hidden" />
			<?php
			echo $ssTextDescription=get_contents();
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