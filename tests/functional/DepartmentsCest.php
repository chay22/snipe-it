<?php

class DepartmentsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_department_name');
    }

    public function tryToLoadDepartmentsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the departments listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/departments');
        $I->waitForElement('table#departmentsTable tbody');
        $I->seeElement('table#departmentsTable thead');
        $I->seeElement('table#departmentsTable tbody');
        $I->seeNumberOfElements('table#departmentsTable tr', [1, 30]);
        $I->seeInTitle('Departments');
        $I->see('Departments');

        $I->seeInPageSource('/departments');
        $I->seeElement('table#departmentsTable thead');
        $I->seeElement('table#departmentsTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(3);
    }

    public function tryToCreateDepartmentButFailed(AcceptanceTester $I)
    {
        $test_department_name = 'MyTestDepartment' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/departments/create');
        $I->seeElement('select[name="manager_id"]');
        $I->seeInTitle('Create Department');
        $I->see('Create Department');

        $I->wantToTest('departments create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(0.1);
        $I->seeElement('.help-block.form-error');
    }

    public function tryTocreateNewDepartment(AcceptanceTester $I, $cookie_name = 'test_department_name')
    {
        $test_department_name = 'MyTestDepartment' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new department');
        $I->reloadPage();
        $I->waitForElementNotVisible('.help-block.form-error');
        $I->seeInTitle('Create Department');
        $I->see('Create Department');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_department_name);

        $I->executeJS('$(\'select[name="company_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-company_select-results .select2-results__option');

        $I->executeJS('
            var company_id_select = $("select[name=\'company_id\']");
            var first_company_id_data = company_id_select.data("select2").$results.children(":first").data("data");
            var first_company_id_option = new Option(first_company_id_data.text, first_company_id_data.id, true, true);

            company_id_select.append(first_company_id_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="company_id"]\').select2("close");');


        $I->executeJS('$(\'select[name="manager_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-assigned_user_select-results .select2-results__option');

        $I->executeJS('
            var manager_id_select = $("select[name=\'manager_id\']");
            var first_manager_id_data = manager_id_select.data("select2").$results.children(":first").data("data");
            var first_manager_id_option = new Option(first_manager_id_data.text, first_manager_id_data.id, true, true);

            manager_id_select.append(first_manager_id_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="manager_id"]\').select2("close");');


        $I->executeJS('$(\'select[name="location_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-location_id_location_select-results .select2-results__option');

        $I->executeJS('
            var location_id_select = $("select[name=\'location_id\']");
            var first_location_id_data = location_id_select.data("select2").$results.children(":first").data("data");
            var first_location_id_option = new Option(first_location_id_data.text, first_location_id_data.id, true, true);

            location_id_select.append(first_location_id_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="location_id"]\').select2("close");');


        $I->click('#create-form [type="submit"]');

        $I->wait(3);
        $I->seeInTitle('Departments');
        $I->see('Departments');
        $I->seeCurrentUrlEquals('/departments');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_department_name);
        $I->saveSessionSnapshot('test_department_name');
    }

    public function tryToEditDepartment(AcceptanceTester $I)
    {
        $test_department_name = $I->grabCookie('test_department_name');

        $I->wantToTest('edit previously created department');
        $I->waitForElement('table#departmentsTable tbody');
        $I->fillField('.search .form-control', $test_department_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#departmentsTable").data("bootstrap.table").data[0].name === "'.$test_department_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#departmentsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_department_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->waitForText('Update Department');
        $I->seeInTitle('Update Department');
        $I->see('Update Department');

        $old_test_department_name = $test_department_name;
        $test_department_name = 'MyTestDepartment' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_department_name);
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#departmentsTable tbody');

        $I->wantTo('ensure previous department name does not exists after update');
        $I->fillField('.search .form-control', $old_test_department_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_department_name', $test_department_name);
        $I->saveSessionSnapshot('test_department_name');
        $I->wait(1);
    }

    public function tryToDeleteDepartment(AcceptanceTester $I)
    {
        $test_department_name = $I->grabCookie('test_department_name');

        $I->wantToTest('delete previously created department');
        $I->fillField('.search .form-control', $test_department_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#departmentsTable").data("bootstrap.table").data[0].name === "'.$test_department_name.'"; } catch(e) { return false; }');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#departmentsTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_department_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_department_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('deleted successfully');
    }
}
