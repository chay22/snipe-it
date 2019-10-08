<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;
   /**
    * Define custom actions here
    */

    public static function test_login($I)
    {
         // if snapshot exists - skipping login
         //if ($I->loadSessionSnapshot('login')) return;

         // logging in
         $I->amOnPage('/login');
         $I->fillField('username', 'snipe');
         $I->fillField('password', 'password');
         $I->click('Login');
         //$I->saveSessionSnapshot('login');
    }

    public static function use_single_login($I)
    {
        if ($I->loadSessionSnapshot('login')) return;

        static::test_login($I);

        $I->saveSessionSnapshot('login');
    }
}
