<?php

class SuppliersCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_supplier_name');
    }

    public function tryToLoadSuppliersListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the suppliers listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/suppliers');
        $I->waitForElement('table#suppliersTable tbody');
        $I->seeElement('table#suppliersTable thead');
        $I->seeElement('table#suppliersTable tbody');
        $I->seeNumberOfElements('table#suppliersTable tr', [1, 30]);
        $I->seeInTitle('Suppliers');
        $I->see('Suppliers');

        $I->seeInPageSource('/suppliers');
        $I->seeElement('table#suppliersTable thead');
        $I->seeElement('table#suppliersTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
    }

    public function tryToCreateSupplierButFailed(AcceptanceTester $I)
    {
        $test_supplier_name = 'MyTestSupplier' . substr(md5(mt_rand()), 0, 10);

        $I->waitForText('Create Supplier');
        $I->seeCurrentUrlEquals('/suppliers/create');
        $I->seeElement('select[name="country"]');
        $I->seeInTitle('Create Supplier');
        $I->see('Create Supplier');

        $I->wantToTest('suppliers create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(0.1);
        $I->seeElement('.help-block.form-error');
    }

    public function tryTocreateNewSupplier(AcceptanceTester $I, $cookie_name = 'test_supplier_name')
    {
        $test_supplier_name = 'MyTestSupplier' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new supplier');
        $I->reloadPage();
        $I->waitForText('Create Supplier');
        $I->seeInTitle('Create Supplier');
        $I->see('Create Supplier');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_supplier_name);


        $I->fillField('[name="address"]', 'Address example');
        $I->fillField('[name="city"]', 'City example');
        $I->fillField('[name="state"]', 'State example');
        $I->fillField('[name="contact"]', 'Supplier Name');

        $I->executeJS('$(\'select[name="country"]\').select2("open");');
        $I->wait(0.2);

        
        $I->fillField('.select2-search__field', 'indonesia');
        $I->selectOption('select[name="country"]', 'ID');
        $I->executeJS('$(\'select[name="country"]\').trigger("change");');

        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#suppliersTable tbody');
        $I->seeInTitle('Suppliers');
        $I->see('Suppliers');
        $I->seeCurrentUrlEquals('/suppliers');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_supplier_name);
        $I->saveSessionSnapshot('test_supplier_name');
    }

    public function tryToEditSupplier(AcceptanceTester $I)
    {
        $test_supplier_name = $I->grabCookie('test_supplier_name');

        $I->wantToTest('edit previously created supplier');
        $I->waitForElement('table#suppliersTable tbody');
        $I->fillField('.search .form-control', $test_supplier_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForJS('try { return $("table#suppliersTable").data("bootstrap.table").data[0].name === "'.$test_supplier_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#suppliersTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_supplier_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update Supplier');
        $I->seeInTitle('Update Supplier');
        $I->see('Update Supplier');

        $old_test_supplier_name = $test_supplier_name;
        $test_supplier_name = 'MyTestSupplier' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_supplier_name);
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#suppliersTable tbody');

        $I->wantTo('ensure previous supplier name does not exists after update');
        $I->fillField('.search .form-control', $old_test_supplier_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->see('No matching records found');

        $I->setCookie('test_supplier_name', $test_supplier_name);
        $I->saveSessionSnapshot('test_supplier_name');
        $I->wait(1);
    }

    public function tryToDeleteSupplier(AcceptanceTester $I)
    {
        $test_supplier_name = $I->grabCookie('test_supplier_name');

        $I->wantToTest('delete previously created supplier');
        $I->waitForElement('table#suppliersTable tbody');
        $I->fillField('.search .form-control', $test_supplier_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForJS('try { return $("table#suppliersTable").data("bootstrap.table").data[0].name === "'.$test_supplier_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#suppliersTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_supplier_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_supplier_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->seeElement('.alert.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('deleted successfully');
    }
}
