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
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use common\models\mysql\Collaborators;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;

class NotificationCollection extends ActiveRecord 
{
    public static function collectionName()
    {
        return 'NotificationCollection';
    }
    
    public function attributes() {
//        parent::attributes();
        return [
      "_id",      
     "TicketId",
     "ProjectId",
     "NotifiedCollaborators",
     "Notification_Type",
     "ActivityFrom",
     "Status",
     "ActivityOn",
     "NotificationDate",
     "OldValue",
     "NewValue",
     "CommentSlug",
     "TargetTicketId"
    
   
        ];
    }
    
//    public function behaviors()
//    {
//        return [
//            TimestampBehavior::className(),
//        ];
//    }

    /**
     * @author Ryan Marshal  
     * @description This method is used for getting all the notifications for user.
     * @param type $user
     * @param type $projectId
     * @return array
     */ 
    
    public static function getNotificationDetails($notificationIds){
        try{
           $query=new Query();
            $query->from('NotificationCollection')
            ->where(["_id" => array('$in'=>$notificationIds)]);
            $notifications = $query->all();
            return $notifications;  
        } catch (\Throwable $ex) {
            Yii::error("NotificationCollection:getNotificationDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     public static function getNotificationsCount($user)
    { 
         try{
          $query=new Query();
          $cond=array("NotifiedCollaborators"=>array('$elemMatch'=>array('CollaboratorId'=>(int) $user,'IsRead'=>(int)0,'SystemNotification'=>(int)1)));
          $query->from('NotificationCollection')
            ->where($cond)
            ->andWhere(['!=','ActivityFrom', (int)$user]);
            $notificationsCount=$query->count();
            return $notificationsCount;
         } catch (\Throwable $ex) {
            Yii::error("NotificationCollection:getNotificationsCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
     }
    
      public static function getNotifications($user,$offset=0,$limit=5,$viewAll=0,$asActivity=false)
      { 
           try{
           $cond=array();    
            
          if($viewAll==1){
          if(!$asActivity)
          $cond=array("NotifiedCollaborators"=>array('$elemMatch'=>array('CollaboratorId'=>(int) $user,'SystemNotification'=>(int)1)));
          else 
          $cond=array("NotifiedCollaborators"=>array('$elemMatch'=>array('CollaboratorId'=>(int) $user)));   
          }else{
          $cond=array("NotifiedCollaborators"=>array('$elemMatch'=>array('CollaboratorId'=>(int) $user,'IsRead'=>(int)0,'SystemNotification'=>(int)1)));
           }
        $query=new Query();
        $query->from('NotificationCollection')->where($cond)
        ->andWhere(['!=','ActivityFrom', (int)$user])
        ->orderBy(["_id"=>SORT_DESC]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
            'pageSize' => $limit,
            'page'=>(int)$offset
            ]
        ]);
        $notifications = $provider->getModels();
//            $query->from('NotificationCollection')
//            ->where($cond)
//            ->andWhere(['!=','ActivityFrom', (int)$user])
//            ->orderBy(["_id"=>SORT_DESC])
//             ->offset($offset)      
//            ->limit($limit);
//            $notifications=$query->all();
            return $notifications;
          } catch (\Throwable $ex) {
            Yii::error("NotificationCollection:getNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
     }
    
 
    /**
     * @author Ryan Marshal
     * @param type $notifyid,$user
     * @return type 
     * @description  Used for deleting the notification 
     */
    public static function readNotification($notify)
    {
        $user=$notify->userInfo->Id;
        $notifyid=$notify->notifyid;
        try
        {
            $notifications=NotificationCollection::getCollection();
            $notifications->update(array('_id'=>$notifyid,'NotifiedCollaborators.CollaboratorId'=>(int)$user), array('$set'=>array('NotifiedCollaborators.$.IsRead'=>1)));
            return;
        }catch (\Throwable $ex) {
            Yii::error("NotificationCollection:readNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
        public static function deleteNotification($notify){
        $user=$notify->userInfo->Id;
        $notifyid=$notify->notifyid;
        try
        {
            error_log("id----".$user."---notif-".$notifyid);
            $notifications=NotificationCollection::getCollection();
            $notifications->update(array('_id'=>$notifyid), array('$pull'=>array("NotifiedCollaborators" => array('CollaboratorId'=>(int)$user))));
            return;
        }catch (\Throwable $ex) {
            Yii::error("NotificationCollection:deleteNotification::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
    
    /**
     * @author Ryan Marshal
     * @param type $notifyid,$user
     * @return type 
     * @description  Used for deleting all notifications of the user 
     */
    public static function readAllNotifications($notify)
    {
        $user=$notify->userInfo->Id;
        try
        {
            $notification=NotificationCollection::getCollection();
            foreach($notification as $notify)
            {
                $notification->update(array('NotifiedCollaborators.CollaboratorId'=>(int)$user), array('$set'=>array('NotifiedCollaborators.$.IsRead'=>1)));
            }
            return;
        }catch (\Throwable $ex) {
            Yii::error("NotificationCollection:readAllNotifications::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * @author Moin Hussain
     * @param type $notificationId
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
        public static function getNotificationDetail($notificationId,$ticketId,$projectId){
        try{
           $query=new Query();
            $query->from('NotificationCollection')
            ->where(["CommentSlug" => $notificationId,"TicketId" => (int)$ticketId,"ProjectId" => (int)$projectId])
                     ->orderBy(["_id"=>SORT_DESC]);
           //  ->where(["ProjectId" => (int)$projectId]);
            $notification = $query->one();
            return $notification;  
        } catch (\Throwable $ex) {
            Yii::error("NotificationCollection:getNotificationDetail::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    public static function getNotificationStatus($activityOn,$userId){
         try{
            echo("=============+FFFFFFFFFFFFFFFF=============".$userId);
            $matchArray = array('NotifiedCollaborators.CollaboratorId' => (int)$userId, "ActivityOn"=>$activityOn);
            $query = Yii::$app->mongodb->getCollection('NotificationCollection');
            $pipeline = array(
                array('$unwind' => '$NotifiedCollaborators'),
                array('$match' => $matchArray),
                array('$limit' => 1),
//                array(
//                    '$group' => array(
//                        '_id' => '$NotifiedCollaborators.CollaboratorId',
//                         "data" => array('$push' => '$NotifiedCollaborators'),
//                    )),
                );
            $notifications = $query->aggregate($pipeline);
            echo ("=========================+************************8".print_r($notifications,1)."===========");exit;
            return $notifications;  
        } catch (\Throwable $ex) {
            Yii::error("NotificationCollection:getNotificationDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
        }
