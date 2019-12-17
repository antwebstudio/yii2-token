<?php
namespace ant\token\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

use ant\helpers\DateTime;
use ant\user\models\User;
use ant\token\models\query\TokenQuery;
use ant\interfaces\Expirable;
use ant\behaviors\TimestampBehavior;
use ant\cart\models\Cart;
use ant\user\models\UserInvite;

class Token extends ActiveRecord implements Expirable
{
    const TOKEN_TYPE_USER_ACTIVATION   = 'user_activation';
    const TOKEN_TYPE_USER_PASSWORD_RESET = 'user_password_reset';
    const TOKEN_TYPE_USER_CHANGE_EMAIL = 'user_change_email';
    const TOKEN_TYPE_USER_INVITE = 'user_invite';

    const TOKEN_TYPE_CART_EVENT_REGISTER = 'cart_event_register';

    const TOKEN_ALGO = 'sha1';
    const TOKEN_SALT = '4FSGGbVKd9KbKj3A3Jq7';
    const TOKEN_KEY_LENGTH = 40;
	
	const ACTIVATION_CODE_LENGTH = 8;

    CONST TOKEN_DEFAULT_DURATION = 1 * 24 * 60 * 60;

    private $_queryParams = [];

    public static function tableName()
    {
        return '{{%token}}';
    }

    public function behaviors()
    {
        return
        [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    public function rules()
    {
        return
        [
			//[['expire_at'], 'date'],
            //[['created_at', 'updated_at'], 'integer'],
            [['token'], 'string', 'max' => 40],
            [['type'], 'string', 'max' => 255],
            [['queryParams', 'duration'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return
        [
            'token' => 'Token',
            'type' => 'Type',
            'expire_at' => 'Expire At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function find()
    {
        return new TokenQuery(get_called_class());
    }

    public static function generate($type, $duration) {
        $token = new self;

        $token->type = $type;
		if (isset($duration)) {
			$token->duration = $duration;
		} else {
			$token->expire_at = null;
		}
        $token->token = self::createTokenKey();

        if (!$token->save()) throw new \Exception('Failed to generate token. '.print_r($token->errors, 1));

        return $token;
    }

    public static function create($model, $type, $queryParams, $duration = self::TOKEN_DEFAULT_DURATION, $extraColumn = [])
    {
        $token = new self;

        $token->type = $type;
        $token->queryParams = $queryParams;
        $token->duration = $duration;

        if($token->save()) $model->link('tokens', $token, $extraColumn);
        else throw new \Exception('Failed to create token. '.print_r($token->errors, 1));

        return $token;
    }

    public function renew($duration = self::TOKEN_DEFAULT_DURATION)
    {
        // Renew only when it is not expired. To renew a expired token, please use method renewExpired()
        if (!$this->isExpired) {
            $this->duration = $duration;

            return $this->save(false);
        }
        return false;
    }

    public function markAsExpired() {
        return $this->expire();
    }

    public function expire()
    {
        $this->renew(-1);
    }

    public function setQueryParams(array $queryParams)
    {
        $this->_queryParams = $queryParams;
        $this->token = self::createTokenString($this->queryParams);
    }

    public function getQueryParams()
    {
        return $this->_queryParams;
    }

    public function setDuration($duration)
    {
		$expire = new DateTime(time() + $duration);
        $this->expire_at = $expire->format(DateTime::FORMAT_MYSQL);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])
            ->viaTable('{{%user_token_map}}', ['token_id' => 'id']);
    }

    public function getCart()
    {
		return $this->hasOne(Cart::className(), ['token_id' => 'id']);
		
        return $this->hasOne(Cart::className(), ['id' => 'cart_id'])
            ->viaTable('{{%cart_token_map}}', ['token_id' => 'id']);
    }

    public function getUserInvite()
    {
        return $this->hasOne(UserInvite::className(), ['token_id' => 'id']);
    }

    public static function createTokenString($data, $algo = self::TOKEN_ALGO, $salt = self::TOKEN_SALT)
    {
        ksort($data);

        $hash = hash_init($algo, HASH_HMAC, $salt);
        hash_update($hash, serialize($data));
        return hash_final($hash);
    }

    public static function createTokenKey(){
        return Yii::$app->security->generateRandomString(self::TOKEN_KEY_LENGTH);
    }

    public function getIsExpired()
    {
		if (!isset($this->expire_at)) return false;
        return !($this->expire_at > new DateTime);
    }
}
?>
