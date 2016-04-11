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
    
    public function actionMosquittoajax()
    {
        $mac            = $_REQUEST['mac'];             //MAC地址
        $msg            = $_REQUEST['msg'];             //消息内容
        $id             = time().rand(1000,9999);       //随机ID
        
        //调用发送进程
        $file = popen("/usr/local/bin/php /var/www/wifibox/server_pub.php '{$mac}' '{$msg}' '{$id}'", 'w');
        pclose($file);
        
        $redis          = Yii::$app->redis;

        //等待数据存储
        sleep(3);

        //返回客户端数据
        $result = $redis->get($id);
        if($result)
        {
            return json_encode(array('code'=>0, 'msg'=>'successful!', 'data'=>$result));
        }
        else{
            return json_encode(array('code'=>1, 'msg'=>'wrong!', 'data'=>'Data empty!'));
        }
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
