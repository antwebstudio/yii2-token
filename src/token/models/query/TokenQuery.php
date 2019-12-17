<?php
namespace ant\token\models\query;

use yii\db\ActiveQuery;

use ant\user\models\User;
use ant\token\models\Token;
use ant\cart\models\Cart;
use ant\user\models\UserInvite;

class TokenQuery extends ActiveQuery
{
    public function byUser($user)
    {
        $user = $user instanceof User ? $user : User::findOne($user);
        $this->joinWith('user u')
            ->andWhere(['user_id' => $user->id]);

        return $this;
    }

    public function byCart($cart)
    {
        $cart = $cart instanceof Cart ? $cart : Cart::findOne($cart);
        $this->joinWith('cart c')
            ->andWhere(['c.id' => $cart->id]);

        return $this;
    }

    public function byUserInvite($userInvite)
    {
        $userInvite = $userInvite instanceof UserInvite ? $userInvite : UserInvite::findOne($userInvite);
        $this->joinWith('userInvite ui')
            ->andWhere(['ui.id' => $userInvite->id]);

        return $this;
    }

    public function byType($type)
    {
        $this->alias('token')->andWhere(['token.type' => $type]);

        return $this;
    }

    public function byQueryParams(array $queryParams)
    {
        $this->andWhere(['token' => Token::createTokenString($queryParams)]);

        return $this;
    }

    public function isNotExpired()
    {
        $this->andWhere(['>', 'expire_at', time()]);

        return $this;
    }
}
?>
