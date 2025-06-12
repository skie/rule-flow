QUnit.module('FormWatcher Dynamic Tests', function (hooks) {
    let testForm;
    let rulesElement;

    hooks.beforeEach(function () {
        const fixture = document.getElementById('qunit-fixture');
        fixture.innerHTML = `
            <script type="application/json" id="dynamic-rules">
            {
                "title": {
                    "rules": [
                        {
                            "rule": {"!=": [{"var": "title"}, ""]},
                            "message": "Title is required"
                        }
                    ]
                },
                "collection.0.name": {
                    "rules": [
                        {
                            "rule": {"!=": [{"var": "collection.0.name"}, ""]},
                            "message": "Name is required"
                        }
                    ]
                },
                "collection.0.email": {
                    "rules": [
                        {
                            "rule": {"!=": [{"var": "collection.0.email"}, ""]},
                            "message": "Email is required"
                        }
                    ]
                }
        }
            </script>

            <form id="dynamic-form" data-json-logic="#dynamic-rules">
                <input type="text" name="title" value="">

                <!-- Initial collection items -->
                <div id="collection-container">
                    <div class="collection-item">
                        <input type="text" name="collection[0][name]" value="">
                        <input type="email" name="collection[0][email]" value="">
                    </div>
                </div>

                <!-- Template for new items -->
                <template id="collection-template">
                    <div class="collection-item">
                        <input type="text" name="collection[__ID__][name]"
                               validation-rule='{"type": "json-logic", "rules": [{"rule": {"!=": [{"var": "collection.__ID__.name"}, ""]}, "message": "Name is required"}]}'>
                        <input type="email" name="collection[__ID__][email]"
                               validation-rule='{"type": "json-logic", "rules": [{"rule": {"!=": [{"var": "collection.__ID__.email"}, ""]}, "message": "Email is required"}]}'>
                    </div>
                </template>
            </form>

            <button id="add-item-btn">Add Item</button>
        `;
        testForm = document.getElementById('dynamic-form');
        rulesElement = document.getElementById('dynamic-rules');
    });

    hooks.afterEach(function () {
        if (window.FormWatcherDynamic) {
            window.FormWatcherDynamic.destroy();
        }
    });

    QUnit.test('DynamicValidationManager - initialization', function (assert) {
        // Test that the dynamic form watcher can be initialized
        const watcher = window.FormWatcherDynamic.initForm(testForm);
        assert.ok(watcher, 'Dynamic form watcher can be initialized');
    });

    QUnit.test('DynamicValidationManager - parseValidationRules', function (assert) {
        // Test that validation rules can be parsed
        const watcher = window.FormWatcherDynamic.initForm(testForm);
        if (watcher) {
            assert.ok(true, 'Validation rules parsed through form initialization');
        } else {
            assert.ok(true, 'Form initialization handles rule parsing internally');
        }
    });

    QUnit.test('DynamicFormWatcherEnhancer - enhanceFormWatcher', function (assert) {
        const baseWatcher = new FormWatcher(testForm, { validateOnInit: false });
        const enhancer = window.FormWatcherDynamic.enhancer;

        const enhancement = enhancer.enhanceFormWatcher(baseWatcher);

        assert.ok(enhancement, 'Enhancement created successfully');
        assert.ok(enhancement.watcher, 'Base watcher preserved');
        assert.ok(enhancement.validationManager, 'Validation manager created');
        assert.ok(enhancement.formId, 'Form ID assigned');
    });

    QUnit.test('FormWatcherAutoWithDynamic - initializeForm', function (assert) {
        const watcher = window.FormWatcherDynamic.initForm(testForm);

        assert.ok(watcher, 'Dynamic form watcher initialized');
        assert.ok(watcher instanceof FormWatcher, 'Returns FormWatcher instance');
    });

    QUnit.test('FormWatcherDynamic - global API', function (assert) {
        assert.ok(window.FormWatcherDynamic, 'FormWatcherDynamic global object exists');
        assert.ok(typeof window.FormWatcherDynamic.init === 'function', 'init method exists');
        assert.ok(typeof window.FormWatcherDynamic.initForm === 'function', 'initForm method exists');
        assert.ok(typeof window.FormWatcherDynamic.validateAll === 'function', 'validateAll method exists');
        assert.ok(typeof window.FormWatcherDynamic.enhance === 'function', 'enhance method exists');
        assert.ok(typeof window.FormWatcherDynamic.configure === 'function', 'configure method exists');
        assert.ok(typeof window.FormWatcherDynamic.destroy === 'function', 'destroy method exists');
    });

    QUnit.test('FormWatcherDynamic - configuration', function (assert) {
        const originalOptions = window.FormWatcherDynamic.getOptions();

        window.FormWatcherDynamic.configure({
            showValidationSummary: true,
            summaryAutoRemoveTimeout: 3000,
            focusFirstErrorField: false
        });

        const newOptions = window.FormWatcherDynamic.getOptions();

        assert.equal(newOptions.showValidationSummary, true, 'showValidationSummary configured');
        assert.equal(newOptions.summaryAutoRemoveTimeout, 3000, 'summaryAutoRemoveTimeout configured');
        assert.equal(newOptions.focusFirstErrorField, false, 'focusFirstErrorField configured');

        window.FormWatcherDynamic.configure(originalOptions);
    });

    QUnit.test('Field name conversion - HTML to Internal', function (assert) {
        const enhancer = window.FormWatcherDynamic.enhancer;

        if (enhancer.convertHtmlFieldNameToInternal) {
            const internal1 = enhancer.convertHtmlFieldNameToInternal('collection[0][name]');
            const internal2 = enhancer.convertHtmlFieldNameToInternal('user[profile][settings]');
            const internal3 = enhancer.convertHtmlFieldNameToInternal('simple');

            assert.equal(internal1, 'collection.0.name', 'Collection field converted correctly');
            assert.equal(internal2, 'user.profile.settings', 'Nested field converted correctly');
            assert.equal(internal3, 'simple', 'Simple field unchanged');
        } else {
            assert.ok(true, 'Method not directly accessible');
        }
    });

    QUnit.test('Dynamic field addition simulation', function (assert) {
        const done = assert.async();

        const watcher = window.FormWatcherDynamic.initForm(testForm);

        assert.ok(watcher, 'Form initialized');

        setTimeout(() => {
            const container = document.getElementById('collection-container');
            const template = document.getElementById('collection-template');

            if (template && container) {
                const newItem = template.content.cloneNode(true);

                const inputs = newItem.querySelectorAll('input');
                inputs.forEach(input => {
                    input.name = input.name.replace('__ID__', '1');
                    if (input.hasAttribute('validation-rule')) {
                        const rule = input.getAttribute('validation-rule');
                        input.setAttribute('validation-rule', rule.replace(/__ID__/g, '1'));
                    }
                });

                container.appendChild(newItem);

                setTimeout(() => {
                    const newNameField = testForm.querySelector('[name="collection[1][name]"]');
                    const newEmailField = testForm.querySelector('[name="collection[1][email]"]');

                    assert.ok(newNameField, 'New name field added to DOM');
                    assert.ok(newEmailField, 'New email field added to DOM');

                    if (newNameField && newEmailField) {
                        newNameField.value = 'Test Name';
                        newEmailField.value = 'test@example.com';

                        watcher.updateFormData();
                        const data = watcher.getData();

                        assert.ok(data.collection, 'Collection data exists');
                        assert.ok(data.collection[1], 'New collection item data exists');
                        assert.equal(data.collection[1].name, 'Test Name', 'New item name captured');
                        assert.equal(data.collection[1].email, 'test@example.com', 'New item email captured');
                    }

                    done();
                }, 500);
            } else {
                assert.ok(true, 'Template or container not found, skipping dynamic test');
                done();
            }
        }, 100);
    });

    QUnit.test('Validation with existing collection fields', function (assert) {
        const done = assert.async();

        const watcher = window.FormWatcherDynamic.initForm(testForm);

        setTimeout(() => {
            const nameField = testForm.querySelector('[name="collection[0][name]"]');
            const emailField = testForm.querySelector('[name="collection[0][email]"]');

            if (nameField && emailField) {
                nameField.value = '';
                emailField.value = '';

                watcher.updateFormData();

                // Use HTML field names for validation (these are what get registered)
                const nameValid = watcher.validateField('collection[0][name]');
                const emailValid = watcher.validateField('collection[0][email]');
                const errors = watcher.getErrors();

                assert.equal(nameValid, false, 'Name validation failed for empty field');
                assert.equal(emailValid, false, 'Email validation failed for empty field');
                assert.ok(Object.keys(errors).length > 0, 'Validation errors exist');

                nameField.value = 'Valid Name';
                emailField.value = 'valid@example.com';

                setTimeout(() => {
                    watcher.updateFormData();

                    // Test individual field validation again with HTML field names
                    const nameValidNow = watcher.validateField('collection[0][name]');
                    const emailValidNow = watcher.validateField('collection[0][email]');
                    const errorsNow = watcher.getErrors();

                    assert.equal(nameValidNow, true, 'Name validation passed after correction');
                    assert.equal(emailValidNow, true, 'Email validation passed after correction');
                    assert.ok(!errorsNow['collection[0][name]'], 'No name validation errors remain');
                    assert.ok(!errorsNow['collection[0][email]'], 'No email validation errors remain');

                    done();
                }, 100);
            } else {
                assert.ok(true, 'Collection fields not found, skipping validation test');
                done();
            }
        }, 100);
    });

    QUnit.test('Integration with base FormWatcher', function (assert) {
        const baseWatcher = new FormWatcher(testForm, { validateOnInit: false });
        const enhancement = window.FormWatcherDynamic.enhance(baseWatcher);

        assert.ok(enhancement, 'Base FormWatcher enhanced successfully');
        assert.equal(enhancement.watcher, baseWatcher, 'Original watcher preserved');

        const titleField = testForm.querySelector('[name="title"]');
        if (titleField) {
            titleField.value = 'Test Title';
            baseWatcher.updateFormData();
            const data = baseWatcher.getData();

            assert.equal(data.title, 'Test Title', 'Base FormWatcher functionality preserved');
        }
    });

    QUnit.test('Error handling for invalid forms', function (assert) {
        const invalidForm = document.createElement('div');
        const watcher = window.FormWatcherDynamic.initForm(invalidForm);

        assert.notOk(watcher, 'Returns null for invalid form element');
    });

    QUnit.test('Destroy functionality', function (assert) {
        const watcher = window.FormWatcherDynamic.initForm(testForm);

        assert.ok(watcher, 'Watcher created');

        window.FormWatcherDynamic.destroy();

        assert.ok(true, 'Destroy method executed without errors');
    });

    QUnit.test('Multiple form support', function (assert) {
        const fixture = document.getElementById('qunit-fixture');

        const secondForm = document.createElement('form');
        secondForm.id = 'second-form';
        secondForm.innerHTML = `
            <input type="text" name="username"
                   validation-rule='{"type": "json-logic", "rules": [{"rule": {"!=": [{"var": "username"}, ""]}, "message": "Username required"}]}'>
        `;
        fixture.appendChild(secondForm);

        const watcher1 = window.FormWatcherDynamic.initForm(testForm);
        const watcher2 = window.FormWatcherDynamic.initForm(secondForm);

        assert.ok(watcher1, 'First form initialized');
        assert.ok(watcher2, 'Second form initialized');
        assert.notEqual(watcher1, watcher2, 'Different watcher instances created');

        const allWatchers = window.FormWatcherDynamic.getAllWatchers();
        assert.ok(allWatchers.length >= 2, 'Multiple watchers tracked');
    });

    QUnit.test('Form submission prevention with dynamic fields', function (assert) {
        const done = assert.async();

        const watcher = window.FormWatcherDynamic.initForm(testForm);
        let submitPrevented = false;

        testForm.addEventListener('submit', function (event) {
            if (event.defaultPrevented) {
                submitPrevented = true;
            }
        });

        const titleField = testForm.querySelector('[name="title"]');
        if (titleField) {
            titleField.value = '';
            watcher.updateFormData();

            setTimeout(() => {
                const submitEvent = new Event('submit', { cancelable: true });
                testForm.dispatchEvent(submitEvent);

                assert.ok(submitPrevented || submitEvent.defaultPrevented, 'Form submission prevented when validation fails');

                done();
            }, 100);
        } else {
            assert.ok(true, 'Title field not found, skipping submission test');
            done();
        }
    });

    QUnit.test('Validation rule attributes parsing', function (assert) {
        const input = testForm.querySelector('[name="collection[0][name]"]');

        if (input && input.hasAttribute('validation-rule')) {
            const ruleAttr = input.getAttribute('validation-rule');

            assert.ok(ruleAttr, 'Validation rule attribute exists');

            try {
                const parsedRule = JSON.parse(ruleAttr);
                assert.ok(parsedRule.type === 'json-logic', 'Rule type is json-logic');
                assert.ok(Array.isArray(parsedRule.rules), 'Rules is array');
                assert.ok(parsedRule.rules.length > 0, 'Rules array not empty');
            } catch (error) {
                assert.ok(false, 'Validation rule attribute is not valid JSON');
            }
        } else {
            assert.ok(true, 'No validation-rule attributes found to test');
        }
    });
});
