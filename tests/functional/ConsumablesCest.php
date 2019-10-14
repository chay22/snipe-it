<?php

class ConsumablesCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_consumable_name');
    }

    public function tryToLoadConsumablesListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the consumables listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/consumables');
        $I->waitForElement('table#consumablesTable tbody');
        $I->seeElement('table#consumablesTable thead');
        $I->seeElement('table#consumablesTable tbody');
        $I->seeNumberOfElements('table#consumablesTable tr', [1, 30]);
        $I->seeInTitle('Consumables');
        $I->see('Consumables');

        $I->seeInPageSource('/consumables');
        $I->seeElement('table#consumablesTable thead');
        $I->seeElement('table#consumablesTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(1);
    }

    public function tryToCreateConsumableButFailed(AcceptanceTester $I)
    {
        $test_consumable_name = 'MyTestConsumable' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/consumables/create');
        $I->seeElement('select[name="category_id"]');

        $I->wantToTest('consumables create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(0.1);
        $I->seeElement('.help-block.form-error');

        // Can not create if all required field not filled: Blocked by backend validation
        $I->wantToTest('consumables create form failed to create consumable when fields do not pass validation');
        $I->fillField('[name="name"]', $test_consumable_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElement('.alert-msg');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewConsumable(AcceptanceTester $I, $cookie_name = 'test_consumable_name')
    {
        $test_consumable_name = 'MyTestConsumable' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new consumable');
        $I->reloadPage();
        $I->waitForElement('[name="category_id"]');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_consumable_name);
        $I->executeJS('$(\'select[name="category_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-category_select_id-results .select2-results__option');

        $I->executeJS('
        	var category_select = $("select[name=\'category_id\']");
        	var first_category_data = category_select.data("select2").$results.children(":first").data("data");
        	var first_category_option = new Option(first_category_data.text, first_category_data.id, true, true);

        	category_select.append(first_category_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="category_id"]\').select2("close");');

        $I->fillField('[name="qty"]', '5');
        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#consumablesTable tbody');
        $I->seeInTitle('Consumables');
        $I->see('Consumables');
        $I->seeCurrentUrlEquals('/consumables');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_consumable_name);
        $I->saveSessionSnapshot('test_consumable_name');
    }

    public function tryToEditConsumable(AcceptanceTester $I)
    {
        $test_consumable_name = $I->grabCookie('test_consumable_name');

        $I->wantToTest('edit previously created consumable');
        $I->waitForElement('table#consumablesTable tbody');
        $I->fillField('.search .form-control', $test_consumable_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#consumablesTable").data("bootstrap.table").data[0].name === "'.$test_consumable_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#consumablesTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_consumable_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update Consumable');
        $I->seeInTitle('Update Consumable');
        $I->see('Update Consumable');

        $old_test_consumable_name = $test_consumable_name;
        $test_consumable_name = 'MyTestConsumable' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_consumable_name);
        $I->fillField('[name="model_number"]', substr(md5(mt_rand()), 0, 14));
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#consumablesTable tbody');

        $I->wantTo('ensure previous consumable name does not exists after update');
        $I->fillField('.search .form-control', $old_test_consumable_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_consumable_name', $test_consumable_name);
        $I->saveSessionSnapshot('test_consumable_name');
        $I->wait(1);
    }

    public function tryToDeleteConsumable(AcceptanceTester $I)
    {
        $test_consumable_name = $I->grabCookie('test_consumable_name');

        $I->wantToTest('delete previously created consumable');
        $I->fillField('.search .form-control', $test_consumable_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForJS('try { return $("table#consumablesTable").data("bootstrap.table").data[0].name === "'.$test_consumable_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#consumablesTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_consumable_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_consumable_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The consumable was deleted successfully');
    }

    public function tryToBulkEditConsumables(AcceptanceTester $I)
    {
        	$I->amOnPage('/consumables/create');
        $this->tryTocreateNewConsumable($I, 'test_consumable_name');
        $I->wait(1);

        $I->amOnPage('/consumables/create');
        $this->tryTocreateNewConsumable($I, 'test_consumable_name2');
        $I->wait(1);

        $I->wantToTest('bulk edit consumables');

        $test_consumable_name = $I->grabCookie('test_consumable_name');
        $test_consumable_name2 = $I->grabCookie('test_consumable_name2');

        $I->fillField('.search .form-control', 'MyTestConsumable');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForJS('try { return $("table#consumablesTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
        $I->click('#bulkEdit');

        $I->waitForText('Consumable Update');

        $I->see('Consumable Update');
        $I->see('2 consumables');

        $I->fillField('[name="qty"]', 2);
        $I->click('form .box-footer [type="submit"]');

        $I->waitForElement('table#consumablesTable tbody');
        $I->seeInTitle('Consumables');
        $I->see('Consumables');
        $I->seeCurrentUrlEquals('/consumables');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->wait(1);
    }

    public function tryToBulkDeleteConsumables(AcceptanceTester $I)
    {
        $I->wantToTest('bulk delete consumables');

        $test_consumable_name = $I->grabCookie('test_consumable_name');
        $test_consumable_name2 = $I->grabCookie('test_consumable_name2');

        $I->fillField('.search .form-control', 'MyTestConsumable');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForJS('try { return $("table#consumablesTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
        $I->click('#bulkEdit');
        $I->waitForText('Confirm Bulk Delete Consumables');

        $I->see('Confirm Bulk Delete Consumables');
        $I->see('2 consumables');

        $I->click('form #submit-button');

        $I->waitForElement('table#consumablesTable tbody');
        $I->seeCurrentUrlEquals('/consumables');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
    }
}
