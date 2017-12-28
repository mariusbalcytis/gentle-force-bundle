<?php
$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('tests')
;
return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        // override some Symfony rules
        'blank_line_before_return' => false,
        'cast_spaces' => false,
        'concat_space' => ['spacing' => 'one'],
        'is_null' => ['use_yoda_style' => false],
        'no_singleline_whitespace_before_semicolons' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'php_unit_fqcn_annotation' => false,
        'pre_increment' => false,
        'yoda_style' => false,
        'blank_line_before_statement' => null,
        'increment_style' => ['style' => 'post'],
        // additional rules
        'array_syntax' => ['syntax' => 'short'],
        'general_phpdoc_annotation_remove' => ['annotations' => ['@author', '@inheritdoc']],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_return' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'strict_comparison' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
