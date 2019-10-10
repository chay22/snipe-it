<?php

class ManufacturersCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_manufacturer_name');
    }

    public function tryToLoadManufacturersListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the manufacturers listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/manufacturers');
        $I->wait(3);
        $I->waitForElement('table#manufacturersTable', 3); // secs
        $I->seeElement('table#manufacturersTable thead');
        $I->seeElement('table#manufacturersTable tbody');
        $I->seeNumberOfElements('table#manufacturersTable tr', [1, 30]);
        $I->seeInTitle('Manufacturers');
        $I->see('Manufacturers');

        $I->seeInPageSource('/manufacturers');
        $I->seeElement('table#manufacturersTable thead');
        $I->seeElement('table#manufacturersTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(3);
    }

    public function tryToCreateManufacturerButFailed(AcceptanceTester $I)
    {
        $test_manufacturer_name = 'MyTestManufacturer' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/manufacturers/create');
        $I->seeElement('[name="url"]');
        $I->seeInTitle('Create Manufacturer');
        $I->see('Create Manufacturer');

        $I->wantToTest('manufacturers create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(1);
        $I->seeElement('.help-block.form-error');
    }

    public function tryTocreateNewManufacturer(AcceptanceTester $I, $cookie_name = 'test_manufacturer_name')
    {
        $test_manufacturer_name = 'MyTestManufacturer' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new manufacturer');
        $I->reloadPage();
        $I->wait(3);
        $I->seeInTitle('Create Manufacturer');
        $I->see('Create Manufacturer');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_manufacturer_name);
        $I->fillField('[name="url"]', 'https://testurl.test');
        $I->fillField('[name="support_email"]', 'testemail@testemail.test');

        $I->click('#create-form [type="submit"]');

        $I->wait(3);
        $I->seeInTitle('Manufacturers');
        $I->see('Manufacturers');
        $I->seeCurrentUrlEquals('/manufacturers');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_manufacturer_name);
        $I->saveSessionSnapshot('test_manufacturer_name');
    }

    public function tryToEditManufacturer(AcceptanceTester $I)
    {
        $test_manufacturer_name = $I->grabCookie('test_manufacturer_name');

        $I->wantToTest('edit previously created manufacturer');
        $I->fillField('.search .form-control', $test_manufacturer_name);
        $I->wait(1);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#manufacturersTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_manufacturer_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->wait(3);
        $I->seeInTitle('Update Manufacturer');
        $I->see('Update Manufacturer');

        $old_test_manufacturer_name = $test_manufacturer_name;
        $test_manufacturer_name = 'MyTestManufacturer' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_manufacturer_name);
        $I->click('#create-form [type="submit"]');
        $I->wait(3);

        $I->wantTo('ensure previous manufacturer name does not exists after update');
        $I->fillField('.search .form-control', $old_test_manufacturer_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_manufacturer_name', $test_manufacturer_name);
        $I->saveSessionSnapshot('test_manufacturer_name');
        $I->wait(1);
    }

    public function tryToDeleteManufacturer(AcceptanceTester $I)
    {
        $test_manufacturer_name = $I->grabCookie('test_manufacturer_name');

        $I->wantToTest('delete previously created manufacturer');
        $I->fillField('.search .form-control', $test_manufacturer_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);
        $I->executeJS('
        	var bootstrap_table_instance = $("table#manufacturersTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_manufacturer_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->wait(3);
        $I->see('Are you sure you wish to delete ' . $test_manufacturer_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->wait(3);
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The manufacturer was deleted successfully');
    }
}
