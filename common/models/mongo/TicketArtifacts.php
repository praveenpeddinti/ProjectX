<?php

namespace common\models\mongo;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
//use yii\db\ActiveRecord;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\data\ActiveDataProvider;
use yii\web\IdentityInterface;
use common\models\mongo\TicketCollection;

class TicketArtifacts extends ActiveRecord {

    public static function collectionName() {
        return 'TicketArtifacts';
    }

    public function attributes() {
//        parent::attributes();
        return [
            "_id",
            "TicketId",
            "ProjectId",
            "Artifacts",
        ];
    }

    public function behaviors() {
        return [
//            TimestampBehavior::className(),
        ];
    }

    public static function createArtifactsRecord($ticketNumber, $projectId, $artifactsList = array()) {
        try {
            
            
          $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
            foreach ($artifactsList as $artifactData){
                 $newdata = array('$push' => array("Artifacts" => $artifactData ));
                  $collection->update(array("TicketId" => (int)$ticketNumber,"ProjectId"=>(int)$projectId), $newdata);              
            }
        } catch (Exception $ex) {
            
        }
    }

    public static function getTicketArtifacts($ticketId, $projectId) {
        error_log("======getTicketArtifacts=========" . $ticketId . "-------------" . $projectId);
        $query = new Query();
        $query->from('TicketArtifacts')
                ->select(array("Artifacts"))
                ->where(['TicketId' => (int) $ticketId, "ProjectId" => (int) $projectId]);
        $ticketArtifactsDetails = $query->one();

        return $ticketArtifactsDetails;
    }

    public static function saveArtifacts($ticketNumber, $projectId, $newArtifactArray = array(), $userId) {
        try {
            if (!empty($newArtifactArray)) {
                foreach ($newArtifactArray as $artifact) {
                    $artifact["UploadedBy"] = (int) $userId;
                    $collection = Yii::$app->mongodb->getCollection('TicketArtifacts');
                    $newdata = array('$addToSet' => array('Artifacts' => $artifact));
                    $res = $collection->update(array("TicketId" => (int) $ticketNumber, "ProjectId" => (int) $projectId), $newdata, array('new' => 1, "upsert" => 1));
                }
            }
        } catch (Exception $ex) {
            
        }
    }

}

?>