<?php

class AssetModelsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_assetmodel_name');
    }

    public function tryToLoadAssetModelsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the assetmodels listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage('/models');
        $I->waitForElement('table#modelsTable tbody');
        $I->seeElement('table#modelsTable thead');
        $I->seeElement('table#modelsTable tbody');
        $I->seeNumberOfElements('table#modelsTable tr', [1, 30]);
        $I->seeInTitle('Models');
        $I->see('Models');

        $I->seeInPageSource('/models');
        $I->seeElement('table#modelsTable thead');
        $I->seeElement('table#modelsTable tbody');

        $I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
        $I->wait(1);
    }

    public function tryToCreateAssetModelButFailed(AcceptanceTester $I)
    {
        $test_assetmodel_name = 'MyTestAssetModel' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals('/models/create');
        $I->seeElement('select[name="category_id"]');

        $I->wantToTest('assetmodels create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.help-block.form-error');
        $I->seeElement('.help-block.form-error');

        // Can not create if all required field not filled: Blocked by backend validation
        $I->wantToTest('assetmodels create form failed to create assetmodel when fields do not pass validation');
        $I->fillField('[name="name"]', $test_assetmodel_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.alert-msg');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewAssetModel(AcceptanceTester $I, $cookie_name = 'test_assetmodel_name')
    {
        $test_assetmodel_name = 'MyTestAssetModel' . substr(md5(mt_rand()), 0, 10);

        $model = factory(App\Models\AssetModel::class)->states('mbp-13-model')->make(['name'=>$test_assetmodel_name]);
        $values = [
            'category_id'       => $model->category_id,
            'depreciation_id'   => $model->depreciation_id,
            'eol'               => $model->eol,
            'manufacturer_id'   => $model->manufacturer_id,
            'model_number'      => $model->model_number,
            'name'              => $model->name,
            'notes'             => $model->notes,
        ];



        $I->wantToTest('create new assetmodel');
        $I->reloadPage();
        $I->waitForText('Create Asset Model');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_assetmodel_name);
        $I->fillField('[name="notes"]', $model->notes);
        $I->fillField('[name="model_number"]', $model->model_number);
        $I->fillField('[name="eol"]', $model->eol);
        $I->selectOption('select[name="depreciation_id"]', $model->depreciation_id);
        $I->executeJS('$(\'select[name="depreciation_id"]\').trigger("change");');

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

        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#modelsTable tbody');
        $I->seeInTitle('Models');
        $I->see('Models');
        $I->seeCurrentUrlEquals('/models');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_assetmodel_name);
        $I->saveSessionSnapshot('test_assetmodel_name');
    }

    public function tryToEditAssetModel(AcceptanceTester $I)
    {
        $test_assetmodel_name = $I->grabCookie('test_assetmodel_name');

        $I->wantToTest('edit previously created assetmodel');
        $I->fillField('.search .form-control', $test_assetmodel_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#modelsTable").data("bootstrap.table").data[0].name === "'.$test_assetmodel_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#modelsTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_assetmodel_name.'") {
                    window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
                }
            });
        ');
        $I->waitForText('Update Asset Model');
        $I->seeInTitle('Update Asset Model');
        $I->see('Update Asset Model');

        $old_test_assetmodel_name = $test_assetmodel_name;
        $test_assetmodel_name = 'MyTestAssetModel' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_assetmodel_name);
        $I->fillField('[name="model_number"]', substr(md5(mt_rand()), 0, 14));
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#modelsTable tbody');

        $I->wantTo('ensure previous assetmodel name does not exists after update');
        $I->fillField('.search .form-control', $old_test_assetmodel_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_assetmodel_name', $test_assetmodel_name);
        $I->saveSessionSnapshot('test_assetmodel_name');
        $I->wait(1);
    }

    public function tryToDeleteAssetModel(AcceptanceTester $I)
    {
        $test_assetmodel_name = $I->grabCookie('test_assetmodel_name');

        $I->wantToTest('delete previously created assetmodel');
        $I->fillField('.search .form-control', $test_assetmodel_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#modelsTable").data("bootstrap.table").data[0].name === "'.$test_assetmodel_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#modelsTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_assetmodel_name.'") {
                    $(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
                }
            });
        ');

        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_assetmodel_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('deleted successfully');
    }

    public function tryToBulkEditAssetModels(AcceptanceTester $I)
    {
        $I->amOnPage('/models/create');
        $this->tryTocreateNewAssetModel($I, 'test_assetmodel_name');
        $I->wait(1);

        $I->amOnPage('/models/create');
        $this->tryTocreateNewAssetModel($I, 'test_assetmodel_name2');
        $I->wait(1);

        $I->wantToTest('bulk edit assetmodels');

        $test_assetmodel_name = $I->grabCookie('test_assetmodel_name');
        $test_assetmodel_name2 = $I->grabCookie('test_assetmodel_name2');

        $I->fillField('.search .form-control', 'MyTestAssetModel');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#modelsTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
        $I->click('#bulkEdit');

        $I->waitForText('Bulk Edit');
        $I->waitForElement('select[name="manufacturer_id"]');

        $I->executeJS('$(\'select[name="manufacturer_id"]\').select2("open");');
        $I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;');
        $I->waitForElementVisible('#select2-manufacturer_select_id-results .select2-results__option');

        $I->executeJS('
            var manufacturer_select = $("select[name=\'manufacturer_id\']");
            var first_manufacturer_data = manufacturer_select.data("select2").$results.children(":first").next().data("data");
            var first_manufacturer_option = new Option(first_manufacturer_data.text, first_manufacturer_data.id, true, true);

            manufacturer_select.append(first_manufacturer_option).trigger("change");
        ');
        $I->executeJS('$(\'select[name="manufacturer_id"]\').select2("close");');


        $I->click('form .box-footer [type="submit"]');

        $I->seeElement('table#modelsTable tbody');
        $I->seeInTitle('Models');
        $I->see('Models');
        $I->seeCurrentUrlEquals('/models');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->wait(1);
    }

    public function tryToBulkDeleteAssetModels(AcceptanceTester $I)
    {
        $I->wantToTest('bulk delete assetmodels');

        $test_assetmodel_name = $I->grabCookie('test_assetmodel_name');
        $test_assetmodel_name2 = $I->grabCookie('test_assetmodel_name2');

        $I->fillField('.search .form-control', 'MyTestAssetModel');
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#modelsTable").data("bootstrap.table").data.length > 1; } catch(e) { return false; }');

        $I->checkOption('input[name="btSelectItem"][data-index="0"]');
        $I->checkOption('input[name="btSelectItem"][data-index="1"]');

        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
        $I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

        $I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
        $I->click('#bulkEdit');

        $I->waitForText('Bulk Delete Asset Models');
        $I->see('Bulk Delete Asset Models');
        $I->see('2 asset models');

        $I->click('form #submit-button');

        $I->waitForText('Success');
        $I->seeCurrentUrlEquals('/models');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
    }
}
