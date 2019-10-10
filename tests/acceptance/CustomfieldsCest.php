<?php

class CustomfieldsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_field_name');
    }

    public function tryToLoadCustomfieldsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the fields listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/fields');
        $I->wait(3);
        $I->waitForElement('table#fieldsTable', 3); // secs
        $I->seeElement('table#fieldsTable thead');
        $I->seeElement('table#fieldsTable tbody');
        $I->seeNumberOfElements('table#fieldsTable tr', [1, 30]);
        $I->seeInTitle('Customfields');
        $I->see('Customfields');

        $I->seeInPageSource('/fields');
        $I->seeElement('table#fieldsTable thead');
        $I->seeElement('table#fieldsTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(3);
    }

    public function tryToCreateCustomfieldButFailed(AcceptanceTester $I)
    {
        $test_field_name = 'MyTestCustomfield' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/fields/create');
        $I->seeElement('select[name="category_id"]');

        $I->wantToTest('fields create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(1);
        $I->seeElement('.help-block.form-error');

        // Can not create if all required field not filled: Blocked by backend validation
        $I->wantToTest('fields create form failed to create field when fields do not pass validation');
        $I->fillField('[name="name"]', $test_field_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(1);
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewCustomfield(AcceptanceTester $I, $cookie_name = 'test_field_name')
    {
        $test_field_name = 'MyTestCustomfield' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new field');
        $I->reloadPage();
        $I->wait(3);
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_field_name);
        $I->executeJS('$(\'select[name="category_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 2);
        $I->wait(1);

        $I->executeJS('
        	var category_select = $("select[name=\'category_id\']");
        	var first_category_data = category_select.data("select2").$results.children(":first").data("data");
        	var first_category_option = new Option(first_category_data.text, first_category_data.id, true, true);

        	category_select.append(first_category_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="category_id"]\').select2("close");');

        $I->fillField('[name="qty"]', '5');
        $I->click('#create-form [type="submit"]');

        $I->wait(3);
        $I->seeInTitle('Customfields');
        $I->see('Customfields');
        $I->seeCurrentUrlEquals('/fields');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_field_name);
        $I->saveSessionSnapshot('test_field_name');
    }

    public function tryToEditCustomfield(AcceptanceTester $I)
    {
        $test_field_name = $I->grabCookie('test_field_name');

        $I->wantToTest('edit previously created field');
        $I->fillField('.search .form-control', $test_field_name);
        $I->wait(1);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#fieldsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_field_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->wait(3);
        $I->seeInTitle('Update Customfield');
        $I->see('Update Customfield');

        $old_test_field_name = $test_field_name;
        $test_field_name = 'MyTestCustomfield' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_field_name);
        $I->fillField('[name="model_number"]', substr(md5(mt_rand()), 0, 14));
        $I->click('#create-form [type="submit"]');
        $I->wait(3);

        $I->wantTo('ensure previous field name does not exists after update');
        $I->fillField('.search .form-control', $old_test_field_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_field_name', $test_field_name);
        $I->saveSessionSnapshot('test_field_name');
        $I->wait(1);
    }

    public function tryToDeleteCustomfield(AcceptanceTester $I)
    {
        $test_field_name = $I->grabCookie('test_field_name');

        $I->wantToTest('delete previously created field');
        $I->fillField('.search .form-control', $test_field_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);
        $I->executeJS('
        	var bootstrap_table_instance = $("table#fieldsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_field_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->wait(3);
        $I->see('Are you sure you wish to delete ' . $test_field_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->wait(3);
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The field was deleted successfully');
    }

    public function tryToBulkEditCustomfields(AcceptanceTester $I)
    {
        	$I->amOnPage('/fields/create');
        $this->tryTocreateNewCustomfield($I, 'test_field_name');
        $I->wait(2);

        $I->amOnPage('/fields/create');
        $this->tryTocreateNewCustomfield($I, 'test_field_name2');
        $I->wait(2);

        $I->wantToTest('bulk edit fields');

        $test_field_name = $I->grabCookie('test_field_name');
        $test_field_name2 = $I->grabCookie('test_field_name2');

        $I->fillField('.search .form-control', 'MyTestCustomfield');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
        $I->click('#bulkEdit');

        $I->wait(3);

        $I->see('Customfield Update');
        $I->see('2 fields');

        $I->fillField('[name="qty"]', 2);
        $I->click('form .box-footer [type="submit"]');

        $I->wait(3);
        $I->seeInTitle('Customfields');
        $I->see('Customfields');
        $I->seeCurrentUrlEquals('/fields');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->wait(3);
    }

    public function tryToBulkDeleteCustomfields(AcceptanceTester $I)
    {
        $I->wantToTest('bulk delete fields');

        $test_field_name = $I->grabCookie('test_field_name');
        $test_field_name2 = $I->grabCookie('test_field_name2');

        $I->fillField('.search .form-control', 'MyTestCustomfield');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
        $I->click('#bulkEdit');

        $I->wait(3);

        $I->see('Confirm Bulk Delete Customfields');
        $I->see('2 fields');

        $I->click('form #submit-button');

        $I->seeCurrentUrlEquals('/fields');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
    }
}
