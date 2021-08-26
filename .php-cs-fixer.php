<?php
$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('tests')
;
$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        // override some Symfony rules
        'blank_line_before_statement' => false,
        'cast_spaces' => false,
        'concat_space' => ['spacing' => 'one'],
        'no_singleline_whitespace_before_semicolons' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'php_unit_fqcn_annotation' => false,
        'increment_style' => false,
        'yoda_style' => false,
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
