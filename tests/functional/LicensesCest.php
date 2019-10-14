<?php

class LicensesCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_license_name');
    }

    public function tryToLoadLicenseListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the licenses listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/licenses');
        $I->waitForElement('table#licensesTable tbody');
        $I->seeElement('table#licensesTable thead');
        $I->seeElement('table#licensesTable tbody');
        $I->seeNumberOfElements('table#licensesTable tr', [1, 30]);
        $I->seeInTitle('License');
        $I->see('License');

        $I->seeInPageSource('/licenses');
        $I->seeElement('table#licensesTable thead');
        $I->seeElement('table#licensesTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(1);
    }

    public function tryToCreateLicenseButFailed(AcceptanceTester $I)
    {
        $test_license_name = 'MyTestLicense' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/licenses/create');
        $I->seeElement('select[name="category_id"]');

        $I->wantToTest('licenses create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.help-block.form-error');
        $I->seeElement('.help-block.form-error');

        // Can not create if all required field not filled: Blocked by backend validation
        $I->wantToTest('licenses create form failed to create license when fields do not pass validation');
        $I->fillField('[name="name"]', $test_license_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.alert-msg');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewLicense(AcceptanceTester $I, $cookie_name = 'test_license_name')
    {
        $test_license_name = 'MyTestLicense' . substr(md5(mt_rand()), 0, 10);

        $license = factory(App\Models\License::class)->states('photoshop')->make([
            'name' => $test_license_name,
            'company_id' => 3,
        ]);

        $I->wantToTest('create new license');
        $I->reloadPage();
        $I->waitForText('Create License');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_license_name);
        $I->fillField('[name="serial"]', $license->serial);
        $I->fillField('[name="seats"]', $license->seats);
        $I->fillField('[name="license_name"]', $license->license_name);
        $I->fillField('[name="license_email"]', $license->license_email);
        $I->fillField('[name="order_number"]', $license->order_number);
        $I->fillField('[name="purchase_cost"]', $license->purchase_cost);
        $I->fillField('[name="purchase_date"]', '2016-01-01');
        $I->fillField('[name="expiration_date"]', '2019-01-01');
        $I->fillField('[name="termination_date"]', '2021-01-01');
        $I->checkOption('[name="maintained"]');
        $I->checkOption('[name="reassignable"]');

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


        $I->executeJS('$(\'select[name="company_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-company_select-results .select2-results__option');

        $I->executeJS('
            var company_select = $("select[name=\'company_id\']");
            var first_company_data = company_select.data("select2").$results.children(":first").data("data");
            var first_company_option = new Option(first_company_data.text, first_company_data.id, true, true);

            company_select.append(first_company_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="company_id"]\').select2("close");');


        $I->executeJS('$(\'select[name="manufacturer_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-manufacturer_select_id-results .select2-results__option');

        $I->executeJS('
            var manufacturer_select = $("select[name=\'manufacturer_id\']");
            var first_manufacturer_data = manufacturer_select.data("select2").$results.children(":first").data("data");
            var first_manufacturer_option = new Option(first_manufacturer_data.text, first_manufacturer_data.id, true, true);

            manufacturer_select.append(first_manufacturer_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="manufacturer_id"]\').select2("close");');



        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#licensesTable tbody');
        $I->seeInTitle('License');
        $I->see('License');
        $I->seeCurrentUrlEquals('/licenses');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_license_name);
        $I->saveSessionSnapshot('test_license_name');
    }

    public function tryToEditLicense(AcceptanceTester $I)
    {
        $test_license_name = $I->grabCookie('test_license_name');

        $I->wantToTest('edit previously created license');
        $I->fillField('.search .form-control', $test_license_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#licensesTable").data("bootstrap.table").data[0].name === "'.$test_license_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#licensesTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_license_name.'") {
                    window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
                }
            });
        ');
        $I->waitForText('Update License');
        $I->seeInTitle('Update License');
        $I->see('Update License');

        $old_test_license_name = $test_license_name;
        $test_license_name = 'MyTestLicense' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_license_name);
        $I->click('#create-form [type="submit"]');

        $I->waitForText('Success');
        $I->amOnPage('/licenses');
        $I->waitForElement('table#licensesTable tbody');

        $I->wantTo('ensure previous license name does not exists after update');
        $I->fillField('.search .form-control', $old_test_license_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_license_name', $test_license_name);
        $I->saveSessionSnapshot('test_license_name');
        $I->wait(1);
    }

    public function tryToDeleteLicense(AcceptanceTester $I)
    {
        $test_license_name = $I->grabCookie('test_license_name');

        $I->wantToTest('delete previously created license');
        $I->fillField('.search .form-control', $test_license_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#licensesTable").data("bootstrap.table").data[0].name === "'.$test_license_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#licensesTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_license_name.'") {
                    $(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
                }
            });
        ');

        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_license_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The license was deleted successfully');
    }

    public function tryToBulkEditLicense(AcceptanceTester $I)
    {
        $I->amOnPage('/licenses/create');
        $this->tryTocreateNewLicense($I, 'test_license_name');
        $I->wait(1);

        $I->amOnPage('/licenses/create');
        $this->tryTocreateNewLicense($I, 'test_license_name2');
        $I->wait(1);

        $I->wantToTest('bulk edit licenses');

        $test_license_name = $I->grabCookie('test_license_name');
        $test_license_name2 = $I->grabCookie('test_license_name2');

        $I->fillField('.search .form-control', 'MyTestLicense');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#licensesTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
        $I->click('#bulkEdit');

        $I->waitForText('Update License');
        $I->see('Update License');
        $I->see('2 licenses');



        $I->executeJS('$(\'select[name="manufacturer_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-manufacturer_select_id-results .select2-results__option');

        $I->executeJS('
            var manufacturer_select = $("select[name=\'manufacturer_id\']");
            var first_manufacturer_data = manufacturer_select.data("select2").$results.children(":first").data("data");
            var first_manufacturer_option = new Option(first_manufacturer_data.text, first_manufacturer_data.id, true, true);

            manufacturer_select.append(first_manufacturer_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="manufacturer_id"]\').select2("close");');
        
        $I->click('form .box-footer [type="submit"]');

        $I->seeElement('table#licensesTable tbody');
        $I->seeInTitle('License');
        $I->see('License');
        $I->seeCurrentUrlEquals('/licenses');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->wait(1);
    }

    public function tryToBulkDeleteLicense(AcceptanceTester $I)
    {
        $I->wantToTest('bulk delete licenses');

        $test_license_name = $I->grabCookie('test_license_name');
        $test_license_name2 = $I->grabCookie('test_license_name2');

        $I->fillField('.search .form-control', 'MyTestLicense');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#licensesTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
        $I->click('#bulkEdit');

        $I->waitForText('Confirm Bulk Delete License');
        $I->see('Confirm Bulk Delete License');
        $I->see('2 licenses');

        $I->click('form #submit-button');

        $I->seeCurrentUrlEquals('/licenses');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
    }
}
