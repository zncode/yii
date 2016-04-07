<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class MosquittoForm extends Model
{
    public $mac;
    public $msg;
    public $response;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['mac'], 'required'],
            [['msg'], 'required'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'mac' => 'Mac 地址',
            'msg' => '消息内容',
            'response' => '回复内容',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param  string  $email the target email address
     * @return boolean whether the model passes validation
     */
    public function publish($topic)
    {
        return false;
    }
}
