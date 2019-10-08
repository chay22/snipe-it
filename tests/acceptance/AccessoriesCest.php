<?php

class AccessoriesCest
{
    public function _before(AcceptanceTester $I)
    {
    	AcceptanceTester::use_single_login($I);

    	$I->loadSessionSnapshot('test_accessory_name');
    }

    public function tryToLoadAccessoriesListingPage(AcceptanceTester $I)
    {
		$I->am('logged in user');
		$I->wantTo('ensure that the accessories listing page loads without errors');
		$I->lookForwardTo('seeing it load without errors');

		$I->amOnPage('/accessories');
		$I->wait(3);
		$I->waitForElement('table#accessoriesTable', 3); // secs
		$I->seeElement('table#accessoriesTable thead');
		$I->seeElement('table#accessoriesTable tbody');
		$I->seeNumberOfElements('table#accessoriesTable tr', [1, 30]);
		$I->seeInTitle('Accessories');
		$I->see('Accessories');

		$I->seeInPageSource('/accessories');
		$I->seeElement('table#accessoriesTable thead');
		$I->seeElement('table#accessoriesTable tbody');

		$I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
		$I->wait(3);
    }

    public function tryToCreateAccessoryButFailed(AcceptanceTester $I)
    {
    	$test_accessory_name = 'MyTestAccessory' . substr(md5(mt_rand()), 0, 10);

		$I->seeCurrentUrlEquals('/accessories/create');
		$I->seeElement('select[name="category_id"]');

		$I->wantToTest('accessories create form prevented from submit if nothing is filled');
		$I->dontSeeElement('.help-block.form-error');
		$I->clickWithLeftButton('#create-form [type="submit"]');
		$I->wait(1);
		$I->seeElement('.help-block.form-error');

		// Can not create if all required field not filled: Blocked by backend validation
		$I->wantToTest('accessories create form failed to create accessory when fields do not pass validation');
		$I->fillField('[name="name"]', $test_accessory_name);
		$I->clickWithLeftButton('#create-form [type="submit"]');
		$I->wait(1);
		$I->seeNumberOfElements('.alert-msg', [1, 3]);
		$I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewAccessory(AcceptanceTester $I, $cookie_name = 'test_accessory_name')
    {
    	$test_accessory_name = 'MyTestAccessory' . substr(md5(mt_rand()), 0, 10);

		$I->wantToTest('create new accessory');
		$I->reloadPage();
		$I->wait(3);
		$I->dontSeeElement('.help-block.form-error');
		$I->fillField('[name="name"]', $test_accessory_name);
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
		$I->seeInTitle('Accessories');
		$I->see('Accessories');
		$I->seeCurrentUrlEquals('/accessories');
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');

		$I->setCookie($cookie_name, $test_accessory_name);
		$I->saveSessionSnapshot('test_accessory_name');
    }

    public function tryToEditAccessory(AcceptanceTester $I)
    {
    	$test_accessory_name = $I->grabCookie('test_accessory_name');

		$I->wantToTest('edit previously created accessory');
		$I->fillField('.search .form-control', $test_accessory_name);
		$I->wait(1);
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->executeJS('
			var bootstrap_table_instance = $("table#accessoriesTable").data("bootstrap.table");

			$.each(bootstrap_table_instance.data, function (k, v) {
				if (v.name === "'.$test_accessory_name.'") {
					window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
				}
			});
		');
		$I->wait(3);
		$I->seeInTitle('Update Accessory');
		$I->see('Update Accessory');

		$old_test_accessory_name = $test_accessory_name;
		$test_accessory_name = 'MyTestAccessory' . substr(md5(mt_rand()), 0, 10);

		$I->fillField('[name="name"]', $test_accessory_name);
		$I->fillField('[name="model_number"]', substr(md5(mt_rand()), 0, 14));
		$I->click('#create-form [type="submit"]');
		$I->wait(3);

		$I->wantTo('ensure previous accessory name does not exists after update');
		$I->fillField('.search .form-control', $old_test_accessory_name);
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->see('No matching records found');

		$I->setCookie('test_accessory_name', $test_accessory_name);
		$I->saveSessionSnapshot('test_accessory_name');
		$I->wait(1);
    }

    public function tryToDeleteAccessory(AcceptanceTester $I)
    {
    	$test_accessory_name = $I->grabCookie('test_accessory_name');

		$I->wantToTest('delete previously created accessory');
		$I->fillField('.search .form-control', $test_accessory_name);
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->wait(1);
		$I->executeJS('
			var bootstrap_table_instance = $("table#accessoriesTable").data("bootstrap.table");

			$.each(bootstrap_table_instance.data, function (k, v) {
				if (v.name === "'.$test_accessory_name.'") {
					$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
				}
			});
		');
		$I->wait(3);
		$I->see('Are you sure you wish to delete ' . $test_accessory_name . '?', '#dataConfirmModal');
		$I->click('#dataConfirmOK');
		$I->wait(3);
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');
		$I->see('The accessory was deleted successfully');
    }

    public function tryToBulkEditAccessories(AcceptanceTester $I)
    {
      	$I->amOnPage('/accessories/create');
    	$this->tryTocreateNewAccessory($I, 'test_accessory_name');
    	$I->wait(2);

    	$I->amOnPage('/accessories/create');
    	$this->tryTocreateNewAccessory($I, 'test_accessory_name2');
    	$I->wait(2);

    	$I->wantToTest('bulk edit accessories');

    	$test_accessory_name = $I->grabCookie('test_accessory_name');
    	$test_accessory_name2 = $I->grabCookie('test_accessory_name2');

		$I->fillField('.search .form-control', 'MyTestAccessory');
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->wait(1);

		$I->checkOption('input[name="btSelectItem"][data-index="0"]');
		$I->checkOption('input[name="btSelectItem"][data-index="1"]');

    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

    	$I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
    	$I->click('#bulkEdit');

    	$I->wait(3);

    	$I->see('Accessory Update');
    	$I->see('2 accessories');

    	$I->fillField('[name="qty"]', 2);
    	$I->click('form .box-footer [type="submit"]');

    	$I->wait(3);
		$I->seeInTitle('Accessories');
		$I->see('Accessories');
		$I->seeCurrentUrlEquals('/accessories');
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');

		$I->wait(3);
    }

    public function tryToBulkDeleteAccessories(AcceptanceTester $I)
    {
    	$I->wantToTest('bulk delete accessories');

    	$test_accessory_name = $I->grabCookie('test_accessory_name');
    	$test_accessory_name2 = $I->grabCookie('test_accessory_name2');

		$I->fillField('.search .form-control', 'MyTestAccessory');
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->wait(1);

		$I->checkOption('input[name="btSelectItem"][data-index="0"]');
		$I->checkOption('input[name="btSelectItem"][data-index="1"]');

    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

    	$I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
    	$I->click('#bulkEdit');

    	$I->wait(3);

    	$I->see('Confirm Bulk Delete Accessories');
    	$I->see('2 accessories');

    	$I->click('form #submit-button');

		$I->seeCurrentUrlEquals('/accessories');
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');
    }
}
