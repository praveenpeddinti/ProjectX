<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use common\models\mongo\SampleCollection;
use common\models\mongo\ProjectTicketSequence;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\components\CommonUtility;
use common\models\bean\ResponseBean;
use common\models\User;
use \common\models\mongo\TinyUserCollection;
/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function beforeAction($action) {
    $this->enableCsrfValidation = false;
    return parent::beforeAction($action);
    }
   
    public function actionTestAjax()
    {
        error_log("----------------");
       // $data = SampleCollection::testMongo();
       $model = new ProjectTicketSequence();
       $model->getNextSequence(2);
      // $db->getNextSequence(1);
      // $db->insert(array("TicketNumber" => $this->getNextSequence(2),"name" => "Sarah C."));
       // error_log("+++++++++++++actionTestAjax+++++++++++++++++++".print_r($data,1));
    
    }
    /**
     * @author Moin Hussain
     * @description This is sample method to demonstrate the response
     * @return type
     * Try this in browser http://10.10.73.33/site/sample-response
     */
    public function actionSampleResponse(){
        try{
        $data = ["firstName" => "Moin", "lastName" => "Hussain"];
        $responseBean = new ResponseBean;
        $responseBean->statusCode = ResponseBean::SUCCESS;
        $responseBean->message = ResponseBean::SUCCESS_MESSAGE;
        $responseBean->data = $data;
        $response = CommonUtility::prepareResponse($responseBean,"xml");
        return $response;   
        } catch (Exception $ex) {
         Yii::log("SiteController:actionSampleResponse::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
       
    
    }
    
    public function actionLogin()
    {
        foreach ($_SERVER as $name => $value) {
   // error_log($name."----".$value,"---");
}
error_log("@@@---**".print_r($_SERVER,1));
        $user_data = json_decode(file_get_contents("php://input"));
      //  error_log("request aprams------------)))".print_r($_SERVER,1)."---".print_r($user_data,1));
        $model = new LoginForm();
        $userData = $model->loginAjax($user_data->username);
        $responseBean = new ResponseBean;
        $responseBean->status = ResponseBean::SUCCESS;
        $responseBean->message = "success";
        $responseBean->data = $userData;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;
 
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    /**
     * Get Collaborators from sql table and insert into mongo document.
     *
     * @author Anand Singh
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionCollaborators(){
          try{
        $collaborators = User::getCollabrators();
        $response=  TinyUserCollection::createUsers($collaborators);
        $responseBean = new ResponseBean;
        $responseBean->status = ResponseBean::SUCCESS;
        $responseBean->message = "success";
        $responseBean->data = $collaborators;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;   
        } catch (Exception $ex) {
         Yii::log("SiteController:actionCollaborators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public function actionInsertCollaborators(){
          try{
        $collaborators = User::insertCollabrators(10000);
        $responseBean = new ResponseBean;
        $responseBean->status = ResponseBean::SUCCESS;
        $responseBean->message = "success";
        $responseBean->data = $collaborators;
        $response = CommonUtility::prepareResponse($responseBean,"json");
        return $response;   
        } catch (Exception $ex) {
         Yii::log("SiteController:actionCollaborators::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    
}
?>