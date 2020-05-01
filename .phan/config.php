<?php

/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command-line arguments will be applied
 * after this file is read.
 */
return [
    // Supported values: `'5.6'`, `'7.0'`, `'7.1'`, `'7.2'`, `'7.3'`,
    // `'7.4'`, `null`.
    // If this is set to `null`,
    // then Phan assumes the PHP version which is closest to the minor version
    // of the php executable used to execute Phan.
    //
    // Note that the **only** effect of choosing `'5.6'` is to infer
    // that functions removed in php 7.0 exist.
    // (See `backward_compatibility_checks` for additional options)
    "target_php_version" => '7.4',
    'backward_compatibility_checks ' => false,
    'ignore_undeclared_variables_in_global_scope' => false,
    'minimum_severity'=>\Phan\Issue::SEVERITY_LOW,

    'file_list' => [
        'f_config/config.php',
        'hwe/a_bestGeneral.php',
        'hwe/a_emperior.php',
        'hwe/a_emperior2.php',
        'hwe/a_emperior_detail.php',
        'hwe/a_genList.php',
        'hwe/a_hallOfFame.php',
        'hwe/a_history.php',
        'hwe/a_kingdomList.php',
        'hwe/a_npcList.php',
        'hwe/a_status.php',
        'hwe/a_traffic.php',
        'hwe/a_vote.php',
        'hwe/battle_simulator.php',
        'hwe/b_auction.php',
        'hwe/b_battleCenter.php',
        'hwe/b_betting.php',
        'hwe/b_chiefcenter.php',
        'hwe/b_currentCity.php',
        'hwe/b_dipcenter.php',
        'hwe/b_diplomacy.php',
        'hwe/b_genList.php',
        'hwe/b_myBossInfo.php',
        'hwe/b_myCityInfo.php',
        'hwe/b_myGenInfo.php',
        'hwe/b_myKingdomInfo.php',
        'hwe/b_myPage.php',
        'hwe/b_processing.php',
        'hwe/b_tournament.php',
        'hwe/b_troop.php',
        'hwe/c_auction.php',
        'hwe/c_die_immediately.php',
        'hwe/c_dipcenter.php',
        'hwe/c_tournament.php',
        'hwe/c_vacation.php',
        'hwe/c_vote.php',
        'hwe/func.php',
        'hwe/func_auction.php',
        'hwe/func_command.php',
        'hwe/func_converter.php',
        'hwe/func_gamerule.php',
        'hwe/func_history.php',
        'hwe/func_legacy.php',
        'hwe/func_map.php',
        'hwe/func_message.php',
        'hwe/func_process.php',
        'hwe/func_string.php',
        'hwe/func_template.php',
        'hwe/func_time_event.php',
        'hwe/func_tournament.php',
        'hwe/index.php',
        'hwe/install.php',
        'hwe/install_db.php',
        'hwe/join.php',
        'hwe/join_post.php',
        'hwe/j_adjust_icon.php',
        'hwe/j_autoreset.php',
        'hwe/j_basic_info.php',
        'hwe/j_betting.php',
        'hwe/j_board_article_add.php',
        'hwe/j_board_comment_add.php',
        'hwe/j_board_get_articles.php',
        'hwe/j_chief_turn.php',
        'hwe/j_diplomacy_destroy_letter.php',
        'hwe/j_diplomacy_get_letter.php',
        'hwe/j_diplomacy_respond_letter.php',
        'hwe/j_diplomacy_rollback_letter.php',
        'hwe/j_diplomacy_send_letter.php',
        'hwe/j_general_set_permission.php',
        'hwe/j_general_turn.php',
        'hwe/j_getChiefTurn.php',
        'hwe/j_get_city_list.php',
        'hwe/j_get_general_list.php',
        'hwe/j_get_nation_general_list.php',
        'hwe/j_get_reserved_command.php',
        'hwe/j_get_select_npc_token.php',
        'hwe/j_image_upload.php',
        'hwe/j_install.php',
        'hwe/j_install_db.php',
        'hwe/j_load_scenarios.php',
        'hwe/j_map.php',
        'hwe/j_map_history.php',
        'hwe/j_msg_contact_list.php',
        'hwe/j_msg_decide_opt.php',
        'hwe/j_msg_delete.php',
        'hwe/j_msg_get_old.php',
        'hwe/j_msg_get_recent.php',
        'hwe/j_msg_submit.php',
        'hwe/j_myBossInfo.php',
        'hwe/j_select_npc.php',
        'hwe/j_server_basic_info.php',
        'hwe/j_set_chief_command.php',
        'hwe/j_set_general_command.php',
        'hwe/j_simulate_battle.php',
        'hwe/j_troop.php',
        'hwe/lib.php',
        'hwe/process_war.php',
        'hwe/select_npc.php',
        'hwe/t_board.php',
        'hwe/t_diplomacy.php',
        'hwe/_119.php',
        'hwe/_119_b.php',
        'hwe/_admin1.php',
        'hwe/_admin1_submit.php',
        'hwe/_admin2.php',
        'hwe/_admin2_submit.php',
        'hwe/_admin4.php',
        'hwe/_admin4_submit.php',
        'hwe/_admin5.php',
        'hwe/_admin5_submit.php',
        'hwe/_admin6.php',
        'hwe/_admin7.php',
        'hwe/_admin8.php',
        'hwe/_admin_force_rehall.php',
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