<?php
namespace common\models\bean;
/* @description This bean is used to prepare response to be send to client.
 * @author Moin Hussain
 */
class ResponseBean {
    
    const SUCCESS = 200;
    const FAILURE = 401;
    public $status = "";
    public $statusCode = "";
    public $message = "";
    public $data = "";
   
    
}
?>
