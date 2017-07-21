<?php
/*
2011-12-25: so here is another approach
if(!$prodViewMode)$prodViewMode='list'; //vs. focus
if($prodViewMode=='list'){
	//one thing we have to do is determine whether it's worthwhile to view more info, and where that location is

}else{
	//one big thing we are concerned with is where to return
}
2010-10-05: docs are now in ecom_flex1_documentation.php
*/
if(!$ecom_flex1_settings){
	//get these settings only once	
	$ecom_flex1_settings=true;

	ob_start();
	if(!$prodCSSSuppressStyleTag){ ?><style type="text/css"><?php }
	?>
	/*Ecommerce CSS*/
	.prodName{
		font-size:16px;
		font-weight:700;
		padding:10px 10px 10px 0;
		}
	.prodCaption{
		margin:0px 0px 0px 25px;
		font-size:12px;
		font-weight:400;
		font-style:italic;
		}
	.prodSKU{
		}
	.prodDescription{
		font-weight:600;
		}
	.on{
		float:left;
		margin:0px 5px 0px 0px;
		}
	.off{
		float:left;
		margin:0px 5px 0px 0px;
		}
	.prodPriceData{
		margin:10px 0px;
		font-size:14px;
		}
	.prodAdminModeCtrl{
		position:absolute;
		right:0px;
		top:0px;
		opacity:.75;
		filter:alpha(opacity=75);
		border:1px solid #666;
		}
	.prodAdminMode{
		position:absolute;
		right:0px;
		top:30px;
		opacity:.75;
		filter:alpha(opacity=75);
		border:1px solid #666;
		background-color:#fff;
		width:250px;
		height:250px;
		}

	/* ------ 2012-06-04 - semi-modal popup displaying cart as a table --------- */
	a.selected {
		background-color:#1F75CC;
		color:white;
		z-index:100;
		}
	.messagepop {
		background-color:#FFFFFF;
		border:1px solid #999999;
		cursor:default;
		display:none;
		margin-top: 15px;
		position:absolute;
		text-align:left;
		width:394px;
		z-index:50;
		padding: 25px 25px 20px;
		}
	label {
		display: block;
		margin-bottom: 3px;
		padding-left: 15px;
		text-indent: -15px;
		}
	.messagepop p, .messagepop.div {
		border-bottom: 1px solid #EFEFEF;
		margin: 8px 0;
		padding-bottom: 8px;
		}
	<?php 
	if(!$prodCSSSuppressStyleTag){ ?></style><?php }
	$prodCSS=get_contents()

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
	function toggle(o,a){
		var c=a['object'].style[a['attrib']];
		if(!c)c=(a['attrib']=='visibility' ? 'hidden' : /*display*/ 'none');
		a['object'].style[a['attrib']]=op[c];
		if(a['onHTML'] && a['offHTML'])o.innerHTML=a[c=='visible' || c=='block' ? 'onHTML' : 'offHTML'];
		return false;
	}
	try{ /* ------- 2012-06-04: first use if jQuery, for modalCartList view ----- */
		function deselect() {
			$(".pop").slideFadeToggle(function(){});
			return false;
		}
		function step1(){
			$(".pop").slideFadeToggle(function() {
				$("#email").focus();
			});
			return false;	
		}
		$.fn.slideFadeToggle = function(easing, callback) {
			return this.animate({ opacity: 'toggle', height: 'toggle' }, "fast", easing, callback);
		};
	}catch(e){ }
	</script>
	<?php
	if(!$adminModeTools)$adminModeTools=2; //2011-12-21
	if(!isset($prodImgAlt))$prodImgAlt='product picture'; // Added By Parker 10/04/10
	if(!isset($prodLinkName))$prodLinkName=true;
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
	if(!isset($thisItemAddedText))$thisItemAddedText='This item has been added to your order. Click here to check out or review your order';
	if(!isset($thisItemAddedTitle))$thisItemAddedTitle='View your order and check out';
	if(!isset($addButtonTitle))$addButtonTitle='Add this product to your order';
	
	if(!isset($addButtonURL)){
		if($componentVersion>=2.0){
			$addButtonURL='/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=products.php&mode=componentControls&submode=addcart'.($prodPostAddDisplay=='modalCartList' ? '&postAddDisplay=modalCartList':'').'&Category='.urlencode(stripslashes($_GET['Category'])).'&SubCategory='.urlencode(stripslashes($_GET['SubCategory']));
		}else{
			$addButtonURL='/index_01_exe.php?mode=addcart&Category='.urlencode(stripslashes($_GET['Category'])).'&SubCategory='.urlencode(stripslashes($_GET['SubCategory']));
		}
	}
	if(!isset($defaultSKULabel))$defaultSKULabel='Item number';
	if(!isset($defaultModelLabel))$defaultModelLabel='Model';
	if(!isset($showSKU))$showSKU=true;
	if(!isset($showModel))$showModel=false;
	
	//default for multiple products listing
	if(!isset($useProductDescriptions))$useProductDescriptions=1;
	if(!isset($showMoreInfoButton))$showMoreInfoButton=true;
	if(!isset($showAddButton))$showAddButton=true;
	if(!isset($prodMainImgPath))$prodMainImgPath='/images/products/thumb/'; #note trailing slash
	if(!isset($prodMainThumbPath))$prodMainThumbPath='/images/products/thumbnails/';
	if(!isset($prodSlidesUseThumbs))$prodSlidesUseThumbs=false;
	if(!isset($prodSlidesThumbSize))$prodSlidesThumbSize='150,175'; //<1 = percentage, n,n=bounding box
	if(!isset($prodSlidesUseExtraLarge))$prodSlidesUseExtraLarge=false;
	if(!isset($prodSlidesExtraLargePath))$prodSlidesExtraLargePath='/images/products/extralarge';
	if(!isset($prodImgMainOptions))$prodImgMainOptions=array(); //for function get_image
	if(!$prodLimitImageWidth)$prodLimitImageWidth=750; //more than we'd ever want to show
	if(!isset($prodSlideClickMessage))$prodSlideClickMessage='Click a square below for more views of the item';
	if(!isset($showRelatedItems))$showRelatedItems=true;
	if(!isset($hideRelatedItemsInAdminMode))$hideRelatedItemsInAdminMode=true;
	if(!isset($relatedItemsHeading))$relatedItemsHeading='Popular Related Items'; // or e.g. Popular Related Items
	if(!isset($autoDetectSlideShow))$autoDetectSlideShow=1; // 1 means look for _1, _2 for addt' product images
	if(!isset($pageHandles['categoryPage']))$pageHandles['categoryPage']='category.php';
	if(!isset($pageHandles['subCategoryPage']))$pageHandles['subCategoryPage']='subcategory.php';
	if(!isset($pageHandles['productsPage']))$pageHandles['productsPage']='products.php';
	if(!isset($pageHandles['singlePage']))$pageHandles['singlePage']='single.php';
	if(!isset($allowProductRanking))$allowProductRanking=true;
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
		for(i=1;i<=range;i++){
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
?><a name="<?php echo strtoupper($rdp['SKU'])?>"></a><div id="prod<?php echo $rdp['ID'];?>" class="<?php echo $prodWrapClass ? $prodWrapClass : 'prodPresentation'?>" <?php echo $adminMode ? 'style="position:relative;"' : ''?>><?php
echo $prodWrap=get_contents();

//special package wording at top (P3)
ob_start();
if($rdp['IsPackage'] && !$hidePackageWording){
	?>
	<div class="prodPackageWording"><?php echo $prodPackageWordingText ? $prodPackageWordingText : '&lt;special package!&gt;'?></div><?php
}
echo $prodPackageWording=get_contents();

//---------------------------- image and image controls --------------------------------------
/*
2011-08-18:
* TODO:
	work on:
		dimensions esp. when needing changed
		size of file
		nofile/noimage
		the extralarge link logic needs changed - 
	make a permanent working place for documentation about the "big picture"
	

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

unset($prodImgPanel, $prodSlides, $slideWidth, $slideHeight, $havePrimary);
if($a=q("SELECT
	ot.Relationship, ot.Tree_ID AS ID, ot.Title, ot.Description, GREATEST(ot.EditDate, t.EditDate) AS EditDate, t.Name, t.Tree_ID
	FROM relatebase_ObjectsTree ot, relatebase_tree t
	WHERE ot.Objects_ID='".$rdp['ID']."' AND ot.ObjectName='finan_items' AND ot.Tree_ID=t.ID AND
	(ot.Relationship IN('Primary Image','Image') OR ot.Relationship='')
	ORDER BY IF(ot.Relationship LIKE '%Primary%',1,2)", O_ARRAY)){
	foreach($a as $v){
		if(!($g=getimagesize($_SERVER['DOCUMENT_ROOT'].($path=tree_id_to_path($v['ID'])))))continue;
		if(!($s=stat($_SERVER['DOCUMENT_ROOT'].$path)))continue;
		$apath=explode('/',$path);
		$unused=array_pop($apath);
		$apath=implode('/',$apath);
		$prodSlides[]=array(
			'name'=>$v['Name'],
			'path'=>$apath,
			'primary'=>(strtolower($v['Relationship'])=='primary image' && !$havePrimary ? ($havePrimary=1) : (preg_match('/\bmain\b/i',$v['Title']) && !$havePrimary ? ($havePrimary=1) : 0)),
			'folder' => 0,
			'atime' => date('Y-m-d H:i:s',$s['atime']),
			'mtime' => date('Y-m-d H:i:s',$s['mtime']),
			'ctime' => date('Y-m-d H:i:s',$s['ctime']),
			'size' => round($s['size']/1024,4),
			'actual_ext' => end(explode('.',$v['Name'])),
			'ext' => strtolower(end(explode('.',$v['Name']))),
			'width' => $g[0],
			'height' => $g[1],
			'area' => $g[0]*$g[1],
			'title' => $v['Title'],
			'dims' => $g[3],
		);
		//get dimensions of containing box if used
		if($g[0]>$slideWidth)$slideWidth=$g[0];
		if($g[1]>$slideHeight)$slideHeight=$g[1];
	}
}else{
	if(isset($prodImgArray)){
		//OK - must be var type Array
	}else{
		//we would normally get it at /images/products/? where
		$prodImgArray=array();
	}
	foreach($prodImgArray as $v){
		if(!preg_match('/^'.$rdp['SKU'].'(_(.+))*(\.(jpg|png|gif|svg))$/i', $v['name'], $a))continue;
		$v['path']='/images/products/large';
		$v['title']=($a[2] ? $a[2] : 'Main picture');
		$v['primary']=($a[2] ? 0 : 1);
		$v['dims']='width="'.$v['width'] . '" height="'.$v['height'].'"';
		$prodSlides[]=$v;
		//get dimensions of containing box if used
		if($v['width']>$slideWidth)$slideWidth=$v['width'];
		if($v['height']>$slideHeight)$slideHeight=$v['height'];
	}	
}
//overall prodImgPanel
ob_start();
if($prodSlides){
	$prodSlides=subkey_sort($prodSlides,'primary','desc');
	?><div id="prodImgPanel"><?php
	
	//-------------- prodImgPanelLarge -------------------
	ob_start(); 
	?><div id="prodImgPanelLarge"><?php
	if(count($prodSlides)>1){
		?><span class="prodSlideClickMessage"><?php echo $prodSlideClickMessage;?></span><?php
	}
	$i=0;
	foreach($prodSlides as $v){
		$i++;
		?><div id="imgInset_<?php echo $flexIdx;?>_<?php echo $i;?>" <?php 
		if($adminMode){ 
			?>onclick="hm_cxlseq=2;showmenuie5(event,0,true);"<?php 
		}
		?> class="prodImg"><?php 
		echo "\n";
		if($v['extralarge']){
			?><a href="<?php echo $prodSlidesExtraLargePath.'/'.$v['extralarge']?>" onclick="return ow(this.href,'l1_picture','<?php ?>');" title="Click here to see a larger version of this image"><?php 
		}
		if($v['width']>$prodLimitImageWidth){
			$dims='width="'.$prodLimitImageWidth.'"';
		}else{
			$dims=$v['dims'];
		}
		?><img 
		id="prodSlide_<?php echo $flexIdx;?>_<?php echo $i;?>" 
		title="<?php echo h($v['title']);?>" 
		src="<?php echo $v['path'].'/'.$v['name'];?>" 
		alt="<?php echo h('image: '.$v['title']);?>" 
		style="display:<?php echo $i==1?'inline':'none';?>;" 
		<?php echo $dims;
		//these are all non XHTML-compliant attributes
		?> 
		filename="<?php echo $v['name']?>"
		filepath="<?php echo $v['path'];?>" 
		size="<?php echo $v['size']?>" 
		nofile="<?php echo $v['nofile']?>" 
		noimage="<?php echo $v['noimage']?>" 
		dims="<?php echo h($v['dims']);?>" 
		mime="<?php echo $v['mime'];?>"
		type="<?php echo $v['mime'];?>"
		/><?php 
		if($v['extralarge']){
			?></a><?php 
		}
		?></div>
		<?php
	}
	?></div><?php
	echo $prodImgPanelLarge=get_contents();

	//-------------- prodImgPanelGallery -------------------
	ob_start(); 
	if(count($prodSlides)>1){
		//controls
		/*begin new slideshow*/
		if($prodSlidesUseThumbs){
			if(!function_exists('create_thumbnail'))require($FUNCTION_ROOT.'/function_create_thumbnail_v200.php');
			//this is the precursor where we inspect the array and build thumbnails for gallery if necessary
			foreach($prodSlides as $n=>$v){
				/*
				the thumbs will be stored "somewhere" and there is a way to map them
					here we encroach on some standard of storing thumbnails
					best way is just the .thumbs.dbr folder
					WHEN A PICTURE IS UPDATED ALL {325x313} E.G. THUMBS MUST BE UPDATED ALSO!!! (IN FEX)
				we assume if there is no thumb, we have a way to create one; last resort the picture itself is the thumb in a very dumb picture set
					namely, we assume the size is known
					remeber, thumbs are just images inside of divs which function as controls to switch main
				we are not yet ready to turn this over to slideshow
				
				NOTE: prodSlidesThumbSize can be a ratio like 0.3, or a bounding box like "175,175"
				
				*/
				
				//first of all, what size are we requiring for the thumbnails?				
				if(strstr($prodSlidesThumbSize,',')){
					//bounding box
					$w=current(explode(',',$prodSlidesThumbSize));
					$h=end(explode(',',$prodSlidesThumbSize));
				}else{
					//ratio
					if(rand(1,5)==3)mail($developerEmail, 'Notice file '.__FILE__.', line '.__LINE__,get_globals('we have set $prodSlidesThumbSize as a ratio, i.e. 0.3.  With this logic there is currently no way to NOT shrink images that are already in a bounding box, since this variable = that info too, need an additional threshold value(s)'),$fromHdrBugs);
					$w=round($v['width'] * $prodSlidesThumbSize);
					$h=round($v['height']* $prodSlidesThumbSize);
				}
				
				if($w<$v['width'] || $h<$v['height'] /* see if thumb needs creating! */){
					if(!is_dir($_SERVER['DOCUMENT_ROOT'].$v['path'].'/.thumbs.dbr') && !mkdir($_SERVER['DOCUMENT_ROOT'].$v['path'].'/.thumbs.dbr')){
						if(!$noFolderNotice)
						$noFolderNotice=mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals('look for $v'),$fromHdrBugs);
						continue;
					}
					if($g=@getimagesize($_SERVER['DOCUMENT_ROOT'].$v['path'].'/.thumbs.dbr/'.preg_replace('/\.(gif|png|jpg|jpeg|svg)$/i','{'.$w.'x'.$h.'}.$1',$v['name']))){
						//OK
					}else{
						$preDims=create_thumbnail(
							$_SERVER['DOCUMENT_ROOT'].$v['path'].'/'.$v['name'],
							$prodSlidesThumbSize,
							'',
							NULL, /* no file yet */
							array('dimsOnly'=>true)
						);
						//correction by 1px usu.
						$w=$preDims[0]; $h=$preDims[1];
						create_thumbnail(
							$_SERVER['DOCUMENT_ROOT'].$v['path'].'/'.$v['name'],
							$prodSlidesThumbSize,
							'',
							$_SERVER['DOCUMENT_ROOT'].$v['path'].'/.thumbs.dbr/'.preg_replace('/\.(gif|png|jpg|jpeg|svg)$/i','{'.$w.'x'.$h.'}.$1',$v['name']),
							''
						);
					}
					$prodSlides[$n]['thumbPath']=$v['path'].'/.thumbs.dbr';
					$prodSlides[$n]['thumbName']=preg_replace('/\.(gif|png|jpg|jpeg|svg)$/i','{'.$w.'x'.$h.'}.$1',$v['name']);
					$prodSlides[$n]['thumbDims']='width="'.$w.'" height="'.$h.'"';
				}else{
					$prodSlides[$n]['thumbPath']=$v['path'];
					$prodSlides[$n]['thumbName']=$v['name'];
					$prodSlides[$n]['thumbDims']=$v['dims'];
				}
			}
		}	
		?><div id="prodImgPanelGallery">
		<?php
		$i=0;
		foreach($prodSlides as $v){
			$i++;
			?><div id="prodSlideButton_<?php echo $flexIdx?>_<?php echo $i;?>" title="Click this to see <?php echo $i==0?'the first picture':'picture '.$i;?>" class="off" onclick="prodSlideToggle(<?php echo $flexIdx?>,<?php echo count($prodSlides);?>,<?php echo $i;?>);"><img src="<?php echo $v['thumbPath'].'/'.$v['thumbName'];?>" <?php echo $v['thumbDims']?> /></div><?php
		}
		?></div><?php
	}
	$prodImgPanelGallery=get_contents();
	
	
	?></div><?php
}
echo $prodImgPanel=get_contents();

//product name and product caption
ob_start();
?><h2 class="prodName">
<?php 
if($prodLinkName){
	?><a href="/products/<?php echo urlencode($rdp['SKU']);?>" title="view more information on this product"><?php
}
?>
<?php echo $rdp[$prodAlternateNameField ? $prodAlternateNameField : 'Name'];?><?php
if($prodLinkName){
	?></a><?php
}
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
ob_start();
if($showModel){
	?><div class="prodModel">
		<span class="key"><?php echo $defaultModelLabel?>: </span>
		<span class="value"><?php echo $rdp['Model'];?></span>
	<?php
	?></div><?php
}
echo $prodModel=get_contents();

//product SKU
ob_start();
if($showSKU){
	?><h3 class="prodSKU">
		<span class="key"><?php echo $defaultSKULabel?>: </span>
		<span class="value"><?php echo $rdp['SKU'];?></span>
	<?php
	?></h3><?php
}
echo $prodSKU=get_contents();

//related items
ob_start();
if((count($b=q("SELECT *
	FROM finan_items_related a LEFT JOIN finan_items b ON
	(Parent_ID='".$rdp['ID']."' AND Child_ID=b.ID) OR
	(Child_ID='".$rdp['ID']."' AND Parent_ID=b.ID AND a.Reflexive=1)
	WHERE Parent_ID='".$rdp['ID']."' OR Child_ID='".$rdp['ID']."'
	ORDER BY IF(UnitPrice!=0,1,2), UnitPrice", O_ARRAY)) && $showRelatedItems) || ($adminMode && !$hideRelatedItemsInAdminMode)){
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
}
echo $prodRelatedItems=get_contents();

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

if($prodShowDimensions && $rdp['Width']>0 && $rdp['Length']>0 && $rdp['Depth']>0){
	ob_start();
	?><div id="prodDimensions">
	<span class="key">Dimensions:</span>
	<span class="value"><?php echo $rdp['Width'];?>"W x <?php echo $rdp['Length'];?>"H x <?php echo $rdp['Depth'];?>"D</span>
	</div><?php
	echo $prodDimensions=get_contents();
}
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
ob_start();
if($showMoreInfoButton){
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
}
echo $prodMoreInfo=get_contents();

//------------------------------- quantity control ---------------------------------
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
ob_start();
if($showAddButton){
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
}
echo $prodAdd=get_contents();

//---------------------------- added OK visual aid -------------------------
ob_start();
?><div id="added<?php echo $rdp['ID']?>" class="prodAdded" style="visibility:hidden;"><a title="<?php echo $thisItemAddedTitle?>" href="<?php echo $shoppingCartURL;?>"><?php echo $thisItemAddedText?></a></div><?php
echo $prodAdded=get_contents();
//this was added 2012-06-04: modal Cart List pops up on addition of cart item; new standard
if(!$prodModalCartList){
	//note this is only added one time
	ob_start();
	?><div id="modalCartList" class="messagepop pop"> <!-- container for cart listing popup --> &nbsp; </div><?php
	echo $prodModalCartList=get_contents();
}

//---------------------------- admin mode features --------------------------
ob_start();
if($adminMode){
	?><div class="prodAdminModeCtrl _editLink_1">
	<a href="#" onclick="return toggle(this, {'object':this.parentNode.nextSibling, 'attrib':'visibility', 'onHTML':'', 'offHTML':''});"><img src="/images/i/arrows/wht-arrow-sm-dn.png" alt="edit" /></a>
	</div><div class="prodAdminMode" style="visibility:hidden;">
	<a href="/console/items.php?Items_ID=<?php echo $rdp['ID'];?>" onclick="return ow(this.href,'l1_items','750,700');"><img src="/images/i/edit2.gif" /> edit this item</a><br />

	<?php
	if($rdp['CreateDate']){
		?>You added this product: <?php echo date('n/j/Y \a\t g:iA', strtotime($rdp['CreateDate']));?><br /><?php
	}
	if(isset($rdp['Used'])){
		if($n=$rdp['Used']){
			?>This item has been used <?php echo $n?> time<?php echo $n>1?'s':''?>&nbsp;&nbsp;[<a href="/console/items.php?Items_ID=<?php echo $rdp['ID']?>" onclick="return ow(this.href,'l1_items','750,700');">view report</a>]<br /><?php
		}else{
			?>This item has not been used in orders<br /><?php
		}
	}
	if($allowProductRanking){
		?><img title="Move item UP (press the control key to move to ABSOLUTE top)" alt="move up" style="cursor:pointer" id="priority<?php echo $rdp['ID'];?>+1" src="/images/i/red-up-toggle.jpg" onclick="setID2(<?php echo $rdp['ID'];?>,event,1)" />
		&nbsp;
		<img title="Move item DOWN (press the control key to move to ABSOLUTE bottom)" alt="move down" style="cursor:pointer" id="priority<?php echo $rdp['ID'];?>-1" src="/images/i/red-down-toggle.jpg" onclick="setID2(<?php echo $rdp['ID'];?>,event,-1)" />&nbsp; Product Ranking<br />
		<?php
	}
	?>
	<br />
	delete<br />
	
	</div><?php
}
echo $prodAdminMode=get_contents();

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

