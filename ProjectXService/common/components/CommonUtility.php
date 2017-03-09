<?php
namespace  common\components;

use common\models\mongo\TicketCollection;
use common\models\mysql\Priority;
use common\models\mysql\Projects;
use common\models\mysql\WorkFlowFields;
use common\models\mongo\TinyUserCollection;
use common\models\mysql\Bucket;
use common\models\mysql\TicketType;
use common\models\mysql\StoryFields;
use common\models\mysql\StoryCustomFields;
use common\models\mysql\FieldTypes;
use common\models\mysql\MapListCustomStoryFields;
use common\models\mysql\PlanLevel;
use Yii;
/* 
 *
 * @author Moin Hussain
 */
class CommonUtility {
   /**
    * @author Moin Hussain
    * @param type $object
    * @param type $type
    * @return type
    */
    public static function prepareResponse($object,$type = "json"){
        if($type == "json"){
           \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }else{
            \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        }
        return $object;
    }
    /**
     * @author Moin Hussain
     * @param type $str
     * @return string
     */
     public static function getExtension($str) {
  try{
    $i = strrpos($str, ".");
    if (!$i) {
        return "";
    }

    $l = strlen($str) - $i;
    $ext = substr($str, $i + 1, $l);
    //$ext .= '_'.$_SESSION['user']->id;
    return $ext;
    } catch (Exception $ex) {
            Yii::log("CommonUtility:getExtension::".$ex->getMessage()."--".$ex->getTraceAsString(), 'error', 'application');
        }
}
    /**
     * @author Moin Hussain
     * @param type $sec
     * @param type $to_tz
     * @param type $from_tz
     * @param type $type
     * @return type
     */
     static function convert_time_zone($sec, $to_tz, $from_tz = "", $type = "") {
        try {
            $date_time = date("Y-m-d H:i:s", $sec);
            if ($from_tz == "" || $from_tz == "undefined") {
                $from_tz = date_default_timezone_get();
            }
            if ($to_tz == "" || $to_tz == "undefined") {
                $to_tz = date_default_timezone_get();
            }
            $time_object = new \DateTime($date_time, new \DateTimeZone($from_tz));
            $time_object->setTimezone(new \DateTimeZone($to_tz));
            if ($type == "sec") {
                return strtotime($time_object->format('m-d-Y H:i:s'));
            } else {
                return $time_object->format('d-m-Y H:i:s');
            }
        } catch (Exception $ex) {
            Yii::log("CommonUtility:convert_time_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @param type $date
     * @return type
     */
    public static function validateDate($date){
        error_log("-----------------".$date);
       $date =  preg_replace("/\([^)]+\)/","",$date);
         error_log("------------afet-----".$date);
     if((bool)strtotime($date)){
         return $date;
     }else{
          return FALSE;
     }
    }
    /**
     * @author Moin Hussain
     * @param type $sec
     * @param type $to_tz
     * @param type $from_tz
     * @param type $type
     * @return type
     */
      static function convert_date_zone($sec, $to_tz, $from_tz = "", $type = "") {
        try {
            $date_time = date("Y-m-d H:i:s", $sec);
            if ($from_tz == "" || $from_tz == "undefined") {
                $from_tz = date_default_timezone_get();
            }
            if ($to_tz == "" || $to_tz == "undefined") {
                $to_tz = date_default_timezone_get();
            }
            $time_object = new \DateTime($date_time, new \DateTimeZone($from_tz));
            $time_object->setTimezone(new \DateTimeZone($to_tz));
           if ($type == "sec") {
                return strtotime($time_object->format('Y-m-d H:i:s'));
            } else {
                return $time_object->format('d-m-Y');
            }
        } catch (Exception $ex) {
            Yii::log("CommonUtility:convert_date_zone::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @description This method is to prepare ticket details
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function prepareTicketDetails($ticketDetails,$projectId,$flag = "part"){
        try{
             $ticketCollectionModel = new TicketCollection();
           // $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId);
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel =  new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            $ticketTypeModel = new TicketType();
            foreach ($ticketDetails["Fields"] as &$value) {
               if(isset($value["custom_field_id"] )){
                $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                 if($storyFieldDetails["Name"] == "List"){
                
                    $listDetails = $mapListModel->getListValue($value["Id"],$value["value"]);
                    $value["readable_value"] = $listDetails; 
                }
                
                
                
               }else{
                 $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
   
               }
                $value["position"] = $storyFieldDetails["Position"];
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];
                if($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5){
                       if($value["value"] != ""){
                             $datetime = $value["value"]->toDateTime();
                     if($storyFieldDetails["Type"] == 4){
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $readableDate = $datetime->format('m-d-Y');
                     }else{
                         $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                         $readableDate = $datetime->format('m-d-Y H:i:s');
                     }
                     $value["readable_value"] =   $readableDate; 
                       }else{
                            $value["readable_value"] = "";
                       }
                   
                 }
                if($storyFieldDetails["Type"] == 6){
                     $value["readable_value"]="";
                    if($value["value"] != ""){
                         $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                        $assignedToDetails["ProfilePicture"] = Yii::$app->params['ServerURL'].$assignedToDetails["ProfilePicture"];
                        $value["readable_value"] = $assignedToDetails;  
                    }
                
                }
                 if($storyFieldDetails["Type"] == 10){
                 $value["readable_value"]= "";
                if($value["value"] != ""){
                 $bucketName = $bucketModel->getBucketName($value["value"],$ticketDetails["ProjectId"]);
                 $value["readable_value"] = $bucketName;  
                }
                }
                if($storyFieldDetails["Field_Name"] == "priority"){
                    $value["readable_value"]= "";
                if($value["value"] != ""){
                    $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                    $value["readable_value"] = $priorityDetails;
                }
                }
                 if($storyFieldDetails["Field_Name"] == "planlevel"){
                 $value["readable_value"]= "";
                if($value["value"] != ""){
                    $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                    $value["readable_value"] = $planlevelDetails; 
                     $ticketDetails["StoryType"] = $planlevelDetails;
                }
                }
                 if($storyFieldDetails["Field_Name"] == "workflow"){
                
                    $value["readable_value"]= "";
                if($value["value"] != ""){
                    $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                     $value["readable_value"] = $workFlowDetails; 
                }
                }
                 if($storyFieldDetails["Field_Name"] == "tickettype"){
                    $value["readable_value"]= "";
                if($value["value"] != ""){
                 $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
                 $value["readable_value"] = $ticketTypeDetails;
                }
                }
               
               
                
                
            }
            usort($ticketDetails["Fields"], function($a, $b)
            {
               // echo $a["position"]."\n";
                return $a["position"] >= $b["position"];
            });
         //  return $ticketDetails["Fields"];
            
           // $ticketDetails["Fields"]="";
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;
            
            $selectFields = [];
            if($flag == "part"){
               $selectFields = ['Title', 'TicketId'];

            }
            foreach ($ticketDetails["Tasks"] as &$task) {
                 $taskDetails = $ticketCollectionModel->getTicketDetails($task,$projectId,$selectFields);
                 $task = $taskDetails;
            }
            foreach ($ticketDetails["RelatedStories"] as &$relatedStory) {
                 $relatedStoryDetails = $ticketCollectionModel->getTicketDetails($relatedStory,$projectId,$selectFields);
                 $relatedStory = $relatedStoryDetails;
            }
            
            
            unset( $ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
          

            return $ticketDetails;
        } catch (Exception $ex) {
Yii::log("CommonUtility:prepareTicketDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
     /**
     * @description This method is to prepare ticket edit details
     * @author Moin Hussain
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public static function prepareTicketEditDetails($ticketId,$projectId){
        try{
             $ticketCollectionModel = new TicketCollection();
            $ticketDetails = $ticketCollectionModel->getTicketDetails($ticketId,$projectId);
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel =  new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            $ticketTypeModel = new TicketType();
            
          
            foreach ($ticketDetails["Fields"] as &$value) {
               if(isset($value["custom_field_id"] )){
                $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                 if($storyFieldDetails["Name"] == "List"){
                
                    $listDetails = $mapListModel->getListValue($value["Id"],$value["value"]);
                    $value["readable_value"] = $listDetails; 
                }
                
                
                
               }else{
                 $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
   
               }
                $value["position"] = $storyFieldDetails["Position"];
                $value["title"] = $storyFieldDetails["Title"];
                $value["required"] = $storyFieldDetails["Required"];
                $value["readonly"] = $storyFieldDetails["ReadOnly"];
                $value["field_type"] = $storyFieldDetails["Name"];
                $value["field_name"] = $storyFieldDetails["Field_Name"];
                
                 
                   if($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5){
                       if($value["value"] != ""){
                             $datetime = $value["value"]->toDateTime();
                     if($storyFieldDetails["Type"] == 4){
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $readableDate = $datetime->format('m-d-Y');
                     }else{
                          $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                         $readableDate = $datetime->format('m-d-Y H:i:s');
                     }
                     $value["readable_value"] =   $readableDate; 
                       }else{
                            $value["readable_value"] = "";
                       }
                   
                 }
                 
                 
                 
                 
                 
                if($storyFieldDetails["Type"] == 6){
                  $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                  $value["readable_value"] = $assignedToDetails;  
                  
                }
                 if($storyFieldDetails["Type"] == 10){
                
                 $bucketName = $bucketModel->getBucketName($value["value"],$ticketDetails["ProjectId"]);
                 $value["readable_value"] = $bucketName;  
                  $value["meta_data"] = $bucketModel->getBucketsList($projectId);
                }
                if($storyFieldDetails["Field_Name"] == "priority"){
                
                    $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                    $value["readable_value"] = $priorityDetails; 
                    $value["meta_data"] = $priorityModel->getPriorityList();
                }
                 if($storyFieldDetails["Field_Name"] == "planlevel"){
                
                    $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                    $value["readable_value"] = $planlevelDetails; 
                     $ticketDetails["StoryType"] = $planlevelDetails;
                    $value["meta_data"] = $planlevelModel->getPlanLevelList();
                }
                 if($storyFieldDetails["Field_Name"] == "workflow"){
                
                   
                    $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                    $value["readable_value"] = $workFlowDetails; 
                    $value["meta_data"] = $workFlowModel->getStoryWorkFlowList();
                }
                 if($storyFieldDetails["Field_Name"] == "tickettype"){
                   
                 $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
                 $value["readable_value"] = $ticketTypeDetails; 
                 $value["meta_data"] = $ticketTypeModel->getTicketTypeList();
                }
               
               
                
                
            }
            $ticketDetails['collaborators'] = ServiceFactory::getCollaboratorServiceInstance()->getProjectTeam($projectId);
            usort($ticketDetails["Fields"], function($a, $b)
            {
               // echo $a["position"]."\n";
                return $a["position"] >= $b["position"];
            });
         //  return $ticketDetails["Fields"];
            
           // $ticketDetails["Fields"]="";
            $projectObj = new Projects();
            $projectDetails = $projectObj->getProjectMiniDetails($ticketDetails["ProjectId"]);
            $ticketDetails["Project"] = $projectDetails;
            
           
            
            unset( $ticketDetails["CreatedOn"]);
            unset($ticketDetails["UpdatedOn"]);
            unset( $ticketDetails["ArtifactsRef"]);
            unset($ticketDetails["CommentsRef"]);
          

            return $ticketDetails;
        } catch (Exception $ex) {
Yii::log("CommonUtility:prepareTicketEditDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    /**
     * @author Moin Hussain
     * @param type $description
     * @return type
     */
  public static function refineDescription($description){
      try{
         
           $description = preg_replace("/<a(.*?)>/", "<a$1 target=\"_blank\">", $description);
              $matches=[];
              preg_match_all("/\[\[\w+:\w+\/\w+(\|[A-Z0-9\s-_+#$%^&()*a-z]+\.\w+)*\]\]/", $description, $matches);
              $filematches = $matches[0];
              for($i = 0; $i< count($filematches); $i++){
                   $value = $filematches[$i];
                   $firstArray =  explode("/", $value);
                   $secondArray = explode("|", $firstArray[1]);
                   $tempFileName = $secondArray[0];
                   $originalFileName = $secondArray[1];
                   $originalFileName = str_replace("]]", "", $originalFileName);
                   $storyArtifactPath = Yii::$app->params['ProjectRoot']. Yii::$app->params['StoryArtifactPath'] ;
                   if(!is_dir($storyArtifactPath)){
                       if(!mkdir($storyArtifactPath, 0775,true)){
                           Yii::log("CommonUtility:refineDescription::Unable to create folder--" . $ex->getTraceAsString(), 'error', 'application');
                       }
                   }
                $newPath = Yii::$app->params['ServerURL'].Yii::$app->params['StoryArtifactPath']."/".$tempFileName."-".$originalFileName;
                if(file_exists("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName")){
                    rename("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName", $storyArtifactPath."/".$tempFileName."-".$originalFileName); 
                }
               
               $extension = CommonUtility::getExtension($originalFileName);
                 $imageExtensions = array("jpg", "jpeg", "gif", "png"); 
              
               if(in_array($extension, $imageExtensions)){
                $replaceString = "<img src='".$newPath."'/>";
             
                }else{
                   $replaceString = "<a href='".$newPath."' target='_blank'/>".$originalFileName."</a>";  
                }
               $description = str_replace($value, $replaceString, $description);
              } 
              
              return $description;
                      
               
      } catch (Exception $ex) {
Yii::log("CommonUtility:refineDescription::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
      }
  }
  
 /**
  * @author Moin Hussain
  * @param type $ticketDetails
  * @param type $projectId
  * @param type $fieldsOrderArray
  * @param type $flag
  * @return array
  */
   public static function prepareDashboardDetails($ticketDetails,$projectId,$fieldsOrderArray,$flag = "part"){
        try{
             $ticketCollectionModel = new TicketCollection();
            $storyFieldsModel = new StoryFields();
            $storyCustomFieldsModel = new StoryCustomFields();
            $tinyUserModel =  new TinyUserCollection();
            $bucketModel = new Bucket();
            $priorityModel = new Priority();
            $mapListModel = new MapListCustomStoryFields();
            $planlevelModel = new PlanLevel();
            $workFlowModel = new WorkFlowFields();
            $ticketTypeModel = new TicketType();
            $newArray = array();
            
            $arr2ordered = array() ;

            $ticketId = array("field_name"=>"Id","value_id"=>"","field_value"=>$ticketDetails["TicketId"],"other_data"=>"");
            $ticketTitle = array("field_name"=>"Title","value_id"=>"","field_value"=>$ticketDetails["Title"],"other_data"=>"");

            array_push($arr2ordered,$ticketId);
            array_push($arr2ordered,$ticketTitle);
             $arr2ordered[1]["other_data"] = sizeof($ticketDetails["Tasks"]);
             $Othervalue=array();
            foreach ($ticketDetails["Fields"] as $key=>$value) {
                
                if($key == "planlevel"){
                    //$arr2ordered[0]["other_data"] = $value["value"];
                    $Othervalue["planlevel"]=$value["value"];
                    $Othervalue["totalSubtasks"]=sizeof($ticketDetails["Tasks"]);
                    $arr2ordered[0]["other_data"] = $Othervalue ;
                }
                if(in_array($value["Id"], $fieldsOrderArray)){
                    
               if(isset($value["custom_field_id"] )){
                $storyFieldDetails = $storyCustomFieldsModel->getFieldDetails($value["Id"]);
                 if($storyFieldDetails["Name"] == "List"){
                
                    $listDetails = $mapListModel->getListValue($value["Id"],$value["value"]);
                    $value["readable_value"] = $listDetails; 
                }
                
                
                
               }else{
                 $storyFieldDetails = $storyFieldsModel->getFieldDetails($value["Id"]);
   
               }
                 $value["title"] = $storyFieldDetails["Title"];

                $value["field_name"] = $storyFieldDetails["Field_Name"];
                if($storyFieldDetails["Type"] == 4 || $storyFieldDetails["Type"] == 5){
                       if($value["value"] != ""){
                             $datetime = $value["value"]->toDateTime();
                     if($storyFieldDetails["Type"] == 4){
                        $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                        $readableDate = $datetime->format('m-d-Y');
                     }else{
                         $datetime->setTimezone(new \DateTimeZone("Asia/Kolkata"));
                         $readableDate = $datetime->format('m-d-Y H:i:s');
                     }
                     $value["readable_value"] =   $readableDate; 
                       }else{
                            $value["readable_value"] = "";
                       }
                   
                 }
                if($storyFieldDetails["Type"] == 6){
                     $value["readable_value"]="";
                    if($value["value"] != ""){
                         $assignedToDetails = $tinyUserModel->getMiniUserDetails($value["value"]);
                        $assignedToDetails["ProfilePicture"] = Yii::$app->params['ServerURL'].$assignedToDetails["ProfilePicture"];
                        $value["readable_value"] = $assignedToDetails;  
                    }
                
                }
                 if($storyFieldDetails["Type"] == 10){
                 $value["readable_value"]= "";
                if($value["value"] != ""){
                 $bucketName = $bucketModel->getBucketName($value["value"],$ticketDetails["ProjectId"]);
                 $value["readable_value"] = $bucketName;  
                }
                }
                if($storyFieldDetails["Field_Name"] == "priority"){
                    $value["readable_value"]= "";
                if($value["value"] != ""){
                    $priorityDetails = $priorityModel->getPriorityDetails($value["value"]);
                    $value["readable_value"] = $priorityDetails;
                }
                }
                 if($storyFieldDetails["Field_Name"] == "planlevel"){
                 $value["readable_value"]= "";
                if($value["value"] != ""){
                    $planlevelDetails = $planlevelModel->getPlanLevelDetails($value["value"]);
                    $value["readable_value"] = $planlevelDetails; 
                     $ticketDetails["StoryType"] = $planlevelDetails;
                }
                }
                 if($storyFieldDetails["Field_Name"] == "workflow"){
                
                    $value["readable_value"]= "";
                if($value["value"] != ""){
                    $workFlowDetails = $workFlowModel->getWorkFlowDetails($value["value"]);
                     $value["readable_value"] = $workFlowDetails; 
                }
                }
                 if($storyFieldDetails["Field_Name"] == "tickettype"){
                    $value["readable_value"]= "";
                if($value["value"] != ""){
                 $ticketTypeDetails = $ticketTypeModel->getTicketType($value["value"]);
                 $value["readable_value"] = $ticketTypeDetails;
                }
                }
                  if($storyFieldDetails["Field_Name"] == "dod"){
                    $value["readable_value"]= "";
                    if($value["value"] != ""){
                     $value["readable_value"] =$value["value"];
                    }
                }
                 if($storyFieldDetails["Field_Name"] == "estimatedpoints"){
                    $value["readable_value"]= "";
                    if($value["value"] != ""){
                     $value["readable_value"] =$value["value"];
                    }
                }
                
                $tempArray = array("field_name" => "", "value_id" => "", "field_value" => "", "other_data" => "");
                    $tempArray["field_name"] = $value["field_name"];
                    $tempArray["value_id"] = $value["value"];

                    if (isset($value["readable_value"]["UserName"])) {
                        $tempArray["field_value"] = $value["readable_value"]["UserName"];
                        $tempArray["other_data"] = $value["readable_value"]["ProfilePicture"];
                    } else if (isset($value["readable_value"]["Name"])) {
                        $tempArray["field_value"] = $value["readable_value"]["Name"];
                    } else {
                        $tempArray["field_value"] = $value["readable_value"];
                    }
                    $newArray[$value["Id"]] = $tempArray;
                }
            }
            foreach ($fieldsOrderArray as $key) {
                array_push($arr2ordered, $newArray[$key]);
            }
            $arrow = array("field_name"=>"arrow","value_id"=>"","field_value"=>"","other_data"=>"");
            $arrow['other_data']=sizeof($ticketDetails["Tasks"]);
            array_push($arr2ordered, $arrow);
            unset($ticketDetails["Fields"]);
            $ticketDetails=$arr2ordered;
            return $ticketDetails;
        } catch (Exception $ex) {
Yii::log("CommonUtility:prepareDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
}

?>
