<?php

class DepreciationsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_depreciation_name');
    }

    public function tryToLoadDepreciationsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the depreciations listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/depreciations');
        $I->waitForElement('table#depreciationsTable tbody');
        $I->seeElement('table#depreciationsTable thead');
        $I->seeElement('table#depreciationsTable tbody');
        $I->seeNumberOfElements('table#depreciationsTable tr', [1, 30]);
        $I->seeInTitle('Depreciations');
        $I->see('Depreciations');

        $I->seeInPageSource('/depreciations');
        $I->seeElement('table#depreciationsTable thead');
        $I->seeElement('table#depreciationsTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
    }

    public function tryToCreateDepreciationButFailed(AcceptanceTester $I)
    {
        $test_depreciation_name = 'MyTestDepreciation' . substr(md5(mt_rand()), 0, 10);

        $I->waitForText('Create Depreciation');

        $I->seeCurrentUrlEquals('/depreciations/create');
        $I->seeElement('[name="name"]');
        $I->seeElement('[name="months"]');
        $I->seeInTitle('Create Depreciation');
        $I->see('Create Depreciation');

        $I->wantToTest('depreciations create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(0.1);
        $I->seeElement('.help-block.form-error');

        $I->fillField('[name="name"]', $test_depreciation_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.alert.fade.in');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewDepreciation(AcceptanceTester $I, $cookie_name = 'test_depreciation_name')
    {
        $test_depreciation_name = 'MyTestDepreciation' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new depreciation');
        $I->reloadPage();
        $I->waitForText('Create Depreciation');
        $I->seeInTitle('Create Depreciation');
        $I->see('Create Depreciation');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_depreciation_name);
        $I->fillField('[name="months"]', 12);

        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#depreciationsTable tbody');
        $I->seeInTitle('Depreciations');
        $I->see('Depreciations');
        $I->seeCurrentUrlEquals('/depreciations');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_depreciation_name);
        $I->saveSessionSnapshot('test_depreciation_name');
    }

    public function tryToEditDepreciation(AcceptanceTester $I)
    {
        $test_depreciation_name = $I->grabCookie('test_depreciation_name');

        $I->wantToTest('edit previously created depreciation');
        $I->fillField('.search .form-control', $test_depreciation_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#depreciationsTable").data("bootstrap.table").data[0].name === "'.$test_depreciation_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#depreciationsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_depreciation_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update Depreciation');
        $I->seeInTitle('Update Depreciation');
        $I->see('Update Depreciation');

        $old_test_depreciation_name = $test_depreciation_name;
        $test_depreciation_name = 'MyTestDepreciation' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_depreciation_name);
        $I->fillField('[name="months"]', 5);
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#depreciationsTable tbody');

        $I->wantTo('ensure previous depreciation name does not exists after update');
        $I->fillField('.search .form-control', $old_test_depreciation_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_depreciation_name', $test_depreciation_name);
        $I->saveSessionSnapshot('test_depreciation_name');
        $I->wait(1);
    }

    public function tryToDeleteDepreciation(AcceptanceTester $I)
    {
        $test_depreciation_name = $I->grabCookie('test_depreciation_name');

        $I->wantToTest('delete previously created depreciation');
        $I->fillField('.search .form-control', $test_depreciation_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#depreciationsTable").data("bootstrap.table").data[0].name === "'.$test_depreciation_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#depreciationsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_depreciation_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_depreciation_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('deleted successfully');
    }
}
