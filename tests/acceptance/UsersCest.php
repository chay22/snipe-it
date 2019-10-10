<?php

class UsersCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_user_name');
    }

    public function tryToLoadUsersListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the users listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/users');
        $I->waitForElement('table#usersTable tbody');
        $I->seeElement('table#usersTable thead');
        $I->seeElement('table#usersTable tbody');
        $I->seeNumberOfElements('table#usersTable tr', [1, 30]);
        $I->seeInTitle('Users');
        $I->see('Users');

        $I->seeInPageSource('/users');
        $I->seeElement('table#usersTable thead');
        $I->seeElement('table#usersTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(1);
    }

    public function tryToCreateUserButFailed(AcceptanceTester $I)
    {
        $test_user_name = 'MyTestUser' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/users/create');
        $I->seeElement('[name="first_name"]');
        $I->seeElement('[name="username"]');
        $I->seeElement('select[name="country"]');
        $I->seeElement('[name="password"]');
        $I->seeElement('[name="password_confirmation"]');
        $I->seeInTitle('Create User');
        $I->see('Create User');

        // Can not create: Blocked by backend validation
        $I->wantToTest('users create form failed to create category when fields do not pass validation');
        $I->clickWithLeftButton('#userForm [type="submit"]');
        $I->waitForElement('.alert-msg');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');

        $I->wantToTest('users create form failed to create category when fields do not pass validation');
        $I->click('[name="username"]');
        $I->wait(0.1);
        $I->fillField('[name="username"]', $test_user_name);

        $I->clickWithLeftButton('#userForm [type="submit"]');
        $I->waitForElement('.alert-msg');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewUser(AcceptanceTester $I, $cookie_name = 'test_user_name')
    {
        $test_user_name = 'MyTestUser' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new user');
        $I->reloadPage();
        $I->waitForText('Create User');
        $I->seeInTitle('Create User');
        $I->see('Create User');
        $I->dontSeeElement('.help-block.form-error');

        $I->click('[name="first_name"]');
        $I->wait(0.1);
        $I->fillField('[name="first_name"]', 'testfirstname');

        $I->click('[name="username"]');
        $I->wait(0.1);
        $I->fillField('[name="username"]', $test_user_name);

        $I->click('[name="password"]');
        $I->wait(0.1);
        $I->fillField('[name="password"]', 'passwordpassword');

        $I->click('[name="password_confirmation"]');
        $I->wait(0.1);
        $I->fillField('[name="password_confirmation"]', 'passwordpassword');

        $I->click('[name="email"]');
        $I->wait(0.1);
        $I->fillField('[name="email"]', 'email@testemail.test');


        $I->click('[name="address"]');
        $I->wait(0.1);
        $I->fillField('[name="address"]', 'Address example');

        $I->click('[name="city"]');
        $I->wait(0.1);
        $I->fillField('[name="city"]', 'City example');

        $I->click('[name="state"]');
        $I->wait(0.1);
        $I->fillField('[name="state"]', 'State example');

        $I->executeJS('$(\'select[name="country"]\').select2("open");');
        $I->wait(0.5);

        
        $I->fillField('.select2-search__field', 'indonesia');
        $I->selectOption('select[name="country"]', 'ID');
        $I->executeJS('$(\'select[name="country"]\').trigger("change");');

        $I->click('#userForm [type="submit"]');

        $I->waitForText('Users');
        $I->seeInTitle('Users');
        $I->see('Users');
        $I->seeCurrentUrlEquals('/users');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_user_name);
        $I->saveSessionSnapshot('test_user_name');
    }

    public function tryToEditUser(AcceptanceTester $I)
    {
        $test_user_name = $I->grabCookie('test_user_name');

        $I->wantToTest('edit previously created user');
        $I->click('.search .form-control');
        $I->wait(0.1);
        $I->fillField('.search .form-control', $test_user_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForElementVisible('tr[data-index="0"]');

        $I->waitForJS('try { return $("table#usersTable").data("bootstrap.table").data[0].username === "'.$test_user_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#usersTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.username === "'.$test_user_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update User');
        $I->seeInTitle('Update User');
        $I->see('Update User');

        $old_test_user_name = $test_user_name;
        $test_user_name = 'MyTestUser' . substr(md5(mt_rand()), 0, 10);

        $I->click('[name="username"]');
        $I->wait(0.1);
        $I->fillField('[name="username"]', $test_user_name);
        $I->click('#userForm [type="submit"]');
        $I->waitForText('Current User');

        $I->wantTo('ensure previous user name does not exists after update');
        $I->fillField('.search .form-control', $old_test_user_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_user_name', $test_user_name);
        $I->saveSessionSnapshot('test_user_name');
        $I->wait(1);
    }

    public function tryToDeleteUser(AcceptanceTester $I)
    {
        $test_user_name = $I->grabCookie('test_user_name');

        $I->wantToTest('delete previously created user');
        $I->fillField('.search .form-control', $test_user_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 2);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForElementVisible('tr[data-index="0"]');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#usersTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.username === "'.$test_user_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('successfully deleted');
    }    
}
