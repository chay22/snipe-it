<?php

class ComponentsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_component_name');
    }

    public function tryToLoadComponentsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the components listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/components');
        $I->waitForElement('table#componentsTable tbody');
        $I->seeElement('table#componentsTable thead');
        $I->seeElement('table#componentsTable tbody');
        $I->seeNumberOfElements('table#componentsTable tr', [1, 30]);
        $I->seeInTitle('Components');
        $I->see('Components');

        $I->seeInPageSource('/components');
        $I->seeElement('table#componentsTable thead');
        $I->seeElement('table#componentsTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(1);
    }

    public function tryToCreateComponentButFailed(AcceptanceTester $I)
    {
        $test_component_name = 'MyTestComponent' . substr(md5(mt_rand()), 0, 10);

        $I->waitForElementVisible('[name="name"]');
        $I->seeCurrentUrlEquals('/components/create');
        $I->seeElement('[name="name"]');
        $I->seeInTitle('Create Component');
        $I->see('Create Component');

        $I->wantToTest('components create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.help-block.form-error');
        $I->seeElement('.help-block.form-error');
    }

    public function tryTocreateNewComponent(AcceptanceTester $I, $cookie_name = 'test_component_name')
    {
        $test_component_name = 'MyTestComponent' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new component');
        $I->reloadPage();
        $I->waitForElementVisible('[name="name"]');
        $I->seeInTitle('Create Component');
        $I->see('Create Component');
        $I->dontSeeElement('.help-block.form-error');


        $component = factory(App\Models\Component::class)->states('ram-crucial4')->make([
            'name' => $test_component_name,
            'serial' => '3523-235325-1350235'
        ]);

        $I->fillField('[name="name"]', $component->name);

        $I->executeJS('$(\'select[name="category_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-category_select_id-results .select2-results__option');

        $I->executeJS('
            var category_id_select = $("select[name=\'category_id\']");
            var first_category_id_data = category_id_select.data("select2").$results.children(":first").data("data");
            var first_category_id_option = new Option(first_category_id_data.text, first_category_id_data.id, true, true);

            category_id_select.append(first_category_id_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="category_id"]\').select2("close");');

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


        $I->fillField('[name="qty"]', $component->qty);
        $I->fillField('[name="min_amt"]', $component->min_amt);
        $I->fillField('[name="order_number"]', $component->order_number);
        $I->fillField('[name="purchase_cost"]', $component->purchase_cost);
        $I->fillField('[name="purchase_date"]', '2016-01-01');
        $I->fillField('[name="serial"]', $component->serial);

        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#componentsTable tbody');
        $I->seeInTitle('Components');
        $I->see('Components');
        $I->seeCurrentUrlEquals('/components');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_component_name);
        $I->saveSessionSnapshot('test_component_name');
    }

    public function tryToEditComponent(AcceptanceTester $I)
    {
        $test_component_name = $I->grabCookie('test_component_name');

        $I->wantToTest('edit previously created component');
        $I->fillField('.search .form-control', $test_component_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#componentsTable").data("bootstrap.table").data[0].name === "'.$test_component_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#componentsTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_component_name.'") {
                    window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
                }
            });
        ');
        $I->waitForText('Update Component');
        $I->seeInTitle('Update Component');
        $I->see('Update Component');

        $old_test_component_name = $test_component_name;
        $test_component_name = 'MyTestComponent' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_component_name);
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#componentsTable tbody');

        $I->wantTo('ensure previous component name does not exists after update');
        $I->fillField('.search .form-control', $old_test_component_name);
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_component_name', $test_component_name);
        $I->saveSessionSnapshot('test_component_name');
        $I->wait(1);
    }

    public function tryToDeleteComponent(AcceptanceTester $I)
    {
        $test_component_name = $I->grabCookie('test_component_name');

        $I->wantToTest('delete previously created component');
        $I->waitForElement('table#componentsTable tbody');
        $I->fillField('.search .form-control', $test_component_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#componentsTable").data("bootstrap.table").data[0].name === "'.$test_component_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#componentsTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_component_name.'") {
                    $(\'tr[data-index="\'+k+\'"] .delete-asset:not(.disabled)\').click();
                }
            });
        ');

        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_component_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElement('table#componentsTable tbody');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The component was deleted successfully');
    }
}
