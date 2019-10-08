<?php

class AssetsCest
{
    public function _before(AcceptanceTester $I)
    {
    	AcceptanceTester::use_single_login($I);

    	$I->loadSessionSnapshot('test_assets_tag');
    }

    public function tryToLoadAssetsListingPage(AcceptanceTester $I)
    {
		$I->am('logged in user');
		$I->wantTo('ensure that the assets listing page loads without errors');
		$I->lookForwardTo('seeing it load without errors');

		$I->amOnPage('/hardware');
		$I->wait(3);
		$I->waitForElement('table#assetsListingTable', 3); // secs
		$I->seeElement('table#assetsListingTable thead');
		$I->seeElement('table#assetsListingTable tbody');
		$I->seeNumberOfElements('table#assetsListingTable tr', [1, 30]);
		$I->seeInTitle('Assets');
		$I->see('Assets');

		$I->seeInPageSource('/hardware');
		$I->seeElement('table#assetsListingTable thead');
		$I->seeElement('table#assetsListingTable tbody');

		$I->clickWithLeftButton('.content-header .pull-right .btn.pull-right');
		$I->wait(3);
    }

    public function tryToCreateAssetButFailed(AcceptanceTester $I)
    {
    	$test_assets_tag = 'MyTestAssets' . substr(md5(mt_rand()), 0, 10);

		$I->seeCurrentUrlEquals('/hardware/create');
		$I->seeElement('select[name="status_id"]');

		$I->wantToTest('assets create form prevented from submit if nothing is filled');
		$I->dontSeeElement('.help-block.form-error');
		$I->clickWithLeftButton('#create-form [type="submit"]');
		$I->wait(1);
		$I->seeElement('.help-block.form-error');

		// Can not create if all required field not filled: Blocked by backend validation
		$I->wantToTest('assets create form failed to create asset when fields do not pass validation');
		$I->fillField('[name^="asset_tags"]', $test_assets_tag);
		$I->clickWithLeftButton('#create-form [type="submit"]');
		$I->wait(1);
		$I->seeNumberOfElements('.help-block.form-error', [1, 3]);
    }

    public function tryTocreateNewAsset(AcceptanceTester $I, $cookie_name = 'test_assets_tag')
    {
    	$test_assets_tag = 'MyTestAssets' . substr(md5(mt_rand()), 0, 10);

		$I->wantToTest('create new asset');
		$I->reloadPage();
		$I->wait(3);
		$I->dontSeeElement('.help-block.form-error');
		$I->fillField('[name^="asset_tags"]', $test_assets_tag);
	


		$I->executeJS('$(\'select[name="model_id"]\').select2("open");');
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 2);
		$I->wait(1);

		$I->executeJS('
			var model_id_select = $("select[name=\'model_id\']");
			var first_model_id_data = model_id_select.data("select2").$results.children(":first").data("data");
			var first_model_id_option = new Option(first_model_id_data.text, first_model_id_data.id, true, true);

			model_id_select.append(first_model_id_option).trigger("change");
		');
		$I->executeJS('$(\'select[name="model_id"]\').select2("close");');


		$I->executeJS('$(\'select[name="status_id"]\').select2("open");');
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 2);
		$I->wait(1);
		$I->executeJS('
			var status_id_select = $("select[name=\'status_id\']");
			var first_status_id_data = status_id_select.data("select2").$results.children(":first").data("data");
			var first_status_id_option = new Option(first_status_id_data.text, first_status_id_data.id, true, true);

			status_id_select.data("select2").$results.children().each(function () {
			  var data= $(this).data("data")

			  if (data.id == 1) {
			    var first_status_id_option = new Option(data.text, data.id, true, true);
			    status_id_select.append(first_status_id_option).trigger("change");
			  }
			})
		');
		$I->executeJS('$(\'select[name="status_id"]\').select2("close");');


		$I->click('#create-form [type="submit"]');

		$I->wait(3);
		$I->seeInTitle('Assets');
		$I->see('Assets');
		$I->seeCurrentUrlEquals('/hardware');
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');

		$I->setCookie($cookie_name, $test_assets_tag);
		$I->saveSessionSnapshot('test_assets_tag');
    }

    public function tryToEditAsset(AcceptanceTester $I)
    {
    	$test_assets_tag = $I->grabCookie('test_assets_tag');

		$I->wantToTest('edit previously created asset');
		$I->fillField('.search .form-control', $test_assets_tag);
		$I->wait(1);
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->executeJS('
			var bootstrap_table_instance = $("table#assetsListingTable").data("bootstrap.table");

			$.each(bootstrap_table_instance.data, function (k, v) {
				if (v.asset_tag === "'.$test_assets_tag.'") {
					window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
				}
			});
		');
		$I->wait(3);
		$I->seeInTitle('Asset Update');
		$I->see('Asset Update');

		$old_test_assets_tag = $test_assets_tag;
		$test_assets_tag = 'MyTestAssets' . substr(md5(mt_rand()), 0, 10);

		$I->fillField('[name^="asset_tags"]', $test_assets_tag);

		$I->executeJS('$(\'select[name="model_id"]\').select2("open");');
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 2);
		$I->wait(1);

		$I->executeJS('
			var model_id_select = $("select[name=\'model_id\']");
			var first_model_id_data = model_id_select.data("select2").$results.children(":first").next().data("data");
			var first_model_id_option = new Option(first_model_id_data.text, first_model_id_data.id, true, true);

			model_id_select.append(first_model_id_option).trigger("change");
		');
		$I->executeJS('$(\'select[name="model_id"]\').select2("close");');

		$I->click('#create-form [type="submit"]');
		$I->wait(3);

		$I->wantTo('ensure previous asset name does not exists after update');
		$I->see($test_assets_tag);
		$I->dontSee($old_test_assets_tag);
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Asset updated successfully');

		$I->setCookie('test_assets_tag', $test_assets_tag);
		$I->saveSessionSnapshot('test_assets_tag');
		$I->wait(1);
    }

    public function tryToDeleteAsset(AcceptanceTester $I)
    {
    	$test_assets_tag = $I->grabCookie('test_assets_tag');

		$I->wantToTest('delete previously created asset');
		$I->amOnPage('/hardware');
		$I->wait(3);
		$I->waitForElement('table#assetsListingTable', 3); // secs
		$I->seeElement('table#assetsListingTable thead');

		$I->fillField('.search .form-control', $test_assets_tag);
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->wait(1);
		$I->reloadPage();
		$I->wait(3);
		$I->executeJS('
			var bootstrap_table_instance = $("table#assetsListingTable").data("bootstrap.table");

			$.each(bootstrap_table_instance.data, function (k, v) {
				if (v.asset_tag === "'.$test_assets_tag.'") {
					$(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
				}
			});
		');
		$I->wait(3);
		$I->see('Are you sure you wish to delete ?', '#dataConfirmModal');
		$I->click('#dataConfirmOK');
		$I->wait(3);
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');
		$I->see('The asset was deleted successfully');
    }

    public function tryToBulkEditAssets(AcceptanceTester $I)
    {
      	$I->amOnPage('/hardware/create');
    	$this->tryTocreateNewAsset($I, 'test_assets_tag');
    	$I->wait(2);

    	$I->amOnPage('/hardware/create');
    	$this->tryTocreateNewAsset($I, 'test_assets_tag2');
    	$I->wait(2);

    	$I->wantToTest('bulk edit assets');

    	$test_assets_tag = $I->grabCookie('test_assets_tag');
    	$test_assets_tag2 = $I->grabCookie('test_assets_tag2');

		$I->fillField('.search .form-control', 'MyTestAssets');
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->wait(1);

		$I->reloadPage();
		$I->wait(3);

		$I->checkOption('input[name="btSelectItem"][data-index="0"]');
		$I->checkOption('input[name="btSelectItem"][data-index="1"]');

    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

    	$I->executeJS("$('select[name=\"bulk_actions\"]').val('edit').trigger('change');");
    	$I->click('#bulkEdit');

    	$I->wait(3);

    	$I->see('Asset Update');
    	$I->see('2 assets');

    	$I->fillField('[name="warranty_months"]', 24);
    	$I->click('form .box-footer [type="submit"]');

    	$I->wait(3);
		$I->seeInTitle('Assets');
		$I->see('Assets');
		$I->seeCurrentUrlEquals('/hardware');
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');

		$I->wait(3);
    }

    public function tryToBulkDeleteAssets(AcceptanceTester $I)
    {
    	$I->wantToTest('bulk delete assets');

    	$test_assets_tag = $I->grabCookie('test_assets_tag');
    	$test_assets_tag2 = $I->grabCookie('test_assets_tag2');

		$I->fillField('.search .form-control', 'MyTestAssets');
		$I->waitForElementNotVisible('.fixed-table-loading');
		$I->wait(1);

		$I->checkOption('input[name="btSelectItem"][data-index="0"]');
		$I->checkOption('input[name="btSelectItem"][data-index="1"]');

    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="0"]');
    	$I->seeCheckboxIsChecked('input[name="btSelectItem"][data-index="1"]');

    	$I->executeJS("$('select[name=\"bulk_actions\"]').val('delete').trigger('change');");
    	$I->click('#bulkEdit');

    	$I->wait(3);

    	$I->see('Confirm Bulk Delete Assets');
    	$I->see('2 assets');

    	$I->click('form #submit-button');

		$I->seeCurrentUrlEquals('/hardware');
		$I->seeElement('.alert.alert-success.fade.in');
		$I->see('Success');
    }
}
