# RuleFlow Plugin for CakePHP

[![Latest Stable Version](https://img.shields.io/packagist/v/skie/rule-flow.svg)](https://packagist.org/packages/skie/rule-flow)
[![Total Downloads](https://img.shields.io/packagist/dt/skie/rule-flow.svg)](https://packagist.org/packages/skie/rule-flow)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/github/actions/workflow/status/skie/rule-flow/ci.yml?branch=master)](https://github.com/skie/rule-flow/actions)

A CakePHP plugin that seamlessly transforms server-side validation rules into client-side JSON Logic validation, providing automatic form validation without requiring separate client-side validation code.

## Features

- **Automatic Rule Transformation**: Converts CakePHP validation rules to JSON Logic format
- **Real-time Validation**: Client-side validation with immediate feedback
- **Custom Rules Support**: Extend with custom PHP rules and JavaScript functions
- **Dynamic Forms**: Support for dynamically added form fields
- **Zero Configuration**: Works automatically with enhanced FormHelper
- **Cross-Platform Patterns**: Consistent regex validation between PHP and JavaScript

## Quick Start

### Installation

```bash
composer require skie/rule-flow
```

### Basic Usage

**1. Load the plugin in your CakePHP application:**

```php
// In config/bootstrap.php or Application.php
$this->addPlugin('RuleFlow');
```

**2. Use in your controller:**

```php
// Load the component
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('RuleFlow.Rule');
}

// Configure validation rules for forms
public function add()
{
    $article = $this->Articles->newEmptyEntity();
    $this->Rule->configureFormRules($this->Articles);
    $this->set(compact('article'));
}
```

**3. Use enhanced FormHelper in your view:**

```php
// In src/View/AppView.php
public function initialize(): void
{
    parent::initialize();
    $this->loadHelper('RuleFlow.Form');
}
```

**4. Create forms with automatic validation:**

```php
<?= $this->Form->create($article) ?>
<?= $this->Form->control('title') ?>
<?= $this->Form->control('content') ?>
<?= $this->Form->button('Submit') ?>
<?= $this->Form->end() ?>
```

That's it! Your forms now have automatic client-side validation based on your CakePHP validation rules.

## Documentation

### Core Documentation
- **[Plugin Documentation](docs/RuleFlow-Plugin-Documentation.md)** - Complete guide to using the plugin
- **[JsonLogic Operations Reference](docs/JsonLogic-Operations-Reference.md)** - All available JsonLogic operations

### Advanced Guides
- **[Custom Rules Guide](docs/custom-rules-guide.md)** - Creating custom validation rules and functions
- **[Regex Compatibility Guide](docs/regex-compatibility.md)** - Cross-platform regex patterns

## Requirements

- **PHP**: 8.3+
- **CakePHP**: 4.5+
- **Browser**: Modern browsers with ES6+ support

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Links

- [Packagist](https://packagist.org/packages/skie/rule-flow)
- [GitHub Repository](https://github.com/skie/rule-flow)
- [Issues](https://github.com/skie/rule-flow/issues)
- [JSON Logic](http://jsonlogic.com/) - The underlying rule engine
