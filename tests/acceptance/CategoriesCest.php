<?php

class CategoriesCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_category_name');
    }

    public function tryToLoadCategoriesListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the categories listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/categories');
        $I->wait(3);
        $I->waitForElement('table#categoryTable', 3); // secs
        $I->seeElement('table#categoryTable thead');
        $I->seeElement('table#categoryTable tbody');
        $I->seeNumberOfElements('table#categoryTable tr', [1, 30]);
        $I->seeInTitle('Categories');
        $I->see('Categories');

        $I->seeInPageSource('/categories');
        $I->seeElement('table#categoryTable thead');
        $I->seeElement('table#categoryTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(3);
    }

    public function tryToCreateCategoryButFailed(AcceptanceTester $I)
    {
        $test_category_name = 'MyTestCategory' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/categories/create');
        $I->seeElement('select[name="category_type"]');
        $I->seeInTitle('Create Category');
        $I->see('Create Category');

        $I->wantToTest('categories create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(1);
        $I->seeElement('.help-block.form-error');

        // Can not create if all required field not filled: Blocked by backend validation
        $I->wantToTest('categories create form failed to create category when fields do not pass validation');
        $I->fillField('[name="name"]', $test_category_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(1);
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewCategory(AcceptanceTester $I, $cookie_name = 'test_category_name')
    {
        $test_category_name = 'MyTestCategory' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new category');
        $I->reloadPage();
        $I->wait(3);
        $I->seeInTitle('Create Category');
        $I->see('Create Category');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_category_name);
        $I->selectOption('select[name="category_type"]', 'accessory');
        $I->executeJS('$(\'select[name="category_type"]\').trigger("change");');
        $I->wait(1);
        $I->click('#create-form [type="submit"]');

        $I->wait(3);
        $I->seeInTitle('Categories');
        $I->see('Categories');
        $I->seeCurrentUrlEquals('/categories');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_category_name);
        $I->saveSessionSnapshot('test_category_name');
    }

    public function tryToEditCategory(AcceptanceTester $I)
    {
        $test_category_name = $I->grabCookie('test_category_name');

        $I->wantToTest('edit previously created category');
        $I->fillField('.search .form-control', $test_category_name);
        $I->wait(1);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#categoryTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_category_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->wait(3);
        $I->seeInTitle('Update Category');
        $I->see('Update Category');

        $old_test_category_name = $test_category_name;
        $test_category_name = 'MyTestCategory' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_category_name);
        $I->selectOption('select[name="category_type"]', 'asset');
        $I->executeJS('$(\'select[name="category_type"]\').trigger("change");');
        $I->click('#create-form [type="submit"]');
        $I->wait(3);

        $I->wantTo('ensure previous category name does not exists after update');
        $I->fillField('.search .form-control', $old_test_category_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_category_name', $test_category_name);
        $I->saveSessionSnapshot('test_category_name');
        $I->wait(1);
    }

    public function tryToDeleteCategory(AcceptanceTester $I)
    {
        $test_category_name = $I->grabCookie('test_category_name');

        $I->wantToTest('delete previously created category');
        $I->fillField('.search .form-control', $test_category_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);
        $I->executeJS('
        	var bootstrap_table_instance = $("table#categoryTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_category_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->wait(3);
        $I->see('Are you sure you wish to delete ' . $test_category_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->wait(3);
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The category was deleted successfully');
    }

    public function tryToBulkEditCategories(AcceptanceTester $I)
    {
        	$I->amOnPage('/categories/create');
        $this->tryTocreateNewCategory($I, 'test_category_name');
        $I->wait(2);

        $I->amOnPage('/categories/create');
        $this->tryTocreateNewCategory($I, 'test_category_name2');
        $I->wait(2);

        $I->wantToTest('bulk edit categories');

        $test_category_name = $I->grabCookie('test_category_name');
        $test_category_name2 = $I->grabCookie('test_category_name2');

        $I->fillField('.search .form-control', 'MyTestCategory');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
        $I->click('#bulkEdit');

        $I->wait(3);

        $I->see('Update Category');
        $I->see('2 categories');

        $I->selectOption('select[name="category_type"]', 'accessory');
        $I->executeJS('$(\'select[name="category_type"]\').trigger("change");');
        $I->click('form .box-footer [type="submit"]');

        $I->wait(3);
        $I->seeInTitle('Categories');
        $I->see('Categories');
        $I->seeCurrentUrlEquals('/categories');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->wait(3);
    }

    public function tryToBulkDeleteCategories(AcceptanceTester $I)
    {
        $I->wantToTest('bulk delete categories');

        $test_category_name = $I->grabCookie('test_category_name');
        $test_category_name2 = $I->grabCookie('test_category_name2');

        $I->fillField('.search .form-control', 'MyTestCategory');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
        $I->click('#bulkEdit');

        $I->wait(3);

        $I->see('Confirm Bulk Delete Categories');
        $I->see('2 categories');

        $I->click('form #submit-button');

        $I->seeCurrentUrlEquals('/categories');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
    }
}
