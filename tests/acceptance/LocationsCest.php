<?php

class LocationsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_location_name');
    }

    public function tryToLoadLocationsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the locations listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/locations');
        $I->wait(3);
        $I->waitForElement('table#locationTable', 3); // secs
        $I->seeElement('table#locationTable thead');
        $I->seeElement('table#locationTable tbody');
        $I->seeNumberOfElements('table#locationTable tr', [1, 30]);
        $I->seeInTitle('Locations');
        $I->see('Locations');

        $I->seeInPageSource('/locations');
        $I->seeElement('table#locationTable thead');
        $I->seeElement('table#locationTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(3);
    }

    public function tryToCreateLocationButFailed(AcceptanceTester $I)
    {
        $test_location_name = 'MyTestLocation' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/locations/create');
        $I->seeElement('select[name="manager_id"]');
        $I->seeInTitle('Create Location');
        $I->see('Create Location');

        $I->wantToTest('locations create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->wait(1);
        $I->seeElement('.help-block.form-error');
    }

    public function tryTocreateNewLocation(AcceptanceTester $I, $cookie_name = 'test_location_name')
    {
        $test_location_name = 'MyTestLocation' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new location');
        $I->reloadPage();
        $I->wait(3);
        $I->seeInTitle('Create Location');
        $I->see('Create Location');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_location_name);

        $I->executeJS('$(\'select[name="manager_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 2);
        $I->wait(1);

        $I->executeJS('
            var manager_id_select = $("select[name=\'manager_id\']");
            var first_manager_id_data = manager_id_select.data("select2").$results.children(":first").data("data");
            var first_manager_id_option = new Option(first_manager_id_data.text, first_manager_id_data.id, true, true);

            manager_id_select.append(first_manager_id_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="manager_id"]\').select2("close");');

        $I->fillField('[name="address"]', 'Address example');

        $I->executeJS('$(\'select[name="country"]\').select2("open");');
        $I->wait(0.5);

        
        $I->fillField('.select2-search__field', 'indonesia');
        $I->selectOption('select[name="country"]', 'ID');
        $I->executeJS('$(\'select[name="country"]\').trigger("change");');

        $I->click('#create-form [type="submit"]');

        $I->wait(3);
        $I->seeInTitle('Locations');
        $I->see('Locations');
        $I->seeCurrentUrlEquals('/locations');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_location_name);
        $I->saveSessionSnapshot('test_location_name');
    }

    public function tryToEditLocation(AcceptanceTester $I)
    {
        $test_location_name = $I->grabCookie('test_location_name');

        $I->wantToTest('edit previously created location');
        $I->fillField('.search .form-control', $test_location_name);
        $I->wait(1);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->executeJS('
        	var bootstrap_table_instance = $("table#locationTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_location_name.'") {
        			window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
        		}
        	});
        ');
        $I->wait(3);
        $I->seeInTitle('Update Location');
        $I->see('Update Location');

        $old_test_location_name = $test_location_name;
        $test_location_name = 'MyTestLocation' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_location_name);
        $I->click('#create-form [type="submit"]');
        $I->wait(3);

        $I->wantTo('ensure previous location name does not exists after update');
        $I->fillField('.search .form-control', $old_test_location_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_location_name', $test_location_name);
        $I->saveSessionSnapshot('test_location_name');
        $I->wait(1);
    }

    public function tryToDeleteLocation(AcceptanceTester $I)
    {
        $test_location_name = $I->grabCookie('test_location_name');

        $I->wantToTest('delete previously created location');
        $I->fillField('.search .form-control', $test_location_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->wait(1);
        $I->executeJS('
        	var bootstrap_table_instance = $("table#locationTable").data("bootstrap.table");

        	$.each(bootstrap_table_instance.data, function (k, v) {
        		if (v.name === "'.$test_location_name.'") {
        			$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
        		}
        	});
        ');
        $I->wait(3);
        $I->see('Are you sure you wish to delete ' . $test_location_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->wait(3);
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The location was deleted successfully');
    }
}
