<?php

class StatuslabelsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_statuslabel_name');
    }

    public function tryToLoadStatuslabelsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the statuslabels listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/statuslabels');
        $I->waitForElement('table#statuslabelsTable tbody');
        $I->seeElement('table#statuslabelsTable thead');
        $I->seeElement('table#statuslabelsTable tbody');
        $I->seeNumberOfElements('table#statuslabelsTable tr', [1, 30]);
        $I->seeInTitle('Status Label');
        $I->see('Status Label');

        $I->seeInPageSource('/statuslabels');
        $I->seeElement('table#statuslabelsTable thead');
        $I->seeElement('table#statuslabelsTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
    }

    public function tryToCreateStatuslabelButFailed(AcceptanceTester $I)
    {
        $test_statuslabel_name = 'MyTestStatuslabel' . substr(md5(mt_rand()), 0, 10);

        $I->waitForText('Create Status Label');
        $I->seeCurrentUrlEquals('/statuslabels/create');
        $I->seeElement('select[name="statuslabel_types"]');
        $I->seeInTitle('Create Status Label');
        $I->see('Create Status Label');

        $I->wantToTest('statuslabels create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(0.1);
        $I->seeElement('.help-block.form-error');
    }

    public function tryTocreateNewStatuslabel(AcceptanceTester $I, $cookie_name = 'test_statuslabel_name')
    {
        $test_statuslabel_name = 'MyTestStatuslabel' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new statuslabel');
        $I->reloadPage();
        $I->waitForText('Create Status Label');
        $I->seeInTitle('Create Status Label');
        $I->see('Create Status Label');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_statuslabel_name);
        $I->selectOption('select[name="statuslabel_types"]', 'deployable');
        $I->fillField('[name="notes"]', 'Test notes');

        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#statuslabelsTable tbody');
        $I->seeInTitle('Status Label');
        $I->see('Status Label');
        $I->seeCurrentUrlEquals('/statuslabels');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_statuslabel_name);
        $I->saveSessionSnapshot('test_statuslabel_name');
    }

    public function tryToEditStatuslabel(AcceptanceTester $I)
    {
        $test_statuslabel_name = $I->grabCookie('test_statuslabel_name');

        $I->wantToTest('edit previously created statuslabel');
        $I->waitForElement('table#statuslabelsTable tbody');
        $I->fillField('.search .form-control', $test_statuslabel_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForJS('try { return $("table#statuslabelsTable").data("bootstrap.table").data[0].name === "'.$test_statuslabel_name.'"; } catch(e) { return false; }');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#statuslabelsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_statuslabel_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update Status Label');
        $I->seeInTitle('Update Status Label');
        $I->see('Update Status Label');

        $old_test_statuslabel_name = $test_statuslabel_name;
        $test_statuslabel_name = 'MyTestStatuslabel' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_statuslabel_name);
        $I->click('#create-form [type="submit"]');
        $I->wait(3);

        $I->wantTo('ensure previous statuslabel name does not exists after update');
        $I->fillField('.search .form-control', $old_test_statuslabel_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->see('No matching records found');

        $I->setCookie('test_statuslabel_name', $test_statuslabel_name);
        $I->saveSessionSnapshot('test_statuslabel_name');
        $I->wait(1);
    }

    public function tryToDeleteStatuslabel(AcceptanceTester $I)
    {
        $test_statuslabel_name = $I->grabCookie('test_statuslabel_name');

        $I->wantToTest('delete previously created statuslabel');
        $I->fillField('.search .form-control', $test_statuslabel_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForJS('try { return $("table#statuslabelsTable").data("bootstrap.table").data[0].name === "'.$test_statuslabel_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#statuslabelsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_statuslabel_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_statuslabel_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->seeElement('.alert.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('deleted successfully');
    }
}
