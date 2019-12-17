<?php
namespace ant\token\behaviors;

use yii\base\Behavior;

use ant\token\models\Token;
use ant\token\models\query\TokenQuery;

class TokenBehavior extends Behavior
{
    public $viaTable = [];
    public $beforeCreate = false;

    public function getTokens($excludeExpired = true)
    {
        $relation = $this->owner->hasMany(Token::className(), ['id' => 'token_id'])
            ->viaTable($this->viaTable[0], $this->viaTable[1]);

        if($excludeExpired) $relation->andOnCondition(['>', 'expire_at', time()]);

        return $relation;
    }

    public function getToken($type, $tokenData)
    {
        $token = is_array($tokenData) ? Token::createTokenString($tokenData) : $tokenData;

        return $this->getTokens()
            ->andWhere(['type' => $type])
            ->andWhere(['token' => $token])
            ->one();
    }

    public function createToken($type, $data = [], $duration = Token::TOKEN_DEFAULT_DURATION, $extraColumn = [])
    {
        $this->beforeCreate($type, $data, $duration, $extraColumn);

        $token = new Token([
            'type' => $type,
            'data' => $data,
            'expire_at' => time() + $duration
        ]);

        if($token->save(false))
        {
            $this->owner->link('tokens', $token, $extraColumn);

            return $token;
        }
        else
        {
            return false;
        }
    }

    private function beforeCreate($type, $data, $duration, $extraColumn)
    {
        if($this->beforeCreate) call_user_func_array($this->beforeCreate, [$type, $data, $duration, $extraColumn]);
    }
}
?>
