<?php
if($useSKUAsImage){
	//step 1. get image if there based on rdp[SKU]; set as $src [string]
	ob_start();
	$alt='img';
	$dims='';
	$src=get_image($rdp[$imageRoot], $$prodImgMainArray, $prodImgMainOptions);
	if($limitImageWidth && $get_image['width']>$limitImageWidth){
		$dims='width="'.$limitImageWidth.'"';
	}else if($get_image){
		$dims='width="'.$get_image['width'].'" height="'.$get_image['height'].'"';
	}
	
	//step 2. get any images as *_[number], plus width and height of the container needed; set as $prodSlides - note it is zero-based and the main image above is added on
	if($autoDetectSlideShow==1){
		unset($prodSlides, $slideWidth, $slideHeight);
		if(count($$prodImgMainArray))
		foreach($$prodImgMainArray as $v){
			if(!preg_match('/^'.$rdp['SKU'].'_(.+)(\.(jpg|png|gif|svg))$/i', $v['name'], $a))continue;
			$v['slidename']=$a[1];
			$v['dims']='width="'.$v['width'] . '" height="'.$v['height'].'"';
			$prodSlides[]=$v;
			//get dimensions of containing box if used
			if($v['width']>$slideWidth)$slideWidth=$v['width'];
			if($v['height']>$slideHeight)$slideHeight=$v['height'];
		}	
	}

	//file attributes kernel
	unset($file);
	if(strlen($src) && @getimagesize($prodMainImgPath.$src)){
		$file['nofile']=0;
		$file['size']=round(filesize($prodMainImgPath.$src)/1024,2);
		$file['mime']=$g['mime'];
		$file['width']=$g[0];
		$file['height']=$g[1];
		$file['noimage']=0;
	}else{
		$file['nofile']=1;
		if($defaultNAImage){
			$imgPath=($defaultNAImagePath ? $defaultNAImagePath . '/':'').$defaultNAImage;
			$dims='width="'.$defaultNAImageWidth.'" height="'.$defaultNAImageHeight.'"';
		}else{
			$imgPath='/images/i/1148-pna.gif';
			$dims='width="150" height="150"';
		}
	}
	$prodSlides['mainImg']=array(
		'name' => $src,
		'folder' => '0',
		'size' => $file['size'],
		'slidename' => 'main',
		'dims' => 'width="'.$file['width'].'" height="'.$file['height'].'"',
	);
	if($prodSlidesUseExtraLarge){
		if(!$largePics) $largePics=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/'.$prodSlidesExtraLargePath);
		foreach($largePics as $o=>$w){
			if(preg_replace('/\.(jpg|png|gif|svg)$/','',strtolower($o))==preg_replace('/\.(jpg|png|gif|svg|svg)$/','',strtolower($prodSlides['mainImg']['name']))) 
			$prodSlides['mainImg']['extralarge']=$w['name'];
		}
	}
	ob_start(); //-------------- prodImgPanel -------------------
	?><div class="prodImgPanel"><?php
	ob_start(); //-------------- prodImgPanelLarge -------------------
	?><div class="prodImgPanelLarge">
	<div id="imgInset_<?php echo h($rdp['SKU'])?>_0" <?php if($adminMode){ ?>onclick="hm_cxlseq=2;showmenuie5(event,0,true);"<?php } ?> class="prodImgMain">
	<?php if(getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.$prodMainImgPath.$src)){ ?>
	<?php if($prodSlides['mainImg']['extralarge']){?><a href="<?php echo $prodSlidesExtraLargePath.'file:///C|/Users/Samuel Fullman/Desktop/Hosted Accounts/'.$prodSlides['mainImg']['extralarge']?>" onClick="return ow(this.href,'l1_picture','<?php ?>');" title="Click here to see a larger version of the main image"><?php }?>
	<img id="prodSlide_<?php echo $flexIdx;?>_0" 
	title="Main picture of this item" 
	src="<?php echo $prodMainImgPath.$src;?>" 
	alt="<?php echo 'image: '.$alt?>" 
	style="display:inline;" 
	<?php //echo $dims;
	//these are all non XHTML-compliant attributes
	?> 
	filename="<?php echo $src?>"
	filepath="<?php echo $prodMainImgPath;?>" 
	size="<?php echo $file['size']?>" 
	nofile="<?php echo $file['nofile']?>" 
	noimage="<?php echo $file['noimage']?>" 
	dims="<?php echo $file['width'].','.$file['height']?>" 
	mime="<?php echo $file['mime'];?>"
	type="<?php echo $file['mime'];?>"
	/>
	<?php if($prodSlides['mainImg']['extralarge']){?></a><?php }?>
	<?php } ?>
	</div><!-- prodImgMain -->
	<?php
	if(count($prodSlides)>1){
		foreach($prodSlides as $n=>$v){
			//file attributes kernel
			unset($file);
			if(file_exists($prodMainImgPath.$v['name'])){
				$file['nofile']=0;
				$file['size']=round(filesize($prodMainImgPath.$v['name'])/1024,2);
				if($g=@getimagesize($prodMainImgPath.$v['name'])){
					$file['mime']=$g['mime'];
					$file['width']=$g[0];
					$file['height']=$g[1];
					$file['noimage']=0;
				}else{
					//this is a document, we need a representation
					$file['noimage']=1;
					$imgPath='/images/i/1148-pna.gif';
					$dims='width="150" height="150"';
				}
				if($prodSlidesUseExtraLarge){
					foreach($largePics as $o=>$w){
						if(preg_replace('/\.(jpg|png|gif|svg)$/','',strtolower($o))==preg_replace('/\.(jpg|png|gif|svg)$/','',strtolower($v['name'])))
						$v['extralarge']=$prodSlides[$n]['extralarge']=$largePics[strtolower($v['name'])]['name'];
					}
				}
			}else{
				$file['nofile']=1;
				$imgPath='/images/i/1148-pna.gif';
				$dims='width="150" height="150"';
			}
			?><div id="imgInset_<?php echo h($rdp['SKU'])?>_<?php echo $n+1;?>" <?php if($adminMode){ ?>onclick="hm_cxlseq=2;showmenuie5(event,0,true);"<?php } ?> class="prodImgMain"><?php if($v['extralarge']){ ?><a href="<?php echo $prodSlidesExtraLargePath.'file:///C|/Users/Samuel Fullman/Desktop/Hosted Accounts/'.$v['extralarge']?>" target="_blank" title="Click here to see a larger version of this image"><?php }?><img 
			id="prodSlide_<?php echo $flexIdx?>_<?php echo $n+1;?>" 
			title="<?php echo $v['extralarge']?'':(is_numeric($v['slidename'])?'Picture '.$v['slidename']:h($v['slidename']))?>" 
			src="<?php  echo $prodMainImgPath . $v['name'];?>" 
			alt="<?php echo $alt?>" 
			style="display:none;"
			<?php //echo $dims;
			//these are all non XHTML-compliant attributes
			?> 
			filename="<?php echo $src?>"
			filepath="<?php echo $prodMainImgPath;?>" 
			size="<?php echo $file['size']?>" 
			nofile="<?php echo $file['nofile']?>" 
			noimage="<?php echo $file['noimage']?>" 
			dims="<?php echo $file['width'].','.$file['height']?>" 
			mime="<?php echo $file['mime'];?>"
			type="<?php echo $file['mime'];?>"
			/><?php if($v['extralarge']){ ?></a><?php }?></div><?php
		}
	}
	?></div><!-- prodImgPanelLarge -->
	<?php
	echo $prodImgPanelLarge=get_contents();
	//product slide controls - initial generic buttons as of 2008-08-27 - see notes and todo above
	if(count($prodSlides)>1){
		//controls
		/*begin new slideshow*/
		if($prodSlidesUseThumbs){
			if(!function_exists('create_thumbnail'))require($FUNCTION_ROOT.'/function_create_thumbnail_v200.php');
			foreach($prodSlides as $n=>$v){
				if(is_file($prodMainThumbPath.$v['name'])){
					$prodSlides[$n]['newDims']='true';
				}else if (is_file($prodMainImgPath.$v['name'])){
					if(is_dir($prodMainThumbPath)){
						$prodSlides[$n]['newDims']=create_thumbnail($_SERVER['DOCUMENT_ROOT'].'/'.$prodMainImgPath.$v['name'],'0.3','',$prodMainThumbPath.$v['name'],'');
					}else{
						mkdir($prodMainThumbPath);
					}
				}
			}
		}
		
		ob_start(); //-------------- prodImgPanelGallery -------------------	
		?>
		<span class="prodSlideClickMessage"><?php echo $prodSlideClickMessage ? $prodSlideClickMessage : 'Click a square below for more views of the item'?></span>
		<div class="prodSlideControl">
		<div id="prodSlideButton_<?php echo $flexIdx?>_0" title="Main picture" class="on" onClick="prodSlideToggle(<?php echo $flexIdx?>,<?php echo count($prodSlides);?>,0);"><?php if($prodSlides['mainImg']['newDims'] && $prodSlides['mainImg']){?><img src="<?php echo $prodMainThumbPath.$v['name'];?>" /><?php }?></div>
		<?php
		foreach($prodSlides as $n=>$v){
			if($v['slidename']=='main') continue;
			?><div id="prodSlideButton_<?php echo $flexIdx?>_<?php echo $n+1?>" title="Click this to see <?php echo $n==0?'the first picture':'picture '.($n+1)?>" class="off" onClick="prodSlideToggle(<?php echo $flexIdx?>,<?php echo count($prodSlides);?>,<?php echo $n+1;?>);"><?php if($v['newDims']){?><img src="<?php echo $prodMainThumbPath.$v['name'];?>" /><?php }?></div><?php
		}
		?></div><?php
		echo $prodImgPanelGallery=get_contents();
	}
	?></div><!-- prodImgPanel --><?php
	echo $prodImgPanel=get_contents();
}
?>