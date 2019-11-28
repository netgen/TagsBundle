<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,

        // Overrides for rules included in PhpCsFixer rule sets
        'concat_space' => ['spacing' => 'one'],
        'method_chaining_indentation' => false,
        'multiline_whitespace_before_semicolons' => false,
        'native_function_invocation' => false,
        'no_superfluous_phpdoc_tags' => false,
        'php_unit_internal_class' => false,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_align' => false,
        'phpdoc_no_alias_tag' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'self_accessor' => false,
        'single_line_comment_style' => false,
        'visibility_required' => ['elements' => ['property', 'method', 'const']],
        'yoda_style' => false,

        // Additional rules
        'date_time_immutable' => true,
        'declare_strict_types' => true,
        'list_syntax' => ['syntax' => 'short'],
        'mb_str_functions' => true,
        'static_lambda' => true,
        'ternary_to_null_coalescing' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['vendor', 'node_modules'])
            ->in(__DIR__)
    )
;
