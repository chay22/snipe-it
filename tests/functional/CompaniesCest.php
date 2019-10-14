<?php

class CompaniesCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_company_name');
    }

    public function tryToLoadCompaniesListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the companies listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/companies');
        $I->waitForElement('table#companiesTable tbody');
        $I->seeElement('table#companiesTable thead');
        $I->seeElement('table#companiesTable tbody');
        $I->seeNumberOfElements('table#companiesTable tr', [1, 30]);
        $I->seeInTitle('Companies');
        $I->see('Companies');

        $I->seeInPageSource('/companies');
        $I->seeElement('table#companiesTable thead');
        $I->seeElement('table#companiesTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(1);
    }

    public function tryToCreateCompanyButFailed(AcceptanceTester $I)
    {
        $test_company_name = 'MyTestCompany' . substr(md5(mt_rand()), 0, 10);

        $I->waitForElementVisible('[name="name"]');
        $I->seeCurrentUrlEquals('/companies/create');
        $I->seeElement('[name="name"]');
        $I->seeInTitle('Create Company');
        $I->see('Create Company');

        $I->wantToTest('companies create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.help-block.form-error');
        $I->seeElement('.help-block.form-error');
    }

    public function tryTocreateNewCompany(AcceptanceTester $I, $cookie_name = 'test_company_name')
    {
        $test_company_name = 'MyTestCompany' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new company');
        $I->reloadPage();
        $I->waitForElementVisible('[name="name"]');
        $I->seeInTitle('Create Company');
        $I->see('Create Company');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_company_name);
        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#companiesTable tbody');
        $I->seeInTitle('Companies');
        $I->see('Companies');
        $I->seeCurrentUrlEquals('/companies');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_company_name);
        $I->saveSessionSnapshot('test_company_name');
    }

    public function tryToEditCompany(AcceptanceTester $I)
    {
        $test_company_name = $I->grabCookie('test_company_name');

        $I->wantToTest('edit previously created company');
        $I->fillField('.search .form-control', $test_company_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#companiesTable").data("bootstrap.table").data[0].name === "'.$test_company_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#companiesTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_company_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update Company');
        $I->seeInTitle('Update Company');
        $I->see('Update Company');

        $old_test_company_name = $test_company_name;
        $test_company_name = 'MyTestCompany' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_company_name);
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#companiesTable tbody');

        $I->wantTo('ensure previous company name does not exists after update');
        $I->fillField('.search .form-control', $old_test_company_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_company_name', $test_company_name);
        $I->saveSessionSnapshot('test_company_name');
        $I->wait(1);
    }

    public function tryToDeleteCompany(AcceptanceTester $I)
    {
        $test_company_name = $I->grabCookie('test_company_name');

        $I->wantToTest('delete previously created company');
        $I->waitForElement('table#companiesTable tbody');
        $I->fillField('.search .form-control', $test_company_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#companiesTable").data("bootstrap.table").data[0].name === "'.$test_company_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#companiesTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_company_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset:not(.disabled)\').click();
        		}
        	});
        ');

        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_company_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElement('table#companiesTable tbody');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The company was deleted successfully');
    }
}
