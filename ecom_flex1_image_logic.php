<?php
/*
2011-03-29 this begins a new phase of ecommerce.  The main issues are:
1. I want cust. to be able to just upload an image w/o thinking part #
2. I want resizing to occur automatically
3. I want real options on layout

//what type of layout
	* for micro-views I may want just a thumbnail image, not process anything else, and I may want the micro-view to be another type of facebook-like presentation[1], do cool things, or be embedded in something else
	* most basic is medium view of image with jquery popup
	* add to this a caption of the image
	* add to this mutliple images
	* from here several types of slide shows - all relying on external js library with simple calls

[1] e.g. a thumbnail with a balloon like my friends online



	
//by default we will seek to upgrade to the new system

!!!! seems to me that to get past just a raw array of image(s) we almost have to have some type of configuration list, and with a specific configuration, we need to ask:
	1. is an image to be constrained or positioned
	2. do we need to make a process tree on the image and re-point the _tree table/ make copies etc.?



prodImgPanel id=unique
	[prodImgMain]
		<img:main>
	[/prodImgMain]
	[prodImgSlideRepository]
		<img:secondary>
		<img:secondary>
		..
	[/prodImgSlideRepository] 
	[prodImgTextRepository]
	
	[/prodImgTextRepository]
/prodImgPanel

ssOverWrap
	ssDescription/
	ssGallery
		ssGalleryControls
		/ssGalleryControls
		[galleryFrameGroup]
			.galleryFrame
				galleryThumb1
				galleryThumb2
				..
			/.galleryFrame
		[/galleryFrameGroup]
	/ssGallery
	ssWrap
		ssComponent
		
		/ssComponent
		pictureTexts
			title1
			title2
			..
		/pictureTexts
		ssControls
			
		/ssControls
	/ssWrap
/ssOverWrap

*/


if(!$foldersManaged){
	$foldersManaged=true;
	if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/products')) && !mkdir($_SERVER['DOCUMENT_ROOT'].'/images/products')){
		$failedFolders[]='products';
		mail
	}
	if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/products/extralarge') && !mkdri($_SERVER['DOCUMENT_ROOT'].'/images/products/extralarge')){
		$failedFolders[]='products/extralarge';
		mail
	}
	if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/products/large') && !mkdri($_SERVER['DOCUMENT_ROOT'].'/images/products/large')){
		$failedFolders[]='products/large';
		mail
	}
	if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/products/thumb') && !mkdri($_SERVER['DOCUMENT_ROOT'].'/images/products/thumb')){
		$failedFolders[]='products/thumb';
		mail
	}
}

function get_image_1step($seed,$folder='', $options=array()){
	global $get_file_assets;
	if(!$folder)$folder=$_SERVER['DOCUMENT_ROOT'].'/images/products/large';
	$a=explode('/',$folder);
	$folderNode=end($a);
	if(!$get_file_assets[$folderNode])$get_file_assets[$folderNode]=get_file_assets($folder);
	//now get the image by SKU match if present - calling previous function
	foreach($get_file_assets[$folderNode] as $n=>$v){
		if(preg_match('/^'.strtolower($seed).'($|[^-a-z0-9])/',strtolower($n))){
			$pictures[$n]=$v;
		}
	}
}
unset($flexImages,$flexI);
if($a=q("SELECT
	ot.Relationship, ot.Tree_ID, ot.Title, ot.Description, GREATEST(ot.EditDate, t.EditDate) AS EditDate, t.Name, t.Tree_ID
	FROM relatebase_ObjectsTree ot, relatebase_tree t
	WHERE ot.Objects_ID='".$rdp['ID']."' AND ot.ObjectName='finan_items' AND ot.Tree_ID=t.ID AND
	ot.Relationship IN('Primary Image','Image') ORDER BY IF(ot.Relationship LIKE '%Primary%',1,2)", O_ARRAY)){
	foreach($a as $v){
		$path=tree_id_to_path($v['Tree_ID']);
		if(!($w=getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.$path))){
			$msg='do we maybe try to re-pull from images/stock or images/products/stock folder?  here is where we would make use of a tree history system to roll back';
			if(rand(1,10)==5)mail($developerEmail, 'Notice file '.__FILE__.', line '.__LINE__,get_globals($msg),$fromHdrBugs);
			continue;
		}
		$pictures[count($pictures)+1]=array(
			'path'=>$path,
			'width'=>$w['width'],
			'height'=>$w['height'],
			'size'=>$w['size'], /* does this work? */
			'rlx'=>$v['Relationship'],
			'title'=>$v['Title'],
			'description'=>$v['Description'],
		);
		
	}	
}else if(
	get_image_1step($rdp['SKU'],'extralarge',array('return'=>'present')) /*this product is in the extralarge folder*/ || 
	get_image_1step($rdp['SKU'],'large',array('return'=>'present')) /*in the large folder*/ || 
	get_image_1step($rdp['SKU'],'thumb',array('return'=>'present')) /*in the thumb folder*/	){
	if(convert /*default true*/){
		//steps to get the image registered in the database
	}
}



?>