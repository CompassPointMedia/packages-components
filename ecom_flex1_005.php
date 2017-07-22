<?php
/*
2010-10-05: docs are now in ecom_flex1_documentation.php
*/
if(!$ecom_flex1_settings){
	//get these settings only once	
	$ecom_flex1_settings=true;

	//initial javascript, hidden fields
	?><script language="javascript" type="text/javascript">
	function getPageOffset(point){
		//http://dev.communityserver.com/forums/t/477071.aspx
		if(typeof window.pageXOffset == 'number'){
			//Netscape compliant
			return (point=='left'?window.pageXOffset:window.pageYOffset);
		}else if (document.body && (document.body.scrollLeft || document.body.scrollTop)){
			//DOM compliant
			return (point=='left'?document.body.scrollLeft:document.body.scrollTop);
		}else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)){
			//IE6 standards compliant mode
			return (point=='left'?document.documentElement.scrollLeft:document.documentElement.scrollTop);
		}
	}
	function setID(id, evt){
		var loc=g('priority'+id);
		var clickDown=evt.clientY - loc.offsetTop + getPageOffset('top');
		var height=loc.height;
		var dir=(clickDown <= height/2 ? 1 : -1);
		var url='<?php echo $thispage?>?ID='+id+'&dir='+dir+'&absolute='+(evt.shiftKey ? 1 : 0)+'&src='+escape(window.location);
		window.location=url;
	}
	function setID2(id, evt, dir){
		var url='<?php echo $thispage?>?ID='+id+'&dir='+dir+'&absolute='+(evt.ctrlKey ? 1 : 0)+'&src='+escape(window.location);
		window.location=url;
	}
	</script>
	<?php
	if(!isset($prodImgAlt))$prodImgAlt='product picture'; // Added By Parker 10/04/10
	if(!isset($nameCaptionDelim))$nameCaptionDelim=' - ';
	if(!isset($useSKUAsImage))$useSKUAsImage=true;
	if(!isset($suggestedPriceTerm))$suggestedPriceTerm='Suggested Price';
	if(!isset($actualPriceTerm))$actualPriceTerm='Our Price';
	if(!isset($singleSalePriceTerm))$singleSalePriceTerm='Sale Price';
	if(!isset($singleRegularPriceTerm))$singleRegularPriceTerm='Our Price';

	if(!isset($wholesalePriceTerm))$wholesalePriceTerm='Wholesale Price';
	if(!isset($showRetailPriceWhenWholesale))$showRetailPriceWhenWholesale=true;
	if(!isset($retailPriceTermWhenWholesale))$retailPriceTermWhenWholesale='Retail Price';

	if(!isset($yourSavingsTerm))$yourSavingsTerm='Your savings';
	if(!isset($showSavingsAs))$showSavingsAs='percent';
	if(!isset($thisItemAddedText))$thisItemAddedText='This item has been added to your order';
	if(!isset($thisItemAddedTitle))$thisItemAddedTitle='View your order and check out';
	if(!isset($addButtonTitle))$addButtonTitle='Add this product to your order';
	if(!isset($addButtonURL))$addButtonURL='index_01_exe.php?mode=addcart&Category='.urlencode(stripslashes($_GET['Category'])).'&SubCategory='.urlencode(stripslashes($_GET['SubCategory']));
	if(!isset($defaultSKULabel))$defaultSKULabel='Item number';
	if(!isset($defaultModelLabel))$defaultModelLabel='Model';
	if(!isset($showSKU))$showSKU=true;
	if(!isset($showModel))$showModel=false;
	
	//default for multiple products listing
	if(!isset($useProductDescriptions))$useProductDescriptions=1;
	if(!isset($showMoreInfoButton))$showMoreInfoButton=true;
	if(!isset($showAddButton))$showAddButton=true;
	if(!isset($prodMainImgPath))$prodMainImgPath='images/products/thumb/'; #note trailing slash
	if(!isset($prodMainThumbPath))$prodMainThumbPath='images/products/thumbnails/';
	if(!isset($prodSlidesUseThumbs))$prodSlidesUseThumbs=false;
	if(!isset($prodSlidesUseExtraLarge))$prodSlidesUseExtraLarge=false;
	if(!isset($prodSlidesExtraLargePath))$prodSlidesExtraLargePath='images/products/extralarge';
	if(!isset($prodImgMainArray))$prodImgMainArray='images';
	if(!isset($prodImgMainOptions))$prodImgMainOptions=array(); //for function get_image
	if(!isset($showRelatedItems))$showRelatedItems=true;
	if(!isset($relatedItemsHeading))$relatedItemsHeading='Popular Related Items'; // or e.g. Popular Related Items
	if(!isset($autoDetectSlideShow))$autoDetectSlideShow=1; // 1 means look for _1, _2 for addt' product images
	if(!isset($pageHandles['categoryPage']))$pageHandles['categoryPage']='category.php';
	if(!isset($pageHandles['subCategoryPage']))$pageHandles['subCategoryPage']='subcategory.php';
	if(!isset($pageHandles['productsPage']))$pageHandles['productsPage']='products.php';
	if(!isset($pageHandles['singlePage']))$pageHandles['singlePage']='single.php';
	if(!isset($allowProductRanking))$allowProductRanking=true;
	if(!$imageRoot)$imageRoot='SKU';
}
/*
prodOutputOrder options
prodPriceData
prodMoreInfo
prodQty
prodAdd
prodAdded
prodAdminMode
prodWrap
prodPackageWording
prodImgPanel
prodCaption
prodName
prodModel
prodSKU
prodRelatedItems
prodDescription
prodPackageData
*/
//notice this is declared outside the above logic as p3 may not be present as the first product shown
//p3 settings - see http://dev.compasspoint-sw.com/mediawiki/index.php/Protocol:Packaged_Product_Protocol
if(!isset($hidePackageWording))$hidePackageWording=false; //allow for "Special Package" wording at top
if(!isset($packageHeading))$packageHeading='Featured in this package';

if(!function_exists('get_image')){
	function get_image($name, $images='', $options=array()){
		/*
		2008-06-30: gets image from get_file_assets() array - by precedence
		options
		imagePrefix
		imageSuffix
		externalImageFunction
		*/
		global $get_image;
		unset($get_image);
		extract($options);
		if($externalImageFunction){
			//Added 2008-10-31 - this allows an external function to process, it must globalize $get_image - with nodes of name (case-sensitive), width, and height.  The source returned must be the actual path to the image plus name
			return $externalImageFunction($name, $images='', $options);
		}
		//assume image array = $images if not explicitly passed
		if(!$images)global $images;
		switch(true){
			case $a=$images[strtolower($imagePrefix).strtolower($name).strtolower($imageSuffix).'.jpg']:
				$get_image=$a;
			break;
			case $a=$images[strtolower($imagePrefix).strtolower($name).strtolower($imageSuffix).'.gif']:
				$get_image=$a;
			break;
			case $a=$images[strtolower($imagePrefix).strtolower($name).strtolower($imageSuffix).'.png']:
				$get_image=$a;
			break;
			case $a=$images[strtolower($imagePrefix).strtolower($name).strtolower($imageSuffix).'.svg']:
				$get_image=$a;
			break;
		}
		if($get_image){
			preg_match('/\.[a-z]+$/i',$get_image['name'],$b);
			$get_image['extension']=str_replace('.','',strtolower($b[0]));
			return $get_image['name'];
		}
	}
}
if(!function_exists('get_contents')){
	$functionVersions['get_contents']=.01;
	function get_contents(){
		/* 2008-06-30 - for handling output buffering 
		2009-11-29 - made an "official" function in a_f; it was in 5 files.  Only in comp_tabs v2.00 (+?) the end logic is NOT if(beginnextbuffer) then ob_start() ELSE return gcontents.out - instead the logic is if(beginnextbuffer) then ob_start(); return gcontents.out PERIOD
		HOWEVER, beginnextbuffer is never flagged in comp_tabs so I have no fear of back-compat problems
		this function will return output and can optionally start the next buffer.
		GOTCHA! since this is a function, we must ob_start() before we return the contents.  Therefore, if you store the value returned as a variable, thats great, but if you wish to echo it, you are already in the next buffer.  So you cannot do a rewrite as done in cal widget and etc.
		*/
		$cmds=array('striptabs','beginnextbuffer','trim');
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
		if($trim)$gcontents['out']=trim($gcontents['out'])."\n";
		ob_end_clean();
		if($striptabs)$gcontents['out']=str_replace("\t",'',$gcontents['out']);
		if($beginnextbuffer){
			ob_start();
		}else{
			return $gcontents['out'];
		}
	}
}
if(!$prodSlidesJS){
	$prodSlidesJS=true;
	?><script id="prodSlidesJS" language="javascript" type="text/javascript">
	var prodSlides=new Array();
	function prodSlideToggle(node, range, idx){
		//show or hide images as appropriate
		for(i=0;i<=range;i++){
			g('prodSlideButton_'+node+'_'+i).className=(i==idx?'on':'off');		
			g('prodSlide_'+node+'_'+i).style.display=(i==idx?'inline':'none');
		}
	}
	</script><?php
}


//------------------------------- controls and variables needed later ----------------------------------
$currentProducts[]=$rdp['ID'];
$key=pk_encode(array('ID'=>$rdp['ID']));

//do we buffer the entire object?
if($flex1Rewrite || $prodOutputOrder){
	ob_start();
}

//-------------------------------- begin retrieving product presentation ------------------------------
//global index must not be interfered with
$flexIdx++;

//main wrapper
ob_start();
?><a name="<?php echo strtoupper($rdp['SKU'])?>"></a><div id="prod<?php echo $rdp['ID'];?>" class="<?php echo $prodWrapClass ? $prodWrapClass : 'prodPresentation'?>"><?php
echo $prodWrap=get_contents();

//special package wording at top (P3)
unset($prodPackageWording);
if($rdp['IsPackage'] && !$hidePackageWording){
	ob_start();
	?>
	<div class="prodPackageWording"><?php echo $prodPackageWordingText ? $prodPackageWordingText : '&lt;special package!&gt;'?></div><?php
	echo $prodPackageWording=get_contents();
}

//image and image controls
/*
2011-02-21: we are working on revamping the image controls so that they merge with the slideshow, more semantic, and more transparent to the user in terms of auto-creating various sizes
if(any valid images present){
	output primary image
	
	output secondary images
	create thumbs if dimensions declared
	?><div id="filmstrip">
	<div id="gallery_sku_1"><a><img src="that source 1" /></a></div>
	<div id="gallery_sku_2"><a><img src="that source 2" /></a></div>
	</div><?php
}
*/


unset($prodImgPanel);
if($useSKUAsImage){
	$alt='img';
	ob_start();
	$dims='';
	$src=get_image($rdp[$imageRoot], $$prodImgMainArray, $prodImgMainOptions);
	if($limitImageWidth && $get_image['width']>$limitImageWidth){
		$dims='width="'.$limitImageWidth.'"';
	}else if($get_image){
		$dims='width="'.$get_image['width'].'" height="'.$get_image['height'].'"';
	}
	
	//get any images as *_[number]
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
	?>
	<div class="prodImgPanel">
	<?php
	ob_start(); //-------------- prodImgPanelLarge -------------------
	?>
	<div class="prodImgPanelLarge">
	<div id="imgInset_<?php echo h($rdp['SKU'])?>_0" <?php if($adminMode){ ?>onclick="hm_cxlseq=2;showmenuie5(event,0,true);"<?php } ?> class="prodImgMain">
	<?php if($prodSlides['mainImg']['extralarge']){?><a href="<?php echo $prodSlidesExtraLargePath.'/'.$prodSlides['mainImg']['extralarge']?>" onclick="return ow(this.href,'l1_picture','<?php ?>');" title="Click here to see a larger version of the main image"><?php }?>
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
	/><?php if($prodSlides['mainImg']['extralarge']){?></a><?php }?></div>
	
	
	
	<?php
	if(count($prodSlides)){
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
			?><div id="imgInset_<?php echo h($rdp['SKU'])?>_<?php echo $n+1;?>" <?php if($adminMode){ ?>onclick="hm_cxlseq=2;showmenuie5(event,0,true);"<?php } ?> class="prodImgMain"><?php if($v['extralarge']){ ?><a href="<?php echo $prodSlidesExtraLargePath.'/'.$v['extralarge']?>" target="_blank" title="Click here to see a larger version of this image"><?php }?><img 
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
	?></div>	
	<?php
	echo $prodImgPanelLarge=get_contents();
	
	ob_start(); //-------------- prodImgPanelGallery -------------------
	//product slide controls - initial generic buttons as of 2008-08-27 - see notes and todo above
	if(count($prodSlides)){
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
		<div id="prodSlideButton_<?php echo $flexIdx?>_0" title="Main picture" class="on" onclick="prodSlideToggle(<?php echo $flexIdx?>,<?php echo count($prodSlides);?>,0);"><?php if($prodSlides['mainImg']['newDims'] && $prodSlides['mainImg']){?><img src="<?php echo $prodMainThumbPath.$v['name'];?>" /><?php }?></div>
		<?php
		foreach($prodSlides as $n=>$v){
			if($v['slidename']=='main') continue;
			?><div id="prodSlideButton_<?php echo $flexIdx?>_<?php echo $n+1?>" title="Click this to see <?php echo $n==0?'the first picture':'picture '.($n+1)?>" class="off" onclick="prodSlideToggle(<?php echo $flexIdx?>,<?php echo count($prodSlides);?>,<?php echo $n+1;?>);"><?php if($v['newDims']){?><img src="<?php echo $prodMainThumbPath.$v['name'];?>" /><?php }?></div><?php
		}
		?></div><?php
		echo $prodImgPanelGallery=get_contents();
	}
	
	?>
	
	</div><?php
	echo $prodImgPanel=get_contents();
}


//product name and product caption
ob_start();
?><h2 id="" class="prodName">
<?php echo $rdp[$prodAlternateNameField ? $prodAlternateNameField : 'Name'];?><?php
if($appendCaption && strlen(trim($rdp['Caption']))){
	echo $nameCaptionDelim;
	?><span class="prodCaption"><?php echo $rdp['Caption'];?></span><?php
}else if(strlen($rdp['Caption'])){
	ob_start();
	?><div class="prodCaption"><?php echo $rdp['Caption'];?></div><?php
	echo $prodCaption=get_contents();
}
?>
</h2><?php
echo $prodName=get_contents();

//product Model
unset($prodModel);
if($showModel){
	ob_start();
	?><div class="prodModel">
		<span class="key"><?php echo $defaultModelLabel?>: </span>
		<span class="value"><?php echo $rdp['Model'];?></span>
	<?php
	?></div><?php
	echo $prodModel=get_contents();
}
//product SKU
unset($prodSKU);
if($showSKU){
	ob_start();
	?><h3 class="prodSKU">
		<span class="key"><?php echo $defaultSKULabel?>: </span>
		<span class="value"><?php echo $rdp['SKU'];?></span>
	<?php
	?></h3><?php
	echo $prodSKU=get_contents();
}

//related items
unset($prodRelatedItems);
if((count($b=q("SELECT *
	FROM finan_items_related a LEFT JOIN finan_items b ON
	(Parent_ID='".$rdp['ID']."' AND Child_ID=b.ID) OR
	(Child_ID='".$rdp['ID']."' AND Parent_ID=b.ID AND a.Reflexive=1)
	WHERE Parent_ID='".$rdp['ID']."' OR Child_ID='".$rdp['ID']."'
	ORDER BY IF(UnitPrice!=0,1,2), UnitPrice", O_ARRAY)) && $showRelatedItems) || ($adminMode && !$hideRelatedItemsInAdminMode)){
	ob_start();
	?><div class="prodRelatedItems" style="<?php if($adminMode){ ?>background-color:aliceblue;<?php }?>"><?php
	?><h3 class="heading"><?php echo $relatedItemsHeading?></h3><?php
	if(count($b))foreach($b as $v){
		?><a title="View related product" href="<?php echo $pageHandles['singlePage']?>?ID=<?php echo $v['ID']?>&from=<?php echo $rdp['ID']?>&Category=<?php echo urlencode($v['Category'])?>&SubCategory=<?php echo urlencode($v['SubCategory']);?>"><strong><?php echo $v['Name']?></strong></a>
		- <?php echo $v['ForwardComments']?>
		<br />
		<?php
	}
	if($adminMode){
		?><a onClick="return ow(this.href,'l1_addrelated','700,700');" href="resources/manage_related_items.php?ID=<?php echo $rdp['ID']?>">Add related item</a><?php
	}
	?></div><?php
	echo $prodRelatedItems=get_contents();
}

//handle product description
ob_start();
?><div class="prodDescription"><?php
if($useProductDescriptions==1){
	if($useCMSBForDescriptionEdit==1 || $useCMSBForDescriptionEdit==3){
		$options=array(
			'method'=>'dynamic:simple',
			'CMSTable'=>'finan_items',
			'CMSContentField'=>'Description',
			'primaryKeyField'=>array('ID'),
			'primaryKeyLabel'=>array('ID'),
			'primaryKeyValue'=>array($rdp['ID']),
			'setTagAs'=>'span',
			'setClass'=>'short'
		);
		CMSB('','',$options);
	}else{
		?><span class="short"><?php echo $rdp['Description']?></span><?php
	}
}else if($useProductDescriptions==2){
	if($useCMSBForDescriptionEdit>1){
		$options=array(
			'method'=>'dynamic:simple',
			'CMSTable'=>'finan_items',
			'CMSContentField'=>'LongDescription',
			'primaryKeyField'=>array('ID'),
			'primaryKeyLabel'=>array('ID'),
			'primaryKeyValue'=>array($rdp['ID']),
			'setTagAs'=>'span',
			'setClass'=>'short'
		);
		CMSB('','',$options);
	}else{
		?><span class="long"><?php echo $rdp['LongDescription']?></span><?php
	}
}else if($useProductDescriptions==3){
	//compare and analyze short description vs. long = 5 or more same words means drop the short description
	$short=explode(' ',trim(strtolower(strip_tags($rdp['Description']))));
	$long=explode(' ',trim(strtolower(strip_tags($rdp['LongDescription']))));
	$i=0;
	$descequal=true;
	foreach($short as $n=>$v){
		$i++;
		if($short[$n]!==$long[$n])$descequal=false;
		if($i>$wordCompareCount)break;
	}
	if(!$descequal){
		if($useCMSBForDescriptionEdit==1 || $useCMSBForDescriptionEdit==3){
			$options=array(
				'method'=>'dynamic:simple',
				'CMSTable'=>'finan_items',
				'CMSContentField'=>'Description',
				'primaryKeyField'=>array('ID'),
				'primaryKeyLabel'=>array('ID'),
				'primaryKeyValue'=>array($rdp['ID']),
				'setTagAs'=>'span',
				'setClass'=>'short'
			);
			CMSB('','',$options);
		}else{
			?><span class="short"><?php echo $rdp['Description']?></span><?php
		}
		?><span class="divider">&nbsp;</span><?php
	}
	if($useCMSBForDescriptionEdit>1){
		$options=array(
			'method'=>'dynamic:simple',
			'CMSTable'=>'finan_items',
			'CMSContentField'=>'LongDescription',
			'primaryKeyField'=>array('ID'),
			'primaryKeyLabel'=>array('ID'),
			'primaryKeyValue'=>array($rdp['ID']),
			'setTagAs'=>'span',
			'setClass'=>'short'
		);
		CMSB('','',$options);
	}else{
		?><span class="long"><?php echo $rdp['LongDescription']?></span><?php
	}
}
?></div><?php
echo $prodDescription=get_contents();


//handle package items table, summary, and images - either package info or single product price info shows, but not both
unset($prodPackageData, $prodPriceData);
if($rdp['IsPackage']){
	ob_start();
	?>
	<div class="prodPackageData">
		<div class="prodPackageHeading"><?php echo $packageHeading;?></div>
		<?php
		if($a=q("SELECT WholesalePrice, UnitPrice AS RetailPrice, UnitPrice2 AS SalePrice, Name, SKU, Description, LongDescription, a.PricingType, a.PriceValue, a.Quantity, a.BonusItem, a.OverrideName, a.OverrideDescription, a.OverrideLongDescription FROM finan_ItemsItems a, finan_items b WHERE a.ParentItems_ID='".$rdp['ID']."' AND a.ChildItems_ID=b.ID ORDER BY a.Idx, IF(a.BonusItem,2,1), IF(a.PricingType='Free',2,1), b.Category, b.SubCategory", O_ARRAY)){
	
			//precalc prices and costs
			$integrateWithCartSession=false;

			//------------------------- codeblock "Tasmanian Devil" ----------------------------------
			$nonBonusItemsTotal=$listTotal=$packageTotal=0;
			$defer=false;
			foreach($a as $n=>$v){
				if($integrateWithCartSession){
					//this was developed for function shopping_cart_calculate() for best fit
					$a[$n]=$v=&$_SESSION['shopCart'][$cart][$v]; //change #1: merge shopcart data in
					if(!$rdp)$rdp=&$_SESSION['shopCart'][$cart][$n]; //change #2: get root item params
				}
				//this is the "price" of the item just as with any other sale item
				$comparison=$a[$n]['comparison']=($v['SalePrice']>0 ? $v['SalePrice'] : $v['RetailPrice']);
				if($v['BonusItem']){
					$pricingType=$a[$n]['PricingType']=strtolower($v['PricingType']);
					if($pricingType=='free'){
						$a[$n]['ListPriceColumn']=round($v['Quantity'] * $comparison,2);
						$a[$n]['YourPriceColumn']=round(0,2);
					}else if($pricingType=='price'){
						$a[$n]['ListPriceColumn']=round($v['Quantity'] * $comparison,2);
						$a[$n]['YourPriceColumn']=round($v['Quantity'] * $v['PriceValue'],2);
					}else if($pricingType=='percent'){
						$a[$n]['ListPriceColumn']=round($v['Quantity'] * $comparison,2);
						$a[$n]['YourPriceColumn']=round($v['Quantity'] * $comparison * (1 - $v['PriceValue']/100),2);
					}
				}else{
					$pricingType=strtolower($rdp['PricingType']);
					$nonBonusItemsTotal+=round($v['Quantity'] * $comparison,2);
					if($pricingType=='no price change'){
						$a[$n]['YourPriceColumn']=
						$a[$n]['ListPriceColumn']=round($v['Quantity'] * $comparison,2);
					}else if($pricingType=='specific package price'){
						//sum for later use - in this case we don't have the actual price column yet
						$defer=true;
					}else if($pricingType=='auto discount'){
						$a[$n]['ListPriceColumn']=round($v['Quantity'] * $comparison,2);
						$a[$n]['YourPriceColumn']=round($v['Quantity'] * $comparison * (1 - $rdp['PriceValue']/100),2);
					}
				}
			}
			//prn($nonBonusItemsTotal);
			//prn($a);
			if($defer)@$discountRatio=$rdp['PriceValue']/$nonBonusItemsTotal;
			foreach($a as $n=>$v){
				if(!$v['BonusItem'] && $defer){
					$a[$n]['ListPriceColumn']=round($v['Quantity'] * $v['comparison'],2);
					$a[$n]['YourPriceColumn']=round($v['Quantity'] * $v['comparison'] * $discountRatio,2);
				}
				$listTotal +=$a[$n]['ListPriceColumn'];
				$packageTotal +=$a[$n]['YourPriceColumn'];
			}
			if($integrateWithCartSession){
				//change #3: place overall calcs in root item
				$_SESSION['shopCart'][$cart][$idx]['Calculated']=$shopping_cart_calculate_version;
				$_SESSION['shopCart'][$cart][$idx]['listTotal']=number_format($packageTotal,2);
				$_SESSION['shopCart'][$cart][$idx]['packageTotal']=number_format($packageTotal,2);
				$_SESSION['shopCart'][$cart][$idx]['nonBonusItemsTotal']=number_format($nonBonusItemsTotal,2);
				
			}
			//-------------------------------------------------------------------------------------------
			?><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php			
			foreach($a as $n=>$v){
				?><tr>
					<?php if($adminMode){ ?>
					<td><?php
					?><img src="images/i/edit2.gif" alt="edit" width="15" height="18" /></td>
					<?php } ?>
					<td><?php echo $v['SKU']?></td>
					<td><?php
					//indidate the quantity
					$qty=preg_replace('/\.$/','',preg_replace('/0+$/','',$v['Quantity']));
					if($qty>1)echo '('.$qty. ' ea.) ';
					?><?php echo $v['Name'] ?> [<a title="more information about this item" href="#">more</a>] </td>
					<td style="text-align:right;"><?php
					if($v['ListPriceColumn'] > $v['YourPriceColumn']){
						?><s><?php echo number_format($v['ListPriceColumn'],2);?></s><?php
					}
					?></td>
					<td style="text-align:right;"><?php
					echo ($v['YourPriceColumn']==0 ? 'FREE' : number_format($v['YourPriceColumn'],2));
					?></td>
				</tr><?php
			}
			?></table><?php
		}else{
			//mail
			mail($developerEmail,'no package items!',get_globals(),$fromHdrBugs);
			?><script language="javascript" type="text/javascript">
			setTimeout('window.location="/";',4000);
			alert('No items in this package! Staff have been alerted.  Redirecting to home page');
			</script><?php
		}
		?>
		<div class="prodPackageExtraImages"> </div>
		<div class="prodPackageCostSummary">
			<div class="actual">
				 <span class="key">Total: </span>
				 <span class="value">$<?php echo number_format($packageTotal,2)?></span>
			</div>
			<div class="savings">
				<span class="key"><?php echo $yourSavingsTerm?>: </span>
				<span class="value"><span class="percent"><?php
				if(@$packageTotal/$listTotal < .98){
					echo round((1 - $packageTotal/$listTotal)*100,2);
					echo '%';
				}
				?></span></span>
			</div>
		</div>
	</div><?php
	echo $prodPackageData=get_contents();
}else{
	//product price
	ob_start();
	?><div class="prodPriceData">
		<?php
		if($wholesale){ /* this var, wholesale, must be positively set true or false */
			?><div class="wholesale">
				<span class="key"><?php echo $wholesalePriceTerm;?></span>
				<span class="value">$<?php echo number_format($rdp['WholesalePrice'],2);?></span>
			</div><?php
			//now get the actual price, if UnitPrice2 present use it
			if($showRetailPriceWhenWholesale){
				$retailPrice=($rdp['UnitPrice2']>0 && $rdp['UnitPrice2']<$rdp['UnitPrice'] ? $rdp['UnitPrice2'] : $rdp['UnitPrice']);
				?><div class="retail">
					<span class="key"><?php echo $retailPriceTermWhenWholesale;?></span>
					<span class="value">$<?php echo number_format($retailPrice,2)?></span>
				</div><?php
			}
		}else{
			$haveDiscount=false;
			if($rdp['UnitPrice'] > 0 && $rdp['UnitPrice2'] > 0 && !$squelchDiscountInfo){
				$haveDiscount=true;
				if($rdp['UnitPrice']>$rdp['UnitPrice2']){
					?><div class="suggested">
						<span class="key"><?php echo $suggestedPriceTerm?>:</span>
						<span class="value">$<?php echo number_format($rdp['UnitPrice'],2)?></span>
					</div><?php
				}
				?>
				<div class="actual">
					<span class="key"><?php echo $actualPriceTerm?>:</span>
					<span class="value">$<?php echo number_format($rdp['UnitPrice2'],2)?></span>
				</div><?php
			}else if($rdp['UnitPrice2']>0){
				//interpret UnitPrice2 as a sales price
				?><div class="actual">
				<span class="key"><?php echo $singleSalePriceTerm?>:</span>
				<span class="value">$<?php echo number_format($rdp['UnitPrice2'],2)?></span>
				</div><?php
			}else if($rdp['UnitPrice']>0){
				//interpret UnitPrice as a regular price
				?><div class="actual">
				<span class="key"><?php echo $singleRegularPriceTerm?>:</span>
				<span class="value">$<?php echo number_format($rdp['UnitPrice'],2)?></span>
				</div><?php
			}
			if(@$haveDiscount && $rdp['UnitPrice2']/$rdp['UnitPrice']<.98){
				?><div class="savings">
				<span class="key"><?php echo $yourSavingsTerm?>:</span>
				<span class="value"><?php
				if($showSavingsAs=='percent' || !$showSavingsAs){
					?><span class="percent"><?php echo round((1 - ($rdp['UnitPrice2']/$rdp['UnitPrice']))*100) . '%';?></span><?php
				}else if($showSavingsAs=='dollar'){
					?><span class="dollar"><?php echo '$'.number_format($rdp['UnitPrice'] - $rdp['UnitPrice2'],2);?></span><?php
				}else if($showSavingsAs=='both'){
					?><span class="dollar"><?php echo '$'.number_format($rdp['UnitPrice'] - $rdp['UnitPrice2'],2);?></span>&nbsp;<?php
					?><span class="percent"><?php 
					echo '('.round((1 - ($rdp['UnitPrice2']/$rdp['UnitPrice']))*100) . '%)';?></span><?php
				}
				?></span>
				</div><?php
			}
		}
	?></div>
	<?php
	echo $prodPriceData=get_contents('striptabs');
}

#      --- controls - I'm needing to go more fine-grained on this one ------

//---------------------------- more info --------------------------------------------
unset($prodMoreInfo);
if($showMoreInfoButton){
	ob_start();
	?><span class="prodMoreInfo"><?php
	if($moreInfoButtonImg){
		?><a title="More information on this item" href="<?php echo $moreInfoButtonURL?>&ID=<?php echo $rdp['ID']?>"><img src="<?php echo $moreInfoButtonImg?>" <?php $a=getimagesize($moreInfoButtonImg); echo $a[2]?> alt="more info" /></a><?php
	}else if(($x=file_exists($_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultMoreInfoButton.jpg')) || ($y=file_exists($_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultMoreInfoButton.gif'))){
		$btn=($x ? $_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultMoreInfoButton.jpg' : $_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultMoreInfoButton.gif');
		?><a title="More information on this item" href="<?php echo $moreInfoButtonURL?>&ID=<?php echo $rdp['ID']?>"><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$btn);?>" <?php $a=getimagesize($btn); echo $a[2]?> alt="more information on this item" /></a>&nbsp;&nbsp;<?php
	}else{
		?>
		<input type="button" name="Submit" value="More Info.." onclick="window.location='<?php echo $moreInfoButtonURL;?>&ID=<?php echo $rdp['ID']?>';" />
		<?php
	}
	?></span><?php
	echo $prodMoreInfo=get_contents();
}


//------------------------------- quantity control ---------------------------------
unset($prodQty);
ob_start();
?><span class="prodQty"><?php
if($quantityFieldType=='select'){
	?><select name="qty<?php echo $rdp['ID']?>" id="qty<?php echo $rdp['ID']?>">
	<option value="">-qty.-</option>
	<?php
	for($i=1;$i<=20;$i++){
		?><option value="<?php echo $i?>"><?php echo $i?></option><?php
	}
	?>
	</select><?php
}else if($quantityFieldType=='input'){
	?><input name="qty" type="text" class="qty" id="qty<?php echo $rdp['ID']?>" value="" size="3" maxlength="4" /><?php
}else{
	?><input name="qty" type="hidden" class="qty" id="qty<?php echo $rdp['ID']?>" value="1" /><?php
}
?></span><?php
echo $prodQty=get_contents();

//---------------------------- add to order -------------------------------
unset($prodAdd);
if($showAddButton){
	ob_start();
	?><span class="prodAdd"><?php
	if($addButtonImg){
		?><a title="<?php echo $addButtonTitle?>" href="<?php echo $addButtonURL?>&ID=<?php echo $rdp['ID']?>" onclick="window.open(this.href+'&qty='+g('qty<?php echo $rdp['ID']?>').value, 'w1'); return false;"><img src="<?php echo $addButtonImg?>" <?php $a=getimagesize($addButtonImg); echo $a[2]?> alt="add to order" /></a><?php
	}else if(($x=file_exists($_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultAddButton.jpg')) || ($y=file_exists($_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultAddButton.gif'))){
		$btn=($x ? $_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultAddButton.jpg' : $_SERVER['DOCUMENT_ROOT'].'/images/assets/defaultAddButton.gif');
		?><a title="<?php echo $addButtonTitle?>" href="<?php echo $addButtonURL?>&ID=<?php echo $rdp['ID']?>" onclick="window.open(this.href+'&qty='+g('qty<?php echo $rdp['ID']?>').value, 'w1'); return false;"><img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$btn);?>" <?php $a=getimagesize($btn); echo $a[2]?> alt="add to order" /></a><?php
	}else{
		?>
		<input type="button" name="Submit" value="Add to Order" onclick="window.open('<?php echo $addButtonURL?>&ID=<?php echo $rdp['ID']?>&qty='+g('qty<?php echo $rdp['ID']?>').value, 'w1');" title="<?php echo $addButtonTitle?>" />
		<?php
	}
	?></span><?php
	echo $prodAdd=get_contents();
}


//---------------------------- added OK visual aid -------------------------
unset($prodAdded);
ob_start();
?><div id="added<?php echo $rdp['ID']?>" class="prodAdded" style="visibility:hidden;"><a title="<?php echo $thisItemAddedTitle?>" href="<?php echo $shoppingCartURL;?>"><?php echo $thisItemAddedText?></a></div><?php
echo $prodAdded=get_contents();



//---------------------------- admin mode features --------------------------
unset($prodAdminMode);
if($adminMode){
	ob_start();
	$key=pk_encode($rdp['ID']);
	?>
	<div class="prodAdminMode">
	<?php
	if($allowProductRanking){
		/*
		?><span title="Hold down the shift key to move product to the absolute top or bottom of this category"><img  style="cursor:pointer" id="priority<?php echo $rdp['ID']?>" src="images/i/red up-down toggle.jpg" onclick="setID(<?php echo $rdp['ID']?>,event)" /> Product Rank</span>
		<br /><?php
		*/
		?><span class="prodRanking">
		<img title="Move item UP (press the control key to move to ABSOLUTE top)" alt="move up" style="cursor:pointer" id="priority<?php echo $rdp['ID'];?>+1" src="/images/i/red-up-toggle.jpg" onclick="setID2(<?php echo $rdp['ID'];?>,event,1)" />
		<img title="Move item DOWN (press the control key to move to ABSOLUTE bottom)" alt="move down" style="cursor:pointer" id="priority<?php echo $rdp['ID'];?>-1" src="/images/i/red-down-toggle.jpg" onclick="setID2(<?php echo $rdp['ID'];?>,event,-1)" />
		Item Rank
		</span>
		<br /><?php
	}
	if($adminModeTools>1){
		?><span class="editproduct">
		<a title="Edit this product" href="/console/items.php?Items_ID=<?php echo $rdp['ID']?>" onclick="return ow(this.href,'l1_items','700,700');"><img src="/images/i/plusminus-plus.gif" alt="edit" /></a>
		</span>
		<a title="To add a picture, upload and rename the appopriate picture to <?php echo $rdp['SKU']?>" href="/admin/file_explorer/?folder=products/thumb" onclick="return ow(this.href,'l1_thumb','600,600');">Add a thumbnail</a>
		<?php	
	}else{
		?>
		<a title="Edit this product in RelateBase" onClick="return ow(this.href,'l2_gVH1','900,700');" href="http://dev.relatebase.com/client/views/VH1_3.0.0/index.php?nullVID=<?php echo $RelateBaseViewID['items'][0]?>&mode=primary&key=<?php echo $key?>&authKey=<?php echo $RelateBaseAuthKey?>&UN=<?php echo $MASTER_USERNAME?>"><img src="/images/i/edit2.gif" alt="edit" width="15" height="18" /> Edit Product in RelateBase</a><br />
		<a title="Edit ALL PRODUCTS in this category" onClick="return ow(this.href,'l1_gVH1','900,700');" href="http://dev.relatebase.com/client/views/VH1_3.0.0/index.php?nullVID=<?php echo $RelateBaseViewID['items'][0]?>&nullSrch=1&searchMode=search&nullSrchAdv=<?php
		$srch='Category=\'';
		$srch.=addslashes($rdp['Category']);
		$srch.='\'';
		echo urlencode($srch);
		?>&authKey=<?php echo $RelateBaseAuthKey?>&UN=<?php echo $MASTER_USERNAME?>"><img src="/images/i/edit2.gif" alt="edit" width="15" height="18" /> Edit Category (<strong><?php echo $rdp['Category']?></strong>)</a><br />
		<a title="Edit products in this category and subcategory" onClick="return ow(this.href,'l1_gVH1','900,700');" href="http://dev.relatebase.com/client/views/VH1_3.0.0/index.php?nullVID=<?php echo $RelateBaseViewID['items'][0]?>&nullSrch=1&searchMode=search&nullSrchAdv=<?php
		$srch='Category=\'';
		$srch.=addslashes($rdp['Category']);
		$srch.='\' AND ';
		$srch.='SubCategory=\'';
		$srch.=addslashes($rdp['SubCategory']);
		$srch.='\'';
		echo urlencode($srch);
		?>&authKey=<?php echo $RelateBaseAuthKey?>&UN=<?php echo $MASTER_USERNAME?>"><img src="/images/i/edit2.gif" alt="edit" width="15" height="18" /> Edit SubCategory (<strong><?php echo $rdp['Category'] . ' > '.$rdp['SubCategory']?></strong>)</a><br />
		
		<a title="Delete this product permanently" target="w1" href="/index_01_exe.php?mode=deleteItem&ID=<?php echo $rdp['ID']?>" onclick="return confirm('This will permanently delete this product from RelateBase! Are you sure?');"><img src="/images/i/del2.gif" alt="edit" width="16" height="18" /> Delete Product</a><br />
		<a title="Add a new product in RelateBase" onClick="return ow(this.href,'l2_gVH1','900,700');" href="http://dev.relatebase.com/client/views/VH1_3.0.0/index.php?nullVID=<?php echo $RelateBaseViewID['items'][0]?>&mode=insert&nullInsert=2&authKey=<?php echo $RelateBaseAuthKey?>&UN=<?php echo $MASTER_USERNAME?>"><img src="/images/i/add_32x32.gif" alt="edit" width="32" height="32" /> Add new product</a><br />
		<a onClick="return ow(this.href,'file_explorer','700,700');" href="/admin/file_explorer/index.php?uid=uploadthumb&folder=products/large" >Upload thumbnail image</a>
		<?php
	}
	?></div><?php
	echo $prodAdminMode=get_contents();
}


//close wrapper
echo $prodWrapEnd='</div>';


//-------------------------------- end retrieving product presentation ------------------------------
if($flex1Rewrite){
	$standardLayout=ob_get_contents();
	ob_end_clean();
}else if($prodOutputOrder){
	//clear all echoed output
	ob_end_clean();
	foreach($prodOutputOrder as $v){
		if(isset($$v))echo $$v;
	}
}
?>

