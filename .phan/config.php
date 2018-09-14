<?php

/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 */
return [

    // Supported values: '7.0', '7.1', '7.2', null.
    // If this is set to null,
    // then Phan assumes the PHP version which is closest to the minor version
    // of the php executable used to execute phan.
    "target_php_version" => '7.2',
    'backward_compatibility_checks ' => false,
    'ignore_undeclared_variables_in_global_scope' => false,
    'minimum_severity'=>\Phan\Issue::SEVERITY_LOW,

    'file_list' => [
        'f_config/config.php',
        'hwe/MYDB.php',
        'hwe/lib.php',
        'hwe/func_auction.php',
        'hwe/func_command.php',
        'hwe/func_converter.php',
        'hwe/func_diplomacy.php',
        'hwe/func_gamerule.php',
        'hwe/func_history.php',
        'hwe/func_legacy.php',
        'hwe/func_map.php',
        'hwe/func_message.php',
        'hwe/func_npc.php',
        'hwe/func_process_chief.php',
        'hwe/func_process_personnel.php',
        'hwe/func_process_sabotage.php',
        'hwe/func_process.php',
        'hwe/func_string.php',
        'hwe/func_template.php',
        'hwe/func_time_event.php',
        'hwe/func_tournament.php',
        'hwe/process_war.php',
        'hwe/func.php'
    ],

    // A list of directories that should be parsed for class and
    // method information. After excluding the directories
    // defined in exclude_analysis_directory_list, the remaining
    // files will be statically analyzed for errors.
    //
    // Thus, both first-party and third-party code being used by
    // your application should be included in this list.
    'directory_list' => [
    	'hwe/d_setting',
    	'hwe/sammo',
    	'd_setting',
        'src/sammo',
        'src/kakao',
        'vendor'
    ],
    'exclude_file_regex' => '/.*\.orig\.php$/',

    // A directory list that defines files that will be excluded
    // from static analysis, but whose class and method
    // information should be included.
    //
    // Generally, you'll want to include the directories for
    // third-party code (such as "vendor/") in this list.
    //
    // n.b.: If you'd like to parse but not analyze 3rd
    //       party code, directories containing that code
    //       should be added to the `directory_list` as
    //       to `exclude_analysis_directory_list`.
    "exclude_analysis_directory_list" => [
        'vendor/'
    ],

    // A list of plugin files to execute.
    // See https://github.com/phan/phan/tree/master/.phan/plugins for even more.
    // (Pass these in as relative paths.
    // The 0.10.2 release will allow passing 'AlwaysReturnPlugin' if referring to a plugin that is bundled with Phan)
    'plugins' => [
        // checks if a function, closure or method unconditionally returns.
        // Checks for syntactically unreachable statements in
        // the global scope or function bodies.
        'UnreachableCodePlugin',
        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
    ],
];