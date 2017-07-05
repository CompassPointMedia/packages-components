<?php
/*
Ecom Flex v1.0 - 005 2010-10-05 by Samuel
-----------------------------------------

2011-02-21: 
	sf broke down prodImgPanel var into prodImgPanelLarge and prodImgPanelGallery - because I need to separate them physically for some applications
	sf changed get_image(); defaultNAImage, defaultNAImageWidth, defaultNAImageHeight, and defaultNAImageExt no longer referenced, and no image present returns nothing vs. populating $get_image

2010-10-05: long time since touching this, big goals are
	clean up editing tools
* note that docs are now a separate file - they are way to big to be in the comp
* added prodOutputOrder - an array which can dictate the order the PHP Output vars are presented

todo
----
* 2009-01-10 - handle featured product highlighting in some easy way
* handle dyanmic sku fields present
* dropdown or ul - click to see different colors of this shirt..
* logout button or hide admin mode controls
* export product into quickbooks or csv
* link to report on product
* for p3 the location of the add To Order div was first conceived as just below the image -  need a way to move strings within the output html
* p3 needs logic about displaying either the main SKU image or the representative images if flagged somehow
* the development in p3 had an idea as follows: [<a href="#">more</a>]</div> <div id="more info">(this expands or balloons)</div>
[grouped product protocol integration - probably better to clone off as a more advanced since the arrays are so different in GPP]

* todo on p3
	gel on calculations - must be abstracted from html for shopping cart and for 
		enough info should be added to each item so that the db doesn't need to be called in calculating overall or individual price.  therefore the fields required are:
			-
			-
			- etc.
	lots of settings like do I want to condense THAT PACKAGE on the shopping cart and just rely on the package entry - legal issues here, docs to consider
	for specific package price option to NOT calculate silly discounts pro-rata and just do a numeric discount below for package, then specific package price, then maybe a "you save x%"
	how to handle descriptions
	a slideshow of products below
	pop-up for the indiv. products in a balloon
	"bonus item!" icon
	more complex styles but as an extension of existing
	bold for (2 ea.)
	idx items in package
	name and description overrides
	ability to have price next to name " (normally 34.95)"



Change Log
-------------------------
2009-03-04
* introduced useCMSBForDescriptionEdit (binary) 1=edit short with CMSB, 2=edit long, 3=both
* implemented wholesale pricing
2008-10-31 
* allows an external function to process, it must globalize $get_image - with nodes of name (case-sensitive), width, and height.  The source returned must be the actual path to the image plus name
2008-10-09
* integrated changes from Compass Guide - priority sorting and if(!settings) wrapper so called only once
2008-09-30
* got start of packaged product protocol (P3) in place - much to do
2008-08-28
* added the slide show feature, recognizing images of [sku]_* as part of sku
* added buttons recognized by default:
	images/assets/defaultMoreInfoButton.jpg
	images/assets/defaultAddButton.jpg
2008-08-21
* added the adminMode features which had been left out


DOCUMENTATION
-----------------------------------------------------
here is a complete summary of the css and the logic approach behind presenting it
prodWrapClass ? prodWrapClass : 'prodPresentation'
prodPackageWording
prodImgPanel
	prodImgMain
	prodSlideClickMessage
	prodSlideControl
		on
		off
prodName
prodCaption
prodSKU
	key
	value
prodRelatedItems
	heading (h3)
prodDescription
	short
	divider
	long
prodPackageData
	prodPackageHeading
	prodPackageExtraImages
	prodPackageCostSummary
		suggested
			key
			value
		actual
			key
			value
		savings
			key
			value
prodPriceData
	wholesale [if present]
		key
		value
	suggested
		key
		value
	actual [or retail if wholesale present]
		key
		value
	savings
		key
		value
	percent
		key
		value
	dollar
		key
		value
prodMoreInfo
	qty [input]
prodQty
prodAdd
prodAdded
prodAdminMode
prodRanking

main PHP Output variables - these can be output in a different order if desired
-------------------------------------------------------------------------------
prodWrap
prodPackageWording
prodImgPanel
prodName
prodModel
prodSKU
prodRelatedItems
prodDescription
prodPackageData || prodPriceData (not both)
prodMoreInfo
prodQty
prodAdd
prodAdded
prodAdminMode
prodWrapEnd



//------------------- this is the original (mostly unimplemented) documentatation on this comp ---------------------
designed to consider width of channel it is placed in, and wrap text around the image either to the left, right, or bottom of the image(s).
Pulls the instructions on layout using standard product layout protocol (SPLP) we are developing right now

local variables available
	$channelWidth = [pixels]
	$targetObjectWidth = [pixels]
	$objectOrientation = vertical | horizontal loop

there are several considerations I want to factor in:
1. space I have to work with.  I assume the space will be a block, however it might be a floated block so that it can load left-right say 3 across
2. width of the images involved
3. what layout has been called-for on the page.  For example, we can specify verbose, sparse, or custom
4. what layout is called-for on the product.  The product may be grouped or need a different layout due to its nature
5. whether the product layout or group layout can override the called-for layout

in order for the system to truly be as flexible as I am envisioning, it would need to do some very complex calculations or at least have a formula considering:
	a. size of image
	b. amount of text in descriptions
	c. redundancy between description and long description
	d. grouping of products
//------------------------------------------------------------------------------------------------------

*/
?>