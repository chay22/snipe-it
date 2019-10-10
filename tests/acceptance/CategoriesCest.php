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
        $I->waitForElement('table#categoryTable tbody');
        $I->seeElement('table#categoryTable thead');
        $I->seeElement('table#categoryTable tbody');
        $I->seeNumberOfElements('table#categoryTable tr', [1, 30]);
        $I->seeInTitle('Categories');
        $I->see('Categories');

        $I->seeInPageSource('/categories');
        $I->seeElement('table#categoryTable thead');
        $I->seeElement('table#categoryTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->waitForElement('select[name="category_type"]');
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
        $I->waitForElementVisible('.help-block.form-error');
        $I->seeElement('.help-block.form-error');

        // Can not create if all required field not filled: Blocked by backend validation
        $I->wantToTest('categories create form failed to create category when fields do not pass validation');
        $I->fillField('[name="name"]', $test_category_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.alert-msg');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewCategory(AcceptanceTester $I, $cookie_name = 'test_category_name')
    {
        $test_category_name = 'MyTestCategory' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new category');
        $I->reloadPage();
        $I->waitForElement('select[name="category_type"]');
        $I->seeInTitle('Create Category');
        $I->see('Create Category');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_category_name);
        $I->selectOption('select[name="category_type"]', 'accessory');
        $I->executeJS('$(\'select[name="category_type"]\').trigger("change");');
        $I->wait(0.1);
        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#categoryTable tbody');
        $I->seeInTitle('Categories');
        $I->see('Categories');
        $I->seeCurrentUrlEquals('/categories');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_category_name);
        $I->saveSessionSnapshot('test_category_name');
        $I->wait(1);
    }

    public function tryToEditCategory(AcceptanceTester $I)
    {
        $test_category_name = $I->grabCookie('test_category_name');

        $I->wantToTest('edit previously created category');
        $I->waitForElement('table#categoryTable tbody');
        $I->fillField('.search .form-control', $test_category_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#categoryTable").data("bootstrap.table").data[0].name === "'.$test_category_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#categoryTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_category_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update Category');
        $I->seeInTitle('Update Category');
        $I->see('Update Category');

        $old_test_category_name = $test_category_name;
        $test_category_name = 'MyTestCategory' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_category_name);
        $I->selectOption('select[name="category_type"]', 'asset');
        $I->executeJS('$(\'select[name="category_type"]\').trigger("change");');
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#categoryTable tbody');

        $I->wantTo('ensure previous category name does not exists after update');
        $I->fillField('.search .form-control', $old_test_category_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
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
        $I->waitForElement('table#categoryTable tbody');
        $I->fillField('.search .form-control', $test_category_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#categoryTable").data("bootstrap.table").data[0].name === "'.$test_category_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#categoryTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_category_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_category_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The category was deleted successfully');
    }

    public function tryToBulkEditCategories(AcceptanceTester $I)
    {
        $I->amOnPage('/categories/create');
        $this->tryTocreateNewCategory($I, 'test_category_name');
        $I->wait(1);

        $I->amOnPage('/categories/create');
        $this->tryTocreateNewCategory($I, 'test_category_name2');
        $I->wait(1);

        $I->wantToTest('bulk edit categories');

        $test_category_name = $I->grabCookie('test_category_name');
        $test_category_name2 = $I->grabCookie('test_category_name2');

        $I->fillField('.search .form-control', 'MyTestCategory');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#categoryTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
        $I->click('#bulkEdit');

        $I->waitForText('Update Category');
        $I->see('Update Category');
        $I->see('2 categories');

        $I->selectOption('select[name="category_type"]', 'accessory');
        $I->executeJS('$(\'select[name="category_type"]\').trigger("change");');
        $I->click('form .box-footer [type="submit"]');

        $I->waitForElement('table#categoryTable tbody');
        $I->seeInTitle('Categories');
        $I->see('Categories');
        $I->seeCurrentUrlEquals('/categories');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->wait(1);
    }

    public function tryToBulkDeleteCategories(AcceptanceTester $I)
    {
        $I->wantToTest('bulk delete categories');

        $test_category_name = $I->grabCookie('test_category_name');
        $test_category_name2 = $I->grabCookie('test_category_name2');

        $I->waitForElement('table#categoryTable tbody');
        $I->fillField('.search .form-control', 'MyTestCategory');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#categoryTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
        $I->click('#bulkEdit');

        $I->waitForText('Confirm Bulk Delete Categories');
        $I->see('Confirm Bulk Delete Categories');
        $I->see('2 categories');

        $I->click('form #submit-button');

        $I->waitForElement('table#categoryTable tbody');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeCurrentUrlEquals('/categories');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
    }
}
