<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\EntryForm;
use app\models\MosquittoForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['mosquitto', 'logout'],
                'rules' => [
                    [
                        'actions' => ['mosquitto', 'logout'],
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

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
    
    public function actionMosquitto()
    {
        $model = new MosquittoForm();
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->session->setFlash('mosquittoFormSubmitted');

            return $this->refresh();
        }
        $redis = Yii::$app->redis;
        $redis->set('k','v');
        $aa = $redis->get('k');
        return $this->render('mosquitto', [
            'model' => $model,
        ]);
    }
    
    public function actionMosquittoajax1()
   {
        $redis          = Yii::$app->redis;
        $mac            = $_REQUEST['mac'];             //MAC地址
        $msg            = $_REQUEST['msg'];             //消息内容
        $pub_topic      = "{$mac}/exec/shell";          //推送主题
        $sub_topic      = "{$mac}/exec/shell/result";   //订阅主题
        $id             = time() . rand(0001, 9999);        //随机ID
        $msg_array      = array(
            'mac'       => $mac,
            'script'    => $msg,
            'id'        => $id,
        );
        $msg_json = json_encode($msg_array);

        $client = new \Mosquitto\Client();
        $client->connect("localhost", 1883, 5);
        //$file = popen("php /var/www/wifibox/server.php {$mac}", 'r');
       // print_r($file);
       // pclose($file);
       // die();
        for($i=0;$i<3;$i++)
        {
            $client->publish($pub_topic, $msg_json, 1, 0);
            sleep(1);
        }
        $client->disconnect();
        unset($client);

        //返回客户端数据
        $back_result = json_decode($redis->get($id));
        $back_result = $back_result->result;
        return json_encode(array('code'=>0, 'msg'=>'successful!', 'data'=>$back_result));
    }

    public function actionMosquittoajax()
   {
        $redis          = Yii::$app->redis;
        $mac            = $_REQUEST['mac'];             //MAC地址
        $msg            = $_REQUEST['msg'];             //消息内容
        $pub_topic      = "{$mac}/exec/cmd";            //推送主题
        $sub_topic      = "{$mac}/exec/result";            //订阅主题
        $id             = time() . rand(0001, 9999);        //随机ID
        $msg_array      = array(
            'id'        => $id,
            'type'      => 'script',
            'data'      => $msg,
        );
        $msg_json = json_encode($msg_array);

        $client = new \Mosquitto\Client();
        $client->onMessage(function($message){
            $payload = json_decode($message->payload);
            $id     = $payload->id;
           // $result = $payload->result;
           // $mac    = $payload->mac;

            //存储客户端数据
            $redis  = Yii::$app->redis;
            $redis->set($id,$message->payload);
        });

        $client->connect("localhost", 1883, 5);
        $client->subscribe($sub_topic, 1);
        for($i=0;$i<3;$i++)
        {
            $client->loop();
            $client->publish($pub_topic, $msg_json, 1, 0);
            $client->loop();
            sleep(1);
        }
        $client->disconnect();
        unset($client);

        //返回客户端数据
        $back_result = json_decode($redis->get($id));
        $back_result = $back_result->result;
        return json_encode(array('code'=>0, 'msg'=>'successful!', 'data'=>$back_result));
    }



    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }

    public function actionEntry()
    {
        $model = new EntryForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // 验证 $model 收到的数据

            // 做些有意义的事 ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // 无论是初始化显示还是数据验证错误
            return $this->render('entry', ['model' => $model]);
        }
    }
}
