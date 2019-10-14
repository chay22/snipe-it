<?php

class GroupsCest
{
    public function _before(AcceptanceTester $I)
    {
        AcceptanceTester::use_single_login($I);

        $I->loadSessionSnapshot('test_group_name');
    }

    public function tryToLoadGroupsListingPage(AcceptanceTester $I)
    {
        $I->am('logged in user');
        $I->wantTo('ensure that the groups listing page loads without errors');
        $I->lookForwardTo('seeing it load without errors');

        $I->amOnPage(route('groups.index'));
        $I->waitForElement('table#groupsTable tbody');
        $I->seeElement('table#groupsTable thead');
        $I->seeElement('table#groupsTable tbody');
        $I->seeNumberOfElements('table#groupsTable tr', [1, 30]);
        $I->seeInTitle('Groups');
        $I->see('Groups');

        $I->seeInPageSource(route('groups.index'));
        $I->seeElement('table#groupsTable thead');
        $I->seeElement('table#groupsTable tbody');

        $I->amOnPage(route('groups.create'));
        $I->wait(1);
    }

    public function tryToCreateGroupButFailed(AcceptanceTester $I)
    {
        $test_group_name = 'MyTestGroup' . substr(md5(mt_rand()), 0, 10);

        $I->seeCurrentUrlEquals(route('groups.create'));
        $I->seeElement('[name="name"]');

        $I->wantToTest('groups create form prevented from submit if nothing is filled');
        $I->dontSeeElement('.help-block.form-error');
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.help-block.form-error');
        $I->seeElement('.help-block.form-error');

        // Can not create if all required field not filled: Blocked by backend validation
        $I->wantToTest('groups create form failed to create group when fields do not pass validation');
        $I->fillField('[name="name"]', $test_group_name);
        $I->clickWithLeftButton('#create-form [type="submit"]');
        $I->waitForElementVisible('.alert-msg');
        $I->seeNumberOfElements('.alert-msg', [1, 3]);
        $I->seeElement('.alert.alert-danger.fade.in');
    }

    public function tryTocreateNewGroup(AcceptanceTester $I, $cookie_name = 'test_group_name')
    {
        $test_group_name = 'MyTestGroup' . substr(md5(mt_rand()), 0, 10);

        $I->wantToTest('create new group');
        $I->reloadPage();
        $I->waitForText('Create Group');
        $I->dontSeeElement('.help-block.form-error');
        $I->fillField('[name="name"]', $test_group_name);
       
        $I->click('#create-form [type="submit"]');

        $I->waitForElement('table#groupsTable tbody');
        $I->seeInTitle('Groups');
        $I->see('Groups');
        $I->seeCurrentUrlEquals('/groups');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');

        $I->setCookie($cookie_name, $test_group_name);
        $I->saveSessionSnapshot('test_group_name');
    }

    public function tryToEditGroup(AcceptanceTester $I)
    {
        $test_group_name = $I->grabCookie('test_group_name');

        $I->wantToTest('edit previously created group');
        $I->fillField('.search .form-control', $test_group_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#groupsTable").data("bootstrap.table").data[0].name === "'.$test_group_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#groupsTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_group_name.'") {
                    window.location.href = $(\'tr[data-index="\'+k+\'"] [data-original-title="Update"]\').attr("href");
                }
            });
        ');
        $I->waitForText('Update Group');
        $I->seeInTitle('Update Group');
        $I->see('Update Group');

        $old_test_group_name = $test_group_name;
        $test_group_name = 'MyTestGroup' . substr(md5(mt_rand()), 0, 10);

        $I->fillField('[name="name"]', $test_group_name);
        $I->fillField('[name="model_number"]', substr(md5(mt_rand()), 0, 14));
        $I->click('#create-form [type="submit"]');
        $I->waitForElement('table#groupsTable tbody');

        $I->wantTo('ensure previous group name does not exists after update');
        $I->fillField('.search .form-control', $old_test_group_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->see('No matching records found');

        $I->setCookie('test_group_name', $test_group_name);
        $I->saveSessionSnapshot('test_group_name');
        $I->wait(1);
    }

    public function tryToDeleteGroup(AcceptanceTester $I)
    {
        $test_group_name = $I->grabCookie('test_group_name');

        $I->wantToTest('delete previously created group');
        $I->fillField('.search .form-control', $test_group_name);
        $I->waitForElementNotVisible('.fixed-table-loading');
        $I->waitForJS('try { return $("table#groupsTable").data("bootstrap.table").data[0].name === "'.$test_group_name.'"; } catch(e) { return false; }');
        $I->executeJS('
            var bootstrap_table_instance = $("table#groupsTable").data("bootstrap.table");

            $.each(bootstrap_table_instance.data, function (k, v) {
                if (v.name === "'.$test_group_name.'") {
                    $(\'tr[data-index="\'+k+\'"] .delete-asset\').click();
                }
            });
        ');

        $I->waitForElementVisible('#dataConfirmModal');
        $I->waitForElementVisible('#dataConfirmOK');
        $I->see('Are you sure you wish to delete ' . $test_group_name . '?', '#dataConfirmModal');
        $I->click('#dataConfirmOK');
        $I->waitForElementVisible('.alert.alert-success.fade.in');
        $I->seeElement('.alert.alert-success.fade.in');
        $I->see('Success');
        $I->see('The group was deleted successfully');
    }
}
