<?php

 //ini_set('display_errors', 1);
 //ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
//ini_set('display_errors', 1);

//FETCH ALL INFO. OF NORMAL CHILD.

$jsonString = file_get_contents("php://input");
$myFile = "infojson.txt";
file_put_contents($myFile,$jsonString);
header('Content-Type: application/x-www-form-urlencoded');
header('Content-Type: application/json');

require_once("config/database.php");
require_once("new_getblockuser_list.php");
$unixTimeStamp=date("Y-m-d"). date("H:i:s");
$server=$_SERVER['SERVER_NAME'];

$decodedinfoArray = json_decode( file_get_contents('php://input'));

$getIterationIDWhoStackInfo = '';
$getSessionIterationIDInfo = '';
$countIterationArray=array();
$userId= $decodedinfoArray->userID;
$type= $decodedinfoArray->type;
$imageId=$decodedinfoArray->imageID;
$iterationId= $decodedinfoArray->iterationID;
$cubeInfo= $decodedinfoArray->cubeInfo;
$loginUserId= $decodedinfoArray->loginUserID;
$iterationButton=$decodedinfoArray->iterationButton;
$lastViewed=$decodedinfoArray->lastViewed;
$relatedThreadID=isset($decodedinfoArray->relatedThreadID)?$decodedinfoArray->relatedThreadID:'';
$autorelatedID=isset($decodedinfoArray->autorelatedID)?$decodedinfoArray->autorelatedID:'';
$forwardChild=isset($decodedinfoArray->forwardChild)?$decodedinfoArray->forwardChild:'';
$oldThreadId=isset($decodedinfoArray->oldThreadId)?$decodedinfoArray->oldThreadId:'';
$oldImageID=isset($decodedinfoArray->oldImageID)?$decodedinfoArray->oldImageID:'';
$oldAutorelatedID=isset($decodedinfoArray->oldAutorelatedID)?$decodedinfoArray->oldAutorelatedID:'';
$oldIterationID=isset($decodedinfoArray->oldIterationID)?$decodedinfoArray->oldIterationID:'';
$contributorSession=isset($decodedinfoArray->contributorSession)?$decodedinfoArray->contributorSession:'';
$optionalOf=isset($decodedinfoArray->optionalOf)?$decodedinfoArray->optionalOf:'';
$optionalIndex=isset($decodedinfoArray->optionalIndex)?$decodedinfoArray->optionalIndex:'';
$contributorSession=isset($contributorSession)?$contributorSession:'0';
$optionalOf=isset($optionalOf)?$optionalOf:'';
$optionalIndex=isset($optionalIndex)?$optionalIndex:'';


$iterationButton=isset($iterationButton)?$iterationButton:'0'; // iteration_button=0 means fetch session data, iteration_button=1 means fetch without session data
 mysqli_set_charset($conn,"utf8mb4");
if($loginUserId=='')
{
	echo json_encode(array('message'=>'login_userid is missing.','success'=>0));
	exit;

}
if($iterationId=='')
{
	echo json_encode(array('message'=>'iteration_id is missing.','success'=>0));
	exit;

}
if($imageId=='')
{
	echo json_encode(array('message'=>'unique_id is missing.','success'=>0));
	exit;

}
if($type=='')
{
	echo json_encode(array('message'=>'type is missing.','success'=>0));
	exit;

}
if($userId=='')
{
	echo json_encode(array('message'=>'user_id is missing.','success'=>0));
	exit;

}
/****Check if stacklink push and hold session exist****/

$stacklinkSession=mysqli_query($conn,"SELECT * FROM stacklink_push_hold_session_table WHERE UserID='$loginUserId' AND iterationID='$iterationId'");
	$my1File = "checkj.txt";
	$jj="SELECT * FROM stacklink_push_hold_session_table WHERE UserID='$loginUserId' AND iterationID='$iterationId'";

	if(mysqli_num_rows($stacklinkSession)>0){
		$iterationButton='1';
		file_put_contents($my1File,$jj);

	}

$blockUserList=getBlockedUsersInfo($loginUserId,$conn);

if($blockUserList=='')
{
	$blockUserList=0;
}
$getBlockUserList = explode(",",$blockUserList);

$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);

$username=mysqli_fetch_row(mysqli_query($conn,"SELECT username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
AS profileimg FROM tb_user WHERE id='$userId'"));

//with out session Query

$getstackInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT imageID,SUBSTRING_INDEX(stacklinks,',',1) As  active_stacklink_name , stacklinks FROM iteration_table WHERE   iterationID='".$iterationId."'"));
$fetchActiveStackName=$getstackInfo['active_stacklink_name'];

$getstackInfos=mysqli_fetch_assoc(mysqli_query($conn,"SELECT imageID,SUBSTRING_INDEX(stacklinks,',',-1) As  active_stacklink_name , stacklinks FROM iteration_table WHERE   iterationID='".$iterationId."'"));
$stackLinkType=$getstackInfos['active_stacklink_name'];


$breakactivestacklink=explode('/',$fetchActiveStackName);
if($breakactivestacklink[1] == 'home')
{
	$r1 =$imageId;
}
else{
$q1 = "SELECT imageID FROM iteration_table WHERE iterationID = $breakactivestacklink[1]";

			$rs1 = mysqli_query ($conn,$q1);
			$r1 = mysqli_fetch_assoc($rs1);
}



function getOwnerName($fdid,$imageId,$conn)
{

	$ownerName=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username FROM `image_table` AS img INNER JOIN `tb_user` AS u ON ( img.`userID` = u.ID )	WHERE img.`imageID` ='".$imageId."'"));
				return $ownerName['username'];

}

function getOwnerName1($fdid,$imageId,$conn)
{

	$ownerName=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username FROM `iteration_table` AS img INNER JOIN `tb_user` AS u ON ( img.`userID` = u.ID )	WHERE img.`iterationID` ='".$imageId."'"));
				return $ownerName['username'];

}
/***fetch count of stories where image existing***/
function storyThreadCount($imageId,$userId,$iterationId,$conn)
{
	$unixTimeStamp=date("Y-m-d"). date("H:i:s");
 $server='https://'.$_SERVER['SERVER_NAME'];

	$storyThreadCount=array();



	$query=mysqli_query($conn,"SELECT cube.id, cube.userID,cube.iterationID FROM cube_table as `cube` INNER JOIN  `iteration_table` as `it` ON  FIND_IN_SET(it.iterationID, cube.tags) WHERE it.`iterationID`=".$iterationId." GROUP BY cube.cube_name order by id desc limit 1");

				$count=mysqli_num_rows($query);
				if($count==1){
					
					$cubaData=mysqli_fetch_assoc($query);


					$checkinData=mysqli_query($conn,"SELECT * FROM display_cube_table WHERE coverImageChange ='0' and iterationID ='".$iterationId."' and cubeID='".$cubaData['id']."' and  cubeiterationID ='".$cubaData['iterationID']."' and userID ='".$userId ."'  ") or die(mysqli_error());

					if(mysqli_num_rows($checkinData)<=0)
					{


						$insertLastViewTable=mysqli_query($conn,"INSERT INTO display_cube_table(coverImageChange,iterationID,cubeID,cubeiterationID,userID)
						VALUES('0','".$iterationId."','".$cubaData['id']."','".$cubaData['iterationID']."','".$userId ."')") or die(mysqli_error());
						
				
						$insertCubeLastViewTable=mysqli_query($conn,"INSERT INTO cube_last_viewed_table(coverImageChange,iterationID,imageID,cubeID,cubeiterationID,userID,created_at,updated_at)
						VALUES('0','".$iterationId."','".$imageId."','".$cubaData['id']."','".$cubaData['iterationID']."','".$userId ."','".strtotime($unixTimeStamp)."','".strtotime($unixTimeStamp)."')") or die(mysqli_error());
					}
					else
					{
						$delcheckinData=mysqli_query($conn,"delete  FROM display_cube_table WHERE iterationID ='".$iterationId."' and cubeID='".$cubaData['id']."' and  cubeiterationID ='".$cubaData['iterationID']."' and userID ='".$userId ."'  ") or die(mysqli_error());

						$insertLastViewTable=mysqli_query($conn,"INSERT INTO display_cube_table(coverImageChange,iterationID,cubeID,cubeiterationID,userID)
						VALUES('0','".$iterationId."','".$cubaData['id']."','".$cubaData['iterationID']."','".$userId ."')") or die(mysqli_error());
						
					
						
						mysqli_query($conn,"update cube_last_viewed_table set updated_at='".strtotime($unixTimeStamp)."'  where cubeiterationID ='".$cubaData['iterationID']."' and  iterationID ='".$iterationId."' and userID ='".$userId."'");


					}




					 $url = $server."/stagingStackApis/new_cube_launching_info.php";
				   	 $json = json_encode([
				        'cubeID' => $cubaData['id'],
				        'userID' => $userId,
						'iterationID' => $iterationId
				    ]);


				 		//prepare curl request
				     $ch = curl_init($url);

				   # curl_setopt($ch, CURLOPT_USERPWD);
				    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

				    $result = curl_exec($ch);
				    $result =json_decode($result);
//echo"<pre>";print_r($result);die;
					$storyThreadCount['storyThreadCount']='1';
					$storyThreadCount['storyData']=$result->data;
				    return $storyThreadCount;
				 	 curl_close($ch);

				}
				else{
					$storyThreadCount['storyThreadCount']=$count;
					$storyThreadCount['storyData']='';
					return $storyThreadCount;
				}


}



#----------------------------fetch parent  ------------------------------#

function checkParent($iterationId)
{

	$parentId = $iterationId; // the parent id
	$arrAllChild = Array(); // array that will store all childreniterationId
	while (true) {
		$arrChild = Array(); // array for storing children in this iteration

		$q = "SELECT linked_iteration FROM tag_table WHERE iterationID = $parentId ";
		$rs = mysqli_query ($q);
		while ($r = mysqli_fetch_assoc($rs)) {
			$arrChild[] = $r['linked_iteration'];
			$arrAllChild[] = $r['linked_iteration'];
		}
		if (empty($arrChild)) { // break if no more children found
			break;
		}
		$parentId = implode(',', $arrChild); // generate comma-separated string of all children and execute the query again
	}

	return $arrAllChild;
}




	#---------------------------end----------------------------------------------#
	/*---------------   fetch stacklink iteration id ----------------------------*/
	function stacklinkIteration($conn,$iterationId,$param = NULL,$imageId= NULL,$userID)
	{

		if($param=='iteration'){
			/* $q = "SELECT toCS FROM iteration_table WHERE iterationID = $iterationId";
			$rs = mysqli_query ($q);
			$r = mysqli_fetch_assoc($rs);
			$toCS=explode('_', $r['toCS']);
			return $toCS[1]; */


			$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$imageId."'  AND iterationID='".$iterationId."' "));

			if($getSubIterationImageInfo['did'] === 1)
			{
				return $iterationId;

			}
			else
			{

				$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
				if(mysqli_num_rows($cubeInfoData)>0)
				{
				
			
					$getIterationIDInfos=mysqli_query($conn,"select * from (SELECT SUBSTRING_INDEX(stacklinks,',',1) As stacklink_name,iterationID,sequenceID,userID FROM `iteration_table` where iterationID in(SELECT iterationID FROM sub_iteration_table WHERE imgID='".$imageId."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',rdid))) as newtable where   userID='".$userID."' order by iterationID desc limit 1");
				}
				else
				{
					
	
					$getIterationIDInfos=mysqli_query($conn,"select * from (SELECT SUBSTRING_INDEX(stacklinks,',',1) As stacklink_name,iterationID,sequenceID,userID FROM `iteration_table` where iterationID in(SELECT iterationID FROM sub_iteration_table WHERE imgID='".$imageId."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',rdid))) as newtable where   userID='".$userID."' and LOCATE('/home',stacklink_name) order by iterationID desc limit 1");
				}





				if(mysqli_num_rows($getIterationIDInfos)==0)
				{
					$a='';



					$getIterationIDInfos1=mysqli_query($conn,"select * from (SELECT SUBSTRING_INDEX(stacklinks,',',1) As stacklink_name,iterationID,sequenceID,userID FROM `iteration_table` where iterationID in(SELECT iterationID FROM sub_iteration_table WHERE imgID='".$imageId."')) as newtable where  LOCATE('/home',stacklink_name) and userID='".$userID."' order by iterationID desc limit 1");
					if(mysqli_num_rows($getIterationIDInfos1)==0)
					{
						return $a;
					}
					else
					{
						$getIterationIDInfonew=mysqli_fetch_assoc($getIterationIDInfos1);
						return	$getIterationIDInfonew['iterationID'];
					}


				}
				else
				{
					$getIterationIDInfo=mysqli_fetch_assoc($getIterationIDInfos);
					return	$getIterationIDInfo['iterationID'];
				}




			}

		}
		if($param=='image'){
			$q = "SELECT toCS FROM iteration_table WHERE iterationID = $iterationId";
			$rs = mysqli_query ($q);
			$r = mysqli_fetch_assoc($rs);
			$toCS=explode('_', $r['toCS']);
			//return $toCS[1];
			$q1 = "SELECT imageID FROM iteration_table WHERE iterationID = $toCS[1]";
			$rs1 = mysqli_query ($q1);
			$r1 = mysqli_fetch_assoc($rs1);
			return $r1['imageID'];
		}
		if($param=='type'){


		$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
		WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID='".$imageId."'"));

			/* $q = "SELECT toCS FROM iteration_table WHERE iterationID = $iterationId";
			$rs = mysqli_query ($q);
			$r = mysqli_fetch_assoc($rs);
			$toCS=explode('_', $r['toCS']);
			$q1 = "SELECT img.type FROM image_table img INNER JOIN iteration_table it ON(img.imageID=it.imageID) WHERE it.iterationID = $toCS[1]";
			$rs1 = mysqli_query ($q1);
			$r1 = mysqli_fetch_assoc($rs1); */

			return $stackDataFetchFromImageTable['type'];
		}
	}
	#---------------------------end----------------------------------------------#

/*---------------   fetch all horizontal swapped images ----------------------------*/
function checkChild($iterationId)
{

	$parentId = $iterationId; // the parent id
	$arrAllChild = Array(); // array that will store all children
	while (true) {
		$arrChild = Array(); // array for storing children in this iteration
		$q = "SELECT iterationID FROM tag_table WHERE linked_iteration = $parentId and lat='empty' and lng='empty'";
		$rs = mysqli_query ($q);
		while ($r = mysqli_fetch_assoc($rs)) {
			$arrChild[] = $r['iterationID'];
			$arrAllChild[] = $r['iterationID'];
		}
		if (empty($arrChild)) { // break if no more children found
			break;
		}
		$parentId = implode(',', $arrChild); // generate comma-separated string of all children and execute the query again
	}

	return $arrAllChild;
}
/*---------------------------------------------------------------------------*/
/******************Fetch autorated parent name **************/
// if($relatedThreadID != "" ){
// 	$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
// 	if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

// 		$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);
// 		$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));

// 		$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
// 	}

// 	if($optionalOf !="" && $optionalIndex !="" )
// 	{
// 		if($optionalOf==$iterationId){
// 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
// 		$arrayIndex=$optionalIndex-1;

//  	}
//  	else{
// 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
// 		$arrayIndex=$autorelatedID;

// 	}

// 		if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

// 		$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
// 		$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));

// 		$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
// 	}

// 	}
// }
// else{

// 	$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$iterationId."'");
// 	if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

// 		$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);
// 		$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));

// 		$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
// 	}




// }
/****************** END  Fetch autorated parent name **************/


if($userId!='' && $type!='' && $imageId!='' && $iterationId!='')
{


		$getSubIterationInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT  count( userID) as totalcount from iteration_table where adopt_photo=0 and iterationID in(SELECT iterationID FROM `sub_iteration_table` where imgID='".$getstackInfo['imageID']."')"));

		$data['countImageShare']=$getSubIterationInfo['totalcount'];


		$checkReportImage=mysqli_query($conn,"SELECT id FROM image_report_table where iterationID ='".$iterationId."' and userID='".$loginUserId."'"); // get here image report or not
		if(mysqli_num_rows($checkReportImage)==0)
		{
			$data['imageRepoted']=0;
		}
		else
		{
			$data['imageRepoted']=1;
		}


		if($lastViewed==1)
		{

				$checkLastInfo=mysqli_query($conn,"SELECT iterationID FROM last_viewed_table where iterationID ='".$iterationId."' and  imageID='".$getstackInfo['imageID']."'
				and userID='".$loginUserId."'");
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");
				if(mysqli_num_rows($checkLastInfo)<=0)
				{

					$insertLastViewTable=mysqli_query($conn,"INSERT INTO last_viewed_table(iterationID,imageID,userID,viewedDate)
					VALUES('".$iterationId."','".$getstackInfo['imageID']."','".$loginUserId."','".strtotime($unixTimeStamp)."')") or die(mysqli_error());
				}
				else{

					$updateLastView=mysqli_query($conn,"update last_viewed_table set viewedDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$getstackInfo['imageID']."' ");
				}
		}


if($type==2) //photo
{
	$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid,setsession FROM sub_iteration_table WHERE imgID='".$imageId."'  AND iterationID='".$iterationId."' "));



	$data['stackIteration']=$getSubIterationImageInfo['did'];
	$getCopiesRdidCount=count(explode(',',$getSubIterationImageInfo['rdid']));
	$lastSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".$imageId."'  order by id desc"));
  // $data['countInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
  // $data['optionalCountInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];

	$getSubIterationFirstInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID FROM sub_iteration_table WHERE imgID='".$imageId."'  and did = 1"));
	
	$creatorUserName=getOwnerName1($getSubIterationImageInfo['fdid'],$iterationId,$conn);
	$originalUserName=getOwnerName1(1,$getSubIterationFirstInfo['iterationID'],$conn);

	$cubeCount=storyThreadCount($imageId,$loginUserId,$iterationId,$conn);


	$logged_username=mysqli_fetch_row(mysqli_query($conn,"SELECT username FROM tb_user WHERE id='$loginUserId'"));
	$owner_username=mysqli_fetch_row(mysqli_query($conn,"SELECT username FROM tb_user WHERE id='$userId'"));


	foreach($cubeCount['storyData']->imageData as $ky => $vl){
		if($vl->autoApprove==0&&($logged_username[0] != $vl->userName)&&($loginUserId!=$vl->autoapproveUserid)  &&($loginUserId!=$vl->existing_autoapprove_userid)){
			unset($cubeCount['storyData']->imageData[$ky]);
		}
	}

	$data['ownerName']=$creatorUserName;
	$data['originalUserName']=$originalUserName;
	$data['storyThreadCount']=($cubeCount['storyThreadCount'] == NULL ? '' : $cubeCount['storyThreadCount']);
	$data['contributorSession']=$contributorSession;
	if($cubeInfo ==1)
	{
		$data['cubeinfo']='';
	}
	else{
	$data['cubeinfo']=($cubeCount['storyData'] == NULL ? '' : $cubeCount['storyData']);
	}
	$newUsername=mysqli_fetch_row(mysqli_query($conn,"SELECT username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
	AS profileimg,cover_image,fname FROM tb_user WHERE id='".$userId."' "));



	$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$loginUserId."' and iterationID='".$iterationId."' and imageID= '".$imageId."' and status ='1'"); //active the notify button or not

	$getInfo=mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='$imageId' ") or die(mysqli_error());


	if(mysqli_num_rows($getInfo)>0)
	{

		$row=mysqli_fetch_assoc($getInfo);

			//query use for session
	//	$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$iterationId."'");
	
	
		$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE iterationID='".$iterationId."'");
		if(mysqli_num_rows($getImageInfo)>0)
		{
			$totalData=NULL;
			$imagerow=mysqli_fetch_assoc($getImageInfo);

			$data['userName']=$newUsername[0];

			if(mysqli_num_rows($stackNotify)>0)
			{
				$data['stackNotify']=1;
			}
			else
			{
				$data['stackNotify']=0;
			}


			$cubeInfoData=mysqli_query($conn,"SELECT id FROM cube_table WHERE profilestory = 1  and  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
			if(mysqli_num_rows($cubeInfoData)>0)
			{
				$data['profileStoryItem']=1;
			}
			else
			{
				$data['profileStoryItem']=0;
			}



			$getBlockUserData=mysqli_query($conn,"SELECT status,id FROM `block_user_table` WHERE ((userID='".$loginUserId."'  and blockUserID='".$userId."') OR (userID='".$userId."'  and blockUserID='".$loginUserId."')) and status ='1'");
			if (mysqli_num_rows($getBlockUserData)>0)
			{
				$data['additionStatus']=1;  //check the iteration for block user(if blocker see the iteration then hide all the button. )
			}
			else
			{
				$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);
				$getBlockImageIDList = explode(",",$allBlockImageID);
				if (in_array($row['imageID'], $getBlockImageIDList))
				{
					$data['additionStatus']=1;
				}
				else
				{
					$data['additionStatus']=0;
				}
			}

			if($imagerow['allow_addition']=='0')
			{

				$data['allowAddition']=0;	 //means user can add anything on stack
			}
			else if($imagerow['allow_addition']=='1' and $imagerow['userID']==$loginUserId )
			{

				$data['allowAddition']=0;	 //means user can add anything on stack
			}
			else if($imagerow['allow_addition']=='1' and $imagerow['userID']!=$loginUserId )
			{

				$data['allowAddition']=1;	 //means user cannot add anything on stack
			}
			else
			{

				$data['allowAddition']=0;	 //means user can add anything on stack
			}
			
			
			
			
			$adoptPhotoType=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where user_id='".$loginUserId."' and adopt_iterationID='".$iterationId."' and status=1"));
			if(!empty($adoptPhotoType))
			{
				if($adoptPhotoType['type']==1)
				{
					$data['adoptPhoto']=1; //down
				}
				else if($adoptPhotoType['type']==2){
					$data['adoptPhoto']=2; //up level
				}
				else{
					
					
					$data['adoptPhoto']=0;
				}

			}
			else
			{
				$data['adoptPhoto']=0;
			}
			$data['allowAdditionToggle']=($imagerow['allow_addition'] == NULL ? '' : $imagerow['allow_addition']);
			$data['caption']=($imagerow['caption'] == NULL ? '' : stripslashes($imagerow['caption']));
			$data['userID']=$imagerow['userID'];
			//$data['name']=$imagerow['stacklink_name'];
			$data['ID']=$imageId;
			$data['title']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
			$data['typeID']=$row['imageID'];
			$data['imageID']=$row['imageID'];
			$data['iterationID']=$imagerow['iterationID'];
			$data['creatorUserID']=$imagerow['userID'];
			$countIterationArray=array();
			$collectIterationID=mysqli_query($conn,"SELECT iterationID FROM iteration_table  WHERE imageID='$imageId'");
			while($collectIterationIDS=mysqli_fetch_assoc($collectIterationID))
			{
				$iterationIDContain[]=$collectIterationIDS['iterationID'];

				$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$collectIterationIDS['iterationID']."',tags) ") or die(mysqli_error());
				if(mysqli_num_rows($cubeInfo)>0)
				{
					while($cubeInformation=mysqli_fetch_assoc($cubeInfo))
					{

						$countIterationArray[]=$cubeInformation['id'];
					}
				}
			}

			if(count($countIterationArray)>0)
			{
				$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
				if(mysqli_num_rows($cubeInfo)>0)
				{
					$data['cubeButton']=1; //cube button
				}
				else
				{
					$data['cubeButton']=0;
				}
				
				
			}
			else
			{
				$data['cubeButton']=0;
			}
			

			$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' and  userID ='".$loginUserId."' ");

			if(mysqli_num_rows($likeImage) > 0)
			{
				$data['like']=1;
			}
			else
			{
				$data['like']=0;
			}
			$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$iterationId."'"));


			if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
			{
				$data['adoptChild']=1; // adopt button enable
			}
			else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
			{
				$data['adoptChild']=0; // adopt button disable
			}
			else if($userId != $loginUserId && $imagerow['adopt_photo']==0)
			{
				
				if($cubeCount['storyData']!= '' and  $imagerow['autoapprove'] ==0)
				{
					$data['adoptChild']=3; //show share button + show edit button
				}
				else
				{
					$data['adoptChild']=2;  //show share button +no show edit button
				}
			}
			else
			{
				
				if($imagerow['userID'] == $loginUserId)
				{
					$data['adoptChild']=3; //show share button + show edit button
					
				}
				else
				{
					$data['adoptChild']=2;
					
				}
			}

			if($imagerow['delete_tag'] == 1 )
			{
				$data['deleteTag']=1;
			}
			else
			{
				if($imagerow['adopt_photo'] == 1 )
				{
					if($fetchcreatorUserID['userID'] == $loginUserId)
					{
						$data['deleteTag']=1;
					}
					
				}
				else
				{
					$data['deleteTag']=0;
				}
			}



			$selectParentImageData1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as imagecount FROM `comment_table` where  imageID='".$row['imageID']."'"));
            $data['imageComment']=$selectParentImageData1['imagecount'];
			$selectParentImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
			WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,frame,lat,lng,webUrl,location,addSpecification FROM image_table WHERE imageID='".$row['imageID']."'"));
			$data['url']=($selectParentImageData['url']!='')? $selectParentImageData['url']:'';
			$data['thumbUrl']=($selectParentImageData['thumb_url']!='')? $selectParentImageData['thumb_url']:'';
			$data['frame']=$selectParentImageData['frame'];
			$data['x']=($selectParentImageData['lat'] == NULL ? '' : $selectParentImageData['lat'] );
			$data['y']=($selectParentImageData['lng']  == NULL ?  '' : $selectParentImageData['lng']  );
			$data['type']=$selectParentImageData['type'];
			$data['webUrl']=($selectParentImageData['webUrl'])?$selectParentImageData['webUrl']:'';
			$data['location']=($selectParentImageData['location'])?$selectParentImageData['location']:'';
			$data['addSpecification']=($selectParentImageData['addSpecification'])?$selectParentImageData['addSpecification']:'';



			//----------------------stacklinks array-------------------------------------
			//------------if stack is part of cube then does not use session --------------
			
			$gettingParentType = stacklinkIteration($conn,$breakactivestacklink[1],'type',$r1['imageID'],$imagerow['userID']);
			
			$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$breakactivestacklink[1]."'))");
			$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
			$gettingParentOfParentType = $gettingCubeData['type'];
			if($gettingParentType  == 6 || $gettingParentOfParentType ==6)
			{
				
				$newIterationID=$iterationId;
				$newUserID=$userId;
			}
			else
			{
				
					$WhoStackLinkIterationID=mysqli_query($conn,"SELECT id from whostack_table inner join iteration_table on whostack_table. reuestedIterationID =iteration_table.iterationID   WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");

					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{
						$getSessionIterationIDInfo =0;
						$getIterationIDWhoStackInfos=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',whostackFdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getIterationIDWhoStackInfo=mysqli_num_rows($getIterationIDWhoStackInfos);
					}

					else
					{

						$getIterationIDWhoStackInfo  = 0;
						$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);

					}

					if($getIterationIDWhoStackInfo > 0 and $iterationButton==0)
					{

							$whoStackIterationIDInfo=mysqli_fetch_assoc($getIterationIDWhoStackInfos);

						
							$newIterationID=$whoStackIterationIDInfo['iterationID'];
							$newUserID=$whoStackIterationIDInfo['user_id'];
							

					}
		
					else if($getSessionIterationIDInfo1 > 0 and $iterationButton==0)
					{

						$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);


						$newIterationID=$IterationIDInfo['iterationID'];
						$newUserID=$IterationIDInfo['user_id'];
							


					}
					else
					{

						$newIterationID=$iterationId;
						$newUserID=$userId;
					}
			}
			
		
			
	
			$data['sessionIterationID']=$newIterationID;
			$data['sessionImageID']=$row['imageID'];

			if($imagerow['adopt_photo']==1)
			{
				$data['iterationButton']=0;
			}
			else
			{
				$data['iterationButton']=1;

			}



			$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$newIterationID."'"));
			
			$words = explode(',',$getImageStacklinksInfo['stacklinks']);
			if (!in_array($fetchActiveStackName, $words)) 
			{
				
				$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$iterationId."'"));
				$words = explode(',',$getImageStacklinksInfo['stacklinks']);
				
			  }
			
						
			$stackLinkData=array();
			if (strpos($getImageStacklinksInfo['stacklinks'], 'home') == false)
			{

				
				
				foreach ($words as $key=> $word)
				{
					
					$result = explode('/',$word);
					$getcount=mysqli_fetch_assoc(mysqli_query($conn,"select imageID  from iteration_table where imageID=(SELECT imageID FROM `iteration_table` where  iterationID='".$result[1]."')"));

					$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type FROM image_table WHERE imageID='".$getcount['imageID']."'"));




					if( $stackFetchFromImageTable['type']!=6)
					{
						

						$arr['stacklink']=$word;
						$stackLinkData[]=$arr;
					}
					else
					{
						
						//$fetchActiveStackName = $breakactivestacklink[0].'/home';
						
						$arr['stacklink']=$result[0].'/home';
						$stackLinkData[]=$arr;
						//$arr['stacklink']=$word;
						//$stackLinkData[]=$arr;
					}



					
				}

				foreach($stackLinkData as $fetchStackLink) {
				$ids[] = $fetchStackLink['stacklink'];
				}
				//$stackLinksArr=array_unique($ids);
				$stackLinksArr=$ids;
			}
			else
			{
			
				$arr = array();
				$reverseString =$getImageStacklinksInfo['stacklinks'];
				$words = explode(',',$reverseString);
				foreach ($words as $key=>$word)
				{


					$result = explode('/',$word);

					$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
					WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$result[1]."')"));

					if($stackFetchFromImageTable['type']!=6)
					{


						$arr['stacklink']=$word;
						$stackLinkData[]=$arr;
					}
					else
					{
					/*	$arr['stacklink']=$word;
						$stackLinkData[]=$arr; */
						//$fetchActiveStackName = $breakactivestacklink[0].'/home';
						
						$arr['stacklink']=$result[0].'/home';
						$stackLinkData[]=$arr;
					}


				}



				foreach($stackLinkData as $fetchStackLink) {
				$ids[] = $fetchStackLink['stacklink'];
				}

				//$stackLinksArr=array_unique($ids);
				$stackLinksArr=$ids;

			}

				
			//-----------------store cube session data here --------------------------
			$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

			$createNewDid = $linkingInfo['did'];
			$createNewFdid = $linkingInfo['fdid'];
			$createNewRdid = $linkingInfo['rdid'];
			$createNewWhoStackFdid = $linkingInfo['whostackFdid'];
			$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
			
			if(mysqli_num_rows($cubeInfoData)>0)
			{
				
				$cubefetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM cube_session_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$iterationId."' and user_id ='".$loginUserId."' ");


				
				
				if(mysqli_num_rows($cubefetchIterationIDInfo)<=0)
				{
					$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
					$insertUserTable=mysqli_query($conn,"INSERT INTO cube_session_table(cubeiterationID,cubeimageID,iterationID,user_id,imageID,
					did,fdid,rdid,date,time,datetime) VALUES('".$cubeInfoData['iterationID']."','".$cubeInfoData['imageID']."','".$iterationId."',
					'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')"); 
						
				}
				else
				{
					
					mysqli_query($conn,"update cube_session_table set date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."'  where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$iterationId."' and user_id ='".$loginUserId."'");
					
					
				}
				
				
				//  ----------------End ------------------------
				
				
				
			}	

			if(count($stackLinksArr) >1)
			{

				if(count($stackLinksArr)>=2 and $iterationButton==0)
				{

					$fetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM user_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."' ");


					
					
					$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

					$createNewDid = $linkingInfo['did'];
					$createNewFdid = $linkingInfo['fdid'];
					$createNewRdid = $linkingInfo['rdid'];
					$createNewWhoStackFdid = $linkingInfo['whostackFdid'];

					if(mysqli_num_rows($fetchIterationIDInfo)<=0)
					{
						$insertUserTable=mysqli_query($conn,"INSERT INTO user_table(iterationID,user_id,imageID,
						did,fdid,rdid,whostackFdid,date,time,datetime) VALUES('".$newIterationID."',
						'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','$createNewWhoStackFdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')");

						
					}
					else
					{
						
						mysqli_query($conn,"update user_table set whostackFdid='".$createNewWhoStackFdid."', date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."' , stack_type='0' where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."'");
						
						
					
							
						
					}
					
				

				}





				$WhoStackLinkIterationID=mysqli_query($conn,"SELECT distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name, whostack_table.datetime FROM `iteration_table`  inner join whostack_table on iteration_table.iterationID = whostack_table. reuestedIterationID    WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");


				if(mysqli_num_rows($WhoStackLinkIterationID)>0)
				{

					while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
					{


						$whostackLinksArr[]=$fetchWhoStackLinkIterationID['who_stacklink_name'];

					}
				}
				if(!empty($whostackLinksArr)>0) // remove who stack data here
				{
					$whostackLinksArrValue=array_reverse(array_diff($whostackLinksArr,$stackLinksArr));
				}


				if(!empty($whostackLinksArrValue))
				{

					foreach($whostackLinksArrValue as $stacklinkCount=>$stackminiArr) //whostack stackLink
					{
						$stackArrInfoData=explode('/',$stackminiArr);

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];

						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];

						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}
						$stackArr1[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];


						if($stackArrInfoData[1]=='home')
						{
							
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								$stackedRelated['stackrelateddata']['type']=6;
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
							}
							else
							{
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['profileStory']="0";
								$stackedRelated['stackrelateddata']['cubeID']=0;
							}



							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
							if($stackminiArr==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}


							$stackedRelated['stackrelateddata']['ID']='';
							//$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['parentName']='';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['imageComment']='';
							//$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);

							$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

							

						}
						else
						{

							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
							if($stackminiArr==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}
							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));


							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;

							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat'] );
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
							$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
								$stackedRelated['stackrelateddata']['profileStory']="0";
							}


							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								//image Data


								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}

								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];

							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}

								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

							}
							if($stackDataFetchFromImageTable['type']==4)
							{


								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							}
							if($stackDataFetchFromImageTable['type']==5)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;

								$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							}

							if($stackDataFetchFromImageTable['type']==6)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}

							if($stackDataFetchFromImageTable['type']==7)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}
// echo "<pre>";print_R($stackedRelated['stackrelateddata']);die;
							//$stackedRelated['stackrelateddata']['jyot']=1;
							$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

						}



					}


				}
				/*  if(!empty($stackArr1)){
				
				echo "one";
				echo "<pre>";print_r($stackArr1);
				 }
				 */
				
				$stacklink['stacklinks']=array_reverse($stackArr1);






				$i=0;
				foreach($stackLinksArr as $stacklinkCount=>$stackminiArr) //session stackLink
				{
				
		
				   $mainStackLink = explode(',', trim($imagerow['stacklinks'])); // check session or original stack of that stack.

					$mainStackLink1 = explode(',', trim($getImageStacklinksInfo['stacklinks']));
					$getstacklinkiteration=explode('/',$mainStackLink1[$i]);
			
					$stackArrInfoData=explode('/',$stackminiArr);
					
					
					$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
					AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
					$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
					/**********if child of other user ownername should show**********/
					if($stackArrInfoData[1]!='home')
					{

						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';

					}
					else
					{
						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
					}
					$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
					if (in_array($stackUserInfo['id'], $getBlockUserList))
					{
						$stacked['stackuserdata']['blockUser']=1;
					}
					else
					{

						$stacked['stackuserdata']['blockUser']=0;
					}
					$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];

					if(in_array($stackminiArr, $mainStackLink))
					{
						$mainStackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain original stack related that stack.
					}
					else
					{
						$sessionstackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain all session stacklink.
					}

					if($stackArrInfoData[1]=='home')
					{


						//echo $active_stacklink_name;
						if($mainStackLink1[$i]==$fetchActiveStackName)
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=0;
						}
						if($stackminiArr==$stackLinkType)
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=2;
						}
						$stackedRelated['stackrelateddata']['userID']=($stackUserInfo['id'] == NULL ? '' : $stackUserInfo['id']);
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory,tags,userID FROM cube_table WHERE    iterationID = '".$getstacklinkiteration[1]."' ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							
						
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							
							
							$sessioncubeInfoData = mysqli_query($conn,"select * from cube_session_table where imageID in( SELECT iteration_table.imageID as imageID FROM
							iteration_table where iterationID in (".$cubeInfoData['tags'].")) and user_id='".$loginUserId."' order by datetime desc limit 1  "  );
							if(mysqli_num_rows($sessioncubeInfoData)>0)
							{	
								 $getCubeInfo1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `cube_table` where   iterationID = '".$getsessioncubeInfoData['cubeiterationID']."' and cube_type='0' "));
								if($getCubeInfo1['userID'] == $cubeInfoData['userID'])
								{
									$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								}
								else
								{ 
									$getsessioncubeInfoData=mysqli_fetch_assoc($sessioncubeInfoData);	
									$stackedRelated['stackrelateddata']['typeID']=($getsessioncubeInfoData['cubeimageID'] == NULL ? '' : $getsessioncubeInfoData['cubeimageID']);
									
								}
								
							}
							else
							{
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
							}
							
							
						
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
							
							$stackedRelated['stackrelateddata']['type']=6;
							
							
							$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
							
							
							$getinterationInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID  FROM
							iteration_table where iterationID in (".$cubeInfoData['tags'].") and imageID ='".$imagerow['imageID']."' limit 1 "));
							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$getinterationInfo['iterationID']."')"));
							
							$stackedRelated['stackrelateddata']['stackType']=(stacklinkIteration($conn,$getstacklinkiteration[1],'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']) == NULL ? '' : stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']));
							$stackedRelated['stackrelateddata']['imageID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);
							$stackedRelated['stackrelateddata']['url']=($stackDataFetchFromImageTable['url'] == NULL ? '' :  $stackDataFetchFromImageTable['url']);
							$stackedRelated['stackrelateddata']['iterationID']=($getinterationInfo['iterationID'] == NULL ? $iterationId : $getinterationInfo['iterationID']);
							
						}
						else
						{
							
							$stackedRelated['stackrelateddata']['cubeID']=0;	
							$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['imageID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['stackType']=(stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']) == NULL ? '' : stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']));
							$stackedRelated['stackrelateddata']['type']=(stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']) == NULL ? '' : stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']));
							$stackedRelated['stackrelateddata']['profileStory']="0";
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						}
						
						
						
						$stackedRelated['stackrelateddata']['ID']='';
						
						
						$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,imageID,stacklink_name,autoapprove_userid FROM iteration_table WHERE iterationID='".$stackedRelated['stackrelateddata']['iterationID']."'"));
						$stackedRelated['stackrelateddata']['name']=isset($nameOfStack['stacklink_name'])?$nameOfStack['stacklink_name']:'';
					
						$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$nameOfStack['autoapprove_userid']."')"));
						
						
						$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
						
						if($auto_username['username']!='')
						{
						
							$autoApproval= array();
							$autoApproval['userID']=$auto_username['id'];

							$autoApproval['userName']=$auto_username['username'];
							$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
							$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
							$autoApproval['firstName']=$auto_username['fname'];

							if (in_array($auto_username['id'], $getBlockUserList))
							{
								$autoApproval['blockUser']=1;
							}
							else
							{

								$autoApproval['blockUser']=0;
							}
							$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
							
						}
						else
						{
							$stackedRelated['stackrelateddata']['autoApproval'] = '';
						}
						
						
						
						if($stackedRelated['stackrelateddata']['name'] == '')
						{
							$stackedRelated['stackrelateddata']['name'] =$imagerow['stacklink_name'];
						}
						$stackedRelated['stackrelateddata']['parentName']='';
						
						$stackedRelated['stackrelateddata']['thumbUrl']='';
						$stackedRelated['stackrelateddata']['frame']='';
						$stackedRelated['stackrelateddata']['x']='';
						$stackedRelated['stackrelateddata']['y']='';
						$stackedRelated['stackrelateddata']['imageComment']='';
						//$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						$stackedRelated['stackrelateddata']['oldThreadId']='';
						$stackedRelated['stackrelateddata']['oldIterationID']='';
						$stackedRelated['stackrelateddata']['oldImageID']='';
						$stackedRelated['stackrelateddata']['oldAutorelatedID']='';

		                $stackedRelated['stackrelateddata']['parentType']="";
						
						$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
						$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
						if(in_array($stackminiArr, $mainStackLink))
						{
							$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];	// contain original stack related that stack.
						}
						else
						{
							$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];// contain all session stacklink.
						}



					}
					else
					{


					/* 	if($stackminiArr==$fetchActiveStackName)
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=1; //active stack
						}
						else
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=0;
						} */
						$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

						$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
					
						if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
						{
							$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
							if($stackDataFetchFromImageTableData['type'] == 1)
							{
								$stackedRelated['stackrelateddata']['parentType']='';
							}
							else
							{
								$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
							}
						}
						else
						{
							$stackedRelated['stackrelateddata']['parentType']="";
							
						}
						
						
						$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT autoapprove_userid,userID,imageID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
						
						
						$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$nameOfStack['autoapprove_userid']."')"));
						
						
						$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
						
						if($auto_username['username']!='')
						{
						
							$autoApproval= array();
							$autoApproval['userID']=$auto_username['id'];

							$autoApproval['userName']=$auto_username['username'];
							$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
							$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
							$autoApproval['firstName']=$auto_username['fname'];

							if (in_array($auto_username['id'], $getBlockUserList))
							{
								$autoApproval['blockUser']=1;
							}
							else
							{

								$autoApproval['blockUser']=0;
							}
							$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
							
						}
						else
						{
							$stackedRelated['stackrelateddata']['autoApproval'] = '';
						}
						
							
						$stackedRelated['stackrelateddata']['userID']=($nameOfStack['userID'] == NULL ? '' : $nameOfStack['userID']);
						$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);
						$stackedRelated['stackrelateddata']['ownerName']=(getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn) == NULL ? '' : getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn));
						$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
						$stackedRelated['stackrelateddata']['frame']=($stackDataFetchFromImageTable['frame'] == NULL ? '' : $stackDataFetchFromImageTable['frame']);
						$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat'] );
						$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
						$stackedRelated['stackrelateddata']['imageComment']=($stackDataFetchFromImageTable['image_comments'] == NULL ? '' : $stackDataFetchFromImageTable['image_comments']);
						$stackedRelated['stackrelateddata']['type']=($stackDataFetchFromImageTable['type'] == NULL ? '' : $stackDataFetchFromImageTable['type']);
						$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
							$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
						}
						else
						{
							$stackedRelated['stackrelateddata']['cubeID']=0;
							$stackedRelated['stackrelateddata']['profileStory']="0";
						}
						$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
						$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];

						if(in_array($fetchActiveStackName,$stackLinksArr ))
						{


							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
						}
						else
						{

							if($stackDataFetchFromImageTable['imageID'] == $r1['imageID'])
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}

						}
						if($stackminiArr==$stackLinkType)
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=2;
						}

						if($stackDataFetchFromImageTable['type']==2)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
							//image Data


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


							$name = explode('_',$nameOfStack['stacklink_name']);
							if($name[1]=='profileStory')
							{
								$stackedRelated['stackrelateddata']['name']=$name[0];
							}
							else
							{
								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							}

						//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							if($stackImageTitle['imageID']==$oldImageID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}

						if($stackDataFetchFromImageTable['type']==7)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
							//image Data


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


							$name = explode('_',$nameOfStack['stacklink_name']);
							if($name[1]=='profileStory')
							{
								$stackedRelated['stackrelateddata']['name']=$name[0];
							}
							else
							{
								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							}

						//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							if($stackImageTitle['imageID']==$oldImageID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}
						if($stackDataFetchFromImageTable['type']==3)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							$name = explode('_',$nameOfStack['stacklink_name']);
							if($name[1]=='profileStory')
							{
								$stackedRelated['stackrelateddata']['name']=$name[0];
							}
							else
							{
								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							}
							//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}

						}
						if($stackDataFetchFromImageTable['type']==4)
						{


							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$name = explode('_',$nameOfStack['stacklink_name']);
								if($name[1]=='profileStory')
								{
									$stackedRelated['stackrelateddata']['name']=$name[0];
								}
								else
								{
									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								}
							//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}
						if($stackDataFetchFromImageTable['type']==5)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;

						        $name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
							//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}

						if($stackDataFetchFromImageTable['type']==6)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']='';
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}

						//$stackedRelated['stackrelateddata']['jyot']=2;
						if(in_array($stackminiArr, $mainStackLink))
						{
							$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{
							$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}


					}
					$i++;
				}
				if(!empty($stackArr1))  //means whostack data exist.
				{
					if(!empty($sessionstackArr))
					{
				
						if(!empty($mainStackArr))
						{
							
							//whostack , session, main stacklink exist
							$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr,$mainStackArr)); // insert to reverse order
						}
						else
						{
						
							$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr));
						}
					}
					else
					{
						//whostack, main stacklink exist but session data does not exist.
						$data['stacklinks']=array_reverse(array_merge($stackArr1,$mainStackArr));
					}

				}
				else{   //means whostack data does not exist.

					if(!empty($sessionstackArr))
					{
						//sesion data exist.
						if(!empty($mainStackArr))
						{
							$data['stacklinks']=array_reverse(array_merge($sessionstackArr,$mainStackArr));
						}
						else
						{
							$data['stacklinks']=array_reverse(array_merge($sessionstackArr));
						}


					}
					else
					{
						//sesion data does not exist.
						$data['stacklinks']=array_reverse(array_merge($mainStackArr));
					}

				}



			}
			else
			{
				$allWhoStackIterationID=0;
				$getAllWhoStackLink=mysqli_query($conn,"SELECT reuestedIterationID FROM whostack_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$imagerow['iterationID']."'  AND  requestStatus =2 ");
				$commaVariable='';
				if(mysqli_num_rows($getAllWhoStackLink)>0)
				{
					while($allWhoStackLink=mysqli_fetch_assoc($getAllWhoStackLink))
					{
						$allWhoStackIterationID.=$commaVariable.$allWhoStackLink['reuestedIterationID'];
						$commaVariable=',';
					}
				}


				$WhoStackLinkIterationID=mysqli_query($conn,"select * from (SELECT  distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name , iterationID FROM `iteration_table` where  iterationID in ($allWhoStackIterationID)) as stack_link_table where who_stacklink_name!='".$stackLinksArr[0]."'  ORDER BY FIELD(iterationID,$allWhoStackIterationID) desc ");

				if(mysqli_num_rows($WhoStackLinkIterationID)>0)
				{
					while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
					{

						$stackArrInfoData=explode('/',$fetchWhoStackLinkIterationID['who_stacklink_name']);

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}

						$stackArr['stackUserInfo']=$stacked['stackuserdata'];

						if($stackArrInfoData[1]=='home')
						{
							if($fetchWhoStackLinkIterationID['who_stacklink_name']==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}

							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								$stackedRelated['stackrelateddata']['type']=6;
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
								$stackedRelated['stackrelateddata']['profileStory']="0";
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['type']=(stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']) == NULL ? '' : stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']));
							}

							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];

							$stackedRelated['stackrelateddata']['activeStacklink']=0;
							$stackedRelated['stackrelateddata']['ID']='';
							//$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['parentName']='';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['imageComment']='';

							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							//$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);

							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{
							if($fetchWhoStackLinkIterationID['who_stacklink_name']==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}
							$stackedRelated['stackrelateddata']['activeStacklink']=0;
							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
							$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,imageID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
							$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat']);
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' : $stackDataFetchFromTagTable['lng']);
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=($stackDataFetchFromImageTable['type'] == NULL ? '' : $stackDataFetchFromImageTable['type']);
							$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
								$stackedRelated['stackrelateddata']['profileStory']="0";
							}
							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];


									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}



								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];

							}
							if($stackDataFetchFromImageTable['type']==4)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							}
							if($stackDataFetchFromImageTable['type']==5)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							}
							if($stackDataFetchFromImageTable['type']==6)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}
							if($stackDataFetchFromImageTable['type']==7)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}


							//$stackedRelated['stackrelateddata']['jyot']=3;
							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

						}

						$data['stacklinks'][]=array_reverse($stackArr);


					}


				}


				$stackArrInfoData=explode('/',$stackLinksArr[0]);
				

				$getstacklinkiteration=explode('/',$imagerow['stacklinks']);


				$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
				AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

				$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
					/**********if child of other ownername should show**********/
				

			
				$stackOwnerInfo1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,CASE WHEN u.profileimg IS NULL OR u.profileimg = '' THEN '' 
				WHEN u.profileimg LIKE 'albumImages/%'  THEN concat( '$serverurl', u.profileimg ) ELSE
				u.profileimg
				END
				AS profileimg,img.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
				WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,img.imageID FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$iterationId."'"));
				
				
				if($stackArrInfoData[1]!='home'){
					
					
					$stackOwnerInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,CASE WHEN u.profileimg IS NULL OR u.profileimg = '' THEN '' 
					WHEN u.profileimg LIKE 'albumImages/%'  THEN concat( '$serverurl', u.profileimg ) ELSE
					u.profileimg
					END
					AS profileimg,img.type,img.url,img.imageID FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$getstacklinkiteration[1]."'"));

					$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
					$stacked['stackuserdata']['profileImg']=($stackOwnerInfo['profileimg']!='')?$stackOwnerInfo['profileimg']:'';

				}
				else{

					$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
					$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
				}
				$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
				$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
				if (in_array($stackUserInfo['id'], $getBlockUserList))
				{
					$stacked['stackuserdata']['blockUser']=1;
				}
				else
				{

					$stacked['stackuserdata']['blockUser']=0;
				}
				$stackArr['stackUserInfo']=$stacked['stackuserdata'];

				if($stackArrInfoData[1]=='home')
				{
			
					$getstackArrInfoData=explode('/',$imagerow['stacklinks']);
				

							if($stackLinksArr[0]==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}

							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE iterationID='".$getstackArrInfoData[1]."'  ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								$stackedRelated['stackrelateddata']['type']=6;
								
							}
							else
							{
								
								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))"));
									if($stackDataFetchFromImageTable['type']==6)
									{
											
										   $stackedRelated['stackrelateddata']['type']=6;
										   $stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);
									}
									else
									{
										$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$iterationId."')"));
										$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
										$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

								}
							}

					$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
					$stackedRelated['stackrelateddata']['activeStacklink']=1;
					$stackedRelated['stackrelateddata']['ID']='';
				    $stackedRelated['stackrelateddata']['imageID']=$stackOwnerInfo1['imageID'];
					$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
					$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
					$stackedRelated['stackrelateddata']['parentName']='';
					$stackedRelated['stackrelateddata']['stackType']=(stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']) == NULL ? '' : stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']));
					$stackedRelated['stackrelateddata']['url']=($stackOwnerInfo1['url'] == NULL ? '' : $stackOwnerInfo1['url']);
					$stackedRelated['stackrelateddata']['thumbUrl']='';
					$stackedRelated['stackrelateddata']['frame']='';
					$stackedRelated['stackrelateddata']['x']='';
					$stackedRelated['stackrelateddata']['y']='';
					$stackedRelated['stackrelateddata']['imageComment']='';
					$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
					$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
					$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT autoapprove_userid,userID,imageID,stacklink_name FROM iteration_table WHERE iterationID='".$iterationId."'"));
					
					
					$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$imagerow['autoapprove_userid']."')"));
					$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
						
					if($auto_username['username']!='')
					{
					
						$autoApproval= array();
						$autoApproval['userID']=$auto_username['id'];

						$autoApproval['userName']=$auto_username['username'];
						$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
						$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
						$autoApproval['firstName']=$auto_username['fname'];

						if (in_array($auto_username['id'], $getBlockUserList))
						{
							$autoApproval['blockUser']=1;
						}
						else
						{

							$autoApproval['blockUser']=0;
						}
						$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
						
					}
					else
					{
						$stackedRelated['stackrelateddata']['autoApproval'] = '';
					}
						
						
					$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
					if(mysqli_num_rows($cubeInfoData)>0)
					{
						$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
						$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
						$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
					}
					else
					{
						$stackedRelated['stackrelateddata']['cubeID']=0;
						$stackedRelated['stackrelateddata']['profileStory']="0";
					}

					$stackedRelated['stackrelateddata']['parentType']="";
					$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
				}
				else
				{

					if($stackLinksArr[0]==$stackLinkType)
					{
						$stackedRelated['stackrelateddata']['originateStackLink']=1;
					}
					else
					{
						$stackedRelated['stackrelateddata']['originateStackLink']=2;
					}
					$stackedRelated['stackrelateddata']['activeStacklink']=1;
					
				
					$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
						
						
						
					$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
					
					if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
					{
						$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
						if($stackDataFetchFromImageTableData['type'] == 1)
						{
							$stackedRelated['stackrelateddata']['parentType']='';
						}
						else
						{
							$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
						}
						
					}
					else
					{
						$stackedRelated['stackrelateddata']['parentType']="";
						
					}
						
						
					$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng,stacklink_name FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
			
					$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT autoapprove_userid,userID,imageID,stacklink_name FROM iteration_table WHERE iterationID='".$iterationId."'"));
					
					
					$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$imagerow['autoapprove_userid']."')"));
					$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
						
					if($auto_username['username']!='')
					{
					
						$autoApproval= array();
						$autoApproval['userID']=$auto_username['id'];

						$autoApproval['userName']=$auto_username['username'];
						$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
						$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
						$autoApproval['firstName']=$auto_username['fname'];

						if (in_array($auto_username['id'], $getBlockUserList))
						{
							$autoApproval['blockUser']=1;
						}
						else
						{

							$autoApproval['blockUser']=0;
						}
						$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
						
					}
					else
					{
						$stackedRelated['stackrelateddata']['autoApproval'] = '';
					}
						
							
					$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username[0])?$auto_username[0]:'';
					$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
					$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
					$stackedRelated['stackrelateddata']['ownerName']=$stackUserInfo['username'];
					$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
					$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
					$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' :  $stackDataFetchFromTagTable['lat']);
					$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' :  $stackDataFetchFromTagTable['lng']);
					$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
					$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
					$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
					$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
					$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
					$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
					if(mysqli_num_rows($cubeInfoData)>0)
					{
						$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
						$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
						$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
					}
					else
					{
						$stackedRelated['stackrelateddata']['cubeID']=0;
						$stackedRelated['stackrelateddata']['profileStory']="0";

					}

					if($stackDataFetchFromImageTable['type']==2)
					{
						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));




						$name = explode('_',$nameOfStack['stacklink_name']);
						if($name[1]=='profileStory')
						{
							$stackedRelated['stackrelateddata']['name']=$name[0];
						}
						else
						{
							$stackedRelated['stackrelateddata']['name']=$stackDataFetchFromTagTable['stacklink_name'];
						}



						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
					}
					if($stackDataFetchFromImageTable['type']==7)
					{
						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));




									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}



						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
					}
					if($stackDataFetchFromImageTable['type']==3)
					{
						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
					//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
											$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}

					}
					if($stackDataFetchFromImageTable['type']==4)
					{

						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

						//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
												$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$stackDataFetchFromTagTable['stacklink_name'];
									}
						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
					}
					if($stackDataFetchFromImageTable['type']==5)
					{

						$stackedRelated['stackrelateddata']['url']='';
						$stackedRelated['stackrelateddata']['thumbUrl']='';

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


						//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
												$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$stackDataFetchFromTagTable['stacklink_name'];
									}
						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
					}
					if($stackDataFetchFromImageTable['type']==6)
					{

						$stackedRelated['stackrelateddata']['url']='';
						$stackedRelated['stackrelateddata']['thumbUrl']='';

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));


						$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						$stackedRelated['stackrelateddata']['ID']=$nameOfStack['imageID'];
					}

					//$stackedRelated['stackrelateddata']['jyot']=4;

					$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

				}

				$data['stacklinks'][]=array_reverse($stackArr);
			}

			$data['userinfo']['userName']=$newUsername[0];
			$data['userinfo']['userID']=$newUserID;

/*
			if($newUsername[1]!='')
			{
				$data['userinfo']['profileImg']=$newUsername[1];
			}
			else
			{
				$data['userinfo']['profileImg']='';
			}

			if($newUsername[2]!='')
			{
				$data['userinfo']['coverImage']=$newUsername[2];
			}
			else
			{
				$data['userinfo']['coverImage']='';
			}
			$data['userinfo']['firstName']=$newUsername[3]; */



/*--------------------------  SWAP SIBLING child -----------------------------------*/


			//new autorelated Fetch by jyoti
			//Add the auto related Linking code here.

		 	if( $relatedThreadID=='' && $autorelatedID==''){



				
				$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());

				// $cubeInfoData=mysqli_num_rows($cubeInfoData);
				// if($cubeInfoData==1)
				// {
					// $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."'  ORDER BY viewDate DESC limit 1");
				// }
				// else
				// {
					$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' ORDER BY viewDate DESC limit 1");
				
				//}
				
				
					if(mysqli_num_rows($autorelated_session)>0){

						$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
						$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
						$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
						$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
						$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
					}
					else
					{



						 $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID,fdid,did FROM sub_iteration_table WHERE imgID='".$imageId."'  AND did=1 "));



							$autorelated_session1=mysqli_query($conn,"SELECT * FROM `new_auto_related` where   FIND_IN_SET('".$getSubIterationImageInfo['iterationID']."',autorelated)");
							if(mysqli_num_rows($autorelated_session1)>0){

								$autorelated_session2=mysqli_query($conn,"SELECT * FROM `new_auto_related` where iterationID ='".$iterationId."' ");
								if(mysqli_num_rows($autorelated_session2)>0){


								$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
								$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
								$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
								$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
								$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
								}
							}

						}
					}




			}
			if( $relatedThreadID=='' && $autorelatedID=='')
			{

	
		//new autorelated by jyoti

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE new_auto_related.iterationID ='".$iterationId."' and new_auto_related.imageID ='".$imagerow['imageID']."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){


						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
				
						
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						
						$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						
						
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$parentChild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$parentChild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoDatas)>0)
							{
								$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
								$parentChild ['cubeID']=$cubeInfoDatas['id'];

							}
							else
							{
								$parentChild ['cubeID']=0;
							}
						}
						$data['parentChild']=$parentChild;	
						 
						$data['countInfo']=count($arrayAuto);
						array_unshift($arrayAuto,$iterationId);


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$fetchNewAutoRelated['iterationID'];
						$currentStack['imageID']=$fetchNewAutoRelated['imageID'];
						$currentStack['forwordrelatedID']=1;
						$currentStack['backwordrelatedID']='';
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';
						$data['CurrentStack']=$currentStack;

						$rightchild['iterationID']=$arrayAuto[1];
						$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[1]."'"));
						$rightchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
						$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
						$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
						if($getStackImageID['type'] == 7)
						{
							$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
						}
						else
						{
							
							$rightchild['videoUrl']='';
							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
						}
						$rightchild['activeIterationID']=$newIterationID;
						$rightchild['userID']=$getStackImageID['userID'];
						$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
						$rightchild['title']=$getStackImageID['stacklink_name'];
						$rightchild['threadID']=$fetchNewAutoRelated['threadID'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
							
						
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}
						}
						else
						{
							
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}
						}

						$data['autorealtedParentStackName']=$imagerow['stacklink_name'];
						if($getStackImageID['type'] == 1)
						{
							$data['rightChild']=array();
						}
						else
						{
							$data['rightChild']=$rightchild;
						}
						
						$data['leftChild']=array();
						$data['optionalChild']=array();

						//iteracation check in database

						$rId = $rightchild['iterationID'];
						$getrightchild =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $rId"));
						if(empty($getrightchild)){

							$data['rightChild']="";

						}

					}
				}

				else
				{

					$data['optionalChild']=array();
					$data['CurrentStack']=array();
					$data['rightChild']=array();
					$data['leftChild']=array();
				}
				/****Delete all back auto_related ****/
				$fetchbackwardDel =mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'");
				/**** end Delete all back auto_related ****/
			}
			else if($optionalOf=='' && $optionalIndex=='' && $relatedThreadID!='')
			{
			
			
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){
						$arrayIndex=$autorelatedID;

						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
						
						
						
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
					
				
			
					
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
							$data['parentImageUrl'] = $getImageDetail['url'];
							$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
							$parentChild ['type'] = $getImageDetail['type'];
						}
						else
						{
							$parentChild ['iterationID'] = '';
							$parentChild ['threadID']='';
							$data['parentImageUrl'] = '';
							$data['parentImageThumbUrl'] = '';
							$parentChild ['type'] = '';
						}
						
						
						//$data['parentImageUrl'] = $getImageDetail['url'];
						
						//$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$parentChild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$parentChild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoDatas)>0)
							{
								$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
								$parentChild ['cubeID']=$cubeInfoDatas['id'];

							}
							else
							{
								$parentChild ['cubeID']=0;
							}
						}
						$data['parentChild']=$parentChild;	
					
						if($arrayIndex == 0)
						{
							
							$data['countInfo']=count($arrayAuto);
							

						}
						else{
							$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNewAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';

						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							$lIndex="";
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex="";
							$lIndex=$leftIndex;
						}
						else{ //if current is neigther last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}
						if($arrayAuto[$lIndex] == $iterationId)
						{
							array_unshift($arrayAuto,$iterationId);
							$indexCount=count($arrayAuto);
							$rIndex=$arrayIndex+1;
							$lIndex=$arrayIndex-1;
							
						}
						

						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';


						//Right child Start
						if($rIndex !== ""){
							$rightchild['iterationID']=$arrayAuto[$rIndex];

							if($rightchild['iterationID'] == $iterationId ){
								$rightchild = "";

							}else{



							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));


							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));

							if($getStackImageID['type'] == 7)
							{
								$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
							}
							else
							{
								$rightchild['videoUrl']='';
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							}
							$rightchild['activeIterationID']=$newIterationID;
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							//$rightchild['userID']=$userId;
							$rightchild['userID']=$getStackImageID['userID'];
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
							$gettingParentOfParentType = $gettingCubeData['type'];
							if($gettingParentOfParentType  ==6)
							{
							
								
								$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
								$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
					
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								    $rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}
							else
							{
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}

							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];

							//Right child End
						 }

						}else{ $rightchild = ""; }

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;

							if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						if($lIndex !==""){
						//Left Child start
						$leftchild['iterationID']=$arrayAuto[$lIndex];
						
						
						
						$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where iterationID ='".$arrayAuto[$lIndex]."'"));

						$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						$leftchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
						$leftchild['activeIterationID']=$newIterationID;
						$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
						$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
						if($getStackImageID1['type'] == 7)
						{
							$leftchild['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							$leftchild['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
						}
						else
						{
							$leftchild['videoUrl']='';
							$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
						}
					
						//$leftchild['userID']=$userId;
						$leftchild['userID']=$getStackImageID1['userID'];
						$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
						$leftchild['title']=$getStackImageID1['stacklink_name'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$arrayAuto[$lIndex]."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$leftchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$leftchild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[$lIndex]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$leftchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$leftchild['cubeID']=0;
							}
						}

						$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE threadID ='".$relatedThreadID."' and userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){

							$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

							if($fetchNewAutoRelated['threadID']!=''){
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
							}
							else
							{
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$lIndex;
							}
						}
						else
						{
							$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
							$leftchild['autoRelatedID']=$lIndex;
						}
						//$leftchild['threadID']=$relatedThreadID;
						//$leftchild['autoRelatedID']=$lIndex;
									//Left Child End
						$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
						if($data['autorealtedParentStackName']){
							$parent_name = $data['autorealtedParentStackName'];
						}else{
							$parent_name = '';
						}
						//Left Child End

						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];

							if($iteration_count==0){

									$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
									VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
								}
						}


						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));


						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						if($fetchbackwardleft['type'] == 7)
						{
							$backRelated['videoUrl']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
							$backRelated['url']=($fetchbackwardleft['thumb_url']!='')? $fetchbackwardleft['thumb_url']:'';
						}
						else
						{
							$backRelated['videoUrl']='';
							$backRelated['url']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
						}
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iteration_id']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}

						}

					} else { $leftchild = ""; }

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
							if($getStackImageID['type'] == 1)
							{
								$data['rightChild']='';
							}
							else
							{
								$data['rightChild']=$rightchild;
							}
  							
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						//$data['leftChild']=$leftchild;

						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}
						if($backRelated['iterationID']  == $arrayAuto[$lIndex]){
							$data['backRelated']=$backRelated;
						}else{
							$data['backRelated']="";
							/****Delete all back auto_related ****/
							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'"));
							/**** end Delete all back auto_related ****/


						}

						//Optional child autorelated start

						//500 entries should be maintained only by jyoti

						$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
						if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){
							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and   order by viewDate desc limit 1");

							$result = mysqli_fetch_assoc($autorelated_session);

							if(mysqli_num_rows($autorelated_session)<1){
									$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());
							}
							elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
							else
							{
								$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
						}
						else{

							$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
							if($autorelated_session_delete){
								$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());

							}
						}

						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								//echo 'ffffffffffffff'.$optionalCount=count($arrayOptionalAuto);
								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									if($getStackImageID['type'] == 7)
									{
										$optionalchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
									}
									else
									{
										$optionalchild['videoUrl']='';
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									}
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									
									$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
									$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
									$gettingParentOfParentType = $gettingCubeData['type'];
									if($gettingParentOfParentType  ==6)
									{
									
										
										$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
										$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
							
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									else
									{
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}



								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								//$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								if($getStackImageID['type'] == 7)
								{
									$rightOptional['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
								}
								else
								{
									$rightOptional['videoUrl']='';
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								}
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								//$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								if($getStackImageID1['type'] == 7)
								{
									$leftOptional['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
								}
								else
								{
									$leftOptional['videoUrl']='';
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								}
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								$data['leftOptional']="";

								//Check iterationID exists in database
								$opid = $optionalchild['iterationID'];
								$ropid = $rightOptional['iterationID'];

								$queryGetitreaction =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $opid"));
								if(empty($queryGetitreaction)){
									$data['optionalChild']="";

								}
								$queryGetrightoption =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $ropid"));
								if(empty($queryGetitreaction)){
									$data['rightOptional']="";

								}


							}
						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";
						}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";
						}
				}
				//new autorelated by jyoti end

			}
			else
			{


				//new autorelated by jyoti
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");
				if($optionalOf==$iterationId){


					$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");

			 		$arrayIndex=$optionalIndex-1;

			 	}
			 	else{
			 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
			 		$arrayIndex=$autorelatedID;

			 	}
				if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

					$fetchNormalAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
					if($fetchNormalAutoRelated['autorelated']!=''){

						$arrayAuto=explode(',', $fetchNormalAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNormalAutoRelated['iterationID']."'"));
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						}
						//$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$parentChild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$parentChild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoDatas)>0)
							{
								$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
								$parentChild ['cubeID']=$cubeInfoDatas['id'];

							}
							else
							{
								$parentChild ['cubeID']=0;
							}
						}
						$data['parentChild']=$parentChild;	
						
                       if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);

						}
						else{
								$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNormalAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						if($arrayIndex<0){
							$arrayIndex=$indexCount-1;
						}
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';
						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							//$lIndex=$indexCount-1;
							$lIndex='';
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex='';
							//$rIndex=0;
							$lIndex=$leftIndex;
						}
						else{ //if current is neigth last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}



						$currentStack['threadID']=$relatedThreadID;
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']=$optionalIndex;
						$currentStack['optionalOf']=$optionalOf;

						if($rIndex!==""){
							//Right child Start
							$rightchild['iterationID']=$arrayAuto[$rIndex];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));
							$rightchild['activeIterationID']=$newIterationID;
						//	$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							//$rightchild['userID']=$userId;
							$rightchild['userID']=$getStackImageID['userID'];
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							if($getStackImageID['type'] == 7)
							{
								$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
							}
							else
							{
								$rightchild['videoUrl']='';
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							
							$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
							$gettingParentOfParentType = $gettingCubeData['type'];
							if($gettingParentOfParentType  ==6)
							{
							
								
								$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
								$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
					
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}
							else
							{
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}
							//Right child End
						}else{
							$rightchild = "";
						}

							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;



						if($lIndex!==""){
							//Left Child start
							$leftchild['iterationID']=$arrayAuto[$lIndex];
							$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

							//$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							if($getStackImageID1['type'] == 7)
							{
								$leftchild['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftchild['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
							}
							else
							{
								$leftchild['videoUrl']='';
								$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							}
							$leftchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
							$leftchild['activeIterationID']=$newIterationID;
							$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
							$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
							//$leftchild['userID']=$userId;
							$leftchild['userID']=$getStackImageID1['userID'];
							$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
							$leftchild['title']=$getStackImageID1['stacklink_name'];
							
							$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
							$gettingParentOfParentType = $gettingCubeData['type'];
							if($gettingParentOfParentType  ==6)
							{
							
								
								$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$arrayAuto[$lIndex]."'))");
								$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
					
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftchild['cubeID']=0;
								}
							}
							else
							{
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$leftchild['iterationID']."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftchild['cubeID']=0;
								}
							}
							/* $leftchild['threadID']=$relatedThreadID;
							$leftchild['autoRelatedID']=$lIndex; */

							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
							if(mysqli_num_rows($autorelated_session)>0){

								$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

								if($fetchNewAutoRelated['threadID']!=''){
									$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								    $leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
								}
								else
								{
									$leftchild['threadID']=$relatedThreadID;
									$leftchild['autoRelatedID']=$lIndex;
								}
							}
							else
							{
								$leftchild['threadID']=$relatedThreadID;
								$leftchild['autoRelatedID']=$lIndex;
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							if($data['autorealtedParentStackName']){
								$parent_name = $data['autorealtedParentStackName'];
							}else{
								$parent_name = '';

							}
							//Left Child End
						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];


							if($iteration_count == 0){
								// echo "INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								// VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')";die;

								$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
							}
						}

						if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));

							$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						//$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						if($backRelated['type'] == 7)
						{
							$backRelated['videoUrl']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
							$backRelated['url']=($fetchbackwardleft['thumb_url']!='')? $fetchbackwardleft['thumb_url']:'';
						}
						else
						{
							$backRelated['videoUrl']='';
							$backRelated['url']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
						}
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}
						}

						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}

						}else{
							$leftchild = "";
						}

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						// $data['rightChild']=$rightchild;
						// $data['leftChild']=$leftchild;
						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}



						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){
							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);
							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								$optionalCount=count($arrayOptionalAuto);


								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									//$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									if($getStackImageID['type'] == 7)
									{
										$optionalchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
									}
									else
									{
										$optionalchild['videoUrl']='';
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									}
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									
									$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
									$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
									$gettingParentOfParentType = $gettingCubeData['type'];
									if($gettingParentOfParentType  ==6)
									{
									
										
										$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
										$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
							
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									else
									{
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}


								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								//$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								if($getStackImageID['type'] == 7)
								{
									$rightOptional['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
								}
								else
								{
									$rightOptional['videoUrl']='';
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								}
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$rightOptional['threadID']=$arrayOptionalAuto[1];
										
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								//$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								if($getStackImageID1['type'] == 7)
								{
									$leftOptional['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
								}
								else
								{
									$leftOptional['videoUrl']='';
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								}
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								if($optionalOf==''){
									$data['leftOptional']=$leftOptional;
								}else{
									$data['leftOptional']="";
								}

								$optionalSession=1;
								$optionalOfSession=$optionalOf;
								$optionalIndexSession=$optionalIndex;
								$optionalRightIndex=1;
								$optionalLeftIndex=$optionalCount-1;
								$optionalRightID=$arrayOptionalAuto[1];
								$optionalLeftID=$arrayOptionalAuto[$optionalCount-1];

							}

						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";

								$optionalSession=0;
								$optionalOfSession='';
								$optionalIndexSession='';
								$optionalRightIndex='';
								$optionalLeftIndex='';
								$optionalRightID='';
								$optionalLeftID='';
						}
							//500 entries should be maintained only by jyoti
								$optionalSession = isset($optionalSession)?$optionalSession:0;
								$optionalOfSession = isset($optionalOfSession)?$optionalOfSession:0;
								$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
								if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){

									$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");
									$result = mysqli_fetch_assoc($autorelated_session);

									if(mysqli_num_rows($autorelated_session)<1){
											$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')");


									}
									//$optionalOf=='' && $optionalIndex
									elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
									else
									{
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
								}
								else{

									$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
									if($autorelated_session_delete){
										$autorelated_session==mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID =='".$relatedThreadID."' order by viewDate desc limit 1");
											$result = mysqli_fetch_assoc($autorelated_session);
											if(mysqli_num_rows($autorelated_session)<1){
												$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
												VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());
											}
											elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
											}
									}

								}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";

						}
				}
				//new autorelated by jyoti end

			}


			$pdata['parent']=$data;

			//fetch child here

// echo "SELECT iteration_table.*,tag_table.username,tag_table.lat,tag_table.lng,tag_table.frame FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.stack_visible=0 AND iteration_table.imageID not in ($allBlockImageID) and iteration_table.iterationID  IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE  iteration_table.verID='".$imagerow['verID']."' AND tag_table.linked_iteration='".$iterationId."' AND tag_table.lat!='empty' AND tag_table.lng!='empty' )";die;


			$selectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.username,tag_table.lat,tag_table.lng,tag_table.frame FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.stack_visible=0 AND iteration_table.imageID not in ($allBlockImageID) and iteration_table.iterationID  IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE  iteration_table.verID='".$imagerow['verID']."' AND tag_table.linked_iteration='".$iterationId."' AND tag_table.lat!='empty' AND tag_table.lng!='empty' )");
			if(mysqli_num_rows($selectChild) > 0)
			{


				while($childImageRow=mysqli_fetch_assoc($selectChild))
				{
					// echo "<pre>";print_r($childImageRow);die;
					$data1['userID']=$childImageRow['username'];
					//$data1['name']=$childImageRow['stacklink_name'];
					$data1['userName']=$newUsername[0];
					$data1['title']=$childImageRow['stacklink_name'];
					$data1['iterationID']=$childImageRow['iterationID'];
					$data1['imageID']=$childImageRow['imageID'];
					$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
					$data1['creatorUserID']=$childImageRow['userID'];
				

					if($childImageRow['adopt_photo']==0)
					{

						$data1['adoptChild']=0;
					}
					else
					{

						$data1['adoptChild']=1;
					}

					$adoptPhotoType=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where iterationID='".$imagerow['iterationID']."' and adopt_iterationID='".$childImageRow['iterationID']."'"));

					$data1['adoptPhoto']=isset($adoptPhotoType['type'])?$adoptPhotoType['type']:'';




					$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,tb_user.username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,tb_user.id FROM image_table INNER JOIN  tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$childImageRow['imageID']."'"));

					$data1['typeID']=$childImageRow['imageID'];

					$data1['type']=$selectChildImageData['type'];
					if($selectChildImageData['type']==4)
					{
						$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
						$data1['ID']=$selectGrid[0];

					}
					if($selectChildImageData['type']==2)
					{
						$data1['ID']=$selectChildImageData['imageID'];
					}
					if($selectChildImageData['type']==5)
					{
						$data1['ID']=$selectChildImageData['imageID'];
					}
					if($selectChildImageData['type']==3)
					{
						$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
						$data1['ID']=$selectMap[0];

					}


					$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$childImageRow['imageID']."'  AND iterationID='".$childImageRow['iterationID']."' "));




					$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

					$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);


					if($getSessionIterationIDInfo1 > 0 )
					{

						$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);

							
						$data1['sessionIterationID']=$IterationIDInfo['iterationID'];
						$data1['sessionImageID']=$childImageRow['imageID'];



					}
					else
					{

						$data1['sessionIterationID']=$childImageRow['iterationID'];
						$data1['sessionImageID']=$childImageRow['imageID'];
					}


					$getIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id,stack_type FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");


					if(mysqli_num_rows($getIterationIDInfo)>0 )
					{
						$IterationIDInfo=mysqli_fetch_assoc($getIterationIDInfo);

						if($IterationIDInfo['stackype']=='1')
						{

							$data1['apiUse']=1; //related

						}
						else
						{
							$data1['apiUse']=0; //normal
						}

					}
					else
					{
						$data1['apiUse']=0; //normal

					}
				
					/* if($getSubIterationImageInfo['did'] == 1 and  $childImageRow['userID'] == $loginUserId )
					{
						$data1['deleteTag']=1;
					}
					else
					{
						$data1['deleteTag']=0;
					} */
					/* if($childImageRow['delete_tag'] == 1 )
					{
						$data1['deleteTag']=1;
					}
					else
					{
						$data1['deleteTag']=0;
					}
					 */
					if($childImageRow['delete_tag'] == 1 )
					{
						$data1['deleteTag']=1;
					}
					else
					{
						if($childImageRow['adopt_photo'] == 1 )
						{
							if($childImageRow['username'] == $loginUserId)
							{
								$data1['deleteTag']=1;
							}
							
						}
						else
						{
							$data1['deleteTag']=0;
						}
					}
	

					$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
					$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';

					$data1['frame']=$childImageRow['frame'];
					$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
					$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);

					$data1['userinfo']['userName']=$selectChildImageData['username'];
					$data1['userinfo']['userID']=$selectChildImageData['id'];
					if($selectChildImageData['profileimg']!='')
					{
						$data1['userinfo']['profileImg']=$selectChildImageData['profileimg'];
					}
					else
					{
						$data1['userinfo']['profileImg']='';
					}
					if($selectChildImageData['type']!=1)
					{

						if($data1['adoptChild']==1 )    //adoptChild display only creator user and profile owner
						{
							if(($loginUserId== $data1['creatorUserID'] || $loginUserId==$data['creatorUserID']))
							{
								if($childImageRow['iteration_ignore'] ==0)
								{
									
									$cdata['child'][]=$data1;
								}

							}
						}
						else
						{
							$cdata['child'][]=$data1;
						}
					}
					



				}
			}


			if(empty($cdata))
			{
				$cdata['child']=array();
			}
			if(empty($pdata))
			{
				$pdata['parent']=array();
			}

			$totalData=array_merge($pdata,$cdata);



		}
		else
		{

			echo json_encode(array('message'=>'There is no relevant data','success'=>0));
			exit;
		}

	}
	else
	{
		echo json_encode(array('message'=>'There is no relevant data','success'=>0));
		exit;
	}

}
if($type==3)
{


	$getInfo=mysqli_query($conn,"SELECT map_table.imageID FROM `map_table`inner join iteration_table on map_table.imageID = iteration_table.imageID where iteration_table.iterationID='$iterationId' and map_table.map_id='$imageId' ") or die(mysqli_error());



	//$getInfo=mysqli_query($conn,"SELECT assigned_name,imageID FROM map_table WHERE map_id='$imageId'") or die(mysqli_error());

	if(mysqli_num_rows($getInfo)>0)
	{
		while($row=mysqli_fetch_assoc($getInfo))
		{

			$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid FROM sub_iteration_table WHERE imgID='".$row['imageID']."'  AND iterationID='".$iterationId."' "));
			$data['stackIteration']=$getSubIterationImageInfo['did'];
			$creatorUserName=getOwnerName($getSubIterationImageInfo['fdid'],$row['imageID'],$conn);

			$data['ownerName']=$creatorUserName;
			$data['contributorSession']=$contributorSession;
			$cubeCount=storyThreadCount($imageId,$loginUserId,$iterationId,$conn);

			$data['storyThreadCount']=$cubeCount['storyThreadCount'];
			$data['cubeinfo']=($cubeCount['storyData'] == NULL ? '' : $cubeCount['storyData']);

			$lastSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".$row['imageID']."'  order by id desc"));
		   $data['countInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
		   $data['optionalCountInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];

			$newUsername=mysqli_fetch_row(mysqli_query($conn,"SELECT username,profileimg,cover_image FROM tb_user WHERE id='$userId' "));

			$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$loginUserId."' and iterationID='".$iterationId."' and imageID= '".$row['imageID']."' and status ='1' ");

					/* 			$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$login_userid."' and iterationID='".$iteration_id."' and imageID= '".$unique_id."' "); */

			$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE imageID='".$row['imageID']."' AND iterationID='".$iterationId."'");

			if(mysqli_num_rows($getImageInfo)>0)
			{
				$totalData=NULL;
				while($imagerow=mysqli_fetch_assoc($getImageInfo))
				{
					$data['userName']=$newUsername[0];
					if(mysqli_num_rows($stackNotify)>0)
					{
						$data['stackNotify']=1;
					}
					else
					{
						$data['stackNotify']=0;
					}


					$getBlockUserData=mysqli_query($conn,"SELECT status,id FROM `block_user_table` WHERE ((userID='".$loginUserId."'  and blockUserID='".$userId."') OR (userID='".$userId."'  and blockUserID='".$loginUserId."')) and status ='1'");
					if (mysqli_num_rows($getBlockUserData)>0)
					{
						$data['addition_status']=1;  //check the iteration for block user(if blocker see the iteration then hide all the button. )
					}
					else
					{
						$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);
						$getBlockImageIDList = explode(",",$allBlockImageID);
						if (in_array($row['imageID'], $getBlockImageIDList))
						{
							$data['additionStatus']=1;
						}
						else
						{
							$data['additionStatus']=0;
						}
					}


					if($imagerow['allow_addition']=='0')
					{

						$data['allowAddition']=0;	 //means user can add anything on stack
					}
					else if($imagerow['allow_addition']=='1' and $imagerow['userID']==$loginUserId )
					{

						$data['allowAddition']=0;	 //means user can add anything on stack
					}
					else if($imagerow['allow_addition']=='1' and $imagerow['userID']!=$loginUserId )
					{

						$data['allowAddition']=1;	 //means user cannot add anything on stack
					}
					else
					{

						$data['allowAddition']=0;	 //means user can add anything on stack
					}
					$data['allowAdditionToggle']=($imagerow['allow_addition'] == NULL ? '' : $imagerow['allow_addition']);
					$data['userID']=$imagerow['userID'];
					//$data['name']=$imagerow['stacklink_name'];
					$data['typeID']=$row['imageID'];
					$data['imageID']=$row['imageID'];
					$data['ID']=$imageId;
					$data['title']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
					$data['creatorUserID']=$imagerow['userID'];
					$data['iterationID']=$imagerow['iterationID'];


					$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' and  userID ='".$loginUserId."' ");

					if(mysqli_num_rows($likeImage) > 0)
					{
					//
						$data['like']=1;

					}
					else
					{
						$data['like']=0;
					}
					$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$iterationId."'"));




					if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
					{
						$data['adoptChild']=1; // adopt button enable
					}
					else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
					{
						$data['adoptChild']=0; // adopt button disable
					}
 					else if($userId != $loginUserId && $imagerow['adopt_photo']==0)
					{
						$data['adoptChild']=2;  //share button +no show edit button
					}
					else
					{
						$data['adoptChild']=3; //share button + show edit button
					}
					if($imagerow['delete_tag'] == 1 )
					{
						$data['deleteTag']=1;
					}
					else
					{
						if($imagerow['adopt_photo'] == 1 )
						{
							if($fetchcreatorUserID['userID'] == $loginUserId)
							{
								$data['deleteTag']=1;
							}
							
						}
						else
						{
							$data['deleteTag']=0;
						}
					}

					if($imagerow['adopt_photo']==1)
					{
						$data['iterationButton']=0;
					}
					else
					{
						$data['iterationButton']=1;

					}
					
					if($imagerow['delete_tag'] == 1 )
					{
						$data['deleteTag']=1;
					}
					else
					{
						if($imagerow['adopt_photo'] == 1 )
						{
							if($fetchcreatorUserID['userID'] == $loginUserId)
							{
								$data['deleteTag']=1;
							}
							
						}
						else
						{
							$data['deleteTag']=0;
						}
					}

					//------------if stack is part of cube then does not use session --------------
			
					$gettingParentType = stacklinkIteration($conn,$breakactivestacklink[1],'type',$r1['imageID'],$imagerow['userID']);
					
					$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$breakactivestacklink[1]."'))");
					$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
					$gettingParentOfParentType = $gettingCubeData['type'];
					if($gettingParentType  == 6 || $gettingParentOfParentType ==6)
					{
						
						$newIterationID=$iterationId;
						$newUserID=$userId;
					}
					else
					{
						if($iterationButton==0)
						{
							$WhoStackLinkIterationID=mysqli_query($conn,"SELECT id from whostack_table inner join iteration_table on whostack_table. reuestedIterationID =iteration_table.iterationID   WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");

							if(mysqli_num_rows($WhoStackLinkIterationID)>0)
							{
								$getSessionIterationIDInfo =0;
								$getIterationIDWhoStackInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',whostackFdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

								$getIterationIDWhoStackInfo=mysqli_num_rows($getIterationIDWhoStackInfo);
							}

							else
							{

								$getIterationIDWhoStackInfo  = 0;
								$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

								$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);

							}

							if($getIterationIDWhoStackInfo > 0 and $iterationButton==0)
							{

								$whoStackIterationIDInfo=mysqli_fetch_assoc($getIterationIDWhoStackInfo);

								$getIterationIDWhoStackInfoUpdate=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  !find_in_set('".$getSubIterationImageInfo['did']."',fdid) and find_in_set('".$getSubIterationImageInfo['did']."',rdid) and user_id='".$loginUserId."' order by datetime desc limit 1");

								if(mysqli_num_rows($getIterationIDWhoStackInfoUpdate)>0)
								{
									$newIterationID=$whoStackIterationIDInfo['iterationID'];
									$newUserID=$whoStackIterationIDInfo['user_id'];
									

								}
								else
								{
									$newIterationID=$iterationId;
									$newUserID=$userId;
									

								}



							}


							else if($getSessionIterationIDInfo1 > 0 and $iterationButton==0)
							{

								$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);


								$newIterationID=$IterationIDInfo['iterationID'];
								$newUserID=$IterationIDInfo['user_id'];
									


							}
							else
							{

								$newIterationID=$iterationId;
								$newUserID=$userId;
							}
						}
						else
						{
							$newIterationID=$iterationId;
							$newUserID=$userId;
						}
					}
			

					$data['sessionIterationID']=$newIterationID;
					$data['sessionImageID']=$row['imageID'];


					$collectIterationID=mysqli_query($conn,"SELECT iterationID FROM iteration_table  WHERE imageID='".$row['imageID']."'");
					while($collectIterationIDS=mysqli_fetch_assoc($collectIterationID))
					{
						$iterationIDContain[]=$collectIterationIDS['iterationID'];

						$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$collectIterationIDS['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfo)>0)
						{
							while($cubeInformation=mysqli_fetch_assoc($cubeInfo))
							{

								$countIterationArray[]=$cubeInformation['id'];
							}
						}
					}

					if(count($countIterationArray)>0)
					{
						$data['cubeButton']=1;
					}
					else
					{
						$data['cubeButton']=0;
					}

					$selectParentImageData1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as imagecount FROM `comment_table` where  imageID='".$row['imageID']."'"));
                    $data['imageComment']=$selectParentImageData1['imagecount'];
					$selectParentImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,frame,lat,lng,webUrl,location,addSpecification FROM image_table WHERE imageID='".$row['imageID']."'"));

					$data['url']=($selectParentImageData['url']!='')? $selectParentImageData['url']:'';
					$data['thumbUrl']=($selectParentImageData['thumb_url']!='')? $selectParentImageData['thumb_url']:'';
					$data['frame']=$selectParentImageData['frame'];
					$data['x']=($selectParentImageData['lat'] == NULL ? '' : $selectParentImageData['lat']);
					$data['y']=($selectParentImageData['lng'] == NULL ? '' : $selectParentImageData['lng']);
					//$data['image_comments']=$imagerow['image_comments']; //wrong change it
					$data['type']=$selectParentImageData['type'];
					$data['webUrl']=($selectParentImageData['webUrl'])?$selectParentImageData['webUrl']:'';
					$data['location']=($selectParentImageData['location'])?$selectParentImageData['location']:'';
					$data['addSpecification']=($selectParentImageData['addSpecification'])?$selectParentImageData['addSpecification']:'';


					//----------------------------------------------stacklinks array-------------------------------------

					$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$newIterationID."'"));

					if (strpos($getImageStacklinksInfo['stacklinks'], 'home') == false) {

						$words = explode(',',$getImageStacklinksInfo['stacklinks']);

						foreach ($words as $word)
						{

							$result = explode('/',$word);
							$getcount=mysqli_fetch_assoc(mysqli_query($conn,"select imageID,count(iterationID) as count_iteration  from iteration_table where imageID=(SELECT imageID FROM `iteration_table` where  iterationID='".$result[1]."')"));



							$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type FROM image_table WHERE imageID='".$getcount['imageID']."'"));


							if($stackFetchFromImageTable['type']!=6)
							{


								$arr['stacklink']=$word;
								$stackLinkData[]=$arr;
							}
							else
							{
								$fetchActiveStackName = $result[0].'/home';
								$arr['stacklink']=$result[0].'/home';
								$stackLinkData[]=$arr;
							}


						}



						foreach($stackLinkData as $fetchStackLink) {
						$ids[] = $fetchStackLink['stacklink'];
						}
						$stackLinksArr=$ids;
					}
					else
					{

						$arr = array();
						 $reverseString =$getImageStacklinksInfo['stacklinks'];
						$words = explode(',',$reverseString);
						foreach ($words as $word)
						{


							$result = explode('/',$word);
							$getcount=mysqli_fetch_assoc(mysqli_query($conn,"SELECT imageID,count(iterationID) as count_iteration FROM `iteration_table` where LOCATE('$word',stacklinks) and iterationID<='".$newIterationID."'"));

							$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$result[1]."')"));

							if($stackFetchFromImageTable['type']!=6)
							{


								$arr['stacklink']=$word;
								$stackLinkData[]=$arr;
							}
							else
							{
								$fetchActiveStackName = $result[0].'/home';
								$arr['stacklink']=$result[0].'/home';
								$stackLinkData[]=$arr;
							}


						}

						foreach($stackLinkData as $fetchStackLink) {
							$ids[] = $fetchStackLink['stacklink'];
						}
						$stackLinksArr=$ids;


					}

					if(count($stackLinksArr) > 1)
					{

						if(count($stackLinksArr)>=2 and $iterationButton==0)
						{

							$fetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM user_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."' ");

							$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

							$createNewDid = $linkingInfo['did'];
							$createNewFdid = $linkingInfo['fdid'];
							$createNewRdid = $linkingInfo['rdid'];
							$createNewWhoStackFdid = $linkingInfo['whostackFdid'];

							if(mysqli_num_rows($fetchIterationIDInfo)<=0)
							{

								$unixTimeStamp=date("Y-m-d"). date("H:i:s");

								$insertUserTable=mysqli_query($conn,"INSERT INTO user_table(iterationID,user_id,imageID,
								did,fdid,rdid,whostackFdid,date,time,datetime) VALUES('".$newIterationID."',
								'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','$createNewWhoStackFdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')");


							}
							else
							{
								$unixTimeStamp=date("Y-m-d"). date("H:i:s");
								mysqli_query($conn,"update user_table set whostackFdid='".$createNewWhoStackFdid."', date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."' , stack_type='0' where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."'");
							}

						}





						$WhoStackLinkIterationID=mysqli_query($conn,"SELECT distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name, whostack_table.datetime FROM `iteration_table`  inner join whostack_table on iteration_table.iterationID = whostack_table. reuestedIterationID    WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");


						if(mysqli_num_rows($WhoStackLinkIterationID)>0)
						{

							while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
							{


								$whostackLinksArr[]=$fetchWhoStackLinkIterationID['who_stacklink_name'];


							}
						}
						if(!empty($whostackLinksArr)>0) // remove who stack data here
						{
							$whostackLinksArrValue=array_reverse(array_diff($whostackLinksArr,$stackLinksArr));
						}



						if(!empty($whostackLinksArrValue))
						{

							foreach($whostackLinksArrValue as $stacklinkCount=>$stackminiArr) //whostack stackLink
							{
								$stackArrInfoData=explode('/',$stackminiArr);

								$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
								$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
								$stacked['stackuserdata']['coverImage']= ($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image'] );
								$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
								if (in_array($stackUserInfo['id'], $getBlockUserList))
								{
									$stacked['stackuserdata']['blockUser']=1;
								}
								else
								{

									$stacked['stackuserdata']['blockUser']=0;
								}

								$stackArr1[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];


								if($stackArrInfoData[1]=='home')
								{
									$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
									$stackedRelated['stackrelateddata']['ID']='';
									$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
									$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';
									$stackedRelated['stackrelateddata']['frame']='';
									$stackedRelated['stackrelateddata']['x']='';
									$stackedRelated['stackrelateddata']['y']='';
									$stackedRelated['stackrelateddata']['imageComment']='';
									$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								else
								{

									$stackedRelated['stackrelateddata']['active_stacklink']=0;

									$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
									$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
									$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
									$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
									$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
									$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
									$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
									$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
									$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

									if($stackDataFetchFromImageTable['type']==2)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
										//image Data


										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
									}
									if($stackDataFetchFromImageTable['type']==3)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

									}
									if($stackDataFetchFromImageTable['type']==4)
									{


										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
									}
									if($stackDataFetchFromImageTable['type']==5)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
									}

									if($stackDataFetchFromImageTable['type']==6)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']='';
									}
									//$stackedRelated['stackrelateddata']['jyot']=5;
									$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

								}



							}
						}


						$stacklink['stacklinks']=array_reverse($stackArr1);


						foreach($stackLinksArr as $stacklinkCount=> $stackminiArr)
						{


							$mainStackLink = explode(',', trim($imagerow['stacklinks'])); // check session or original stack of that stack.
							$stackArrInfoData=explode('/',$stackminiArr);

							$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
							AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

							$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
							/**********if child of other ownername should show**********/

							if($stackArrInfoData[1]!='home'){
								$stackOwnerInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,CASE WHEN u.profileimg IS NULL OR u.profileimg = '' THEN '' 
								WHEN u.profileimg LIKE 'albumImages/%' THEN concat( '$serverurl', u.profileimg ) ELSE
								u.profileimg
								END
								AS profileimg,img.type FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$stackArrInfoData[1]."'"));

								$stacked['stackuserdata']['userName']=$stackOwnerInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackOwnerInfo['profileimg']!='')?$stackOwnerInfo['profileimg']:'';

							}
							else{
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							}
							$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
							$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
							if (in_array($stackUserInfo['id'], $getBlockUserList))
							{
								$stacked['stackuserdata']['blockUser']=1;
							}
							else
							{

								$stacked['stackuserdata']['blockUser']=0;
							}

							if(in_array($stackminiArr, $mainStackLink))
							{
								$mainStackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain original stack related that stack.
							}
							else
							{
								$sessionstackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain all session stacklink.
							}

							//$stackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];

							if($stackArrInfoData[1]=='home')
							{

								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}
								$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
								$stackedRelated['stackrelateddata']['ID']='';
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackedRelated['stackrelateddata']['frame']='';
								$stackedRelated['stackrelateddata']['x']='';
								$stackedRelated['stackrelateddata']['y']='';
								$stackedRelated['stackrelateddata']['imageComment']='';
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);


								if(in_array($stackminiArr, $mainStackLink))
								{
									$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];	// contain original stack related that stack.
								}
								else
								{
									$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];// contain all session stacklink.
								}
								//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{

								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}

								$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID='".$nameOfStack['imageID']."'"));
								$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
								$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
								$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
								$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
								$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
								$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' :  $stackDataFetchFromImageTable['lat']);
								$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
								$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
								$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

								if($stackDataFetchFromImageTable['type']==2)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
									//image Data
									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}
								if($stackDataFetchFromImageTable['type']==3)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
								}
								if($stackDataFetchFromImageTable['type']==4)
								{

									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
								}
								if($stackDataFetchFromImageTable['type']==5)
								{

									$stackedRelated['stackrelateddata']['url']=$imageurl['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$imageurl['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$nameOfStack['imageID']."'")) ;

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
								}
								if($stackDataFetchFromImageTable['type']==6)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
								}
								//$stackedRelated['stackrelateddata']['jyot']=6;

								if(in_array($stackminiArr, $mainStackLink))
								{
									$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								else
								{
									$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

							}
						}

						if(!empty($stackArr1))  //means whostack data exist.
						{
							if(!empty($sessionstackArr))
							{
								//whostack , session, main stacklink exist
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr,$mainStackArr)); // insert to reverse order
							}
							else
							{
								//whostack, main stacklink exist but session data does not exist.
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$mainStackArr));
							}

						}
						else
						{   //means whostack data does not exist.

							if(!empty($sessionstackArr))
							{
								//sesion data exist.

								$data['stacklinks']=array_reverse(array_merge($sessionstackArr,$mainStackArr));
							}
							else
							{
								//sesion data does not exist.
								$data['stacklinks']=array_reverse(array_merge($mainStackArr));
							}

						}
					}
					else
					{

						$getAllWhoStackLink=mysqli_query($conn,"SELECT reuestedIterationID FROM whostack_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$imagerow['iterationID']."'  AND  requestStatus =2 ");
						$commaVariable='';
						if(mysqli_num_rows($getAllWhoStackLink)>0)
						{
							while($allWhoStackLink=mysqli_fetch_assoc($getAllWhoStackLink))
							{
								$allWhoStackIterationID.=$commaVariable.$allWhoStackLink['reuestedIterationID'];
								$commaVariable=',';
							}
						}


						$WhoStackLinkIterationID=mysqli_query($conn,"select * from (SELECT  distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name , iterationID FROM `iteration_table` where  iterationID in ($allWhoStackIterationID)) as stack_link_table where who_stacklink_name!='".$stackLinksArr[0]."'  ORDER BY FIELD(iterationID,$allWhoStackIterationID) desc ");

						if(mysqli_num_rows($WhoStackLinkIterationID)>0)
						{
							while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
							{

								$stackArrInfoData=explode('/',$fetchWhoStackLinkIterationID['who_stacklink_name']);

								$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

								$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
								$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
								$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
								if (in_array($stackUserInfo['id'], $getBlockUserList))
								{
									$stacked['stackuserdata']['blockUser']=1;
								}
								else
								{

									$stacked['stackuserdata']['blockUser']=0;
								}
								$stackArr['stackUserInfo']=$stacked['stackuserdata'];

								if($stackArrInfoData[1]=='home')
								{

									$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
									$stackedRelated['stackrelateddata']['ID']='';
									$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
									$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';
									$stackedRelated['stackrelateddata']['frame']='';
									$stackedRelated['stackrelateddata']['x']='';
									$stackedRelated['stackrelateddata']['y']='';
									$stackedRelated['stackrelateddata']['imageComment']='';
									$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
									$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
									$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
									$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
									$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
									$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
									$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
									$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat'] );
									$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL  ? '' : $stackDataFetchFromTagTable['lng']);
									$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
									$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

									if($stackDataFetchFromImageTable['type']==2)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
									}
									if($stackDataFetchFromImageTable['type']==3)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];

									}
									if($stackDataFetchFromImageTable['type']==4)
									{

										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
									}
									if($stackDataFetchFromImageTable['type']==5)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
									}
									if($stackDataFetchFromImageTable['type']==6)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']='';
									}



									$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

								}

								$data['stacklinks'][]=array_reverse($stackArr);


							}


						}
						$stackArrInfoData=explode('/',$stackLinksArr[0]);

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}
						$stackArr['stackUserInfo']=$stacked['stackuserdata'];

						if($stackArrInfoData[1]=='home')
						{

							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
							$stackedRelated['stackrelateddata']['activeStacklink']=1;
							$stackedRelated['stackrelateddata']['ID']='';
							$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['imageComment']='';
							$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{

							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID='".$nameOfStack['imageID']."'"));

							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['activeStacklink']=1;
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
							$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$nameOfStack['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$nameOfStack['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
							}
							if($stackDataFetchFromImageTable['type']==4)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$nameOfStack['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							}
							if($stackDataFetchFromImageTable['type']==5)
							{


								$stackedRelated['stackrelateddata']['url']=$imageurl['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$imageurl['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$nameOfStack['imageID']."'")) or die(mysqli_error());

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];

							}
							if($stackDataFetchFromImageTable['type']==6)
							{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
							}


							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						$data['stacklinks'][]=array_reverse($stackArr);
					}
					//--------------------------------------------------------------------------------------------------


					$data['userinfo']['userName']=$newUsername[0];
					$data['userinfo']['firstName']=$newUsername[4];
					$data['userinfo']['userID']=$newUserID;
					if($newUsername[1]!='')
					{
						$data['userinfo']['profileImg']=$newUsername[1];
					}
					else
					{
						$data['userinfo']['profileImg']='';
					}




				/*-------------   call function fetch all swap child ---------------------*/
				// New Autorelated By jyoti

				if( $relatedThreadID=='' && $autorelatedID==''){


					$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' ORDER BY viewDate DESC limit 1");
					if(mysqli_num_rows($autorelated_session)>0){

						$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
						$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
						$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
						$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
						$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
					}
					else
					{
						$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){

							$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
							$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
							$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
							$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
							$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
						}
					}



				}
				if( $relatedThreadID=='' && $autorelatedID=='')
				{

					//new autorelated by jyoti

					$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$iterationId."' and imageID ='".$imagerow['imageID']."'");
					if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

						$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

						if($fetchNewAutoRelated['autorelated']!=''){

							$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
							array_unshift($arrayAuto,$iterationId);

							$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
							$currentStack['iterationID']=$fetchNewAutoRelated['iterationID'];
							$currentStack['imageID']=$fetchNewAutoRelated['imageID'];
							$currentStack['forwordrelatedID']=1;
							$currentStack['backwordrelatedID']='';
							$currentStack['optionalIndex']='';
								$currentStack['optionalOf']='';
							$data['CurrentStack']=$currentStack;

							$rightchild['iterationID']=$arrayAuto[1];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[1]."'"));
							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							$rightchild['userID']=$userId;
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$data['rightChild']=$rightchild;
							$data['leftChild']=array();
							$data['optionalChild']=array();

						}
					}

					else
					{
						$data['optionalChild']=array();
						$data['CurrentStack']=array();
						$data['rightChild']=array();
						$data['leftChild']=array();
					}
				}
				else if($optionalOf=='' && $optionalIndex=='' && $relatedThreadID!='')
				{
					
					$unixTimeStamp=date("Y-m-d"). date("H:i:s");


					$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
					if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

						$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

						if($fetchNewAutoRelated['autorelated']!=''){
							$arrayIndex=$autorelatedID;
							$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
							array_unshift($arrayAuto,$fetchNewAutoRelated['iterationID']);
							$indexCount=count($arrayAuto);
							$rightIndex=$arrayIndex+1;
							$leftIndex=$arrayIndex-1;
							$lIndex='';
							$fIndex='';

							if($arrayIndex == 0){ //if current is first item then main iteration is left child
								$rIndex=$rightIndex;
								$lIndex=$indexCount-1;
							}
							else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
								$rIndex=0;
								$lIndex=$leftIndex;
							}
							else{ //if current is neigther last nor first
								$rIndex=$rightIndex;
								$lIndex=$leftIndex;
							}
							$currentStack['threadID']=$relatedThreadID;
							$currentStack['iterationID']=$iterationId;
							$currentStack['imageID']=$imageId;
							$currentStack['forwordrelatedID']=$rIndex;
							$currentStack['backwordrelatedID']=$lIndex;
							$currentStack['optionalIndex']='';
							$currentStack['optionalOf']='';
							//Right child Start
							$rightchild['iterationID']=$arrayAuto[$rIndex];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));
							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							$rightchild['userID']=$userId;
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							//Right child End
							//Left Child start
							$leftchild['iterationID']=$arrayAuto[$lIndex];
							$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN 	thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));
							$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
							$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
							$leftchild['userID']=$userId;
							$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
							$leftchild['title']=$getStackImageID1['stacklink_name'];
							$leftchild['threadID']=$relatedThreadID;
							$leftchild['autoRelatedID']=$lIndex;
								//Left Child End
							$data['CurrentStack']=$currentStack;
							$data['rightChild']=$rightchild;
							$data['leftChild']=$leftchild;
							//Optional child autorelated start

							//500 entries should be maintained only by jyoti

								//500 entries should be maintained only by jyoti

							$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
							if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){
								$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");

								$result = mysqli_fetch_assoc($autorelated_session);

								if(mysqli_num_rows($autorelated_session)<1){
										$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
										VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());
								}
								elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
								}
								else
								{
									$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
								}

							}
							else{

								$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
								if($autorelated_session_delete){
									$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
										VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());

								}
							}

							$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
							if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

								$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

								if($optionalAutoRelated['autorelated']!=''){
									$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
									array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
									$optionalCount=count($arrayOptionalAuto);
									$optionalchild['iterationID']=$arrayOptionalAuto[1];
									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$data['optionalChild']=$optionalchild;

									$rightOptional['iterationID']=$arrayOptionalAuto[1];
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$rightOptional['userID']=$userId;
									$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$rightOptional['title']=$getStackImageID['stacklink_name'];
									$rightOptional['threadID']=$optionalAutoRelated['threadID'];
									$rightOptional['autoRelatedID']=1;
									$data['rightOptional']=$rightOptional;

									$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

									$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
									$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
									$leftOptional['userID']=$userId;
									$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
									$leftOptional['title']=$getStackImageID1['stacklink_name'];
									$leftOptional['threadID']=$getStackImageID1['threadID'];
									$leftOptional['autoRelatedID']=$optionalCount-1;
									$leftOptional['threadID']=$arrayOptionalAuto[1];
									$data['leftOptional']=$leftOptional;


								}
							}
							else
							{
								$data['optionalChild']=array();
								$data['rightOptional']=array();
								$data['leftOptional']=array();
							}

						}
						else
							{
								$data['optionalChild']=array();
								$data['CurrentStack']=array();
								$data['rightChild']=array();
								$data['leftChild']=array();
							}
					}
					//new autorelated by jyoti end

				}
				else
				{
										
					//new autorelated by jyoti
					$unixTimeStamp=date("Y-m-d"). date("H:i:s");
					if($optionalOf==$iterationId){
						$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
				 		$arrayIndex=$optionalIndex-1;
				 	}
				 	else{
				 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
				 		$arrayIndex=$autorelatedID;

				 	}

					if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

						$fetchNormalAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
						if($fetchNormalAutoRelated['autorelated']!=''){

							$arrayAuto=explode(',', $fetchNormalAutoRelated['autorelated']);
							array_unshift($arrayAuto,$fetchNormalAutoRelated['iterationID']);
							$indexCount=count($arrayAuto);
							if($arrayIndex<0){
								$arrayIndex=$indexCount-1;

							}
							$rightIndex=$arrayIndex+1;
							$leftIndex=$arrayIndex-1;
							$lIndex='';
							$fIndex='';
							if($arrayIndex == 0){ //if current is first item then main iteration is left child
								$rIndex=$rightIndex;
								$lIndex=$indexCount-1;
							}
							else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
								$rIndex=0;
								$lIndex=$leftIndex;
							}
							else{ //if current is neigth last nor first
								$rIndex=$rightIndex;
								$lIndex=$leftIndex;
							}
							$currentStack['threadID']=$relatedThreadID;
							$currentStack['iterationID']=$iterationId;
							$currentStack['imageID']=$imageId;
							$currentStack['forwordrelatedID']=$rIndex;
							$currentStack['backwordrelatedID']=$lIndex;
							$currentStack['optionalIndex']=$optionalIndex;
							$currentStack['optionalOf']=$optionalOf;
							//Right child Start
							$rightchild['iterationID']=$arrayAuto[$rIndex];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));
							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							$rightchild['userID']=$userId;
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							//Right child End
							//Left Child start
							$leftchild['iterationID']=$arrayAuto[$lIndex];
							$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));
							$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
							$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
							$leftchild['userID']=$userId;
							$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
							$leftchild['title']=$getStackImageID1['stacklink_name'];
							$leftchild['threadID']=$relatedThreadID;
							$leftchild['autoRelatedID']=$lIndex;
							//Left Child End
							$data['CurrentStack']=$currentStack;
							$data['rightChild']=$rightchild;
							$data['leftChild']=$leftchild;
							$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
							if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

								$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

								if($optionalAutoRelated['autorelated']!=''){
									$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
									array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
									$optionalCount=count($arrayOptionalAuto);
									$optionalchild['iterationID']=$arrayOptionalAuto[1];
									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$data['optionalChild']=$optionalchild;

									$rightOptional['iterationID']=$arrayOptionalAuto[1];
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$rightOptional['userID']=$userId;
									$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$rightOptional['title']=$getStackImageID['stacklink_name'];
									$rightOptional['threadID']=$optionalAutoRelated['threadID'];
									$rightOptional['autoRelatedID']=1;
									$rightOptional['threadID']=$arrayOptionalAuto[1];
									$data['rightOptional']=$rightOptional;

									$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

									$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
									$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
									$leftOptional['userID']=$userId;
									$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
									$leftOptional['title']=$getStackImageID1['stacklink_name'];
									$leftOptional['threadID']=$getStackImageID1['threadID'];
									$leftOptional['autoRelatedID']=$optionalCount-1;
									$leftOptional['threadID']=$arrayOptionalAuto[1];
									$data['leftOptional']=$leftOptional;

									$optionalSession=1;
									$optionalOfSession=$optionalOf;
									$optionalIndexSession=$optionalIndex;
									$optionalRightIndex=1;
									$optionalLeftIndex=$optionalCount-1;
									$optionalRightID=$arrayOptionalAuto[1];
									$optionalLeftID=$arrayOptionalAuto[$optionalCount-1];


								}

							}
							else
							{
								$data['optionalChild']=array();
								$data['rightOptional']=array();
								$data['leftOptional']=array();

									$optionalSession=0;
									$optionalOfSession='';
									$optionalIndexSession='';
									$optionalRightIndex='';
									$optionalLeftIndex='';
									$optionalRightID='';
									$optionalLeftID='';
							}
								//500 entries should be maintained only by jyoti

								$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
								if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){

									$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");
									$result = mysqli_fetch_assoc($autorelated_session);

									if(mysqli_num_rows($autorelated_session)<1){

											$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());


									}
									//$optionalOf=='' && $optionalIndex
									elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
									else
									{
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
								}
								else{

									$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
									if($autorelated_session_delete){
										$autorelated_session==mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID =='".$relatedThreadID."' order by viewDate desc limit 1");
											$result = mysqli_fetch_assoc($autorelated_session);
											if(mysqli_num_rows($autorelated_session)<1){
												$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
												VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());
											}
											elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
											}
									}

								}

						}
						else
							{
								$data['optionalChild']=array();
								$data['CurrentStack']=array();
								$data['rightChild']=array();
								$data['leftChild']=array();
							}
					}
					//new autorelated by jyoti end

				}

								/*------------------------------------------------------*/
					$pdata['parent']=$data;





					$selectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.lat,tag_table.lng,tag_table.frame,tag_table.username FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.imageID not in ($allBlockImageID) AND iteration_table.iterationID IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE  iteration_table.verID='".$imagerow['verID']."' AND tag_table.linked_iteration='".$iterationId."' AND tag_table.lat!='empty' AND tag_table.lng!='empty' )");


					if(mysqli_num_rows($selectChild) > 0)
					{


						while($childImageRow=mysqli_fetch_assoc($selectChild))
						{

							$data1['userId']=$childImageRow['username'];
							//$data1['name']=$childImageRow['stacklink_name'];
							$data1['userName']=$newUsername[0];
							$data1['title']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$data1['iterationID']=$childImageRow['iterationID'];
							$data1['imageID']=$childImageRow['imageID'];
							$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
							$data1['creatorUserID']=$childImageRow['userID'];
							$data1['iterationIgnore']=$childImageRow['iteration_ignore'];

							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$childImageRow['imageID']."'  AND iterationID='".$childImageRow['iterationID']."' "));




							$getIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id,stack_type FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");


							if(mysqli_num_rows($getIterationIDInfo)>0 )
							{
								$IterationIDInfo=mysqli_fetch_assoc($getIterationIDInfo);

								if($IterationIDInfo['stack_type']=='1')
								{

									$data1['apiUse']=1; //related

								}
								else
								{
									$data1['apiUse']=0; //normal
								}

							}
							else{
								$data1['apiUse']=0; //normal

							}

							if($childImageRow['adopt_photo']==0)
							{

								$data1['adoptChild']=0;
							}
							else
							{

								$data1['adoptChild']=1;
							}
							
							if($childImageRow['delete_tag'] == 1 )
							{
								$data1['deleteTag']=1;
							}
							else
							{
								if($childImageRow['adopt_photo'] == 1 )
								{
									if($childImageRow['username'] == $loginUserId)
									{
										$data1['deleteTag']=1;
									}
									
								}
								else
								{
									$data1['deleteTag']=0;
								}
							}

							$adopt_photo_type=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where iterationID='".$imagerow['iterationID']."' and adopt_iterationID='".$childImageRow['iterationID']."'"));
							$data1['adoptPhoto']=isset($adopt_photo_type['type'])?$adopt_photo_type['type']:'';





							$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,tb_user.username,tb_user.profileimg,tb_user.id FROM image_table INNER JOIN  tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$childImageRow['imageID']."'"));


							$data1['coverPhoto']=array();

							$coverPhoto['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
							$coverPhoto['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
							$data1['coverPhoto'][]=$coverPhoto;

							$data1['typeID']=$childImageRow['imageID'];

							$data1['type']=$selectChildImageData['type'];
							if($selectChildImageData['type']==4)
							{
								$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
								$data1['ID']=$selectGrid[0];

							}
							if($selectChildImageData['type']==2)
							{
								$data1['ID']=$selectChildImageData['imageID'];
							}
							if($selectChildImageData['type']==5)
							{
								$data1['ID']=$selectChildImageData['imageID'];
							}
							if($selectChildImageData['type']==3)
							{
								$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
								$data1['ID']=$selectMap[0];

							}

							$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
							$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';

							$data1['frame']=$childImageRow['frame'];
							$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
							$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);

							$data1['userinfo']['userName']=$selectChildImageData['username'];
							$data1['userinfo']['userID']=$selectChildImageData['id'];
							if($selectChildImageData['profileimg']!='')
							{
								$data1['userinfo']['profileImg']=$selectChildImageData['profileimg'];
							}
							else
							{
								$data1['userinfo']['profileImg']='';
							}


							if($data1['adoptChild']==1 )
							{
								if(($loginUserId== $data1['creatorUserID'] || $loginUserId==$data['creatorUserID']))
								{
									if($childImageRow['iteration_ignore'] ==0)
									{
										
										$cdata['child'][]=$data1;
									}
								}

							}
							else
							{
								$cdata['child'][]=$data1;
							}

						}
					}




					if(empty($cdata))
					{
						$cdata['child']=array();
					}
					if(empty($pdata))
					{
						$pdata['parent']=array();
					}

					$totalData=array_merge($pdata,$cdata);

				}
			}
			else
			{
				echo json_encode(array('message'=>'There is no relevant data','success'=>0));
				exit;
			}
		}
	}
	else
	{

		$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid FROM sub_iteration_table WHERE imgID='".$imageId."'  AND iterationID='".$iterationId."' "));

		$creatorUserName=getOwnerName($getSubIterationImageInfo['fdid'],$imageId,$conn);
		$data['stackIteration']=$getSubIterationImageInfo['did'];
		$data['ownerName']=$creatorUserName;
		$cubeCount=storyThreadCount($imageId,$loginUserId,$iterationId,$conn);
		$data['storyThreadCount']=$cubeCount['storyThreadCount'];
		$data['cubeinfo']=($cubeCount['storyData'] == NULL ? '' : $cubeCount['storyData']);

		$lastSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".imageId."'  order by id desc"));
		$data['countInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
		 $data['optionalCountInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
		//------------if stack is part of cube then does not use session --------------
			
			$gettingParentType = stacklinkIteration($conn,$breakactivestacklink[1],'type',$r1['imageID'],$imagerow['userID']);
			
			$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$breakactivestacklink[1]."'))");
			$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
			$gettingParentOfParentType = $gettingCubeData['type'];
			if($gettingParentType  == 6 || $gettingParentOfParentType ==6)
			{
				
				$newIterationID=$iterationId;
				$newUserID=$userId;
			}
			else
			{
				if($iterationButton==0)
				{
					$WhoStackLinkIterationID=mysqli_query($conn,"SELECT id from whostack_table inner join iteration_table on whostack_table. reuestedIterationID =iteration_table.iterationID   WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");

					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{
						$getSessionIterationIDInfo =0;
						$getIterationIDWhoStackInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',whostackFdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getIterationIDWhoStackInfo=mysqli_num_rows($getIterationIDWhoStackInfo);
					}

					else
					{

						$getIterationIDWhoStackInfo  = 0;
						$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);

					}

					if($getIterationIDWhoStackInfo > 0 and $iterationButton==0)
					{

						$whoStackIterationIDInfo=mysqli_fetch_assoc($getIterationIDWhoStackInfo);

						$getIterationIDWhoStackInfoUpdate=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  !find_in_set('".$getSubIterationImageInfo['did']."',fdid) and find_in_set('".$getSubIterationImageInfo['did']."',rdid) and user_id='".$loginUserId."' order by datetime desc limit 1");

						if(mysqli_num_rows($getIterationIDWhoStackInfoUpdate)>0)
						{
							$newIterationID=$whoStackIterationIDInfo['iterationID'];
							$newUserID=$whoStackIterationIDInfo['user_id'];
							

						}
						else
						{
							$newIterationID=$iterationId;
							$newUserID=$userId;
							

						}



					}


					else if($getSessionIterationIDInfo1 > 0 and $iterationButton==0)
					{

						$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);


						$newIterationID=$IterationIDInfo['iterationID'];
						$newUserID=$IterationIDInfo['user_id'];
							


					}
					else
					{

						$newIterationID=$iterationId;
						$newUserID=$userId;
					}
				}
				else
				{
					$newIterationID=$iterationId;
					$newUserID=$userId;
				}
			}
			
		$data['sessionIterationID']=$newIterationID;
		$data['sessionImageID']=$imageId;


		$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$newIterationID."' and  userID ='".$loginUserId."' ");

		if(mysqli_num_rows($likeImage) > 0)
		{
		//
			$data['like']=1;

		}
		else
		{
			$data['like']=0;
		}
		$newUsername=mysqli_fetch_row(mysqli_query($conn,"SELECT username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
		AS profileimg,cover_image FROM tb_user WHERE id='$userId' "));



		$getInfo=mysqli_query($conn,"SELECT assigned_name,imageID FROM map_table WHERE imageID='$imageId'") or die(mysqli_error());

		if(mysqli_num_rows($getInfo)>0)
		{
			while($row=mysqli_fetch_assoc($getInfo))
			{
				$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE imageID='".$row['imageID']."' AND iterationID='".$iterationId."'");



				$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$loginUserId."' and iterationID='".$iterationId."' and imageID= '".$row['imageID']."' and status ='1' ");

				if(mysqli_num_rows($getImageInfo)>0)
				{
					$totalData=NULL;
					while($imagerow=mysqli_fetch_assoc($getImageInfo))
					{
						$data['userName']=$newUsername[0];
						$data['userID']=$imagerow['userID'];
						if(mysqli_num_rows($stackNotify)>0)
						{
							$data['stackNotify']=1;
						}
						else
						{
							$data['stackNotify']=0;
						}

						$getBlockUserData=mysqli_query($conn,"SELECT status,id FROM `block_user_table` WHERE ((userID='".$loginUserId."'  and blockUserID='".$userId."') OR (userID='".$userId."'  and blockUserID='".$loginUserId."')) and status ='1'");
						if (mysqli_num_rows($getBlockUserData)>0)
						{
							$data['addition_status']=1;  //check the iteration for block user(if blocker see the iteration then hide all the button. )
						}
						else
						{
							$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);
							$getBlockImageIDList = explode(",",$allBlockImageID);
							if (in_array($row['imageID'], $getBlockImageIDList))
							{
								$data['additionStatus']=1;
							}
							else
							{
								$data['additionStatus']=0;
							}
						}
						if($imagerow['allow_addition']=='0')
						{

							$data['allowAddition']=0;	 //means user can add anything on stack
						}
						else if($imagerow['allow_addition']=='1' and $imagerow['userID']==$loginUserId )
						{

							$data['allow_addition']=0;	 //means user can add anything on stack
						}
						else if($imagerow['allow_addition']=='1' and $imagerow['userID']!=$loginUserId )
						{

							$data['allowAddition']=1;	 //means user cannot add anything on stack
						}
						else
						{

							$data['allowAddition']=0;	 //means user can add anything on stack
						}
						$data['allowAdditionToggle']=($imagerow['allow_addition'] == NULL ? '' : $imagerow['allow_addition'] );
						$data['title']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						$data['name']=$imagerow['stacklink_name'];
						$data['typeID']=$row['imageID'];
						$data['ID']=$imageId;
						$data['imageID']=$row['imageID'];
						$data['iterationID']=$imagerow['iterationID'];
						$data['creatorUserID']=$imagerow['userID'];

						$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$iterationId."'"));
						if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
						{
							$data['adoptChild']=1; // adopt button enable
						}
						else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
						{
							$data['adoptChild']=0; // adopt button disable
						}
						else if($userId != $loginUserId && $imagerow['adopt_photo']==0)
						{
							$data['adoptChild']=2;  //share button +no show edit button
						}
						else
						{
							$data['adoptChild']=3; //share button + show edit button
						}
						if($imagerow['delete_tag'] == 1 )
						{
							$data['deleteTag']=1;
						}
						else
						{
							if($imagerow['adopt_photo'] == 1 )
							{
								if($fetchcreatorUserID['userID'] == $loginUserId)
								{
									$data['deleteTag']=1;
								}
								
							}
							else
							{
								$data['deleteTag']=0;
							}
						}


						if($imagerow['adopt_photo']==1)
						{
							$data['iterationButton']=0;
						}
						else
						{
							$data['iterationButton']=1;

						}



						$collectIterationID=mysqli_query($conn,"SELECT iterationID FROM iteration_table  WHERE imageID='".$row['imageID']."'");
						while($collectIterationIDS=mysqli_fetch_assoc($collectIterationID))
						{
							$iterationIDContain[]=$collectIterationIDS['iterationID'];

							$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$collectIterationIDS['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfo)>0)
							{
								while($cubeInformation=mysqli_fetch_assoc($cubeInfo))
								{

									$countIterationArray[]=$cubeInformation['id'];
								}
							}
						}

						if(count($countIterationArray)>0)
						{
							$data['cubeButton']=1;
						}
						else
						{
							$data['cubeButton']=0;
						}


						$selectParentImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,frame,lat,lng,webUrl,location,addSpecification FROM image_table WHERE imageID='".$row['imageID']."'"));

						$data['url']=($selectParentImageData['url']!='')? $selectParentImageData['url']:'';
						$data['thumbUrl']=($selectParentImageData['thumb_url']!='')? $selectParentImageData['thumb_url']:'';
						$data['frame']=$selectParentImageData['frame'];
						$data['x']= ($selectParentImageData['lat'] == NULL ? '' :  $selectParentImageData['lat']);
						$data['y']=($selectParentImageData['lng'] == NULL ? '' : $selectParentImageData['lng']);
						$data['type']=$selectParentImageData['type'];
						$data['webUrl']=($selectParentImageData['webUrl'])?$selectParentImageData['webUrl']:'';
						$data['location']=($selectParentImageData['location'])?$selectParentImageData['location']:'';
						$data['addSpecification']=($selectParentImageData['addSpecification'])?$selectParentImageData['addSpecification']:'';

						//----------------------------------------------stacklinks array-------------------------------------


						$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$newIterationID."'"));

						if (strpos($getImageStacklinksInfo['stacklinks'], 'home') == false) {


							$words = explode(',',$getImageStacklinksInfo['stacklinks']);

							foreach ($words as $word)
							{

								$result = explode('/',$word);
								$getcount=mysqli_fetch_assoc(mysqli_query($conn,"select imageID, count(iterationID) as count_iteration  from iteration_table where imageID=(SELECT imageID FROM `iteration_table` where  iterationID='".$result[1]."')"));

								$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type FROM image_table WHERE imageID='".$getcount['imageID']."'"));

								if($stackFetchFromImageTable['type']!=6)
								{


									$arr['stacklink']=$word;
									$stackLinkData[]=$arr;
								}
								else
								{
									$fetchActiveStackName = $result[0].'/home';
									$arr['stacklink']=$result[0].'/home';
									$stackLinkData[]=$arr;
								}

							}



							foreach($stackLinkData as $fetchStackLink) {
							$ids[] = $fetchStackLink['stacklink'];
							}
							$stackLinksArr=$ids;
						}
						else
						{

							$arr = array();
							$reverseString =$getImageStacklinksInfo['stacklinks'];
							$words = explode(',',$reverseString);
							foreach ($words as $word)
							{
								$result = explode('/',$word);
								$getcount=mysqli_fetch_assoc(mysqli_query($conn,"SELECT imageID,count(iterationID) as count_iteration FROM `iteration_table` where LOCATE('$word',stacklinks) and iterationID<='".$newIterationID."' "));

								$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$result[1]."')"));



								if($stackFetchFromImageTable['type']!=6)
								{

									$arr['stacklink']=$word;
									$stackLinkData[]=$arr;
								}
								else
								{
									$fetchActiveStackName = $result[0].'/home';
									$arr['stacklink']=$result[0].'/home';
									$stackLinkData[]=$arr;
								}



							}


							foreach($stackLinkData as $fetchStackLink) {
							$ids[] = $fetchStackLink['stacklink'];
							}
							$stackLinksArr=$ids;
						}
						if(count($stackLinksArr) > 1)
						{


							if(count($stackLinksArr)>=2 and $iterationButton==0)
							{

								$fetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM user_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."' ");

								$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

								$createNewDid = $linkingInfo['did'];
								$createNewFdid = $linkingInfo['fdid'];
								$createNewRdid = $linkingInfo['rdid'];
								$createNewWhoStackFdid = $linkingInfo['whostackFdid'];

								if(mysqli_num_rows($fetchIterationIDInfo)<=0)
								{

									$unixTimeStamp=date("Y-m-d"). date("H:i:s");

									$insertUserTable=mysqli_query($conn,"INSERT INTO user_table(iterationID,user_id,imageID,
									did,fdid,rdid,whostackFdid,date,time,datetime) VALUES('".$newIterationID."',
									'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','$createNewWhoStackFdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')");


								}
								else
								{
									$unixTimeStamp=date("Y-m-d"). date("H:i:s");
									mysqli_query($conn,"update user_table set whostackFdid='".$createNewWhoStackFdid."', date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."' , stack_type='0' where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."'");
								}

							}





							$WhoStackLinkIterationID=mysqli_query($conn,"SELECT distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name, whostack_table.datetime FROM `iteration_table`  inner join whostack_table on iteration_table.iterationID = whostack_table. reuestedIterationID    WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");


							if(mysqli_num_rows($WhoStackLinkIterationID)>0)
							{

								while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
								{


									$whostackLinksArr[]=$fetchWhoStackLinkIterationID['who_stacklink_name'];


								}
							}
							if(!empty($whostackLinksArr)>0) // remove who stack data here
							{
								$whostackLinksArrValue=array_reverse(array_diff($whostackLinksArr,$stackLinksArr));
							}





							foreach($whostackLinksArrValue as $stacklinkCount=>$stackminiArr) //whostack stackLink
							{
								$stackArrInfoData=explode('/',$stackminiArr);

								$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
								$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
								$stacked['stackuserdata']['coverImage']=( $stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
								$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
								if (in_array($stackUserInfo['id'], $getBlockUserList))
								{
									$stacked['stackuserdata']['blockUser']=1;
								}
								else
								{

									$stacked['stackuserdata']['blockUser']=0;
								}
								$stackArr1[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];


								if($stackArrInfoData[1]=='home')
								{
									$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
									if($stackminiArr==$fetchActiveStackName)
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
									}
									$stackedRelated['stackrelateddata']['ID']='';
									$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
									$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';
									$stackedRelated['stackrelateddata']['frame']='';
									$stackedRelated['stackrelateddata']['x']='';
									$stackedRelated['stackrelateddata']['y']='';
									$stackedRelated['stackrelateddata']['imageComment']='';
									$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								else
								{

									if($stackminiArr==$fetchActiveStackName)
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
									}

									$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
									$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
									$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
									$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
									$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
									$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
									$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
									$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
									$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

									if($stackDataFetchFromImageTable['type']==2)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
										//image Data


										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
									}
									if($stackDataFetchFromImageTable['type']==3)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									    $stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

									}
									if($stackDataFetchFromImageTable['type']==4)
									{


										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
									}
									if($stackDataFetchFromImageTable['type']==5)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
									}

									if($stackDataFetchFromImageTable['type']==6)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']='';
									}


									$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

								}



							}
							$stacklink['stacklinks']=array_reverse($stackArr1);

							foreach($stackLinksArr as $stacklinkCount=> $stackminiArr)
							{

								$mainStackLink = explode(',', trim($imagerow['stacklinks'])); // check session or original stack of that stack.

								$stackArrInfoData=explode('/',$stackminiArr);

								$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

								$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
								$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
								$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
								if (in_array($stackUserInfo['id'], $getBlockUserList))
								{
									$stacked['stackuserdata']['blockUser']=1;
								}
								else
								{

									$stacked['stackuserdata']['blockUser']=0;
								}

								if(in_array($stackminiArr, $mainStackLink))
								{
									$mainStackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain original stack related that stack.
								}
								else
								{
									$sessionstackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain all session stacklink.
								}
								//$stackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];

								if($stackArrInfoData[1]=='home')
								{
									if($stackminiArr==$fetchActiveStackName)
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
									}
									$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
									$stackedRelated['stackrelateddata']['ID']='';
									$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
									$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';
									$stackedRelated['stackrelateddata']['frame']='';
									$stackedRelated['stackrelateddata']['x']='';
									$stackedRelated['stackrelateddata']['y']='';
									$stackedRelated['stackrelateddata']['imageComment']='';
									$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);


									if(in_array($stackminiArr, $mainStackLink))
									{
										$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];	// contain original stack related that stack.
									}
									else
									{
										$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];// contain all session stacklink.
									}

									//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								else
								{
									if($stackminiArr==$fetchActiveStackName)
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
									}

									$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
									$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
									$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
									$stackedRelated['stackrelateddata']['iterationID88']=$stackArrInfoData[1]; //new add
									$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
									$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
									$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' :  $stackDataFetchFromImageTable['lng'] );
									$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
									$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

									if($stackDataFetchFromImageTable['type']==2)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
										//image Data
										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$nameOfStack['imageID']."'"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
									}
									if($stackDataFetchFromImageTable['type']==3)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									    $stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$nameOfStack['imageID']."'"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
									}
									if($stackDataFetchFromImageTable['type']==4)
									{

										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$nameOfStack['imageID']."'"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
									}
									if($stackDataFetchFromImageTable['type']==5)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$nameOfStack['imageID']."'")) ;

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
									}
									if($stackDataFetchFromImageTable['type']==6)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']='';
									}

									if(in_array($stackminiArr, $mainStackLink))
									{
										$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
									}
									else
									{
										$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
									}

									//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

								}
							}

							if(!empty($stackArr1))  //means whostack data exist.
							{
								if(!empty($sessionstackArr))
								{
									//whostack , session, main stacklink exist
									$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr,$mainStackArr)); // insert to reverse order
								}
								else
								{
									//whostack, main stacklink exist but session data does not exist.
									$data['stacklinks']=array_reverse(array_merge($stackArr1,$mainStackArr));
								}

							}
							else{   //means whostack data does not exist.

								if(!empty($sessionstackArr))
								{
									//sesion data exist.

									$data['stacklinks']=array_reverse(array_merge($sessionstackArr,$mainStackArr));
								}
								else
								{
									//sesion data does not exist.
									$data['stacklinks']=array_reverse(array_merge($mainStackArr));
								}

							}
						}
						else
						{
							$getAllWhoStackLink=mysqli_query($conn,"SELECT reuestedIterationID FROM whostack_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$imagerow['iterationID']."'  AND  requestStatus =2 ");
							$commaVariable='';
							if(mysqli_num_rows($getAllWhoStackLink)>0)
							{
								while($allWhoStackLink=mysqli_fetch_assoc($getAllWhoStackLink))
								{
									$allWhoStackIterationID.=$commaVariable.$allWhoStackLink['reuestedIterationID'];
									$commaVariable=',';
								}
							}


							$WhoStackLinkIterationID=mysqli_query($conn,"select * from (SELECT  distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name , iterationID FROM `iteration_table` where  iterationID in ($allWhoStackIterationID)) as stack_link_table where who_stacklink_name!='".$stackLinksArr[0]."'  ORDER BY FIELD(iterationID,$allWhoStackIterationID) desc ");



							if(mysqli_num_rows($WhoStackLinkIterationID)>0)
							{
								while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
								{

									$stackArrInfoData=explode('/',$fetchWhoStackLinkIterationID['who_stacklink_name']);

									$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
									AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

									$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
									$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
									$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
									$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' : $stackUserInfo['cover_image']);
									$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
									if (in_array($stackUserInfo['id'], $getBlockUserList))
									{
										$stacked['stackuserdata']['blockUser']=1;
									}
									else
									{

										$stacked['stackuserdata']['blockUser']=0;
									}
									$stackArr['stackUserInfo']=$stacked['stackuserdata'];

									if($stackArrInfoData[1]=='home')
									{

										$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
										$stackedRelated['stackrelateddata']['ID']='';
										$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
										$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
										$stackedRelated['stackrelateddata']['name']='';
										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';
										$stackedRelated['stackrelateddata']['frame']='';
										$stackedRelated['stackrelateddata']['x']='';
										$stackedRelated['stackrelateddata']['y']='';
										$stackedRelated['stackrelateddata']['imageComment']='';
										$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
										$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
									}
									else
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
										$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
										$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
										$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

										$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
										$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
										$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
										$stackedRelated['stackrelateddata']['iterationID666']=$stackArrInfoData[1];  //new add
										$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
										$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat']);
										$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' : $stackDataFetchFromTagTable['lng']);
										$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
										$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

										if($stackDataFetchFromImageTable['type']==2)
										{
											$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
											$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

											$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

											$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
											$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
										}
										if($stackDataFetchFromImageTable['type']==3)
										{
											$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									        $stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

											$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

											$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
											$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];

										}
										if($stackDataFetchFromImageTable['type']==4)
										{

											$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
											$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

											$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

											$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
											$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
										}
										if($stackDataFetchFromImageTable['type']==5)
										{

											$stackedRelated['stackrelateddata']['url']='';
											$stackedRelated['stackrelateddata']['thumbUrl']='';

											$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


											$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
											$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
										}
										if($stackDataFetchFromImageTable['type']==6)
										{

											$stackedRelated['stackrelateddata']['url']='';
											$stackedRelated['stackrelateddata']['thumbUrl']='';

											$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


											$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
											$stackedRelated['stackrelateddata']['ID']='';
										}



										$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

									}

									$data['stacklinks'][]=array_reverse($stackArr);


								}


							}
							$stackArrInfoData=explode('/',$stackLinksArr[0]);

							$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
							AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

							$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
							$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
							$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
							$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
							if (in_array($stackUserInfo['id'], $getBlockUserList))
							{
								$stacked['stackuserdata']['blockUser']=1;
							}
							else
							{

								$stacked['stackuserdata']['blockUser']=0;
							}
							$stackArr['stackUserInfo']=$stacked['stackuserdata'];

							if($stackArrInfoData[1]=='home')
							{

								$stackedRelated['stackrelateddata']['activeDtacklink']=1;
								$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
								$stackedRelated['stackrelateddata']['ID']='';
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackedRelated['stackrelateddata']['frame']='';
								$stackedRelated['stackrelateddata']['x']='';
								$stackedRelated['stackrelateddata']['y']='';
								$stackedRelated['stackrelateddata']['imageComment']='';
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{

								$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID='".$nameOfStack['imageID']."'"));

								$stackedRelated['stackrelateddata']['activeStacklink']=1;
								$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
								$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
								$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
								$stackedRelated['stackrelateddata']['iterationID44']=$stackArrInfoData[1];  //new add
								$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
								$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
								$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng'] );
								$stackedRelated['stackrelateddata']['imageComments']=$stackDataFetchFromImageTable['image_comments'];
								$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

								if($stackDataFetchFromImageTable['type']==2)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
									//image Data
									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}
								if($stackDataFetchFromImageTable['type']==3)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
								}
								if($stackDataFetchFromImageTable['type']==4)
								{

									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
								}
								if($stackDataFetchFromImageTable['type']==5)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';
									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$nameOfStack['imageID']."'")) or die(mysqli_error());

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];

								}
								if($stackDataFetchFromImageTable['type']==6)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
								}

								$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
								$data['stacklinks'][]=array_reverse($stackArr);
						}
						//--------------------------------------------------------------------------------------------------


						$data['userinfo']['userName']=$newUsername[0];
						$data['userinfo']['userID']=$newUserID;
						if($newUsername[1]!='')
						{
							$data['userinfo']['profileImg']=$newUsername[1];
						}
						else
						{
							$data['userinfo']['profileImg']='';
						}


				/*--------------------------   call function fetch all swap child -----------------------------------*/


				// New Autorelated By jyoti

					if( $relatedThreadID=='' && $autorelatedID==''){

						$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and threadID ='".$threadID."'  ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){

							$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
							$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
							$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
							$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
							$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
						}



					}
					if( $relatedThreadID=='' && $autorelatedID=='')
					{
						//new autorelated by jyoti

						$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$iterationId."' and imageID ='".$imagerow['imageID']."'");
						if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

							$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

							if($fetchNewAutoRelated['autorelated']!=''){

								$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
								array_unshift($arrayAuto,$iterationId);

								$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
								$currentStack['iterationID']=$fetchNewAutoRelated['iterationID'];
								$currentStack['imageID']=$fetchNewAutoRelated['imageID'];
								$currentStack['forwordrelatedID']=1;
								$currentStack['backwordrelatedID']='';
								$currentStack['optionalIndex']='';
									$currentStack['optionalOf']='';
								$data['CurrentStack']=$currentStack;

								$rightchild['iterationID']=$arrayAuto[1];
								$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[1]."'"));
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightchild['userID']=$userId;
								$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightchild['title']=$getStackImageID['stacklink_name'];
								$data['rightChild']=$rightchild;
								$data['leftChild']=array();
								$data['optionalChild']=array();

							}
						}

						else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']=array();
							$data['leftChild']=array();
						}
					}
					else if($optionalOf=='' && $optionalIndex=='' && $relatedThreadID!='')
					{

						$unixTimeStamp=date("Y-m-d"). date("H:i:s");


						$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

							$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

							if($fetchNewAutoRelated['autorelated']!=''){
								$arrayIndex=$autorelatedID;
								$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
								array_unshift($arrayAuto,$fetchNewAutoRelated['iterationID']);
								$indexCount=count($arrayAuto);
								$rightIndex=$arrayIndex+1;
								$leftIndex=$arrayIndex-1;
								$lIndex='';
								$fIndex='';

								if($arrayIndex == 0){ //if current is first item then main iteration is left child
									$rIndex=$rightIndex;
									$lIndex=$indexCount-1;
								}
								else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
									$rIndex=0;
									$lIndex=$leftIndex;
								}
								else{ //if current is neigther last nor first
									$rIndex=$rightIndex;
									$lIndex=$leftIndex;
								}
								$currentStack['threadID']=$relatedThreadID;
								$currentStack['iterationID']=$iterationId;
								$currentStack['imageID']=$imageId;
								$currentStack['forwordrelatedID']=$rIndex;
								$currentStack['backwordrelatedID']=$lIndex;
								$currentStack['optionalIndex']='';
								$currentStack['optionalOf']='';
								//Right child Start
								$rightchild['iterationID']=$arrayAuto[$rIndex];
								$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightchild['userID']=$userId;
								$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightchild['title']=$getStackImageID['stacklink_name'];
								$rightchild['threadID']=$relatedThreadID;
								$rightchild['autoRelatedID']=$rIndex;
								//Right child End
								//Left Child start
								$leftchild['iterationID']=$arrayAuto[$lIndex];
								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));
								$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftchild['userID']=$userId;
								$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftchild['title']=$getStackImageID1['stacklink_name'];
								$leftchild['threadID']=$relatedThreadID;
								$leftchild['autoRelatedID']=$lIndex;
									//Left Child End
								$data['CurrentStack']=$currentStack;
								$data['rightChild']=$rightchild;
								$data['leftChild']=$leftchild;
								//Optional child autorelated start

								//500 entries should be maintained only by jyoti

								$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
								if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){
									$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");

									$result = mysqli_fetch_assoc($autorelated_session);

									if(mysqli_num_rows($autorelated_session)<1){
											$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());
									}
									elseif($result['optionalOf']!=$optionalOf){
												/***Need to update**/
												$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
									else
									{
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
								}
								else{

									$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
									if($autorelated_session_delete){
										$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());

									}
								}

								$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
								if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

									$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

									if($optionalAutoRelated['autorelated']!=''){
										$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
										array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
										$optionalCount=count($arrayOptionalAuto);
										$optionalchild['iterationID']=$arrayOptionalAuto[1];
										$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
										$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
										$optionalchild['userID']=$userId;
										$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
										$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
										$optionalchild['title']=$getStackImageID['stacklink_name'];
										$optionalchild['threadID']=$optionalAutoRelated['threadID'];
										$optionalchild['forwordrelatedID']=1;
										$optionalchild['backwordrelatedID']='';
										$data['optionalChild']=$optionalchild;

										$rightOptional['iterationID']=$arrayOptionalAuto[1];
										$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
										$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
										$rightOptional['userID']=$userId;
										$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
										$rightOptional['title']=$getStackImageID['stacklink_name'];
										$rightOptional['threadID']=$optionalAutoRelated['threadID'];
										$rightOptional['autoRelatedID']=1;
										$data['rightOptional']=$rightOptional;

										$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

										$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
										$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
										$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
										$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
										$leftOptional['userID']=$userId;
										$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
										$leftOptional['title']=$getStackImageID1['stacklink_name'];
										$leftOptional['threadID']=$getStackImageID1['threadID'];
										$leftOptional['autoRelatedID']=$optionalCount-1;
										$leftOptional['threadID']=$arrayOptionalAuto[1];
										$data['leftOptional']=$leftOptional;


									}
								}
								else
								{
									$data['optionalChild']=array();
									$data['rightOptional']=array();
									$data['leftOptional']=array();
								}

							}
							else
								{
									$data['optionalChild']=array();
									$data['CurrentStack']=array();
									$data['rightChild']=array();
									$data['leftChild']=array();
								}
						}
						//new autorelated by jyoti end

					}
					else
					{

						//new autorelated by jyoti
						$unixTimeStamp=date("Y-m-d"). date("H:i:s");
						if($optionalOf==$iterationId){
							$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
					 		$arrayIndex=$optionalIndex-1;
					 	}
					 	else{
					 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
					 		$arrayIndex=$autorelatedID;

					 	}

						if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

							$fetchNormalAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
							if($fetchNormalAutoRelated['autorelated']!=''){

								$arrayAuto=explode(',', $fetchNormalAutoRelated['autorelated']);
								array_unshift($arrayAuto,$fetchNormalAutoRelated['iterationID']);
								$indexCount=count($arrayAuto);
								if($arrayIndex<0){
									$arrayIndex=$indexCount-1;

								}
								$rightIndex=$arrayIndex+1;
								$leftIndex=$arrayIndex-1;
								$lIndex='';
								$fIndex='';
								if($arrayIndex == 0){ //if current is first item then main iteration is left child
									$rIndex=$rightIndex;
									$lIndex=$indexCount-1;
								}
								else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
									$rIndex=0;
									$lIndex=$leftIndex;
								}
								else{ //if current is neigth last nor first
									$rIndex=$rightIndex;
									$lIndex=$leftIndex;
								}
								$currentStack['threadID']=$relatedThreadID;
								$currentStack['iterationID']=$iterationId;
								$currentStack['imageID']=$imageId;
								$currentStack['forwordrelatedID']=$rIndex;
								$currentStack['backwordrelatedID']=$lIndex;
								$currentStack['optionalIndex']=$optionalIndex;
								$currentStack['optionalOf']=$optionalOf;
								//Right child Start
								$rightchild['iterationID']=$arrayAuto[$rIndex];
								$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightchild['userID']=$userId;
								$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightchild['title']=$getStackImageID['stacklink_name'];
								$rightchild['threadID']=$relatedThreadID;
								$rightchild['autoRelatedID']=$rIndex;
								//Right child End
								//Left Child start
								$leftchild['iterationID']=$arrayAuto[$lIndex];
								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));
								$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftchild['userID']=$userId;
								$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftchild['title']=$getStackImageID1['stacklink_name'];
								$leftchild['threadID']=$relatedThreadID;
								$leftchild['autoRelatedID']=$lIndex;
								//Left Child End
								$data['CurrentStack']=$currentStack;
								$data['rightChild']=$rightchild;
								$data['leftChild']=$leftchild;
								$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
								if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

									$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

									if($optionalAutoRelated['autorelated']!=''){
										$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
										array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
										$optionalCount=count($arrayOptionalAuto);
										$optionalchild['iterationID']=$arrayOptionalAuto[1];
										$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
										$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
										$optionalchild['activeIterationID']=$newIterationID;
										$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
										$optionalchild['userID']=$userId;
										$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
										$optionalchild['title']=$getStackImageID['stacklink_name'];
										$optionalchild['threadID']=$optionalAutoRelated['threadID'];
										$optionalchild['forwordrelatedID']=1;
										$optionalchild['backwordrelatedID']='';
										$data['optionalChild']=$optionalchild;

										$rightOptional['iterationID']=$arrayOptionalAuto[1];
										$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
										$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
										$rightOptional['userID']=$userId;
										$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
										$rightOptional['title']=$getStackImageID['stacklink_name'];
										$rightOptional['threadID']=$optionalAutoRelated['threadID'];
										$rightOptional['autoRelatedID']=1;
										$rightOptional['threadID']=$arrayOptionalAuto[1];
										$data['rightOptional']=$rightOptional;

										$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

										$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
										$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
										$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
										$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
										$leftOptional['userID']=$userId;
										$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
										$leftOptional['title']=$getStackImageID1['stacklink_name'];
										$leftOptional['threadID']=$getStackImageID1['threadID'];
										$leftOptional['autoRelatedID']=$optionalCount-1;
										$leftOptional['threadID']=$arrayOptionalAuto[1];
										$data['leftOptional']=$leftOptional;

										$optionalSession=1;
										$optionalOfSession=$optionalOf;
										$optionalIndexSession=$optionalIndex;
										$optionalRightIndex=1;
										$optionalLeftIndex=$optionalCount-1;
										$optionalRightID=$arrayOptionalAuto[1];
										$optionalLeftID=$arrayOptionalAuto[$optionalCount-1];


									}

								}
								else
								{
									$data['optionalChild']=array();
									$data['rightOptional']=array();
									$data['leftOptional']=array();

										$optionalSession=0;
										$optionalOfSession='';
										$optionalIndexSession='';
										$optionalRightIndex='';
										$optionalLeftIndex='';
										$optionalRightID='';
										$optionalLeftID='';
								}

									//500 entries should be maintained only by jyoti

									$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
									if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){

										$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");
										$result = mysqli_fetch_assoc($autorelated_session);

										if(mysqli_num_rows($autorelated_session)<1){

												$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
												VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());


										}
										//$optionalOf=='' && $optionalIndex
										elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
										}
										else
										{
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
										}
									}
									else{

										$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
										if($autorelated_session_delete){
											$autorelated_session==mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID =='".$relatedThreadID."' order by viewDate desc limit 1");
												$result = mysqli_fetch_assoc($autorelated_session);
												if(mysqli_num_rows($autorelated_session)<1){
													$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
													VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());
												}
												elseif($result['optionalOf']!=$optionalOf){
												/***Need to update**/
												$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
												}
										}

									}

							}
							else
								{
									$data['optionalChild']=array();
									$data['CurrentStack']=array();
									$data['rightChild']=array();
									$data['leftChild']=array();
								}
						}
						//new autorelated by jyoti end

					}



			/*------------------------------------------------------*/
						$pdata['parent']=$data;

						$selectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.lat,tag_table.lng,tag_table.frame,tag_table.username FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.imageID not in ($allBlockImageID) and iteration_table.stack_visible=0 AND iteration_table.iterationID IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE  iteration_table.verID='".$imagerow['verID']."' AND tag_table.linked_iteration='".$iterationId."' AND tag_table.lat!='empty' AND tag_table.lng!='empty' )");


						if(mysqli_num_rows($selectChild) > 0)
						{


							while($childImageRow=mysqli_fetch_assoc($selectChild))
							{

								$data1['userID']=$childImageRow['username'];
								$data1['name']=$childImageRow['stacklink_name'];
								$data1['userName']=$newUsername[0];
								$data1['title']=$childImageRow['stacklink_name'];
								$data1['iterationID']=$childImageRow['iterationID'];
								$data1['imageID']=$childImageRow['imageID'];
								$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
								$data1['creatorUserID']=$childImageRow['userID'];
								$data1['iterationIgnore']=$childImageRow['iteration_ignore'];

								if($childImageRow['adopt_photo']==0)
								{
									$data1['adoptChild']=0;
								}
								else
								{
									$data1['adoptChild']=1;
								}
								if($childImageRow['delete_tag'] == 1 )
								{
									$data1['deleteTag']=1;
								}
								else
								{
									if($childImageRow['adopt_photo'] == 1 )
									{
										if($childImageRow['username'] == $loginUserId)
										{
											$data1['deleteTag']=1;
										}
										
									}
									else
									{
										$data1['deleteTag']=0;
									}
								}

								$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$childImageRow['imageID']."'  AND iterationID='".$childImageRow['iterationID']."' "));

								
								$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

								$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);


								if($getSessionIterationIDInfo1 > 0 )
								{

									$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);

										
									$data1['sessionIterationID']=$IterationIDInfo['iterationID'];
									$data1['sessionImageID']=$childImageRow['imageID'];



								}
								else
								{

									$data1['sessionIterationID']=$childImageRow['iterationID'];
									$data1['sessionImageID']=$childImageRow['imageID'];
								}


								$getIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id,stack_type FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");


								if(mysqli_num_rows($getIterationIDInfo)>0 )
								{
									$IterationIDInfo=mysqli_fetch_assoc($getIterationIDInfo);

									if($IterationIDInfo['stack_type']=='1')
									{

										$data1['apiUse']=1; //related

									}
									else
									{
										$data1['apiUse']=0; //normal
									}

								}
								else{
									$data1['apiUse']=0; //normal

								}


								$adopt_photo_type=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where iterationID='".$imagerow['iterationID']."' and adopt_iterationID='".$childImageRow['iterationID']."'"));
								$data1['adopt_photo']=isset($adopt_photo_type['type'])?$adopt_photo_type['type']:'';
								$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,tb_user.username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg,tb_user.id FROM image_table INNER JOIN  tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$childImageRow['imageID']."'"));


								$data1['coverPhoto']=array();

								$coverPhoto['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
								$coverPhoto['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
								$data1['coverPhoto'][]=$coverPhoto;

								$data1['typeID']=$childImageRow['imageID'];

								$data1['type']=$selectChildImageData['type'];
								if($selectChildImageData['type']==4)
								{
									$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
									$data1['ID']=$selectGrid[0];

								}
								if($selectChildImageData['type']==2)
								{
									$data1['ID']=$selectChildImageData['imageID'];
								}
								if($selectChildImageData['type']==5)
								{
									$data1['ID']=$selectChildImageData['imageID'];
								}
								if($selectChildImageData['type']==3)
								{
									$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
									$data1['ID']=$selectMap[0];

								}

								$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
								$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';

								$data1['frame']=$childImageRow['frame'];
								$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
								$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);

								$data1['userinfo']['userName']=$selectChildImageData['username'];
								$data1['userinfo']['userID']=$selectChildImageData['id'];
								if($selectChildImageData['profileimg']!='')
								{
									$data1['userinfo']['profileImg']=$selectChildImageData['profileimg'];
								}
								else
								{
									$data1['userinfo']['profileImg']='';
								}


								if($data1['adoptChild']==1 )
								{
									if(($loginUserId== $data1['creatorUserID'] || $loginUserId==$data['creatorUserID']))
									{
										if($childImageRow['iteration_ignore'] ==0)
										{
											
											$cdata['child'][]=$data1;
										}
									}

								}
								else
								{
									$cdata['child'][]=$data1;
								}

							}
						}




						if(empty($cdata))
						{
							$cdata['child']=array();
						}
						if(empty($pdata))
						{
							$pdata['parent']=array();
						}

						$totalData=array_merge($pdata,$cdata);

					}
				}
				else
				{
					echo json_encode(array('message'=>'There is no relevant data','success'=>0));
					exit;
				}
			}
		}
	}



}
if($type==4)
{

	$getInfo=mysqli_query($conn,"SELECT grid_table.imageID FROM `grid_table`inner join iteration_table on grid_table.imageID = iteration_table.imageID where iteration_table.iterationID='$iterationId' and grid_table.grid_id='$imageId' ") or die(mysqli_error());
	if(mysqli_num_rows($getInfo)>0)
	{


		$row=mysqli_fetch_assoc($getInfo);

		$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid FROM sub_iteration_table WHERE imgID='".$row['imageID']."'  AND iterationID='".$iterationId."' "));
		$getSubIterationFirstInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID FROM sub_iteration_table WHERE imgID='".$row['imageID']."'  and did = 1"));
		$originalUserName=getOwnerName1(1,$getSubIterationFirstInfo['iterationID'],$conn);
		$data['originalUserName']=$originalUserName;
		$getSubIterationImageInfo['fdid'];
		$data['stackIteration']=$getSubIterationImageInfo['did'];
		
		$creatorUserName=getOwnerName($getSubIterationImageInfo['fdid'],$row['imageID'],$conn);
		$data['ownerName']=$creatorUserName;
		$data['contributorSession']=$contributorSession;
		$cubeCount=storyThreadCount($imageId,$loginUserId,$iterationId,$conn);
		$data['storyThreadCount']=$cubeCount['storyThreadCount'];
		$data['cubeinfo']=($cubeCount['storyData'] == NULL ? '' :  $cubeCount['storyData']);


		$lastSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".$row['imageID']."'  order by id desc"));
		$data['countInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
		 $data['optionalCountInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];

		$newUsername=mysqli_fetch_row(mysqli_query($conn,"SELECT username,profileimg,cover_image FROM tb_user WHERE id='$userId' "));
		$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$loginUserId."' and iterationID='".$iterationId."' and imageID= '".$row['imageID']."' and status ='1' ");
		$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$iterationId."'");

		if(mysqli_num_rows($getImageInfo)>0)
		{
			$totalData=NULL;
			while($imagerow=mysqli_fetch_assoc($getImageInfo))
			{
				$data['userName']=$newUsername[0];
				if(mysqli_num_rows($stackNotify)>0)
				{
					$data['stackNotify']=1;
				}
				else
				{
					$data['stackNotify']=0;
				}

				$getBlockUserData=mysqli_query($conn,"SELECT status,id FROM `block_user_table` WHERE ((userID='".$loginUserId."'  and blockUserID='".$userId."') OR (userID='".$userId."'  and blockUserID='".$loginUserId."')) and status ='1'");
				if (mysqli_num_rows($getBlockUserData)>0)
				{
					$data['additionStatus']=1;  //check the iteration for block user(if blocker see the iteration then hide all the button. )
				}
				else
				{
					$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);
					$getBlockImageIDList = explode(",",$allBlockImageID);
					if (in_array($row['imageID'], $getBlockImageIDList))
					{
						$data['additionStatus']=1;
					}
					else
					{
						$data['additionStatus']=0;
					}
				}
				if($imagerow['allow_addition']=='0')
				{

					$data['allowAddition']=0;	 //means user can add anything on stack
				}
				else if($imagerow['allow_addition']=='1' and $imagerow['userID']==$loginUserId )
				{

					$data['allowAddition']=0;	 //means user can add anything on stack
				}
				else if($imagerow['allow_addition']=='1' and $imagerow['userID']!=$loginUserId )
				{

					$data['allowAddition']=1;	 //means user cannot add anything on stack
				}
				else
				{

					$data['allowAddition']=0;	 //means user can add anything on stack
				}
				$data['allowAdditionToggle']=($imagerow['allow_addition'] == NULL ? '' : $imagerow['allow_addition'] );
				$data['userID']=$imagerow['userID'];
				//$data['name']=$imagerow['stacklink_name'];
				$data['caption']=($imagerow['caption'] == NULL ? '' : stripslashes($imagerow['caption']));
				$data['typeID']=$row['imageID'];
				$data['imageID']=$row['imageID'];
				$data['ID']=$imageId;
				$data['iterationID']=$imagerow['iterationID'];
				$data['title']=$imagerow['stacklink_name'];
				$data['creatorUserID']=$imagerow['userID'];
				$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$iterationId."'"));

				if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
				{
					$data['adoptChild']=1; // adopt button enable
				}
				else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
				{
					$data['adoptChild']=0; // adopt button disable
				}
				else if($userId != $loginUserId && $imagerow['adopt_photo']==0)
				{
					$data['adoptChild']=2;  //share button +no show edit button
				}
				else
				{
					$data['adoptChild']=3; //share button + show edit button
				}
				if($imagerow['delete_tag'] == 1 )
				{
					$data['deleteTag']=1;
				}
				else
				{
					if($imagerow['adopt_photo'] == 1 )
					{
						if($fetchcreatorUserID['userID'] == $loginUserId)
						{
							$data['deleteTag']=1;
						}
						
					}
					else
					{
						$data['deleteTag']=0;
					}
				}

				$collectIterationID=mysqli_query($conn,"SELECT iterationID FROM iteration_table  WHERE imageID='".$row['imageID']."'");
				while($collectIterationIDS=mysqli_fetch_assoc($collectIterationID))
				{
					$iterationIDContain[]=$collectIterationIDS['iterationID'];


					$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$collectIterationIDS['iterationID']."',tags) ") or die(mysqli_error());
					if(mysqli_num_rows($cubeInfo)>0)
					{
						while($cubeInformation=mysqli_fetch_assoc($cubeInfo))
						{

							$countIterationArray[]=$cubeInformation['id'];
						}
					}
				}

				if(count($countIterationArray)>0)
				{
					$data['cubeButton']=1;
				}
				else
				{
					$data['cubeButton']=0;
				}

				$likeImage=mysqli_query($conn,"select * from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' and  userID ='".$loginUserId."' ");

				if(mysqli_num_rows($likeImage) > 0)
				{
				//
					$data['like']=1;

				}
				else
				{
					$data['like']=0;
				}

				//------------if stack is part of cube then does not use session --------------
			
			$gettingParentType = stacklinkIteration($conn,$breakactivestacklink[1],'type',$r1['imageID'],$imagerow['userID']);
			
			$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$breakactivestacklink[1]."'))");
			$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
			$gettingParentOfParentType = $gettingCubeData['type'];
			if($gettingParentType  == 6 || $gettingParentOfParentType ==6)
			{
				
				$newIterationID=$iterationId;
				$newUserID=$userId;
			}
			else
			{
				if($iterationButton==0)
				{
					$WhoStackLinkIterationID=mysqli_query($conn,"SELECT id from whostack_table inner join iteration_table on whostack_table. reuestedIterationID =iteration_table.iterationID   WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");

					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{
						$getSessionIterationIDInfo =0;
						$getIterationIDWhoStackInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',whostackFdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getIterationIDWhoStackInfo=mysqli_num_rows($getIterationIDWhoStackInfo);
					}

					else
					{

						$getIterationIDWhoStackInfo  = 0;
						$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);

					}

					if($getIterationIDWhoStackInfo > 0 and $iterationButton==0)
					{

						$whoStackIterationIDInfo=mysqli_fetch_assoc($getIterationIDWhoStackInfo);

						$getIterationIDWhoStackInfoUpdate=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  !find_in_set('".$getSubIterationImageInfo['did']."',fdid) and find_in_set('".$getSubIterationImageInfo['did']."',rdid) and user_id='".$loginUserId."' order by datetime desc limit 1");

						if(mysqli_num_rows($getIterationIDWhoStackInfoUpdate)>0)
						{
							$newIterationID=$whoStackIterationIDInfo['iterationID'];
							$newUserID=$whoStackIterationIDInfo['user_id'];
							

						}
						else
						{
							$newIterationID=$iterationId;
							$newUserID=$userId;
							

						}



					}


					else if($getSessionIterationIDInfo1 > 0 and $iterationButton==0)
					{

						$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);


						$newIterationID=$IterationIDInfo['iterationID'];
						$newUserID=$IterationIDInfo['user_id'];
							


					}
					else
					{

						$newIterationID=$iterationId;
						$newUserID=$userId;
					}
				}
				else
				{
					$newIterationID=$iterationId;
					$newUserID=$userId;
				}
			}
			

				$data['sessionIterationID']=$newIterationID;
				$data['sessionImageID']=$row['imageID'];

				$selectParentImageData1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as imagecount FROM `comment_table` where  imageID='".$row['imageID']."'"));
                $data['imageComment']=$selectParentImageData1['imagecount'];
				$selectParentImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,frame,lat,lng,webUrl,location,addSpecification FROM image_table WHERE imageID='".$row['imageID']."'"));

				$data['url']=($selectParentImageData['url']!='')? $selectParentImageData['url']:'';
				$data['thumbUrl']=($selectParentImageData['thumb_url']!='')? $selectParentImageData['thumb_url']:'';
				$data['frame']=$selectParentImageData['frame'];
				$data['x']= ($selectParentImageData['lat'] == NULL ? '' : $selectParentImageData['lat']);
				$data['y']=($selectParentImageData['lng'] == NULL ? '' : $selectParentImageData['lng'] );
				//$data['image_comments']=$imagerow['image_comments']; //wrong change it
				$data['type']=$selectParentImageData['type'];
				$data['webUrl']=($selectParentImageData['webUrl'])?$selectParentImageData['webUrl']:'';
				$data['location']=($selectParentImageData['location'])?$selectParentImageData['location']:'';
				$data['addSpecification']=stripcslashes($selectParentImageData['addSpecification'])?$selectParentImageData['addSpecification']:'';

				//----------------------------------------------stacklinks array-------------------------------------

				$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$newIterationID."'"));

				if (strpos($getImageStacklinksInfo['stacklinks'], 'home') == false) {

					$words = explode(',',$getImageStacklinksInfo['stacklinks']);

					foreach ($words as $word)
					{

						$result = explode('/',$word);
						$getcount=mysqli_fetch_assoc(mysqli_query($conn,"select imageID,count(iterationID) as count_iteration  from iteration_table where imageID=(SELECT imageID FROM `iteration_table` where  iterationID='".$result[1]."' and imageId='".$row['imageID']."')"));
						$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type FROM image_table WHERE imageID='".$getcount['imageID']."'"));

						if($stackFetchFromImageTable['type']!=6)
						{

							$arr['stacklink']=$word;
							$stackLinkData[]=$arr;
						}
						else
						{
							$fetchActiveStackName = $result[0].'/home';
							$arr['stacklink']=$result[0].'/home';
							$stackLinkData[]=$arr;
						}

					}



					foreach($stackLinkData as $fetchStackLink) {
					$ids[] = $fetchStackLink['stacklink'];
					}
					$stackLinksArr=$ids;
				}
				else
				{

					$arr = array();
					$reverseString =$getImageStacklinksInfo['stacklinks'];
					$words = explode(',',$reverseString);
					foreach ($words as $word)
					{
						$getcount=mysqli_fetch_assoc(mysqli_query($conn,"SELECT imageID,count(iterationID) as count_iteration FROM `iteration_table` where LOCATE('$word',stacklinks) and iterationID<='".$newIterationID."' and imageId='".$row['imageID']."'"));

						$result = explode('/',$word);
						$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$result[1]."')"));


						if($stackFetchFromImageTable['type']!=6)
						{

							$arr['stacklink']=$word;
							$stackLinkData[]=$arr;
						}
						else
						{
							$fetchActiveStackName = $result[0].'/home';
							$arr['stacklink']=$result[0].'/home';
							$stackLinkData[]=$arr;
						}

					}


					foreach($stackLinkData as $fetchStackLink) {
					$ids[] = $fetchStackLink['stacklink'];
					}
					$stackLinksArr=$ids;
				}


				if(count($stackLinksArr) > 1)
				{

					if(count($stackLinksArr)>=2 and $iterationButton==0)
					{

						$fetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM user_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."' ");

						$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

						$createNewDid = $linkingInfo['did'];
						$createNewFdid = $linkingInfo['fdid'];
						$createNewRdid = $linkingInfo['rdid'];
						$createNewWhoStackFdid = $linkingInfo['whostackFdid'];

						if(mysqli_num_rows($fetchIterationIDInfo)<=0)
						{

							$unixTimeStamp=date("Y-m-d"). date("H:i:s");

							$insertUserTable=mysqli_query($conn,"INSERT INTO user_table(iterationID,user_id,imageID,
							did,fdid,rdid,whostackFdid,date,time,datetime) VALUES('".$newIterationID."',
							'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','$createNewWhoStackFdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')");


						}
						else
						{
							$unixTimeStamp=date("Y-m-d"). date("H:i:s");
							mysqli_query($conn,"update user_table set whostackFdid='".$createNewWhoStackFdid."', date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."' , stack_type='0' where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."'");
						}

					}





					$WhoStackLinkIterationID=mysqli_query($conn,"SELECT distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name, whostack_table.datetime FROM `iteration_table`  inner join whostack_table on iteration_table.iterationID = whostack_table. reuestedIterationID    WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");


					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{

						while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
						{


							$whostackLinksArr[]=$fetchWhoStackLinkIterationID['who_stacklink_name'];


						}
					}
					if(!empty($whostackLinksArr)>0) // remove who stack data here
					{
						$whostackLinksArrValue=array_reverse(array_diff($whostackLinksArr,$stackLinksArr));
					}



					if(!empty($whostackLinksArrValue))
					{

						foreach($whostackLinksArrValue  as $stacklinkCount=>$stackminiArr)
						{
							$stackArrInfoData=explode('/',$stackminiArr);


							$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
							AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
							$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
							$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
							$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ? '' :  $stackUserInfo['cover_image']);
							$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
							if (in_array($stackUserInfo['id'], $getBlockUserList))
							{
								$stacked['stackuserdata']['blockUser']=1;
							}
							else
							{

								$stacked['stackuserdata']['blockUser']=0;
							}

							$stackArr1[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];


							if($stackArrInfoData[1]=='home')
							{
								$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}
								if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}
								$stackedRelated['stackrelateddata']['ID']='';
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['parentName']='';
								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackedRelated['stackrelateddata']['frame']='';
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['x']='';
								$stackedRelated['stackrelateddata']['y']='';
								$stackedRelated['stackrelateddata']['imageComments']='';
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);

								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
								}
								$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{
								if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}

								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}

								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
								$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
								$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
								$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
								$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
								$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
								$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
								$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng'] );
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
								$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
								}
								$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

								if($stackDataFetchFromImageTable['type']==2)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
									//image Data


									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}
								if($stackDataFetchFromImageTable['type']==3)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

								}
								if($stackDataFetchFromImageTable['type']==4)
								{


									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
								}
								if($stackDataFetchFromImageTable['type']==5)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
								}

								if($stackDataFetchFromImageTable['type']==6)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
								}


								$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

							}



						}
					}
					$stacklink['stacklinks']=array_reverse($stackArr1);

					foreach($stackLinksArr as $stacklinkCount=>$stackminiArr)
					{
						$stackArrInfoData=explode('/',$stackminiArr);

						$mainStackLink = explode(',', trim($imagerow['stacklinks'])); // check session or original stack of that stack.

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
						/**********if child of other ownername should show**********/

							if($stackArrInfoData[1]!='home'){
								$stackOwnerInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,CASE WHEN u.profileimg IS NULL OR u.profileimg = '' THEN '' 
									WHEN u.profileimg LIKE 'albumImages/%'  THEN concat( '$serverurl', u.profileimg ) ELSE
									u.profileimg
									END
									AS profileimg,img.type FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$stackArrInfoData[1]."'"));

								$stacked['stackuserdata']['userName']=$stackOwnerInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackOwnerInfo['profileimg']!='')?$stackOwnerInfo['profileimg']:'';

							}
							else{

								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							}
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}

						if(in_array($stackminiArr, $mainStackLink))
						{
							$mainStackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain original stack related that stack.
						}
						else
						{
							$sessionstackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain all session stacklink.
						}

						//$stackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];

						if($stackArrInfoData[1]=='home')
						{
							if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}
							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
							$stackedRelated['stackrelateddata']['ID']='';
							$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['parentName']='';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							$stackedRelated['stackrelateddata']['imageComment']='';

							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
							}
							$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['parentType']="";

							if(in_array($stackminiArr, $mainStackLink))
							{
								$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];	// contain original stack related that stack.
							}
							else
							{
								$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];// contain all session stacklink.
							}

							//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{
							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							
							$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
							if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
							{
								$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
								if($stackDataFetchFromImageTableData['type'] == 1)
								{
									$stackedRelated['stackrelateddata']['parentType']='';
								}
								else
								{
									$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
								}
							}
							else
							{
								$stackedRelated['stackrelateddata']['parentType']="";
								
							}
							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							if($stackminiArr==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}
							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
							$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat'] );
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
							$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
							}
							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								//image Data
								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
							}
							if($stackDataFetchFromImageTable['type']==4)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];

							}
							if($stackDataFetchFromImageTable['type']==5)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$stackDataFetchFromImageTable['imageID']."'")) or die(mysqli_error());

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							}
							if($stackDataFetchFromImageTable['type']==6)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}

							if(in_array($stackminiArr, $mainStackLink))
							{
								$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{
								$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}



							//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

						}



						if(!empty($stackArr1))  //means whostack data exist.
						{
							if(!empty($sessionstackArr))
							{
								//whostack , session, main stacklink exist
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr,$mainStackArr)); // insert to reverse order
							}
							else
							{
								//whostack, main stacklink exist but session data does not exist.
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$mainStackArr));
							}

						}
						else{   //means whostack data does not exist.

							if(!empty($sessionstackArr))
							{
								//sesion data exist.

								$data['stacklinks']=array_reverse(array_merge($sessionstackArr,$mainStackArr));
							}
							else
							{
								//sesion data does not exist.
								$data['stacklinks']=array_reverse(array_merge($mainStackArr));
							}

						}

					}
				}
				else
				{


					$getAllWhoStackLink=mysqli_query($conn,"SELECT reuestedIterationID FROM whostack_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$imagerow['iterationID']."'  AND  requestStatus =2 ");

					$commaVariable='';
					if(mysqli_num_rows($getAllWhoStackLink)>0)
					{
						while($allWhoStackLink=mysqli_fetch_assoc($getAllWhoStackLink))
						{
							$allWhoStackIterationID.=$commaVariable.$allWhoStackLink['reuestedIterationID'];
							$commaVariable=',';
						}
					}


					$WhoStackLinkIterationID=mysqli_query($conn,"select * from (SELECT  distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name , iterationID FROM `iteration_table` where  iterationID in ($allWhoStackIterationID)) as stack_link_table where who_stacklink_name!='".$stackLinksArr[0]."'  ORDER BY FIELD(iterationID,$allWhoStackIterationID) desc ");

					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{
						while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
						{

							$stackArrInfoData=explode('/',$fetchWhoStackLinkIterationID['who_stacklink_name']);

							$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
							AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

							$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
							$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
							$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
							$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
							if (in_array($stackUserInfo['id'], $getBlockUserList))
							{
								$stacked['stackuserdata']['blockUser']=1;
							}
							else
							{

								$stacked['stackuserdata']['blockUser']=0;
							}
							$stackArr['stackUserInfo']=$stacked['stackuserdata'];

							if($stackArrInfoData[1]=='home')
							{
								if($fetchWhoStackLinkIterationID['who_stacklink_name']==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}

								$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
								$stackedRelated['stackrelateddata']['ID']='';
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['parentName']='';
								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackedRelated['stackrelateddata']['frame']='';
								$stackedRelated['stackrelateddata']['x']='';
								$stackedRelated['stackrelateddata']['y']='';
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['imageComment']='';
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['parentType']="";	
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
								}
								$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{
								if($fetchWhoStackLinkIterationID['who_stacklink_name']==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
								
								$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
								if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
								{
									$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
									if($stackDataFetchFromImageTableData['type'] == 1)
									{
										$stackedRelated['stackrelateddata']['parentType']='';
									}
									else
									{
										$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
									}
								}
								else
								{
									$stackedRelated['stackrelateddata']['parentType']="";
									
								}
								$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
								$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

								$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
								$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
								$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
								$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
								$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
								$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat'] );
								$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' : $stackDataFetchFromTagTable['lng']);
								$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
								}

								if($stackDataFetchFromImageTable['type']==2)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}
								if($stackDataFetchFromImageTable['type']==3)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];

								}
								if($stackDataFetchFromImageTable['type']==4)
								{

									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
								}
								if($stackDataFetchFromImageTable['type']==5)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
								}
								if($stackDataFetchFromImageTable['type']==6)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
								}



								$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

							}

							$data['stacklinks'][]=array_reverse($stackArr);


						}


					}
					$stackArrInfoData=explode('/',$stackLinksArr[0]);

					$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
					AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

					$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
					$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
					$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
					$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
					$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
					if (in_array($stackUserInfo['id'], $getBlockUserList))
					{
						$stacked['stackuserdata']['blockUser']=1;
					}
					else
					{

						$stacked['stackuserdata']['blockUser']=0;
					}
					$stackArr['stackUserInfo']=$stacked['stackuserdata'];

					if($stackArrInfoData[1]=='home')
					{
						if($stackLinksArr[0]==$stackLinkType)
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=2;
						}


						$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
						$stackedRelated['stackrelateddata']['activeStacklink']=1;
						$stackedRelated['stackrelateddata']['ID']='';
						$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
						$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						$stackedRelated['stackrelateddata']['parentName']='';
						$stackedRelated['stackrelateddata']['url']='';
						$stackedRelated['stackrelateddata']['thumbUrl']='';
						$stackedRelated['stackrelateddata']['frame']='';
						$stackedRelated['stackrelateddata']['x']='';
						$stackedRelated['stackrelateddata']['y']='';
						$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
						$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
						$stackedRelated['stackrelateddata']['imageComment']='';
						$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$stackedRelated['stackrelateddata']['cubeID']=0;
						}
						$stackedRelated['stackrelateddata']['parentType']="";
						$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
					}
					else
					{
						$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
						
						$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
						if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
						{
							$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
							if($stackDataFetchFromImageTableData['type'] == 1)
							{
								$stackedRelated['stackrelateddata']['parentType']='';
							}
							else
							{
								$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
							}
						}
						else
						{
							$stackedRelated['stackrelateddata']['parentType']="";
							
						}

						$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
						if($stackLinksArr[0]==$stackLinkType)
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=2;
						}

						$stackedRelated['stackrelateddata']['activeStacklink']=1;
						$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
						$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
						$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
						$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
						$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
						$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
						$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
						$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
						$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng'] ) ;
						$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
						$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
						$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$stackedRelated['stackrelateddata']['cubeID']=0;
						}

						if($stackDataFetchFromImageTable['type']==2)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
						}
						if($stackDataFetchFromImageTable['type']==3)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
						}
						if($stackDataFetchFromImageTable['type']==4)
						{

							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
						}
						if($stackDataFetchFromImageTable['type']==5)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$stackDataFetchFromImageTable['imageID']."'")) or die(mysqli_error());

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
						}
						if($stackDataFetchFromImageTable['type']==6)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']='';
						}

						$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

					}
					$data['stacklinks'][]=array_reverse($stackArr);
				}
				//--------------------------------------------------------------------------------------------------

				$data['userinfo']['userName']=$newUsername[0];
				$data['userinfo']['userID']=$newUserID;
				if($newUsername[1]!='')
				{
					$data['userinfo']['profileImg']=$newUsername[1];
				}
				else
				{
					$data['userinfo']['profileImg']='';
				}

				/*--------------------------  SWAP SIBLING child -----------------------------------*/


				//Add the auto related Linking code here.
			// New Autorelated By jyoti

				/*--------------------------  SWAP SIBLING child -----------------------------------*/


			//new autorelated Fetch by jyoti
			//Add the auto related Linking code here.

		 	if( $relatedThreadID=='' && $autorelatedID==''){



				$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' ORDER BY viewDate DESC limit 1");
					if(mysqli_num_rows($autorelated_session)>0){

						$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
						$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
						$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
						$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
						$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
					}
					else
					{



						 $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID,fdid,did FROM sub_iteration_table WHERE imgID='".$imageId."'  AND did=1 "));



							$autorelated_session1=mysqli_query($conn,"SELECT * FROM `new_auto_related` where   FIND_IN_SET('".$getSubIterationImageInfo['iterationID']."',autorelated)");
							if(mysqli_num_rows($autorelated_session1)>0){

								$autorelated_session2=mysqli_query($conn,"SELECT * FROM `new_auto_related` where iterationID ='".$iterationId."' ");
								if(mysqli_num_rows($autorelated_session2)>0){


								$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
								$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
								$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
								$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
								$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
								}
							}

						}
					}




			}
			if( $relatedThreadID=='' && $autorelatedID=='')
			{


				//new autorelated by jyoti

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE new_auto_related.iterationID ='".$iterationId."' and new_auto_related.imageID ='".$imagerow['imageID']."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){


						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						
						$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
					    $data['countInfo']=count($arrayAuto);
						array_unshift($arrayAuto,$iterationId);


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$fetchNewAutoRelated['iterationID'];
						$currentStack['imageID']=$fetchNewAutoRelated['imageID'];
						$currentStack['forwordrelatedID']=1;
						$currentStack['backwordrelatedID']='';
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';
						$data['CurrentStack']=$currentStack;

						$rightchild['iterationID']=$arrayAuto[1];
						$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[1]."'"));

						$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
						$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
						$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
						$rightchild['userID']=$userId;
						$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
						$rightchild['title']=$getStackImageID['stacklink_name'];
						$rightchild['threadID']=$fetchNewAutoRelated['threadID'];
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$rightchild['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$rightchild['cubeID']=0;
						}

						$data['autorealtedParentStackName']=$imagerow['stacklink_name'];
						$data['rightChild']=$rightchild;
						$data['leftChild']=array();
						$data['optionalChild']=array();

						//iteracation check in database

						$rId = $rightchild['iterationID'];
						$getrightchild =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $rId"));
						if(empty($getrightchild)){

							$data['rightChild']="";

						}

					}
				}

				else
				{

					$data['optionalChild']=array();
					$data['CurrentStack']=array();
					$data['rightChild']=array();
					$data['leftChild']=array();
				}
				/****Delete all back auto_related ****/
				$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'"));
				/**** end Delete all back auto_related ****/
			}
			else if($optionalOf=='' && $optionalIndex=='' && $relatedThreadID!='')
			{

				$unixTimeStamp=date("Y-m-d"). date("H:i:s");

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){
						$arrayIndex=$autorelatedID;

						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						}
						
						//$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
						if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);

						}
						else{
							$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNewAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';

						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							$lIndex="";
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex="";
							$lIndex=$leftIndex;
						}
						else{ //if current is neigther last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';


						//Right child Start
						if($rIndex !== ""){
							$rightchild['iterationID']=$arrayAuto[$rIndex];

							if($rightchild['iterationID'] == $iterationId ){
								$rightchild = "";

							}else{



							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));


							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));


							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							$rightchild['userID']=$userId;
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}


							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];

							//Right child End
						 }

						}else{ $rightchild = ""; }

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;

							if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						if($lIndex !==""){
						//Left Child start
						$leftchild['iterationID']=$arrayAuto[$lIndex];
						$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

						$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));


						$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
						$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
						$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
						$leftchild['userID']=$userId;
						$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
						$leftchild['title']=$getStackImageID1['stacklink_name'];
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[$lIndex]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$leftchild['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$leftchild['cubeID']=0;
						}


						$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE threadID ='".$relatedThreadID."' and userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){

							$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

							if($fetchNewAutoRelated['threadID']!=''){
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
							}
							else
							{
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$lIndex;
							}
						}
						else
						{
							$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
							$leftchild['autoRelatedID']=$lIndex;
						}
						//$leftchild['threadID']=$relatedThreadID;
						//$leftchild['autoRelatedID']=$lIndex;
									//Left Child End
						$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
						if($data['autorealtedParentStackName']){
							$parent_name = $data['autorealtedParentStackName'];
						}else{
							$parent_name = '';
						}
						//Left Child End

						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];

							if($iteration_count==0){

									$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
									VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
								}
						}


						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));


						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iteration_id']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$backRelated['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$backRelated['cubeID']=0;
						}



					} else { $leftchild = ""; }

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						//$data['leftChild']=$leftchild;

						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}
						if($backRelated['iterationID']  == $arrayAuto[$lIndex]){
							$data['backRelated']=$backRelated;
						}else{
							$data['backRelated']="";
							/****Delete all back auto_related ****/
							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'"));
							/**** end Delete all back auto_related ****/


						}

						//Optional child autorelated start

						//500 entries should be maintained only by jyoti

						$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
						if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){
							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and   order by viewDate desc limit 1");

							$result = mysqli_fetch_assoc($autorelated_session);

							if(mysqli_num_rows($autorelated_session)<1){
									$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());
							}
							elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
							else
							{
								$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
						}
						else{

							$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
							if($autorelated_session_delete){
								$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());

							}
						}

						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								//echo 'ffffffffffffff'.$optionalCount=count($arrayOptionalAuto);
								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$optionalchild['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$optionalchild['cubeID']=0;
									}

									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}



								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightOptional['cubeID']=0;
								}
								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftOptional['cubeID']=0;
								}
								$data['leftOptional']="";

								//Check iterationID exists in database
								$opid = $optionalchild['iterationID'];
								$ropid = $rightOptional['iterationID'];

								$queryGetitreaction =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $opid"));
								if(empty($queryGetitreaction)){
									$data['optionalChild']="";

								}
								$queryGetrightoption =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $ropid"));
								if(empty($queryGetitreaction)){
									$data['rightOptional']="";

								}


							}
						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";
						}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";
						}
				}
				//new autorelated by jyoti end

			}
			else
			{


				//new autorelated by jyoti
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");
				if($optionalOf==$iterationId){


					$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");

			 		$arrayIndex=$optionalIndex-1;

			 	}
			 	else{
			 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
			 		$arrayIndex=$autorelatedID;

			 	}
				if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

					$fetchNormalAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
					if($fetchNormalAutoRelated['autorelated']!=''){

						$arrayAuto=explode(',', $fetchNormalAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNormalAutoRelated['iterationID']."'"));
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						}
						
						//$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
                       if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);

						}
						else{
								$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNormalAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						if($arrayIndex<0){
							$arrayIndex=$indexCount-1;
						}
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';
						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							//$lIndex=$indexCount-1;
							$lIndex='';
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex='';
							//$rIndex=0;
							$lIndex=$leftIndex;
						}
						else{ //if current is neigth last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}



						$currentStack['threadID']=$relatedThreadID;
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']=$optionalIndex;
						$currentStack['optionalOf']=$optionalOf;

						if($rIndex!==""){
							//Right child Start
							$rightchild['iterationID']=$arrayAuto[$rIndex];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							$rightchild['userID']=$userId;
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}

							//Right child End
						}else{
							$rightchild = "";
						}

							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;



						if($lIndex!==""){
							//Left Child start
							$leftchild['iterationID']=$arrayAuto[$lIndex];
							$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

							$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
							$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
							$leftchild['userID']=$userId;
							$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
							$leftchild['title']=$getStackImageID1['stacklink_name'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$leftchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$leftchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$leftchild['cubeID']=0;
							}
							/* $leftchild['threadID']=$relatedThreadID;
							$leftchild['autoRelatedID']=$lIndex; */

							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
							if(mysqli_num_rows($autorelated_session)>0){

								$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

								if($fetchNewAutoRelated['threadID']!=''){
									$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								    $leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
								}
								else
								{
									$leftchild['threadID']=$relatedThreadID;
									$leftchild['autoRelatedID']=$lIndex;
								}
							}
							else
							{
								$leftchild['threadID']=$relatedThreadID;
								$leftchild['autoRelatedID']=$lIndex;
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							if($data['autorealtedParentStackName']){
								$parent_name = $data['autorealtedParentStackName'];
							}else{
								$parent_name = '';

							}
							//Left Child End
						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];


							if($iteration_count == 0){
								// echo "INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								// VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')";die;

								$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
							}
						}

						if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));

						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$backRelated['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$backRelated['cubeID']=0;
						}

						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}

						}else{
							$leftchild = "";
						}

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						// $data['rightChild']=$rightchild;
						// $data['leftChild']=$leftchild;
						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}



						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){
							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);
							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								$optionalCount=count($arrayOptionalAuto);


								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$optionalchild['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$optionalchild['cubeID']=0;
									}
									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}


								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$rightOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightOptional['cubeID']=0;
								}

								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftOptional['cubeID']=0;
								}
								if($optionalOf==''){
									$data['leftOptional']=$leftOptional;
								}else{
									$data['leftOptional']="";
								}

								$optionalSession=1;
								$optionalOfSession=$optionalOf;
								$optionalIndexSession=$optionalIndex;
								$optionalRightIndex=1;
								$optionalLeftIndex=$optionalCount-1;
								$optionalRightID=$arrayOptionalAuto[1];
								$optionalLeftID=$arrayOptionalAuto[$optionalCount-1];

							}

						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";

								$optionalSession=0;
								$optionalOfSession='';
								$optionalIndexSession='';
								$optionalRightIndex='';
								$optionalLeftIndex='';
								$optionalRightID='';
								$optionalLeftID='';
						}
							//500 entries should be maintained only by jyoti

								$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
								if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){

									$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");
									$result = mysqli_fetch_assoc($autorelated_session);

									if(mysqli_num_rows($autorelated_session)<1){

											$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());


									}
									//$optionalOf=='' && $optionalIndex
									elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
									else
									{
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
								}
								else{

									$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
									if($autorelated_session_delete){
										$autorelated_session==mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID =='".$relatedThreadID."' order by viewDate desc limit 1");
											$result = mysqli_fetch_assoc($autorelated_session);
											if(mysqli_num_rows($autorelated_session)<1){
												$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
												VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());
											}
											elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
											}
									}

								}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";
						}
				}
				//new autorelated by jyoti end

			}


			//----------------------------------------------------------END-----------------------------------------------------------------------------

				$pdata['parent']=$data;



				$selectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.lat,tag_table.lng,tag_table.frame,tag_table.username FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.imageID not in ($allBlockImageID) AND iteration_table.iterationID IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE tag_table.linked_iteration='".$iterationId."')");

				if(mysqli_num_rows($selectChild) > 0)
				{

					while($childImageRow=mysqli_fetch_assoc($selectChild))
					{

						$data1['userID']=$childImageRow['username'];
						$data1['userName']=$newUsername[0];
						$data1['iterationID']=$childImageRow['iterationID'];
						$data1['imageID']=$childImageRow['imageID'];
						$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
						$data1['creatorUserID']=$childImageRow['userID'];
						$data1['iterationIgnore']=$childImageRow['iteration_ignore'];
						$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$newIterationID."'"));
						if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
						{
							$data1['adoptChild']=1; // adopt button enable
						}
						else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
						{
							$data1['adoptChild']=0; // adopt button disable
						}
						else
						{
							$data1['adoptChild']=2; //share button
						}
						if($childImageRow['delete_tag'] == 1 )
						{
							$data1['deleteTag']=1;
						}
						else
						{
							if($childImageRow['adopt_photo'] == 1 )
							{
								if($childImageRow['username'] == $loginUserId)
								{
									$data1['deleteTag']=1;
								}
								
							}
							else
							{
								$data1['deleteTag']=0;
							}
						}
						

						$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$childImageRow['imageID']."'  AND iterationID='".$childImageRow['iterationID']."' "));
						
						$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);


						if($getSessionIterationIDInfo1 > 0 )
						{

							$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);

								
							$data1['sessionIterationID']=$IterationIDInfo['iterationID'];
							$data1['sessionImageID']=$childImageRow['imageID'];



						}
						else
						{

							$data1['sessionIterationID']=$childImageRow['iterationID'];
							$data1['sessionImageID']=$childImageRow['imageID'];
						}



						$getIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id,stack_type FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");


						if(mysqli_num_rows($getIterationIDInfo)>0 )
						{
							$IterationIDInfo=mysqli_fetch_assoc($getIterationIDInfo);

							if($IterationIDInfo['stack_type']=='1')
							{

								$data1['apiUse']=1; //related

							}
							else
							{
								$data1['apiUse']=0; //normal
							}

						}
						else{
							$data1['apiUse']=0; //normal
						}
						
				
						

						$adoptPhotoType=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where iterationID='".$imagerow['iterationID']."' and adopt_iterationID='".$childImageRow['iterationID']."'"));
						$data1['adoptPhoto']=isset($adoptPhotoType['type'])?$adoptPhotoType['type']:'';


						$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.collageFrameType,image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,image_table.lat,image_table.lng,tb_user.username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,tb_user.id FROM image_table INNER JOIN tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$childImageRow['imageID']."'"));

						$data1['name']=$childImageRow['stacklink_name'];
						$data1['title']=$childImageRow['stacklink_name'];
                        $data1['typeID']=$childImageRow['imageID'];
						$data1['collageFrameType']=$selectChildImageData['collageFrameType']; //(image id)
						$data1['type']=$selectChildImageData['type'];
						if($selectChildImageData['type']==4)
						{
							$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
							$data1['ID']=$selectGrid[0];

						}
						if($selectChildImageData['type']==2)
						{
							$data1['ID']=$selectChildImageData['imageID'];
						}
						if($selectChildImageData['type']==5)
						{
							$data1['ID']=$selectChildImageData['imageID'];
						}
						if($selectChildImageData['type']==3)
						{
							$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'"));
							$data1['ID']=$selectMap[0];

						}
						$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
						$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';


						$data1['frame']=$childImageRow['frame'];
						$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat'] );
						$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']) ;

						$data1['userinfo']['userName']=$selectChildImageData['username'];
						$data1['userinfo']['userID']=$selectChildImageData['id'];
						if($selectChildImageData['profileimg']!='')
						{
							$data1['userinfo']['profileImg']=$selectChildImageData['profileimg'];
						}
						else
						{
							$data1['userinfo']['profileImg']='';
						}

						if($data1['adoptChild']==1 )
						{
							if(($loginUserId== $data1['creatorUserID'] || $loginUserId==$data['creatorUserID']))
							{
								if($childImageRow['iteration_ignore'] ==0)
								{
									
									$cdata['child'][]=$data1;
								}
							}

						}
						else
						{
							$cdata['child'][]=$data1;
						}

					}

				}



				if(empty($cdata))
				{
					$cdata['child']=array();
				}
				if(empty($pdata))
				{
					$pdata['parent']=array();
				}

				$totalData=array_merge($pdata,$cdata);

			}
		}
		else
		{
			echo json_encode(array('message'=>'There is no relevant data','success'=>0));
			exit;
		}


	}

	else
	{

		$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid FROM sub_iteration_table WHERE imgID='".$imageId."'  AND iterationID='".$iterationId."' "));
		
		$getSubIterationFirstInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID FROM sub_iteration_table WHERE imgID='".$imageId."'  and did = 1"));
		$originalUserName=getOwnerName1(1,$getSubIterationFirstInfo['iterationID'],$conn);
		$data['originalUserName']=$originalUserName;
		
		$creatorUserName=getOwnerName($getSubIterationImageInfo['fdid'],$imageId,$conn);
		$data['stackIteration']=$getSubIterationImageInfo['did'];
		$data['ownerName']=$creatorUserName;
		$cubeCount=storyThreadCount($imageId,$loginUserId,$iterationId,$conn);
		$data['storyThreadCount']=$cubeCount['storyThreadCount'];
		$data['cubeinfo']=($cubeCount['storyData'] == NULL ? '' : $cubeCount['storyData']);
        $lastSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".$imageId."'  order by id desc"));
		$data['countInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
		 $data['optionalCountInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
		$newUsername=mysqli_fetch_row(mysqli_query($conn,"SELECT username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
		AS profileimg,cover_image FROM tb_user WHERE id='$userId' "));

		$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$loginUserId."' and iterationID='".$iterationId."' and imageID= '".$imageId."' and status ='1' ");

		$getInfo=mysqli_query($conn,"SELECT assigned_name,imageID FROM grid_table WHERE imageID='$imageId' ") or die(mysqli_error());
		if(mysqli_num_rows($getInfo)>0)
		{

			while($row=mysqli_fetch_assoc($getInfo))
			{
				$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$iterationId."'");

				if(mysqli_num_rows($getImageInfo)>0)
				{
					$totalData=NULL;
					while($imagerow=mysqli_fetch_assoc($getImageInfo))
					{
						$data['userName']=$newUsername[0];
						if(mysqli_num_rows($stackNotify)>0)
						{
							$data['stackNotify']=1;
						}
						else
						{
							$data['stackNotify']=0;
						}

						$getBlockUserData=mysqli_query($conn,"SELECT status,id FROM `block_user_table` WHERE ((userID='".$loginUserId."'  and blockUserID='".$userId."') OR (userID='".$userId."'  and blockUserID='".$loginUserId."')) and status ='1'");
						if (mysqli_num_rows($getBlockUserData)>0)
						{
							$data['additionStatus']=1;  //check the iteration for block user(if blocker see the iteration then hide all the button. )
						}
						else
						{
							$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);
							$getBlockImageIDList = explode(",",$allBlockImageID);
							if (in_array($row['imageID'], $getBlockImageIDList))
							{
								$data['additionStatus']=1;
							}
							else
							{
								$data['additionStatus']=0;
							}
						}
						if($imagerow['allow_addition']=='0')
						{

						$data['allow_addition']=0;	 //means user can add anything on stack
						}
						else if($imagerow['allow_addition']=='1' and $imagerow['userID']==$loginUserId )
						{

						$data['allow_addition']=0;	 //means user can add anything on stack
						}
						else if($imagerow['allow_addition']=='1' and $imagerow['userID']!=$loginUserId )
						{

							$data['allowAddition']=1;	 //means user cannot add anything on stack
						}
						else
						{

							$data['allowAddition']=0;	 //means user can add anything on stack
						}
						$data['allowAdditionToggle']=($imagerow['allow_addition'] == NULL ? '' : $imagerow['allow_addition']) ;
						$data['userID']=$imagerow['userID'];
						//$data['name']=$imagerow['stacklink_name'];
						$data['typeID']=$row['imageID'];
						$data['imageID']=$row['imageID'];
						$data['ID']=$imageId;
						$data['caption']=($imagerow['caption'] == NULL ? '' : stripslashes($imagerow['caption']));
						$data['title']=$imagerow['stacklink_name'];
						$data['iterationID']=$imagerow['iterationID'];
						$data['creatorUserID']=$imagerow['userID'];
						$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$iterationId."'"));
						if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
						{
							$data['adoptChild']=1; // adopt button enable
						}
						else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
						{
							$data['adoptChild']=0; // adopt button disable
						}
						else if($userId != $loginUserId && $imagerow['adopt_photo']==0)
						{
							$data['adoptChild']=2;  //share button +no show edit button
						}
						else
						{
							$data['adoptChild']=3; //share button + show edit button
						}
						if($imagerow['delete_tag'] == 1 )
						{
							$data['deleteTag']=1;
						}
						else
						{
							if($imagerow['adopt_photo'] == 1 )
							{
								if($fetchcreatorUserID['userID'] == $loginUserId)
								{
									$data['deleteTag']=1;
								}
								
							}
							else
							{
								$data['deleteTag']=0;
							}
						}



						$collectIterationID=mysqli_query($conn,"SELECT iterationID FROM iteration_table  WHERE imageID='$imageId'");
						while($collectIterationIDS=mysqli_fetch_assoc($collectIterationID))
						{
							$iterationIDContain[]=$collectIterationIDS['iterationID'];

							$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$collectIterationIDS['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfo)>0)
							{
								while($cubeInformation=mysqli_fetch_assoc($cubeInfo))
								{

									$countIterationArray[]=$cubeInformation['id'];
								}
							}
						}

						if(count($countIterationArray)>0)
						{
							$data['cubeButton']=1;
						}
						else
						{
							$data['cubeButton']=0;
						}


						$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' and  userID ='".$loginUserId."' ");

						if(mysqli_num_rows($likeImage) > 0)
						{
						//
							$data['like']=1;

						}
						else
						{
							$data['like']=0;
						}

						//------------if stack is part of cube then does not use session --------------
			
						$gettingParentType = stacklinkIteration($conn,$breakactivestacklink[1],'type',$r1['imageID'],$imagerow['userID']);
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$breakactivestacklink[1]."'))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentType  == 6 || $gettingParentOfParentType ==6)
						{
							
							$newIterationID=$iterationId;
							$newUserID=$userId;
						}
						else
						{
							if($iterationButton==0)
							{
								$WhoStackLinkIterationID=mysqli_query($conn,"SELECT id from whostack_table inner join iteration_table on whostack_table. reuestedIterationID =iteration_table.iterationID   WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");

								if(mysqli_num_rows($WhoStackLinkIterationID)>0)
								{
									$getSessionIterationIDInfo =0;
									$getIterationIDWhoStackInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',whostackFdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

									$getIterationIDWhoStackInfo=mysqli_num_rows($getIterationIDWhoStackInfo);
								}

								else
								{

									$getIterationIDWhoStackInfo  = 0;
									$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

									$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);

								}

								if($getIterationIDWhoStackInfo > 0 and $iterationButton==0)
								{

									$whoStackIterationIDInfo=mysqli_fetch_assoc($getIterationIDWhoStackInfo);

									$getIterationIDWhoStackInfoUpdate=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  !find_in_set('".$getSubIterationImageInfo['did']."',fdid) and find_in_set('".$getSubIterationImageInfo['did']."',rdid) and user_id='".$loginUserId."' order by datetime desc limit 1");

									if(mysqli_num_rows($getIterationIDWhoStackInfoUpdate)>0)
									{
										$newIterationID=$whoStackIterationIDInfo['iterationID'];
										$newUserID=$whoStackIterationIDInfo['user_id'];
										

									}
									else
									{
										$newIterationID=$iterationId;
										$newUserID=$userId;
										

									}



								}


								else if($getSessionIterationIDInfo1 > 0 and $iterationButton==0)
								{

									$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);


									$newIterationID=$IterationIDInfo['iterationID'];
									$newUserID=$IterationIDInfo['user_id'];
										


								}
								else
								{

									$newIterationID=$iterationId;
									$newUserID=$userId;
								}
							}
							else
							{
								$newIterationID=$iterationId;
								$newUserID=$userId;
							}
						}
			
						$data['sessionIterationID']=$newIterationID;
						$data['sessionImageID']=$row['imageID'];


						if($imagerow['adopt_photo']==1)
						{
							$data['iterationButton']=0;
						}
						else
						{
							$data['iterationButton']=1;

						}

						$selectParentImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,frame,lat,lng,webUrl,location,addSpecification FROM image_table WHERE imageID='".$row['imageID']."'"));

						$data['url']=($selectParentImageData['url']!='')? $selectParentImageData['url']:'';
						$data['thumbUrl']=($selectParentImageData['thumb_url']!='')? $selectParentImageData['thumb_url']:'';
						$data['frame']=$selectParentImageData['frame'];
						$data['x']=($selectParentImageData['lat'] == NULL ? '' : $selectParentImageData['lat'] );
						$data['y']=($selectParentImageData['lng'] == NULL ? '' :  $selectParentImageData['lng']);
						//$data['image_comments']=$imagerow['image_comments']; //wrong change it
						$data['type']=$selectParentImageData['type'];
						$data['webUrl']=($selectParentImageData['webUrl'])?$selectParentImageData['webUrl']:'';
						$data['location']=($selectParentImageData['location'])?$selectParentImageData['location']:'';
						$data['addSpecification']=stripcslashes($selectParentImageData['addSpecification'])?$selectParentImageData['addSpecification']:'';


						//----------------------------------------------stacklinks array-------------------------------------
						$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$newIterationID."'"));
						if (strpos($getImageStacklinksInfo['stacklinks'], 'home') == false)
						{

							$words = explode(',',$getImageStacklinksInfo['stacklinks']);

							foreach ($words as $word)
							{

								$result = explode('/',$word);
								$getcount=mysqli_fetch_assoc(mysqli_query($conn,"select imageID, count(iterationID) as count_iteration  from iteration_table where imageID=(SELECT imageID FROM `iteration_table` where  iterationID='".$result[1]."' and imageId='".$row['imageID']."')"));
								$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type FROM image_table WHERE imageID='".$getcount['imageID']."'"));

								if($stackFetchFromImageTable['type']!=6)
								{

									$arr['stacklink']=$word;
									$stackLinkData[]=$arr;
								}
								else
								{
									$fetchActiveStackName = $result[0].'/home';
									$arr['stacklink']=$result[0].'/home';
									$stackLinkData[]=$arr;
								}

							}



							foreach($stackLinkData as $fetchStackLink) {
							$ids[] = $fetchStackLink['stacklink'];
							}
							$stackLinksArr=$ids;
						}
						else
						{

							$arr = array();
							$reverseString =$getImageStacklinksInfo['stacklinks'];
							$words = explode(',',$reverseString);
							foreach ($words as $word)
							{
								$getcount=mysqli_fetch_assoc(mysqli_query($conn,"SELECT imageID,count(iterationID) as count_iteration FROM `iteration_table` where LOCATE('$word',stacklinks) and iterationID<='".$newIterationID."' and imageId='".$row['imageID']."' "));
								$result = explode('/',$word);
								$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$result[1]."')"));



								if($stackFetchFromImageTable['type']!=6)
								{

									$arr['stacklink']=$word;
									$stackLinkData[]=$arr;
								}
								else
								{
									$fetchActiveStackName = $result[0].'/home';
									$arr['stacklink']=$result[0].'/home';
									$stackLinkData[]=$arr;
								}


							}


							foreach($stackLinkData as $fetchStackLink) {
							$ids[] = $fetchStackLink['stacklink'];
							}
							$stackLinksArr=$ids;
						}


						if(count($stackLinksArr) > 1)
				{

					if(count($stackLinksArr)>=2 and $iterationButton==0)
					{

						$fetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM user_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."' ");

						$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

						$createNewDid = $linkingInfo['did'];
						$createNewFdid = $linkingInfo['fdid'];
						$createNewRdid = $linkingInfo['rdid'];
						$createNewWhoStackFdid = $linkingInfo['whostackFdid'];

						if(mysqli_num_rows($fetchIterationIDInfo)<=0)
						{

							$unixTimeStamp=date("Y-m-d"). date("H:i:s");

							$insertUserTable=mysqli_query($conn,"INSERT INTO user_table(iterationID,user_id,imageID,
							did,fdid,rdid,whostackFdid,date,time,datetime) VALUES('".$newIterationID."',
							'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','$createNewWhoStackFdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')");


						}
						else
						{
							$unixTimeStamp=date("Y-m-d"). date("H:i:s");
							mysqli_query($conn,"update user_table set whostackFdid='".$createNewWhoStackFdid."', date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."' , stack_type='0' where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."'");
						}

					}





					$WhoStackLinkIterationID=mysqli_query($conn,"SELECT distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name, whostack_table.datetime FROM `iteration_table`  inner join whostack_table on iteration_table.iterationID = whostack_table. reuestedIterationID    WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");


					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{

						while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
						{


							$whostackLinksArr[]=$fetchWhoStackLinkIterationID['who_stacklink_name'];


						}
					}
					if(!empty($whostackLinksArr)>0) // remove who stack data here
					{
						$whostackLinksArrValue=array_reverse(array_diff($whostackLinksArr,$stackLinksArr));
					}



					if(!empty($whostackLinksArrValue))
					{

						foreach($whostackLinksArrValue  as $stacklinkCount=>$stackminiArr)
						{
							$stackArrInfoData=explode('/',$stackminiArr);


							$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
							AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
							$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
							$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
							$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
							$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
							if (in_array($stackUserInfo['id'], $getBlockUserList))
							{
								$stacked['stackuserdata']['blockUser']=1;
							}
							else
							{

								$stacked['stackuserdata']['blockUser']=0;
							}

							$stackArr1[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];


							if($stackArrInfoData[1]=='home')
							{
								$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}

								if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}

								$stackedRelated['stackrelateddata']['ID']='';
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['name']='';
								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackedRelated['stackrelateddata']['frame']='';
								$stackedRelated['stackrelateddata']['x']='';
								$stackedRelated['stackrelateddata']['y']='';
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['imageComments']='';
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{

								if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}
								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}

								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
								$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
								$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
								$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
								$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
								$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
								$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
								$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']) ;
								$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
								$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
									$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
									$stackedRelated['stackrelateddata']['profileStory']="0";
								}
								if($stackDataFetchFromImageTable['type']==2)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
									//image Data


									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}
								if($stackDataFetchFromImageTable['type']==3)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

								}
								if($stackDataFetchFromImageTable['type']==4)
								{


									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
								}
								if($stackDataFetchFromImageTable['type']==5)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
								}

								if($stackDataFetchFromImageTable['type']==6)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
								}


								$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

							}



						}
					}
					$stacklink['stacklinks']=array_reverse($stackArr1);

					foreach($stackLinksArr as $stacklinkCount=>$stackminiArr)
					{
						$stackArrInfoData=explode('/',$stackminiArr);

						$mainStackLink = explode(',', trim($imagerow['stacklinks'])); // check session or original stack of that stack.

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
						/**********if child of other ownername should show**********/

							if($stackArrInfoData[1]!='home'){
								$stackOwnerInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,CASE WHEN u.profileimg IS NULL OR u.profileimg = '' THEN '' 
									WHEN u.profileimg LIKE 'albumImages/%'  THEN concat( '$serverurl', u.profileimg ) ELSE
									u.profileimg
									END
									AS profileimg,img.type FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$stackArrInfoData[1]."'"));

								$stacked['stackuserdata']['userName']=$stackOwnerInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackOwnerInfo['profileimg']!='')?$stackOwnerInfo['profileimg']:'';

							}
							else{

								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							}
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}

						if(in_array($stackminiArr, $mainStackLink))
						{
							$mainStackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain original stack related that stack.
						}
						else
						{
							$sessionstackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain all session stacklink.
						}

						//$stackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];

						if($stackArrInfoData[1]=='home')
						{
							if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}
							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
							$stackedRelated['stackrelateddata']['ID']='';
							$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['parentName']='';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['imageComment']='';
							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['parentType']="";
					
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
							}

							if(in_array($stackminiArr, $mainStackLink))
							{
								$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];	// contain original stack related that stack.
							}
							else
							{
								$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];// contain all session stacklink.
							}

							//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{
							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
							if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
							{
								$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
								if($stackDataFetchFromImageTableData['type'] == 1)
								{
									$stackedRelated['stackrelateddata']['parentType']='';
								}
								else
								{
									$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
								}
							}
							else
							{
								$stackedRelated['stackrelateddata']['parentType']="";
								
							}
							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));


							if($stackminiArr==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}
							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
							$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng'] );
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
							$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
							}
							
							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));
							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								//image Data
								

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
							}
							if($stackDataFetchFromImageTable['type']==4)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];

							}
							if($stackDataFetchFromImageTable['type']==5)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$stackDataFetchFromImageTable['imageID']."'")) or die(mysqli_error());

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							}
							if($stackDataFetchFromImageTable['type']==6)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}
							if($stackDataFetchFromImageTable['type']==7)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

							
								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}

							if(in_array($stackminiArr, $mainStackLink))
							{
								$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{
								$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}



							//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

						}



						if(!empty($stackArr1))  //means whostack data exist.
						{
							if(!empty($sessionstackArr))
							{
								//whostack , session, main stacklink exist
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr,$mainStackArr)); // insert to reverse order
							}
							else
							{
								//whostack, main stacklink exist but session data does not exist.
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$mainStackArr));
							}

						}
						else{   //means whostack data does not exist.

							if(!empty($sessionstackArr))
							{
								//sesion data exist.

								$data['stacklinks']=array_reverse(array_merge($sessionstackArr,$mainStackArr));
							}
							else
							{
								//sesion data does not exist.
								$data['stacklinks']=array_reverse(array_merge($mainStackArr));
							}

						}

					}
				}
				else
				{


					$getAllWhoStackLink=mysqli_query($conn,"SELECT reuestedIterationID FROM whostack_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$imagerow['iterationID']."'  AND  requestStatus =2 ");

					$commaVariable='';
					if(mysqli_num_rows($getAllWhoStackLink)>0)
					{
						while($allWhoStackLink=mysqli_fetch_assoc($getAllWhoStackLink))
						{
							$allWhoStackIterationID.=$commaVariable.$allWhoStackLink['reuestedIterationID'];
							$commaVariable=',';
						}
					}


					$WhoStackLinkIterationID=mysqli_query($conn,"select * from (SELECT  distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name , iterationID FROM `iteration_table` where  iterationID in ($allWhoStackIterationID)) as stack_link_table where who_stacklink_name!='".$stackLinksArr[0]."'  ORDER BY FIELD(iterationID,$allWhoStackIterationID) desc ");

					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{
						while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
						{

							$stackArrInfoData=explode('/',$fetchWhoStackLinkIterationID['who_stacklink_name']);

							$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
							AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

							$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
							$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
							$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
							$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
							if (in_array($stackUserInfo['id'], $getBlockUserList))
							{
								$stacked['stackuserdata']['blockUser']=1;
							}
							else
							{

								$stacked['stackuserdata']['blockUser']=0;
							}
							$stackArr['stackUserInfo']=$stacked['stackuserdata'];

							if($stackArrInfoData[1]=='home')
							{
								if($fetchWhoStackLinkIterationID['who_stacklink_name'] ==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}

								$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
								$stackedRelated['stackrelateddata']['ID']='';
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['parentName']='';
								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackedRelated['stackrelateddata']['frame']='';
								$stackedRelated['stackrelateddata']['x']='';
								$stackedRelated['stackrelateddata']['y']='';
								$stackedRelated['stackrelateddata']['imageComment']='';
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['parentType']="";
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
								}
								$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{
								if($fetchWhoStackLinkIterationID['who_stacklink_name'] ==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
								$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
								if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
								{
									$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
									if($stackDataFetchFromImageTableData['type'] == 1)
									{
										$stackedRelated['stackrelateddata']['parentType']='';
									}
									else
									{
										$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
									}
								}
								else
								{
									$stackedRelated['stackrelateddata']['parentType']="";
									
								}
								$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
								$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

								$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
								$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
								$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
								$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
								$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
								$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat']);
								$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' : $stackDataFetchFromTagTable['lng']);
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
								$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
								}

								if($stackDataFetchFromImageTable['type']==2)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}
								if($stackDataFetchFromImageTable['type']==3)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];

								}
								if($stackDataFetchFromImageTable['type']==4)
								{

									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
								}
								if($stackDataFetchFromImageTable['type']==5)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
								}
								if($stackDataFetchFromImageTable['type']==6)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
								}



								$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

							}

							$data['stacklinks'][]=array_reverse($stackArr);


						}


					}
					$stackArrInfoData=explode('/',$stackLinksArr[0]);

					$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
					AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

					$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
					$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
					$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
					$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
					$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
					if (in_array($stackUserInfo['id'], $getBlockUserList))
					{
						$stacked['stackuserdata']['blockUser']=1;
					}
					else
					{

						$stacked['stackuserdata']['blockUser']=0;
					}
					$stackArr['stackUserInfo']=$stacked['stackuserdata'];

					if($stackArrInfoData[1]=='home')
					{

						if($stackLinksArr[0] ==$stackLinkType)
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=2;
						}
						$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
						$stackedRelated['stackrelateddata']['activeStacklink']=1;
						$stackedRelated['stackrelateddata']['ID']='';
						$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
						$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						$stackedRelated['stackrelateddata']['parentName']='';
						$stackedRelated['stackrelateddata']['url']='';
						$stackedRelated['stackrelateddata']['thumbUrl']='';
						$stackedRelated['stackrelateddata']['frame']='';
						$stackedRelated['stackrelateddata']['x']='';
						$stackedRelated['stackrelateddata']['y']='';
						$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
						$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
						$stackedRelated['stackrelateddata']['imageComment']='';
						$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						$stackedRelated['stackrelateddata']['parentType']="";
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$stackedRelated['stackrelateddata']['cubeID']=0;
						}
						$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
					}
					else
					{
						$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

						$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

						if($stackLinksArr[0] ==$stackLinkType)
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['originateStackLink']=2;
						}
						$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
						if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
						{
							$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
							if($stackDataFetchFromImageTableData['type'] == 1)
							{
								$stackedRelated['stackrelateddata']['parentType']='';
							}
							else
							{
								$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
							}
						}
						else
						{
							$stackedRelated['stackrelateddata']['parentType']="";
							
						}
						$stackedRelated['stackrelateddata']['activeStacklink']=1;
						$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
						$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
						$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
						$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
						$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
						$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? ''  :  $stackDataFetchFromImageTable['lat'] ) ;
						$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
						$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
						$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
						$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
						$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$stackedRelated['stackrelateddata']['cubeID']=0;
						}
						if($stackDataFetchFromImageTable['type']==2)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
						}
						if($stackDataFetchFromImageTable['type']==3)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
						}
						if($stackDataFetchFromImageTable['type']==4)
						{

							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$stackDataFetchFromImageTable['imageID']."'"));

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
						}
						if($stackDataFetchFromImageTable['type']==5)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$stackDataFetchFromImageTable['imageID']."'")) or die(mysqli_error());

							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
						}
						if($stackDataFetchFromImageTable['type']==6)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']='';
						}

						$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

					}
					$data['stacklinks'][]=array_reverse($stackArr);
				}
				//--------------------------------------------------------------------------------------------------

				$data['userinfo']['userName']=$newUsername[0];
				$data['userinfo']['userID']=$newUserID;
				if($newUsername[1]!='')
				{
					$data['userinfo']['profileImg']=$newUsername[1];
				}
				else
				{
					$data['userinfo']['profileImg']='';
				}


					/*--------------------------  SWAP SIBLING child -----------------------------------*/


			//new autorelated Fetch by jyoti
			//Add the auto related Linking code here.

		 	if( $relatedThreadID=='' && $autorelatedID==''){



				$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' ORDER BY viewDate DESC limit 1");
					if(mysqli_num_rows($autorelated_session)>0){

						$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
						$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
						$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
						$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
						$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
					}
					else
					{



						 $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID,fdid,did FROM sub_iteration_table WHERE imgID='".$imageId."'  AND did=1 "));



							$autorelated_session1=mysqli_query($conn,"SELECT * FROM `new_auto_related` where   FIND_IN_SET('".$getSubIterationImageInfo['iterationID']."',autorelated)");
							if(mysqli_num_rows($autorelated_session1)>0){

								$autorelated_session2=mysqli_query($conn,"SELECT * FROM `new_auto_related` where iterationID ='".$iterationId."' ");
								if(mysqli_num_rows($autorelated_session2)>0){


								$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
								$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
								$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
								$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
								$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
								}
							}

						}
					}




			}
			if( $relatedThreadID=='' && $autorelatedID=='')
			{


				//new autorelated by jyoti

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE new_auto_related.iterationID ='".$iterationId."' and new_auto_related.imageID ='".$imagerow['imageID']."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){


						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						
						$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
					   $data['countInfo']=count($arrayAuto);
						array_unshift($arrayAuto,$iterationId);


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$fetchNewAutoRelated['iterationID'];
						$currentStack['imageID']=$fetchNewAutoRelated['imageID'];
						$currentStack['forwordrelatedID']=1;
						$currentStack['backwordrelatedID']='';
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';
						$data['CurrentStack']=$currentStack;

						$rightchild['iterationID']=$arrayAuto[1];
						$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[1]."'"));

						$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
						$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
						$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
						$rightchild['userID']=$userId;
						$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
						$rightchild['title']=$getStackImageID['stacklink_name'];
						$rightchild['threadID']=$fetchNewAutoRelated['threadID'];
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$rightchild['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$rightchild['cubeID']=0;
						}

						$data['autorealtedParentStackName']=$imagerow['stacklink_name'];
						$data['rightChild']=$rightchild;
						$data['leftChild']=array();
						$data['optionalChild']=array();

						//iteracation check in database

						$rId = $rightchild['iterationID'];
						$getrightchild =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $rId"));
						if(empty($getrightchild)){

							$data['rightChild']="";

						}

					}
				}

				else
				{

					$data['optionalChild']=array();
					$data['CurrentStack']=array();
					$data['rightChild']=array();
					$data['leftChild']=array();
				}
				/****Delete all back auto_related ****/
				$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'"));
				/**** end Delete all back auto_related ****/
			}
			else if($optionalOf=='' && $optionalIndex=='' && $relatedThreadID!='')
			{

				$unixTimeStamp=date("Y-m-d"). date("H:i:s");

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){
						$arrayIndex=$autorelatedID;

						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
					$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						}
						
						//$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
						 
						if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);

						}
						else{
							$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNewAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';

						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							$lIndex="";
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex="";
							$lIndex=$leftIndex;
						}
						else{ //if current is neigther last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';


						//Right child Start
						if($rIndex !== ""){
							$rightchild['iterationID']=$arrayAuto[$rIndex];

							if($rightchild['iterationID'] == $iterationId ){
								$rightchild = "";

							}else{



							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));


							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));


							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							$rightchild['userID']=$userId;
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}


							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];

							//Right child End
						 }

						}else{ $rightchild = ""; }

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;

							if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						if($lIndex !==""){
						//Left Child start
						$leftchild['iterationID']=$arrayAuto[$lIndex];
						$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

						$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));


						$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
						$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
						$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
						$leftchild['userID']=$userId;
						$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
						$leftchild['title']=$getStackImageID1['stacklink_name'];
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[$lIndex]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$leftchild['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$leftchild['cubeID']=0;
						}


						$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE threadID ='".$relatedThreadID."' and userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){

							$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

							if($fetchNewAutoRelated['threadID']!=''){
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
							}
							else
							{
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$lIndex;
							}
						}
						else
						{
							$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
							$leftchild['autoRelatedID']=$lIndex;
						}
						//$leftchild['threadID']=$relatedThreadID;
						//$leftchild['autoRelatedID']=$lIndex;
									//Left Child End
						$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
						if($data['autorealtedParentStackName']){
							$parent_name = $data['autorealtedParentStackName'];
						}else{
							$parent_name = '';
						}
						//Left Child End

						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];

							if($iteration_count==0){

									$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
									VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
								}
						}


						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));


						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iteration_id']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$backRelated['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$backRelated['cubeID']=0;
						}



					} else { $leftchild = ""; }

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						//$data['leftChild']=$leftchild;

						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}
						if($backRelated['iterationID']  == $arrayAuto[$lIndex]){
							$data['backRelated']=$backRelated;
						}else{
							$data['backRelated']="";
							/****Delete all back auto_related ****/
							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'"));
							/**** end Delete all back auto_related ****/


						}

						//Optional child autorelated start

						//500 entries should be maintained only by jyoti

						$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
						if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){
							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and   order by viewDate desc limit 1");

							$result = mysqli_fetch_assoc($autorelated_session);

							if(mysqli_num_rows($autorelated_session)<1){
									$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());
							}
							elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
							else
							{
								$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
						}
						else{

							$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
							if($autorelated_session_delete){
								$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());

							}
						}

						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								//echo 'ffffffffffffff'.$optionalCount=count($arrayOptionalAuto);
								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['userID']=$userId;
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$optionalchild['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$optionalchild['cubeID']=0;
									}

									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}



								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightOptional['cubeID']=0;
								}
								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftOptional['cubeID']=0;
								}
								$data['leftOptional']="";

								//Check iterationID exists in database
								$opid = $optionalchild['iterationID'];
								$ropid = $rightOptional['iterationID'];

								$queryGetitreaction =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $opid"));
								if(empty($queryGetitreaction)){
									$data['optionalChild']="";

								}
								$queryGetrightoption =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $ropid"));
								if(empty($queryGetitreaction)){
									$data['rightOptional']="";

								}


							}
						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";
						}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";
						}
				}
				//new autorelated by jyoti end

			}
			else
			{


				//new autorelated by jyoti
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");
				if($optionalOf==$iterationId){


					$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");

			 		$arrayIndex=$optionalIndex-1;

			 	}
			 	else{
			 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
			 		$arrayIndex=$autorelatedID;

			 	}
				if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

					$fetchNormalAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
					if($fetchNormalAutoRelated['autorelated']!=''){

						$arrayAuto=explode(',', $fetchNormalAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNormalAutoRelated['iterationID']."'"));
						
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						}
						//$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
						 
                       if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);

						}
						else{
								$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNormalAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						if($arrayIndex<0){
							$arrayIndex=$indexCount-1;
						}
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';
						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							//$lIndex=$indexCount-1;
							$lIndex='';
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex='';
							//$rIndex=0;
							$lIndex=$leftIndex;
						}
						else{ //if current is neigth last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}



						$currentStack['threadID']=$relatedThreadID;
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']=$optionalIndex;
						$currentStack['optionalOf']=$optionalOf;

						if($rIndex!==""){
							//Right child Start
							$rightchild['iterationID']=$arrayAuto[$rIndex];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							$rightchild['userID']=$userId;
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}

							//Right child End
						}else{
							$rightchild = "";
						}

							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;



						if($lIndex!==""){
							//Left Child start
							$leftchild['iterationID']=$arrayAuto[$lIndex];
							$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

							$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
							$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
							$leftchild['userID']=$userId;
							$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
							$leftchild['title']=$getStackImageID1['stacklink_name'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$leftchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$leftchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$leftchild['cubeID']=0;
							}
							/* $leftchild['threadID']=$relatedThreadID;
							$leftchild['autoRelatedID']=$lIndex; */

							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
							if(mysqli_num_rows($autorelated_session)>0){

								$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

								if($fetchNewAutoRelated['threadID']!=''){
									$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								    $leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
								}
								else
								{
									$leftchild['threadID']=$relatedThreadID;
									$leftchild['autoRelatedID']=$lIndex;
								}
							}
							else
							{
								$leftchild['threadID']=$relatedThreadID;
								$leftchild['autoRelatedID']=$lIndex;
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							if($data['autorealtedParentStackName']){
								$parent_name = $data['autorealtedParentStackName'];
							}else{
								$parent_name = '';

							}
							//Left Child End
						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];


							if($iteration_count == 0){
								// echo "INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								// VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')";die;

								$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
							}
						}

						if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));

						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$backRelated['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$backRelated['cubeID']=0;
						}

						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}

						}else{
							$leftchild = "";
						}

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						// $data['rightChild']=$rightchild;
						// $data['leftChild']=$leftchild;
						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}



						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){
							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);
							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								$optionalCount=count($arrayOptionalAuto);


								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$optionalchild['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$optionalchild['cubeID']=0;
									}
									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}


								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$rightOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightOptional['cubeID']=0;
								}

								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftOptional['cubeID']=0;
								}
								if($optionalOf==''){
									$data['leftOptional']=$leftOptional;
								}else{
									$data['leftOptional']="";
								}

								$optionalSession=1;
								$optionalOfSession=$optionalOf;
								$optionalIndexSession=$optionalIndex;
								$optionalRightIndex=1;
								$optionalLeftIndex=$optionalCount-1;
								$optionalRightID=$arrayOptionalAuto[1];
								$optionalLeftID=$arrayOptionalAuto[$optionalCount-1];

							}

						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";

								$optionalSession=0;
								$optionalOfSession='';
								$optionalIndexSession='';
								$optionalRightIndex='';
								$optionalLeftIndex='';
								$optionalRightID='';
								$optionalLeftID='';
						}
							//500 entries should be maintained only by jyoti

								$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
								if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){

									$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");
									$result = mysqli_fetch_assoc($autorelated_session);

									if(mysqli_num_rows($autorelated_session)<1){

											$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());


									}
									//$optionalOf=='' && $optionalIndex
									elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
									else
									{
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
								}
								else{

									$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
									if($autorelated_session_delete){
										$autorelated_session==mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID =='".$relatedThreadID."' order by viewDate desc limit 1");
											$result = mysqli_fetch_assoc($autorelated_session);
											if(mysqli_num_rows($autorelated_session)<1){
												$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
												VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());
											}
											elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
											}
									}

								}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";
						}
				}
				//new autorelated by jyoti end

			}

			//----------------------------------------------------------END-----------------------------------------------------------------------------

						$pdata['parent']=$data;


						$selectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.lat,tag_table.lng,tag_table.frame,tag_table.username FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.imageID not in ($allBlockImageID) and iteration_table.iterationID IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE iteration_table.threadID='".$imagerow['threadID']."' AND  tag_table.linked_iteration='".$iterationId."')");

						if(mysqli_num_rows($selectChild) > 0)
						{

							while($childImageRow=mysqli_fetch_assoc($selectChild))
							{

								$data1['userID']=$childImageRow['username'];
								$data1['userName']=$newUsername[0];
								$data1['iterationID']=$childImageRow['iterationID'];
								$data1['creatorUserID']=$childImageRow['userID'];
								$data1['iterationIgnore']=$childImageRow['iteration_ignore'];
								if($childImageRow['adopt_photo']==0)
								{
									$data1['adoptChild']=0;
								}
								else
								{
									$data1['adoptChild']=1;
								}


								$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$childImageRow['imageID']."'  AND iterationID='".$childImageRow['iterationID']."' "));

								
								$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

								$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);


								if($getSessionIterationIDInfo1 > 0 )
								{

									$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);

										
									$data1['sessionIterationID']=$IterationIDInfo['iterationID'];
									$data1['sessionImageID']=$childImageRow['imageID'];



								}
								else
								{

									$data1['sessionIterationID']=$childImageRow['iterationID'];
									$data1['sessionImageID']=$childImageRow['imageID'];
								}


								$getIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id,stack_type FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");


								if(mysqli_num_rows($getIterationIDInfo)>0 )
								{
									$IterationIDInfo=mysqli_fetch_assoc($getIterationIDInfo);

									if($IterationIDInfo['stack_type']=='1')
									{

										$data1['apiUse']=1; //related

									}
									else
									{
										$data1['apiUse']=0; //normal
									}

								}
								else{
									$data1['apiUse']=0; //normal
								}
								
								if($childImageRow['delete_tag'] == 1 )
								{
									$data1['deleteTag']=1;
								}
								else
								{
									if($childImageRow['adopt_photo'] == 1 )
									{
										if($childImageRow['username'] == $loginUserId)
										{
											$data1['deleteTag']=1;
										}
										
									}
									else
									{
										$data1['deleteTag']=0;
									}
								}
							
								

								$adoptPhotoType=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where iterationID='".$imagerow['iterationID']."' and adopt_iterationID='".$childImageRow['iterationID']."'"));
								$data1['adoptPhoto']=isset($adoptPhotoType['type'])?$adoptPhotoType['type']:'';


								$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.collageFrameType,image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,image_table.lat,image_table.lng,image_url_table.assigned_name,tb_user.username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg,tb_user.id FROM image_table INNER JOIN image_url_table ON image_table.imageID=image_url_table.imageID INNER JOIN tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$childImageRow['imageID']."'"));

								//$data1['name']=$childImageRow['assigned_name'];
								$data1['title']=$childImageRow['stacklink_name'];
								$data1['typeID']=$childImageRow['imageID']; //(image id)
								$data1['type']=$selectChildImageData['type'];
								$data1['imageID']=$childImageRow['imageID'];
								  $data1['collageFrameType']=$selectChildImageData['collageFrameType'];
								$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
								if($selectChildImageData['type']==4)
								{

									$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
									$data1['ID']=$selectChildImageData['imageID'];

								}
								if($selectChildImageData['type']==2)
								{

									$data1['ID']=$selectChildImageData['imageID'];
								}
								if($selectChildImageData['type']==5)
								{

									$data1['ID']=$selectChildImageData['imageID'];
								}
								if($selectChildImageData['type']==3)
								{

									$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'"));
									$data1['ID']=$selectMap[0];

								}
								$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
								$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';

								$data1['frame']=$childImageRow['frame'];
								$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat'] ) ;
								$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);

								$data1['userinfo']['userName']=$selectChildImageData['username'];
								$data1['userinfo']['userID']=$selectChildImageData['id'];
								if($selectChildImageData['profileimg']!='')
								{
									$data1['userinfo']['profileImg']=$selectChildImageData['profileimg'];
								}
								else
								{
									$data1['userinfo']['profileImg']='';
								}
								if($data1['adoptChild']==1 )
								{
									if(($loginUserId== $data1['creatorUserId'] || $loginUserId==$data['creatorUserId']))
									{
										$cdata['child'][]=$data1;
									}

								}
								else
								{
									$cdata['child'][]=$data1;
								}

							}

						}


						if(empty($cdata))
						{
							$cdata['child']=array();
						}
						if(empty($pdata))
						{
							$pdata['parent']=array();
						}

						$totalData=array_merge($pdata,$cdata);

					}
				}
				else
				{
					echo json_encode(array('message'=>'There is no relevant data','success'=>0));
					exit;
				}
			}

		}
	}

}


if($type==7) //viddeo
{

	$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".$imageId."'  AND iterationID='".$iterationId."' "));



	$data['stackIteration']=$getSubIterationImageInfo['did'];
	$getCopiesRdidCount=count(explode(',',$getSubIterationImageInfo['rdid']));
	$lastSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".$imageId."'  order by id desc"));
  // $data['countInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
  // $data['optionalCountInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
  
	$getSubIterationFirstInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID FROM sub_iteration_table WHERE imgID='".$imageId."'  and did = 1"));
	$originalUserName=getOwnerName1(1,$getSubIterationFirstInfo['iterationID'],$conn);
	$data['originalUserName']=$originalUserName;
	
	$creatorUserName=getOwnerName($getSubIterationImageInfo['fdid'],$imageId,$conn);
	$cubeCount=storyThreadCount($imageId,$loginUserId,$iterationId,$conn);
	$data['ownerName']=$creatorUserName;
	$data['storyThreadCount']=$cubeCount['storyThreadCount'];
	$data['contributorSession']=$contributorSession;
	$data['cubeinfo']=($cubeCount['storyData'] == NULL ? '' : $cubeCount['storyData'] ) ;

	$newUsername=mysqli_fetch_row(mysqli_query($conn,"SELECT username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
	AS profileimg,cover_image FROM tb_user WHERE id='".$userId."' "));



	$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$loginUserId."' and iterationID='".$iterationId."' and imageID= '".$imageId."' and status ='1'"); //active the notify button or not

	$getInfo=mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='$imageId' ") or die(mysqli_error());


	if(mysqli_num_rows($getInfo)>0)
	{

		$row=mysqli_fetch_assoc($getInfo);

			//query use for session
		$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$iterationId."'");
		if(mysqli_num_rows($getImageInfo)>0)
		{
			$totalData=NULL;
			$imagerow=mysqli_fetch_assoc($getImageInfo);

			$data['userName']=$newUsername[0];

			if(mysqli_num_rows($stackNotify)>0)
			{
				$data['stackNotify']=1;
			}
			else
			{
				$data['stackNotify']=0;
			}


			$cubeInfoData=mysqli_query($conn,"SELECT id FROM cube_table WHERE profilestory = 1  and  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
			if(mysqli_num_rows($cubeInfoData)>0)
			{
				$data['profileStoryItem']=1;
			}
			else
			{
				$data['profileStoryItem']=0;
			}



			$getBlockUserData=mysqli_query($conn,"SELECT status,id FROM `block_user_table` WHERE ((userID='".$loginUserId."'  and blockUserID='".$userId."') OR (userID='".$userId."'  and blockUserID='".$loginUserId."')) and status ='1'");
			if (mysqli_num_rows($getBlockUserData)>0)
			{
				$data['additionStatus']=1;  //check the iteration for block user(if blocker see the iteration then hide all the button. )
			}
			else
			{
				$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);
				$getBlockImageIDList = explode(",",$allBlockImageID);
				if (in_array($row['imageID'], $getBlockImageIDList))
				{
					$data['additionStatus']=1;
				}
				else
				{
					$data['additionStatus']=0;
				}
			}

			if($imagerow['allow_addition']=='0')
			{

				$data['allowAddition']=0;	 //means user can add anything on stack
			}
			else if($imagerow['allow_addition']=='1' and $imagerow['userID']==$loginUserId )
			{

				$data['allowAddition']=0;	 //means user can add anything on stack
			}
			else if($imagerow['allow_addition']=='1' and $imagerow['userID']!=$loginUserId )
			{

				$data['allowAddition']=1;	 //means user cannot add anything on stack
			}
			else
			{

				$data['allowAddition']=0;	 //means user can add anything on stack
			}

			$adoptPhotoType=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where user_id='".$loginUserId."' and adopt_iterationID='".$iterationId."' and status=1"));

			if($adoptPhotoType['type']==1)
			{
				$data['adoptPhoto']=1; //down
			}
			else if($adoptPhotoType['type']==2){
				$data['adoptPhoto']=2; //up level
			}
			else{
				$data['adoptPhoto']=0;
			}


			$data['allowAdditionToggle']=($imagerow['allow_addition'] == NULL ? '' : $imagerow['allow_addition']);
			$data['userID']=$imagerow['userID'];
			//$data['name']=$imagerow['stacklink_name'];
			$data['ID']=$imageId;
			$data['title']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
			$data['typeID']=$row['imageID'];
			$data['imageID']=$row['imageID'];
			$data['iterationID']=$imagerow['iterationID'];
			$data['creatorUserID']=$imagerow['userID'];
			$data['caption']=($imagerow['caption'] == NULL ? '' : stripslashes($imagerow['caption']));
			$collectIterationID=mysqli_query($conn,"SELECT iterationID FROM iteration_table  WHERE imageID='$imageId'");
			while($collectIterationIDS=mysqli_fetch_assoc($collectIterationID))
			{
				$iterationIDContain[]=$collectIterationIDS['iterationID'];

				$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$collectIterationIDS['iterationID']."',tags) ") or die(mysqli_error());
				if(mysqli_num_rows($cubeInfo)>0)
				{
					while($cubeInformation=mysqli_fetch_assoc($cubeInfo))
					{

						$countIterationArray[]=$cubeInformation['id'];
					}
				}
			}

			if(count($countIterationArray)>0)
			{
				$data['cubeButton']=1; //cube button
			}
			else
			{
				$data['cubeButton']=0;
			}

			$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' and  userID ='".$loginUserId."' ");

			if(mysqli_num_rows($likeImage) > 0)
			{
				$data['like']=1;
			}
			else
			{
				$data['like']=0;
			}
					$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$iterationId."'"));


			if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
			{
				$data['adoptChild']=1; // adopt button enable
			}
			else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
			{
				$data['adoptChild']=0; // adopt button disable
			}
			else if($userId != $loginUserId && $imagerow['adopt_photo']==0)
			{
				
				if($cubeCount['storyData']!= '' and  $imagerow['autoapprove'] ==0)
				{
					$data['adoptChild']=3; //show share button + show edit button
				}
				else
				{
					$data['adoptChild']=2;  //show share button +no show edit button
				}
			}
			else
			{
				
				if($imagerow['userID'] == $loginUserId)
				{
					$data['adoptChild']=3; //show share button + show edit button
					
				}
				else
				{
					$data['adoptChild']=2;
					
				}
			}
			if($imagerow['delete_tag'] == 1 )
			{
				$data['deleteTag']=1;
			}
			else
			{
				if($imagerow['adopt_photo'] == 1 )
				{
					if($fetchcreatorUserID['userID'] == $loginUserId)
					{
						$data['deleteTag']=1;
					}
					
				}
				else
				{
					$data['deleteTag']=0;
				}
			}
			$selectParentImageData1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as imagecount FROM `comment_table` where  imageID='".$row['imageID']."'"));
            $data['imageComment']=$selectParentImageData1['imagecount'];
			$selectParentImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
			WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,frame,lat,lng,webUrl,location,addSpecification FROM image_table WHERE imageID='".$row['imageID']."'"));
			$data['url']=($selectParentImageData['url']!='')? $selectParentImageData['url']:'';
			$data['thumbUrl']=($selectParentImageData['thumb_url']!='')? $selectParentImageData['thumb_url']:'';
			$data['frame']=$selectParentImageData['frame'];
			$data['x']=($selectParentImageData['lat'] == NULL ? '' : $selectParentImageData['lat']);
			$data['y']=($selectParentImageData['lng'] == NULL ? '' : $selectParentImageData['lng']);
			$data['type']=$selectParentImageData['type'];
			$data['webUrl']=($selectParentImageData['webUrl'])?$selectParentImageData['webUrl']:'';
			$data['location']=($selectParentImageData['location'])?$selectParentImageData['location']:'';
			$data['addSpecification']=($selectParentImageData['addSpecification'])?$selectParentImageData['addSpecification']:'';

			//----------------------stacklinks array-------------------------------------

			//------------if stack is part of cube then does not use session --------------
			
			$gettingParentType = stacklinkIteration($conn,$breakactivestacklink[1],'type',$r1['imageID'],$imagerow['userID']);
			
			$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$breakactivestacklink[1]."'))");
			$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
			$gettingParentOfParentType = $gettingCubeData['type'];
			if($gettingParentType  == 6 || $gettingParentOfParentType ==6)
			{
				
				$newIterationID=$iterationId;
				$newUserID=$userId;
			}
			else
			{
				if($iterationButton==0)
				{
					$WhoStackLinkIterationID=mysqli_query($conn,"SELECT id from whostack_table inner join iteration_table on whostack_table. reuestedIterationID =iteration_table.iterationID   WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");

					if(mysqli_num_rows($WhoStackLinkIterationID)>0)
					{
						$getSessionIterationIDInfo =0;
						$getIterationIDWhoStackInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',whostackFdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getIterationIDWhoStackInfo=mysqli_num_rows($getIterationIDWhoStackInfo);
					}

					else
					{

						$getIterationIDWhoStackInfo  = 0;
						$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

						$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);

					}

					if($getIterationIDWhoStackInfo > 0 and $iterationButton==0)
					{

						$whoStackIterationIDInfo=mysqli_fetch_assoc($getIterationIDWhoStackInfo);

						$getIterationIDWhoStackInfoUpdate=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  !find_in_set('".$getSubIterationImageInfo['did']."',fdid) and find_in_set('".$getSubIterationImageInfo['did']."',rdid) and user_id='".$loginUserId."' order by datetime desc limit 1");

						if(mysqli_num_rows($getIterationIDWhoStackInfoUpdate)>0)
						{
							$newIterationID=$whoStackIterationIDInfo['iterationID'];
							$newUserID=$whoStackIterationIDInfo['user_id'];
							

						}
						else
						{
							$newIterationID=$iterationId;
							$newUserID=$userId;
							

						}



					}


					else if($getSessionIterationIDInfo1 > 0 and $iterationButton==0)
					{

						$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);


						$newIterationID=$IterationIDInfo['iterationID'];
						$newUserID=$IterationIDInfo['user_id'];
							


					}
					else
					{

						$newIterationID=$iterationId;
						$newUserID=$userId;
					}
				}
				else
				{
					$newIterationID=$iterationId;
					$newUserID=$userId;
				}
			}
			
			$data['sessionIterationID']=$newIterationID;
			$data['sessionImageID']=$row['imageID'];

			if($imagerow['adopt_photo']==1)
			{
				$data['iterationButton']=0;
			}
			else
			{
				$data['iterationButton']=1;

			}



			$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$newIterationID."'"));

			$stackLinkData=array();
			if (strpos($getImageStacklinksInfo['stacklinks'], 'home') == false)
			{

				$words = explode(',',$getImageStacklinksInfo['stacklinks']);

				foreach ($words as $word)
				{

					$result = explode('/',$word);
					$getcount=mysqli_fetch_assoc(mysqli_query($conn,"select imageID  from iteration_table where imageID=(SELECT imageID FROM `iteration_table` where  iterationID='".$result[1]."')"));

					$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type FROM image_table WHERE imageID='".$getcount['imageID']."'"));




					if( $stackFetchFromImageTable['type']!=6)
					{

						$arr['stacklink']=$word;
						$stackLinkData[]=$arr;
					}
					else
					{

						$fetchActiveStackName = $result[0].'/home';
						$arr['stacklink']=$result[0].'/home';
						$stackLinkData[]=$arr;
						//$arr['stacklink']=$word;
						//$stackLinkData[]=$arr;
					}




				}

				foreach($stackLinkData as $fetchStackLink) {
				$ids[] = $fetchStackLink['stacklink'];
				}
				$stackLinksArr=array_unique($ids);
			}
			else
			{
				$arr = array();
				$reverseString =$getImageStacklinksInfo['stacklinks'];
				$words = explode(',',$reverseString);
				foreach ($words as $word)
				{


					$result = explode('/',$word);

					$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
					WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$result[1]."')"));

					if($stackFetchFromImageTable['type']!=6)
					{


						$arr['stacklink']=$word;
						$stackLinkData[]=$arr;
					}
					else
					{
					/*	$arr['stacklink']=$word;
						$stackLinkData[]=$arr; */

						$fetchActiveStackName = $result[0].'/home';
						$arr['stacklink']=$result[0].'/home';
						$stackLinkData[]=$arr;
					}


				}



				foreach($stackLinkData as $fetchStackLink) {
				$ids[] = $fetchStackLink['stacklink'];
				}


				$stackLinksArr=array_unique($ids);

			}


			if(count($stackLinksArr) >1)
			{

				if(count($stackLinksArr)>=2 and $iterationButton==0)
				{

					$fetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM user_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."' ");

					$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

					$createNewDid = $linkingInfo['did'];
					$createNewFdid = $linkingInfo['fdid'];
					$createNewRdid = $linkingInfo['rdid'];
					$createNewWhoStackFdid = $linkingInfo['whostackFdid'];

					if(mysqli_num_rows($fetchIterationIDInfo)<=0)
					{

						$unixTimeStamp=date("Y-m-d"). date("H:i:s");

						$insertUserTable=mysqli_query($conn,"INSERT INTO user_table(iterationID,user_id,imageID,
						did,fdid,rdid,whostackFdid,date,time,datetime) VALUES('".$newIterationID."',
						'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','$createNewWhoStackFdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')");


					}
					else
					{
						$unixTimeStamp=date("Y-m-d"). date("H:i:s");
						mysqli_query($conn,"update user_table set whostackFdid='".$createNewWhoStackFdid."', date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."' , stack_type='0' where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."'");
					}

				}





				$WhoStackLinkIterationID=mysqli_query($conn,"SELECT distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name, whostack_table.datetime FROM `iteration_table`  inner join whostack_table on iteration_table.iterationID = whostack_table. reuestedIterationID    WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");


				if(mysqli_num_rows($WhoStackLinkIterationID)>0)
				{

					while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
					{


						$whostackLinksArr[]=$fetchWhoStackLinkIterationID['who_stacklink_name'];

					}
				}
				if(!empty($whostackLinksArr)>0) // remove who stack data here
				{
					$whostackLinksArrValue=array_reverse(array_diff($whostackLinksArr,$stackLinksArr));
				}


				if(!empty($whostackLinksArrValue))
				{

					foreach($whostackLinksArrValue as $stacklinkCount=>$stackminiArr) //whostack stackLink
					{
						$stackArrInfoData=explode('/',$stackminiArr);

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];

						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];

						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}
						$stackArr1[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];


						if($stackArrInfoData[1]=='home')
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								$stackedRelated['stackrelateddata']['type']=6;
							}
							else
							{
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							}



							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
							$stackedRelated['stackrelateddata']['ID']='';
							//$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['parentName']='';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['imageComment']='';
							//$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);

							$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];


						}
						else
						{

							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}

							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));


							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;

							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']) ;
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng'] );
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
							$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							if($stackDataFetchFromImageTable['type']==7)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								//image Data


								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}

								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];

							}
							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								//image Data


								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}

								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];

							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}

								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

							}
							if($stackDataFetchFromImageTable['type']==4)
							{


								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							}
							if($stackDataFetchFromImageTable['type']==5)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;

								$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							}

							if($stackDataFetchFromImageTable['type']==6)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}

							//$stackedRelated['stackrelateddata']['jyot']=1;
							$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

						}



					}
				}
				$stacklink['stacklinks']=array_reverse($stackArr1);







				foreach($stackLinksArr as $stacklinkCount=>$stackminiArr) //session stackLink
				{

					$mainStackLink = explode(',', trim($imagerow['stacklinks'])); // check session or original stack of that stack.


					$stackArrInfoData=explode('/',$stackminiArr);
					$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
					AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
					$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
					/**********if child of other user ownername should show**********/
						if($stackArrInfoData[1]!='home'){



							/* 	$stackOwnerInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,u.profileimg,img.type FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$stackArrInfoData[1]."'"));

								$stacked['stackuserdata']['userName1']=$stackOwnerInfo['username'];
								$stacked['stackuserdata']['profileImg1']=($stackOwnerInfo['profileimg']!='')?$stackOwnerInfo['profileimg']:''; */

								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';

							}
							else{
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
							}
					$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
					if (in_array($stackUserInfo['id'], $getBlockUserList))
					{
						$stacked['stackuserdata']['blockUser']=1;
					}
					else
					{

						$stacked['stackuserdata']['blockUser']=0;
					}
					$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];

					if(in_array($stackminiArr, $mainStackLink))
					{
						$mainStackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain original stack related that stack.
					}
					else
					{
						$sessionstackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain all session stacklink.
					}

					if($stackArrInfoData[1]=='home')
					{


						//echo $active_stacklink_name;
						if($stackminiArr==$fetchActiveStackName)
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=1;
						}
						else
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=0;
						}


						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								$stackedRelated['stackrelateddata']['type']=6;
							}
							else
							{
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							}
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
							$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
						}
						else
						{
							$stackedRelated['stackrelateddata']['cubeID']=0;
							$stackedRelated['stackrelateddata']['profileStory']="0";
						}
						$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
						$stackedRelated['stackrelateddata']['ID']='';
						//$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
						$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						$stackedRelated['stackrelateddata']['parentName']='';
						$stackedRelated['stackrelateddata']['url']='';
						$stackedRelated['stackrelateddata']['thumbUrl']='';
						$stackedRelated['stackrelateddata']['frame']='';
						$stackedRelated['stackrelateddata']['x']='';
						$stackedRelated['stackrelateddata']['y']='';
						$stackedRelated['stackrelateddata']['imageComment']='';
						//$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
						$stackedRelated['stackrelateddata']['oldThreadId']='';
						$stackedRelated['stackrelateddata']['oldIterationID']='';
						$stackedRelated['stackrelateddata']['oldImageID']='';
						$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
						
						$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,imageID,stacklink_name,autoapprove_userid FROM iteration_table WHERE iterationID='".$stackedRelated['stackrelateddata']['iterationID']."'"));
						$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$nameOfStack['autoapprove_userid']."')"));
						
						
						$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
						
						if($auto_username['username']!='')
						{
						
							$autoApproval= array();
							$autoApproval['userID']=$auto_username['id'];

							$autoApproval['userName']=$auto_username['username'];
							$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
							$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
							$autoApproval['firstName']=$auto_username['fname'];

							if (in_array($auto_username['id'], $getBlockUserList))
							{
								$autoApproval['blockUser']=1;
							}
							else
							{

								$autoApproval['blockUser']=0;
							}
							$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
							
						}
						else
						{
							$stackedRelated['stackrelateddata']['autoApproval'] = '';
						}


						if(in_array($stackminiArr, $mainStackLink))
						{
							$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];	// contain original stack related that stack.
						}
						else
						{
							$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];// contain all session stacklink.
						}



					}
					else
					{


					/* 	if($stackminiArr==$fetchActiveStackName)
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=1; //active stack
						}
						else
						{
							$stackedRelated['stackrelateddata']['activeStacklink']=0;
						} */
						$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


						$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT autoapprove_userid,userID,imageID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

						$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
						$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
						$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
						$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
						$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
						$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
						$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
						$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
						$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
						$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
						
						$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$nameOfStack['autoapprove_userid']."')"));
						
						
						$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
						
						if($auto_username['username']!='')
						{
						
							$autoApproval= array();
							$autoApproval['userID']=$auto_username['id'];

							$autoApproval['userName']=$auto_username['username'];
							$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
							$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
							$autoApproval['firstName']=$auto_username['fname'];

							if (in_array($auto_username['id'], $getBlockUserList))
							{
								$autoApproval['blockUser']=1;
							}
							else
							{

								$autoApproval['blockUser']=0;
							}
							$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
							
						}
						else
						{
							$stackedRelated['stackrelateddata']['autoApproval'] = '';
						}
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
							$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
						}
						else
						{
							$stackedRelated['stackrelateddata']['cubeID']=0;
							$stackedRelated['stackrelateddata']['profileStory']="0";
						}

						if(in_array($fetchActiveStackName,$stackLinksArr ))
						{


							if($stackminiArr==$fetchActiveStackName)
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}
						}
						else
						{

							if($stackDataFetchFromImageTable['imageID'] == $r1['imageID'])
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['activeStacklink']=0;
							}

						}
                      if($stackDataFetchFromImageTable['type']==7)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
							//image Data


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


							$name = explode('_',$nameOfStack['stacklink_name']);
							if($name[1]=='profileStory')
							{
								$stackedRelated['stackrelateddata']['name']=$name[0];
							}
							else
							{
								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							}

						//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							if($stackImageTitle['imageID']==$oldImageID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}

						if($stackDataFetchFromImageTable['type']==2)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
							//image Data


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));


							$name = explode('_',$nameOfStack['stacklink_name']);
							if($name[1]=='profileStory')
							{
								$stackedRelated['stackrelateddata']['name']=$name[0];
							}
							else
							{
								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							}

						//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							if($stackImageTitle['imageID']==$oldImageID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}
						if($stackDataFetchFromImageTable['type']==3)
						{
							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

						$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
							//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}

						}
						if($stackDataFetchFromImageTable['type']==4)
						{


							$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
							$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
							//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}
						if($stackDataFetchFromImageTable['type']==5)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;

						        $name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
							//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}

						if($stackDataFetchFromImageTable['type']==6)
						{

							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';

							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
							$stackedRelated['stackrelateddata']['ID']='';
							if($stackArrInfoData[1]==$oldIterationID){
								$stackedRelated['stackrelateddata']['oldThreadId']=isset($oldThreadId)?$oldThreadId:'';
								$stackedRelated['stackrelateddata']['oldIterationID']=isset($oldIterationID)?$oldIterationID:'';
								$stackedRelated['stackrelateddata']['oldImageID']=isset($oldImageID)?$oldImageID:'';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']=isset($oldAutorelatedID)?$oldAutorelatedID:'';
							}
							else{
								$stackedRelated['stackrelateddata']['oldThreadId']='';
								$stackedRelated['stackrelateddata']['oldIterationID']='';
								$stackedRelated['stackrelateddata']['oldImageID']='';
								$stackedRelated['stackrelateddata']['oldAutorelatedID']='';
							}
						}

						//$stackedRelated['stackrelateddata']['jyot']=2;
						if(in_array($stackminiArr, $mainStackLink))
						{
							$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{
							$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}


					}

				}
				if(!empty($stackArr1))  //means whostack data exist.
				{
					if(!empty($sessionstackArr))
					{
						//whostack , session, main stacklink exist
						$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr,$mainStackArr)); // insert to reverse order
					}
					else
					{
						//whostack, main stacklink exist but session data does not exist.
						$data['stacklinks']=array_reverse(array_merge($stackArr1,$mainStackArr));
					}

				}
				else{   //means whostack data does not exist.

					if(!empty($sessionstackArr))
					{
						//sesion data exist.
						if(!empty($mainStackArr))
						{
							$data['stacklinks']=array_merge($sessionstackArr,$mainStackArr);
						}
						else
						{
							$data['stacklinks']=array_merge($sessionstackArr);
						}


					}
					else
					{
						//sesion data does not exist.
						$data['stacklinks']=array_merge($mainStackArr);
					}

				}



			}
			else
			{

				$getAllWhoStackLink=mysqli_query($conn,"SELECT reuestedIterationID FROM whostack_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$imagerow['iterationID']."'  AND  requestStatus =2 ");
				$commaVariable='';
				if(mysqli_num_rows($getAllWhoStackLink)>0)
				{
					while($allWhoStackLink=mysqli_fetch_assoc($getAllWhoStackLink))
					{
						$allWhoStackIterationID.=$commaVariable.$allWhoStackLink['reuestedIterationID'];
						$commaVariable=',';
					}
				}


				$WhoStackLinkIterationID=mysqli_query($conn,"select * from (SELECT  distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name , iterationID FROM `iteration_table` where  iterationID in ($allWhoStackIterationID)) as stack_link_table where who_stacklink_name!='".$stackLinksArr[0]."'  ORDER BY FIELD(iterationID,$allWhoStackIterationID) desc ");

				if(mysqli_num_rows($WhoStackLinkIterationID)>0)
				{
					while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
					{

						$stackArrInfoData=explode('/',$fetchWhoStackLinkIterationID['who_stacklink_name']);

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}

						$stackArr['stackUserInfo']=$stacked['stackuserdata'];

						if($stackArrInfoData[1]=='home')
						{


							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								$stackedRelated['stackrelateddata']['type']=6;
							}
							else
							{
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							}

							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];

							$stackedRelated['stackrelateddata']['activeStacklink']=0;
							$stackedRelated['stackrelateddata']['ID']='';
							//$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['parentName']='';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['imageComment']='';
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
								$stackedRelated['stackrelateddata']['profileStory']="0";
							}
							//$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);

							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{

							$stackedRelated['stackrelateddata']['activeStacklink']=0;
							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
							$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,imageID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
							$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat'] );
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' : $stackDataFetchFromTagTable['lng']);
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
							$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
								$stackedRelated['stackrelateddata']['profileStory']="0";
							}
	                        if($stackDataFetchFromImageTable['type']==7)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];


									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}



								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}

							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

							//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];


									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}



								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];

							}
							if($stackDataFetchFromImageTable['type']==4)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							}
							if($stackDataFetchFromImageTable['type']==5)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
														$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
							}
							if($stackDataFetchFromImageTable['type']==6)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}


							//$stackedRelated['stackrelateddata']['jyot']=3;
							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

						}

						$data['stacklinks'][]=array_reverse($stackArr);


					}


				}

				$stackArrInfoData=explode('/',$stackLinksArr[0]);


				$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
				AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

				$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
					/**********if child of other ownername should show**********/

				if($stackArrInfoData[1]!='home'){
					$stackOwnerInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,CASE WHEN u.profileimg IS NULL OR u.profileimg = '' THEN '' 
					WHEN u.profileimg LIKE 'albumImages/%' THEN concat( '$serverurl', u.profileimg ) ELSE
					u.profileimg
					END
					AS profileimg,img.type FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$stackArrInfoData[1]."'"));

					$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
					$stacked['stackuserdata']['profileImg']=($stackOwnerInfo['profileimg']!='')?$stackOwnerInfo['profileimg']:'';

				}
				else{

					$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
					$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
				}
				$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
				$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
				if (in_array($stackUserInfo['id'], $getBlockUserList))
				{
					$stacked['stackuserdata']['blockUser']=1;
				}
				else
				{

					$stacked['stackuserdata']['blockUser']=0;
				}
				$stackArr['stackUserInfo']=$stacked['stackuserdata'];

				if($stackArrInfoData[1]=='home')
				{



							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['typeID']=($cubeInfoData['imageID'] == NULL ? '' : $cubeInfoData['imageID']);
								$stackedRelated['stackrelateddata']['type']=6;
							}
							else
							{
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))"));
									if($stackDataFetchFromImageTable['type']==6)
									{
										   $stackedRelated['stackrelateddata']['type']=6;
										   $stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
									}
									else
									{
										$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$iterationId."')"));
										$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];;

								}
							}

					$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
					$stackedRelated['stackrelateddata']['activeStacklink']=1;
					$stackedRelated['stackrelateddata']['ID']='';
					//$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
					$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
					$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
					$stackedRelated['stackrelateddata']['parentName']='';
					$stackedRelated['stackrelateddata']['url']='';
					$stackedRelated['stackrelateddata']['thumbUrl']='';
					$stackedRelated['stackrelateddata']['frame']='';
					$stackedRelated['stackrelateddata']['x']='';
					$stackedRelated['stackrelateddata']['y']='';
					$stackedRelated['stackrelateddata']['imageComment']='';
					
					$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$imagerow['autoapprove_userid']."')"));
					
					
					$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
					
					if($auto_username['username']!='')
					{
					
						$autoApproval= array();
						$autoApproval['userID']=$auto_username['id'];

						$autoApproval['userName']=$auto_username['username'];
						$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
						$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
						$autoApproval['firstName']=$auto_username['fname'];

						if (in_array($auto_username['id'], $getBlockUserList))
						{
							$autoApproval['blockUser']=1;
						}
						else
						{

							$autoApproval['blockUser']=0;
						}
						$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
						
					}
					else
					{
						$stackedRelated['stackrelateddata']['autoApproval'] = '';
					}
					$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
					if(mysqli_num_rows($cubeInfoData)>0)
					{
						$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
						$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
						$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
					}
					else
					{
						$stackedRelated['stackrelateddata']['cubeID']=0;
						$stackedRelated['stackrelateddata']['profileStory']="0";
					}

					$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
				}
				else
				{
					$stackedRelated['stackrelateddata']['activeStacklink']=1;
					$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
					WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
					$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
					$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,imageID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

					$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
					$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);
					$stackedRelated['stackrelateddata']['ownerName']=$stackUserInfo['username'];
					$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
					$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
					$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat']) ;
					$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' : $stackDataFetchFromTagTable['lng']);
					$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
					$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
					$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
					
					$auto_username=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
					AS profileimg,cover_image,fname FROM tb_user WHERE id=('".$imagerow['autoapprove_userid']."')"));
					
					$stackedRelated['stackrelateddata']['autoUsername']=isset($auto_username['username'])?$auto_username['username']:'';
					
					if($auto_username['username']!='')
					{
					
						$autoApproval= array();
						$autoApproval['userID']=$auto_username['id'];

						$autoApproval['userName']=$auto_username['username'];
						$autoApproval['profileImg']=($auto_username['profileimg']!='')?$auto_username['profileimg']:'';
						$autoApproval['coverImage']=($auto_username['cover_image'] == NULL ? '' : $auto_username['cover_image']);
						$autoApproval['firstName']=$auto_username['fname'];

						if (in_array($auto_username['id'], $getBlockUserList))
						{
							$autoApproval['blockUser']=1;
						}
						else
						{

							$autoApproval['blockUser']=0;
						}
						$stackedRelated['stackrelateddata']['autoApproval'] = $autoApproval;
						
					}
					else
					{
						$stackedRelated['stackrelateddata']['autoApproval'] = '';
					}
					$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
					if(mysqli_num_rows($cubeInfoData)>0)
					{
						$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
						$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
						$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
					}
					else
					{
						$stackedRelated['stackrelateddata']['cubeID']=0;
						$stackedRelated['stackrelateddata']['profileStory']="0";
					}
                    if($stackDataFetchFromImageTable['type']==7)
					{
						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));




						$name = explode('_',$nameOfStack['stacklink_name']);
						if($name[1]=='profileStory')
						{
							$stackedRelated['stackrelateddata']['name']=$name[0];
						}
						else
						{
							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						}



						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
					}
					if($stackDataFetchFromImageTable['type']==2)
					{
						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));




									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}



						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
					}
					if($stackDataFetchFromImageTable['type']==3)
					{
						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
					//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						$name = explode('_',$nameOfStack['stacklink_name']);
						if($name[1]=='profileStory')
						{
							$stackedRelated['stackrelateddata']['name']=$name[0];
						}
						else
						{
							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						}

					}
					if($stackDataFetchFromImageTable['type']==4)
					{

						$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
						$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

						//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						$name = explode('_',$nameOfStack['stacklink_name']);
						if($name[1]=='profileStory')
						{
							$stackedRelated['stackrelateddata']['name']=$name[0];
						}
						else
						{
							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						}
						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
					}
					if($stackDataFetchFromImageTable['type']==5)
					{

						$stackedRelated['stackrelateddata']['url']='';
						$stackedRelated['stackrelateddata']['thumbUrl']='';

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


						//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
												$name = explode('_',$nameOfStack['stacklink_name']);
						if($name[1]=='profileStory')
						{
							$stackedRelated['stackrelateddata']['name']=$name[0];
						}
						else
						{
							$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						}
						$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
					}
					if($stackDataFetchFromImageTable['type']==6)
					{

						$stackedRelated['stackrelateddata']['url']='';
						$stackedRelated['stackrelateddata']['thumbUrl']='';

						$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));


						$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
						$stackedRelated['stackrelateddata']['ID']=$nameOfStack['imageID'];
					}

					//$stackedRelated['stackrelateddata']['jyot']=4;

					$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

				}

				$data['stacklinks'][]=array_reverse($stackArr);
			}

			$data['userinfo']['userName']=$newUsername[0];
			$data['userinfo']['userID']=$newUserID;


			if($newUsername[1]!='')
			{
				$data['userinfo']['profileImg']=$newUsername[1];
			}
			else
			{
				$data['userinfo']['profileImg']='';
			}


			/*--------------------------  SWAP SIBLING child -----------------------------------*/


			//new autorelated Fetch by jyoti
			//Add the auto related Linking code here.

		 	if( $relatedThreadID=='' && $autorelatedID==''){



				$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."'  ORDER BY viewDate DESC limit 1");
					if(mysqli_num_rows($autorelated_session)>0){

						$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
						$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
						$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
						$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
						$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
					}
					else
					{



						 $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID,fdid,did FROM sub_iteration_table WHERE imgID='".$imageId."'  AND did=1 "));



							$autorelated_session1=mysqli_query($conn,"SELECT * FROM `new_auto_related` where   FIND_IN_SET('".$getSubIterationImageInfo['iterationID']."',autorelated)");
							if(mysqli_num_rows($autorelated_session1)>0){

								$autorelated_session2=mysqli_query($conn,"SELECT * FROM `new_auto_related` where iterationID ='".$iterationId."' ");
								if(mysqli_num_rows($autorelated_session2)>0){


								$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
								$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
								$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
								$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
								$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
								}
							}

						}
					}




			}
			if( $relatedThreadID=='' && $autorelatedID=='')
			{


				//new autorelated by jyoti

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE new_auto_related.iterationID ='".$iterationId."' and new_auto_related.imageID ='".$imagerow['imageID']."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){


						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
					
						
						
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						}
						
						//$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
						 
						$data['countInfo']=count($arrayAuto);
						array_unshift($arrayAuto,$iterationId);


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$fetchNewAutoRelated['iterationID'];
						$currentStack['imageID']=$fetchNewAutoRelated['imageID'];
						$currentStack['forwordrelatedID']=1;
						$currentStack['backwordrelatedID']='';
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';
						$data['CurrentStack']=$currentStack;

						$rightchild['iterationID']=$arrayAuto[1];
						
						$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[1]."'"));

						$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
						$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
						if($getStackImageID['type'] == 7)
						{
							$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
						}
						else
						{
							$rightchild['videoUrl']='';
							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
						}
						//$rightchild['userID']=$userId;
						$rightchild['userID']=$getStackImageID['userID'];
						$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
						$rightchild['title']=$getStackImageID['stacklink_name'];
						$rightchild['threadID']=$fetchNewAutoRelated['threadID'];
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[1]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$rightchild['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$rightchild['cubeID']=0;
						}

						$data['autorealtedParentStackName']=$imagerow['stacklink_name'];
						$data['rightChild']=$rightchild;
						$data['leftChild']=array();
						$data['optionalChild']=array();

						//iteracation check in database

						$rId = $rightchild['iterationID'];
						$getrightchild =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $rId"));
						if(empty($getrightchild)){

							$data['rightChild']="";

						}

					}
				}

				else
				{

					$data['optionalChild']=array();
					$data['CurrentStack']=array();
					$data['rightChild']=array();
					$data['leftChild']=array();
				}
				/****Delete all back auto_related ****/
				$fetchbackwardDel =mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'");
				/**** end Delete all back auto_related ****/
			}
			else if($optionalOf=='' && $optionalIndex=='' && $relatedThreadID!='')
			{

				$unixTimeStamp=date("Y-m-d"). date("H:i:s");

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){
						$arrayIndex=$autorelatedID;

						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
						
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						}
						
						//$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
						 
						if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);
							

						}
						else{
							$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNewAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';

						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							$lIndex="";
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex="";
							$lIndex=$leftIndex;
						}
						else{ //if current is neigther last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';


						//Right child Start
						if($rIndex !== ""){
							$rightchild['iterationID']=$arrayAuto[$rIndex];

							if($rightchild['iterationID'] == $iterationId ){
								$rightchild = "";

							}else{



							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));


							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));

							if($getStackImageID['type'] == 7)
							{
								$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
							}
							else
							{
								$rightchild['videoUrl']='';
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							}
							
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							//$rightchild['userID']=$userId;
								$rightchild['userID']=$getStackImageID['userID'];
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}


							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];

							//Right child End
						 }

						}else{ $rightchild = ""; }

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;

							if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						if($lIndex !==""){
						//Left Child start
						$leftchild['iterationID']=$arrayAuto[$lIndex];
						$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

						$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));

						$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
						$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
						if($getStackImageID1['type'] == 7)
						{
							$leftchild['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							$leftchild['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
						}
						else
						{
							$leftchild['videoUrl']='';
							$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
						}
						
						//$leftchild['userID']=$userId;
							$leftchild['userID']=$getStackImageID1['userID'];
						$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
						$leftchild['title']=$getStackImageID1['stacklink_name'];
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[$lIndex]."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$leftchild['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$leftchild['cubeID']=0;
						}


						$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE threadID ='".$relatedThreadID."' and userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){

							$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

							if($fetchNewAutoRelated['threadID']!=''){
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
							}
							else
							{
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$lIndex;
							}
						}
						else
						{
							$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
							$leftchild['autoRelatedID']=$lIndex;
						}
						//$leftchild['threadID']=$relatedThreadID;
						//$leftchild['autoRelatedID']=$lIndex;
									//Left Child End
						$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
						if($data['autorealtedParentStackName']){
							$parent_name = $data['autorealtedParentStackName'];
						}else{
							$parent_name = '';
						}
						//Left Child End

						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];

							if($iteration_count==0){

									$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
									VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
								}
						}


						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));


						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						if($fetchbackwardleft['type'] == 7)
						{
							$backRelated['videoUrl']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
							$backRelated['url']=($fetchbackwardleft['thumb_url']!='')? $fetchbackwardleft['thumb_url']:'';
						}
						else
						{
							$backRelated['videoUrl']='';
							$backRelated['url']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
						}
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iteration_id']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$backRelated['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$backRelated['cubeID']=0;
						}



					} else { $leftchild = ""; }

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						//$data['leftChild']=$leftchild;

						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}
						if($backRelated['iterationID']  == $arrayAuto[$lIndex]){
							$data['backRelated']=$backRelated;
						}else{
							$data['backRelated']="";
							/****Delete all back auto_related ****/
							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'"));
							/**** end Delete all back auto_related ****/


						}

						//Optional child autorelated start

						//500 entries should be maintained only by jyoti

						$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
						if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){
							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and   order by viewDate desc limit 1");

							$result = mysqli_fetch_assoc($autorelated_session);

							if(mysqli_num_rows($autorelated_session)<1){
									$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());
							}
							elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
							else
							{
								$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
						}
						else{

							$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
							if($autorelated_session_delete){
								$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());

							}
						}

						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								//echo 'ffffffffffffff'.$optionalCount=count($arrayOptionalAuto);
								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									if($getStackImageID['type'] == 7)
									{
										$optionalchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
									}
									else
									{
										$optionalchild['videoUrl']='';
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									}
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$optionalchild['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$optionalchild['cubeID']=0;
									}

									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}



								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								//$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								if($getStackImageID['type'] == 7)
								{
									$rightOptional['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
								}
								else
								{
									$rightOptional['videoUrl']='';
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								}
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightOptional['cubeID']=0;
								}
								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								//$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								if($getStackImageID1['type'] == 7)
								{
									$leftOptional['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
								}
								else
								{
									$leftOptional['videoUrl']='';
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								}
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftOptional['cubeID']=0;
								}
								$data['leftOptional']="";

								//Check iterationID exists in database
								$opid = $optionalchild['iterationID'];
								$ropid = $rightOptional['iterationID'];

								$queryGetitreaction =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $opid"));
								if(empty($queryGetitreaction)){
									$data['optionalChild']="";

								}
								$queryGetrightoption =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $ropid"));
								if(empty($queryGetitreaction)){
									$data['rightOptional']="";

								}


							}
						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";
						}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";
						}
				}
				//new autorelated by jyoti end

			}
			else
			{


				//new autorelated by jyoti
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");
				if($optionalOf==$iterationId){


					$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");

			 		$arrayIndex=$optionalIndex-1;

			 	}
			 	else{
			 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
			 		$arrayIndex=$autorelatedID;

			 	}
				if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

					$fetchNormalAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
					if($fetchNormalAutoRelated['autorelated']!=''){

						$arrayAuto=explode(',', $fetchNormalAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNormalAutoRelated['iterationID']."'"));
						
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						}
						//$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoDatas)>0)
						{
							$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
							$parentChild ['cubeID']=$cubeInfoDatas['id'];

						}
						else
						{
							$parentChild ['cubeID']=0;
						}
						$data['parentChild']=$parentChild;	
						 
                       if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);

						}
						else{
								$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNormalAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						if($arrayIndex<0){
							$arrayIndex=$indexCount-1;
						}
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';
						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							//$lIndex=$indexCount-1;
							$lIndex='';
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex='';
							//$rIndex=0;
							$lIndex=$leftIndex;
						}
						else{ //if current is neigth last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}



						$currentStack['threadID']=$relatedThreadID;
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']=$optionalIndex;
						$currentStack['optionalOf']=$optionalOf;

						if($rIndex!==""){
							//Right child Start
							$rightchild['iterationID']=$arrayAuto[$rIndex];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

						//	$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							//$rightchild['userID']=$userId;
							$rightchild['userID']=$getStackImageID['userID'];
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							if($getStackImageID['type'] == 7)
							{
								$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
							}
							else
							{
								$rightchild['videoUrl']='';
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}

							//Right child End
						}else{
							$rightchild = "";
						}

							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;



						if($lIndex!==""){
							//Left Child start
							$leftchild['iterationID']=$arrayAuto[$lIndex];
							$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

							//$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							if($getStackImageID1['type'] == 7)
							{
								$leftchild['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftchild['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
							}
							else
							{
								$leftchild['videoUrl']='';
								$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							}
							$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
							$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
							//$leftchild['userID']=$userId;
							$leftchild['userID']=$getStackImageID1['userID'];
							$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
							$leftchild['title']=$getStackImageID1['stacklink_name'];
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$leftchild['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$leftchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$leftchild['cubeID']=0;
							}
							/* $leftchild['threadID']=$relatedThreadID;
							$leftchild['autoRelatedID']=$lIndex; */

							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
							if(mysqli_num_rows($autorelated_session)>0){

								$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

								if($fetchNewAutoRelated['threadID']!=''){
									$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								    $leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
								}
								else
								{
									$leftchild['threadID']=$relatedThreadID;
									$leftchild['autoRelatedID']=$lIndex;
								}
							}
							else
							{
								$leftchild['threadID']=$relatedThreadID;
								$leftchild['autoRelatedID']=$lIndex;
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							if($data['autorealtedParentStackName']){
								$parent_name = $data['autorealtedParentStackName'];
							}else{
								$parent_name = '';

							}
							//Left Child End
						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];


							if($iteration_count == 0){
								// echo "INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								// VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')";die;

								$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
							}
						}

						if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));

						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						//$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						if($backRelated['type'] == 7)
						{
							$backRelated['videoUrl']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
							$backRelated['url']=($fetchbackwardleft['thumb_url']!='')? $fetchbackwardleft['thumb_url']:'';
						}
						else
						{
							$backRelated['videoUrl']='';
							$backRelated['url']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
						}
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfoData)>0)
						{
							$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
							$backRelated['cubeID']=$cubeInfoData['id'];

						}
						else
						{
							$backRelated['cubeID']=0;
						}

						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}

						}else{
							$leftchild = "";
						}

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						// $data['rightChild']=$rightchild;
						// $data['leftChild']=$leftchild;
						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}



						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){
							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);
							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								$optionalCount=count($arrayOptionalAuto);


								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									//$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									if($getStackImageID['type'] == 7)
									{
										$optionalchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
									}
									else
									{
										$optionalchild['videoUrl']='';
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									}
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$optionalchild['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$optionalchild['cubeID']=0;
									}
									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}


								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								//$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								if($getStackImageID['type'] == 7)
								{
									$rightOptional['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
								}
								else
								{
									$rightOptional['videoUrl']='';
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								}
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$rightOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightOptional['cubeID']=0;
								}

								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								//$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								if($getStackImageID1['type'] == 7)
								{
									$leftOptional['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
								}
								else
								{
									$leftOptional['videoUrl']='';
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								}
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftOptional['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftOptional['cubeID']=0;
								}
								if($optionalOf==''){
									$data['leftOptional']=$leftOptional;
								}else{
									$data['leftOptional']="";
								}

								$optionalSession=1;
								$optionalOfSession=$optionalOf;
								$optionalIndexSession=$optionalIndex;
								$optionalRightIndex=1;
								$optionalLeftIndex=$optionalCount-1;
								$optionalRightID=$arrayOptionalAuto[1];
								$optionalLeftID=$arrayOptionalAuto[$optionalCount-1];

							}

						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";

								$optionalSession=0;
								$optionalOfSession='';
								$optionalIndexSession='';
								$optionalRightIndex='';
								$optionalLeftIndex='';
								$optionalRightID='';
								$optionalLeftID='';
						}
							//500 entries should be maintained only by jyoti

								$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
								if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){

									$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");
									$result = mysqli_fetch_assoc($autorelated_session);

									if(mysqli_num_rows($autorelated_session)<1){

											$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());


									}
									//$optionalOf=='' && $optionalIndex
									elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
									else
									{
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
								}
								else{

									$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
									if($autorelated_session_delete){
										$autorelated_session==mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID =='".$relatedThreadID."' order by viewDate desc limit 1");
											$result = mysqli_fetch_assoc($autorelated_session);
											if(mysqli_num_rows($autorelated_session)<1){
												$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
												VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());
											}
											elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
											}
									}

								}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";

						}
				}
				//new autorelated by jyoti end

			}

			$pdata['parent']=$data;

			//fetch child here


			$selectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.username,tag_table.lat,tag_table.lng,tag_table.frame FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.stack_visible=0 AND iteration_table.imageID not in ($allBlockImageID) and iteration_table.iterationID  IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE  iteration_table.verID='".$imagerow['verID']."' AND tag_table.linked_iteration='".$iterationId."' AND tag_table.lat!='empty' AND tag_table.lng!='empty' )");


			if(mysqli_num_rows($selectChild) > 0)
			{


				while($childImageRow=mysqli_fetch_assoc($selectChild))
				{

					$data1['userID']=$childImageRow['username'];
					//$data1['name']=$childImageRow['stacklink_name'];
					$data1['userName']=$newUsername[0];
					$data1['title']=$childImageRow['stacklink_name'];
					$data1['iterationID']=$childImageRow['iterationID'];
					$data1['imageID']=$childImageRow['imageID'];
					$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
					$data1['creatorUserID']=$childImageRow['userID'];
					$data1['iterationIgnore']=$childImageRow['iteration_ignore'];

					if($childImageRow['adopt_photo']==0)
					{

						$data1['adoptChild']=0;
					}
					else
					{

						$data1['adoptChild']=1;
					}

					$adoptPhotoType=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where iterationID='".$imagerow['iterationID']."' and adopt_iterationID='".$childImageRow['iterationID']."'"));

					$data1['adoptPhoto']=isset($adoptPhotoType['type'])?$adoptPhotoType['type']:'';




					$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,tb_user.username,tb_user.profileimg,tb_user.id FROM image_table INNER JOIN  tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$childImageRow['imageID']."'"));

					$data1['typeID']=$childImageRow['imageID'];

					$data1['type']=$selectChildImageData['type'];
					if($selectChildImageData['type']==4)
					{
						$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
						$data1['ID']=$selectGrid[0];

					}
					if($selectChildImageData['type']==7)
					{
						$data1['ID']=$selectChildImageData['imageID'];
					}
					if($selectChildImageData['type']==2)
					{
						$data1['ID']=$selectChildImageData['imageID'];
					}
					if($selectChildImageData['type']==5)
					{
						$data1['ID']=$selectChildImageData['imageID'];
					}
					if($selectChildImageData['type']==3)
					{
						$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
						$data1['ID']=$selectMap[0];

					}


					$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$childImageRow['imageID']."'  AND iterationID='".$childImageRow['iterationID']."' "));




					$getIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id,stack_type FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");


					if(mysqli_num_rows($getIterationIDInfo)>0 )
					{
						$IterationIDInfo=mysqli_fetch_assoc($getIterationIDInfo);

						if($IterationIDInfo['stackype']=='1')
						{

							$data1['apiUse']=1; //related

						}
						else
						{
							$data1['apiUse']=0; //normal
						}

					}
					else
					{
						$data1['apiUse']=0; //normal

					}
					if($childImageRow['delete_tag'] == 1 )
						{
							$data1['deleteTag']=1;
						}
						else
						{
							if($childImageRow['adopt_photo'] == 1 )
							{
								if($childImageRow['username'] == $loginUserId)
								{
									$data1['deleteTag']=1;
								}
								
							}
							else
							{
								$data1['deleteTag']=0;
							}
						}
						
					

					$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
					$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';

					$data1['frame']=$childImageRow['frame'];
					$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
					$data1['y']= ($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);

					$data1['userinfo']['userName']=$selectChildImageData['username'];
					$data1['userinfo']['userID']=$selectChildImageData['id'];
					if($selectChildImageData['profileimg']!='')
					{
						$data1['userinfo']['profileImg']=$selectChildImageData['profileimg'];
					}
					else
					{
						$data1['userinfo']['profileImg']='';
					}


					if($data1['adoptChild']==1 )    //adoptChild display only creator user and profile owner
					{
						if(($loginUserId== $data1['creatorUserID'] || $loginUserId==$data['creatorUserID']))
						{
							if($childImageRow['iteration_ignore'] ==0)
							{
								
								$cdata['child'][]=$data1;
							}

						}

					}
					else
					{

						$cdata['child'][]=$data1;
					}



				}
			}


			if(empty($cdata))
			{
				$cdata['child']=array();
			}
			if(empty($pdata))
			{
				$pdata['parent']=array();
			}

			$totalData=array_merge($pdata,$cdata);



		}
		else
		{
			echo json_encode(array('message'=>'There is no relevant data','success'=>0));
			exit;
		}

	}
	else
	{
		echo json_encode(array('message'=>'There is no relevant data','success'=>0));
		exit;
	}

}


if($type==5)
{

	$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid FROM sub_iteration_table WHERE imgID='".$imageId."'  AND iterationID='".$iterationId."' "));
	$creatorUserName=getOwnerName($getSubIterationImageInfo['fdid'],$imageId,$conn);
	$getSubIterationFirstInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID FROM sub_iteration_table WHERE imgID='".$imageId."'  and did = 1"));
	$originalUserName=getOwnerName1(1,$getSubIterationFirstInfo['iterationID'],$conn);
	$data['originalUserName']=$originalUserName;
	
	$data['ownerName']=$creatorUserName;
	$data['contributorSession']=$contributorSession;
	$cubeCount=storyThreadCount($imageId,$loginUserId,$iterationId,$conn);
	$data['storyThreadCount']=($cubeCount['storyThreadCount'] == NULL ? '' : $cubeCount['storyThreadCount']);

	$data['cubeinfo']=($cubeCount['storyData'] == NULL ? '' : $cubeCount['storyData'] );

	$lastSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did,rdid FROM sub_iteration_table WHERE imgID='".$imageId."'  order by id desc"));
	$data['countInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
	$data['optionalCountInfo'] =$getSubIterationImageInfo['did'].'/'.$lastSubIterationImageInfo['did'];
	$data['stackIteration']=$getSubIterationImageInfo['did'];

	$newUsername=mysqli_fetch_row(mysqli_query($conn,"SELECT username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
	AS profileimg,cover_image FROM tb_user WHERE id='$userId' "));


	$getInfo=mysqli_query($conn,"SELECT assigned_name,collectionID as imageID FROM collection_table WHERE collectionID='$imageId'") or die(mysqli_error());


	if(mysqli_num_rows($getInfo)>0)
	{

		while($row=mysqli_fetch_assoc($getInfo))
		{
			$getImageInfo=mysqli_query($conn,"SELECT * FROM iteration_table WHERE imageID='".$row['imageID']."' AND iterationID='".$iterationId."'");

			$stackNotify=mysqli_query($conn,"SELECT notifier_user_id FROM `stack_notifications` where notifier_user_id='".$loginUserId."' and iterationID='".$iterationId."' and imageID= '".$row['imageID']."' and status ='1' ");

			if(mysqli_num_rows($getImageInfo)>0)
			{

				$totalData=NULL;
				while($imagerow=mysqli_fetch_assoc($getImageInfo))
				{
					$data['userName']=$newUsername[0];
					if(mysqli_num_rows($stackNotify)>0)
					{
						$data['stackNotify']=1;
					}
					else
					{
						$data['stackNotify']=0;
					}


					$getBlockUserData=mysqli_query($conn,"SELECT status,id FROM `block_user_table` WHERE ((userID='".$loginUserId."'  and blockUserID='".$userId."') OR (userID='".$userId."'  and blockUserID='".$loginUserId."')) and status ='1'");
					if (mysqli_num_rows($getBlockUserData)>0)
					{
						$data['additionStatus']=1;  //check the iteration for block user(if blocker see the iteration then hide all the button. )
					}
					else
					{
						$allBlockImageID=fetchBlockUserIteration($loginUserId,$conn);
						$getBlockImageIDList = explode(",",$allBlockImageID);
						if (in_array($row['imageID'], $getBlockImageIDList))
						{
							$data['additionStatus']=1;
						}
						else
						{
							$data['additionStatus']=0;
						}
					}

					if($imagerow['allow_addition']=='0')
					{

						$data['allowAddition']=0;	 //means user can add anything on stack
					}
					else if($imagerow['allow_addition']=='1' and $imagerow['userID']==$loginUserId )
					{

						$data['allowAddition']=0;	 //means user can add anything on stack
					}
					else if($imagerow['allow_addition']=='1' and $imagerow['userID']!=$loginUserId )
					{

						$data['allowAddition']=1;	 //means user cannot add anything on stack
					}
					else
					{

						$data['allowAddition']=0;	 //means user can add anything on stack
					}
					$data['allowAdditionToggle']=($imagerow['allow_addition'] == NULL ? '' : $imagerow['allow_addition']);
					$data['userID']=$imagerow['userID'];
					//$data['name']=$imagerow['stacklink_name'];
					$data['imageID']=$row['imageID'];
					$data['typeID']=$imageId;
					$data['ID']=$row['imageID'];
					$data['title']=$imagerow['stacklink_name'];
					$data['iterationID']=$imagerow['iterationID'];
					$data['smiles']=($imagerow['smiles'] == NULL ? '' : $imagerow['smiles']) ;
					$data['creatorUserID']=$imagerow['userID'];
					$data['caption']=($imagerow['caption'] == NULL ? '' : stripslashes($imagerow['caption']));

					$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' and  userID ='".$loginUserId."' ");

					if(mysqli_num_rows($likeImage) > 0)
					{
					//
						$data['like']=1;

					}
					else
					{
						$data['like']=0;
					}


					$fetchcreatorUserID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT username as userID FROM tag_table WHERE iterationID='".$iterationId."'"));
					if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] == $loginUserId )
					{
						$data['adoptChild']=1; // adopt button enable
					}
					else if($imagerow['adopt_photo']==1 && $fetchcreatorUserID['userID'] != $loginUserId)
					{
						$data['adoptChild']=0; // adopt button disable
					}
 					else if($userId != $loginUserId && $imagerow['adopt_photo']==0)
					{
						$data['adoptChild']=2;  //share button +no show edit button
					}
					else
					{
						if($imagerow['userID'] == $loginUserId)
						{
							$data['adoptChild']=3; //show share button + show edit button
							
						}
						else
						{
							$data['adoptChild']=2;
							
						}
						
					}
					if($imagerow['delete_tag'] == 1 )
					{
						$data['deleteTag']=1;
					}
					else
					{
						if($imagerow['adopt_photo'] == 1 )
						{
							if($fetchcreatorUserID['userID'] == $loginUserId)
							{
								$data['deleteTag']=1;
							}
							
						}
						else
						{
							$data['deleteTag']=0;
						}
					}




					if($imagerow['adopt_photo']==1)
					{
						$data['iterationButton']=0;
					}
					else
					{
						$data['iterationButton']=1;

					}


					//------------if stack is part of cube then does not use session --------------
			
					$gettingParentType = stacklinkIteration($conn,$breakactivestacklink[1],'type',$r1['imageID'],$imagerow['userID']);
					
					$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$breakactivestacklink[1]."'))");
					$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
					$gettingParentOfParentType = $gettingCubeData['type'];
					if($gettingParentType  == 6 || $gettingParentOfParentType ==6)
					{
						
						$newIterationID=$iterationId;
						$newUserID=$userId;
					}
					else
					{
						if($iterationButton==0)
						{
							$WhoStackLinkIterationID=mysqli_query($conn,"SELECT id from whostack_table inner join iteration_table on whostack_table. reuestedIterationID =iteration_table.iterationID   WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");

							if(mysqli_num_rows($WhoStackLinkIterationID)>0)
							{
								$getSessionIterationIDInfo =0;
								$getIterationIDWhoStackInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',whostackFdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

								$getIterationIDWhoStackInfo=mysqli_num_rows($getIterationIDWhoStackInfo);
							}

							else
							{

								$getIterationIDWhoStackInfo  = 0;
								$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

								$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);

							}

							if($getIterationIDWhoStackInfo > 0 and $iterationButton==0)
							{

								$whoStackIterationIDInfo=mysqli_fetch_assoc($getIterationIDWhoStackInfo);

								$getIterationIDWhoStackInfoUpdate=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$row['imageID']."'  AND  !find_in_set('".$getSubIterationImageInfo['did']."',fdid) and find_in_set('".$getSubIterationImageInfo['did']."',rdid) and user_id='".$loginUserId."' order by datetime desc limit 1");

								if(mysqli_num_rows($getIterationIDWhoStackInfoUpdate)>0)
								{
									$newIterationID=$whoStackIterationIDInfo['iterationID'];
									$newUserID=$whoStackIterationIDInfo['user_id'];
									

								}
								else
								{
									$newIterationID=$iterationId;
									$newUserID=$userId;
									

								}



							}


							else if($getSessionIterationIDInfo1 > 0 and $iterationButton==0)
							{

								$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);


								$newIterationID=$IterationIDInfo['iterationID'];
								$newUserID=$IterationIDInfo['user_id'];
									


							}
							else
							{

								$newIterationID=$iterationId;
								$newUserID=$userId;
							}
						}
						else
						{
							$newIterationID=$iterationId;
							$newUserID=$userId;
						}
					}
			
					$data['sessionIterationID']=$newIterationID;
					$data['sessionImageID']=$row['imageID'];


					$collectIterationID=mysqli_query($conn,"SELECT iterationID FROM iteration_table  WHERE imageID='$imageId'");
					while($collectIterationIDS=mysqli_fetch_assoc($collectIterationID))
					{
						$iterationIDContain[]=$collectIterationIDS['iterationID'];

						$cubeInfo=mysqli_query($conn,"SELECT id FROM cube_table WHERE  FIND_IN_SET('".$collectIterationIDS['iterationID']."',tags) ") or die(mysqli_error());
						if(mysqli_num_rows($cubeInfo)>0)
						{
							while($cubeInformation=mysqli_fetch_assoc($cubeInfo))
							{

								$countIterationArray[]=$cubeInformation['id'];
							}
						}
					}

					if(count($countIterationArray)>0)
					{
						$data['cubeButton']=1;
					}
					else
					{
						$data['cubeButton']=0;
					}

					$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' and  userID ='".$newUserID."' ");

					if(mysqli_num_rows($likeImage) > 0)
					{
					//
						$data['isLike']=1;

					}
					else
					{
						$data['isLike']=0;
					}
					$data['profilepic']='';
					$userProfilePic=mysqli_query($conn,"select CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
					AS profileimg from tb_user where id in(SELECT userID FROM `like_table` where imageID='".$imagerow['imageID']."' and iterationID='".$imagerow['iterationID']."' order by likeDate,likeTime desc)limit 3");
					$j = 0;
					while($userProfile=mysqli_fetch_assoc($userProfilePic))
					{
						//
						$profileData['profilePic']=$userProfile['profileimg'];
						$data['profilepic'][$j]=$profileData;
						$j++;
					}
                    $selectParentImageData1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as imagecount FROM `comment_table` where  imageID='".$row['imageID']."'"));
                    $data['imageComment']=$selectParentImageData1['imagecount'];
					$selectParentImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,frame,lat,lng,webUrl,location,addSpecification FROM image_table WHERE imageID='".$row['imageID']."'"));

					$data['url']=($selectParentImageData['url']!='')? $selectParentImageData['url']:'';
					$data['thumbUrl']=($selectParentImageData['thumb_url']!='')? $selectParentImageData['thumb_url']:'';
					$data['frame']=$selectParentImageData['frame'];
					$data['x']=($selectParentImageData['lat'] == NULL ? '' : $selectParentImageData['lat']);
					$data['y']=($selectParentImageData['lng'] == NULL ? '' : $selectParentImageData['lng'] );
					//$data['image_comments']=$imagerow['image_comments']; //wrong change it
					$data['type']=$selectParentImageData['type'];
					$data['webUrl']=($selectParentImageData['webUrl'])?$selectParentImageData['webUrl']:'';
					$data['location']=($selectParentImageData['location'])?$selectParentImageData['location']:'';
					$data['addSpecification']=($selectParentImageData['addSpecification'])?$selectParentImageData['addSpecification']:'';

					//----------------------------------------------stacklinks array-------------------------------------


					$getImageStacklinksInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklinks FROM iteration_table WHERE imageID='".$row['imageID']."'  AND iterationID='".$newIterationID."'"));


					if (strpos($getImageStacklinksInfo['stacklinks'], 'home') == false) {

						$words = explode(',',$getImageStacklinksInfo['stacklinks']);

						foreach ($words as $word)
						{

							$result = explode('/',$word);
							$getcount=mysqli_fetch_assoc(mysqli_query($conn,"select imageID, count(iterationID) as count_iteration  from iteration_table where imageID=(SELECT imageID FROM `iteration_table` where  iterationID='".$result[1]."' and imageId='".$row['imageID']."')"));
							$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT type FROM image_table WHERE imageID='".$getcount['imageID']."'"));

							if($stackFetchFromImageTable['type']!=6)
							{

								$arr['stacklink']=$word;
								$stackLinkData[]=$arr;
							}
							else
							{
								$fetchActiveStackName = $result[0].'/home';
								$arr['stacklink']=$result[0].'/home';
								$stackLinkData[]=$arr;
							}

						}



						foreach($stackLinkData as $fetchStackLink) {
						$ids[] = $fetchStackLink['stacklink'];
						}
						$stackLinksArr=$ids;
					}
					else
					{

						$arr = array();
						$reverseString =$getImageStacklinksInfo['stacklinks'];
						$words = explode(',',$reverseString);
						foreach ($words as $word)
						{
							$getcount=mysqli_fetch_assoc(mysqli_query($conn,"SELECT imageID FROM `iteration_table` where LOCATE('$word',stacklinks) and iterationID<='".$newIterationID."' and imageId='".$row['imageID']."'"));

							$result = explode('/',$word);

							$stackFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$result[1]."')"));



							if($stackFetchFromImageTable['type']!=6)
							{

								$arr['stacklink']=$word;
								$stackLinkData[]=$arr;
							}
							else
							{
								$fetchActiveStackName = $result[0].'/home';
								$arr['stacklink']=$result[0].'/home';
								$stackLinkData[]=$arr;
							}

						}


						foreach($stackLinkData as $fetchStackLink) {
						$ids[] = $fetchStackLink['stacklink'];
						}
						$stackLinksArr=$ids;
					}
					if(count($stackLinksArr) > 1)
					{


						if(count($stackLinksArr)>=2 and $iterationButton==0)
						{

							$fetchIterationIDInfo=mysqli_query($conn,"SELECT iterationID FROM user_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."' ");

							$linkingInfo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT did,fdid,rdid,whostackFdid FROM sub_iteration_table WHERE iterationID='".$newIterationID."' and imgID='".$imagerow['imageID']."' "));

							$createNewDid = $linkingInfo['did'];
							$createNewFdid = $linkingInfo['fdid'];
							$createNewRdid = $linkingInfo['rdid'];
							$createNewWhoStackFdid = $linkingInfo['whostackFdid'];

							if(mysqli_num_rows($fetchIterationIDInfo)<=0)
							{

								$unixTimeStamp=date("Y-m-d"). date("H:i:s");

								$insertUserTable=mysqli_query($conn,"INSERT INTO user_table(iterationID,user_id,imageID,
								did,fdid,rdid,whostackFdid,date,time,datetime) VALUES('".$newIterationID."',
								'".$loginUserId."','".$imagerow['imageID']."','".$createNewDid."','$createNewFdid','$createNewRdid','$createNewWhoStackFdid','".date("Y-m-d")."','".date("H:i:s")."','".strtotime($unixTimeStamp)."')");


							}
							else
							{
								$unixTimeStamp=date("Y-m-d"). date("H:i:s");
								mysqli_query($conn,"update user_table set whostackFdid='".$createNewWhoStackFdid."', date ='".date("Y-m-d")."' , time ='".date("H:i:s")."' , datetime='".strtotime($unixTimeStamp)."' , stack_type='0' where imageID='".$imagerow['imageID']."'  AND  iterationID ='".$newIterationID."' and user_id ='".$loginUserId."'");
							}

						}





						$WhoStackLinkIterationID=mysqli_query($conn,"SELECT distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name, whostack_table.datetime FROM `iteration_table`  inner join whostack_table on iteration_table.iterationID = whostack_table. reuestedIterationID    WHERE whostack_table. imageID='".$imagerow['imageID']."'  AND  whostack_table.iterationID ='".$imagerow['iterationID']."'  AND  whostack_table.requestStatus = 2  ");


						if(mysqli_num_rows($WhoStackLinkIterationID)>0)
						{

							while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
							{


								$whostackLinksArr[]=$fetchWhoStackLinkIterationID['who_stacklink_name'];



							}
						}
						if(!empty($whostackLinksArr)>0) // remove who stack data here
						{
							$whostackLinksArrValue=array_reverse(array_diff($whostackLinksArr,$stackLinksArr));
						}



						if(!empty($whostackLinksArrValue))
						{

							foreach($whostackLinksArrValue as $stacklinkCount=>$stackminiArr)
							{

								$stackArrInfoData=explode('/',$stackminiArr);


								$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
								$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
								$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
								$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
								if (in_array($stackUserInfo['id'], $getBlockUserList))
								{
									$stacked['stackuserdata']['blockUser']=1;
								}
								else
								{

									$stacked['stackuserdata']['blockUser']=0;
								}
								$stackArr1[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];


								if($stackArrInfoData[1]=='home')
								{
									$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
									if($stackminiArr==$fetchActiveStackName)
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
									}

									if($stackminiArr==$stackLinkType)
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=2;
									}
									
									$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
									$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
									$stackedRelated['stackrelateddata']['parentName']='';
									$stackedRelated['stackrelateddata']['ID']='';
									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';
									$stackedRelated['stackrelateddata']['frame']='';
									$stackedRelated['stackrelateddata']['x']='';
									$stackedRelated['stackrelateddata']['y']='';
									$stackedRelated['stackrelateddata']['imageComment']='';
									$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);

									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$stackedRelated['stackrelateddata']['cubeID']=0;
									}
									$stackedRelated['stackrelateddata']['parentType']="";
									$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								else
								{

									if($stackminiArr==$fetchActiveStackName)
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['activeStacklink']=0;
									}

									if($stackminiArr==$stackLinkType)
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=2;
									}
									$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
									$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
									if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
									{
										$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
										if($stackDataFetchFromImageTableData['type'] == 1)
										{
											$stackedRelated['stackrelateddata']['parentType']='';
										}
										else
										{
											$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
										}
									}
									else
									{
										$stackedRelated['stackrelateddata']['parentType']="";
										
									}	
									$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
									$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
									$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
									$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
									$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
									$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
									$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
									$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
									$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
									$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$stackedRelated['stackrelateddata']['cubeID']=0;
									}
									if($stackDataFetchFromImageTable['type']==2)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
										//image Data


										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
									//	$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
									}
									if($stackDataFetchFromImageTable['type']==3)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
																$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}

										//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];

									}
									if($stackDataFetchFromImageTable['type']==4)
									{


										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
										//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
									}
									if($stackDataFetchFromImageTable['type']==5)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID5']=$stackImageTitle['collectionID'];
									}

									if($stackDataFetchFromImageTable['type']==6)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']='';
									}


									$stackArr1[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

								}



							}

						}
						$stacklink['stacklinks']=array_reverse($stackArr1);

						foreach($stackLinksArr as $stacklinkCount=> $stackminiArr)
						{
							$stackArrInfoData=explode('/',$stackminiArr);
							$mainStackLink = explode(',', trim($imagerow['stacklinks'])); // check session or original stack of that stack.

							$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
							AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));
							/*$stacked['stackuserdata']['arr']=$stackArrInfoData;*/
							$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
							/**********if child of collection ownername should show**********/
							if($stackArrInfoData[1]!='home'){
								$stackOwnerInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.username,CASE WHEN u.profileimg IS NULL OR u.profileimg = '' THEN '' 
									WHEN u.profileimg LIKE 'albumImages/%'  THEN concat( '$serverurl', u.profileimg ) ELSE
									u.profileimg
									END
									AS profileimg,img.type FROM tb_user u INNER JOIN image_table img ON(img.UserID=u.id) INNER JOIN iteration_table it ON(it.imageID=img.imageID) WHERE it.iterationID='".$stackArrInfoData[1]."'"));

								$stacked['stackuserdata']['userName']=$stackOwnerInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackOwnerInfo['profileimg']!='')?$stackOwnerInfo['profileimg']:'';

							}
							else{
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';


							}

							$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
							$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
							if (in_array($stackUserInfo['id'], $getBlockUserList))
							{
								$stacked['stackuserdata']['blockUser']=1;
							}
							else
							{

								$stacked['stackuserdata']['blockUser']=0;
							}

							if(in_array($stackminiArr, $mainStackLink))
							{
								$mainStackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain original stack related that stack.
							}
							else
							{
								$sessionstackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata']; // contain all session stacklink.
							}

							//$stackArr[$stacklinkCount]['stackUserInfo']=$stacked['stackuserdata'];

							if($stackArrInfoData[1]=='home')
							{

								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}
								if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}

								$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
								$stackedRelated['stackrelateddata']['ID']='';
								$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
								$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['parentName']='';
								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackedRelated['stackrelateddata']['frame']='';
								$stackedRelated['stackrelateddata']['x']='';
								$stackedRelated['stackrelateddata']['y']='';
								$stackedRelated['stackrelateddata']['imageComment']='';
								$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
									$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];
													
								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
									$stackedRelated['stackrelateddata']['profileStory']="0";
								}
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];


								if(in_array($stackminiArr, $mainStackLink))
								{
									$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];	// contain original stack related that stack.
								}
								else
								{
									$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];// contain all session stacklink.
								}
								//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
							}
							else
							{

								$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
								$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID='".$nameOfStack['imageID']."'"));


								if($stackminiArr==$fetchActiveStackName)
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
								}

								if($stackminiArr==$stackLinkType)
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=1;
								}
								else
								{
									$stackedRelated['stackrelateddata']['originateStackLink']=2;
								}
								$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
								$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
								$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
								$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1]; //new add
								$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
								$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? '' : $stackDataFetchFromImageTable['lat']);
								$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
								$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
								$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
								$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
								$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
								$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
									$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];

								}
								else
								{
									$stackedRelated['stackrelateddata']['cubeID']=0;
									$stackedRelated['stackrelateddata']['profileStory']='0';
								}
								if($stackDataFetchFromImageTable['type']==2)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
									//image Data
									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$nameOfStack['imageID']."'"));


									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
									//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}
								if($stackDataFetchFromImageTable['type']==3)
								{
									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
								}
								if($stackDataFetchFromImageTable['type']==4)
								{

									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$nameOfStack['imageID']."'"));

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
								}
								if($stackDataFetchFromImageTable['type']==5)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$nameOfStack['imageID']."'")) ;

									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
								}
								if($stackDataFetchFromImageTable['type']==6)
								{

									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']='';
								}
								if($stackDataFetchFromImageTable['type']==7)
								{

									$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
									$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

									$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$nameOfStack['imageID']."'"));



									$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
								}

								if(in_array($stackminiArr, $mainStackLink))
								{
									$mainStackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								else
								{
									$sessionstackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
								}
								//$stackArr[$stacklinkCount]['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

							}
						}


						if(!empty($stackArr1))  //means whostack data exist.
						{
							if(!empty($sessionstackArr))
							{
								//whostack , session, main stacklink exist
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$sessionstackArr,$mainStackArr)); // insert to reverse order
							}
							else
							{
								//whostack, main stacklink exist but session data does not exist.
								$data['stacklinks']=array_reverse(array_merge($stackArr1,$mainStackArr));
							}

						}
						else{   //means whostack data does not exist.

							if(!empty($sessionstackArr))
							{
								//sesion data exist.

								$data['stacklinks']=array_reverse(array_merge($sessionstackArr,$mainStackArr));
							}
							else
							{
								//sesion data does not exist.
								$data['stacklinks']=array_reverse(array_merge($mainStackArr));
							}

						}
					}
					else
					{

						$getAllWhoStackLink=mysqli_query($conn,"SELECT reuestedIterationID FROM whostack_table WHERE imageID='".$imagerow['imageID']."'  AND  iterationID ='".$imagerow['iterationID']."'  AND  requestStatus =2 ");
						$commaVariable='';
						if(mysqli_num_rows($getAllWhoStackLink)>0)
						{
							while($allWhoStackLink=mysqli_fetch_assoc($getAllWhoStackLink))
							{
								$allWhoStackIterationID.=$commaVariable.$allWhoStackLink['reuestedIterationID'];
								$commaVariable=',';
							}
						}


						$WhoStackLinkIterationID=mysqli_query($conn,"select * from (SELECT  distinct SUBSTRING_INDEX(stacklinks,',',1) As  who_stacklink_name , iterationID FROM `iteration_table` where  iterationID in ($allWhoStackIterationID)) as stack_link_table where who_stacklink_name!='".$stackLinksArr[0]."'  ORDER BY FIELD(iterationID,$allWhoStackIterationID) desc ");

						if(mysqli_num_rows($WhoStackLinkIterationID)>0)
						{
							while($fetchWhoStackLinkIterationID=mysqli_fetch_assoc($WhoStackLinkIterationID))
							{

								$stackArrInfoData=explode('/',$fetchWhoStackLinkIterationID['who_stacklink_name']);

								$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

								$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
								$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
								$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
								$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
								$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
								if (in_array($stackUserInfo['id'], $getBlockUserList))
								{
									$stacked['stackuserdata']['blockUser']=1;
								}
								else
								{

									$stacked['stackuserdata']['blockUser']=0;
								}

								$stackArr['stackUserInfo']=$stacked['stackuserdata'];

								if($fetchWhoStackLinkIterationID['who_stacklink_name']=='home')
								{
									if($stackminiArr==$stackLinkType)
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=2;
									}

									$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
									$stackedRelated['stackrelateddata']['ID']='';
									$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
									$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
									$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
									$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
									$stackedRelated['stackrelateddata']['parentName']='';
									$stackedRelated['stackrelateddata']['url']='';
									$stackedRelated['stackrelateddata']['thumbUrl']='';
									$stackedRelated['stackrelateddata']['frame']='';
									$stackedRelated['stackrelateddata']['x']='';
									$stackedRelated['stackrelateddata']['y']='';
									$stackedRelated['stackrelateddata']['imageComments']='';
									$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
									$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
										$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];

									}
									else
									{
										$stackedRelated['stackrelateddata']['cubeID']=0;
										$stackedRelated['stackrelateddata']['profileStory']='0';
									}
								}
								else
								{

									if($fetchWhoStackLinkIterationID['who_stacklink_name']==$stackLinkType)
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=1;
									}
									else
									{
										$stackedRelated['stackrelateddata']['originateStackLink']=2;
									}
									$stackedRelated['stackrelateddata']['activeStacklink']=0;
									$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
									$stackDataFetchFromTagTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT lat,lng FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));
									$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
									$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
									$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
									$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
									$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
									$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromTagTable['lat'] == NULL ? '' : $stackDataFetchFromTagTable['lat']);
									$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
									$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
									$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromTagTable['lng'] == NULL ? '' : $stackDataFetchFromTagTable['lng']);
									$stackedRelated['stackrelateddata']['imageComments']=$stackDataFetchFromImageTable['image_comments'];
									$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';		
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
										$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];

									}
									else
									{
										$stackedRelated['stackrelateddata']['cubeID']=0;
										$stackedRelated['stackrelateddata']['profileStory']='0';
									}
									$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];

									if($stackDataFetchFromImageTable['type']==2)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));
						              $name = explode('_',$nameOfStack['stacklink_name']);
										if($name[1]=='profileStory')
										{
											$stackedRelated['stackrelateddata']['name']=$name[0];
										}
										else
										{
											$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										}
										//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
									}
									if($stackDataFetchFromImageTable['type']==3)
									{
										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];

									}
									if($stackDataFetchFromImageTable['type']==4)
									{

										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')"));

										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
									}
									if($stackDataFetchFromImageTable['type']==5)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID=(SELECT imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."')")) or die(mysqli_error());


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];
									}
									if($stackDataFetchFromImageTable['type']==6)
									{

										$stackedRelated['stackrelateddata']['url']='';
										$stackedRelated['stackrelateddata']['thumbUrl']='';

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']='';
									}
									if($stackDataFetchFromImageTable['type']==7)
									{

										$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
										$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

										$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
										$stackedRelated['stackrelateddata']['ID']='';
									}



									$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];

								}

								$data['stacklinks'][]=array_reverse($stackArr);


							}


						}
						$stackArrInfoData=explode('/',$stackLinksArr[0]);

						$stackUserInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id,username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
						AS profileimg,cover_image,fname FROM tb_user WHERE id='".$stackArrInfoData[0]."'"));

						$stacked['stackuserdata']['userID']=$stackUserInfo['id'];
						$stacked['stackuserdata']['userName']=$stackUserInfo['username'];
						$stacked['stackuserdata']['profileImg']=($stackUserInfo['profileimg']!='')?$stackUserInfo['profileimg']:'';
						$stacked['stackuserdata']['coverImage']=($stackUserInfo['cover_image'] == NULL ?  '' : $stackUserInfo['cover_image']);
						$stacked['stackuserdata']['firstName']=$stackUserInfo['fname'];
						if (in_array($stackUserInfo['id'], $getBlockUserList))
						{
							$stacked['stackuserdata']['blockUser']=1;
						}
						else
						{

							$stacked['stackuserdata']['blockUser']=0;
						}
						$stackArr['stackUserInfo']=$stacked['stackuserdata'];

						if($stackArrInfoData[1]=='home')
						{
							if($stackLinksArr[0]==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}

							$stackedRelated['stackrelateddata']['activeStacklink']=1;
							$stackedRelated['stackrelateddata']['userID']=$stackUserInfo['id'];
							$stackedRelated['stackrelateddata']['ID']='';
							$stackedRelated['stackrelateddata']['typeID']=($imagerow['imageID'] == NULL ? '' : $imagerow['imageID']);
							$stackedRelated['stackrelateddata']['iterationID']=stacklinkIteration($conn,$iterationId,'iteration',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$stackedRelated['stackrelateddata']['name']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							$stackedRelated['stackrelateddata']['parentName']='';
							$stackedRelated['stackrelateddata']['url']='';
							$stackedRelated['stackrelateddata']['thumbUrl']='';
							$stackedRelated['stackrelateddata']['frame']='';
							$stackedRelated['stackrelateddata']['x']='';
							$stackedRelated['stackrelateddata']['y']='';
							$stackedRelated['stackrelateddata']['imageComment']='';
							$stackedRelated['stackrelateddata']['parentType']="";
							$stackedRelated['stackrelateddata']['type']=stacklinkIteration($conn,$iterationId,'type',$imagerow['imageID'],$stackedRelated['stackrelateddata']['userID']);
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];

							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
								$stackedRelated['stackrelateddata']['profileStory']='0';
							}

							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
						else
						{

							$nameOfStack=mysqli_fetch_assoc(mysqli_query($conn,"SELECT userID,stacklink_name,imageID FROM iteration_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$stackDataFetchFromImageTable=mysqli_fetch_assoc(mysqli_query($conn,"SELECT *,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url FROM image_table WHERE imageID='".$nameOfStack['imageID']."'"));
							$stackDataFetchFromImageTable1=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$stackArrInfoData[1]."'))");
						
							if(mysqli_num_rows($stackDataFetchFromImageTable1)>0)
							{
								$stackDataFetchFromImageTableData=mysqli_fetch_assoc($stackDataFetchFromImageTable1);
								if($stackDataFetchFromImageTableData['type'] == 1)
								{
									$stackedRelated['stackrelateddata']['parentType']='';
								}
								else
								{
									$stackedRelated['stackrelateddata']['parentType']=$stackDataFetchFromImageTableData['type'];
								}
							}
							else
							{
								$stackedRelated['stackrelateddata']['parentType']="";
								
							}	
							if($stackLinksArr[0]==$stackLinkType)
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=1;
							}
							else
							{
								$stackedRelated['stackrelateddata']['originateStackLink']=2;
							}

							$stackedRelated['stackrelateddata']['activeStacklink']=1;
							$stackedRelated['stackrelateddata']['userID']=$nameOfStack['userID'];
							$stackedRelated['stackrelateddata']['typeID']=($stackDataFetchFromImageTable['imageID'] == NULL ? '' : $stackDataFetchFromImageTable['imageID']);;
							$stackedRelated['stackrelateddata']['ownerName']=getOwnerName(1,$stackDataFetchFromImageTable['imageID'],$conn);
							$stackedRelated['stackrelateddata']['sessionIterationID']=$newIterationID;
							$stackedRelated['stackrelateddata']['sessionImageID']=$row['imageID'];
							$stackedRelated['stackrelateddata']['iterationID']=$stackArrInfoData[1];  //new add
							$stackedRelated['stackrelateddata']['frame']=$stackDataFetchFromImageTable['frame'];
							$stackedRelated['stackrelateddata']['x']=($stackDataFetchFromImageTable['lat'] == NULL ? ''  : $stackDataFetchFromImageTable['lat']) ;
							$stackedRelated['stackrelateddata']['y']=($stackDataFetchFromImageTable['lng'] == NULL ? '' : $stackDataFetchFromImageTable['lng']);
							$stackedRelated['stackrelateddata']['imageComment']=$stackDataFetchFromImageTable['image_comments'];
							$stackedRelated['stackrelateddata']['type']=$stackDataFetchFromImageTable['type'];
							$stackedRelated['stackrelateddata']['parentName']=isset($imagerow['stacklink_name'])?$imagerow['stacklink_name']:'';
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID,profilestory FROM cube_table WHERE  FIND_IN_SET('".$stackArrInfoData[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$stackedRelated['stackrelateddata']['cubeID']=$cubeInfoData['id'];
								$stackedRelated['stackrelateddata']['profileStory']=$cubeInfoData['profilestory'];

							}
							else
							{
								$stackedRelated['stackrelateddata']['cubeID']=0;
								$stackedRelated['stackrelateddata']['profileStory']='0';
							}

							if($stackDataFetchFromImageTable['type']==2)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								//image Data
								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,imageID FROM image_url_table WHERE imageID='".$nameOfStack['imageID']."'"));
									$name = explode('_',$nameOfStack['stacklink_name']);
									if($name[1]=='profileStory')
									{
										$stackedRelated['stackrelateddata']['name']=$name[0];
									}
									else
									{
										$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
									}
								//$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['imageID'];
							}
							if($stackDataFetchFromImageTable['type']==3)
							{
								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,map_id FROM map_table WHERE imageID='".$nameOfStack['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['map_id'];
							}
							if($stackDataFetchFromImageTable['type']==4)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,grid_id FROM grid_table WHERE imageID='".$nameOfStack['imageID']."'"));

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['grid_id'];
							}
							if($stackDataFetchFromImageTable['type']==5)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';
								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT assigned_name,collectionID FROM collection_table WHERE collectionID='".$nameOfStack['imageID']."'")) or die(mysqli_error());

								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']=$stackImageTitle['collectionID'];

							}
							if($stackDataFetchFromImageTable['type']==6)
							{

								$stackedRelated['stackrelateddata']['url']='';
								$stackedRelated['stackrelateddata']['thumbUrl']='';

								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}
							if($stackDataFetchFromImageTable['type']==7)
							{

								$stackedRelated['stackrelateddata']['url']=$stackDataFetchFromImageTable['url'];
								$stackedRelated['stackrelateddata']['thumbUrl']=$stackDataFetchFromImageTable['thumb_url'];
								$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'")) ;


								$stackedRelated['stackrelateddata']['name']=$nameOfStack['stacklink_name'];
								$stackedRelated['stackrelateddata']['ID']='';
							}



							$stackArr['stackRelatedInfo']=$stackedRelated['stackrelateddata'];
						}
							$data['stacklinks'][]=array_reverse($stackArr);
					}
					//--------------------------------------------------------------------------------------------------


					$data['userinfo']['userName']=$newUsername[0];
					$data['userinfo']['userID']=$newUserID;
					if($newUsername[1]!='')
					{
						$data['userinfo']['profileImg']=$newUsername[1];
					}
					else
					{
						$data['userinfo']['profileImg']='';
					}



				/*--------------------------   call function fetch all swap child -----------------------------------*/

							// New Autorelated By jyoti

					if( $relatedThreadID=='' && $autorelatedID==''){



				$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' ORDER BY viewDate DESC limit 1");
					if(mysqli_num_rows($autorelated_session)>0){

						$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
						$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
						$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
						$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
						$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
					}
					else
					{



						 $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID,fdid,did FROM sub_iteration_table WHERE imgID='".$imageId."'  AND did=1 "));



							$autorelated_session1=mysqli_query($conn,"SELECT * FROM `new_auto_related` where   FIND_IN_SET('".$getSubIterationImageInfo['iterationID']."',autorelated)");
							if(mysqli_num_rows($autorelated_session1)>0){

								$autorelated_session2=mysqli_query($conn,"SELECT * FROM `new_auto_related` where iterationID ='".$iterationId."' ");
								if(mysqli_num_rows($autorelated_session2)>0){


								$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
								$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
								$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
								$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
								$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
								}
							}

						}
					}




			}
			/*--------------------------  SWAP SIBLING child -----------------------------------*/


			//new autorelated Fetch by jyoti
			//Add the auto related Linking code here.

		 		if( $relatedThreadID=='' && $autorelatedID==''){



				
				$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$iterationId."',tags) ") or die(mysqli_error());

				// $cubeInfoData=mysqli_num_rows($cubeInfoData);
				// if($cubeInfoData==1)
				// {
					// $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."'  ORDER BY viewDate DESC limit 1");
				// }
				// else
				// {
					$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' ORDER BY viewDate DESC limit 1");
				
				//}
				
				
					if(mysqli_num_rows($autorelated_session)>0){

						$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
						$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
						$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
						$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
						$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
					}
					else
					{



						 $autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){


							$stackImageTitle=mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM cube_table WHERE iterationID='".$stackArrInfoData[1]."'"));

							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iterationID,fdid,did FROM sub_iteration_table WHERE imgID='".$imageId."'  AND did=1 "));



							$autorelated_session1=mysqli_query($conn,"SELECT * FROM `new_auto_related` where   FIND_IN_SET('".$getSubIterationImageInfo['iterationID']."',autorelated)");
							if(mysqli_num_rows($autorelated_session1)>0){

								$autorelated_session2=mysqli_query($conn,"SELECT * FROM `new_auto_related` where iterationID ='".$iterationId."' ");
								if(mysqli_num_rows($autorelated_session2)>0){


								$fetchSessionData=mysqli_fetch_assoc($autorelated_session);
								$relatedThreadID=isset($fetchSessionData['threadID'])?$fetchSessionData['threadID']:$relatedThreadID;
								$autorelatedID=isset($fetchSessionData['currentIndex'])?$fetchSessionData['currentIndex']:$autorelatedID;
								$optionalOf=isset($fetchSessionData['optionalOf'])?$fetchSessionData['optionalOf']:$optionalOf;
								$optionalIndex=isset($fetchSessionData['optionalIndex'])?$fetchSessionData['optionalIndex']:$optionalIndex;
								}
							}

						}
					}




			}
			if( $relatedThreadID=='' && $autorelatedID=='')
			{

		
		//new autorelated by jyoti

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE new_auto_related.iterationID ='".$iterationId."' and new_auto_related.imageID ='".$imagerow['imageID']."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){


						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
				
						
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						
						
						$parentChild ['iterationID'] = $fetchNewAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						$parentChild ['threadID']=$fetchNewAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						
						
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$parentChild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$parentChild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoDatas)>0)
							{
								$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
								$parentChild ['cubeID']=$cubeInfoDatas['id'];

							}
							else
							{
								$parentChild ['cubeID']=0;
							}
						}
						$data['parentChild']=$parentChild;	
						 
						$data['countInfo']=count($arrayAuto);
						array_unshift($arrayAuto,$iterationId);


						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$fetchNewAutoRelated['iterationID'];
						$currentStack['imageID']=$fetchNewAutoRelated['imageID'];
						$currentStack['forwordrelatedID']=1;
						$currentStack['backwordrelatedID']='';
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';
						$data['CurrentStack']=$currentStack;

						$rightchild['iterationID']=$arrayAuto[1];
						$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[1]."'"));
						$rightchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
						$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
						$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
						if($getStackImageID['type'] == 7)
						{
							$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
						}
						else
						{
							
							$rightchild['videoUrl']='';
							$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
						}
						$rightchild['activeIterationID']=$newIterationID;
						$rightchild['userID']=$getStackImageID['userID'];
						$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
						$rightchild['title']=$getStackImageID['stacklink_name'];
						$rightchild['threadID']=$fetchNewAutoRelated['threadID'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[1]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$rightchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$rightchild['cubeID']=0;
							}
						}

						$data['autorealtedParentStackName']=$imagerow['stacklink_name'];
						if($getStackImageID['type'] == 1)
						{
							$data['rightChild']=array();
						}
						else
						{
							$data['rightChild']=$rightchild;
						}
						
						$data['leftChild']=array();
						$data['optionalChild']=array();

						//iteracation check in database

						$rId = $rightchild['iterationID'];
						$getrightchild =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $rId"));
						if(empty($getrightchild)){

							$data['rightChild']="";

						}

					}
				}

				else
				{

					$data['optionalChild']=array();
					$data['CurrentStack']=array();
					$data['rightChild']=array();
					$data['leftChild']=array();
				}
				/****Delete all back auto_related ****/
				$fetchbackwardDel =mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'");
				/**** end Delete all back auto_related ****/
			}
			else if($optionalOf=='' && $optionalIndex=='' && $relatedThreadID!='')
			{
				
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");

				$fetchNewAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");
				if(mysqli_num_rows($fetchNewAutoRelatedRes)>0){

					$fetchNewAutoRelated=mysqli_fetch_assoc($fetchNewAutoRelatedRes);

					if($fetchNewAutoRelated['autorelated']!=''){
						$arrayIndex=$autorelatedID;

						$arrayAuto=explode(',', $fetchNewAutoRelated['autorelated']);
						
						
						
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNewAutoRelated['iterationID']."'"));
					
				
			
					
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
							$data['parentImageUrl'] = $getImageDetail['url'];
							$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
							$parentChild ['type'] = $getImageDetail['type'];
						}
						else
						{
							$parentChild ['iterationID'] = '';
							$parentChild ['threadID']='';
							$data['parentImageUrl'] = '';
							$data['parentImageThumbUrl'] = '';
							$parentChild ['type'] = '';
						}
						
						
						//$data['parentImageUrl'] = $getImageDetail['url'];
						
						//$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$parentChild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$parentChild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoDatas)>0)
							{
								$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
								$parentChild ['cubeID']=$cubeInfoDatas['id'];

							}
							else
							{
								$parentChild ['cubeID']=0;
							}
						}
						$data['parentChild']=$parentChild;	
					
						if($arrayIndex == 0)
						{
							
							$data['countInfo']=count($arrayAuto);
							

						}
						else{
							$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNewAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';

						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							$lIndex="";
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex="";
							$lIndex=$leftIndex;
						}
						else{ //if current is neigther last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}
						if($arrayAuto[$lIndex] == $iterationId)
						{
							array_unshift($arrayAuto,$iterationId);
							$indexCount=count($arrayAuto);
							$rIndex=$arrayIndex+1;
							$lIndex=$arrayIndex-1;
							
						}
						

						$currentStack['threadID']=$fetchNewAutoRelated['threadID'];
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']='';
						$currentStack['optionalOf']='';


						//Right child Start
						if($rIndex !== ""){
							$rightchild['iterationID']=$arrayAuto[$rIndex];

							if($rightchild['iterationID'] == $iterationId ){
								$rightchild = "";

							}else{



							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));


							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));

							if($getStackImageID['type'] == 7)
							{
								$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
							}
							else
							{
								$rightchild['videoUrl']='';
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							}
							$rightchild['activeIterationID']=$newIterationID;
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							//$rightchild['userID']=$userId;
							$rightchild['userID']=$getStackImageID['userID'];
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
							$gettingParentOfParentType = $gettingCubeData['type'];
							if($gettingParentOfParentType  ==6)
							{
							
								
								$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
								$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
					
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								    $rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}
							else
							{
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}

							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];

							//Right child End
						 }

						}else{ $rightchild = ""; }

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;

							if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						if($lIndex !==""){
						//Left Child start
						$leftchild['iterationID']=$arrayAuto[$lIndex];
						
						
						
						$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where iterationID ='".$arrayAuto[$lIndex]."'"));

						$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNewAutoRelated['iterationID']."'"));
						$leftchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
						$leftchild['activeIterationID']=$newIterationID;
						$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
						$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
						if($getStackImageID1['type'] == 7)
						{
							$leftchild['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							$leftchild['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
						}
						else
						{
							$leftchild['videoUrl']='';
							$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
						}
					
						//$leftchild['userID']=$userId;
						$leftchild['userID']=$getStackImageID1['userID'];
						$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
						$leftchild['title']=$getStackImageID1['stacklink_name'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$arrayAuto[$lIndex]."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$leftchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$leftchild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayAuto[$lIndex]."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$leftchild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$leftchild['cubeID']=0;
							}
						}

						$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE threadID ='".$relatedThreadID."' and userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
						if(mysqli_num_rows($autorelated_session)>0){

							$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

							if($fetchNewAutoRelated['threadID']!=''){
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
							}
							else
							{
								$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								$leftchild['autoRelatedID']=$lIndex;
							}
						}
						else
						{
							$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
							$leftchild['autoRelatedID']=$lIndex;
						}
						//$leftchild['threadID']=$relatedThreadID;
						//$leftchild['autoRelatedID']=$lIndex;
									//Left Child End
						$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
						if($data['autorealtedParentStackName']){
							$parent_name = $data['autorealtedParentStackName'];
						}else{
							$parent_name = '';
						}
						//Left Child End

						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];

							if($iteration_count==0){

									$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
									VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
								}
						}


						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."'"));

							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));


						$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						if($fetchbackwardleft['type'] == 7)
						{
							$backRelated['videoUrl']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
							$backRelated['url']=($fetchbackwardleft['thumb_url']!='')? $fetchbackwardleft['thumb_url']:'';
						}
						else
						{
							$backRelated['videoUrl']='';
							$backRelated['url']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
						}
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iteration_id']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}

						}

					} else { $leftchild = ""; }

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
							if($getStackImageID['type'] == 1)
							{
								$data['rightChild']='';
							}
							else
							{
								$data['rightChild']=$rightchild;
							}
  							
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						//$data['leftChild']=$leftchild;

						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}
						if($backRelated['iterationID']  == $arrayAuto[$lIndex]){
							$data['backRelated']=$backRelated;
						}else{
							$data['backRelated']="";
							/****Delete all back auto_related ****/
							$fetchbackwardDel =mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$userId."'"));
							/**** end Delete all back auto_related ****/


						}

						//Optional child autorelated start

						//500 entries should be maintained only by jyoti

						$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
						if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){
							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and   order by viewDate desc limit 1");

							$result = mysqli_fetch_assoc($autorelated_session);

							if(mysqli_num_rows($autorelated_session)<1){
									$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());
							}
							elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
							else
							{
								$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
							}
						}
						else{

							$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
							if($autorelated_session_delete){
								$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex)
									VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."')") or die(mysqli_error());

							}
						}

						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){

							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);

							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								//echo 'ffffffffffffff'.$optionalCount=count($arrayOptionalAuto);
								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									if($getStackImageID['type'] == 7)
									{
										$optionalchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
									}
									else
									{
										$optionalchild['videoUrl']='';
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									}
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									
									$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
									$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
									$gettingParentOfParentType = $gettingCubeData['type'];
									if($gettingParentOfParentType  ==6)
									{
									
										
										$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
										$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
							
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									else
									{
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}



								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								//$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								if($getStackImageID['type'] == 7)
								{
									$rightOptional['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
								}
								else
								{
									$rightOptional['videoUrl']='';
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								}
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								//$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								if($getStackImageID1['type'] == 7)
								{
									$leftOptional['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
								}
								else
								{
									$leftOptional['videoUrl']='';
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								}
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								$data['leftOptional']="";

								//Check iterationID exists in database
								$opid = $optionalchild['iterationID'];
								$ropid = $rightOptional['iterationID'];

								$queryGetitreaction =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $opid"));
								if(empty($queryGetitreaction)){
									$data['optionalChild']="";

								}
								$queryGetrightoption =mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `iteration_table` where iterationID = $ropid"));
								if(empty($queryGetitreaction)){
									$data['rightOptional']="";

								}


							}
						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";
						}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";
						}
				}
				//new autorelated by jyoti end

			}
			else
			{


				//new autorelated by jyoti
				$unixTimeStamp=date("Y-m-d"). date("H:i:s");
				if($optionalOf==$iterationId){


					$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE threadID ='".$relatedThreadID."'");

			 		$arrayIndex=$optionalIndex-1;

			 	}
			 	else{
			 		$fetchNormalAutoRelatedRes=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE iterationID ='".$optionalOf."'");
			 		$arrayIndex=$autorelatedID;

			 	}
				if(mysqli_num_rows($fetchNormalAutoRelatedRes)>0){

					$fetchNormalAutoRelated=mysqli_fetch_assoc($fetchNormalAutoRelatedRes);
					if($fetchNormalAutoRelated['autorelated']!=''){

						$arrayAuto=explode(',', $fetchNormalAutoRelated['autorelated']);
						$getImageDetail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.userID,iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
						WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$fetchNormalAutoRelated['iterationID']."'"));
						
						$getChildDetail=mysqli_query($conn,"SELECT iteration_table.* ,new_auto_related.threadID as newthreadID  FROM `tag_table`  INNER JOIN iteration_table on `tag_table`.`iterationID`=iteration_table.iterationID  inner join new_auto_related ON new_auto_related.iterationID=iteration_table.iterationID where tag_table.linked_iteration='".$iterationId."' and iteration_table.imageID ='".$getImageDetail['imageID']."'");
						if(mysqli_num_rows($getChildDetail)>0)
						{
							$getChildDetail=mysqli_fetch_assoc($getChildDetail);
							$parentChild ['iterationID'] = $getChildDetail['iterationID'];
							$parentChild ['threadID']=$getChildDetail['newthreadID'];
						}
						else
						{
							$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
							$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						}
						//$parentChild ['iterationID'] = $fetchNormalAutoRelated['iterationID'];
						$data['parentImageUrl'] = $getImageDetail['url'];
						$parentChild ['type'] = $getImageDetail['type'];
						$data['parentImageThumbUrl'] = $getImageDetail['thumb_url'];
						//$parentChild ['threadID']=$fetchNormalAutoRelated['threadID'];
						$parentChild ['userID']=$userId;
						$parentChild ['imageID']=$getImageDetail['imageID'];
						$parentChild ['relatedID']=0;
						$parentChild ['ownerName']=getOwnerName(1,$getImageDetail['imageID'],$conn);
						if($getImageDetail['type'] == 7)
						{
							$parentChild['videoUrl']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
							$parentChild['url']=($getImageDetail['thumb_url']!='')? $getImageDetail['thumb_url']:'';
						}
						else
						{
							$parentChild['videoUrl']='';
							$parentChild['url']=($getImageDetail['url']!='')? $getImageDetail['url']:'';
						}
						$parentChild ['title']=$getImageDetail['stacklink_name'];
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$parentChild['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$parentChild['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoDatas=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchNewAutoRelated['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoDatas)>0)
							{
								$cubeInfoDatas=mysqli_fetch_assoc($cubeInfoDatas);
								$parentChild ['cubeID']=$cubeInfoDatas['id'];

							}
							else
							{
								$parentChild ['cubeID']=0;
							}
						}
						$data['parentChild']=$parentChild;	
						
                       if($arrayIndex == 0)
						{
							$data['countInfo']=count($arrayAuto);

						}
						else{
								$key = array_search($iterationId, $arrayAuto);
							$data['countInfo']=1+$key.'/'.count($arrayAuto);
						}
						array_unshift($arrayAuto,$fetchNormalAutoRelated['iterationID']);
						$indexCount=count($arrayAuto);
						if($arrayIndex<0){
							$arrayIndex=$indexCount-1;
						}
						$rightIndex=$arrayIndex+1;
						$leftIndex=$arrayIndex-1;
						$lIndex='';
						$fIndex='';
						if($arrayIndex == 0){ //if current is first item then main iteration is left child
							$rIndex=$rightIndex;
							//$lIndex=$indexCount-1;
							$lIndex='';
						}
						else if($arrayIndex==$indexCount-1){ //if current is last item then right should be first to repeat
							$rIndex='';
							//$rIndex=0;
							$lIndex=$leftIndex;
						}
						else{ //if current is neigth last nor first
							$rIndex=$rightIndex;
							$lIndex=$leftIndex;
						}



						$currentStack['threadID']=$relatedThreadID;
						$currentStack['iterationID']=$iterationId;
						$currentStack['imageID']=$imageId;
						$currentStack['forwordrelatedID']=$rIndex;
						$currentStack['backwordrelatedID']=$lIndex;
						$currentStack['optionalIndex']=$optionalIndex;
						$currentStack['optionalOf']=$optionalOf;

						if($rIndex!==""){
							//Right child Start
							$rightchild['iterationID']=$arrayAuto[$rIndex];
							$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$rIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));
							$rightchild['activeIterationID']=$newIterationID;
						//	$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							$rightchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
							$rightchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
							$rightchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
							//$rightchild['userID']=$userId;
							$rightchild['userID']=$getStackImageID['userID'];
							$rightchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
							$rightchild['title']=$getStackImageID['stacklink_name'];
							$rightchild['threadID']=$relatedThreadID;
							$rightchild['autoRelatedID']=$rIndex;
							if($getStackImageID['type'] == 7)
							{
								$rightchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								$rightchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
							}
							else
							{
								$rightchild['videoUrl']='';
								$rightchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							
							$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
							$gettingParentOfParentType = $gettingCubeData['type'];
							if($gettingParentOfParentType  ==6)
							{
							
								
								$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
								$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
					
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}
							else
							{
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$rightchild['iterationID']."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$rightchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$rightchild['cubeID']=0;
								}
							}
							//Right child End
						}else{
							$rightchild = "";
						}

							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];
							$fetchbackwardcount = $fetchbackwardcountfinal+1;



						if($lIndex!==""){
							//Left Child start
							$leftchild['iterationID']=$arrayAuto[$lIndex];
							$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayAuto[$lIndex]."'"));

							$getautorealted_parent=mysqli_fetch_assoc(mysqli_query($conn,"SELECT stacklink_name FROM iteration_table where iterationID='".$fetchNormalAutoRelated['iterationID']."'"));

							//$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							if($getStackImageID1['type'] == 7)
							{
								$leftchild['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								$leftchild['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
							}
							else
							{
								$leftchild['videoUrl']='';
								$leftchild['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
							}
							$leftchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
							$leftchild['activeIterationID']=$newIterationID;
							$leftchild['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
							$leftchild['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
							//$leftchild['userID']=$userId;
							$leftchild['userID']=$getStackImageID1['userID'];
							$leftchild['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
							$leftchild['title']=$getStackImageID1['stacklink_name'];
							
							$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
							$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
							$gettingParentOfParentType = $gettingCubeData['type'];
							if($gettingParentOfParentType  ==6)
							{
							
								
								$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$arrayAuto[$lIndex]."'))");
								$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
					
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftchild['cubeID']=0;
								}
							}
							else
							{
								$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$leftchild['iterationID']."',tags) ") or die(mysqli_error());
								if(mysqli_num_rows($cubeInfoData)>0)
								{
									$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
									$leftchild['cubeID']=$cubeInfoData['id'];

								}
								else
								{
									$leftchild['cubeID']=0;
								}
							}
							/* $leftchild['threadID']=$relatedThreadID;
							$leftchild['autoRelatedID']=$lIndex; */

							$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$getStackImageID1['imageID']."' and iterationID ='".$arrayAuto[$lIndex]."' ORDER BY viewDate DESC limit 1");
							if(mysqli_num_rows($autorelated_session)>0){

								$fetchNewAutoRelated=mysqli_fetch_assoc($autorelated_session);

								if($fetchNewAutoRelated['threadID']!=''){
									$leftchild['threadID']=$fetchNewAutoRelated['threadID'];
								    $leftchild['autoRelatedID']=$fetchNewAutoRelated['currentIndex'];
								}
								else
								{
									$leftchild['threadID']=$relatedThreadID;
									$leftchild['autoRelatedID']=$lIndex;
								}
							}
							else
							{
								$leftchild['threadID']=$relatedThreadID;
								$leftchild['autoRelatedID']=$lIndex;
							}
							$data['autorealtedParentStackName']=$getautorealted_parent['stacklink_name'];
							if($data['autorealtedParentStackName']){
								$parent_name = $data['autorealtedParentStackName'];
							}else{
								$parent_name = '';

							}
							//Left Child End
						if($forwardChild == 1){
							$fetchcount_it=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(iteration_id) as iteration_count  FROM autorelated_backward WHERE user_id ='".$loginUserId."' and iteration_id ='".$leftchild['iterationID']."'"));
							$iteration_count = $fetchcount_it['iteration_count'];


							if($iteration_count == 0){
								// echo "INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								// VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')";die;

								$insertback=mysqli_query($conn,"INSERT INTO autorelated_backward(user_id,iteration_id,url,imageID,type,ownerName,title,threadID,autorelated,optionalof,optional_index,serial_number,parent_name)
								VALUES('".$loginUserId."','".$leftchild['iterationID']."','".$leftchild['url']."','".$leftchild['imageID']."','".$leftchild['type']."','".$leftchild['ownerName']."','".$leftchild['title']."','".$leftchild['threadID']."','".$leftchild['autoRelatedID']."','".$optionalOf."','".$optionalIndex."','".$fetchbackwardcount."','".$parent_name."')") or die(mysqli_error());
							}
						}

						if($forwardChild == 0){
							$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

							$fetchbackwardDel==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_backward WHERE user_id = '".$loginUserId."' AND serial_number = '".$fetchbackwardcountfinal."'"));

						}

						$fetchbackward=mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(user_id) as totalcount FROM autorelated_backward WHERE user_id ='".$loginUserId."' ORDER BY serial_number"));
							$fetchbackwardcountfinal = $fetchbackward['totalcount'];

						$fetchsrcount = $fetchbackwardcountfinal;
						$fetchbackwardleft=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM autorelated_backward WHERE user_id ='".$loginUserId."' and serial_number ='".$fetchsrcount."'"));

							$backRelated['userID']=$userId;
						$backRelated['iterationID']=isset($fetchbackwardleft['iteration_id'])?$fetchbackwardleft['iteration_id']:'';
						$backRelated['imageID']=isset($fetchbackwardleft['imageID'])?$fetchbackwardleft['imageID']:'';
						//$backRelated['url']=isset($fetchbackwardleft['url'])?$fetchbackwardleft['url']:'';
						$backRelated['ownerName']=isset($fetchbackwardleft['ownerName'])?$fetchbackwardleft['ownerName']:'';
						$backRelated['type']=isset($fetchbackwardleft['type'])?$fetchbackwardleft['type']:'';
						if($backRelated['type'] == 7)
						{
							$backRelated['videoUrl']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
							$backRelated['url']=($fetchbackwardleft['thumb_url']!='')? $fetchbackwardleft['thumb_url']:'';
						}
						else
						{
							$backRelated['videoUrl']='';
							$backRelated['url']=($fetchbackwardleft['url']!='')? $fetchbackwardleft['url']:'';
						}
						$backRelated['title']=isset($fetchbackwardleft['title'])?$fetchbackwardleft['title']:'';
						$backRelated['threadID']=isset($fetchbackwardleft['threadID'])?$fetchbackwardleft['threadID']:'';
						$backRelated['autorelated']=isset($fetchbackwardleft['autorelated'])?$fetchbackwardleft['autorelated']:'';
						$backRelated['optionalIndex']=isset($fetchbackwardleft['optional_index'])?$fetchbackwardleft['optional_index']:'';
						$backRelated['optionalOf']=isset($fetchbackwardleft['optionalof'])?$fetchbackwardleft['optionalof']:'';
						$backRelated['parent_name']=isset($fetchbackwardleft['parent_name'])?$fetchbackwardleft['parent_name']:'';
						
						
						$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
						$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
						$gettingParentOfParentType = $gettingCubeData['type'];
						if($gettingParentOfParentType  ==6)
						{
						
							
							$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
							$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
				
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}
						}
						else
						{
							$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$fetchbackwardleft['iterationID']."',tags) ") or die(mysqli_error());
							if(mysqli_num_rows($cubeInfoData)>0)
							{
								$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
								$backRelated['cubeID']=$cubeInfoData['id'];

							}
							else
							{
								$backRelated['cubeID']=0;
							}
						}

						if($backRelated['parent_name']){

							$data['autorealtedParentStackName'] = $backRelated['parent_name'];

						}

						}else{
							$leftchild = "";
						}

						$data['CurrentStack']=$currentStack;
						if($rightchild['imageID'] != "")
						{
  							$data['rightChild']=$rightchild;
						}else{
							$data['rightChild']= "";
						}

						if($leftchild['imageID'] !="")
						{
							$data['leftChild']=$leftchild;
						}else{
							$data['leftChild']="";
						}



						// $data['rightChild']=$rightchild;
						// $data['leftChild']=$leftchild;
						if($fetchbackwardcountfinal == 0){
							$data['backRelated']="";
						}else{
							$data['backRelated']=$backRelated;
						}



						$fetchOptionalAutoRelated=mysqli_query($conn,"SELECT * FROM new_auto_related WHERE imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID !='".$relatedThreadID."'");
						if(mysqli_num_rows($fetchOptionalAutoRelated)>0){
							$optionalAutoRelated=mysqli_fetch_assoc($fetchOptionalAutoRelated);
							if($optionalAutoRelated['autorelated']!=''){
								$arrayOptionalAuto=explode(',', $optionalAutoRelated['autorelated']);
								$optionalCount1=count($arrayOptionalAuto);
								array_unshift($arrayOptionalAuto,$optionalAutoRelated['iterationID']);
								$optionalCount=count($arrayOptionalAuto);


								$optionalchild['iterationID']=$arrayOptionalAuto[1];
								if($rightchild['iterationID'] != $arrayOptionalAuto[1])
								{

									$data['optionalCountInfo'] =$optionalCount1;

									$getStackImageID=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
									WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[1]."'"));
									//$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									if($getStackImageID['type'] == 7)
									{
										$optionalchild['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
										$optionalchild['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
									}
									else
									{
										$optionalchild['videoUrl']='';
										$optionalchild['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									}
									$optionalchild['typeID']=isset($imagerow['imageID'])?$imagerow['imageID']:'';
									$optionalchild['activeIterationID']=$newIterationID;
									$optionalchild['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
									$optionalchild['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
									$optionalchild['userID']=$userId;
									$optionalchild['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
									$optionalchild['title']=$getStackImageID['stacklink_name'];
									$optionalchild['threadID']=$optionalAutoRelated['threadID'];
									$optionalchild['forwordrelatedID']=1;
									$optionalchild['backwordrelatedID']='';
									
									$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
									$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
									$gettingParentOfParentType = $gettingCubeData['type'];
									if($gettingParentOfParentType  ==6)
									{
									
										
										$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
										$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
							
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									else
									{
										$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
										if(mysqli_num_rows($cubeInfoData)>0)
										{
											$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
											$optionalchild['cubeID']=$cubeInfoData['id'];

										}
										else
										{
											$optionalchild['cubeID']=0;
										}
									}
									$data['optionalChild']=$optionalchild;


								}
								else
								{
									$data['optionalChild']="";
								}


								$rightOptional['iterationID']=$arrayOptionalAuto[1];
								//$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								if($getStackImageID['type'] == 7)
								{
									$rightOptional['videoUrl']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
									$rightOptional['url']=($getStackImageID['thumb_url']!='')? $getStackImageID['thumb_url']:'';
								}
								else
								{
									$rightOptional['videoUrl']='';
									$rightOptional['url']=($getStackImageID['url']!='')? $getStackImageID['url']:'';
								}
								$rightOptional['imageID']=isset($getStackImageID['imageID'])?$getStackImageID['imageID']:'';
								$rightOptional['type']=isset($getStackImageID['type'])?$getStackImageID['type']:'';
								$rightOptional['userID']=$userId;
								$rightOptional['ownerName']=getOwnerName(1,$getStackImageID['imageID'],$conn);
								$rightOptional['title']=$getStackImageID['stacklink_name'];
								$rightOptional['threadID']=$optionalAutoRelated['threadID'];
								$rightOptional['autoRelatedID']=1;
								$rightOptional['threadID']=$arrayOptionalAuto[1];
										
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$rightOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$rightOptional['cubeID']=0;
									}
								}
								$data['rightOptional']=$rightOptional;

								$getStackImageID1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT iteration_table.stacklink_name,image_table.imageID,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
								WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,type,iteration_table.userID FROM iteration_table inner join image_table on iteration_table.imageID=image_table.imageID where  iterationID='".$arrayOptionalAuto[$optionalCount-1]."'"));

								$leftOptional['iterationID']=$arrayOptionalAuto[$optionalCount-1];
								//$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								if($getStackImageID1['type'] == 7)
								{
									$leftOptional['videoUrl']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
									$leftOptional['url']=($getStackImageID1['thumb_url']!='')? $getStackImageID1['thumb_url']:'';
								}
								else
								{
									$leftOptional['videoUrl']='';
									$leftOptional['url']=($getStackImageID1['url']!='')? $getStackImageID1['url']:'';
								}
								$leftOptional['imageID']=isset($getStackImageID1['imageID'])?$getStackImageID1['imageID']:'';
								$leftOptional['type']=isset($getStackImageID1['type'])?$getStackImageID1['type']:'';
								$leftOptional['userID']=$userId;
								$leftOptional['ownerName']=getOwnerName(1,$getStackImageID1['imageID'],$conn);
								$leftOptional['title']=$getStackImageID1['stacklink_name'];
								$leftOptional['threadID']=$getStackImageID1['threadID'];
								$leftOptional['autoRelatedID']=$optionalCount-1;
								$leftOptional['threadID']=$arrayOptionalAuto[1];
								
								$checkingCubeData=mysqli_query($conn,"SELECT type FROM image_table WHERE imageID=(SELECT imageID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."')))");
								$gettingCubeData=mysqli_fetch_assoc($checkingCubeData);
								$gettingParentOfParentType = $gettingCubeData['type'];
								if($gettingParentOfParentType  ==6)
								{
								
									
									$checkingCubeData1=mysqli_query($conn,"SELECT imageID,iterationID FROM iteration_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID=(SELECT linked_iteration FROM tag_table WHERE iterationID='".$iterationId."'))");
									$gettingCubeDatainfo=mysqli_fetch_assoc($checkingCubeData1);
						
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  iterationID ='".$gettingCubeDatainfo['iterationID']."'") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								else
								{
									$cubeInfoData=mysqli_query($conn,"SELECT id,iterationID,imageID FROM cube_table WHERE  FIND_IN_SET('".$arrayOptionalAuto[$optionalCount-1]."',tags) ") or die(mysqli_error());
									if(mysqli_num_rows($cubeInfoData)>0)
									{
										$cubeInfoData=mysqli_fetch_assoc($cubeInfoData);
										$leftOptional['cubeID']=$cubeInfoData['id'];

									}
									else
									{
										$leftOptional['cubeID']=0;
									}
								}
								if($optionalOf==''){
									$data['leftOptional']=$leftOptional;
								}else{
									$data['leftOptional']="";
								}

								$optionalSession=1;
								$optionalOfSession=$optionalOf;
								$optionalIndexSession=$optionalIndex;
								$optionalRightIndex=1;
								$optionalLeftIndex=$optionalCount-1;
								$optionalRightID=$arrayOptionalAuto[1];
								$optionalLeftID=$arrayOptionalAuto[$optionalCount-1];

							}

						}
						else
						{
							$data['optionalChild']="";
							$data['rightOptional']="";
							$data['leftOptional']="";

								$optionalSession=0;
								$optionalOfSession='';
								$optionalIndexSession='';
								$optionalRightIndex='';
								$optionalLeftIndex='';
								$optionalRightID='';
								$optionalLeftID='';
						}
							//500 entries should be maintained only by jyoti
								$optionalSession = isset($optionalSession)?$optionalSession:0;
								$optionalOfSession = isset($optionalOfSession)?$optionalOfSession:0;
								$autorelated_session_count==mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as count FROM autorelated_session WHERE userID='".$loginUserId."'"));
								if($autorelated_session_count=='' || ($autorelated_session_count['count']<500)){

									$autorelated_session=mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID ='".$relatedThreadID."' order by viewDate desc limit 1");
									$result = mysqli_fetch_assoc($autorelated_session);

									if(mysqli_num_rows($autorelated_session)<1){
											$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
											VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')");


									}
									//$optionalOf=='' && $optionalIndex
									elseif($result['optionalOf']!=$optionalOf){
										/***Need to update**/
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
									else
									{
										$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
									}
								}
								else{

									$autorelated_session_delete==mysqli_fetch_assoc(mysqli_query($conn,"DELETE FROM autorelated_session WHERE userID = '".$loginUserId."' ORDER BY viewDate LIMIT 1"));
									if($autorelated_session_delete){
										$autorelated_session==mysqli_query($conn,"SELECT * FROM autorelated_session WHERE userID='".$loginUserId."' and imageID ='".$imageId."' and iterationID ='".$iterationId."' and threadID =='".$relatedThreadID."' order by viewDate desc limit 1");
											$result = mysqli_fetch_assoc($autorelated_session);
											if(mysqli_num_rows($autorelated_session)<1){
												$insertLastViewTable=mysqli_query($conn,"INSERT INTO autorelated_session(userID,iterationID,imageID,threadID,viewDate,currentIndex,leftID,leftIndex,rightID,rightIndex,optional,optionalOf,optionalIndex,optionalRight,optionalLeft)
												VALUES('".$loginUserId."','".$iterationId."','".$imageId."','".$relatedThreadID."','".strtotime($unixTimeStamp)."','".$arrayIndex."','".$arrayAuto[$lIndex]."','".$lIndex."','".$arrayAuto[$rIndex]."','".$rIndex."','".$optionalSession."','".$optionalOfSession."','".$optionalIndexSession."','".$optionalRightIndex."','".$optionalLeftIndex."')") or die(mysqli_error());
											}
											elseif($result['optionalOf']!=$optionalOf){
											/***Need to update**/
											$updateLastView=mysqli_query($conn,"update autorelated_session set viewDate='".strtotime($unixTimeStamp)."', optional='1', optionalOf='".$optionalOf."', optionalIndex='".$optionalIndex."' where iterationID='".$iterationId."' and imageID ='".$imageId."' and threadID='".$relatedThreadID."'");
											}
									}

								}

					}
					else
						{
							$data['optionalChild']=array();
							$data['CurrentStack']=array();
							$data['rightChild']="";
							$data['leftChild']="";

						}
				}
				//new autorelated by jyoti end

			}


			$pdata['parent']=$data;

			//fetch child here

					


					$selectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.username,tag_table.lat,tag_table.lng,tag_table.frame FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE iteration_table.imageID not in ($allBlockImageID) and iteration_table.stack_visible=0 AND iteration_table.iterationID IN(SELECT tag_table.iterationID FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.linked_iteration WHERE iteration_table.threadID='".$imagerow['threadID']."' AND  iteration_table.iterationID='".$iterationId."')");

					if(mysqli_num_rows($selectChild) > 0)
					{

						while($childImageRow=mysqli_fetch_assoc($selectChild))
						{

							$data1['iterationIgnore']=$childImageRow['iteration_ignore'];
							$getSubIterationImageInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT fdid,did FROM sub_iteration_table WHERE imgID='".$childImageRow['imageID']."'  AND iterationID='".$childImageRow['iterationID']."' "));
							
							
							$getSessionIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");

							$getSessionIterationIDInfo1 = mysqli_num_rows($getSessionIterationIDInfo);


							if($getSessionIterationIDInfo1 > 0 )
							{

								$IterationIDInfo=mysqli_fetch_assoc($getSessionIterationIDInfo);

									
								$data1['sessionIterationID']=$IterationIDInfo['iterationID'];
								$data1['sessionImageID']=$childImageRow['imageID'];



							}
							else
							{

								$data1['sessionIterationID']=$childImageRow['iterationID'];
								$data1['sessionImageID']=$childImageRow['imageID'];
							}





							$getIterationIDInfo=mysqli_query($conn,"SELECT iterationID,user_id,stack_type FROM user_table WHERE imageID='".$childImageRow['imageID']."'  AND  find_in_set('".$getSubIterationImageInfo['did']."',fdid)  and user_id='".$loginUserId."' order by datetime desc limit 1");


							if(mysqli_num_rows($getIterationIDInfo)>0 )
							{
								$IterationIDInfo=mysqli_fetch_assoc($getIterationIDInfo);

								if($IterationIDInfo['stack_type']=='1')
								{

									$data1['apiUse']=1; //related

								}
								else
								{
									$data1['apiUse']=0; //normal
								}

							}
							else{
								$data1['apiUse']=0; //normal

							}

							if($getSubIterationImageInfo['did'] == 1 and  $childImageRow['userID'] == $loginUserId )
							{
							$data1['deleteTag']=1;
							}
							else
							{
							$data1['deleteTag']=0;
							}





							$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
							WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,image_table.lat,image_table.lng,tb_user.username,tb_user.profileimg,tb_user.id FROM image_table INNER JOIN tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$childImageRow['imageID']."'"));


							$data1['coverPhoto']=array();

							$coverPhoto['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
							$coverPhoto['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
							$data1['coverPhoto'][]=$coverPhoto;


							if($selectChildImageData['type']==4)
							{
								$data1['profilepic']=array();
								$userProfilePic=mysqli_query($conn,"select CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg from tb_user where id in(SELECT userID FROM `like_table` where imageID='".$childImageRow['imageID']."' and iterationID='".$childImageRow['iterationID']."' order by likeDate,likeTime desc)limit 3");

								while($userProfile=mysqli_fetch_assoc($userProfilePic))
								{

									$profileData['profilePic']=$userProfile['profileimg'];
									$data1['profilepic'][]=$profileData;

								}
								$data1['userID']=$childImageRow['username'];
								$data1['stacklinks']=$childImageRow['stacklinks'];
								$data1['userName']=$newUsername[0];
								$data1['iterationID']=$childImageRow['iterationID'];
								$data1['typeID']=$childImageRow['imageID'];
								$data1['imageID']=$childImageRow['imageID'];
								$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
								$data1['name']=$childImageRow['stacklink_name'];
								$data1['title']=$childImageRow['stacklink_name'];
								$data1['type']=$selectChildImageData['type'];
								$data1['frame']=$childImageRow['frame'];
								$data1['x']= ($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
								$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);
								$data1['creatorUserId']=$childImageRow['userID'];





								$data1['smiles']=($childImageRow['smiles'] == NULL ? '' : $childImageRow['smiles'] );
								$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$childImageRow['imageID']."' and iterationID='".$childImageRow['iterationID']."' and  userID ='".$loginUserId."' ");

								if(mysqli_num_rows($likeImage) > 0)
								{
								//
									$data1['like']=1;

								}
								else
								{
									$data1['like']=0;
								}
								$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) or die(mysqli_error());
								$data1['ID']=$selectGrid[0];
								$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
								$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
								$data1['insidechild']=array();

							}
							if($selectChildImageData['type']==2)
							{

								$data1['profilepic']=array();
								$userProfilePic=mysqli_query($conn,"select CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg from tb_user where id in(SELECT userID FROM `like_table` where imageID='".$childImageRow['imageID']."' and iterationID='".$childImageRow['iterationID']."' order by likeDate,likeTime desc)limit 3");
								
								while($userProfile=mysqli_fetch_assoc($userProfilePic))
								{

									$profileData['profilePic']=$userProfile['profileimg'];
									$data1['profilepic'][]=$profileData;

								}


								$data1['userID']=$childImageRow['username'];
								$data1['stacklinks']=$childImageRow['stacklinks'];
								$data1['userName']=$newUsername[0];
								$data1['iterationID']=$childImageRow['iterationID'];
								$data1['typeID']=$childImageRow['imageID'];
								$data1['imageID']=$childImageRow['imageID'];
								$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
								$data1['name']=$childImageRow['stacklink_name'];
								$data1['title']=$childImageRow['stacklink_name'];
								$data1['type']=$selectChildImageData['type'];
								$data1['frame']=$childImageRow['frame'];
								$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
								$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);
								$data1['smiles']=($childImageRow['smiles'] == NULL ? '' : $childImageRow['smiles']);
								$data1['creatorUserId']=$childImageRow['userID'];

								$likeImage=mysqli_query($conn,"select id from like_table where  imageID='".$childImageRow['imageID']."' and iterationID='".$childImageRow['iterationID']."' and  userID ='".$loginUserId."' ");

								if(mysqli_num_rows($likeImage) > 0)
								{
								//
									$data1['like']=1;

								}
								else
								{
									$data1['like']=0;
								}

								$data1['ID']=$selectChildImageData['imageID'];
								$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
								$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
								$data1['insidechild']=array();
							}
							if($selectChildImageData['type']==3)
							{
								$data1['userID']=$childImageRow['username'];
								$data1['stacklinks']=$childImageRow['stacklinks'];
								$data1['userName']=$newUsername[0];
								$data1['iterationID']=$childImageRow['iterationID'];
								$data1['typeID']=$childImageRow['imageID'];
								$data1['imageID']=$childImageRow['imageID'];
								$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
								$data1['name']=$childImageRow['stacklink_name'];
								$data1['title']=$childImageRow['stacklink_name'];
								$data1['type']=$selectChildImageData['type'];
								$data1['frame']=$childImageRow['frame'];
								$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
								$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']) ;
								$data1['smiles']=($childImageRow['smiles'] == NULL ? '' : $childImageRow['smiles']);
								$data1['creatorUserId']=$childImageRow['userID'];

								$likeImage=mysqli_query($conn,"select * from like_table where  imageID='".$childImageRow['imageID']."' and iterationID='".$childImageRow['iterationID']."' and  userID ='".$loginUserId."' ");

								if(mysqli_num_rows($likeImage) > 0)
								{

									$data1['like']=1;

								}
								else
								{
									$data1['like']=0;
								}

								$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'")) or die(mysqli_error());
								$data1['ID']=$selectMap[0];

								$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
								$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
								$data1['insidechild']=array();
							}

							if($selectChildImageData['type']==5)
							{

								$data1['profilepic']=array();
								$userProfilePic=mysqli_query($conn,"select CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
								AS profileimg from tb_user where id in(SELECT userID FROM `like_table` where imageID='".$childImageRow['imageID']."' and iterationID='".$childImageRow['iterationID']."' order by likeDate,likeTime desc)limit 3");

								while($userProfile=mysqli_fetch_assoc($userProfilePic))
								{

									$profileData['profilePic']=$userProfile['profileimg'];
									$data1['profilepic'][]=$profileData;

								}

								$data1['userID']=$childImageRow['username'];
								$data1['stacklinks']=$childImageRow['stacklinks'];
								$data1['userName']=$newUsername[0];
								$data1['iterationID']=$childImageRow['iterationID'];
								$data1['typeID']=$childImageRow['imageID'];
								$data1['imageID']=$childImageRow['imageID'];
								$data1['ownerName']=getOwnerName(1,$childImageRow['imageID'],$conn);
								$data1['name']=$childImageRow['stacklink_name'];
								$data1['title']=$childImageRow['stacklink_name'];
								$data1['type']=$selectChildImageData['type'];
								$data1['frame']=$childImageRow['frame'];
								$data1['x']=($childImageRow['lat'] == NULL ? '' : $childImageRow['lat']);
								$data1['y']=($childImageRow['lng'] == NULL ? '' : $childImageRow['lng']);
								$data1['creatorUserId']=$childImageRow['userID'];

								$data1['smiles']=($childImageRow['smiles'] == NULL ? '' : $childImageRow['smiles']);
								$likeImage=mysqli_query($conn,"select * from like_table where  imageID='".$childImageRow['imageID']."' and iterationID='".$childImageRow['iterationID']."' and  userID ='".$loginUserId."' ");

								if(mysqli_num_rows($likeImage) > 0)
								{

									$data1['like']=1;

								}
								else
								{
									$data1['like']=0;
								}


								$data1['ID']=$selectChildImageData['imageID'];

								$data1['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
								$data1['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';



								$data1['insidechild']='';




								$subselectChild=mysqli_query($conn,"SELECT iteration_table.*,tag_table.username,tag_table.lat,tag_table.lng,tag_table.frame FROM iteration_table INNER JOIN tag_table ON iteration_table.iterationID=tag_table.iterationID WHERE tag_table.linked_iteration='".$childImageRow['iterationID']."' limit 2");


								if(mysqli_num_rows($subselectChild) > 0)
								{

									while($subchildImageRow=mysqli_fetch_assoc($subselectChild))
									{
										$data12['profilepic']='';
										$userProfilePic=mysqli_query($conn,"select CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
										AS profileimg from tb_user where id in(SELECT userID FROM `like_table` where imageID='".$subchildImageRow['imageID']."' and iterationID='".$subchildImageRow['iterationID']."' order by likeDate,likeTime desc)limit 3");

										while($userProfile=mysqli_fetch_assoc($userProfilePic))
										{

											$profileData['profilePic']=$userProfile['profileimg'];
											$data12['profilepic'][]=$profileData;

										}

										$data12['userID']=$subchildImageRow['username'];
										$data12['name']=$subchildImageRow['stacklink_name'];
										$data12['userName']=$newUsername[0];
										$data12['iterationID']=$subchildImageRow['iterationID'];
										$data12['imageID']=$subchildImageRow['imageID'];
										$data12['smiles']=($subchildImageRow['smiles'] == NULL ? '' : $subchildImageRow['smiles']);
										$likeImage=mysqli_query($conn,"select * from like_table where  imageID='".$subchildImageRow['imageID']."' and iterationID='".$subchildImageRow['iterationID']."' and  userID ='".$loginUserId."' ");

										if(mysqli_num_rows($likeImage) > 0)
										{

											$data12['like']=1;

										}
										else
										{
											$data12['like']=0;
										}



										$selectChildImageData=mysqli_fetch_assoc(mysqli_query($conn,"SELECT image_table.webUrl,image_table.location,image_table.addSpecification,image_table.imageID,image_table.type,CASE WHEN url IS NULL OR url = '' THEN '' WHEN url LIKE 'albumImages/%' THEN concat( '$serverurl/', url ) 
										WHEN url LIKE '/albumImages/%' THEN concat( '$serverurl', url ) ELSE url END AS url,CASE WHEN thumb_url IS NULL OR thumb_url = '' THEN '' WHEN thumb_url LIKE '/albumImages/%' THEN concat( '$serverurl', thumb_url  ) WHEN thumb_url LIKE 'albumImages/%' THEN concat( '$serverurl/', thumb_url  ) ELSE thumb_url END AS thumb_url,image_table.frame,tb_user.username,CASE WHEN profileimg IS NULL OR profileimg = '' THEN '' WHEN profileimg LIKE 'albumImages/%' THEN concat( '$serverurl/', profileimg ) ELSE profileimg END
										AS profileimg,tb_user.id FROM image_table INNER JOIN  tb_user ON image_table.userID=tb_user.id WHERE image_table.imageID='".$subchildImageRow['imageID']."'"));



										$data12['webUrl']=isset($subchildImageRow['webUrl'])?$subchildImageRow['webUrl']:'';
										$data12['location']=isset($subchildImageRow['location'])?$subchildImageRow['location']:'';
										$data12['addSpecification']=isset($subchildImageRow['addSpecification'])?$subchildImageRow['addSpecification']:'';
										$data12['typeID']=$subchildImageRow['imageID'];

										$data12['type']=$selectChildImageData['type'];
										if($selectChildImageData['type']==4)
										{
											$selectGrid=mysqli_fetch_row(mysqli_query($conn,"SELECT grid_id FROM grid_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
											$data12['ID']=$selectGrid[0];
											$data12['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
											$data12['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';

										}
										if($selectChildImageData['type']==2)
										{

											$data12['ID']=$selectChildImageData['imageID'];
											$data12['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
											$data12['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
										}
										if($selectChildImageData['type']==5)
										{
											$data12['ID']=$selectChildImageData['imageID'];
											$data12['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
											$data12['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';

										}
										 if($selectChildImageData['type']==3)
										{
											$selectMap=mysqli_fetch_row(mysqli_query($conn,"SELECT map_id FROM map_table WHERE imageID='".$selectChildImageData['imageID']."'")) ;
											$data12['ID']=$selectMap[0];
											$data12['url']=($selectChildImageData['url']!='')? $selectChildImageData['url']:'';
											$data12['thumbUrl']=($selectChildImageData['thumb_url']!='')? $selectChildImageData['thumb_url']:'';
										}


										$data12['frame']=$subchildImageRow['frame'];
										$data12['x']=($subchildImageRow['lat'] == NULL ? '' : $subchildImageRow['lat'] );
										$data12['y']=($subchildImageRow['lng'] == NULL ? '' : $subchildImageRow['lng']);


										$data12['userinfo']['userName']=$selectChildImageData['username'];
										$data12['userinfo']['userId']=$selectChildImageData['id'];
										if($selectChildImageData['profileimg']!='')
										{
											$data12['userinfo']['profileImg']=$selectChildImageData['profileimg'];
										}
										else
										{
											$data12['userinfo']['profileImg']='';
										}
										$getSubIterationInfo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT  count( userID) as totalcount from iteration_table where adopt_photo=0 and iterationID in(SELECT iterationID FROM `sub_iteration_table` where imgID='".$subchildImageRow['imageID']."')"));
										// echo "<pre>";print_r($getSubIterationInfo);die;
										$data12['countImageShare']=$getSubIterationInfo['totalcount'];
										// echo "<pre>";print_r($data12);die;

									$data1['insidechild']=$data12;




									}


								}
								else
								{
									$data1['insidechild']=array();
								}


								$newIterationID=$iterationId;

							}



							$data1['userinfo']['userName']=$selectChildImageData['username'];
							$data1['userinfo']['userID']=$selectChildImageData['id'];
							if($selectChildImageData['profileimg']!='')
							{
								$data1['userinfo']['profileImg']=$selectChildImageData['profileimg'];
							}
							else
							{
								$data1['userinfo']['profileImg']='';
							}
							if($childImageRow['adopt_photo']==0)
							{
								$data1['adoptChild']=0;
							}
							else
							{
								$data1['adoptChild']=1;
							}


							if($data1['adoptChild']==1 )
							{


								if(($loginUserId== $data1['creatorUserId'] || $loginUserId==$data['creatorUserID']))
								{

									$cdata['child'][]=$data1;

								}


							}
							else
							{
								$cdata['child'][]=$data1;
							}


							$adopt_photo_type=mysqli_fetch_assoc(mysqli_query($conn,"select type from adopt_table where iterationID='".$imagerow['iterationID']."' and adopt_iterationID='".$childImageRow['iterationID']."'"));
							$data1['adoptPhoto']=isset($adopt_photo_type['type'])?$adopt_photo_type['type']:'';
						}

					}



					if(empty($cdata))
					{
						$cdata['child']=array();
					}
					if(empty($pdata))
					{
						$pdata['parent']=array();
					}

					$totalData=array_merge($pdata,$cdata);

				}
			}
			else
			{
				echo json_encode(array('message'=>'There is no relevant data','success'=>0));
				exit;
			}
		}
	}
	else
	{
		echo json_encode(array('message'=>'There is no relevant data','success'=>0));
		exit;
	}



}


if(!empty($totalData))
{
	echo json_encode(array('data'=>$totalData,'success'=>1));
	exit;
}
else
{
	echo json_encode(array('data'=>array(),'success'=>0));
	exit;
}

}
else
{
	echo json_encode(array('message'=>'Insufficient Data','success'=>0));
	exit;
}
?>
