<?php

factors to consider
	how many images do I have[1]
	what configuration am I asking for
	what main and gallery resolution am I being asked for
	do any or all of the images I am asking for have a extra large size
	{am in in a list or focus view - though this may not be significant}
[1] good time i think to convert and move them


//get all images associated with this product
if($pictures=q("SELECT", O_ARRAY) | $pictures=get_pictures_from_SKU($rdp['SKU'])){
	$imageMode=($qr['count']?'byDatabase':'byFilesystem');
	
	
	
}else{
	//handle no image present
	if($adminMode){
	
	}else{
	
	}
}

//notifications for lack of images - build as an email list and hand off to page_end()
	* new notification system





what do I get in terms of images
if(configuration==list)
	just the key image with popup (imageDisplay=
	in what resolution=medium / {thumb|small|medium|large|extralarge}
else
	full slide (imageDisplay=
	in what resolution=large / {thumb|small|medium|large|extralarge}


	configuration
		no image = 0
		key image = 2
		key image with popup = 8
		key image with slide show = 32
		key image with zooming = 128




<script language="javascript" type="text/javascript">
//declare parameters of the gallery
var galleryCount=8;
var galleryFrameCount=1;
var galleryFrameSize=16;</script>
<script language="javascript" type="text/javascript">
//set first image in ly1, next image in ly2 at zero visibility
var idx=1;
srcs.length-1==idx?idxNext=1:idxNext=idx+1;
//begin loading images
var ssUseDynamicTitles=false;
var ssUseDynamicDescriptions=true;
var ssUseEditor=false;
var starttime=parseFloat( new Date()/1000 );
loadImagesStats(idx);</script>
<input name="Idx" id="Idx" type="hidden" />

<div id="ssOverWrap">
	<?php //-------------------- actual 2-layer slide show object ---------------------- ?>
	<div id="ssWrap">
		<div id="ssComponent">
			<div id="ly2" style="display:none;-moz-opacity:0.0;">
				<img id="img2" style="max-width:500px;" src="images/stock/all/600x600/2 09 Paseo Miramar Living Room.jpg" alt="slideshow frame" />
			</div>
			<div id="ly1" style="display:block;-moz-opacity:1.0;">
				<img id="img1" style="max-width:500px;" src="images/stock/teamviewer-disks/palisades dusk2 [640x480].jpg" alt="slideshow frame" />
			</div>
		</div>
		<div id="pictureTexts"><?php
		if($pictureTexts){
			foreach($pictureTexts as $n=>$v){
				?><div id="title_1">Living Room - Red Chairs</div><?php
			}
		}
		?></div>
		<input id="Description" name="Description" value="Living Room - Red Chairs" type="hidden" />
	</div>

	<?php //-------------------- description of the current slide ---------------------- ?>
	<div id="ssDescription" >Living Room - Red Chairs</div>


	<?php //-------------------- thumbnail gallery ---------------------- ?>
	<div id="ssGallery">
		<div id="galleryControls" style="visibility:hidden;">
			<?php
			//previous and next button
			?>
		</div>
		<div id="galleryFrameGroup">
			<?php //build out to handle multiple frames ?>
			<div id="galleryframe_1" class="galleryFrame" style="display:block">
				<?php
				if($galleryThumbs){
					foreach($galleryThumbs as $n=>$v){
						?><div id="gallerythumb_1" class="galleryThumb on" idx="1" style="background-image:url('images/slides/gallery_thumbs/album_7/gallery_thumbs_67_67/palisades dusk2 [640x480].jpg');" title="Click here to move slideshow to this location" onclick="gallery_highlight(1);reindexDelay=6000; return previousNext(1,1);">&nbsp;</div><?php
					}
				}
				?>
			</div>
		</div>
	</div>

	<?php //-------------------- controls ---------------------- ?>
	<div id="ssControls">
		<div id="ctrlBg">&nbsp;</div>
		<div id="ctrlFg">
			<span><img id="bStartStop" src="/images/i/slide/button-pause-1.jpg" onclick="startStop();" title="Stop slideshow"></span>
			<span><img src="/images/i/slide/arrow-left-white-1.png" onclick="reindexDelay=6000;previousNext(-1);" title="Previous Picture"></span>
			<span><img src="/images/i/slide/arrow-right-white-1.png" onclick="previousNext(1);" title="Next Picture"></span>
		</div>
	</div>
	<?php if($prodImgClearningDiv){ ?><div class="cb">&nbsp;</div><?php } ?>
</div>
