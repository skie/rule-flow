QUnit.module('FormWatcher Auto Tests', function (hooks) {
    let testForm;
    let rulesElement;

    hooks.beforeEach(function () {
        const fixture = document.getElementById('qunit-fixture');
        fixture.innerHTML = `
            <script type="application/json" id="test-rules">
            {
                "username": {
                    "rules": [
                        {
                            "rule": {"!=": [{"var": "username"}, ""]},
                            "message": "Username is required"
                        }
                    ]
                },
                "email": {
                    "rules": [
                        {
                            "rule": {"!=": [{"var": "email"}, ""]},
                            "message": "Email is required"
                        }
                    ]
                }
        }
            </script>

            <form id="test-form" data-json-logic="#test-rules">
                <input type="text" name="username" value="">
                <input type="email" name="email" value="">
                <input type="password" name="password" value="">

                <!-- Fields with validation-rule attributes -->
                <input type="text" name="title"
                       validation-rule='{"type": "json-logic", "rules": [{"rule": {"!=": [{"var": "title"}, ""]}, "message": "Title is required"}]}'>

                <!-- Collection fields -->
                <input type="text" name="collection[0][name]"
                       validation-rule='{"type": "json-logic", "rules": [{"rule": {"!=": [{"var": "collection.0.name"}, ""]}, "message": "Name is required"}]}'>
                <input type="email" name="collection[0][email]"
                       validation-rule='{"type": "json-logic", "rules": [{"rule": {"!=": [{"var": "collection.0.email"}, ""]}, "message": "Email is required"}]}'>
            </form>
        `;
        testForm = document.getElementById('test-form');
        rulesElement = document.getElementById('test-rules');
    });

    hooks.afterEach(function () {
        if (window.FormWatcherAuto) {
            window.FormWatcherAuto.initializer.watchers.clear();
            window.FormWatcherAuto.initializer.validators.clear();
        }
    });

    QUnit.test('ValidationRuleLoader - loadRulesFromElement', function (assert) {
        const loader = new window.FormWatcherAuto.ValidationRuleLoader();
        const rules = loader.loadRulesFromElement('test-rules');

        assert.ok(rules, 'Rules loaded successfully');
        assert.ok(rules.username, 'Username rules exist');
        assert.ok(rules.email, 'Email rules exist');
        assert.ok(Array.isArray(rules.username.rules), 'Username rules is array');
        assert.equal(rules.username.rules.length, 1, 'Username has 1 rule');
        assert.equal(rules.email.rules.length, 1, 'Email has 1 rule');
    });

    QUnit.test('ValidationRuleLoader - parseFormWithDataJsonLogic', function (assert) {
        const loader = new window.FormWatcherAuto.ValidationRuleLoader();
        const extractedRules = loader.parseFormWithDataJsonLogic(testForm);

        assert.ok(extractedRules, 'Rules extracted from form');
        assert.ok(extractedRules.username, 'Username rules extracted');
        assert.ok(extractedRules.email, 'Email rules extracted');
        assert.ok(Array.isArray(extractedRules.username), 'Username rules is array');
        assert.equal(extractedRules.username.length, 1, 'Username has 1 rule');
    });

    QUnit.test('ValidationRuleLoader - parseValidationAttributes', function (assert) {
        const loader = new window.FormWatcherAuto.ValidationRuleLoader();
        const extractedRules = loader.parseValidationAttributes(testForm);

        assert.ok(extractedRules, 'Rules extracted from attributes');
        assert.ok(extractedRules.title, 'Title rules extracted');
        assert.ok(extractedRules['collection[0][name]'], 'Collection name rules extracted');
        assert.ok(extractedRules['collection[0][email]'], 'Collection email rules extracted');
    });

    QUnit.test('ValidationRuleLoader - parseAllValidationRules', function (assert) {
        const loader = new window.FormWatcherAuto.ValidationRuleLoader();
        const allRules = loader.parseAllValidationRules(testForm);

        assert.ok(allRules, 'All rules extracted');
        assert.ok(allRules.username, 'Data-json-logic rules included');
        assert.ok(allRules.title, 'Validation-rule attributes included');
        assert.ok(allRules['collection[0][name]'], 'Collection rules included');
    });

    QUnit.test('ValidationRuleLoader - createValidator', function (assert) {
        const loader = new window.FormWatcherAuto.ValidationRuleLoader();

        loader.addRule('username', {"!=": [{"var": "username"}, ""]}, 'Username is required');

        const validator = loader.createValidator('username');

        assert.ok(typeof validator === 'function', 'Validator function created');

        const result1 = validator('', { username: '' });
        assert.ok(Array.isArray(result1), 'Returns array of errors for invalid input');
        assert.ok(result1.length > 0, 'Has validation errors');
        assert.equal(result1[0], 'Username is required', 'Correct error message');

        const result2 = validator('validuser', { username: 'validuser' });
        assert.equal(result2, true, 'Returns true for valid input');
    });

    QUnit.test('ValidationRuleLoader - createValidatorFromRules', function (assert) {
        const loader = new window.FormWatcherAuto.ValidationRuleLoader();

        const rules = [
            {
                "rule": {"!=": [{"var": "username"}, ""]},
                "message": "Username is required"
        }
        ];

        const validator = loader.createValidatorFromRules('username', rules);

        assert.ok(typeof validator === 'function', 'Validator function created from rules');

        const result1 = validator('', { username: '' });
        assert.equal(result1, 'Username is required', 'Returns error message for empty input');

        const result2 = validator('validuser', { username: 'validuser' });
        assert.equal(result2, true, 'Returns true for valid input');
    });

    QUnit.test('FormWatcherAutoInitializer - initializeForm', function (assert) {
        const initializer = new window.FormWatcherAuto.initializer.constructor();
        const watcher = initializer.initializeForm(testForm);

        assert.ok(watcher, 'FormWatcher instance created');
        assert.ok(watcher instanceof FormWatcher, 'Correct instance type');
        assert.ok(initializer.watchers.size > 0, 'Watcher stored in initializer');
        assert.ok(initializer.validators.size > 0, 'Validator stored in initializer');
    });

    QUnit.test('FormWatcherAutoInitializer - field name conversion', function (assert) {
        const initializer = new window.FormWatcherAuto.initializer.constructor();

        const htmlName1 = initializer.convertInternalFieldNameToHtml('username');
        const htmlName2 = initializer.convertInternalFieldNameToHtml('collection.0.name');
        const htmlName3 = initializer.convertInternalFieldNameToHtml('user.profile.settings');

        assert.equal(htmlName1, 'username', 'Simple field name unchanged');
        assert.equal(htmlName2, 'collection[0][name]', 'Collection field name converted correctly');
        assert.equal(htmlName3, 'user[profile][settings]', 'Nested field name converted correctly');
    });

    QUnit.test('FormWatcherAuto - global API', function (assert) {
        assert.ok(window.FormWatcherAuto, 'FormWatcherAuto global object exists');
        assert.ok(typeof window.FormWatcherAuto.init === 'function', 'init method exists');
        assert.ok(typeof window.FormWatcherAuto.initForm === 'function', 'initForm method exists');
        assert.ok(typeof window.FormWatcherAuto.validateAll === 'function', 'validateAll method exists');
        assert.ok(typeof window.FormWatcherAuto.getWatcher === 'function', 'getWatcher method exists');
        assert.ok(typeof window.FormWatcherAuto.analyze === 'function', 'analyze method exists');
        assert.ok(typeof window.FormWatcherAuto.configure === 'function', 'configure method exists');
    });

    QUnit.test('FormWatcherAuto - configuration', function (assert) {
        const originalOptions = window.FormWatcherAuto.getOptions();

        window.FormWatcherAuto.configure({
            showValidationSummary: true,
            summaryAutoRemoveTimeout: 5000,
            focusFirstErrorField: false
        });

        const newOptions = window.FormWatcherAuto.getOptions();

        assert.equal(newOptions.showValidationSummary, true, 'showValidationSummary configured');
        assert.equal(newOptions.summaryAutoRemoveTimeout, 5000, 'summaryAutoRemoveTimeout configured');
        assert.equal(newOptions.focusFirstErrorField, false, 'focusFirstErrorField configured');

        window.FormWatcherAuto.configure(originalOptions);
    });

    QUnit.test('RuleAnalyzer - analyzeForm', function (assert) {
        const analysis = window.FormWatcherAuto.analyze(testForm);

        assert.ok(analysis, 'Form analysis returned');
        assert.ok(analysis.formId, 'Form ID identified');
        assert.ok(analysis.fieldCount > 0, 'Fields counted');
        assert.ok(analysis.hasValidation, 'Validation detected');
        assert.ok(analysis.hasDataJsonLogic, 'Data-json-logic attribute detected');
        assert.ok(analysis.hasValidationRuleAttributes, 'Validation-rule attributes detected');
        assert.ok(Array.isArray(analysis.fields), 'Fields array provided');
        assert.ok(typeof analysis.rules === 'object', 'Rules object provided');
    });

    QUnit.test('RuleAnalyzer - testRule', function (assert) {
        const rule = {"!=": [{"var": "username"}, ""]};
        const testData = [
            { username: '' },
            { username: 'test' }
        ];

        const results = window.FormWatcherAuto.testRule(rule, testData);

        assert.ok(Array.isArray(results), 'Results is array');
        assert.equal(results.length, 2, 'Two results returned');
        assert.equal(results[0].valid, false, 'First test case invalid');
        assert.equal(results[1].valid, true, 'Second test case valid');
    });

    QUnit.test('JSON Logic operations - basic functionality', function (assert) {
        assert.ok(typeof jsonLogic !== 'undefined', 'JSON Logic library available');

        const varResult = jsonLogic.apply({"var": "username"}, { username: 'testuser' });
        assert.equal(varResult, 'testuser', 'Variable operation works');

        const notEqualResult1 = jsonLogic.apply({"!=": [{"var": "username"}, ""]}, { username: '' });
        assert.equal(notEqualResult1, false, 'Not equal operation works with empty string');

        const notEqualResult2 = jsonLogic.apply({"!=": [{"var": "username"}, ""]}, { username: 'test' });
        assert.equal(notEqualResult2, true, 'Not equal operation works with non-empty string');
    });

    QUnit.test('Integration - form validation with JSON Logic rules', function (assert) {
        const done = assert.async();

        const watcher = window.FormWatcherAuto.initForm(testForm);

        assert.ok(watcher, 'Form initialized with auto watcher');

        // Test individual field validation instead of validateAll
        testForm.username.value = '';
        testForm.email.value = '';

        setTimeout(() => {
            watcher.updateFormData();

            // Test individual field validation
            const usernameValid = watcher.validateField('username');
            const emailValid = watcher.validateField('email');
            const errors = watcher.getErrors();

            assert.equal(usernameValid, false, 'Username validation failed as expected');
            assert.equal(emailValid, false, 'Email validation failed as expected');
            assert.ok(errors.username, 'Username validation error exists');
            assert.ok(errors.email, 'Email validation error exists');

            testForm.username.value = 'validuser';
            testForm.email.value = 'valid@example.com';

            setTimeout(() => {
                watcher.updateFormData();

                // Test individual field validation again
                const usernameValidNow = watcher.validateField('username');
                const emailValidNow = watcher.validateField('email');
                const errorsNow = watcher.getErrors();

                assert.equal(usernameValidNow, true, 'Username validation passed after correction');
                assert.equal(emailValidNow, true, 'Email validation passed after correction');
                assert.ok(!errorsNow.username, 'No username validation errors remain');
                assert.ok(!errorsNow.email, 'No email validation errors remain');

                done();
            }, 100);
        }, 100);
    });

    QUnit.test('Form submission prevention', function (assert) {
        const done = assert.async();

        const watcher = window.FormWatcherAuto.initForm(testForm);
        let submitPrevented = false;

        testForm.addEventListener('submit', function (event) {
            if (event.defaultPrevented) {
                submitPrevented = true;
            }
        });

        testForm.username.value = '';
        watcher.updateFormData();

        setTimeout(() => {
            const submitEvent = new Event('submit', { cancelable: true });
            testForm.dispatchEvent(submitEvent);

            assert.ok(submitPrevented || submitEvent.defaultPrevented, 'Form submission prevented when validation fails');

            done();
        }, 100);
    });
});
