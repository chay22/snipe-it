<?php

class CustomfieldsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_field_name');
        $I->loadSessionSnapshot('test_fieldset_name');
    }

    public function tryToLoadCustomfieldsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the fields listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/fields');
        $I->waitForElement('table#customFieldsTable tbody');
        $I->waitForElement('table#customFieldsetsTable tbody');
        $I->seeElement('table#customFieldsTable thead');
        $I->seeElement('table#customFieldsTable tbody');
        $I->seeNumberOfElements('table#customFieldsTable tr', [1, 30]);
        $I->seeInTitle('Custom Fields');
        $I->see('Custom Fields');

        $I->seeInPageSource('/fields');
        $I->seeElement('table#customFieldsTable');
    }

    public function tryToCreateCustomfieldButFailed(AcceptanceTester $I)
    {
        $test_field_name = 'MyTestCustomfield' . substr(md5(mt_rand()), 0, 10);

        $I->amOnPage('/fields/create');
        $I->waitForElement('select[name="element"]');
        $I->seeElement('select[name="element"]');
        $I->seeElement('select[name="format"]');

        $I->wantToTest('fields create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.alert.alert-danger.fade.in');
        $I->clickWithLeftButton('#webui .form-horizontal [type="submit"]');
        $I->waitForElementVisible('.alert.alert-danger.fade.in');
        $I->seeElement('.alert.alert-danger.fade.in');
        $I->reloadPage();
        $I->wait(1);
    }

    public function tryTocreateNewCustomfield(AcceptanceTester $I, $cookie_name = 'test_field_name')
    {
        $test_field_name = 'MyTestCustomfield' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new field');
        $I->waitForText('Custom Fields');
        $I->waitForElement('select[name="element"]');
        $I->dontSeeElement('.alert.alert-danger.fade.in');
        $I->fillField('[name="name"]', $test_field_name);
        $I->selectOption('select[name="element"]', 'text');
        $I->selectOption('select[name="format"]', 'ANY');

        $I->clickWithLeftButton('#webui .form-horizontal [type="submit"]');
        $I->waitForElement('table#customFieldsTable tbody');
        $I->seeInTitle('Custom Fields');
        $I->see('Custom Fields');
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
        $I->waitForElement('table#customFieldsTable tbody');
        $I->waitForElement('table#customFieldsetsTable tbody');
        $I->fillField('#customFieldsTableWrapper .search .form-control', $test_field_name);
        $I->waitForElementNotVisible('.fixed-table-loading');

        $I->waitForJS('try { return $("table#customFieldsTable").data("bootstrap.table").data[0][0] === "'.$test_field_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            window.location.href = $("table#customFieldsTable").data("bootstrap.table")
                                        .$tableBody.find("tbody tr[data-index=\'0\'] td:last a.btn").attr("href");
        ');
        $I->waitForText('Custom Field');
        $I->waitForElement('select[name="element"]');
        $I->seeElement('select[name="element"]');
        $I->seeElement('select[name="format"]');
        $I->seeInTitle('Custom Field');
        $I->see('Custom Field');

        $old_test_field_name = $test_field_name;
        $test_field_name = 'MyTestCustomfield' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_field_name);
        $I->click('#webui .form-horizontal [type="submit"]');
        $I->dontSeeElement('.alert.alert-danger.fade.in');
        $I->waitForElement('table#customFieldsTable tbody');

        $I->wantTo('ensure previous field name does not exists after update');
        $I->fillField('#customFieldsTableWrapper .search .form-control', $old_test_field_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_field_name', $test_field_name);
        $I->saveSessionSnapshot('test_field_name');
        $I->wait(1);
    }







    public function tryToCreateCustomfieldsetButFailed(AcceptanceTester $I)
    {
        $test_fieldset_name = 'MyTestCustomfieldset' . substr(md5(mt_rand()), 0, 10);

        $I->amOnPage('/fields/fieldsets/create');
        $I->waitForElement('[name="name"]');
        $I->seeElement('[name="name"]');
        $I->waitForText('New Fieldset');

        $I->wantToTest('fieldsets create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.alert.alert-danger.fade.in');
        $I->clickWithLeftButton('#webui .form-horizontal [type="submit"]');
        $I->waitForElementVisible('.alert.alert-danger.fade.in');
        $I->seeElement('.alert.alert-danger.fade.in');
        $I->reloadPage();
        $I->wait(1);
    }

    public function tryTocreateNewCustomfieldset(AcceptanceTester $I, $cookie_name = 'test_fieldset_name')
    {
        $test_fieldset_name = 'MyTestCustomfieldset' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new fieldset');
        $I->waitForText('New Fieldset');
        $I->waitForElement('[name="name"]');
        $I->seeElement('[name="name"]');
        $I->dontSeeElement('.alert.alert-danger.fade.in');
        $I->fillField('[name="name"]', $test_fieldset_name);

        $I->clickWithLeftButton('#webui .form-horizontal [type="submit"]');
        $I->waitForElement('table[name="fieldsets"]');
        $I->seeInTitle('Custom Fields');
        $I->see('Fieldset');
        $I->seeInCurrentUrl('/fields/fieldsets');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->seeElement('[name="required"]');
        $I->seeElement('[name="order"]');
        $I->seeElement('[name="field_id"]');

        $I->setCookie($cookie_name, $test_fieldset_name);
        $I->saveSessionSnapshot('test_fieldset_name');
        $I->amOnPage('fields');
        $I->waitForElementVisible('table#customFieldsTable tbody');
    }

    public function tryToAddFieldToCustomfieldset(AcceptanceTester $I)
    {
        $test_fieldset_name = $I->grabCookie('test_fieldset_name');

        $I->wantToTest('add field to fieldset');
        $I->waitForElement('table#customFieldsetsTable tbody');
        $I->fillField('#customFieldsetsTableWrapper .search .form-control', $test_fieldset_name);
        $I->waitForElementNotVisible('.fixed-table-loading');

        $I->waitForJS('try { return $("table#customFieldsetsTable").data("bootstrap.table").$tableBody.find("tr[data-index=\'0\'] > td:first a").text() === "'.$test_fieldset_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            window.location.href = $("table#customFieldsetsTable").data("bootstrap.table")
                                        .$tableBody.find("tr[data-index=\'0\'] > td:first a").attr("href");
        ');
        $I->waitForText($test_fieldset_name . ' Fieldset');
        
        $I->seeInTitle('Custom Fields');
        $I->see('Custom Fields');

        $I->seeElement('[name="required"]');
        $I->seeElement('[name="order"]');
        $I->seeElement('[name="field_id"]');

        $I->checkOption('[name="required"]');
        $first_fieldset_id = $I->executeJS('return $("select[name=\'field_id\'] option:first").next().attr("value");');
        $I->selectOption('[name="field_id"]', $first_fieldset_id);

        $I->waitForElement('.alert.fade.in');
        $I->see('Success');

        $I->seeNumberOfElements('table[name="fieldsets"] tbody tr', 1);
        $I->amOnPage('/fields');
        $I->waitForElement('table#customFieldsTable tbody');
    }

    public function tryToDeleteCustomfieldset(AcceptanceTester $I)
    {
        $test_fieldset_name = $I->grabCookie('test_fieldset_name');

        $I->wantToTest('delete previously created fieldset');
        $I->amOnPage('/fields');
        $I->waitForElement('table#customFieldsetsTable tbody');
        $I->fillField('#customFieldsetsTableWrapper .search .form-control', $test_fieldset_name);
        $I->waitForElementNotVisible('.fixed-table-loading');

        $I->waitForJS('try { return $("table#customFieldsetsTable").data("bootstrap.table").$tableBody.find("tr[data-index=\'0\'] > td:first a").text() === "'.$test_fieldset_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            $("table#customFieldsetsTable").data("bootstrap.table")
                        .$tableBody.find("tr[data-index=\'0\'] > td:last button[type=\'submit\']").click();
        ');
        // $I->waitForElementVisible('#dataConfirmModal');
        // $I->waitForElementVisible('#dataConfirmOK');
        // $I->see('Are you sure you wish to delete ' . $test_fieldset_name . '?', '#dataConfirmModal');
        // $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('deleted successfully');
    }




    public function tryToDeleteCustomfield(AcceptanceTester $I)
    {
        $test_field_name = $I->grabCookie('test_field_name');

        $I->wantToTest('delete previously created field');
        $I->amOnPage('/fields');
        $I->waitForElement('table#customFieldsTable tbody');
        $I->fillField('#customFieldsTableWrapper .search .form-control', $test_field_name);
        $I->waitForElementNotVisible('.fixed-table-loading');

        $I->waitForJS('try { return $("table#customFieldsTable").data("bootstrap.table").data[0][0] === "'.$test_field_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            $("table#customFieldsTable").data("bootstrap.table")
                        .$tableBody.find("tbody tr[data-index=\'0\'] td:last button[type=\'submit\'].btn-danger").click();
        ');
        // $I->waitForElementVisible('#dataConfirmModal');
        // $I->waitForElementVisible('#dataConfirmOK');
        // $I->see('Are you sure you wish to delete ' . $test_field_name . '?', '#dataConfirmModal');
        // $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('deleted successfully');
    }

}
