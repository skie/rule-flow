QUnit.module('FormWatcher Core Tests', function (hooks) {
    let testForm;
    let watcher;

    hooks.beforeEach(function () {
        const fixture = document.getElementById('qunit-fixture');
        fixture.innerHTML = `
            < form id = "test-form" >
                < input type = "text" name = "username" value = "" >
                < input type = "email" name = "email" value = "" >
                < input type = "password" name = "password" value = "" >
                < input type = "checkbox" name = "terms" value = "1" >
                < input type = "radio" name = "gender" value = "male" >
                < input type = "radio" name = "gender" value = "female" >
                < select name = "country" >
                    < option value = "" > Select Country < / option >
                    < option value = "us" > United States < / option >
                    < option value = "ca" > Canada < / option >
                <  / select >
                < textarea name = "bio" > < / textarea >

                < !--Collection fields-- >
                < input type = "text" name = "collection[0][name]" value = "" >
                < input type = "email" name = "collection[0][email]" value = "" >
                < input type = "text" name = "collection[1][name]" value = "" >
                < input type = "email" name = "collection[1][email]" value = "" >
            <  / form >
        `;
        testForm = document.getElementById('test-form');
    });

    hooks.afterEach(function () {
        if (watcher) {
            watcher.destroy();
            watcher = null;
        }
    });

    QUnit.test('FormWatcher initialization', function (assert) {
        watcher = new FormWatcher(testForm);

        assert.ok(watcher instanceof FormWatcher, 'FormWatcher instance created');
        assert.equal(watcher.form, testForm, 'Form element stored correctly');
        assert.ok(typeof watcher.data === 'object', 'Data object initialized');
        assert.ok(typeof watcher.validators === 'object', 'Validators object initialized');
        assert.ok(typeof watcher.errors === 'object', 'Errors object initialized');
    });

    QUnit.test('Form data extraction - simple fields', function (assert) {
        watcher = new FormWatcher(testForm);

        testForm.username.value = 'testuser';
        testForm.email.value = 'test@example.com';
        testForm.password.value = 'password123';
        testForm.terms.checked = true;
        testForm.gender[0].checked = true;
        testForm.country.value = 'us';
        testForm.bio.value = 'Test bio';

        watcher.updateFormData();
        const data = watcher.getData();

        assert.equal(data.username, 'testuser', 'Text input value extracted');
        assert.equal(data.email, 'test@example.com', 'Email input value extracted');
        assert.equal(data.password, 'password123', 'Password input value extracted');
        assert.equal(data.terms, '1', 'Checkbox value extracted when checked');
        assert.equal(data.gender, 'male', 'Radio button value extracted');
        assert.equal(data.country, 'us', 'Select value extracted');
        assert.equal(data.bio, 'Test bio', 'Textarea value extracted');
    });

    QUnit.test('Form data extraction - collection fields', function (assert) {
        watcher = new FormWatcher(testForm);

        testForm.querySelector('[name="collection[0][name]"]').value = 'John';
        testForm.querySelector('[name="collection[0][email]"]').value = 'john@example.com';
        testForm.querySelector('[name="collection[1][name]"]').value = 'Jane';
        testForm.querySelector('[name="collection[1][email]"]').value = 'jane@example.com';

        watcher.updateFormData();
        const data = watcher.getData();

        assert.ok(data.collection, 'Collection object exists');
        assert.ok(data.collection[0], 'Collection item 0 exists');
        assert.ok(data.collection[1], 'Collection item 1 exists');
        assert.equal(data.collection[0].name, 'John', 'Collection item 0 name correct');
        assert.equal(data.collection[0].email, 'john@example.com', 'Collection item 0 email correct');
        assert.equal(data.collection[1].name, 'Jane', 'Collection item 1 name correct');
        assert.equal(data.collection[1].email, 'jane@example.com', 'Collection item 1 email correct');
    });

    QUnit.test('Field value retrieval', function (assert) {
        watcher = new FormWatcher(testForm);

        testForm.username.value = 'testuser';
        testForm.querySelector('[name="collection[0][name]"]').value = 'John';

        watcher.updateFormData();

        assert.equal(watcher.getFieldValue('username'), 'testuser', 'Simple field value retrieved');
        assert.equal(watcher.getFieldValue('collection[0][name]'), 'John', 'Collection field value retrieved');
    });

    QUnit.test('Validator registration and execution', function (assert) {
        watcher = new FormWatcher(testForm, { validateOnInit: false });

        let validationCalled = false;
        let validationValue = null;
        let validationData = null;

        watcher.setValidator('username', function (value, data) {
            validationCalled = true;
            validationValue = value;
            validationData = data;
            return value && value.length >= 3 ? true : 'Username must be at least 3 characters';
        });

        testForm.username.value = 'ab';
        watcher.updateFormData();
        const isValid = watcher.validateField('username');

        assert.ok(validationCalled, 'Validator function was called');
        assert.equal(validationValue, 'ab', 'Correct value passed to validator');
        assert.ok(validationData, 'Form data passed to validator');
        assert.equal(isValid, false, 'Validation failed for short username');
        assert.ok(watcher.getErrors().username, 'Error stored for invalid field');

        testForm.username.value = 'validuser';
        watcher.updateFormData();
        const isValidNow = watcher.validateField('username');

        assert.equal(isValidNow, true, 'Validation passed for valid username');
        assert.notOk(watcher.getErrors().username, 'Error cleared for valid field');
    });

    QUnit.test('Validation callbacks', function (assert) {
        let errorCallback = null;
        let successCallback = null;
        let errorField = null;
        let errorMessage = null;
        let successField = null;

        watcher = new FormWatcher(testForm, {
            validateOnInit: false,
            onValidationError: function (fieldName, errors) {
                errorCallback = true;
                errorField = fieldName;
                errorMessage = errors;
            },
            onValidationSuccess: function (fieldName) {
                successCallback = true;
                successField = fieldName;
            }
        });

        watcher.setValidator('username', function (value) {
            return value && value.length >= 3 ? true : 'Too short';
        });

        testForm.username.value = 'ab';
        watcher.updateFormData();
        watcher.validateField('username');

        assert.ok(errorCallback, 'Error callback called');
        assert.equal(errorField, 'username', 'Correct field name in error callback');
        assert.equal(errorMessage, 'Too short', 'Correct error message in callback');

        testForm.username.value = 'validuser';
        watcher.updateFormData();
        watcher.validateField('username');

        assert.ok(successCallback, 'Success callback called');
        assert.equal(successField, 'username', 'Correct field name in success callback');
    });

    QUnit.test('Validate all fields', function (assert) {
        watcher = new FormWatcher(testForm, { validateOnInit: false });

        watcher.setValidator('username', function (value) {
            return value && value.length >= 3 ? true : 'Username too short';
        });

        watcher.setValidator('email', function (value) {
            return value && value.includes('@') ? true : 'Invalid email';
        });

        testForm.username.value = 'ab';
        testForm.email.value = 'invalid';
        watcher.updateFormData();

        const isValid = watcher.validateAll();
        const errors = watcher.getErrors();

        assert.equal(isValid, false, 'validateAll returns false when there are errors');
        assert.ok(errors.username, 'Username error exists');
        assert.ok(errors.email, 'Email error exists');

        testForm.username.value = 'validuser';
        testForm.email.value = 'valid@example.com';
        watcher.updateFormData();

        const isValidNow = watcher.validateAll();
        const errorsNow = watcher.getErrors();

        assert.equal(isValidNow, true, 'validateAll returns true when all fields are valid');
        assert.equal(Object.keys(errorsNow).length, 0, 'No errors when all fields are valid');
    });

    QUnit.test('Path parsing for complex field names', function (assert) {
        watcher = new FormWatcher(testForm);

        const simplePath = watcher.getPathFromKey('username');
        const arrayPath = watcher.getPathFromKey('collection[0][name]');
        const nestedPath = watcher.getPathFromKey('user[profile][settings][theme]');

        assert.deepEqual(simplePath, ['username'], 'Simple field path parsed correctly');
        assert.deepEqual(arrayPath, ['collection', '0', 'name'], 'Array field path parsed correctly');
        assert.deepEqual(nestedPath, ['user', 'profile', 'settings', 'theme'], 'Nested field path parsed correctly');
    });

    QUnit.test('Data structure creation', function (assert) {
        watcher = new FormWatcher(testForm);

        const rawData = {
            'username': 'testuser',
            'collection[0][name]': 'John',
            'collection[0][email]': 'john@example.com',
            'collection[1][name]': 'Jane',
            'user[profile][name]': 'Test User'
        };

        const structured = watcher.structureData(rawData);

        assert.equal(structured.username, 'testuser', 'Simple field structured correctly');
        assert.equal(structured.collection[0].name, 'John', 'Collection item 0 name structured correctly');
        assert.equal(structured.collection[0].email, 'john@example.com', 'Collection item 0 email structured correctly');
        assert.equal(structured.collection[1].name, 'Jane', 'Collection item 1 name structured correctly');
        assert.equal(structured.user.profile.name, 'Test User', 'Nested object structured correctly');
    });

    QUnit.test('Options configuration', function (assert) {
        const customOptions = {
            debounceTime: 500,
            validateOnChange: false,
            validateOnInit: true
        };

        watcher = new FormWatcher(testForm, customOptions);

        assert.equal(watcher.options.debounceTime, 500, 'Custom debounce time set');
        assert.equal(watcher.options.validateOnChange, false, 'Custom validateOnChange set');
        assert.equal(watcher.options.validateOnInit, true, 'Custom validateOnInit set');
    });

    QUnit.test('Destroy functionality', function (assert) {
        watcher = new FormWatcher(testForm);

        assert.ok(watcher.form, 'Form reference exists before destroy');

        watcher.destroy();

        assert.ok(true, 'Destroy method executed without errors');
    });
});
