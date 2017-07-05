<?php
/*
2008-09-04 - version 2.00
----------
note that the array currently comes out "raw" or unsorted with the index being the raw order.  Sorting is a secondary operation
I got rid of the where clause entirely but saved the left joins based on the premise of TARGETED SEARCH (i.e. not a google one-field search, and if I want that I set FirstName=LastName=BusinessName=..=value)

2008-06-23
----------
requires a query string like this: 
?BusinessName=&MembershipType=&FirstName=&LastName=&Keywords=near+Austin&any=1 (per public form)
returns an array $searchResults with a relevance value set

*/
unset($_SESSION['special']['searchQuery']);
$sampleKeywords='Living Wills, Plumbing Repair, etc.';
if(isset($Keywords))$Keywords=str_replace($sampleKeywords,'',$Keywords);
//these could return a numeric quantity vs. true to indicate depth of match

if(!function_exists('business_match')){
	function business_match($record,$request){
		if(!$record)return false;
		if(stristr($record,$request))return true;
	}
}
if(!function_exists('name_match')){
	function name_match($recordFirstName,$recordLastName,$requestFirstName,$requestLastName){
		if(!$recordFirstName && !$recordLastName)return false;
		if($requestFirstName && $requestLastName && $requestFirstName !== $requestLastName){
			if(stristr($recordFirstName,$requestFirstName) && stristr($recordLastName,$requestLastName))return true;
		}else if($requestLastName && stristr($recordLastName,$requestLastName)){
			return true;
		}else if($requestFirstName && stristr($recordFirstName,$requestFirstName)){
			return true;
		}
	}
}
if(!function_exists('needslashes')){
	function needslashes($str, $escape='\''){
		for($i=0;$i<strlen($str);$i++){
			$current=substr($str,$i,1);
			if(stristr($escape,$current) && $buffer!=='\\')$out.='\\';
			$out.=$current;
			$buffer=$current;
		}
		return $out;
	}
}
if(isset($BusinessName) || isset($FirstName) || isset($LastName) || isset($MembershipType) || isset($Keywords) || $ShowAll==true){
	if(!$position)$position=1;
	if(!$batch)$batch=20;
	unset($joinA, $fieldsA, $joinB, $fieldsB, $where);
	//any one is sufficient criteria for search
	if($FirstName || $LastName){
		//we DO NOT RELY ON the first and last name in clients
		/* how? */ $joinA=' LEFT JOIN finan_ClientsContacts cc ON a.ID=cc.Clients_ID LEFT JOIN addr_contacts ctc ON cc.Contacts_ID=ctc.ID';
		$fieldsA='ctc.FirstName, ctc.LastName, ctc.ID AS Contacts_ID, cc.Notes, ';
	}
	/*
	if($MembershipType){
		//we will need to evaluate the categorie(s).  Since a member can have >1 category, we avoid this join unless it's specified because
		$joinB=" LEFT JOIN finan_ClientsCategories ccat ON a.ID=ccat.Clients_ID LEFT JOIN finan_items_categories ic ON ccat.Categories_ID=ic.ID AND ic.Name='".needslashes($MembershipType)."'";
		$fieldsB='ic.ID AS Categories_ID, ic.Name AS Category, ';
	}
	*/



	
	$sql="SELECT
	a.ID AS Clients_ID,
	a.UserName,
	IF(a.ClientName!='', a.ClientName, CONCAT(PrimaryFirstName, ' ', PrimaryLastName)) AS UseName,
	IF(a.ClientName!='', 0,1) AS PersonalName,
	a.PrimaryFirstName,
	a.PrimaryLastName,
	a.ID AS Clients_ID,
	a.ClientName,
	a.CompanyName,
	a.Address1, a.City, a.State, a.Zip,
	a.Phone, 
	a.Fax,
	a.Mobile,
	a.WebPage,
	a.Email,
	a.Keywords,
	a.Description,
	$fieldsA
	$fieldsB
	'1'
	FROM
	finan_clients a $joinA $joinB
	
	WHERE ".($bypassMembershipDate ? '' : "
	a.MembershipStart<=CURDATE() AND 
	(a.MembershipEnd >= CURDATE() OR a.MembershipEnd IS NULL) AND ")."
	a.Statuses_ID >= 20 /* current members verified or pending review */ AND 
	".($overrideActive ? '' : "a.Active=1 AND ")."
	(a.ClientName!='' OR a.PrimaryFirstName!='' OR a.PrimaryLastName!='')
	GROUP BY a.ID $having";
	$searchResults=array();
	if($a=q($sql,O_ARRAY)){
		$i=0;
		foreach($a as $v){
			$crit=array();//set to zero
			$continue=false;
			while(true){
				if($BusinessName){
					$crit['business']=(business_match($v['UseName'],$BusinessName) ? 1 : 0);
					if(!$crit['business'] && !$any){
						$continue=true;
						break;
					}
				}
				if($FirstName || $LastName){
					//fn or ln only a like compare; both, a fn% ln% compare
					$crit['name']=(name_match($v['FirstName'],$v['LastName'],$FirstName,$LastName) ? 1 : 0);
					if(!$crit['name'] && !$any){
						$continue=true;
						break;
					}
				}
				if($MembershipType){
					/* 2008-12-24 unable to do this for multiple categories in the main query */
					$crit['type']=q("SELECT COUNT(*) FROM finan_ClientsCategories a, finan_items_categories b WHERE a.Categories_ID=b.ID AND b.Name='".needslashes($MembershipType)."' AND a.Clients_ID='".$v['Clients_ID']."'", O_VALUE);
					//prn($qr);
					if(!$crit['type'] && !$any){
						$continue=true;
						break;
					}
				}
				if($Keywords){
					$crit['keywords']=(stristr($v['Keywords'],stripslashes($Keywords)) || stristr($v['Description'],stripslashes($Keywords)) ? 1:0);
					if(!$crit['keywords'] && !$any){
						$continue=true;
						break;
					}
				}
				if(!array_sum($crit))$continue=true;
				//no keywords for now
				break;
			}
			if($ShowAll){
				//OK			
			}else if($continue){
				continue;
			}
			
			//now build the array - handle duplicates.  The $crit array will be used to handle
			$i++;
			$searchResults[$i]=array_merge($v,array('relevance'=>array_sum($crit)));
			//resort the array
			
		}
	}
	//we handle range in post-eval of the return
	$q=get_navstats(count($searchResults), $position, $batch);
	if(count($searchResults)){
		$_SESSION['special']['searchQuery']=$_SERVER['QUERY_STRING'];
	}else{
		$_SESSION['special']['searchQuery']='reset=1';
	}
}
?>