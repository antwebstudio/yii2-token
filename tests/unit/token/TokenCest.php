<?php
namespace token;

use Yii;
use UnitTester;
use ant\helpers\DateTime;
use ant\token\models\Token;

class TokenCest
{
    public function _before(UnitTester $I)
    {
		
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testSetDuration(UnitTester $I)
    {
		$duration = 30;
		
		$token = new Token;
		$token->setDuration($duration);
		$expected = (new DateTime)->addSeconds($duration);
		$I->assertEquals($expected, $token->expireAt);
		
		$token = new Token;
		$token->setDuration(null);
		$I->assertEquals(null, $token->expireAt);
    }
	
	public function testGenerate(UnitTester $I) {
		$now = (new \yii\db\Query)->select(new \yii\db\Expression('NOW() as now'))->one();
		$now = $now['now'];
		
        $token = Token::generate('test', 5 * 60); // 5 minutes
		
		$now = new DateTime($now);
		$expire = new DateTime($token->expire_at);
		
		$expected = $now;
		$expected->addSeconds(5 * 60);
		
		// If failed, check whether config timezone is same as database timezone
		$I->assertEquals($expected, $expire);
	}
	
	public function testGenerateAfterUserSetTimezone(UnitTester $I, $scenario) {
		//$scenario->skip();
		
		Yii::$app->setTimeZone('UTC'); // Set by user 
		//throw new \Exception(Yii::$app->formatter->asDateTime('2019-09-10 20:00:00'));
		
		Yii::$app->session->set('timezone', 'UTC'); // Set by user 
		
		$now = (new \yii\db\Query)->select(new \yii\db\Expression('NOW() as now'))->one();
		$now = $now['now'];
		
        $token = Token::generate('test', 5 * 60); // 5 minutes
		
		$now = new DateTime($now);
		$expire = new DateTime($token->expire_at);
		
		$expected = $now;
		$expected->addSeconds(5 * 60);
		
		$I->assertEquals($expected, $expire);
	}
	
	public function testIsExpired(UnitTester $I) {
        $token = Token::generate('test', 5); // 5 minutes
        $I->assertFalse($token->isExpired);
	}

    // tests
    public function testRenew(UnitTester $I, $scenario)
    {
		//$scenario->skip();
		
        $time = new DateTime;
        $token = Token::generate('test', 5 * 60); // 5 minutes

        $I->assertEquals($time->cloneIt()->addSeconds(5 * 60), $token->expire_at);
        
        sleep(5);
        
        $I->assertTrue($token->renew(5 * 60));
        $I->assertEquals($time->cloneIt()->addSeconds(5 * 60 + 5)->toString(), $token->expire_at);
    }

    // tests
    public function testRenewTokenWhichIsExpired(UnitTester $I)
    {
        $time = new DateTime;
        $token = Token::generate('test', -5 * 60); // 5 minutes
        $expireAt = $token->expire_at;

        $I->assertEquals($time->addSeconds(- 5 * 60)->toString(), $token->expire_at);
        $I->assertTrue($token->isExpired);
        
        $I->assertFalse($token->renew(5 * 60));
        $I->assertEquals($expireAt, $token->expire_at);
    }
	
	public function testTokenWhichNeverExpire(UnitTester $I) {
        $token = Token::generate('test', null);
        $I->assertEquals(null, $token->expire_at);
        $I->assertFalse($token->isExpired);
	}
}
