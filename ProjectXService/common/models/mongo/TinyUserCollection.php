<?php
namespace common\models\mongo;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * refer : 
 * https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/usage-ar.md
 */
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\base\ErrorException;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;

class TinyUserCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'TinyUserCollection';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
 "_id" ,      
     "CollaboratorId",
     "UserName",
     "FirstName",
     "LastName",
     "ProfilePicture",
     "Email",
    "OrganizationId",
    'CreatedOn',
    'UpdatedOn',
        ];
    }
    
    public function behaviors()
    {
        return [
                       'timestamp' => [
                'class' => '\yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['CreatedOn', 'UpdatedOn'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['UpdatedOn'],
                ],
                 'value' => function() { return new \MongoDB\BSON\UTCDateTime(time() * 1000); // unix timestamp 
                 },
            ],
        ];
       
    }
    
       public static function createNewUser($user,$userId){
        
        try {
        error_log("======================3##################=================");
        $userObj= new TinyUserCollection();
        $userObj->CollaboratorId = (int)$userId;
       // $userObj->UserName=$user->firstName.'.'.$user->lastName;
         $userObj->FirstName=$user->firstName;
         $userObj->LastName=$user->lastName;
         $userObj->UserName=$user->displayName;
       // $userObj->DisplayName=$user->displayName;
        $userObj->ProfilePicture=$user->userProfileImage;
        $userObj->Email=$user->email;
        $userObj->OrganizationId= 1;
        $userObj->insert();  
        unset($userObj);
    
        } catch (\Throwable $ex) {
            Yii::error("TinyUserCollection:createNewUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    public static function getMiniUserDetails($collaboratorId){
        
        $query = new Query();
        // compose the query
          $query->select(['CollaboratorId','FirstName','LastName','UserName','ProfilePicture','Email',])
                ->from('TinyUserCollection')
            ->where(['CollaboratorId' => (int)$collaboratorId ]);
        // execute the query
        $userDetails = $query->one();
        $userDetails["ProfilePicture"] = Yii::$app->params['ServerURL'].$userDetails["ProfilePicture"];
       return $userDetails;
     
    }
      public static function createUsers($data){
        
        try {
//        $collection = Yii::$app->mongodb->getCollection('TinyUserCollection');
//        $collection->batchInsert($data);
        foreach ($data as $value) {
        $userObj= new TicketCollection();
         $userObj->Title="hi";
        $userObj->CollaboratorId=(int)$value['Id'];
        $userObj->UserName=$value['UserName'];
        $userObj->ProfilePicture='';
        $userObj->Email=$value['Email'];
        $userObj->OrganizationId=  (int)$value['OrganizationId'];
        $userObj->insert();  
        unset($userObj);
        }
        } catch (\Throwable $ex) {
            Yii::error("TinyUserCollection:createUsers::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    public static function getProfileOfFollower($follower) {
        try {
            $query = new Query();
            $query->from('TinyUserCollection')
                    ->where(['CollaboratorId' => (int) $follower]);
            $profile_pic = $query->one();
            return $profile_pic["ProfilePicture"];
        } catch (\Throwable $ex) {
            Yii::error("TinyUserCollection:getProfileOfFollower::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

}
?>
